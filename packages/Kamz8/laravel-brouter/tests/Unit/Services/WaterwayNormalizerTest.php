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

    /** @test */
    public function it_normalizes_relations_nodes_features_water_bodies_and_all_tags(): void
    {
        $tags = [
            'waterway' => 'waterfall',
            'name' => 'Hidden Falls',
            'operator' => 'OSM',
        ];
        $data = [
            'elements' => [
                ['type' => 'node', 'id' => 7, 'lat' => 53.1, 'lon' => 18.1, 'tags' => ['barrier' => 'dam', 'custom' => 'yes']],
                ['type' => 'relation', 'id' => 20, 'tags' => $tags, 'members' => [[
                    'type' => 'way',
                    'geometry' => [
                        ['lat' => 53.1, 'lon' => 18.1],
                        ['lat' => 53.2, 'lon' => 18.2],
                    ],
                ]]],
                ['type' => 'way', 'id' => 30, 'tags' => ['natural' => 'water', 'water' => 'lake'], 'geometry' => [
                    ['lat' => 53.0, 'lon' => 18.0],
                    ['lat' => 53.0, 'lon' => 18.1],
                    ['lat' => 53.1, 'lon' => 18.1],
                    ['lat' => 53.0, 'lon' => 18.0],
                ]],
            ],
        ];

        $normalized = (new WaterwayNormalizer())->normalize($data);

        $this->assertSame($tags, $normalized['waterways'][0]['source_tags']);
        $this->assertSame(['barrier' => 'dam', 'custom' => 'yes'], $normalized['features'][0]['source_tags']);
        $this->assertSame($tags, $normalized['features'][1]['source_tags']);
        $this->assertSame('lake', $normalized['water_bodies'][0]['water_type']);
        $this->assertCount(1, $normalized['water_bodies']);
    }
}
