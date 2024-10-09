<?php

namespace Kamz8\LaravelOverpass\Providers;

use Illuminate\Support\ServiceProvider;
use Kamz8\LaravelOverpass\Overpass;

/**
 * Class OverpassServiceProvider
 *
 * This service provider bootstraps the Overpass package.
 *
 * @package Kamz8\LaravelOverpass\Providers
 */
class OverpassServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        $configPath = __DIR__ . '/../../config/overpass.php';
        $publishPath = config_path('overpass.php');

        $this->publishes([
            $configPath => $publishPath,
        ], 'overpass-config');

    }

    /**
     * Register bindings in the container.
     *
     * @return void
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
            return new Overpass($app['config']['overpass']);
        });
    }
}
