<?php

namespace App\Providers;

use App\Services\Surreal\Migrations\SurrealMigrationRepository;
use App\Services\Surreal\Queue\SurrealQueueConnector;
use App\Services\Surreal\Schema\SurrealSchemaConnection;
use App\Services\Surreal\SurrealConnection;
use App\Services\Surreal\SurrealDocumentStore;
use App\Services\Surreal\SurrealHttpClient;
use App\Services\Surreal\SurrealRuntimeManager;
use App\Support\Native\NativeRuntimePersistence;
use Illuminate\Database\DatabaseManager;
use Illuminate\Queue\QueueManager;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SurrealConnection::class, fn (): SurrealConnection => SurrealConnection::fromConfig(config('surreal')));
        $this->app->singleton(SurrealHttpClient::class);
        $this->app->singleton(SurrealRuntimeManager::class);
        $this->app->singleton(SurrealDocumentStore::class);
    }

    public function boot(): void
    {
        $this->app->make(NativeRuntimePersistence::class)->configure();

        $this->app->extend('migration.repository', function ($repository, $app): SurrealMigrationRepository {
            $migrations = $app['config']['database.migrations'];
            $table = is_array($migrations) ? ($migrations['table'] ?? 'migrations') : $migrations;

            return new SurrealMigrationRepository($app['db'], $table, $app->make(SurrealHttpClient::class));
        });

        $this->app->make(DatabaseManager::class)->extend('surreal', function (array $config, string $name): SurrealSchemaConnection {
            return SurrealSchemaConnection::fromConfig(
                array_merge(config('surreal'), $config),
                $name,
            );
        });

        $this->app->make('session')->extend('surreal', function ($app): DatabaseSessionHandler {
            $connection = config('session.connection') ?: 'surreal';
            $table = config('session.table');
            $lifetime = (int) config('session.lifetime');

            return new DatabaseSessionHandler(
                $app['db']->connection($connection),
                $table,
                $lifetime,
                $app,
            );
        });

        $this->app->afterResolving('queue', function (QueueManager $manager): void {
            $manager->addConnector('surreal', function (): SurrealQueueConnector {
                return new SurrealQueueConnector($this->app['db']);
            });
        });
    }
}
