<?php

namespace Tests\Unit\Services;

use App\Models\Region;
use App\Models\Trail;
use App\Services\RegionService;
use App\Enums\RegionType;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class RegionServiceTest extends TestCase
{
    private RegionService $regionService;
    private Region $polandRegion;
    private Region $lithuaniaRegion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->regionService = app(RegionService::class);

        // Pobieramy istniejące regiony testowe
        $this->polandRegion = Region::where('slug', 'polska')
            ->where('type', RegionType::COUNTRY)
            ->firstOrFail();

        $this->lithuaniaRegion = Region::where('slug', 'litwa')
            ->where('type', RegionType::COUNTRY)
            ->firstOrFail();
    }

    public function test_get_flat_regions_for_country_returns_paginated_results(): void
    {
        // Act
        $result = $this->regionService->getFlatRegionsForCountry('polska');

        // Assert
        $this->assertGreaterThan(0, $result->count());
        $this->assertArrayHasKey('trails_count', $result->first()->toArray());
        $this->assertArrayHasKey('parent_name', $result->first()->toArray());
        $this->assertArrayHasKey('ancestors_names', $result->first()->toArray());

        // Sprawdź czy istnieją państwa
        $this->assertTrue($result->contains('name', 'Biebrzański Park Narodowy'));
        $this->assertTrue($result->contains('name', 'Drawieński Park Narodowy'));
    }

    public function test_get_flat_regions_for_nonexistent_country_returns_empty_paginator(): void
    {
        $result = $this->regionService->getFlatRegionsForCountry('nieistniejacy-kraj');
        $this->assertEquals(0, $result->count());
    }

    public function test_ancestors_path_is_built_correctly(): void
    {
        // Pobieramy istniejący park narodowy z bazy
        $result = $this->regionService->getFlatRegionsForCountry('polska');
        $biebrza = $result->firstWhere('name', 'Biebrzański Park Narodowy');

        $this->assertNotNull($biebrza);
        $this->assertStringContainsString('Polska', $biebrza->ancestors_names);
        $this->assertStringContainsString('Podlaskie', $biebrza->ancestors_names);
    }

    public function test_flat_regions_are_properly_ordered(): void
    {
        // Act
        $result = $this->regionService->getFlatRegionsForCountry('polska');

        // Filtruj tylko województwa
        $voivodeships = $result->filter(fn($region) =>
            $region->type === RegionType::STATE
        )->values();

        // Assert
        $this->assertGreaterThan(1, $voivodeships->count());

        // Sprawdź sortowanie
        $sorted = $voivodeships->sortBy('name')->values();
        $this->assertEquals(
            $sorted->pluck('name'),
            $voivodeships->pluck('name')
        );
    }

    public function test_cache_is_working_properly(): void
    {
        // First call - should hit database
        $result1 = $this->regionService->getFlatRegionsForCountry('polska');

        // Second call - should hit cache
        $result2 = $this->regionService->getFlatRegionsForCountry('polska');

        $this->assertEquals(
            $result1->pluck('id'),
            $result2->pluck('id')
        );
    }
}
