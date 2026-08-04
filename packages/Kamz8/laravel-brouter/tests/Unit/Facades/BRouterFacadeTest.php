<?php

// tests/Unit/Facades/BRouterFacadeTest.php

namespace Kamz\LaravelBRouter\Tests\Unit\Facades;

use Kamz\LaravelBRouter\Contracts\RouterInterface;
use Kamz\LaravelBRouter\DTO\RouteRequestData;
use Kamz\LaravelBRouter\Facades\BRouter;
use Kamz\LaravelBRouter\Models\RouteResult;
use Tests\TestCase;

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
            ->withArgs(fn (RouteRequestData $request): bool => $request->riverName === 'river')
            ->andReturn(new RouteResult(path: [[16.989449, 51.136986]]));

        $result = BRouter::findRoute(new RouteRequestData(
            riverName: 'river',
            start: ['lat' => 51.136986, 'lng' => 16.989449],
            end: ['lat' => 51.144048, 'lng' => 16.977333],
        ));

        $this->assertInstanceOf(RouteResult::class, $result);
        $this->assertSame([[16.989449, 51.136986]], $result->path);
    }
}
