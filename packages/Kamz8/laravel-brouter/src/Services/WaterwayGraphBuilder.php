<?php

namespace Kamz\LaravelBRouter\Services;

use Fhaculty\Graph\Graph;

class WaterwayGraphBuilder
{
    public function build(array $normalized): array
    {
        $graph = new Graph();
        $vertices = [];
        $edges = [];
        $nodes = [];

        foreach ($normalized['nodes'] ?? [] as $node) {
            $vertices[$node['id']] = $graph->createVertex($node['id']);
            $vertices[$node['id']]->getAttributeBag()->setAttributes($node);
            $nodes[$node['id']] = $node;
        }

        foreach ($normalized['edges'] ?? [] as $edge) {
            if (! isset($vertices[$edge['from_node']], $vertices[$edge['to_node']])) {
                continue;
            }

            $graphEdge = $vertices[$edge['from_node']]->createEdge($vertices[$edge['to_node']]);
            $graphEdge->getAttributeBag()->setAttributes($edge + ['weight' => $edge['distance_m']]);

            $edges[$edge['id']] = $edge + ['graph_edge' => $graphEdge];
        }

        $components = $this->components($nodes, $edges);
        foreach ($edges as $id => $edge) {
            $edges[$id]['component_id'] = $components['edge_components'][$id] ?? 0;
        }

        return [
            'graph' => $graph,
            'vertices' => $vertices,
            'nodes' => $nodes,
            'edges' => $edges,
            'components' => $components,
        ];
    }

    private function components(array $nodes, array $edges): array
    {
        $adjacency = [];

        foreach ($edges as $edge) {
            $adjacency[$edge['from_node']][] = $edge['to_node'];
            $adjacency[$edge['to_node']][] = $edge['from_node'];
        }

        $nodeComponents = [];
        $componentId = 0;

        foreach (array_keys($nodes) as $nodeId) {
            if (isset($nodeComponents[$nodeId])) {
                continue;
            }

            $componentId++;
            $queue = [$nodeId];

            while ($queue !== []) {
                $current = array_pop($queue);
                if (isset($nodeComponents[$current])) {
                    continue;
                }

                $nodeComponents[$current] = $componentId;

                foreach ($adjacency[$current] ?? [] as $neighbor) {
                    if (! isset($nodeComponents[$neighbor])) {
                        $queue[] = $neighbor;
                    }
                }
            }
        }

        $edgeComponents = [];
        foreach ($edges as $edgeId => $edge) {
            $edgeComponents[$edgeId] = $nodeComponents[$edge['from_node']] ?? 0;
        }

        return [
            'node_components' => $nodeComponents,
            'edge_components' => $edgeComponents,
            'component_count' => $componentId,
        ];
    }
}
