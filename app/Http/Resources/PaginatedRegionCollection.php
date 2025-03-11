<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PaginatedRegionCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'has_more_pages' => $this->hasMorePages(),
            ],
            'statistics' => [
                'types_count' => $this->collection->groupBy('type')->map->count(),
                'total_trails' => $this->collection->sum('total_trails_count')
            ]
        ];
    }
}
