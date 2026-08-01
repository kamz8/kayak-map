<?php

namespace Kamz\LaravelBRouter\Services;

use Illuminate\Support\Facades\DB;
use Kamz\LaravelBRouter\Contracts\DataProviderInterface;

class PublishedImportDataProvider implements DataProviderInterface
{
    private const CONNECTION = 'brouter';

    public function getWaterwaysInBBox(array $bbox): array
    {
        return $this->query()->get()->map(fn (object $waterway): array => $this->element($waterway))->all();
    }

    public function getWaterwayByName(string $name, ?array $bbox = null): array
    {
        return $this->query()->where('name', $name)->get()->map(fn (object $waterway): array => $this->element($waterway))->all();
    }

    private function query(): \Illuminate\Database\Query\Builder
    {
        return DB::connection(self::CONNECTION)->table('waterways')
            ->join('imports', 'imports.id', '=', 'waterways.import_id')
            ->where('imports.status', 'published')
            ->where('imports.is_active', true)
            ->select([
                'waterways.id',
                'waterways.name',
                'waterways.waterway',
                'waterways.source_tags',
                DB::raw('ST_AsGeoJSON(waterways.geometry) AS geometry_json'),
            ]);
    }

    private function element(object $waterway): array
    {
        $geometry = json_decode((string) $waterway->geometry_json, true, 512, JSON_THROW_ON_ERROR);

        return [
            'type' => 'way',
            'id' => $waterway->id,
            'tags' => is_string($waterway->source_tags) ? json_decode($waterway->source_tags, true, 512, JSON_THROW_ON_ERROR) : $waterway->source_tags,
            'geometry' => array_map(fn (array $coordinate): array => ['lon' => $coordinate[0], 'lat' => $coordinate[1]], $geometry['coordinates']),
        ];
    }
}
