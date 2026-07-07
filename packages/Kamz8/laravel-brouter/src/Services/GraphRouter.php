<?php

namespace Kamz\LaravelBRouter\Services;

use Kamz\LaravelBRouter\DTO\SnapResultData;
use Kamz\LaravelBRouter\Exceptions\DisconnectedWaterwayException;

class GraphRouter
{
    public function route(array $payload, SnapResultData $start, SnapResultData $end): array
    {
        $startEdge = $payload['edges'][$start->edgeId];
        $endEdge = $payload['edges'][$end->edgeId];

        $startNode = $this->nearestEdgeNode($start, $startEdge, $payload['vertices']);
        $endNode = $this->nearestEdgeNode($end, $endEdge, $payload['vertices']);
        $result = $this->shortestPath($payload, $startNode, $endNode);

        if ($result === null) {
            throw new DisconnectedWaterwayException('No connected waterway path found.');
        }

        return $result;
    }

    private function shortestPath(array $payload, string $startNode, string $endNode): ?array
    {
        $distances = [$startNode => 0.0];
        $previous = [];
        $queue = [$startNode => 0.0];
        $adjacency = $this->adjacency($payload['edges']);

        while ($queue !== []) {
            asort($queue);
            $node = array_key_first($queue);
            unset($queue[$node]);

            if ($node === $endNode) {
                break;
            }

            foreach ($adjacency[$node] ?? [] as $neighbor => $edge) {
                $distance = $distances[$node] + $edge['distance_m'];

                if (! isset($distances[$neighbor]) || $distance < $distances[$neighbor]) {
                    $distances[$neighbor] = $distance;
                    $previous[$neighbor] = $node;
                    $queue[$neighbor] = $distance;
                }
            }
        }

        if (! isset($distances[$endNode])) {
            return null;
        }

        $nodes = $this->pathNodes($previous, $startNode, $endNode);

        return [
            'coordinates' => array_map(fn (string $node): array => [
                $payload['vertices'][$node]->getAttribute('lng'),
                $payload['vertices'][$node]->getAttribute('lat'),
            ], $nodes),
            'distance_m' => $distances[$endNode],
        ];
    }

    private function adjacency(array $edges): array
    {
        $adjacency = [];

        foreach ($edges as $edge) {
            $adjacency[$edge['from_node']][$edge['to_node']] = $edge;
            $adjacency[$edge['to_node']][$edge['from_node']] = $edge;
        }

        return $adjacency;
    }

    private function pathNodes(array $previous, string $startNode, string $endNode): array
    {
        $nodes = [$endNode];
        $node = $endNode;

        while ($node !== $startNode) {
            $node = $previous[$node];
            array_unshift($nodes, $node);
        }

        return $nodes;
    }

    private function nearestEdgeNode(SnapResultData $snap, array $edge, array $vertices): string
    {
        $fromDistance = $this->distanceMeters($snap->snapped, $vertices[$edge['from_node']]->getAttributeBag()->getAttributes());
        $toDistance = $this->distanceMeters($snap->snapped, $vertices[$edge['to_node']]->getAttributeBag()->getAttributes());

        return $fromDistance <= $toDistance ? $edge['from_node'] : $edge['to_node'];
    }

    private function distanceMeters(array $first, array $second): float
    {
        $metersPerDegreeLat = 111320.0;
        $metersPerDegreeLng = 111320.0 * cos(deg2rad($first['lat']));
        $x = ($second['lng'] - $first['lng']) * $metersPerDegreeLng;
        $y = ($second['lat'] - $first['lat']) * $metersPerDegreeLat;

        return sqrt(($x * $x) + ($y * $y));
    }
}
