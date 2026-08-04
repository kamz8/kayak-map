<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Services;

use Kamz\LaravelBRouter\Contracts\DataProviderInterface;
use Kamz\LaravelBRouter\DTO\RouteRequestData;
use Kamz\LaravelBRouter\Models\RouteResult;
use Kamz\LaravelBRouter\Services\EdgeSnapper;
use Kamz\LaravelBRouter\Services\GraphRouter;
use Kamz\LaravelBRouter\Services\RouteCache;
use Kamz\LaravelBRouter\Services\RoutingEngine;
use Kamz\LaravelBRouter\Services\WaterwayGraphBuilder;
use Kamz\LaravelBRouter\Services\WaterwayNormalizer;
use Kamz\LaravelBRouter\Tests\TestCase;

class RoutingEngineTest extends TestCase
{
    /** @test */
    public function it_builds_route_result_from_provider_waterway_data(): void
    {
        config()->set('brouter.cache.enabled', false);
        config()->set('brouter.routing.bbox_buffer_km', 1);

        $engine = new RoutingEngine(
            new class implements DataProviderInterface
            {
                public array $bbox = [];

                public function getWaterwaysInBBox(array $bbox): array
                {
                    return [];
                }

                public function getWaterwayByName(string $name, ?array $bbox = null): array
                {
                    $this->bbox = $bbox ?? [];

                    return [
                        'elements' => [[
                            'type' => 'way',
                            'id' => 10,
                            'tags' => ['waterway' => 'river', 'name' => $name],
                            'geometry' => [
                                ['lat' => 0.0, 'lon' => 0.0],
                                ['lat' => 0.0, 'lon' => 1.0],
                                ['lat' => 0.0, 'lon' => 2.0],
                            ],
                        ]],
                    ];
                }
            },
            new RouteCache(),
            new WaterwayNormalizer(),
            new WaterwayGraphBuilder(),
            new EdgeSnapper(),
            new GraphRouter(),
        );

        $result = $engine->findRoute(new RouteRequestData(
            riverName: 'Test River',
            start: ['lat' => 0.0, 'lng' => 0.0],
            end: ['lat' => 0.0, 'lng' => 2.0],
            snapToleranceMeters: 100,
        ));

        $this->assertInstanceOf(RouteResult::class, $result);
        $this->assertSame([[0.0, 0.0], [1.0, 0.0], [2.0, 0.0]], $result->path);
        $this->assertSame('versioned', $result->cache['route']);
        $this->assertSame('runtime', $result->cache['graph']);
        $this->assertGreaterThan(0, $result->distanceMeters);
    }
}
