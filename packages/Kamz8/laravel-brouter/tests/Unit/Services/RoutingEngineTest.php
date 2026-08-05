<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Services;

use Kamz\LaravelBRouter\Contracts\DataProviderInterface;
use Kamz\LaravelBRouter\DTO\PgRouteResultData;
use Kamz\LaravelBRouter\DTO\RouteRequestData;
use Kamz\LaravelBRouter\Models\RouteResult;
use Kamz\LaravelBRouter\Services\BrouterGraphRepository;
use Kamz\LaravelBRouter\Services\EdgeSnapper;
use Kamz\LaravelBRouter\Services\GraphRouter;
use Kamz\LaravelBRouter\Services\PgRoutingService;
use Kamz\LaravelBRouter\Services\RouteCache;
use Kamz\LaravelBRouter\Services\RiverMicroGraphService;
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

    /** @test */
    public function it_delegates_published_route_execution_to_postgres_routing(): void
    {
        $repository = new class extends BrouterGraphRepository
        {
            public function activeGraphVersion(?array $bbox = null): ?string
            {
                return 'graph:postgres';
            }

            public function activeGraphImportId(?array $bbox = null): ?int
            {
                return 42;
            }

            public function activeGraphVersionForRiver(string $riverName, ?array $bbox = null): ?string
            {
                return 'graph:postgres';
            }

            public function activeGraphImportIdForRiver(string $riverName, ?array $bbox = null): ?int
            {
                return 42;
            }
        };
        $routing = new class extends PgRoutingService
        {
            public ?RouteRequestData $request = null;
            public ?int $importId = null;

            public function route(RouteRequestData $request, int $importId): PgRouteResultData
            {
                $this->request = $request;
                $this->importId = $importId;

                return new PgRouteResultData(
                    path: [[16.0, 51.0], [16.1, 51.1]],
                    startSnap: ['distance_m' => 1.0],
                    endSnap: ['distance_m' => 2.0],
                    distanceMeters: 1000.0,
                    importId: $importId,
                    cache: ['engine' => 'pgrouting', 'algorithm' => 'astar'],
                );
            }
        };

        $engine = new RoutingEngine(
            new class implements DataProviderInterface
            {
                public function getWaterwaysInBBox(array $bbox): array { return []; }
                public function getWaterwayByName(string $name, ?array $bbox = null): array { return []; }
            },
            new RouteCache(),
            new WaterwayNormalizer(),
            new WaterwayGraphBuilder(),
            new EdgeSnapper(),
            new GraphRouter(),
            graphRepository: $repository,
            pgRouting: $routing,
        );

        $result = $engine->findRoute(new RouteRequestData(
            riverName: 'Odra',
            start: ['lat' => 51.0, 'lng' => 16.0],
            end: ['lat' => 51.1, 'lng' => 16.1],
        ));

        $this->assertInstanceOf(RouteResult::class, $result);
        $this->assertSame([[16.0, 51.0], [16.1, 51.1]], $result->path);
        $this->assertSame('pgrouting', $result->cache['engine']);
        $this->assertSame(42, $routing->importId);
    }

    /** @test */
    public function it_routes_a_missing_river_from_a_temporary_micrograph_and_queues_indexing(): void
    {
        $repository = new class extends BrouterGraphRepository
        {
            public function activeGraphVersionForRiver(string $riverName, ?array $bbox = null): ?string { return null; }
            public function activeGraphImportIdForRiver(string $riverName, ?array $bbox = null): ?int { return null; }
            public function activeGraphVersion(?array $bbox = null): ?string { return null; }
            public function activeGraphImportId(?array $bbox = null): ?int { return null; }
        };
        $microGraphs = new class extends RiverMicroGraphService
        {
            public bool $queued = false;

            public function findPublishedTile(string $riverKey, array $start, array $end): ?object
            {
                return null;
            }

            public function ensureTemporaryTile(string $riverKey, array $bbox, array $metadata = []): object
            {
                return (object) ['import_id' => 99, 'version' => 'temporary:99'];
            }

            public function queueIndexing(object $temporaryTile): void
            {
                $this->queued = $temporaryTile->import_id === 99;
            }
        };
        $routing = new class extends PgRoutingService
        {
            public function route(RouteRequestData $request, int $importId): PgRouteResultData
            {
                return new PgRouteResultData(
                    path: [[16.0, 51.0], [16.1, 51.1]],
                    startSnap: [],
                    endSnap: [],
                    distanceMeters: 1000.0,
                    importId: $importId,
                );
            }
        };

        $engine = new RoutingEngine(
            new class implements DataProviderInterface
            {
                public function getWaterwaysInBBox(array $bbox): array { return []; }
                public function getWaterwayByName(string $name, ?array $bbox = null): array { return []; }
            },
            new RouteCache(),
            new WaterwayNormalizer(),
            new WaterwayGraphBuilder(),
            new EdgeSnapper(),
            new GraphRouter(),
            graphRepository: $repository,
            pgRouting: $routing,
            microGraphs: $microGraphs,
        );

        $result = $engine->findRoute(new RouteRequestData(
            riverName: 'Lithuanian river',
            start: ['lat' => 51.0, 'lng' => 16.0],
            end: ['lat' => 51.1, 'lng' => 16.1],
        ));

        $this->assertSame('temporary:99', $result->cache['graph']);
        $this->assertTrue($microGraphs->queued);
        $this->assertSame('queued', $result->cache['indexing']['status']);
    }
}
