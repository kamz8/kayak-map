<?php

namespace App\Services;

use Illuminate\Support\Collection;

class GeodataService
{
    /**
     * Calculate the distance between two points using the Haversine formula.
     *
     * @param float $lat1 Latitude of the first point
     * @param float $lon1 Longitude of the first point
     * @param float $lat2 Latitude of the second point
     * @param float $lon2 Longitude of the second point
     * @return float Distance in meters
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    /**
     * Find the nearest point on a river to a given point.
     *
     * @param array $point The reference point [lat, lon]
     * @param Collection $riverPoints Collection of river points [[lat, lon], ...]
     * @return array|null The nearest point on the river or null if not found
     */
    public function findNearestPointOnRiver(array $point, Collection $riverPoints): ?array
    {
        return $riverPoints->sortBy(function ($riverPoint) use ($point) {
            return $this->calculateDistance($point[0], $point[1], $riverPoint[0], $riverPoint[1]);
        })->first();
    }

    /**
     * Simplify a path using the Ramer-Douglas-Peucker algorithm.
     *
     * @param array $points Array of points [[lat, lon], ...]
     * @param float $epsilon The maximum distance of a point to a line for the point to be simplified
     * @return array Simplified array of points
     */
    public function simplifyPath(array $points, float $epsilon = 0.00001): array
    {
        $dmax = 0;
        $index = 0;
        $end = count($points) - 1;

        for ($i = 1; $i < $end; $i++) {
            $d = $this->perpendicularDistance($points[$i], $points[0], $points[$end]);
            if ($d > $dmax) {
                $index = $i;
                $dmax = $d;
            }
        }

        if ($dmax > $epsilon) {
            $results1 = $this->simplifyPath(array_slice($points, 0, $index + 1), $epsilon);
            $results2 = $this->simplifyPath(array_slice($points, $index), $epsilon);

            return array_merge(array_slice($results1, 0, -1), $results2);
        } else {
            return [$points[0], $points[$end]];
        }
    }

    /**
     * Calculate the perpendicular distance of a point from a line.
     *
     * @param array $point The point to calculate the distance for
     * @param array $lineStart The start point of the line
     * @param array $lineEnd The end point of the line
     * @return float The perpendicular distance
     */
    private function perpendicularDistance(array $point, array $lineStart, array $lineEnd): float
    {
        $x = $point[1];
        $y = $point[0];
        $x1 = $lineStart[1];
        $y1 = $lineStart[0];
        $x2 = $lineEnd[1];
        $y2 = $lineEnd[0];

        $A = $x - $x1;
        $B = $y - $y1;
        $C = $x2 - $x1;
        $D = $y2 - $y1;

        $dot = $A * $C + $B * $D;
        $lenSq = $C * $C + $D * $D;
        $param = $dot / $lenSq;


        if ($param < 0 || ($x1 == $x2 && $y1 == $y2)) {
            $xx = $x1;
            $yy = $y1;
        } elseif ($param > 1) {
            $xx = $x2;
            $yy = $y2;
        } else {
            $xx = $x1 + $param * $C;
            $yy = $y1 + $param * $D;
        }

        $dx = $x - $xx;
        $dy = $y - $yy;

        return sqrt($dx * $dx + $dy * $dy);
    }

    /**
     * Find a path along a river between two points.
     *
     * @param array $start Start point [lat, lon]
     * @param array $end End point [lat, lon]
     * @param Collection $riverPoints Collection of river points [[lat, lon], ...]
     * @return array|null Path along the river or null if not found
     */
    public function findRiverPath(array $start, array $end, Collection $riverPoints): ?array
    {
        $nearestStart = $this->findNearestPointOnRiver($start, $riverPoints);
        $nearestEnd = $this->findNearestPointOnRiver($end, $riverPoints);

        if (!$nearestStart || !$nearestEnd) {
            return null;
        }

        $startIndex = $riverPoints->search($nearestStart);
        $endIndex = $riverPoints->search($nearestEnd);

        if ($startIndex === false || $endIndex === false) {
            return null;
        }

        if ($startIndex > $endIndex) {
            [$startIndex, $endIndex] = [$endIndex, $startIndex];
        }

        return $riverPoints->slice($startIndex, $endIndex - $startIndex + 1)->values()->all();
    }

    /**
     * Find the index of the nearest point in the river points collection.
     *
     * @param array $point The reference point [lat, lon]
     * @param Collection $riverPoints Collection of river points [[lat, lon], ...]
     * @return int|null The index of the nearest point or null if not found
     */
    private function findNearestPointIndex(array $point, Collection $riverPoints): ?int
    {
        $nearestIndex = null;
        $minDistance = PHP_FLOAT_MAX;

        foreach ($riverPoints as $index => $riverPoint) {
            $distance = $this->calculateDistance($point[0], $point[1], $riverPoint[0], $riverPoint[1]);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearestIndex = $index;
            }
        }

        return $nearestIndex;
    }
}
