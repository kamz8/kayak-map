<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource\BaseTrailResource;
use App\Traits\HasLocationInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendedTrailResource extends BaseTrailResource
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
            'river_name' => $this->river_name,
            'trail_name' => $this->trail_name,
            'slug' => $this->slug,
            'rating' => $this->rating,
            'main_image' => new ImageResource($this->main_image),
            'trail_length' => $this->trail_length,
            'author' => $this->author,
            'difficulty' => $this->difficulty,
            'scenery' => $this->scenery,
            'length' => $this->trail_length,
            'location_name' => $this->getLocationPath(),
            'location_type' => $location['type'],
            'location_slug'=> $location['slug'],
            'relevance_score' => round($this->relevance_score, 2),
            'distance_from_current' => $this->when(
                isset($this->distance_km),
                fn() => round($this->distance_km, 2) . ' km'
            ),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'location_tags' => $this->whenLoaded('regions', fn() => $this->getLocationTags($this->regions)),
        ];
    }
}
