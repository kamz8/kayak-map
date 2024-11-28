<?php
Namespace App\Traits;
use App\Enums\RegionType;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait HasLocationInfo
{
    protected function getLocationInfo(): array
    {
        $regions = $this->resource->regions
            ->sortByDesc('type');

        $locationParts = [];
        $locationType = null;
        $hasCity = false;

        foreach ($regions as $region) {
            if ($region->type === 'city') {
                $hasCity = true;
            }

            if ($hasCity && $region->type === 'geographic_area') {
                continue;
            }

            $locationParts[] = $region->name;
            if (!$locationType) {
                $locationType = $region->type;
            }
        }

        return [
            'name' => implode(', ', array_reverse($locationParts)),
            'type' => $locationType,
            'slug'=> $this->getFullSlug()
        ];
    }
    private function getLocationTags(Collection $regions): array
    {
        $region = $regions->first();
        if (!$region) {
            return [
                'country' => null,
                'state' => null,
                'city' => null,
                'geographic_area' => null
            ];
        }

        return [
            'country' => $region->parent?->parent?->name ?? null,
            'state' => $region->parent?->name ?? null,
            'city' => $region->type === RegionType::CITY ? $region->name : null,
            'geographic_area' => $region->type === RegionType::GEOGRAPHIC_AREA ? $region->name : null,
        ];
    }

    protected function getLocationPath(): string
    {
        $regions = $this->resource->regions;
        $mainRegion = $regions->first(fn($r) => $r->type->value === 'geographic_area')
            ?? $regions->first(fn($r) => $r->type->value === 'city')
            ?? $regions->first();

        if (!$mainRegion) {
            return '';
        }

        $locationParts = [$mainRegion->name];

        $parent = $mainRegion->parent;
        while ($parent) {
            $locationParts[] = $parent->name;
            $parent = $parent->parent;
        }

        return implode(' • ', array_reverse($locationParts));
    }

    protected function getFullSlug(): string
    {
        $regions = $this->resource->regions;
        $region = $regions->first();

        if (!$region) {
            return '';
        }

        $slugParts = [];

        // Dodaj slug aktualnego regionu
        $slugParts[] = $region->slug;

        // Iteruj przez rodziców
        $parent = $region->parent;
        while ($parent) {
            $slugParts[] = $parent->slug;
            $parent = $parent->parent;
        }

        return implode('/', array_reverse($slugParts));
    }
}

