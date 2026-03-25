<?php

use App\Services\Surreal\SurrealCliClient;
use App\Services\Surreal\SurrealConnection;
use App\Services\Surreal\SurrealHttpClient;
use App\Services\Surreal\SurrealRuntimeManager;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

test('the surreal session driver supports the normal session lifecycle', function () {
    $client = app(SurrealCliClient::class);

    if (! $client->isAvailable()) {
        $this->markTestSkipped('The `surreal` CLI is not available in this environment.');
    }

    $storagePath = storage_path('app/surrealdb/session-driver-test-'.Str::uuid());
    $originalDefaultConnection = config('database.default');
    $originalMigrationConnection = config('database.migrations.connection');
    $originalSessionDriver = config('session.driver');
    $originalSessionConnection = config('session.connection');
    $originalSessionTable = config('session.table');
    $originalSessionLifetime = config('session.lifetime');
    $originalSessionCookie = config('session.cookie');

    File::deleteDirectory($storagePath);
    File::ensureDirectoryExists(dirname($storagePath));

    try {
        $server = retryStartingSurrealSessionServer($client, $storagePath);

        config()->set('database.default', 'sqlite');
        config()->set('database.migrations.connection', null);
        config()->set('surreal.host', '127.0.0.1');
        config()->set('surreal.port', $server['port']);
        config()->set('surreal.endpoint', $server['endpoint']);
        config()->set('surreal.username', 'root');
        config()->set('surreal.password', 'root');
        config()->set('surreal.namespace', 'katra');
        config()->set('surreal.database', 'session_driver_test');
        config()->set('surreal.storage_engine', 'surrealkv');
        config()->set('surreal.storage_path', $storagePath);
        config()->set('surreal.runtime', 'local');
        config()->set('surreal.autostart', false);
        config()->set('session.driver', 'surreal');
        config()->set('session.connection', null);
        config()->set('session.table', 'sessions');
        config()->set('session.lifetime', 1);
        config()->set('session.cookie', 'surreal-session-test');

        resetSurrealSessionState();

        expect(Artisan::call('migrate', [
            '--database' => 'surreal',
            '--force' => true,
            '--realpath' => true,
            '--path' => database_path('migrations/0001_01_01_000000_create_users_table.php'),
        ]))->toBe(0);

        $session = app('session')->driver('surreal');

        expect($session->getHandler())->toBeInstanceOf(DatabaseSessionHandler::class);

        $session->start();
        $session->put('workspace', 'katra-local');
        $session->save();

        $sessionId = $session->getId();

        $reloadedSession = refreshedSurrealSessionStore($sessionId);

        expect($reloadedSession->get('workspace'))->toBe('katra-local');

        $reloadedSession->put('workspace', 'katra-server');
        $reloadedSession->save();

        $updatedSession = refreshedSurrealSessionStore($sessionId);

        expect($updatedSession->get('workspace'))->toBe('katra-server');

        expect(DB::connection('surreal')->table('sessions')->where('id', $sessionId)->update([
            'last_activity' => now()->subMinutes(5)->timestamp,
        ]))->toBe(1);

        $expiredSession = refreshedSurrealSessionStore($sessionId);

        expect($expiredSession->get('workspace'))->toBeNull();

        $expiredSession->getHandler()->gc(config('session.lifetime') * 60);

        expect(DB::connection('surreal')->table('sessions')->where('id', $sessionId)->count())->toBe(0);
    } finally {
        config()->set('database.default', $originalDefaultConnection);
        config()->set('database.migrations.connection', $originalMigrationConnection);
        config()->set('session.driver', $originalSessionDriver);
        config()->set('session.connection', $originalSessionConnection);
        config()->set('session.table', $originalSessionTable);
        config()->set('session.lifetime', $originalSessionLifetime);
        config()->set('session.cookie', $originalSessionCookie);

        resetSurrealSessionState();

        if (isset($server['process'])) {
            $server['process']->stop(1);
        }

        File::deleteDirectory($storagePath);
    }
});

function refreshedSurrealSessionStore(string $sessionId): Store
{
    resetSurrealSessionState();

    /** @var Store $session */
    $session = app('session')->driver('surreal');
    $session->setId($sessionId);
    $session->start();

    return $session;
}

function resetSurrealSessionState(): void
{
    app()->forgetInstance(SurrealConnection::class);
    app()->forgetInstance(SurrealRuntimeManager::class);
    DB::purge('surreal');
    app('session')->forgetDrivers();
    app()->forgetInstance('session.store');
    app()->forgetInstance('migration.repository');
    app()->forgetInstance('migrator');
}

/**
 * @return array{endpoint: string, port: int, process: Process}
 */
function retryStartingSurrealSessionServer(SurrealCliClient $client, string $storagePath, int $attempts = 3): array
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

    throw new RuntimeException('Unable to start the SurrealDB session test runtime.');
}
