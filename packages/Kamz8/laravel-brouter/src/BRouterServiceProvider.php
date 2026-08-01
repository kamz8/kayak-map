<?php

namespace Kamz\LaravelBRouter;

use Illuminate\Support\ServiceProvider;
use Kamz\LaravelBRouter\Contracts\DataProviderInterface;
use Kamz\LaravelBRouter\Contracts\ImportRepositoryInterface;
use Kamz\LaravelBRouter\Contracts\ImportDataProviderInterface;
use Kamz\LaravelBRouter\Console\PrecacheWaterwaysCommand;
use Kamz\LaravelBRouter\Contracts\RouterInterface;
use Kamz\LaravelBRouter\Services\BrouterImportRepository;
use Kamz\LaravelBRouter\Services\OverpassDataProvider;
use Kamz\LaravelBRouter\Services\PublishedImportDataProvider;
use Kamz\LaravelBRouter\Services\RoutingEngine;

class BRouterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/brouter.php', 'brouter'
        );

        $this->app->singleton(DataProviderInterface::class, PublishedImportDataProvider::class);
        $this->app->singleton(ImportDataProviderInterface::class, OverpassDataProvider::class);
        $this->app->singleton(ImportRepositoryInterface::class, BrouterImportRepository::class);
        $this->app->singleton(RouterInterface::class, RoutingEngine::class);

        $this->app->alias(RouterInterface::class, 'brouter');
    }

    public function boot(): void
    {
        $this->commands([PrecacheWaterwaysCommand::class]);
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publish config
        $this->publishes([
            __DIR__.'/../config/brouter.php' => config_path('brouter.php'),
        ], 'brouter-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'brouter-migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Load views if needed
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'brouter');
    }
}
