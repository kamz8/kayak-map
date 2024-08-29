<?php

namespace App\Services;

class GeodataService
{
    /**
     * Calculate the distance between two points using the Haversine formula.
     *
     * @param float $lat1 Latitude of the first point.
     * @param float $lng1 Longitude of the first point.
     * @param float $lat2 Latitude of the second point.
     * @param float $lng2 Longitude of the second point.
     * @return float Distance in meters.
     */
    public static function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        $earthRadius = 6371000; // Radius of the earth in meters.

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lngDelta / 2) * sin($lngDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return (int) round($distance);
    }

    /**
     * Smooth a trail using the Ramer-Douglas-Peucker algorithm.
     *
     * @param array $points Array of points, each point is an array with 'lat' and 'lng' keys.
     * @param float $epsilon The maximum distance of a point to a line for the point to be simplified.
     * @return array Smoothed array of points.
     */
    public static function smoothTrail(array $points, float $epsilon = 0.00001): array
    {
        $pointCount = count($points);
        if ($pointCount <= 2) {
            return $points;
        }

        $maxDistance = 0;
        $index = 0;
        $end = $pointCount - 1;

        for ($i = 1; $i < $end; $i++) {
            $distance = self::perpendicularDistance($points[$i], $points[0], $points[$end]);
            if ($distance > $maxDistance) {
                $index = $i;
                $maxDistance = $distance;
            }
        }

        if ($maxDistance > $epsilon) {
            $recResults1 = self::smoothTrail(array_slice($points, 0, $index + 1), $epsilon);
            $recResults2 = self::smoothTrail(array_slice($points, $index), $epsilon);

            $result = [...array_slice($recResults1, 0, -1), ...$recResults2];
        } else {
            $result = [$points[0], $points[$end]];
        }

        return $result;
    }

    /**
     * Calculate the perpendicular distance from a point to a line.
     *
     * @param array $point The point to calculate the distance from.
     * @param array $lineStart The start point of the line.
     * @param array $lineEnd The end point of the line.
     * @return float The perpendicular distance.
     */
    private static function perpendicularDistance(array $point, array $lineStart, array $lineEnd): float
    {
        $area = abs(
            ($lineStart['lat'] * $lineEnd['lng'] +
                $lineEnd['lat'] * $point['lng'] +
                $point['lat'] * $lineStart['lng'] -
                $lineEnd['lat'] * $lineStart['lng'] -
                $point['lat'] * $lineEnd['lng'] -
                $lineStart['lat'] * $point['lng']) / 2.0
        );

        $bottom = sqrt(
            ($lineStart['lat'] - $lineEnd['lat']) ** 2 +
            ($lineStart['lng'] - $lineEnd['lng']) ** 2
        );

        return ($area * 2) / $bottom;
    }

    /**
     * Find the shortest path between two points using Dijkstra's algorithm.
     *
     * @param array $graph An associative array representing the graph. Keys are node IDs, values are arrays of neighboring nodes and distances.
     * @param string $start The ID of the start node.
     * @param string $end The ID of the end node.
     * @return array|null The shortest path as an array of node IDs, or null if no path is found.
     */
    public function dijkstra(array $graph, string $start, string $end): ?array
    {
        $distances = [];
        $previous = [];
        $queue = new \SplPriorityQueue();

        foreach ($graph as $vertex => $neighbors) {
            $distances[$vertex] = INF;
            $previous[$vertex] = null;
        }

        $distances[$start] = 0;
        $queue->insert($start, 0);

        while (!$queue->isEmpty()) {
            $current = $queue->extract();

            if ($current === $end) {
                $path = [];
                while ($current !== null) {
                    array_unshift($path, $current);
                    $current = $previous[$current];
                }
                return $path;
            }

            if (!isset($graph[$current])) {
                continue;
            }

            foreach ($graph[$current] as $neighbor => $cost) {
                $alt = $distances[$current] + $cost;
                if ($alt < $distances[$neighbor]) {
                    $distances[$neighbor] = $alt;
                    $previous[$neighbor] = $current;
                    $queue->insert($neighbor, -$alt);
                }
            }
        }

        return null; // No path found
    }

    /**
     * Convert an array of coordinate points to a graph structure for Dijkstra's algorithm.
     *
     * @param array $points Array of points, each point is an array [lat, lng].
     * @return array The graph structure.
     */
    public function pointsToGraph(array $points): array
    {
        $graph = [];
        $count = count($points);

        for ($i = 0; $i < $count; $i++) {
            $graph["node_$i"] = [];
            for ($j = $i + 1; $j < $count; $j++) {
                $distance = $this->calculateDistance(
                    $points[$i][0], $points[$i][1],
                    $points[$j][0], $points[$j][1]
                );
                $graph["node_$i"]["node_$j"] = $distance;
                $graph["node_$j"]["node_$i"] = $distance;
            }
        }

        return $graph;
    }
}
