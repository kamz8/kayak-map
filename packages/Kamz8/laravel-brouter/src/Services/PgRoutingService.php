<?php

namespace Kamz\LaravelBRouter\Services;

use Illuminate\Support\Facades\DB;
use Kamz\LaravelBRouter\DTO\PgRouteResultData;
use Kamz\LaravelBRouter\DTO\RouteRequestData;
use Kamz\LaravelBRouter\Exceptions\DisconnectedWaterwayException;
use Kamz\LaravelBRouter\Exceptions\SnapDistanceExceededException;
use stdClass;

class PgRoutingService
{
    private const CONNECTION = 'brouter';

    public function route(RouteRequestData $request, int $importId): PgRouteResultData
    {
        $start = $this->snap($request->start, $importId, $request->snapToleranceMeters);
        $end = $this->snap($request->end, $importId, $request->snapToleranceMeters);

        $startNode = $this->snapNode($start);
        $endNode = $this->snapNode($end);
        $path = $this->astar($importId, $startNode, $endNode);

        if ($path === []) {
            throw new DisconnectedWaterwayException('No connected waterway path found.');
        }

        $coordinates = array_map(
            static fn (stdClass $row): array => [(float) $row->lng, (float) $row->lat],
            $path,
        );
        $coordinates = $this->withSnapEndpoints($coordinates, $start, $end);
        $distance = (float) $path[array_key_last($path)]->agg_cost
            + $this->partialDistance($start)
            + $this->partialDistance($end);

        return new PgRouteResultData(
            path: $coordinates,
            startSnap: $this->snapArray($start),
            endSnap: $this->snapArray($end),
            distanceMeters: $distance,
            importId: $importId,
            cache: ['engine' => 'pgrouting', 'algorithm' => 'astar'],
        );
    }

    private function snap(array $point, int $importId, float $tolerance): stdClass
    {
        $queryPoint = 'ST_SetSRID(ST_MakePoint(?, ?), 4326)';
        $row = DB::connection(self::CONNECTION)->selectOne(<<<SQL
WITH requested_point AS (
    SELECT {$queryPoint} AS geometry
)
SELECT
    e.id,
    e.source,
    e.target,
    e.distance_m AS edge_distance_m,
    ST_LineLocatePoint(e.geometry, requested_point.geometry) AS fraction,
    ST_Distance(e.geometry::geography, requested_point.geometry::geography) AS distance_m,
    ST_X(ST_ClosestPoint(e.geometry, requested_point.geometry)) AS snapped_lng,
    ST_Y(ST_ClosestPoint(e.geometry, requested_point.geometry)) AS snapped_lat
FROM waterway_edges e
CROSS JOIN requested_point
WHERE e.import_id = ?
  AND ST_DWithin(e.geometry::geography, requested_point.geometry::geography, ?)
ORDER BY e.geometry <-> requested_point.geometry
LIMIT 1
SQL, [$point['lng'], $point['lat'], $importId, $tolerance]);

        if ($row === null) {
            throw new SnapDistanceExceededException('No waterway edge found within snap tolerance.');
        }

        return $row;
    }

    private function astar(int $importId, int $startNode, int $endNode): array
    {
        $edgeSql = "SELECT id, source, target, cost, reverse_cost, "
            .'ST_X(ST_StartPoint(geometry)) AS x1, '
            .'ST_Y(ST_StartPoint(geometry)) AS y1, '
            .'ST_X(ST_EndPoint(geometry)) AS x2, '
            .'ST_Y(ST_EndPoint(geometry)) AS y2 '
            .'FROM waterway_edges '
            .'WHERE import_id = '.(int) $importId;

        return DB::connection(self::CONNECTION)->select(<<<SQL
SELECT route.seq, route.node, route.edge, route.cost, route.agg_cost,
       ST_X(node_geometry.geometry) AS lng,
       ST_Y(node_geometry.geometry) AS lat
FROM pgr_aStar(?::text, ?::bigint, ?::bigint, true) AS route
JOIN waterway_nodes node_geometry
  ON node_geometry.id = route.node
 AND node_geometry.import_id = ?
ORDER BY route.seq
SQL, [$edgeSql, $startNode, $endNode, $importId]);
    }

    private function snapNode(stdClass $snap): int
    {
        return (int) ((float) $snap->fraction <= 0.5 ? $snap->source : $snap->target);
    }

    private function partialDistance(stdClass $snap): float
    {
        $fraction = (float) $snap->fraction;

        return (float) $snap->edge_distance_m * min($fraction, 1 - $fraction);
    }

    private function snapArray(stdClass $snap): array
    {
        return [
            'input' => null,
            'snapped' => [(float) $snap->snapped_lng, (float) $snap->snapped_lat],
            'distance_m' => (float) $snap->distance_m,
            'edge_id' => (string) $snap->id,
            'fraction' => (float) $snap->fraction,
        ];
    }

    private function withSnapEndpoints(array $coordinates, stdClass $start, stdClass $end): array
    {
        $startCoordinate = [(float) $start->snapped_lng, (float) $start->snapped_lat];
        $endCoordinate = [(float) $end->snapped_lng, (float) $end->snapped_lat];

        if ($coordinates === []) {
            return [$startCoordinate, $endCoordinate];
        }

        if ($coordinates[0] !== $startCoordinate) {
            array_unshift($coordinates, $startCoordinate);
        }

        if ($coordinates[array_key_last($coordinates)] !== $endCoordinate) {
            $coordinates[] = $endCoordinate;
        }

        return $coordinates;
    }
}
