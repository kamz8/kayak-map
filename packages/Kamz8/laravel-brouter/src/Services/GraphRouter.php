<?php

namespace Kamz\LaravelBRouter\Services;

use Kamz\LaravelBRouter\DTO\SnapResultData;
use Kamz\LaravelBRouter\Exceptions\DisconnectedWaterwayException;
use SplPriorityQueue;

class GraphRouter
{
    public function route(array $payload, SnapResultData $start, SnapResultData $end): array
    {
        $startEdge = $payload['edges'][$start->edgeId];
        $endEdge = $payload['edges'][$end->edgeId];

        if ($start->edgeId === $end->edgeId && $start->position !== null && $end->position !== null) {
            return [
                'coordinates' => [
                    [$start->snapped['lng'], $start->snapped['lat']],
                    [$end->snapped['lng'], $end->snapped['lat']],
                ],
                'distance_m' => abs($end->position - $start->position) * (float) $startEdge['distance_m'],
            ];
        }

        $routingPayload = $this->withVirtualSnapNodes($payload, $start, $end, $startEdge, $endEdge);
        $result = $this->shortestPath($routingPayload, $routingPayload['start_node'], $routingPayload['end_node']);

        if ($result === null) {
            throw new DisconnectedWaterwayException('No connected waterway path found.');
        }

        return $result;
    }

    private function shortestPath(array $payload, string $startNode, string $endNode): ?array
    {
        $distances = [$startNode => 0.0];
        $previous = [];
        $queue = new SplPriorityQueue();
        $queue->setExtractFlags(SplPriorityQueue::EXTR_DATA);
        $queue->insert($startNode, 0.0);
        $adjacency = $this->adjacency($payload['edges']);

        while (! $queue->isEmpty()) {
            $node = $queue->extract();

            if ($node === $endNode) {
                break;
            }

            foreach ($adjacency[$node] ?? [] as $neighbor => $edge) {
                $distance = $distances[$node] + $edge['distance_m'];

                if (! isset($distances[$neighbor]) || $distance < $distances[$neighbor]) {
                    $distances[$neighbor] = $distance;
                    $previous[$neighbor] = $node;
                    $queue->insert($neighbor, -$distance);
                }
            }
        }

        if (! isset($distances[$endNode])) {
            return null;
        }

        $nodes = $this->pathNodes($previous, $startNode, $endNode);

        return [
            'coordinates' => array_map(fn (string $node): array => [
                $this->node($payload, $node)['lng'],
                $this->node($payload, $node)['lat'],
            ], $nodes),
            'distance_m' => $distances[$endNode],
        ];
    }

    private function adjacency(array $edges): array
    {
        $adjacency = [];

        foreach ($edges as $edge) {
            $adjacency[$edge['from_node']][$edge['to_node']] = $edge;
            if (($edge['is_bidirectional'] ?? true) === true) {
                $adjacency[$edge['to_node']][$edge['from_node']] = $edge;
            }
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

    private function withVirtualSnapNodes(array $payload, SnapResultData $start, SnapResultData $end, array $startEdge, array $endEdge): array
    {
        $payload['nodes'] ??= $this->nodesFromVertices($payload['vertices'] ?? []);
        [$payload, $startNode] = $this->attachSnap($payload, $start, $startEdge, 'start');
        [$payload, $endNode] = $this->attachSnap($payload, $end, $endEdge, 'end');
        $payload['start_node'] = $startNode;
        $payload['end_node'] = $endNode;

        return $payload;
    }

    private function attachSnap(array $payload, SnapResultData $snap, array $edge, string $prefix): array
    {
        $position = $snap->position;

        if ($position !== null && $position <= 0.000001) {
            return [$payload, $edge['from_node']];
        }

        if ($position !== null && $position >= 0.999999) {
            return [$payload, $edge['to_node']];
        }

        if ($position === null) {
            return [$payload, $this->nearestEdgeNode($snap, $edge, $payload)];
        }

        $nodeId = "virtual:{$prefix}:{$edge['id']}";
        $payload['nodes'][$nodeId] = [
            'id' => $nodeId,
            'lat' => $snap->snapped['lat'],
            'lng' => $snap->snapped['lng'],
            'virtual' => true,
        ];

        $edgeDistance = (float) $edge['distance_m'];
        unset($payload['edges'][$edge['id']]);
        $payload['edges']["{$edge['id']}:{$prefix}:from"] = $this->partialEdge($edge, $edge['from_node'], $nodeId, $edgeDistance * $position);
        $payload['edges']["{$edge['id']}:{$prefix}:to"] = $this->partialEdge($edge, $nodeId, $edge['to_node'], $edgeDistance * (1 - $position));

        return [$payload, $nodeId];
    }

    private function partialEdge(array $edge, string $fromNode, string $toNode, float $distanceMeters): array
    {
        return array_replace($edge, [
            'id' => "{$edge['id']}:{$fromNode}:{$toNode}",
            'from_node' => $fromNode,
            'to_node' => $toNode,
            'distance_m' => max(0.0, $distanceMeters),
            'is_bidirectional' => $edge['is_bidirectional'] ?? true,
        ]);
    }

    private function nearestEdgeNode(SnapResultData $snap, array $edge, array $payload): string
    {
        $fromDistance = $this->distanceMeters($snap->snapped, $this->node($payload, $edge['from_node']));
        $toDistance = $this->distanceMeters($snap->snapped, $this->node($payload, $edge['to_node']));

        return $fromDistance <= $toDistance ? $edge['from_node'] : $edge['to_node'];
    }

    private function node(array $payload, string $node): array
    {
        if (isset($payload['nodes'][$node])) {
            return $payload['nodes'][$node];
        }

        return $payload['vertices'][$node]->getAttributeBag()->getAttributes();
    }

    private function nodesFromVertices(array $vertices): array
    {
        $nodes = [];

        foreach ($vertices as $id => $vertex) {
            $nodes[$id] = $vertex->getAttributeBag()->getAttributes();
        }

        return $nodes;
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
