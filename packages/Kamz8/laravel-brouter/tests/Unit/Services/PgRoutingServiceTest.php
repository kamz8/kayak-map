<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Services;

use Illuminate\Support\Facades\DB;
use Kamz\LaravelBRouter\DTO\RouteRequestData;
use Kamz\LaravelBRouter\Services\BrouterImportRepository;
use Kamz\LaravelBRouter\Services\PgRoutingService;
use Kamz\LaravelBRouter\Tests\TestCase;

class PgRoutingServiceTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.connections.brouter', [
            'driver' => 'pgsql',
            'host' => getenv('BROUTER_DB_HOST') ?: 'brouter-db',
            'port' => getenv('BROUTER_DB_PORT') ?: 5432,
            'database' => getenv('BROUTER_DB_DATABASE') ?: 'brouter',
            'username' => getenv('BROUTER_DB_USERNAME') ?: 'brouter',
            'password' => getenv('BROUTER_DB_PASSWORD') ?: 'brouter',
            'charset' => 'utf8',
            'prefix' => '',
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ]);
    }

    /** @test */
    public function it_uses_pgrouting_astar_without_php_graph_routing(): void
    {
        $source = file_get_contents((new \ReflectionClass(PgRoutingService::class))->getFileName());

        $this->assertStringContainsString('pgr_aStar', $source);
        $this->assertStringNotContainsString('pgr_dijkstra', strtolower($source));
    }

    /** @test */
    public function it_routes_a_postgis_graph_with_astar(): void
    {
        $repository = new BrouterImportRepository();
        $importId = $repository->create([
            'south' => 51.0,
            'west' => 16.0,
            'north' => 51.2,
            'east' => 16.2,
        ], ['name' => 'A* test river']);

        try {
            $repository->persist($importId, [
                'nodes' => [
                    ['id' => 'node-a', 'osm_id' => 'a', 'lat' => 51.0, 'lng' => 16.0, 'source_tags' => []],
                    ['id' => 'node-b', 'osm_id' => 'b', 'lat' => 51.1, 'lng' => 16.1, 'source_tags' => []],
                    ['id' => 'node-c', 'osm_id' => 'c', 'lat' => 51.2, 'lng' => 16.2, 'source_tags' => []],
                ],
                'edges' => [
                    $this->edge('node-a', 'node-b', 'way-1', 1000.0, 51.0, 16.0, 51.1, 16.1),
                    $this->edge('node-b', 'node-c', 'way-2', 1000.0, 51.1, 16.1, 51.2, 16.2),
                ],
                'waterways' => [],
                'features' => [],
                'water_bodies' => [],
            ], []);

            $result = (new PgRoutingService())->route(new RouteRequestData(
                riverName: 'A* test river',
                start: ['lat' => 51.0, 'lng' => 16.0],
                end: ['lat' => 51.2, 'lng' => 16.2],
                snapToleranceMeters: 1000,
            ), $importId);

            $this->assertSame([[16.0, 51.0], [16.1, 51.1], [16.2, 51.2]], $result->path);
            $this->assertSame(2000.0, $result->distanceMeters);
            $this->assertSame($importId, $result->importId);
        } finally {
            DB::connection('brouter')->table('imports')->where('id', $importId)->delete();
        }
    }

    private function edge(string $from, string $to, string $wayId, float $distance, float $startLat, float $startLng, float $endLat, float $endLng): array
    {
        return [
            'from_node' => $from,
            'to_node' => $to,
            'way_id' => $wayId,
            'distance_m' => $distance,
            'geometry' => [
                ['lat' => $startLat, 'lon' => $startLng],
                ['lat' => $endLat, 'lon' => $endLng],
            ],
            'is_bidirectional' => true,
            'is_water_body_crossing' => false,
            'source_tags' => [],
        ];
    }
}
