<?php
// tests/Unit/BRouterServiceProviderTest.php

namespace Kamz\LaravelBRouter\Tests\Unit;

use Kamz\LaravelBRouter\Contracts\DataProviderInterface;
use Kamz\LaravelBRouter\Contracts\RouterInterface;
use Kamz\LaravelBRouter\Facades\BRouter;
use Kamz\LaravelBRouter\Services\OverpassDataProvider;
use Kamz\LaravelBRouter\Services\RoutingEngine;
use Kamz\LaravelBRouter\Tests\TestCase;

class BRouterServiceProviderTest extends TestCase
{
    /** @test */
    public function service_is_registered_in_container()
    {
        $this->assertTrue($this->app->bound(RouterInterface::class));

        $service = $this->app->make(RouterInterface::class);
        $this->assertInstanceOf(RoutingEngine::class, $service);
    }

    /** @test */
    public function data_provider_is_registered_in_container()
    {
        $this->assertTrue($this->app->bound(DataProviderInterface::class));

        $provider = $this->app->make(DataProviderInterface::class);
        $this->assertInstanceOf(OverpassDataProvider::class, $provider);
    }

    /** @test */
    public function facade_is_registered_correctly()
    {
        $this->assertInstanceOf(RouterInterface::class, BRouter::getFacadeRoot());
    }

    /** @test */
    public function config_is_merged_correctly()
    {
        $config = config('brouter');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('default_profile', $config);
        $this->assertArrayHasKey('cache', $config);
        $this->assertArrayHasKey('routing', $config);
        $this->assertArrayHasKey('overpass', $config);
        $this->assertEquals('river', $config['default_profile']);
        $this->assertSame('redis', $config['cache']['store']);
        $this->assertSame(500, $config['routing']['max_snap_distance']);
    }
}
