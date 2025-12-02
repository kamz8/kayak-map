<?php
// tests/TestCase.php

namespace Kamz\LaravelBRouter\Tests;

use Kamz\LaravelBRouter\BRouterServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            BRouterServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'BRouter' => \Kamz\LaravelBRouter\Facades\BRouter::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Konfiguracja środowiska testowego
        $app['config']->set('brouter.default_profile', 'river');
        $app['config']->set('brouter.cache.enabled', false);

        // Konfiguracja bazy danych
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
