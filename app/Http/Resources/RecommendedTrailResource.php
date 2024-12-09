<?php

namespace App\Http\Resources;

use App\Http\Resources\BaseResource\BaseTrailResource;
use App\Traits\HasLocationInfo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
/**
 * @OA\Schema(
 *     schema="RecommendedTrailResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="river_name", type="string"),
 *     @OA\Property(property="trail_name", type="string"),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="rating", type="number"),
 *     @OA\Property(property="main_image", ref="#/components/schemas/ImageResource"),
 *     @OA\Property(property="trail_length", type="integer"),
 *     @OA\Property(property="author", type="string"),
 *     @OA\Property(property="difficulty", type="string"),
 *     @OA\Property(property="scenery", type="integer"),
 *     @OA\Property(property="length", type="integer"),
 *     @OA\Property(property="location_name", type="string"),
 *     @OA\Property(property="location_type", type="string"),
 *     @OA\Property(property="location_slug", type="string"),
 *     @OA\Property(property="relevance_score", type="number"),
 *     @OA\Property(property="distance_from_current", type="string"),
 *     @OA\Property(property="images", type="array", @OA\Items(ref="#/components/schemas/ImageResource")),
 *     @OA\Property(property="location_tags", type="array", @OA\Items(type="string"))
 * )
 */
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
