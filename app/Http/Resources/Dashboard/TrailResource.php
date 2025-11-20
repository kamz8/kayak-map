<?php

namespace App\Http\Resources\Dashboard;

use App\Http\Resources\ImageResource;
use App\Http\Resources\RegionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="DashboardTrailResource",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="river_name", type="string"),
 *     @OA\Property(property="trail_name", type="string"),
 *     @OA\Property(property="slug", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="start_lat", type="number", format="float"),
 *     @OA\Property(property="start_lng", type="number", format="float"),
 *     @OA\Property(property="end_lat", type="number", format="float"),
 *     @OA\Property(property="end_lng", type="number", format="float"),
 *     @OA\Property(property="trail_length", type="integer"),
 *     @OA\Property(property="author", type="string"),
 *     @OA\Property(property="difficulty", type="string", enum={"łatwy", "umiarkowany", "trudny"}),
 *     @OA\Property(property="difficulty_detailed", type="string"),
 *     @OA\Property(property="scenery", type="integer"),
 *     @OA\Property(property="rating", type="number", format="float"),
 *     @OA\Property(property="status", type="string", enum={"active", "inactive", "draft", "archived"}),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class TrailResource extends JsonResource
{
    /**
     * Transform the resource into an array for dashboard display.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'river_name' => $this->river_name,
            'trail_name' => $this->trail_name,
            'slug' => $this->slug,
            'description' => $this->description,

            // Coordinates
            'start_lat' => $this->start_lat,
            'start_lng' => $this->start_lng,
            'end_lat' => $this->end_lat,
            'end_lng' => $this->end_lng,

            // Trail properties
            'trail_length' => $this->trail_length,
            'author' => $this->author,
            'difficulty' => $this->difficulty?->value ?? $this->difficulty,
            'difficulty_detailed' => $this->difficulty_detailed,
            'scenery' => $this->scenery,
            'rating' => $this->rating,
            'status' => $this->status,

            // Main image
            'main_image' => $this->when($this->main_image, function () {
                return new ImageResource($this->main_image);
            }),

            // Counts for dashboard statistics
            'images_count' => $this->whenCounted('images'),
            'sections_count' => $this->whenCounted('sections'),
            'points_count' => $this->whenCounted('points'),
            'regions_count' => $this->whenCounted('regions'),

            // Relationships (when loaded)
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'regions' => RegionResource::collection($this->whenLoaded('regions')),
            'sections' => $this->whenLoaded('sections', function () {
                return $this->sections->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'name' => $section->name,
                        'description' => $section->description,
                        'scenery' => $section->scenery,
                    ];
                });
            }),
            'points' => $this->whenLoaded('points', function () {
                return $this->points->map(function ($point) {
                    return [
                        'id' => $point->id,
                        'name' => $point->name,
                        'description' => $point->description,
                        'point_type_id' => $point->point_type_id,
                        'at_length' => $point->at_length,
                    ];
                });
            }),

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // Computed properties for dashboard
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
            'difficulty_label' => $this->getDifficultyLabel(),
            'difficulty_color' => $this->getDifficultyColor(),
            'has_river_track' => $this->relationLoaded('riverTrack') && $this->riverTrack !== null,
        ];
    }

    /**
     * Get status label in Polish
     */
    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'active' => 'Aktywny',
            'inactive' => 'Nieaktywny',
            'draft' => 'Wersja robocza',
            'archived' => 'Zarchiwizowany',
            default => 'Nieznany'
        };
    }

    /**
     * Get status color for UI badges
     */
    private function getStatusColor(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'inactive' => 'warning',
            'draft' => 'secondary',
            'archived' => 'default',
            default => 'default'
        };
    }

    /**
     * Get difficulty label in Polish
     */
    private function getDifficultyLabel(): string
    {
        $difficulty = $this->difficulty?->value ?? $this->difficulty;

        return match ($difficulty) {
            'łatwy' => 'Łatwy',
            'umiarkowany' => 'Umiarkowany',
            'trudny' => 'Trudny',
            default => 'Nieznany'
        };
    }

    /**
     * Get difficulty color for UI badges
     */
    private function getDifficultyColor(): string
    {
        $difficulty = $this->difficulty?->value ?? $this->difficulty;

        return match ($difficulty) {
            'łatwy' => 'success',
            'umiarkowany' => 'warning',
            'trudny' => 'destructive',
            default => 'default'
        };
    }
}