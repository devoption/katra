<?php

namespace Katra\Katra\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Katra\Katra\Console\Commands\InstallCommand;

class KatraServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/katra.php',
            'katra'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../../config/fortify.php',
            'fortify'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../../config/blade-icons.php',
            'blade-icons'
        );
    }

    public function boot()
    {
        $this->configureCommands();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../public'                 => public_path('katra'),
                __DIR__.'/../../config/katra.php'       => config_path('katra.php'),
                __DIR__.'/../../config/fortify.php'     => config_path('fortify.php'),
                __DIR__.'/../../config/blade-icons.php' => config_path('blade-icons.php'),
                __DIR__.'/../../resources/views'        => resource_path('views/vendor/katra'),
            ], 'assets');
        }

        $this->registerRoutes();

        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'katra');

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    protected function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        });
    }

    protected function routeConfiguration()
    {
        return [
            'prefix'     => config('katra.route.prefix'),
            'middleware' => config('katra.route.middleware'),
        ];
    }

    /**
     * Configure the commands offered by the application.
     *
     * @return void
     */
    protected function configureCommands()
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
        ]);
    }
}
