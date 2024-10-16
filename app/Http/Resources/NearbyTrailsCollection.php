<?php

namespace App\Http\Resources;

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class NearbyTrailsCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'status' => 'success',
            'data' => NearbyTrailResource::collection($this->collection),
            'message' => 'Top 10 nearby trails retrieved successfully.',
            'total_trails' => $this->collection->count(), // dodatkowe pole
        ];
    }
}

