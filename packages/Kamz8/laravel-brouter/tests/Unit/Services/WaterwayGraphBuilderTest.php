<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Services;

use Fhaculty\Graph\Graph;
use Kamz\LaravelBRouter\Services\WaterwayGraphBuilder;
use Kamz\LaravelBRouter\Tests\TestCase;

class WaterwayGraphBuilderTest extends TestCase
{
    /** @test */
    public function it_builds_graph_from_normalized_nodes_and_edges(): void
    {
        $normalized = [
            'nodes' => [
                ['id' => 'n1', 'lat' => 53.1, 'lng' => 18.1, 'virtual' => false],
                ['id' => 'n2', 'lat' => 53.2, 'lng' => 18.2, 'virtual' => false],
                ['id' => 'n3', 'lat' => 53.3, 'lng' => 18.3, 'virtual' => false],
            ],
            'edges' => [
                ['id' => 'e1', 'from_node' => 'n1', 'to_node' => 'n2', 'geometry' => [['lat' => 53.1, 'lng' => 18.1], ['lat' => 53.2, 'lng' => 18.2]], 'distance_m' => 100.0, 'way_id' => '10', 'river_name' => 'Wda', 'waterway' => 'river'],
                ['id' => 'e2', 'from_node' => 'n2', 'to_node' => 'n3', 'geometry' => [['lat' => 53.2, 'lng' => 18.2], ['lat' => 53.3, 'lng' => 18.3]], 'distance_m' => 120.0, 'way_id' => '10', 'river_name' => 'Wda', 'waterway' => 'river'],
            ],
        ];

        $payload = (new WaterwayGraphBuilder())->build($normalized);

        $this->assertInstanceOf(Graph::class, $payload['graph']);
        $this->assertCount(3, $payload['vertices']);
        $this->assertCount(2, $payload['edges']);
        $this->assertSame(100.0, $payload['edges']['e1']['distance_m']);
    }
}
