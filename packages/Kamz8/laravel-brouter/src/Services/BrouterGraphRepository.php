<?php

namespace Kamz\LaravelBRouter\Services;

use Illuminate\Support\Facades\DB;

class BrouterGraphRepository
{
    private const CONNECTION = 'brouter';

    public function activeGraphVersion(): ?string
    {
        $graph = DB::connection(self::CONNECTION)->table('graphs')
            ->join('imports', 'imports.id', '=', 'graphs.import_id')
            ->where('imports.status', 'published')
            ->where('imports.is_active', true)
            ->orderByDesc('graphs.built_at')
            ->select('graphs.version')
            ->first();

        return $graph?->version;
    }

    /**
     * @return array{import_id: int, version: string}|null
     */
    public function activeGraphForRoute(array $start, array $end): ?array
    {
        $graph = DB::connection(self::CONNECTION)->table('graphs')
            ->join('imports', 'imports.id', '=', 'graphs.import_id')
            ->where('imports.status', 'published')
            ->where('imports.is_active', true)
            ->whereRaw('ST_Covers(imports.bbox, ST_SetSRID(ST_MakePoint(?, ?), 4326))', [$start['lng'], $start['lat']])
            ->whereRaw('ST_Covers(imports.bbox, ST_SetSRID(ST_MakePoint(?, ?), 4326))', [$end['lng'], $end['lat']])
            ->orderByDesc('graphs.built_at')
            ->select(['graphs.import_id', 'graphs.version'])
            ->first();

        if ($graph === null) {
            return null;
        }

        return [
            'import_id' => (int) $graph->import_id,
            'version' => (string) $graph->version,
        ];
    }

    public function activeGraphPayload(?int $importId = null): array
    {
        $rows = DB::connection(self::CONNECTION)->table('waterway_edges')
            ->join('imports', 'imports.id', '=', 'waterway_edges.import_id')
            ->join('waterway_nodes as from_node', 'from_node.id', '=', 'waterway_edges.from_node_id')
            ->join('waterway_nodes as to_node', 'to_node.id', '=', 'waterway_edges.to_node_id')
            ->where('imports.status', 'published')
            ->where('imports.is_active', true)
            ->when($importId !== null, fn ($query) => $query->where('waterway_edges.import_id', $importId))
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
