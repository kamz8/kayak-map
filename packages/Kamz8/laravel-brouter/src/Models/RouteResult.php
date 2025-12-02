<?php

namespace Kamz\LaravelBRouter\Models;

class RouteResult
{
    public $path;
    public $startSnap;
    public $endSnap;
    public $distance;
    public $duration;

    public function toArray()
    {
        return [
            'type' => 'FeatureCollection',
            'features' => [
                $this->toGeoJSON()
            ],
            'properties' => [
                'distance' => $this->distance,
                'duration' => $this->duration,
            ]
        ];
    }

    public function toGeoJSON()
    {
        // Convert to GeoJSON
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'LineString',
                'coordinates' => $this->path
            ]
        ];
    }
}
