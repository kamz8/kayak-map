<?php

namespace Kamz8\LaravelOverpass\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * Define environment setup for testing.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        // Set up any specific configuration for testing here
        $app['config']->set('overpass.throttle', true);
        $app['config']->set('overpass.throttle_limit', 2);
    }

    /**
     * Get package providers for testing.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            \Kamz8\LaravelOverpass\Providers\OverpassServiceProvider::class,
        ];
    }

    /**
     * Get package aliases for testing.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Overpass' => \Kamz8\LaravelOverpass\Facades\Overpass::class,
        ];
    }
}
