<?php

use App\Models\User;
use App\Services\Surreal\SurrealCliClient;
use App\Services\Surreal\SurrealConnection;
use App\Services\Surreal\SurrealHttpClient;
use App\Services\Surreal\SurrealRuntimeManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

test('standard eloquent user queries work on the surreal connection', function () {
    $client = app(SurrealCliClient::class);

    if (! $client->isAvailable()) {
        $this->markTestSkipped('The `surreal` CLI is not available in this environment.');
    }

    $storagePath = storage_path('app/surrealdb/eloquent-driver-test-'.Str::uuid());
    $originalDefaultConnection = config('database.default');
    $originalMigrationConnection = config('database.migrations.connection');

    File::deleteDirectory($storagePath);
    File::ensureDirectoryExists(dirname($storagePath));

    try {
        $server = retryStartingSurrealEloquentServer($client, $storagePath);

        config()->set('database.default', 'surreal');
        config()->set('database.migrations.connection', null);
        config()->set('surreal.host', '127.0.0.1');
        config()->set('surreal.port', $server['port']);
        config()->set('surreal.endpoint', $server['endpoint']);
        config()->set('surreal.username', 'root');
        config()->set('surreal.password', 'root');
        config()->set('surreal.namespace', 'katra');
        config()->set('surreal.database', 'eloquent_driver_test');
        config()->set('surreal.storage_engine', 'surrealkv');
        config()->set('surreal.storage_path', $storagePath);
        config()->set('surreal.runtime', 'local');
        config()->set('surreal.autostart', false);

        app()->forgetInstance(SurrealConnection::class);
        app()->forgetInstance(SurrealRuntimeManager::class);
        DB::purge('surreal');
        app()->forgetInstance('migration.repository');
        app()->forgetInstance('migrator');

        $migrateExitCode = Artisan::call('migrate', [
            '--force' => true,
            '--realpath' => true,
            '--path' => database_path('migrations/0001_01_01_000000_create_users_table.php'),
        ]);

        expect($migrateExitCode)->toBe(0);

        $featureMigrateExitCode = Artisan::call('migrate', [
            '--force' => true,
            '--realpath' => true,
            '--path' => database_path('migrations/2026_03_21_004800_create_features_table.php'),
        ]);

        expect($featureMigrateExitCode)->toBe(0);

        $sessionPayload = json_encode([
            'url' => 'https://katra.test/?workspace=katra-local',
            '_flash' => [
                'old' => [],
                'new' => ['status'],
            ],
        ], JSON_THROW_ON_ERROR);

        expect(DB::connection('surreal')->table('sessions')->insert([
            'id' => 'session-with-slashes',
            'user_id' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Pest Browser',
            'payload' => $sessionPayload,
            'last_activity' => now()->timestamp,
        ]))->toBeTrue();

        $storedSession = DB::connection('surreal')->table('sessions')
            ->where('id', 'session-with-slashes')
            ->first();

        expect($storedSession)->not->toBeNull()
            ->and($storedSession?->payload)->toBe($sessionPayload)
            ->and(data_get($storedSession, 'user_id'))->toBeNull();

        DB::connection('surreal')->table('features')->insert([
            [
                'id' => 1,
                'name' => 'ui.desktop.mvp-shell',
                'scope' => 'desktop-ui',
                'value' => 'true',
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ],
            [
                'id' => 2,
                'name' => 'ui.desktop.workspace-navigation',
                'scope' => 'desktop-ui',
                'value' => 'false',
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ],
        ]);

        $featureRecords = DB::connection('surreal')->table('features')
            ->where(fn ($query) => $query->where('name', 'ui.desktop.mvp-shell')->where('scope', 'desktop-ui'))
            ->orWhere(fn ($query) => $query->where('name', 'ui.desktop.workspace-navigation')->where('scope', 'desktop-ui'))
            ->orderBy('id')
            ->get();

        expect($featureRecords)->toHaveCount(2)
            ->and($featureRecords->pluck('name')->all())->toBe([
                'ui.desktop.mvp-shell',
                'ui.desktop.workspace-navigation',
            ]);

        $user = User::query()->create([
            'name' => 'Derek Bourgeois',
            'email' => 'derek@katra.io',
            'password' => 'password',
        ]);

        expect($user->id)->toBe(1)
            ->and($user->exists)->toBeTrue();

        $queriedUser = User::query()->where('email', 'derek@katra.io')->first();

        expect($queriedUser)->not->toBeNull()
            ->and($queriedUser?->name)->toBe('Derek Bourgeois')
            ->and($queriedUser?->id)->toBe(1);

        $foundUser = User::query()->find(1);

        expect($foundUser)->not->toBeNull()
            ->and($foundUser?->email)->toBe('derek@katra.io');

        $user->forceFill(['remember_token' => 'remember-me']);
        $user->save();

        $rememberedUser = User::query()->where('remember_token', 'remember-me')->first();

        expect($rememberedUser)->not->toBeNull()
            ->and($rememberedUser?->id)->toBe(1)
            ->and(User::query()->count())->toBe(1)
            ->and(User::query()->where('email', 'derek@katra.io')->exists())->toBeTrue();

        expect($user->delete())->toBeTrue()
            ->and(User::query()->find(1))->toBeNull();
    } finally {
        config()->set('database.default', $originalDefaultConnection);
        config()->set('database.migrations.connection', $originalMigrationConnection);

        app()->forgetInstance(SurrealConnection::class);
        app()->forgetInstance(SurrealRuntimeManager::class);
        DB::purge('surreal');
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
function retryStartingSurrealEloquentServer(SurrealCliClient $client, string $storagePath, int $attempts = 3): array
{
    $httpClient = app(SurrealHttpClient::class);
    $lastException = null;

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
        $lastException = new RuntimeException(sprintf('SurrealDB did not become ready on %s.', $endpoint));
    }

    throw $lastException ?? new RuntimeException('Unable to start the SurrealDB eloquent test runtime.');
}
