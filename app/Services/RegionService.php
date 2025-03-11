<?php

namespace App\Services;

use App\Enums\RegionType;
use App\Helpers\CacheKeyGeneratorHelper;
use App\Models\Region;
use App\Models\Trail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\Point;

class RegionService
{
    private const CACHE_TAG_TRAILS = 'trails';
    private const CACHE_TAG_REGIONS = 'regions';
    private const CACHE_TTL = 86400; // 24 godziny

    public function __construct(
        private readonly GeodataService $geodataService
    )
    {
    }

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
        return Region::where('is_root', true)->with('children.children.children')->get();
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

        $radiusInKm = match ($region->type) {
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

    /**
     * Pobiera top 10 najlepszych tras w regionie.
     *
     * @param Region $region
     * @return Collection
     */
    public function getTopTrailsInRegion(Region $region): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = CacheKeyGeneratorHelper::forRequest();

        return Cache::store('redis')
            ->tags([self::CACHE_TAG_TRAILS, self::CACHE_TAG_REGIONS, "region_{$region->id}"])
            ->remember($cacheKey, now()->addSeconds(self::CACHE_TTL), function () use ($region) {
                $query = Trail::query()
                    ->select(['trails.*'])
                    ->with([
                        'riverTrack',
                        'regions',
                        'images'
                    ]);

                // Dodajemy join z trail_region
                $query->join('trail_region', 'trails.id', '=', 'trail_region.trail_id')
                    ->where('trail_region.region_id', $region->id);

                if ($region->center_point instanceof Point) {
                    $query->addSelect([
                        \DB::raw('(
                            6371000 * acos(
                                cos(radians(?)) *
                                cos(radians(start_lat)) *
                                cos(radians(start_lng) - radians(?)) +
                                sin(radians(?)) *
                                sin(radians(start_lat))
                            )
                        ) as distance')
                    ])->addBinding([
                        $region->center_point->latitude,
                        $region->center_point->longitude,
                        $region->center_point->latitude
                    ], 'select');

                    $radius = $this->calculateRegionRadius($region);

                    // Używamy center_point do stworzenia bounding box
                    $boundingBox = $this->geodataService->createBoundingBox(
                        $region->center_point,
                        ($radius / 111000) * 1.5 // Zwiększamy obszar o 50%
                    );

                    $query->where(function($q) use ($boundingBox) {
                        $q->whereBetween('start_lat', [$boundingBox[0][0], $boundingBox[1][0]])
                            ->whereBetween('start_lng', [$boundingBox[0][1], $boundingBox[1][1]])
                            ->orWhereBetween('end_lat', [$boundingBox[0][0], $boundingBox[1][0]])
                            ->orWhereBetween('end_lng', [$boundingBox[0][1], $boundingBox[1][1]]);
                    });

                    $query->orderBy('distance');
                }

                return $query->where('rating', '>=', 0)
                    ->orderByDesc('rating')
                    ->limit(10)
                    ->get();
            });
    }

    /**
     * Czyści cache dla konkretnego regionu
     */
    public function clearRegionCache(Region $region): void
    {
        Cache::store('redis')->tags(["region_{$region->id}"])->flush();
    }

    /**
     * Czyści cały cache tras
     */
    public function clearTrailsCache(): void
    {
        Cache::store('redis')->tags([self::CACHE_TAG_TRAILS])->flush();
    }

    /**
     * Czyści cały cache regionów
     */
    public function clearRegionsCache(): void
    {
        Cache::store('redis')->tags([self::CACHE_TAG_REGIONS])->flush();
    }

    private function calculateRegionRadius(Region $region): float
    {
        return match ($region->type) {
            'country' => 500000,
            'state' => 200000,
            'city', 'geographic_area' => 100000,
            default => 100000,
        };
    }

    public function getFlatRegionsForCountry(string $countrySlug, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        $cacheKey = CacheKeyGeneratorHelper::forRequest(); // generate hash key for request
        return Cache::tags([self::CACHE_TAG_REGIONS])->remember(
            $cacheKey,
            self::CACHE_TTL,
            function () use ($countrySlug, $perPage) {
                $countryId = Region::where('slug', $countrySlug)
                    ->where('type', RegionType::COUNTRY)
                    ->value('id');

                if (!$countryId) {
                    return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
                }

                return Region::query()
                    ->select([
                        'regions.*',
                        DB::raw('(
                        SELECT COUNT(DISTINCT t.id)
                        FROM trails t
                        JOIN trail_region tr ON tr.trail_id = t.id
                        WHERE tr.region_id = regions.id
                    ) as trails_count'),
                        // Poprawiony parent_name - używamy JOIN zamiast subquery
                        DB::raw('p.name as parent_name'),
                        // Poprawiona ścieżka przodków
                        DB::raw("CONCAT_WS(' > ',
                        COALESCE(p2.name, ''),
                        COALESCE(p.name, '')
                    ) as full_path")
                    ])
                    ->leftJoin('regions as p', 'regions.parent_id', '=', 'p.id')
                    ->leftJoin('regions as p2', 'p.parent_id', '=', 'p2.id')
                    ->whereIn('regions.id', function($query) use ($countryId) {
                        $query->select('r.id')
                            ->from('regions as r')
                            ->where(function($q) use ($countryId) {
                                $q->where('r.parent_id', $countryId)
                                    ->orWhereIn('r.parent_id', function($sq) use ($countryId) {
                                        $sq->select('id')
                                            ->from('regions')
                                            ->where('parent_id', $countryId);
                                    });
                            });
                    })
                    ->with(['images' => function($query) {
                        $query->wherePivot('is_main', true);
                    }])
//                    ->orderBy('regions.type')
                    ->orderByDesc('trails_count')
                    ->orderBy('regions.name')
                    ->paginate($perPage);
            }
        );
    }
}
