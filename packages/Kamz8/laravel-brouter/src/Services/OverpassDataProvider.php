<?php

namespace Kamz\LaravelBRouter\Services;

use Kamz\LaravelBRouter\Contracts\DataProviderInterface;
use Kamz\LaravelOverpass\Overpass;

class OverpassDataProvider implements DataProviderInterface
{
    public function getWaterwaysInBBox(array $bbox)
    {
        return Overpass::query()
            ->boundingBox($bbox['south'], $bbox['west'], $bbox['north'], $bbox['east'])
            ->whereIn('waterway', ['river', 'canal', 'stream'])
            ->orWhere('natural', 'water')
            ->get();
    }

    public function getWaterwayByName(string $name, array $bbox = null)
    {
        $query = Overpass::query()
            ->whereIn('waterway', ['river', 'canal'])
            ->where('name', $name);

        if ($bbox) {
            $query->boundingBox(...$bbox);
        }

        return $query->get();
    }
}
