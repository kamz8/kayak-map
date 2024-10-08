<?php

namespace Kamz8\LaravelOverpass\Tests\Helpers;

use Kamz8\LaravelOverpass\Helpers\RouteHelper;
use Orchestra\Testbench\TestCase;

class RouteHelperTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Kamz8\LaravelOverpass\OverpassServiceProvider::class,
        ];
    }

    /** @test */
    public function it_finds_route_between_two_points()
    {
        $latA = 51.5;
        $lonA = -0.1;
        $latB = 51.6;
        $lonB = -0.1;

        $routeHelper = new RouteHelper();

        $data = $routeHelper->findRoute($latA, $lonA, $latB, $lonB);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('elements', $data);
        // Additional assertions based on expected route data
    }
}
