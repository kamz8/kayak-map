<?php

namespace Kamz\LaravelBRouter\Models;

class RouteResult
{
    public function __construct(
        public array $path = [],
        public array $startSnap = [],
        public array $endSnap = [],
        public float $distanceMeters = 0.0,
        public array $warnings = [],
        public array $cache = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'data' => [
                'path' => $this->path,
                'start_snap' => $this->startSnap,
                'end_snap' => $this->endSnap,
                'distance_m' => $this->distanceMeters,
                'warnings' => $this->warnings,
                'cache' => $this->cache,
                'routing' => [
                    'engine' => $this->cache['engine'] ?? 'pgrouting',
                    'algorithm' => $this->cache['algorithm'] ?? 'astar',
                ],
                'graph' => $this->cache['graph'] ?? null,
                'indexing' => $this->cache['indexing'] ?? ['status' => 'published'],
            ],
        ];
    }

    public function toGeoJSON(): array
    {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'LineString',
                'coordinates' => $this->path
            ]
        ];
    }
}
