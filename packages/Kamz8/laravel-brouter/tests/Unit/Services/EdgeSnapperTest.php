<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Services;

use Kamz\LaravelBRouter\Exceptions\SnapDistanceExceededException;
use Kamz\LaravelBRouter\Services\EdgeSnapper;
use Kamz\LaravelBRouter\Tests\TestCase;

class EdgeSnapperTest extends TestCase
{
    /** @test */
    public function it_snaps_point_to_nearest_edge_segment(): void
    {
        $edges = [
            'edge-1' => [
                'id' => 'edge-1',
                'from_node' => 'n1',
                'to_node' => 'n2',
                'geometry' => [
                    ['lat' => 0.0, 'lng' => 0.0],
                    ['lat' => 0.0, 'lng' => 1.0],
                ],
                'distance_m' => 100.0,
            ],
        ];

        $snap = (new EdgeSnapper())->snap(['lat' => 0.1, 'lng' => 0.5], $edges, 20000);

        $this->assertSame('edge-1', $snap->edgeId);
        $this->assertEqualsWithDelta(0.0, $snap->snapped['lat'], 0.0001);
        $this->assertEqualsWithDelta(0.5, $snap->snapped['lng'], 0.0001);
        $this->assertEqualsWithDelta(0.5, $snap->position, 0.0001);
        $this->assertSame('n1', $snap->fromNodeId);
        $this->assertSame('n2', $snap->toNodeId);
        $this->assertGreaterThan(0, $snap->distanceMeters);
    }

    /** @test */
    public function it_rejects_point_outside_snap_tolerance(): void
    {
        $this->expectException(SnapDistanceExceededException::class);

        (new EdgeSnapper())->snap(['lat' => 10.0, 'lng' => 10.0], [
            'edge-1' => [
                'id' => 'edge-1',
                'from_node' => 'n1',
                'to_node' => 'n2',
                'geometry' => [
                    ['lat' => 0.0, 'lng' => 0.0],
                    ['lat' => 0.0, 'lng' => 1.0],
                ],
                'distance_m' => 100.0,
            ],
        ], 10);
    }
}
