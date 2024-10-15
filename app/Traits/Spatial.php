<?php
namespace App\Traits;
trait Spatial {
    /**
     * Konwertuje punkty do formatu WKT LineString.
     *
     * @param array $points
     * @return string
     */
    protected function convertToWKTLineString(array $points): string
    {
        $coordinates = array_map(function ($point) {
            return implode(' ', $point);
        }, $points);

        return 'LINESTRING(' . implode(', ', $coordinates) . ')';
    }
}
