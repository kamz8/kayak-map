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
}
