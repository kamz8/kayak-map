<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $main_image
 * @property mixed $rating
 */
class TrailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'river_name' => $this->river_name,
            'trail_name' => $this->trail_name,
            'slug' => $this->slug,
            'description' => $this->description,
            'main_image' => new ImageResource($this->main_image),
            'start_lat' => $this->start_lat,
            'start_lng' => $this->start_lng,
            'end_lat' => $this->end_lat,
            'end_lng' => $this->end_lng,
            'trail_length' => $this->trail_length,
            'author' => $this->author,
            'difficulty' => $this->difficulty,
            'scenery' => $this->scenery,
            'rating' => $this->rating,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'river_track' => new RiverTrackResource($this->riverTrack),
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
            'points' => PointResource::collection($this->whenLoaded('points')),
            'regions' => RegionResource::collection($this->whenLoaded('regions')),
            'links' => LinkResource::collection($this->links),
        ];
    }
}
