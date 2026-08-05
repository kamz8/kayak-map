<?php

namespace Kamz\LaravelBRouter\DTO;

class PgRouteResultData
{
    public function __construct(
        public array $path,
        public array $startSnap,
        public array $endSnap,
        public float $distanceMeters,
        public int $importId,
        public array $cache = [],
    ) {
    }
}
