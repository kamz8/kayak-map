<?php

namespace Kamz8\LaravelOverpass\Providers;

use Illuminate\Support\ServiceProvider;
use Kamz8\LaravelOverpass\Overpass;

class OverpassServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        // Publikacja pliku konfiguracyjnego
        $this->publishes([
            __DIR__ . '/../../config/overpass.php' => config_path('overpass.php'),
        ], 'overpass-config');
    }

    /**
     * Register bindings in the container.
     */
    public function register(): void
    {
        // Łączenie konfiguracji pakietu z aplikacją
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/overpass.php',
            'overpass'
        );

        // Rejestracja singletonu dla Overpass
        $this->app->singleton('overpass', function ($app) {
            return new Overpass();
        });
    }
}
