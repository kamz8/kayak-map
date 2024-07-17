<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'description' => $this->description,
            'start_lat' => $this->start_lat,
            'start_lng' => $this->start_lng,
            'end_lat' => $this->end_lat,
            'end_lng' => $this->end_lng,
            'trail_length' => $this->trail_length,
            'author' => $this->author,
            'difficulty' => $this->difficulty,
            'scenery' => $this->scenery,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'river_track' => new RiverTrackResource($this->riverTrack),
            'sections' => SectionResource::collection($this->sections),
            'points' => PointResource::collection($this->points),
        ];
    }
}
