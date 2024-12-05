<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RecommendedTrailsCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'status' => 'success',
            'data' => RecommendedTrailResource::collection($this->collection),
            'message' => 'Recommended trails retrieved successfully.',
            'total_trails' => $this->collection->count(),
        ];
    }
}
