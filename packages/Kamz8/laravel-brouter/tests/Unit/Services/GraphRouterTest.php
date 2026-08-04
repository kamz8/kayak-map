<?php

namespace Kamz\LaravelBRouter\Tests\Unit\Services;

use Kamz\LaravelBRouter\DTO\SnapResultData;
use Kamz\LaravelBRouter\Exceptions\DisconnectedWaterwayException;
use Kamz\LaravelBRouter\Services\GraphRouter;
use Kamz\LaravelBRouter\Services\WaterwayGraphBuilder;
use Kamz\LaravelBRouter\Tests\TestCase;

class GraphRouterTest extends TestCase
{
    /** @test */
    public function it_routes_between_snapped_edges_on_connected_graph(): void
    {
        $payload = (new WaterwayGraphBuilder())->build($this->connectedNetwork());

        $path = (new GraphRouter())->route(
            $payload,
            new SnapResultData(['lat' => 0, 'lng' => 0], ['lat' => 0, 'lng' => 0], 0, 'e1'),
            new SnapResultData(['lat' => 0, 'lng' => 2], ['lat' => 0, 'lng' => 2], 0, 'e2'),
        );

        $this->assertSame([[0.0, 0.0], [1.0, 0.0], [2.0, 0.0]], $path['coordinates']);
        $this->assertSame(200.0, $path['distance_m']);
    }

    /** @test */
    public function it_routes_between_virtual_snap_points_on_the_same_edge(): void
    {
        $payload = (new WaterwayGraphBuilder())->build([
            'nodes' => [
                ['id' => 'n1', 'lat' => 0.0, 'lng' => 0.0, 'virtual' => false],
                ['id' => 'n2', 'lat' => 0.0, 'lng' => 10.0, 'virtual' => false],
            ],
            'edges' => [
                ['id' => 'e1', 'from_node' => 'n1', 'to_node' => 'n2', 'geometry' => [['lat' => 0.0, 'lng' => 0.0], ['lat' => 0.0, 'lng' => 10.0]], 'distance_m' => 1000.0],
            ],
        ]);

        $route = (new GraphRouter())->route(
            $payload,
            new SnapResultData(['lat' => 0, 'lng' => 2], ['lat' => 0, 'lng' => 2], 0, 'e1', 0.2, 200.0, 'n1', 'n2'),
            new SnapResultData(['lat' => 0, 'lng' => 7], ['lat' => 0, 'lng' => 7], 0, 'e1', 0.7, 700.0, 'n1', 'n2'),
        );

        $this->assertSame([[2, 0], [7, 0]], $route['coordinates']);
        $this->assertEqualsWithDelta(500.0, $route['distance_m'], 0.0001);
    }

    /** @test */
    public function it_throws_for_disconnected_graph(): void
    {
        $this->expectException(DisconnectedWaterwayException::class);

        $payload = (new WaterwayGraphBuilder())->build($this->disconnectedNetwork());

        (new GraphRouter())->route(
            $payload,
            new SnapResultData(['lat' => 0, 'lng' => 0], ['lat' => 0, 'lng' => 0], 0, 'e1'),
            new SnapResultData(['lat' => 10, 'lng' => 11], ['lat' => 10, 'lng' => 11], 0, 'e2'),
        );
    }

    private function connectedNetwork(): array
    {
        return [
            'nodes' => [
                ['id' => 'n1', 'lat' => 0.0, 'lng' => 0.0, 'virtual' => false],
                ['id' => 'n2', 'lat' => 0.0, 'lng' => 1.0, 'virtual' => false],
                ['id' => 'n3', 'lat' => 0.0, 'lng' => 2.0, 'virtual' => false],
            ],
            'edges' => [
                ['id' => 'e1', 'from_node' => 'n1', 'to_node' => 'n2', 'geometry' => [['lat' => 0.0, 'lng' => 0.0], ['lat' => 0.0, 'lng' => 1.0]], 'distance_m' => 100.0],
                ['id' => 'e2', 'from_node' => 'n2', 'to_node' => 'n3', 'geometry' => [['lat' => 0.0, 'lng' => 1.0], ['lat' => 0.0, 'lng' => 2.0]], 'distance_m' => 100.0],
            ],
        ];
    }

    private function disconnectedNetwork(): array
    {
        return [
            'nodes' => [
                ['id' => 'n1', 'lat' => 0.0, 'lng' => 0.0, 'virtual' => false],
                ['id' => 'n2', 'lat' => 0.0, 'lng' => 1.0, 'virtual' => false],
                ['id' => 'n3', 'lat' => 10.0, 'lng' => 10.0, 'virtual' => false],
                ['id' => 'n4', 'lat' => 10.0, 'lng' => 11.0, 'virtual' => false],
            ],
            'edges' => [
                ['id' => 'e1', 'from_node' => 'n1', 'to_node' => 'n2', 'geometry' => [['lat' => 0.0, 'lng' => 0.0], ['lat' => 0.0, 'lng' => 1.0]], 'distance_m' => 100.0],
                ['id' => 'e2', 'from_node' => 'n3', 'to_node' => 'n4', 'geometry' => [['lat' => 10.0, 'lng' => 10.0], ['lat' => 10.0, 'lng' => 11.0]], 'distance_m' => 100.0],
            ],
        ];
    }
}
