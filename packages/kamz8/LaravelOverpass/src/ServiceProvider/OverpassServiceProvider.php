<?php

namespace kamz8\LaravelOverpass;

use Illuminate\Support\ServiceProvider;

class OverpassServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Public config
        $this->publishes([
            __DIR__ . '/../config/overpass.php' => config_path('overpass.php'),
        ], 'config');
    }

    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__ . '/../config/overpass.php', 'overpass');

        $this->app->singleton('overpass', function ($app) {
            return new Overpass();
        });
    }
}
