<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointResource extends JsonResource
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
            'point_type_id' => $this->point_type_id ?? 0,
            'point_type_key' => $this->pointType->type ?? '',
            'name' => $this->name,
            'description' => $this->description,
            'length_at' => $this->at_length,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
