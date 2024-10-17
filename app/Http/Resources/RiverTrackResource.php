<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiverTrackResource extends JsonResource
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
            'trail_id' => $this->trail_id,
            'track_points' => $this->track_points,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
