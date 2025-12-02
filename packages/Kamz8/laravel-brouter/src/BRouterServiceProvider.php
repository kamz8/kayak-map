<?php

namespace Kamz\LaravelBRouter;

use Illuminate\Support\ServiceProvider;
use Kamz\LaravelBRouter\Services\RoutingEngine;
use Kamz\LaravelBRouter\Contracts\RouterInterface;

class BRouterServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/brouter.php', 'brouter'
        );

        // Register main service
        $this->app->singleton(RouterInterface::class, function ($app) {
            return new RoutingEngine(
                config('brouter.cache_duration'),
                config('brouter.max_snap_distance')
            );
        });

        // Register facade
        $this->app->alias(RouterInterface::class, 'brouter');
    }

    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/brouter.php' => config_path('brouter.php'),
        ], 'brouter-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'brouter-migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        // Load views if needed
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'brouter');
    }
}
