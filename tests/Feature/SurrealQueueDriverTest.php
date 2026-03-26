<?php

use App\Services\Surreal\Queue\SurrealQueue;
use App\Services\Surreal\SurrealCliClient;
use App\Services\Surreal\SurrealConnection;
use App\Services\Surreal\SurrealHttpClient;
use App\Services\Surreal\SurrealRuntimeManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

test('the surreal queue driver supports the queue lifecycle', function () {
    $client = app(SurrealCliClient::class);

    if (! $client->isAvailable()) {
        $this->markTestSkipped('The `surreal` CLI is not available in this environment.');
    }

    $storagePath = storage_path('app/surrealdb/queue-driver-test-'.Str::uuid());
    $completionMarker = storage_path('app/queue-driver-test-complete-'.Str::uuid().'.txt');
    $retryMarker = storage_path('app/queue-driver-test-retry-'.Str::uuid().'.txt');
    $originalDefaultConnection = config('database.default');
    $originalMigrationConnection = config('database.migrations.connection');
    $originalQueueDefault = config('queue.default');
    $originalSurrealQueueConnection = config('queue.connections.surreal');
    $originalFailedDriver = config('queue.failed.driver');
    $originalFailedDatabase = config('queue.failed.database');
    $originalFailedTable = config('queue.failed.table');

    File::deleteDirectory($storagePath);
    File::delete($completionMarker);
    File::delete($retryMarker);
    File::ensureDirectoryExists(dirname($storagePath));
    File::ensureDirectoryExists(dirname($completionMarker));

    try {
        $server = retryStartingSurrealQueueServer($client, $storagePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.migrations.connection', null);
        config()->set('surreal.host', '127.0.0.1');
        config()->set('surreal.port', $server['port']);
        config()->set('surreal.endpoint', $server['endpoint']);
        config()->set('surreal.username', 'root');
        config()->set('surreal.password', 'root');
        config()->set('surreal.namespace', 'katra');
        config()->set('surreal.database', 'queue_driver_test');
        config()->set('surreal.storage_engine', 'surrealkv');
        config()->set('surreal.storage_path', $storagePath);
        config()->set('surreal.runtime', 'local');
        config()->set('surreal.autostart', false);
        config()->set('queue.default', 'surreal');
        config()->set('queue.connections.surreal', [
            'driver' => 'surreal',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 60,
            'after_commit' => false,
        ]);
        config()->set('queue.failed.driver', 'database-uuids');
        config()->set('queue.failed.database', 'surreal');
        config()->set('queue.failed.table', 'failed_jobs');

        resetSurrealQueueState();

        expect(Artisan::call('migrate', [
            '--database' => 'surreal',
            '--force' => true,
            '--realpath' => true,
            '--path' => database_path('migrations/0001_01_01_000002_create_jobs_table.php'),
        ]))->toBe(0);

        $queue = app('queue')->connection('surreal');

        expect($queue)->toBeInstanceOf(SurrealQueue::class);

        $queue->push(new SurrealQueueCompletingTestJob($completionMarker, 'completed'));

        $reservedJob = $queue->pop();

        expect($reservedJob)->not->toBeNull()
            ->and(DB::connection('surreal')->table('jobs')->where('id', $reservedJob->getJobId())->value('reserved_at'))->not->toBeNull();

        $reservedJob->fire();
        $reservedJob->delete();

        expect(File::get($completionMarker))->toBe('completed')
            ->and(DB::connection('surreal')->table('jobs')->count())->toBe(0);

        $queue->push(new SurrealQueueCompletingTestJob($retryMarker, 'retried'));

        $releasedJob = $queue->pop();
        $releasedJob->release(0);

        $releasedRecord = DB::connection('surreal')->table('jobs')->orderBy('id', 'desc')->first();
        $releasedRecordData = (array) $releasedRecord;

        expect($releasedRecord)->not->toBeNull()
            ->and(array_key_exists('reserved_at', $releasedRecordData))->toBeFalse()
            ->and((int) $releasedRecord->attempts)->toBe(1);

        $retriedJob = $queue->pop();

        expect($retriedJob)->not->toBeNull()
            ->and($retriedJob->attempts())->toBe(2);

        $retriedJob->fire();
        $retriedJob->delete();

        expect(File::get($retryMarker))->toBe('retried')
            ->and(DB::connection('surreal')->table('jobs')->count())->toBe(0);

        $queue->push(new SurrealQueueCompletingTestJob($retryMarker, 'expired-reservation'));

        $expiredReservationJob = $queue->pop();

        expect($expiredReservationJob)->not->toBeNull();

        expect(DB::connection('surreal')->table('jobs')->where('id', $expiredReservationJob->getJobId())->update([
            'reserved_at' => now()->subMinutes(5)->timestamp,
        ]))->toBe(1);

        $expiredReservationRetry = $queue->pop();

        expect($expiredReservationRetry)->not->toBeNull()
            ->and($expiredReservationRetry->getJobId())->toBe($expiredReservationJob->getJobId())
            ->and($expiredReservationRetry->attempts())->toBe(2);

        $expiredReservationRetry->fire();
        $expiredReservationRetry->delete();

        expect(File::get($retryMarker))->toBe('expired-reservation')
            ->and(DB::connection('surreal')->table('jobs')->count())->toBe(0);

        $queue->push(new SurrealQueueFailingTestJob);

        Artisan::call('queue:work', [
            'connection' => 'surreal',
            '--once' => true,
            '--tries' => 1,
        ]);

        $failedJob = DB::connection('surreal')->table('failed_jobs')->first();

        expect($failedJob)->not->toBeNull()
            ->and($failedJob->connection)->toBe('surreal')
            ->and($failedJob->queue)->toBe('default')
            ->and($failedJob->exception)->toContain('Surreal queue failure.')
            ->and(DB::connection('surreal')->table('jobs')->count())->toBe(0);
    } finally {
        config()->set('database.default', $originalDefaultConnection);
        config()->set('database.migrations.connection', $originalMigrationConnection);
        config()->set('queue.default', $originalQueueDefault);
        config()->set('queue.connections.surreal', $originalSurrealQueueConnection);
        config()->set('queue.failed.driver', $originalFailedDriver);
        config()->set('queue.failed.database', $originalFailedDatabase);
        config()->set('queue.failed.table', $originalFailedTable);

        resetSurrealQueueState();

        if (isset($server['process'])) {
            $server['process']->stop(1);
        }

        File::deleteDirectory($storagePath);
        File::delete($completionMarker);
        File::delete($retryMarker);
    }
});

function resetSurrealQueueState(): void
{
    app()->forgetInstance(SurrealConnection::class);
    app()->forgetInstance(SurrealRuntimeManager::class);
    DB::purge('surreal');
    app()->forgetInstance('queue');
    app()->forgetInstance('queue.connection');
    app()->forgetInstance('queue.failer');
    app()->forgetInstance('migration.repository');
    app()->forgetInstance('migrator');
}

/**
 * @return array{endpoint: string, port: int, process: Process}
 */
function retryStartingSurrealQueueServer(SurrealCliClient $client, string $storagePath, int $attempts = 3): array
{
    $httpClient = app(SurrealHttpClient::class);

    for ($attempt = 1; $attempt <= $attempts; $attempt++) {
        $port = random_int(10240, 65535);
        $endpoint = sprintf('ws://127.0.0.1:%d', $port);
        $process = $client->startLocalServer(
            bindAddress: sprintf('127.0.0.1:%d', $port),
            datastorePath: $storagePath,
            username: 'root',
            password: 'root',
            storageEngine: 'surrealkv',
        );

        if ($httpClient->waitUntilReady($endpoint)) {
            return [
                'endpoint' => $endpoint,
                'port' => $port,
                'process' => $process,
            ];
        }

        $process->stop(1);
    }

    throw new RuntimeException('Unable to start the SurrealDB queue test runtime.');
}

class SurrealQueueCompletingTestJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public string $path,
        public string $message,
    ) {}

    public function handle(): void
    {
        File::put($this->path, $this->message);
    }
}

class SurrealQueueFailingTestJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;

    public function handle(): void
    {
        throw new RuntimeException('Surreal queue failure.');
    }
}
