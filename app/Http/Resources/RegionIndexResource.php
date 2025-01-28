<?php

namespace App\Http\Resources;

use App\Helpers\GeoHelper;
use Illuminate\Http\Resources\Json\JsonResource;
use function Webmozart\Assert\Tests\StaticAnalysis\nullOrCount;
/*
 * Build resource for region guide
 * */
class RegionIndexResource extends JsonResource
{
    public function toArray($request): array
    {

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
//            'description' => $this->description ?? '', It could be added in future
            'is_root' => $this->is_root,

            'statistics' => [
                'national_parks_count' => $this->national_parks_count,
                'cities_count' => $this->cities_count,
                'total_trails_count' => $this->total_trails_count,
                'total'=> $this->whenLoaded('children')->count() ?? 0,
            ],

            'main_image' => $this->when($this->main_image, function() {
                return [
                    'id' => $this->main_image->id,
                    'path' => $this->main_image->path,
                ];
            }),

            // Relacje
            'children' => RegionResource::collection($this->whenLoaded('children')),
//            'parent' => new RegionResource($this->whenLoaded('parent')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at

        ];
    }


}
