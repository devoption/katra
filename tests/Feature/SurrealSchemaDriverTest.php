<?php

use App\Services\Surreal\SurrealCliClient;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

test('laravel schema can create alter and drop surreal-backed tables', function () {
    $client = app(SurrealCliClient::class);

    if (! $client->isAvailable()) {
        $this->markTestSkipped('The `surreal` CLI is not available in this environment.');
    }

    $storagePath = storage_path('app/surrealdb/schema-driver-test-'.Str::uuid());

    File::deleteDirectory($storagePath);
    File::ensureDirectoryExists(dirname($storagePath));

    try {
        $server = retryStartingSurrealSchemaServer($client, $storagePath);

        config()->set('surreal.host', '127.0.0.1');
        config()->set('surreal.port', $server['port']);
        config()->set('surreal.endpoint', $server['endpoint']);
        config()->set('surreal.username', 'root');
        config()->set('surreal.password', 'root');
        config()->set('surreal.namespace', 'katra');
        config()->set('surreal.database', 'schema_driver_test');
        config()->set('surreal.storage_engine', 'surrealkv');
        config()->set('surreal.storage_path', $storagePath);
        config()->set('surreal.runtime', 'local');
        config()->set('surreal.autostart', false);

        DB::purge('surreal');

        $schema = Schema::connection('surreal');

        $schema->create('conversation_nodes', function (Blueprint $table): void {
            $table->string('title');
            $table->text('summary')->nullable();
            $table->boolean('open');
            $table->timestamps();
        });

        expect($schema->hasTable('conversation_nodes'))->toBeTrue()
            ->and($schema->hasColumns('conversation_nodes', [
                'title',
                'summary',
                'open',
                'created_at',
                'updated_at',
            ]))->toBeTrue()
            ->and($schema->getColumnType('conversation_nodes', 'title'))->toBe('string')
            ->and($schema->getColumnType('conversation_nodes', 'summary', fullDefinition: true))->toBe('none | string')
            ->and($schema->getColumnType('conversation_nodes', 'open'))->toBe('bool')
            ->and($schema->getColumnType('conversation_nodes', 'created_at'))->toBe('datetime');

        $schema->table('conversation_nodes', function (Blueprint $table): void {
            $table->string('status')->nullable();
        });

        expect($schema->hasColumn('conversation_nodes', 'status'))->toBeTrue()
            ->and($schema->getColumnType('conversation_nodes', 'status', fullDefinition: true))->toBe('none | string');

        $schema->table('conversation_nodes', function (Blueprint $table): void {
            $table->dropColumn('status');
        });

        expect($schema->hasColumn('conversation_nodes', 'status'))->toBeFalse();

        $schema->dropIfExists('conversation_nodes');

        expect($schema->hasTable('conversation_nodes'))->toBeFalse();
    } finally {
        DB::purge('surreal');

        if (isset($server['process'])) {
            $server['process']->stop(1);
        }

        File::deleteDirectory($storagePath);
    }
});

test('laravel can run and refresh the application migrations with surreal as the default connection', function () {
    $client = app(SurrealCliClient::class);

    if (! $client->isAvailable()) {
        $this->markTestSkipped('The `surreal` CLI is not available in this environment.');
    }

    $storagePath = storage_path('app/surrealdb/schema-driver-migrate-test-'.Str::uuid());

    File::deleteDirectory($storagePath);
    File::ensureDirectoryExists(dirname($storagePath));

    try {
        $server = retryStartingSurrealSchemaServer($client, $storagePath);

        config()->set('database.default', 'surreal');
        config()->set('database.migrations.connection', null);

        config()->set('surreal.host', '127.0.0.1');
        config()->set('surreal.port', $server['port']);
        config()->set('surreal.endpoint', $server['endpoint']);
        config()->set('surreal.username', 'root');
        config()->set('surreal.password', 'root');
        config()->set('surreal.namespace', 'katra');
        config()->set('surreal.database', 'schema_driver_migrate_test');
        config()->set('surreal.storage_engine', 'surrealkv');
        config()->set('surreal.storage_path', $storagePath);
        config()->set('surreal.runtime', 'local');
        config()->set('surreal.autostart', false);

        DB::purge('surreal');
        app()->forgetInstance('migration.repository');
        app()->forgetInstance('migrator');

        $migrateExitCode = Artisan::call('migrate', [
            '--force' => true,
            '--realpath' => true,
            '--path' => database_path('migrations'),
        ]);

        $schema = Schema::connection('surreal');
        $repository = app('migration.repository');

        expect($migrateExitCode)->toBe(0)
            ->and($schema->hasTable('users'))->toBeTrue()
            ->and($schema->hasTable('cache'))->toBeTrue()
            ->and($schema->hasTable('jobs'))->toBeTrue()
            ->and($schema->hasTable('features'))->toBeTrue()
            ->and($schema->hasTable('agent_conversations'))->toBeTrue()
            ->and($schema->hasTable('migrations'))->toBeTrue()
            ->and($repository->getRan())->toHaveCount(5);

        app()->forgetInstance('migration.repository');
        app()->forgetInstance('migrator');

        $freshExitCode = Artisan::call('migrate:fresh', [
            '--database' => 'surreal',
            '--force' => true,
            '--realpath' => true,
            '--path' => database_path('migrations'),
        ]);

        $repository = app('migration.repository');

        expect($freshExitCode)->toBe(0)
            ->and($schema->hasTable('users'))->toBeTrue()
            ->and($schema->hasTable('migrations'))->toBeTrue()
            ->and($repository->getRan())->toHaveCount(5);
    } finally {
        DB::purge('surreal');

        if (isset($server['process'])) {
            $server['process']->stop(1);
        }

        File::deleteDirectory($storagePath);
    }
});

/**
 * @return array{endpoint: string, port: int, process: Process}
 */
function retryStartingSurrealSchemaServer(SurrealCliClient $client, string $storagePath, int $attempts = 3): array
{
    $lastException = null;

    for ($attempt = 1; $attempt <= $attempts; $attempt++) {
        $port = reserveSurrealSchemaPort();
        $endpoint = sprintf('ws://127.0.0.1:%d', $port);
        $process = $client->startLocalServer(
            bindAddress: sprintf('127.0.0.1:%d', $port),
            datastorePath: $storagePath,
            username: 'root',
            password: 'root',
            storageEngine: 'surrealkv',
        );

        if ($client->waitUntilReady($endpoint)) {
            return [
                'endpoint' => $endpoint,
                'port' => $port,
                'process' => $process,
            ];
        }

        $process->stop(1);
        $lastException = new RuntimeException(sprintf('SurrealDB did not become ready on %s.', $endpoint));
    }

    throw $lastException ?? new RuntimeException('Unable to start the SurrealDB schema test runtime.');
}

function reserveSurrealSchemaPort(): int
{
    $socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);

    if ($socket === false) {
        throw new RuntimeException(sprintf('Unable to reserve a free TCP port: %s (%d)', $errorMessage, $errorCode));
    }

    $address = stream_socket_get_name($socket, false);

    fclose($socket);

    if ($address === false) {
        throw new RuntimeException('Unable to determine the reserved TCP port.');
    }

    $port = (int) ltrim((string) strrchr($address, ':'), ':');

    if ($port <= 0) {
        throw new RuntimeException(sprintf('Unable to parse the reserved TCP port from [%s].', $address));
    }

    return $port;
}
