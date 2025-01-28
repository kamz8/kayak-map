<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SimpleRegionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'main_image' => $this->when($this->main_image, [
                'path' => $this->main_image?->path
            ]),
            'total_trails_count' => $this->trails_count ?? 0,
            'parent_name' => $this->parent_name,
            'full_path' => $this->full_path // full path from region slugs
        ];
    }
}
