<?php

namespace App\Services;

use App\Models\Trail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class TrailService
{
    public function getTrails(array $filters): Collection
    {
        $query = Trail::with(['riverTrack', 'images', 'regions']);

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
                'images',
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
    public function getNearbyTrails(float $latitude, float $longitude, ?string $locationName = null)
    {
        $cacheKey = "nearby_trails_{$latitude}_{$longitude}_{$locationName}";

        return Cache::store('redis')->remember($cacheKey, now()->hours(24), function () use ($latitude, $longitude, $locationName) {
            $radius = 50000;

            $query = Trail::selectRaw("
                    trails.*,
                    ST_Distance_Sphere(POINT(?, ?), POINT(trails.start_lat, trails.start_lng)) AS distance
                ", [$latitude, $longitude])
                ->whereRaw('ST_Distance_Sphere(POINT(?, ?), POINT(trails.start_lat, trails.start_lng)) <= ?', [
                    $latitude, $longitude, $radius
                ])
                ->orderBy('distance')
                ->orderByDesc('rating') // Sortowanie po ocenie (najlepsze trasy)
                ->limit(10);

            // Filtrowanie na podstawie nazwy lokalizacji (nazwa regionu)
            if ($locationName) {
                $query->whereHas('regions', function ($q) use ($locationName) {
                    $q->where('name', 'LIKE', "%$locationName%");
                });
            }

            // Zwracamy wyniki z bazy danych
            return $query->get();
        });
    }
}
