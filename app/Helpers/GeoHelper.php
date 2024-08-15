<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class GeoHelper
{
    /**
     * Formatuje tablicę współrzędnych do formatu WKT (Well-Known Text) dla wielokąta.
     *
     * @param array|null $coordinates Tablica współrzędnych [longitude, latitude]
     * @return string|null Sformatowany ciąg WKT dla wielokąta lub null, jeśli brak danych
     */
    public static function formatPolygonPoints(?array $coordinates): ?string
    {
        if (!$coordinates) {
            return null;
        }

        $formattedPoints = array_map(function($point) {
            return "{$point[0]} {$point[1]}";
        }, $coordinates);

        // Upewnij się, że pierwszy i ostatni punkt są takie same (zamknięty wielokąt)
        if ($formattedPoints[0] !== end($formattedPoints)) {
            $formattedPoints[] = $formattedPoints[0];
        }

        return implode(',', $formattedPoints);
    }

    /**
     * Tworzy wyrażenie SQL dla punktu geograficznego.
     *
     * @param float $longitude
     * @param float $latitude
     * @return \Illuminate\Database\Query\Expression
     */
    public static function pointToGeography($longitude, $latitude): \Illuminate\Database\Query\Expression
    {
        return DB::raw("ST_GeomFromText('POINT({$longitude} {$latitude})')");
    }

    /**
     * Tworzy wyrażenie SQL dla wielokąta geograficznego.
     *
     * @param string|null $polygonPoints Sformatowany ciąg punktów wielokąta
     * @return \Illuminate\Database\Query\Expression|null
     */
    public static function polygonToGeography(?string $polygonPoints): ?\Illuminate\Database\Query\Expression
    {
        if (!$polygonPoints) {
            return null;
        }
        return DB::raw("ST_GeomFromText('POLYGON(({$polygonPoints}))')");
    }

    public static function formatArea($area): array
    {
        $coordinates = $area->getCoordinates();
        return array_map(function($ring) {
            return array_map(function($point) {
                return [
                    'latitude' => $point->latitude,
                    'longitude' => $point->longitude,
                ];
            }, $ring);
        }, $coordinates);
    }
}
