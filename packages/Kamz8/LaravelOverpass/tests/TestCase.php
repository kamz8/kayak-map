<?php

namespace Kamz8\LaravelOverpass\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Kamz8\LaravelOverpass\Providers\OverpassServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            OverpassServiceProvider::class,
        ];
    }
}
