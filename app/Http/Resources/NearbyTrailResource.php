<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource\BaseTrailResource;
use App\Traits\HasLocationInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NearbyTrailResource extends JsonResource
{
    use HasLocationInfo;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $location = $this->getLocationInfo();

        return [
            'id' => $this->id,
            'name' => $this->trail_name,
            'image' => new ImageResource($this->main_image),
            'rating' => $this->rating,
            'distance' => $this->when(
                isset($this->distance),
                fn() => round($this->distance / 1000, 2) . ' km'
            ),
            'difficulty' => $this->difficulty,
            'slug' => $this->slug,
            'length' => round($this->trail_length / 1000, 2),
            'location_name' => $location['name'],
            'location_type' => $location['type']
        ];
    }


}
