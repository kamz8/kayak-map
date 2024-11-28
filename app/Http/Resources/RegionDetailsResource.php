<?php

namespace App\Http\Resources;

use App\Services\RegionService;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionDetailsResource extends JsonResource
{

    public function toArray($request): array
    {
        $regionService = app(RegionService::class);
        $data = [
            'id' => $this->region->id,
            'name' => $this->region->name,
            'slug' => $this->region->slug,
            'type' => $this->region->type,
            'is_root' => $this->region->is_root,
        ];

        // Dodawanie opcjonalnych pól tylko jeśli istnieją
        if ($this->region->center_point) {
            $data['center'] = $this->region->center_point;
        }

        if ($this->bounds) {
            $data['bounds'] = $this->bounds;
        }

        if ($this->region->area) {
            $data['area'] = $this->region->area;
        }

        // Dodawanie relacji
        $data['images'] = ImageResource::collection($this->region->images);
        $data['main_image'] = new ImageResource($this->region->mainImage);
        $data['links'] = LinkResource::collection($this->region->links);
        $data['breadcrumbs'] = $regionService->getRegionPath($this->region);

        // Statystyki
        $data['statistics'] = [
            'total_trails' => $this->statistics->total_trails ?? 0,
            'avg_rating' => $this->statistics->avg_rating ?? 0,
            'total_length' => $this->statistics->total_length ?? 0,
            'difficulty_stats' => [
                'easy' => $this->statistics->easy_trails ?? 0,
                'moderate' => $this->statistics->moderate_trails ?? 0,
                'hard' => $this->statistics->hard_trails ?? 0
            ],
            'avg_scenery' => $this->statistics->avg_scenery ?? 0
        ];

        if ($this->nearbyRegions->isNotEmpty()) {
            $data['nearby_regions'] = RegionResource::collection($this->nearbyRegions);
        }

        return $data;
    }

}
