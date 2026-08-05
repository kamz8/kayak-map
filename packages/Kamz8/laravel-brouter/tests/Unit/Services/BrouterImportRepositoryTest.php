<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Services;

use Illuminate\Support\Facades\DB;
use Kamz\LaravelBRouter\Services\BrouterImportRepository;
use Kamz\LaravelBRouter\Tests\TestCase;

class BrouterImportRepositoryTest extends TestCase
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
    public function it_persists_pg_routing_edge_columns(): void
    {
        $repository = new BrouterImportRepository();
        $importId = $repository->create([
            'south' => 51.0,
            'west' => 16.0,
            'north' => 51.1,
            'east' => 16.1,
        ], ['name' => 'Test river']);

        try {
            $repository->persist($importId, [
                'nodes' => [
                    ['id' => 'node-a', 'osm_id' => 'a', 'lat' => 51.0, 'lng' => 16.0, 'source_tags' => []],
                    ['id' => 'node-b', 'osm_id' => 'b', 'lat' => 51.1, 'lng' => 16.1, 'source_tags' => []],
                ],
                'edges' => [[
                    'from_node' => 'node-a',
                    'to_node' => 'node-b',
                    'way_id' => 'way-1',
                    'distance_m' => 1000.0,
                    'geometry' => [
                        ['lat' => 51.0, 'lon' => 16.0],
                        ['lat' => 51.1, 'lon' => 16.1],
                    ],
                    'is_bidirectional' => true,
                    'is_water_body_crossing' => false,
                    'source_tags' => [],
                ]],
                'waterways' => [],
                'features' => [],
                'water_bodies' => [],
            ], []);

            $edge = DB::connection('brouter')->table('waterway_edges')->where('import_id', $importId)->first();

            $this->assertSame(1000.0, (float) $edge->cost);
            $this->assertSame(1000.0, (float) $edge->reverse_cost);
            $this->assertNotNull($edge->source);
            $this->assertNotNull($edge->target);
        } finally {
            DB::connection('brouter')->table('imports')->where('id', $importId)->delete();
        }
    }
}
