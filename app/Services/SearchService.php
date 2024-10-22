<?php

namespace App\Services;

use App\Models\Trail;
use App\Models\Region;
use App\Enums\RegionType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

/**
 * Service class for handling search operations across trails and regions.
 */
class SearchService
{
    /**
     * Cache tag for search results.
     */
    private const CACHE_TAG = 'search_results';
    /**
     * Perform a search operation.
     *
     * @param string $query The search query.
     * @param string $type The type of search (all, trail, or a RegionType value).
     * @param int $limit The maximum number of results to return.
     * @return Collection The search results.
     */
    public function search(string $query, string $type = 'all', int $limit = 50): Collection
    {
        if (empty($query)) {
            return $this->getEmptySearchResult();
        }

        $cacheTtl = Config::get('search.cache_ttl', 3600);

        return Cache::tags([self::CACHE_TAG])->remember("search:{$query}:{$type}:{$limit}", $cacheTtl, function () use ($query, $type, $limit) {
            $results = collect();

            if ($type === 'all' || $type === 'trail') {
                $results = $results->concat($this->searchTrails($query));
            }

            if ($type === 'all' || RegionType::tryFrom($type)) {
                $results = $results->concat($this->searchRegions($query, $type));
            }

            return $results
                ->sortByDesc('relevance')
                ->take($limit);
        });
    }

    /**
     * Search for trails.
     *
     * @param string $query The search query.
     * @return Collection Collection of formatted trail results.
     */
    private function searchTrails(string $query): Collection
    {
        return Trail::query()
            ->with('regions') // Eager load the region relationship
            ->where(function (Builder $builder) use ($query) {
                $builder->where('trail_name', 'like', "%{$query}%")
                    ->orWhere('river_name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhereHas('regions', function (Builder $regionQuery) use ($query) {
                        $regionQuery->where('name', 'like', "%{$query}%");
                    });
            })
            ->get()
            ->map(fn ($trail) => $this->formatResult($trail, 'trail'))
            ->each(fn ($item) => $item['relevance'] = $this->calculateRelevance($item, $query));
    }

    /**
     * Search for regions.
     *
     * @param string $query The search query.
     * @param string $type The type of region to search for (or 'all').
     * @return Collection Collection of formatted region results.
     */
    private function searchRegions(string $query, string $type = 'all'): Collection
    {
        $regionQuery = Region::query()
            ->with('parent') // Eager load the parent relationship
            ->where('name', 'like', "%{$query}%");

        if ($type !== 'all') {
            $regionQuery->where('type', RegionType::from($type));
        }

        return $regionQuery->get()
            ->map(fn ($region) => $this->formatResult($region, $region->type))
            ->each(fn ($item) => $item['relevance'] = $this->calculateRelevance($item, $query));
    }

    /**
     * Format a search result.
     *
     * @param Trail|Region $item The item to format.
     * @param string|RegionType $type The type of the item.
     * @return array The formatted result.
     */
    /**
     * Format a search result.
     *
     * @param Trail|Region $item The item to format.
     * @param string|RegionType $type The type of the item.
     * @param string|null $stateName The state name (for Trail).
     * @param string|null $countryName The country name (for Trail).
     * @return array The formatted result.
     */
    private function formatResult($item, string|RegionType $type): array
    {
        $typeString = $type instanceof RegionType ? $type->value : $type;
        $location = $this->formatLocation($item, $typeString);

        $result = [
            'id' => $item->id,
            'name' => $typeString === 'trail' ? $item->trail_name : $item->name,
            'type' => $typeString,
            'icon' => $this->getIconForType($typeString),
            'location' => $location,
        ];

        if ($typeString !== 'country') {
            $result['state_name'] = $this->getStateName($item, $typeString);
            $result['country_name'] = $this->getCountryName($item, $typeString);
        }

        $result['slug'] = $this->generateSlug($item, $typeString, $location, $result['state_name'] ?? null, $result['country_name'] ?? null);

        return $result;
    }
    private function getStateName($item, string $type): ?string
    {
        if ($type === 'trail') {
            return $item->region?->parent?->name;
        } elseif ($type === 'city' || $type === 'geographic_area') {
            return $item->parent?->name;
        } elseif ($type === 'state') {
            return $item->name;
        }
        return null;
    }

    private function getCountryName($item, string $type): ?string
    {
        if ($type === 'trail') {
            $region = $item->region;
        } elseif (in_array($type, ['city', 'geographic_area', 'state'])) {
            $region = $item;
        } else {
            return null;
        }

        while ($region && $region->type !== RegionType::COUNTRY) {
            $region = $region->parent;
        }

        return $region ? $region->name : null;
    }


    /**
     * Generate a hierarchical slug for the item.
     *
     * @param Trail|Region $item The item to generate slug for.
     * @param string $type The type of the item.
     * @param string $location The formatted location string.
     * @param string|null $stateName The state name (for Trail).
     * @param string|null $countryName The country name (for Trail).
     * @return string The generated slug.
     */
    private function generateSlug($item, string $type, string $location, ?string $stateName = null, ?string $countryName = null): string
    {
        $slugParts = [];

        if ($countryName) {
            $slugParts[] = Str::slug($countryName);
        }

        if ($stateName) {
            $slugParts[] = Str::slug($stateName);
        }

        if ($type === 'trail') {
            // Dla szlaku, dodaj nazwę miasta lub obszaru geograficznego, jeśli istnieje
            $cityOrArea = $item->region?->name;
            if ($cityOrArea) {
                $slugParts[] = Str::slug($cityOrArea);
            }
        } elseif (in_array($type, ['city', 'geographic_area'])) {
            $slugParts[] = Str::slug($item->name);
        }

        // Dodaj nazwę szlaku na końcu dla typu 'trail'
        if ($type === 'trail') {
            $slugParts[] = Str::slug($item->trail_name);
        }

        return implode('/', $slugParts);
    }


    /**
     * Generate a slug for a trail.
     *
     * @param Trail $trail The trail to generate slug for.
     * @param string|null $stateName The state name.
     * @param string|null $countryName The country name.
     * @return string The generated slug.
     */
    private function generateTrailSlug(Trail $trail, ?string $stateName, ?string $countryName): string
    {
        $slugParts = [];

        if ($countryName) {
            $slugParts[] = Str::slug($countryName);
        }

        if ($stateName) {
            $slugParts[] = Str::slug($stateName);
        }

        // Get the first associated region (city or geographic area)
        $associatedRegion = $trail->regions()->whereIn('type', [RegionType::CITY, RegionType::GEOGRAPHIC_AREA])->first();

        if ($associatedRegion) {
            $slugParts[] = Str::slug($associatedRegion->name);
        }

        $slugParts[] = Str::slug($trail->trail_name);

        return implode('/', $slugParts);
    }

    /**
     * Format the location string based on the item type and hierarchy.
     *
     * @param Trail|Region $item The item to format location for.
     * @param string $type The type of the item.
     * @return string The formatted location string.
     */
    private function formatLocation($item, string $type): string
    {
        if ($type === 'trail') {
            $region = $item->region;
        } else {
            $region = $item;
        }

        $locationParts = [];
        while ($region) {
            array_unshift($locationParts, $region->name);
            $region = $region->parent;
        }

        return implode(', ', $locationParts);
    }

    /**
     * Get the full region hierarchy as a string.
     *
     * @param Region|null $region The region to get hierarchy for.
     * @return string The formatted region hierarchy.
     */
    private function getRegionHierarchy(?Region $region): string
    {
        if (!$region) {
            return '';
        }

        $hierarchy = [$region->name];
        $currentRegion = $region;

        while ($currentRegion->parent) {
            $currentRegion = $currentRegion->parent;
            array_unshift($hierarchy, $currentRegion->name);
        }

        return implode(', ', $hierarchy);
    }

    /**
     * Get the icon for a given type.
     *
     * @param string $type The type to get the icon for.
     * @return string The icon name.
     */
    private function getIconForType(string $type): string
    {
        $icons = Config::get('search.icons', []);
        return $icons[strtolower($type)] ?? $icons['default'];
    }

    /**
     * Calculate the relevance of a search result.
     *
     * @param array $item The search result item.
     * @param string $query The search query.
     * @return float The calculated relevance.
     */
    private function calculateRelevance(array $item, string $query): float
    {
        // Simple relevance calculation based on string similarity
        $nameRelevance = similar_text(strtolower($item['name']), strtolower($query)) / 100;
        $locationRelevance = similar_text(strtolower($item['location']), strtolower($query)) / 100;
        return max($nameRelevance, $locationRelevance);
    }

    /**
     * Get an empty search result.
     *
     * @return Collection A collection with a single empty result.
     */
    private function getEmptySearchResult(): Collection
    {
        return collect([
            [
                'id' => null,
                'name' => 'No results',
                'type' => 'empty',
                'icon' => Config::get('search.icons.empty', 'mdi-magnify-off'),
                'location' => '',
                'slug' => '',
                'relevance' => 0,
            ]
        ]);
    }
    /**
     * Get an empty search result.
     *
     * @return void Util method for flush cache by tag.
     */
    public function clearSearchCache(?string $type = null): void
    {
        if ($type) {
            $cacheTag = self::CACHE_TAG . ':' . $type;
            Cache::tags([$cacheTag])->flush();
        } else {
            Cache::tags([self::CACHE_TAG])->flush();
        }
    }
}
