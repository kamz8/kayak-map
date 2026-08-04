<?php

namespace Kamz\LaravelBRouter\Services;

use Illuminate\Support\Facades\DB;
use Kamz\LaravelBRouter\Contracts\ImportRepositoryInterface;
use RuntimeException;

class BrouterImportRepository implements ImportRepositoryInterface
{
    private const CONNECTION = 'brouter';

    public function create(array $bbox, array $metadata): int
    {
        return (int) DB::connection(self::CONNECTION)->table('imports')->insertGetId([
            'version' => (string) str()->uuid(),
            'status' => 'staging',
            'is_active' => false,
            'bbox' => DB::raw(sprintf(
                "ST_MakeEnvelope(%s, %s, %s, %s, 4326)",
                $bbox['west'],
                $bbox['south'],
                $bbox['east'],
                $bbox['north'],
            )),
            'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id');
    }

    public function setStatus(int $importId, string $status): void
    {
        DB::connection(self::CONNECTION)->table('imports')->where('id', $importId)->update([
            'status' => $status,
            'failed_at' => $status === 'failed' ? now() : null,
            'updated_at' => now(),
        ]);
    }

    public function persist(int $importId, array $normalized, array $raw): array
    {
        $connection = DB::connection(self::CONNECTION);

        return $connection->transaction(function () use ($connection, $importId, $normalized): array {
            $nodeIds = [];
            foreach ($normalized['nodes'] ?? [] as $node) {
                $databaseId = $connection->table('waterway_nodes')->insertGetId([
                    'import_id' => $importId,
                    'osm_id' => (string) $node['osm_id'],
                    'geometry' => DB::raw($this->pointWkt($node['lat'], $node['lng'])),
                    'source_tags' => json_encode($node['source_tags'] ?? [], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ], 'id');
                $nodeIds[$node['id']] = $databaseId;
            }

            $waterwayIds = [];
            foreach ($normalized['waterways'] ?? [] as $waterway) {
                if (count($waterway['geometry']) < 2) {
                    continue;
                }

                $waterwayIds[$waterway['id']] = $connection->table('waterways')->insertGetId([
                    'import_id' => $importId,
                    'osm_id' => (string) $waterway['id'],
                    'name' => $waterway['name'],
                    'waterway' => $waterway['waterway'],
                    'geometry' => DB::raw($this->lineWkt($waterway['geometry'])),
                    'source_tags' => json_encode($waterway['source_tags'], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ], 'id');
            }

            foreach ($normalized['edges'] ?? [] as $edge) {
                if (! isset($nodeIds[$edge['from_node']], $nodeIds[$edge['to_node']])) {
                    continue;
                }

                $connection->table('waterway_edges')->insert([
                    'import_id' => $importId,
                    'waterway_id' => $waterwayIds[$edge['way_id']] ?? null,
                    'from_node_id' => $nodeIds[$edge['from_node']],
                    'to_node_id' => $nodeIds[$edge['to_node']],
                    'way_id' => $edge['way_id'],
                    'component_id' => (int) ($edge['component_id'] ?? 0),
                    'is_bidirectional' => (bool) ($edge['is_bidirectional'] ?? true),
                    'is_water_body_crossing' => (bool) ($edge['is_water_body_crossing'] ?? false),
                    'geometry' => DB::raw($this->lineWkt($edge['geometry'])),
                    'distance_m' => $edge['distance_m'],
                    'source_tags' => json_encode($edge['source_tags'] ?? [], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($normalized['features'] ?? [] as $feature) {
                $connection->table('waterway_features')->insert([
                    'import_id' => $importId,
                    'osm_id' => (string) $feature['id'],
                    'feature_type' => $feature['feature_type'],
                    'name' => $feature['name'],
                    'is_navigable' => $feature['is_navigable'] ?? null,
                    'routing_penalty' => $feature['routing_penalty'] ?? 0,
                    'portage_required' => $feature['portage_required'] ?? false,
                    'warning_level' => $feature['warning_level'] ?? null,
                    'geometry' => DB::raw($this->featureWkt($feature['geometry'])),
                    'source_tags' => json_encode($feature['source_tags'], JSON_THROW_ON_ERROR),
                    'metadata' => json_encode($feature['metadata'] ?? [], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($normalized['water_bodies'] ?? [] as $waterBody) {
                if (! $this->isClosed($waterBody['geometry'])) {
                    continue;
                }

                $connection->table('water_bodies')->insert([
                    'import_id' => $importId,
                    'osm_id' => (string) $waterBody['id'],
                    'water_type' => $waterBody['water_type'],
                    'name' => $waterBody['name'],
                    'geometry' => DB::raw($this->polygonWkt($waterBody['geometry'])),
                    'source_tags' => json_encode($waterBody['source_tags'], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [
                'node_count' => count($nodeIds),
                'edge_count' => $connection->table('waterway_edges')->where('import_id', $importId)->count(),
                'feature_count' => count($normalized['features'] ?? []),
                'water_body_count' => count($normalized['water_bodies'] ?? []),
            ];
        });
    }

    public function buildGraph(int $importId, array $normalized): array
    {
        $counts = [
            'node_count' => DB::connection(self::CONNECTION)->table('waterway_nodes')->where('import_id', $importId)->count(),
            'edge_count' => DB::connection(self::CONNECTION)->table('waterway_edges')->where('import_id', $importId)->count(),
        ];

        DB::connection(self::CONNECTION)->table('graphs')->insert([
            'import_id' => $importId,
            'version' => (string) str()->uuid(),
            ...$counts,
            'metadata' => json_encode([
                'source' => 'overpass',
                'components' => $normalized['components'] ?? [],
                'graph_cache_key' => "brouter:graph:{$importId}",
            ], JSON_THROW_ON_ERROR),
            'built_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $counts;
    }

    public function validate(int $importId): void
    {
        $connection = DB::connection(self::CONNECTION);
        $counts = [
            $connection->table('waterway_nodes')->where('import_id', $importId)->count(),
            $connection->table('waterway_edges')->where('import_id', $importId)->count(),
        ];

        if ($counts[0] === 0 || $counts[1] === 0) {
            throw new RuntimeException('Import validation requires at least one node and one edge.');
        }

        foreach (['waterways', 'waterway_nodes', 'waterway_edges', 'waterway_features', 'water_bodies'] as $table) {
            if ($connection->selectOne("SELECT 1 FROM {$table} WHERE import_id = ? AND NOT ST_IsValid(geometry) LIMIT 1", [$importId])) {
                throw new RuntimeException("Import contains invalid geometry in {$table}.");
            }
        }
    }

    public function publish(int $importId, array $metadata): void
    {
        DB::connection(self::CONNECTION)->transaction(function () use ($importId, $metadata): void {
            $connection = DB::connection(self::CONNECTION);
            $connection->table('imports')->where('status', 'published')->where('is_active', true)->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
            $connection->table('imports')->where('id', $importId)->update([
                'status' => 'published',
                'is_active' => true,
                'metadata' => json_encode($metadata, JSON_THROW_ON_ERROR),
                'published_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    private function pointWkt(float $lat, float $lng): string
    {
        return sprintf("ST_SetSRID(ST_Point(%F, %F), 4326)", $lng, $lat);
    }

    private function lineWkt(array $geometry): string
    {
        return "ST_SetSRID(ST_GeomFromText('LINESTRING(".$this->coordinates($geometry).")'), 4326)";
    }

    private function polygonWkt(array $geometry): string
    {
        return "ST_Multi(ST_SetSRID(ST_GeomFromText('POLYGON((".$this->coordinates($geometry)."))'), 4326))";
    }

    private function featureWkt(array $geometry): string
    {
        if (count($geometry) === 1) {
            return $this->pointWkt((float) $geometry[0]['lat'], (float) $geometry[0]['lon']);
        }

        return $this->lineWkt($geometry);
    }

    private function coordinates(array $geometry): string
    {
        return implode(',', array_map(fn (array $point): string => sprintf('%F %F', $point['lon'], $point['lat']), $geometry));
    }

    private function isClosed(array $geometry): bool
    {
        return count($geometry) >= 4 && $geometry[0] === $geometry[array_key_last($geometry)];
    }
}
