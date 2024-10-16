<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NearbyTrailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trail_name' => $this->trail_name,
            'rating' => $this->rating,
            'distance' => round($this->distance / 1000, 2) . ' km',  // Odległość w kilometrach
            'location_name' => $this->location_name,
        ];
    }
}
