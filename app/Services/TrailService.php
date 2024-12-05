<?php

namespace App\Services;

use App\Models\Trail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;
use phpGPX\Models\Point;

class TrailService
{
    public function getTrails(array $filters): Collection
    {
        $query = Trail::with(['riverTrack', 'images', 'regions.parent', 'links']);

        $this->applyDifficultyFilter($query, $filters);
        $this->applySceneryFilter($query, $filters);
        $this->applyRatingFilter($query, $filters);
        $this->applyLengthFilter($query, $filters);
        $this->applySearchFilter($query, $filters);
        $this->applyBoundingBoxFilter($query, $filters);

        return $query->limit(1000)->get();
    }

    public function getTrailDetails(string $slug): Trail
    {
        return Trail::where('slug', $slug)
            ->with([
                'riverTrack',
                'sections.links',
                'points.pointType.pointDescription',
                'images',
                'regions'
            ])
            ->firstOrFail();
    }

    public function getTrailById(int $id): Trail
    {
        return Trail::with([
                'riverTrack',
                'sections.links',
                'points.pointType',
                'images.trails',
                'regions'
            ])
            ->findOrFail($id);
    }

    /*Filters*/
    private function applyDifficultyFilter(Builder $query, array $filters): void
    {
        if (!empty($filters['difficulty']) && is_array($filters['difficulty'])) {
            $query->whereIn('difficulty', $filters['difficulty']);
        }
    }

    private function applySceneryFilter(Builder $query, array $filters): void
    {
        if (!empty($filters['scenery'])) {
            $query->where('scenery', '>=', $filters['scenery']);
        }
    }

    private function applyRatingFilter(Builder $query, array $filters): void
    {
        if (!empty($filters['rating'])) {
            $query->where('rating', '>=', $filters['rating']);
        }
    }

    private function applyLengthFilter(Builder $query, array $filters): void
    {
        if (!empty($filters['min_length'])) {
            $query->where('trail_length', '>=', $filters['min_length']);
        }

        if (!empty($filters['max_length'])) {
            $query->where('trail_length', '<=', $filters['max_length']);
        }
    }

    private function applySearchFilter(Builder $query, array $filters): void
    {
        if (!empty($filters['search_query'])) {
            $searchTerm = '%' . $filters['search_query'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('trail_name', 'like', $searchTerm)
                    ->orWhere('river_name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }
    }

    private function applyBoundingBoxFilter(Builder $query, array $filters): void
    {
        $requiredFields = ['start_lat', 'end_lat', 'start_lng', 'end_lng'];
        if (count(array_intersect_key(array_flip($requiredFields), $filters)) === count($requiredFields)) {
            $query->where(function ($q) use ($filters) {
                $q->where(function ($subQ) use ($filters) {
                    $subQ->whereBetween('start_lat', [
                        min((float)$filters['start_lat'], (float)$filters['end_lat']),
                        max((float)$filters['start_lat'], (float)$filters['end_lat'])
                    ])
                        ->whereBetween('start_lng', [
                            min((float)$filters['start_lng'], (float)$filters['end_lng']),
                            max((float)$filters['start_lng'], (float)$filters['end_lng'])
                        ]);
                })
                    ->orWhere(function ($subQ) use ($filters) {
                        $subQ->whereBetween('end_lat', [
                            min((float)$filters['start_lat'], (float)$filters['end_lat']),
                            max((float)$filters['start_lat'], (float)$filters['end_lat'])
                        ])
                            ->whereBetween('end_lng', [
                                min((float)$filters['start_lng'], (float)$filters['end_lng']),
                                max((float)$filters['start_lng'], (float)$filters['end_lng'])
                            ]);
                    });
            });
        }
    }

    /**
     * Pobiera listę tras w pobliżu na podstawie współrzędnych.
     *
     * @param float $latitude
     * @param float $longitude
     * @param string|null $locationName
     * @return \Illuminate\Support\Collection
     */
    public function getNearbyTrails(?float $latitude = null, ?float $longitude = null, ?string $locationName = null): Collection
    {
        $cacheKey = "nearby_trails_{$latitude}_{$longitude}_{$locationName}";

        return Cache::store('redis')->remember($cacheKey, now()->hours(24), function () use ($latitude, $longitude, $locationName) {
            $query = Trail::query()
                ->with(['riverTrack', 'images', 'regions']);

            if ($latitude !== null && $longitude !== null) {
                $radius = 50000; // 50 km
                $query->select('trails.*')
                    ->selectRaw("
                    ST_Distance_Sphere(
                        POINT(?, ?),
                        POINT(trails.start_lng, trails.start_lat)
                    ) AS distance
                ", [$longitude, $latitude])
                    ->whereRaw('ST_Distance_Sphere(POINT(?, ?), POINT(trails.start_lng, trails.start_lat)) <= ?', [
                        $longitude, $latitude, $radius
                    ])
                    ->orderBy('distance');
            } else {
                $query->orderByDesc('rating');
            }

            if ($locationName) {
                $query->whereHas('regions', function ($q) use ($locationName) {
                    $q->where('name', 'LIKE', "%{$locationName}%");
                });
            }

            return $query->limit(10)->get();
        });
    }

    public function getRecommendedTrails(Trail $trail, int $radius): Collection
    {
/*        return Cache::store('redis')
            ->tags(['trails', 'trails:recommended'])
            ->remember(
                "recommended_trails:{$trail->slug}:radius_{$radius}",
                now()->hours(24),
                fn() => $this->findRecommendedTrails($trail, $radius)
            );*/
        return $this->findRecommendedTrails($trail, $radius);
    }

    private function findRecommendedTrails(Trail $trail, int $radius): Collection
    {
        return Trail::select([
            'trails.*',
            DB::raw('(
               6371 * acos(
                   cos(radians(' . $trail->start_lat . ')) * cos(radians(start_lat)) *
                   cos(radians(start_lng) - radians(' . $trail->start_lng . ')) +
                   sin(radians(' . $trail->start_lat . ')) * sin(radians(start_lat))
               )
           ) as distance_km'),
            DB::raw('(
               rating * 0.6 + (1 - LEAST(
                   6371 * acos(
                       cos(radians(' . $trail->start_lat . ')) * cos(radians(start_lat)) *
                       cos(radians(start_lng) - radians(' . $trail->start_lng . ')) +
                       sin(radians(' . $trail->start_lat . ')) * sin(radians(start_lat))
                   ) / ' . $radius . ', 1
               )) * 0.4
           ) * 5 as relevance_score')
        ])
            ->with(['riverTrack', 'images', 'regions.parent.parent'])
            ->where('slug', '!=', $trail->slug)
            ->where('start_lat', '!=', -1)
            ->where('end_lat', '!=', -1)
            ->having('distance_km', '<', $radius)
            ->orderByDesc('relevance_score')
            ->orderBy('distance_km')
            ->limit(5)
            ->get();
    }


    private function getTopTrailsForRegion(array $bounds, Point $centerPoint, float $radius): Collection
    {
        $cacheKey = "top_trails_region_" . md5(json_encode($bounds) . $centerPoint->toLngLat() . $radius);

        return Cache::store('redis')->remember($cacheKey, now()->hours(24), function () use ($bounds, $centerPoint, $radius) {
            return Trail::query()
                ->select([
                    'trails.*',
                    DB::raw($this->getDistanceQuery($centerPoint))
                ])
                // Poprawiony eager loading z relacjami
                ->with([
                    'riverTrack',
                    'images' => function($query) {
                        $query->wherePivot('is_main', true); // Tylko główne zdjęcie
                    },
                    'regions'
                ])
                ->where(function ($q) use ($bounds) {
                    $this->applyBoundsFilter($q, $bounds);
                })
                ->having('distance', '<=', $radius)
                ->orderBy('distance')
                ->orderByDesc('rating')
                ->where('rating', '>', 0)
                // Zamieniamy whereHas na exists dla lepszej wydajności
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('images')
                        ->join('imageables', 'images.id', '=', 'imageables.image_id')
                        ->whereColumn('imageables.imageable_id', 'trails.id')
                        ->where('imageables.imageable_type', Trail::class)
                        ->where('imageables.is_main', true);
                })
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('river_tracks')
                        ->whereColumn('river_tracks.trail_id', 'trails.id');
                })
                ->limit(10)
                ->get();
        });
    }
}
