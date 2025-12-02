<?php
// tests/Unit/BRouterServiceProviderTest.php

namespace Kamz\LaravelBRouter\Tests\Unit;

use Tests\TestCase;
use Kamz\LaravelBRouter\BRouterServiceProvider;
use Kamz\LaravelBRouter\Contracts\RouterInterface;
use Kamz\LaravelBRouter\Facades\BRouter;
use Illuminate\Foundation\Application;

class BRouterServiceProviderTest extends TestCase
{
    /** @test */
    public function service_provider_is_loaded_correctly()
    {
        $provider = new BRouterServiceProvider($this->app);

        $this->assertInstanceOf(BRouterServiceProvider::class, $provider);
    }

    /** @test */
    public function service_is_registered_in_container()
    {
        $this->assertTrue($this->app->bound(RouterInterface::class));

        $service = $this->app->make(RouterInterface::class);
        $this->assertInstanceOf(RouterInterface::class, $service);
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
        $this->assertEquals('river', $config['default_profile']);
    }
}
