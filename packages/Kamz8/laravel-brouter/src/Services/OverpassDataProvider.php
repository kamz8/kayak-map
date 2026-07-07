<?php

namespace Kamz\LaravelBRouter\Services;

use Kamz8\LaravelOverpass\Overpass;
use Kamz\LaravelBRouter\Exceptions\OverpassException;
use Kamz\LaravelBRouter\Contracts\DataProviderInterface;
use Throwable;

class OverpassDataProvider implements DataProviderInterface
{
    public function __construct(private readonly Overpass $overpass) {}

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

    private function fetch(string $query): array
    {
        try {
            $result = $this->overpass->raw($query)->get();
        } catch (Throwable $exception) {
            throw new OverpassException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        return is_array($result) ? $result : [];
    }

    private function buildWaterwaysQuery(array $bbox): string
    {
        $bboxString = $this->formatBbox($bbox);

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
}
