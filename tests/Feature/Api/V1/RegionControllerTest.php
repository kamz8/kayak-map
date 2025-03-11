<?php

namespace Tests\Feature\Api\V1;

use App\Models\Region;
use App\Models\Trail;
use App\Enums\RegionType;
use App\Services\RegionService;
use Tests\TestCase;

class RegionControllerTest extends TestCase
{
    private string $apiEndpoint = 'api/v1/regions/country';
    private Region $polandRegion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->polandRegion = Region::where('slug', 'polska')
            ->where('type', RegionType::COUNTRY)
            ->firstOrFail();
    }

    public function test_endpoint_returns_404_for_nonexistent_country(): void
    {
        $response = $this->getJson("{$this->apiEndpoint}/nieistniejacy-kraj");
        $response->assertNotFound();
    }

    public function test_endpoint_returns_paginated_regions_for_country(): void
    {
        // Act
        $response = $this->getJson("{$this->apiEndpoint}/polska");

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'type',
                        'total_trails_count',
                        'parent_name',
                        'full_path'
                    ]
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'has_more_pages'
                ],
                'statistics' => [
                    'types_count',
                    'total_trails'
                ]
            ])
            ->assertJsonCount(15, 'data'); // default pagination
    }

    public function test_respects_per_page_parameter(): void
    {
        $response = $this->getJson("{$this->apiEndpoint}/polska?per_page=20");

        $response->assertOk()
            ->assertJsonCount(20, 'data');
    }

    public function test_enforces_maximum_per_page_limit(): void
    {
        $response = $this->getJson("{$this->apiEndpoint}/polska?per_page=100");

        $response->assertOk()
            ->assertJsonCount(50, 'data'); // Max limit
    }

    public function test_statistics_are_correct(): void
    {
        // Act
        $response = $this->getJson("{$this->apiEndpoint}/polska");

        $data = $response->json();

        // Assert
        $this->assertArrayHasKey('statistics', $data);
        $this->assertArrayHasKey('types_count', $data['statistics']);
        $this->assertArrayHasKey('total_trails', $data['statistics']);

        // Sprawdź czy liczby się zgadzają z danymi testowymi
        $stats = $data['statistics'];
        $this->assertGreaterThan(0, $stats['types_count']['geographic_area'] ?? 0, 'No national parks found');
        $this->assertGreaterThan(0, $stats['types_count']['state'] ?? 0, 'No voivodeships found');
    }

    public function test_returns_proper_error_response_on_server_error(): void
    {
        // Arrange
        $this->mock(RegionService::class, function ($mock) {
            $mock->shouldReceive('getFlatRegionsForCountry')
                ->andThrow(new \Exception('Test error'));
        });

        // Act
        $response = $this->getJson("{$this->apiEndpoint}/polska");

        // Assert
        $response->assertStatus(500)
            ->assertJsonStructure([
                'message',
                'error'
            ]);
    }
}
