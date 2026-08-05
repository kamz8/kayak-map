<?php

namespace Kamz\LaravelBRouter\Services;

use Illuminate\Support\Facades\DB;

class BrouterGraphRepository
{
    private const CONNECTION = 'brouter';

    public function activeGraphVersion(?array $bbox = null): ?string
    {
        return $this->publishedGraph($bbox)?->version;
    }

    public function activeGraphImportId(?array $bbox = null): ?int
    {
        $graph = $this->publishedGraph($bbox);

        return $graph === null ? null : (int) $graph->import_id;
    }

    public function activeGraphVersionForRiver(string $riverName, ?array $bbox = null): ?string
    {
        return $this->publishedGraphForRiver($riverName, $bbox)?->version;
    }

    public function activeGraphImportIdForRiver(string $riverName, ?array $bbox = null): ?int
    {
        $graph = $this->publishedGraphForRiver($riverName, $bbox);

        return $graph === null ? null : (int) $graph->import_id;
    }

    private function publishedGraph(?array $bbox): ?object
    {
        $query = DB::connection(self::CONNECTION)->table('graphs')
            ->join('imports', 'imports.id', '=', 'graphs.import_id')
            ->where('imports.status', 'published')
            ->orderByDesc('graphs.built_at')
            ->select(['graphs.version', 'graphs.import_id']);

        if ($bbox === null) {
            $query->where('imports.is_active', true);
        } else {
            $query->whereRaw(
                'ST_Intersects(imports.bbox, ST_MakeEnvelope(?, ?, ?, ?, 4326))',
                [$bbox['west'], $bbox['south'], $bbox['east'], $bbox['north']]
            )->orderByDesc('imports.is_active');
        }

        return $query->first();
    }

    private function publishedGraphForRiver(string $riverName, ?array $bbox): ?object
    {
        $query = DB::connection(self::CONNECTION)->table('graphs')
            ->join('imports', 'imports.id', '=', 'graphs.import_id')
            ->where('imports.status', 'published')
            ->whereRaw(
                "lower(coalesce(imports.metadata->>'name', imports.metadata->'metadata'->>'name')) = lower(?)",
                [$riverName]
            )
            ->orderByDesc('graphs.built_at')
            ->select(['graphs.version', 'graphs.import_id']);

        if ($bbox !== null) {
            $query->whereRaw(
                'ST_Intersects(imports.bbox, ST_MakeEnvelope(?, ?, ?, ?, 4326))',
                [$bbox['west'], $bbox['south'], $bbox['east'], $bbox['north']]
            );
        }

        return $query->first();
    }

    public function activeGraphPayload(): array
    {
        $rows = DB::connection(self::CONNECTION)->table('waterway_edges')
            ->join('imports', 'imports.id', '=', 'waterway_edges.import_id')
            ->join('waterway_nodes as from_node', 'from_node.id', '=', 'waterway_edges.from_node_id')
            ->join('waterway_nodes as to_node', 'to_node.id', '=', 'waterway_edges.to_node_id')
            ->where('imports.status', 'published')
            ->where('imports.is_active', true)
            ->select([
                'waterway_edges.id',
                'waterway_edges.distance_m',
                'waterway_edges.component_id',
                'waterway_edges.is_bidirectional',
                'waterway_edges.is_water_body_crossing',
                'from_node.id as from_node_id',
                DB::raw('ST_Y(from_node.geometry) as from_lat'),
                DB::raw('ST_X(from_node.geometry) as from_lng'),
                'to_node.id as to_node_id',
                DB::raw('ST_Y(to_node.geometry) as to_lat'),
                DB::raw('ST_X(to_node.geometry) as to_lng'),
                DB::raw('ST_AsGeoJSON(waterway_edges.geometry) as geometry_json'),
            ])
            ->get();

        $nodes = [];
        $edges = [];

        foreach ($rows as $row) {
            $fromNode = 'db:'.$row->from_node_id;
            $toNode = 'db:'.$row->to_node_id;
            $nodes[$fromNode] = ['id' => $fromNode, 'lat' => (float) $row->from_lat, 'lng' => (float) $row->from_lng, 'virtual' => false];
            $nodes[$toNode] = ['id' => $toNode, 'lat' => (float) $row->to_lat, 'lng' => (float) $row->to_lng, 'virtual' => false];

            $geometry = json_decode((string) $row->geometry_json, true, 512, JSON_THROW_ON_ERROR);
            $edges[(string) $row->id] = [
                'id' => (string) $row->id,
                'from_node' => $fromNode,
                'to_node' => $toNode,
                'geometry' => array_map(fn (array $coordinate): array => ['lng' => $coordinate[0], 'lat' => $coordinate[1]], $geometry['coordinates']),
                'distance_m' => (float) $row->distance_m,
                'component_id' => (int) $row->component_id,
                'is_bidirectional' => (bool) $row->is_bidirectional,
                'is_water_body_crossing' => (bool) $row->is_water_body_crossing,
            ];
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges,
        ];
    }
}
