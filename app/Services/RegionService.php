<?php

namespace App\Services;

use App\Enums\RegionType;
use App\Models\Region;
use App\Models\Trail;
use Collection;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\Point;

class RegionService
{
    public function getRegions(): \Illuminate\Database\Eloquent\Collection
    {
        return Region::with('children')->where('is_root', true)->get();
    }

    public function findRegionBySlug(string $slug): ?Region
    {
        return Region::where('slug', $slug)->first();
    }

    public function getTrailsInRegion(Region $region): mixed
    {
        return $region->trails;
    }

    public function getRootRegions()
    {
        return Region::where('is_root', true)->get();
    }

    public function getRegionBySlug($slug)
    {
        return Region::where('slug', $slug)->with('children')->firstOrFail();
    }

    public function associateTrailWithRegions(Trail $trail): void
    {
        $regions = Region::all();
        foreach ($regions as $region) {
            if ($this->isTrailInRegion($trail, $region)) {
                if (!$trail->regions()->where('region_id', $region->id)->exists()) {
                    $trail->regions()->attach($region->id);
                }
            }
        }
    }

    private function isTrailInRegion(Trail $trail, Region $region): bool
    {
        // Sprawdź, czy trail ma river_track i czy river_track ma track_points
        if (!$trail->river_track || !isset($trail->river_track->track_points)) {
            return false;
        }

        $trailPoints = json_decode($trail->river_track->track_points, true);

        // Sprawdź, czy dekodowanie JSON się powiodło i czy mamy tablicę punktów
        if (!is_array($trailPoints) || empty($trailPoints)) {
            return false;
        }

        foreach ($trailPoints as $point) {
            // Sprawdź, czy punkt ma prawidłowy format
            if (!isset($point[0]) || !isset($point[1])) {
                continue; // Pomiń nieprawidłowe punkty
            }

            $longitude = $point[0];
            $latitude = $point[1];
            $trailPoint = new \MatanYadaev\EloquentSpatial\Objects\Point($latitude, $longitude);

            // Sprawdź, czy region ma zdefiniowany obszar
            if ($region->area && $region->area->contains($trailPoint)) {
                return true;
            }
        }

        return false;
    }

    public function getMainRegion(Point $centerPoint): ?Region
    {
        return Region::query()
            ->whereNotNull('area')
            ->orderByRaw("CASE
                WHEN type = 'city' THEN 1
                WHEN type = 'geographic_area' THEN 2
                WHEN type = 'state' THEN 3
                WHEN type = 'country' THEN 4
                ELSE 5 END")
            ->whereRaw('ST_Contains(area, ST_GeomFromText(?))', [
                "POINT({$centerPoint->longitude} {$centerPoint->latitude})"
            ])
            ->first();
    }

    /**
     * @param Region|null $region
     * @return string|null
     */
    public function getFullSlug(?Region $region): ?string
    {
        if (!$region) {
            return null;
        }

        $slugParts = [];
        $currentRegion = $region;

        while ($currentRegion) {
            array_unshift($slugParts, $currentRegion->slug);
            $currentRegion = $currentRegion->parent;
        }

        return implode('/', $slugParts);
    }

    /**
     * @param Region|null $region
     * @return array
     */
    public function getRegionPath(?Region $region): array
    {
        if (!$region) {
            return [];
        }

        $path = [];
        $currentRegion = $region;

        while ($currentRegion) {
            array_unshift($path, [
                'name' => $currentRegion->name,
                'type' => $currentRegion->type,
                'slug' => $currentRegion->slug,
            ]);
            $currentRegion = $currentRegion->parent;
        }

        return $path;
    }

    /**
     * @param int $regionId
     * @return object|null
     */
    public function getRegionStatistics(int $regionId): object
    {
        return DB::table('region_trail_statistics')
            ->where('region_id', $regionId)
            ->first();
    }
    /**
     * Get nearby regions of the same type
     *
     * @param Region $region
     * @param float $radius Radius in kilometers
     * @return \Illuminate\Support\Collection
     */
    public function getNearbyRegions(Region $region): \Illuminate\Support\Collection
    {
        if (!$region->center_point) {
            return collect();
        }

        $radiusInKm = match($region->type) {
            RegionType::CITY => 100,  // 50km dla miast
            RegionType::GEOGRAPHIC_AREA => 150,
            RegionType::STATE => 250, // 200km dla województw
            default => 50
        };

        // Najpierw pobieramy ID wszystkich przodków
        /** @var SpatialBuilder $query */
        return Region::query()
            ->whereNotNull('area')
            ->whereNotNull('center_point')
            ->where('id', '!=', $region->id)
            ->where('parent_id', $region->parent_id) // wystarczy ten warunek
            ->whereDistance('center_point', $region->center_point, '<=', $radiusInKm * 1000)
            ->orderByDistance('center_point', $region->center_point)
            ->limit(10)
            ->get();
    }

    public function getRegionDetails(string $slug): object
    {
        $region = $this->getRegionBySlug($slug);

        // Pobieranie z widoku SQL
        $statistics = DB::table('region_trail_statistics')
            ->where('region_id', $region->id)
            ->first();

        // Pobieranie nearby_regions tylko jeśli region ma center_point
        $nearbyRegions = $region->center_point
            ? $this->getNearbyRegions($region)
            : collect();

        // Generowanie bounds tylko jeśli jest center_point
        $bounds = $region->center_point
            ? app(GeodataService::class)->createBoundingBox($region->center_point)
            : null;

        return (object)[
            'region' => $region,
            'statistics' => $statistics ?? (object)[],
            'nearbyRegions' => $nearbyRegions,
            'bounds' => $bounds
        ];
    }

}
