<?php

// tests/Unit/Console/BRouterServiceProviderTest.php

namespace Kamz\LaravelBRouter\Tests\Unit\Console;

use Kamz\LaravelBRouter\Contracts\RouterInterface;
use Kamz\LaravelBRouter\Tests\TestCase;

class BRouterServiceProviderTest extends TestCase
{
    /** @test */
    public function it_registers_router_service()
    {
        $this->assertTrue($this->app->bound(RouterInterface::class));

        $service = $this->app->make(RouterInterface::class);
        $this->assertInstanceOf(RouterInterface::class, $service);
    }

    /** @test */
    public function it_registers_facade()
    {
        $this->assertTrue(class_exists('BRouter'));

        $instance = \BRouter::getFacadeRoot();
        $this->assertInstanceOf(RouterInterface::class, $instance);
    }

    /** @test */
    public function it_merges_configuration()
    {
        $config = config('brouter');

        $this->assertIsArray($config);
        $this->assertEquals('river', $config['default_profile']);
        $this->assertFalse($config['cache']['enabled']);
    }
}
