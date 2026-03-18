<?php

namespace App\Providers;

use App\Services\Surreal\SurrealConnection;
use App\Services\Surreal\SurrealDocumentStore;
use App\Services\Surreal\SurrealRuntimeManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SurrealConnection::class, fn (): SurrealConnection => SurrealConnection::fromConfig(config('surreal')));
        $this->app->singleton(SurrealRuntimeManager::class);
        $this->app->singleton(SurrealDocumentStore::class);
    }

    public function boot(): void
    {
        //
    }
}
