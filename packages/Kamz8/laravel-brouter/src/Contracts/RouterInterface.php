<?php

namespace Kamz\LaravelBRouter\Contracts;

interface RouterInterface
{
    public function findRoute(array $start, array $end, string $profile = null);
    public function findNearestWaterway(array $point, float $maxDistance = null);
    public function getRouteStatistics(array $start, array $end);
}
