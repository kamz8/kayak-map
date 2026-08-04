<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Services;

use Kamz\LaravelBRouter\Services\WaterwayGraphBuilder;
use Kamz\LaravelBRouter\Tests\TestCase;

class WaterwayGraphBuilderTest extends TestCase
{
    /** @test */
    public function it_assigns_connected_component_ids_to_edges(): void
    {
        $payload = (new WaterwayGraphBuilder())->build([
            'nodes' => [
                ['id' => 'a', 'lat' => 0.0, 'lng' => 0.0],
                ['id' => 'b', 'lat' => 0.0, 'lng' => 1.0],
                ['id' => 'c', 'lat' => 10.0, 'lng' => 10.0],
                ['id' => 'd', 'lat' => 10.0, 'lng' => 11.0],
            ],
            'edges' => [
                ['id' => 'ab', 'from_node' => 'a', 'to_node' => 'b', 'geometry' => [], 'distance_m' => 1.0],
                ['id' => 'cd', 'from_node' => 'c', 'to_node' => 'd', 'geometry' => [], 'distance_m' => 1.0],
            ],
        ]);

        $this->assertSame(2, $payload['components']['component_count']);
        $this->assertNotSame($payload['edges']['ab']['component_id'], $payload['edges']['cd']['component_id']);
    }
}
