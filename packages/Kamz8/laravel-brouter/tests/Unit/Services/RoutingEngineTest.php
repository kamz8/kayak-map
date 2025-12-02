<?php
// tests/Unit/Services/RoutingEngineTest.php

namespace Kamz\LaravelBRouter\Tests\Unit\Services;

use Kamz\LaravelBRouter\Tests\TestCase;
use Kamz\LaravelBRouter\Services\RoutingEngine;
use Kamz\LaravelBRouter\Contracts\DataProviderInterface;
use Mockery;

class RoutingEngineTest extends TestCase
{
    private $mockDataProvider;
    private $routingEngine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockDataProvider = Mockery::mock(DataProviderInterface::class);
        $this->routingEngine = new RoutingEngine($this->mockDataProvider);
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(RoutingEngine::class, $this->routingEngine);
    }

    /** @test */
    public function it_returns_array_from_find_route()
    {
        $start = [16.989449, 51.136986];
        $end = [16.977333, 51.144048];

        $result = $this->routingEngine->findRoute($start, $end);

        $this->assertIsArray($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
