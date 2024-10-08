<?php

namespace Kamz8\LaravelOverpass;

use Illuminate\Support\ServiceProvider;

class OverpassServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/overpass.php' => config_path('overpass.php'),
        ], 'overpass-config');
    }

    public function register(): void
    {
        // Merge configurations
        $this->mergeConfigFrom(__DIR__.'/../config/overpass.php', 'overpass');

        // Register the service
        $this->app->singleton('overpass', function ($app) {
            return new Overpass();
        });
    }
}
