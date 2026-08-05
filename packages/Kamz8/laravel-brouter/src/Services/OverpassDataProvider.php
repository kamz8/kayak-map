<?php

namespace Kamz\LaravelBRouter\Services;

use Kamz8\LaravelOverpass\Overpass;
use Kamz\LaravelBRouter\Exceptions\OverpassException;
use Kamz\LaravelBRouter\Contracts\DataProviderInterface;
use Kamz\LaravelBRouter\Contracts\ImportDataProviderInterface;
use Throwable;

class OverpassDataProvider implements DataProviderInterface, ImportDataProviderInterface
{
    public function __construct(private readonly ?Overpass $overpass = null) {}

    public function getImportData(array $bbox): array
    {
        return $this->fetch($this->buildImportQuery($this->validatedBbox($bbox)));
    }

    public function getWaterwaysInBBox(array $bbox): array
    {
        return $this->fetch($this->buildWaterwaysQuery($bbox));
    }

    public function getWaterwayByName(string $name, ?array $bbox = null): array
    {
        if ($bbox === null) {
            throw new OverpassException('Bounding box is required for named waterway lookup.');
        }

        return $this->fetch($this->buildNamedWaterwayQuery($name, $bbox));
    }

    public function getNamedImportData(string $name, array $bbox): array
    {
        $bboxString = $this->formatBbox($this->validatedBbox($bbox));
        $escapedName = str_replace('"', '\\"', $name);

        return $this->fetch(<<<OVERPASS
[out:json][timeout:60];
(
  way["waterway"]["name"="{$escapedName}"]({$bboxString});
);
out geom;
OVERPASS);
    }

    private function fetch(string $query): array
    {
        try {
            $result = ($this->overpass ?? app(Overpass::class))->raw($query)->get();
        } catch (Throwable $exception) {
            throw new OverpassException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        return is_array($result) ? $result : [];
    }

    private function buildWaterwaysQuery(array $bbox): string
    {
        $bboxString = $this->formatBbox($this->validatedBbox($bbox));

        return <<<OVERPASS
[out:json][timeout:60];
(
  way["waterway"]({$bboxString});
  relation["waterway"]({$bboxString});
);
out geom;
OVERPASS;
    }

    private function buildNamedWaterwayQuery(string $name, array $bbox): string
    {
        $escapedName = addslashes($name);
        $bboxString = $this->formatBbox($bbox);

        return <<<OVERPASS
[out:json][timeout:60];
(
  way["waterway"]["name"="{$escapedName}"]({$bboxString});
  relation["waterway"]["name"="{$escapedName}"]({$bboxString});
);
out geom;
OVERPASS;
    }

    private function formatBbox(array $bbox): string
    {
        return implode(',', [
            $bbox['south'],
            $bbox['west'],
            $bbox['north'],
            $bbox['east'],
        ]);
    }

    private function buildImportQuery(array $bbox): string
    {
        $bboxString = $this->formatBbox($bbox);
        $featureTags = implode('|', array_map('preg_quote', config('brouter.overpass.feature_tags', [])));

        return <<<OVERPASS
[out:json][timeout:60];
(
  node["waterway"]({$bboxString});
  way["waterway"]({$bboxString});
  relation["waterway"]({$bboxString});
  node["waterway"~"^({$featureTags})$"]({$bboxString});
  way["waterway"~"^({$featureTags})$"]({$bboxString});
  relation["waterway"~"^({$featureTags})$"]({$bboxString});
  node["barrier"~"^(dam|weir)$"]({$bboxString});
  way["barrier"~"^(dam|weir)$"]({$bboxString});
  relation["barrier"~"^(dam|weir)$"]({$bboxString});
  node["lock"="yes"]({$bboxString});
  way["lock"="yes"]({$bboxString});
  relation["lock"="yes"]({$bboxString});
  way["natural"="water"]({$bboxString});
  relation["natural"="water"]({$bboxString});
  way["water"~"^(reservoir|lake)$"]({$bboxString});
  relation["water"~"^(reservoir|lake)$"]({$bboxString});
);
out geom;
OVERPASS;
    }

    private function validatedBbox(array $bbox): array
    {
        foreach (['south', 'west', 'north', 'east'] as $key) {
            if (! isset($bbox[$key]) || ! is_numeric($bbox[$key])) {
                throw new OverpassException('Bounding box must contain numeric south, west, north and east values.');
            }
        }

        $validated = array_map('floatval', $bbox);

        if ($validated['south'] >= $validated['north'] || $validated['west'] >= $validated['east']) {
            throw new OverpassException('Bounding box minimums must be smaller than maximums.');
        }

        if ($validated['south'] < -90 || $validated['north'] > 90 || $validated['west'] < -180 || $validated['east'] > 180) {
            throw new OverpassException('Bounding box coordinates are outside the valid coordinate range.');
        }

        return $validated;
    }
}
