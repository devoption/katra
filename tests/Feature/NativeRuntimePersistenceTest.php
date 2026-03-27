<?php

use App\Services\Surreal\SurrealCliClient;
use App\Services\Surreal\SurrealConnection;
use App\Services\Surreal\SurrealHttpClient;
use App\Services\Surreal\SurrealRuntimeManager;
use Illuminate\Cache\FileStore;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

test('the native runtime keeps auth, sessions, and cache state on surreal', function () {
    $client = app(SurrealCliClient::class);

    if (! $client->isAvailable()) {
        $this->markTestSkipped('The `surreal` CLI is not available in this environment.');
    }

    $storagePath = storage_path('app/surrealdb/native-runtime-test-'.Str::uuid());
    $originalConfig = snapshotNativeRuntimeConfig();
    $originalNativeRunning = env('NATIVEPHP_RUNNING');
    $originalNativeStoragePath = env('NATIVEPHP_STORAGE_PATH');
    $originalNativeDatabasePath = env('NATIVEPHP_DATABASE_PATH');

    File::deleteDirectory($storagePath);
    File::ensureDirectoryExists(dirname($storagePath));

    try {
        $server = retryStartingNativeRuntimeSurrealServer($client, $storagePath);

        setNativeRuntimeEnvironment('NATIVEPHP_RUNNING', 'true');
        setNativeRuntimeEnvironment('NATIVEPHP_STORAGE_PATH', storage_path('framework/testing/native-runtime-'.Str::uuid()));
        setNativeRuntimeEnvironment('NATIVEPHP_DATABASE_PATH', database_path('native-runtime-test.sqlite'));

        $this->refreshApplication();

        config()->set('surreal.host', '127.0.0.1');
        config()->set('surreal.port', $server['port']);
        config()->set('surreal.endpoint', $server['endpoint']);
        config()->set('surreal.username', 'root');
        config()->set('surreal.password', 'root');
        config()->set('surreal.namespace', 'katra');
        config()->set('surreal.database', 'native_runtime_test');
        config()->set('surreal.storage_engine', 'surrealkv');
        config()->set('surreal.storage_path', $storagePath);
        config()->set('surreal.runtime', 'local');
        config()->set('surreal.autostart', false);
        config()->set('session.table', 'sessions');
        config()->set('session.cookie', 'native-runtime-surreal');

        resetNativeRuntimePersistenceState();

        expect(config('database.default'))->toBe('surreal')
            ->and(config('database.migrations.connection'))->toBe('surreal')
            ->and(config('session.driver'))->toBe('surreal')
            ->and(config('session.connection'))->toBe('surreal')
            ->and(config('cache.default'))->toBe('surreal')
            ->and(config('queue.default'))->toBe('surreal')
            ->and(config('queue.failed.database'))->toBe('surreal')
            ->and(config('queue.batching.database'))->toBe('surreal')
            ->and(cache()->driver(config('cache.limiter'))->getStore())->toBeInstanceOf(FileStore::class);

        expect(Artisan::call('migrate', [
            '--database' => 'surreal',
            '--force' => true,
            '--realpath' => true,
            '--path' => database_path('migrations/0001_01_01_000000_create_users_table.php'),
        ]))->toBe(0);

        expect(Artisan::call('migrate', [
            '--database' => 'surreal',
            '--force' => true,
            '--realpath' => true,
            '--path' => database_path('migrations/2026_03_24_064850_add_profile_name_columns_to_users_table.php'),
        ]))->toBe(0);

        expect(Artisan::call('migrate', [
            '--database' => 'surreal',
            '--force' => true,
            '--realpath' => true,
            '--path' => database_path('migrations/0001_01_01_000001_create_cache_table.php'),
        ]))->toBe(0);

        expect(Cache::store(config('cache.default'))->put('desktop:last-workspace', 'katra-local', 60))->toBeTrue()
            ->and(Cache::store(config('cache.default'))->get('desktop:last-workspace'))->toBe('katra-local');

        $this->post(route('register'), [
            'first_name' => 'Native',
            'last_name' => 'Tester',
            'email' => 'native@katra.test',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();

        $storedUser = DB::connection('surreal')->table('users')
            ->where('email', 'native@katra.test')
            ->first();

        expect($storedUser)->not->toBeNull()
            ->and(data_get($storedUser, 'first_name'))->toBe('Native')
            ->and(data_get($storedUser, 'last_name'))->toBe('Tester')
            ->and(DB::connection('surreal')->table('sessions')->count())->toBeGreaterThan(0);
    } finally {
        restoreNativeRuntimeConfig($originalConfig);
        setNativeRuntimeEnvironment('NATIVEPHP_RUNNING', $originalNativeRunning);
        setNativeRuntimeEnvironment('NATIVEPHP_STORAGE_PATH', $originalNativeStoragePath);
        setNativeRuntimeEnvironment('NATIVEPHP_DATABASE_PATH', $originalNativeDatabasePath);
        $this->refreshApplication();
        resetNativeRuntimePersistenceState();

        if (isset($server['process'])) {
            $server['process']->stop(1);
        }

        File::deleteDirectory($storagePath);
    }
});

function snapshotNativeRuntimeConfig(): array
{
    $configKeys = [
        'nativephp-internal.running',
        'database.default',
        'database.migrations.connection',
        'session.driver',
        'session.connection',
        'session.table',
        'session.cookie',
        'cache.default',
        'cache.limiter',
        'cache.stores.database.connection',
        'cache.stores.database.lock_connection',
        'cache.stores.surreal.connection',
        'cache.stores.surreal.lock_connection',
        'queue.default',
        'queue.failed.database',
        'queue.batching.database',
        'queue.connections.database.connection',
        'queue.connections.surreal.connection',
        'ai.caching.embeddings.store',
    ];

    $snapshot = [];

    foreach ($configKeys as $key) {
        $snapshot[$key] = config($key);
    }

    return $snapshot;
}

function restoreNativeRuntimeConfig(array $snapshot): void
{
    foreach ($snapshot as $key => $value) {
        config()->set($key, $value);
    }
}

function setNativeRuntimeEnvironment(string $key, string|false|null $value): void
{
    if ($value === false || $value === null) {
        putenv($key);
        unset($_ENV[$key], $_SERVER[$key]);

        return;
    }

    putenv(sprintf('%s=%s', $key, $value));
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

function resetNativeRuntimePersistenceState(): void
{
    app()->forgetInstance(SurrealConnection::class);
    app()->forgetInstance(SurrealRuntimeManager::class);
    DB::purge('surreal');
    DB::purge('nativephp');
    Cache::forgetDriver('database');
    Cache::forgetDriver('surreal');
    app()->forgetInstance('cache');
    app()->forgetInstance('cache.store');
    app('session')->forgetDrivers();
    app()->forgetInstance('session.store');
    app()->forgetInstance('migration.repository');
    app()->forgetInstance('migrator');
}

/**
 * @return array{endpoint: string, port: int, process: Process}
 */
function retryStartingNativeRuntimeSurrealServer(SurrealCliClient $client, string $storagePath, int $attempts = 3): array
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

    throw new RuntimeException('Unable to start the SurrealDB native runtime test server.');
}
