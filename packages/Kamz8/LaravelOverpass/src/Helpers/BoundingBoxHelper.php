<?php

namespace Kamz8\LaravelOverpass\Helpers;

/**
 * Class BoundingBoxHelper
 *
 * Helper class for generating bounding boxes for geographical coordinates.
 *
 * @package Kamz8\LaravelOverpass\Helpers
 */
class BoundingBoxHelper
{
    /**
     * Generate a bounding box string from two points with an optional margin.
     *
     * @param float $lat1 Latitude of the first point
     * @param float $lon1 Longitude of the first point
     * @param float $lat2 Latitude of the second point
     * @param float $lon2 Longitude of the second point
     * @param float $marginPercent Percentage of margin to add around the bounding box (default: 10%)
     * @return string Bounding box string in Overpass API format
     */
    public static function generateBBox(float $lat1, float $lon1, float $lat2, float $lon2, float $marginPercent = 10.0): string
    {
        $south = min($lat1, $lat2);
        $north = max($lat1, $lat2);
        $west = min($lon1, $lon2);
        $east = max($lon1, $lon2);

        $latMargin = ($north - $south) * ($marginPercent / 100);
        $lonMargin = ($east - $west) * ($marginPercent / 100);

        $south -= $latMargin;
        $north += $latMargin;
        $west -= $lonMargin;
        $east += $lonMargin;

        return sprintf("(%.6f,%.6f,%.6f,%.6f)", $south, $west, $north, $east);
    }

    /**
     * Calculate a bounding box from two points.
     *
     * @param float $startLat Starting latitude
     * @param float $startLng Starting longitude
     * @param float $endLat Ending latitude
     * @param float $endLng Ending longitude
     * @return array Associative array with 'south', 'west', 'north', and 'east' keys
     */
    public static function calculateBoundingBox(float $startLat, float $startLng, float $endLat, float $endLng): array
    {
        return [
            'south' => min($startLat, $endLat),
            'west'  => min($startLng, $endLng),
            'north' => max($startLat, $endLat),
            'east'  => max($startLng, $endLng),
        ];
    }
}
