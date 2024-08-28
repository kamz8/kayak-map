<?php

namespace App\Services;

use App\Models\Trail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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
                'points.pointType',
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
}
