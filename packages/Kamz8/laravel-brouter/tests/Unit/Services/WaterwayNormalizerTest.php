<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Services;

use Kamz\LaravelBRouter\Services\WaterwayNormalizer;
use Kamz\LaravelBRouter\Tests\TestCase;

class WaterwayNormalizerTest extends TestCase
{
    /** @test */
    public function it_normalizes_overpass_way_geometry_into_nodes_and_edges(): void
    {
        $data = [
            'elements' => [[
                'type' => 'way',
                'id' => 10,
                'tags' => ['name' => 'Wda', 'waterway' => 'river'],
                'geometry' => [
                    ['lat' => 53.1, 'lon' => 18.1],
                    ['lat' => 53.2, 'lon' => 18.2],
                    ['lat' => 53.3, 'lon' => 18.3],
                ],
            ]],
        ];

        $normalized = (new WaterwayNormalizer())->normalize($data, 'Wda');

        $this->assertCount(3, $normalized['nodes']);
        $this->assertCount(2, $normalized['edges']);
        $this->assertSame('way:10:0', $normalized['nodes'][0]['id']);
        $this->assertSame('edge:10:0', $normalized['edges'][0]['id']);
        $this->assertSame('way:10:0', $normalized['edges'][0]['from_node']);
        $this->assertSame('way:10:1', $normalized['edges'][0]['to_node']);
        $this->assertSame('Wda', $normalized['edges'][0]['river_name']);
        $this->assertGreaterThan(0, $normalized['edges'][0]['distance_m']);
    }
}
