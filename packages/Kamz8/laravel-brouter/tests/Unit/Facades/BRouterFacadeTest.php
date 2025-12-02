<?php
// tests/Unit/Facades/BRouterFacadeTest.php

namespace Kamz\LaravelBRouter\Tests\Unit\Facades;

use Tests\TestCase;
use Kamz\LaravelBRouter\Facades\BRouter;
use Kamz\LaravelBRouter\Contracts\RouterInterface;

class BRouterFacadeTest extends TestCase
{
    /** @test */
    public function facade_returns_correct_instance()
    {
        $instance = BRouter::getFacadeRoot();

        $this->assertInstanceOf(RouterInterface::class, $instance);
    }

    /** @test */
    public function facade_proxy_method_calls()
    {
        BRouter::shouldReceive('findRoute')
            ->once()
            ->with([16.989449, 51.136986], [16.977333, 51.144048], 'river')
            ->andReturn(['test' => 'data']);

        $result = BRouter::findRoute(
            [16.989449, 51.136986],
            [16.977333, 51.144048],
            'river'
        );

        $this->assertEquals(['test' => 'data'], $result);
    }
}
