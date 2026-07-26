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

        foreach ($normalized['nodes'] ?? [] as $node) {
            $vertices[$node['id']] = $graph->createVertex($node['id']);
            $vertices[$node['id']]->getAttributeBag()->setAttributes($node);
        }

        foreach ($normalized['edges'] ?? [] as $edge) {
            if (! isset($vertices[$edge['from_node']], $vertices[$edge['to_node']])) {
                continue;
            }

            $graphEdge = $vertices[$edge['from_node']]->createEdge($vertices[$edge['to_node']]);
            $graphEdge->getAttributeBag()->setAttributes($edge + ['weight' => $edge['distance_m']]);

            $edges[$edge['id']] = $edge + ['graph_edge' => $graphEdge];
        }

        return [
            'graph' => $graph,
            'vertices' => $vertices,
            'edges' => $edges,
        ];
    }
}
