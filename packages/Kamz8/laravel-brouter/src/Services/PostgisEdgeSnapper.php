<?php

namespace Kamz\LaravelBRouter\Services;

use Illuminate\Support\Facades\DB;
use Kamz\LaravelBRouter\DTO\SnapResultData;
use Kamz\LaravelBRouter\Exceptions\SnapDistanceExceededException;

class PostgisEdgeSnapper
{
    private const CONNECTION = 'brouter';

    public function snap(array $point, float $maxDistanceMeters): SnapResultData
    {
        $queryPoint = 'ST_SetSRID(ST_MakePoint(?, ?), 4326)';
        $row = DB::connection(self::CONNECTION)->selectOne(<<<SQL
SELECT
  e.id,
  e.from_node_id,
  e.to_node_id,
  e.distance_m AS edge_distance_m,
  ST_Distance(e.geometry::geography, {$queryPoint}::geography) AS distance_m,
  ST_LineLocatePoint(e.geometry, {$queryPoint}) AS position,
  ST_X(ST_ClosestPoint(e.geometry, {$queryPoint})) AS snapped_lng,
  ST_Y(ST_ClosestPoint(e.geometry, {$queryPoint})) AS snapped_lat
FROM waterway_edges e
JOIN imports i ON i.id = e.import_id
WHERE i.status = 'published'
  AND i.is_active = true
  AND ST_DWithin(e.geometry::geography, {$queryPoint}::geography, ?)
ORDER BY e.geometry <-> {$queryPoint}
LIMIT 1
SQL, [
            $point['lng'], $point['lat'],
            $point['lng'], $point['lat'],
            $point['lng'], $point['lat'],
            $point['lng'], $point['lat'],
            $point['lng'], $point['lat'],
            $maxDistanceMeters,
            $point['lng'], $point['lat'],
        ]);

        if ($row === null || (float) $row->distance_m > $maxDistanceMeters) {
            throw new SnapDistanceExceededException('No waterway edge found within snap tolerance.');
        }

        $position = (float) $row->position;

        return new SnapResultData(
            $point,
            ['lat' => (float) $row->snapped_lat, 'lng' => (float) $row->snapped_lng],
            (float) $row->distance_m,
            (string) $row->id,
            $position,
            (float) $row->edge_distance_m * $position,
            'db:'.$row->from_node_id,
            'db:'.$row->to_node_id,
        );
    }
}
