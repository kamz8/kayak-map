<?php

namespace App\Services;

use App\Models\Trail;
use Illuminate\Support\Collection;

class TrailService
{
    public function getTrails(array $filters): Collection
    {
        $query = Trail::with(['riverTrack', 'sections', 'points']);

        if (!empty($filters['difficulty'])) {
            $query->where('difficulty', $filters['difficulty']);
        }

        if (!empty($filters['scenery'])) {
            $query->where('scenery', '>=', $filters['scenery']);
        }

        if (!empty($filters['start_lat']) && !empty($filters['end_lat']) && !empty($filters['start_lng']) && !empty($filters['end_lng'])) {
            $query->whereBetween('start_lat', [$filters['start_lat'], $filters['end_lat']])
                ->whereBetween('end_lat', [$filters['start_lat'], $filters['end_lat']])
                ->whereBetween('start_lng', [$filters['start_lng'], $filters['end_lng']])
                ->whereBetween('end_lng', [$filters['start_lng'], $filters['end_lng']]);
        }

        return $query->get();
    }
}
