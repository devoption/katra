<?php

use App\Services\Surreal\SurrealCliClient;
use App\Services\Surreal\SurrealConnection;
use App\Services\Surreal\SurrealHttpClient;
use App\Services\Surreal\SurrealRuntimeManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

test('the database cache store works on the surreal connection', function () {
    $client = app(SurrealCliClient::class);

    if (! $client->isAvailable()) {
        $this->markTestSkipped('The `surreal` CLI is not available in this environment.');
    }

    $storagePath = storage_path('app/surrealdb/database-cache-test-'.Str::uuid());
    $originalDefaultConnection = config('database.default');
    $originalMigrationConnection = config('database.migrations.connection');
    $originalCacheStore = config('cache.default');
    $originalCacheDatabaseConnection = config('cache.stores.database.connection');
    $originalCacheLockConnection = config('cache.stores.database.lock_connection');

    File::deleteDirectory($storagePath);
    File::ensureDirectoryExists(dirname($storagePath));

    try {
        $server = retryStartingSurrealCacheServer($client, $storagePath);

        config()->set('database.default', 'surreal');
        config()->set('database.migrations.connection', null);
        config()->set('surreal.host', '127.0.0.1');
        config()->set('surreal.port', $server['port']);
        config()->set('surreal.endpoint', $server['endpoint']);
        config()->set('surreal.username', 'root');
        config()->set('surreal.password', 'root');
        config()->set('surreal.namespace', 'katra');
        config()->set('surreal.database', 'database_cache_test');
        config()->set('surreal.storage_engine', 'surrealkv');
        config()->set('surreal.storage_path', $storagePath);
        config()->set('surreal.runtime', 'local');
        config()->set('surreal.autostart', false);
        config()->set('cache.default', 'database');
        config()->set('cache.stores.database.connection', 'surreal');
        config()->set('cache.stores.database.lock_connection', 'surreal');

        app()->forgetInstance(SurrealConnection::class);
        app()->forgetInstance(SurrealRuntimeManager::class);
        DB::purge('surreal');
        Cache::forgetDriver('database');
        app()->forgetInstance('cache');
        app()->forgetInstance('cache.store');
        app()->forgetInstance('migration.repository');
        app()->forgetInstance('migrator');

        expect(Artisan::call('migrate', [
            '--force' => true,
            '--realpath' => true,
            '--path' => database_path('migrations/0001_01_01_000001_create_cache_table.php'),
        ]))->toBe(0);

        $store = Cache::store('database');

        expect($store->add('login:127.0.0.1', 'first-hit', 60))->toBeTrue()
            ->and($store->add('login:127.0.0.1', 'second-hit', 60))->toBeFalse()
            ->and($store->get('login:127.0.0.1'))->toBe('first-hit');

        expect($store->put('login:127.0.0.1', 'updated-hit', 60))->toBeTrue()
            ->and($store->get('login:127.0.0.1'))->toBe('updated-hit');
    } finally {
        config()->set('database.default', $originalDefaultConnection);
        config()->set('database.migrations.connection', $originalMigrationConnection);
        config()->set('cache.default', $originalCacheStore);
        config()->set('cache.stores.database.connection', $originalCacheDatabaseConnection);
        config()->set('cache.stores.database.lock_connection', $originalCacheLockConnection);

        app()->forgetInstance(SurrealConnection::class);
        app()->forgetInstance(SurrealRuntimeManager::class);
        DB::purge('surreal');
        Cache::forgetDriver('database');
        app()->forgetInstance('cache');
        app()->forgetInstance('cache.store');
        app()->forgetInstance('migration.repository');
        app()->forgetInstance('migrator');

        if (isset($server['process'])) {
            $server['process']->stop(1);
        }

        File::deleteDirectory($storagePath);
    }
});

/**
 * @return array{endpoint: string, port: int, process: Process}
 */
function retryStartingSurrealCacheServer(SurrealCliClient $client, string $storagePath, int $attempts = 3): array
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

    throw new RuntimeException('Unable to start the SurrealDB cache test runtime.');
}
