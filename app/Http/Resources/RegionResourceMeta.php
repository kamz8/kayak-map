<?php

namespace App\Http\Resources;

use App\Enums\RegionType;
use App\Helpers\GeoHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionResourceMeta extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'type_label'=> RegionType::from($this->type),
            'parent_id' => $this->parent_id,
            'is_root' => $this->is_root,
            'center_point' => $this->center_point ? [
                'latitude' => $this->center_point->latitude,
                'longitude' => $this->center_point->longitude,
            ] : null,
            'area' => $this->area ? GeoHelper::formatArea($this->area) : null,
        ];
    }


}
