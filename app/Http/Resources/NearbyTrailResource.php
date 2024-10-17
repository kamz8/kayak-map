<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NearbyTrailResource extends JsonResource
{
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
            'name' => $this->trail_name,
            'image' => $this->main_image,
            'rating' => $this->rating,
            'distance' => $this->formatDistance($this->distance) ?? 0,
            'difficulty' => $this->difficulty,
            'slug' => $this->slug,
            'length' => round($this->trail_length / 1000, 2),
            'location_name' => $location['name'],
            'location_type' => $location['type'],
        ];
    }

    /**
     * Format the distance.
     *
     * @param float|null $distance
     * @return string|null
     */
    protected function formatDistance(?float $distance): ?string
    {
        if ($distance === null) {
            return null;
        }

        $distanceInKm = $distance / 1000;
        return round($distanceInKm, 2) . ' km';
    }

    /**
     * Get the location information for the trail.
     *
     * @return array
     */
    protected function getLocationInfo(): array
    {
        $regions = $this->regions()
            ->orderBy('type', 'desc')  // Sortujemy od najniższego poziomu (city/geographic_area) do najwyższego (country)
            ->get();

        $locationParts = [];
        $locationType = null;
        $hasCity = false;

        foreach ($regions as $region) {
            if ($region->type === 'city') {
                $hasCity = true;
            }

            if ($hasCity && $region->type === 'geographic_area') {
                continue;  // Pomijamy obszar geograficzny, jeśli mamy już miasto
            }

            $locationParts[] = $region->name;
            if (!$locationType) {
                $locationType = $region->type;
            }
        }

        $locationName = implode(', ', array_reverse($locationParts));

        return [
            'name' => $locationName,
            'type' => $locationType,
        ];
    }
}
