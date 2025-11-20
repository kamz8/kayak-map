<?php

namespace Tests\Feature\Api\V1\Dashboard;

use App\Models\Region;
use App\Models\Trail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TrailControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $adminUser;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user with roles
        $this->adminUser = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Create permissions
        $viewPermission = Permission::create(['name' => 'trails.view', 'guard_name' => 'api']);

        // Create and assign admin role
        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'api']);
        $adminRole->givePermissionTo($viewPermission);
        $this->adminUser->assignRole($adminRole);

        // Generate JWT token
        $this->token = JWTAuth::fromUser($this->adminUser);
    }

    protected function tearDown(): void
    {
        $this->adminUser->delete();
        parent::tearDown();
    }

    /**
     * Helper method to make authenticated request
     */
    private function authenticatedGet(string $uri, array $params = [])
    {
        $url = $uri;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $this->getJson($url, [
            'Authorization' => 'Bearer ' . $this->token,
            'X-Client-Type' => 'web',
        ]);
    }

    private function authenticatedPost(string $uri, array $data = [])
    {
        return $this->postJson($uri, $data, [
            'Authorization' => 'Bearer ' . $this->token,
            'X-Client-Type' => 'web',
        ]);
    }

    private function authenticatedPatch(string $uri, array $data = [])
    {
        return $this->patchJson($uri, $data, [
            'Authorization' => 'Bearer ' . $this->token,
            'X-Client-Type' => 'web',
        ]);
    }

    /** @test */
    public function test_can_get_trails_list()
    {
        // Arrange
        $trails = Trail::factory()->count(5)->create();

        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'river_name',
                        'trail_name',
                        'slug',
                        'description',
                        'start_lat',
                        'start_lng',
                        'end_lat',
                        'end_lng',
                        'trail_length',
                        'author',
                        'difficulty',
                        'difficulty_detailed',
                        'scenery',
                        'rating',
                        'status',
                        'created_at',
                        'updated_at',
                        'status_label',
                        'status_color',
                        'difficulty_label',
                        'difficulty_color',
                    ]
                ],
                'links',
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ]
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    /** @test */
    public function test_can_filter_trails_by_status()
    {
        // Arrange
        Trail::factory()->active()->count(3)->create();
        Trail::factory()->inactive()->count(2)->create();
        Trail::factory()->draft()->count(1)->create();

        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'status' => 'active'
        ]);

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertCount(3, $data);
        foreach ($data as $trail) {
            $this->assertEquals('active', $trail['status']);
        }
    }

    /** @test */
    public function test_can_filter_trails_by_multiple_statuses()
    {
        // Arrange
        Trail::factory()->active()->count(3)->create();
        Trail::factory()->inactive()->count(2)->create();
        Trail::factory()->draft()->count(1)->create();

        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'statuses' => ['active', 'inactive']
        ]);

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertCount(5, $data);
        foreach ($data as $trail) {
            $this->assertContains($trail['status'], ['active', 'inactive']);
        }
    }

    /** @test */
    public function test_can_filter_trails_by_difficulty()
    {
        // Arrange
        Trail::factory()->easy()->count(2)->create();
        Trail::factory()->moderate()->count(3)->create();
        Trail::factory()->hard()->count(1)->create();

        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'difficulty' => 'łatwy'
        ]);

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertCount(2, $data);
        foreach ($data as $trail) {
            $this->assertEquals('łatwy', $trail['difficulty']);
        }
    }

    /** @test */
    public function test_can_filter_trails_by_multiple_difficulties()
    {
        // Arrange
        Trail::factory()->easy()->count(2)->create();
        Trail::factory()->moderate()->count(3)->create();
        Trail::factory()->hard()->count(1)->create();

        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'difficulties' => ['łatwy', 'trudny']
        ]);

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertCount(3, $data);
        foreach ($data as $trail) {
            $this->assertContains($trail['difficulty'], ['łatwy', 'trudny']);
        }
    }

    /** @test */
    public function test_can_filter_trails_by_region()
    {
        // Arrange
        $region1 = Region::factory()->create();
        $region2 = Region::factory()->create();

        $trails1 = Trail::factory()->count(3)->create();
        $trails2 = Trail::factory()->count(2)->create();

        // Attach trails to regions
        foreach ($trails1 as $trail) {
            $trail->regions()->attach($region1->id);
        }
        foreach ($trails2 as $trail) {
            $trail->regions()->attach($region2->id);
        }

        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'region_id' => $region1->id
        ]);

        // Assert
        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function test_can_filter_trails_by_date_range()
    {
        // Arrange
        Trail::factory()->count(2)->create(['created_at' => now()->subDays(10)]);
        Trail::factory()->count(3)->create(['created_at' => now()->subDays(5)]);
        Trail::factory()->count(1)->create(['created_at' => now()->subDays(1)]);

        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'start_date' => now()->subDays(6)->format('Y-m-d'),
            'end_date' => now()->subDays(2)->format('Y-m-d'),
        ]);

        // Assert
        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    /** @test */
    public function test_can_search_trails()
    {
        // Arrange
        Trail::factory()->create([
            'trail_name' => 'Dunajec Kayak Trail',
            'river_name' => 'Dunajec',
        ]);
        Trail::factory()->create([
            'trail_name' => 'Wisła Adventure',
            'river_name' => 'Wisła',
        ]);
        Trail::factory()->create([
            'trail_name' => 'Odra River Path',
            'river_name' => 'Odra',
        ]);

        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'search' => 'Dunajec'
        ]);

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertStringContainsString('Dunajec', $data[0]['trail_name']);
    }

    /** @test */
    public function test_can_filter_trails_by_scenery_rating()
    {
        // Arrange
        Trail::factory()->count(2)->create(['scenery' => 3]);
        Trail::factory()->count(3)->create(['scenery' => 7]);
        Trail::factory()->count(1)->create(['scenery' => 9]);

        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'min_scenery' => 7,
            'max_scenery' => 10,
        ]);

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertCount(4, $data);
        foreach ($data as $trail) {
            $this->assertGreaterThanOrEqual(7, $trail['scenery']);
            $this->assertLessThanOrEqual(10, $trail['scenery']);
        }
    }

    /** @test */
    public function test_can_filter_trails_by_trail_length()
    {
        // Arrange
        Trail::factory()->create(['trail_length' => 5000]);
        Trail::factory()->create(['trail_length' => 15000]);
        Trail::factory()->create(['trail_length' => 25000]);

        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'min_length' => 10000,
            'max_length' => 30000,
        ]);

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertCount(2, $data);
        foreach ($data as $trail) {
            $this->assertGreaterThanOrEqual(10000, $trail['trail_length']);
            $this->assertLessThanOrEqual(30000, $trail['trail_length']);
        }
    }

    /** @test */
    public function test_can_filter_trails_by_author()
    {
        // Arrange
        Trail::factory()->create(['author' => 'John Doe']);
        Trail::factory()->create(['author' => 'Jane Smith']);
        Trail::factory()->create(['author' => 'John Brown']);

        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'author' => 'John'
        ]);

        // Assert
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function test_can_combine_multiple_filters()
    {
        // Arrange
        Trail::factory()->active()->easy()->count(2)->create(['scenery' => 8]);
        Trail::factory()->active()->moderate()->count(1)->create(['scenery' => 9]);
        Trail::factory()->inactive()->easy()->count(1)->create(['scenery' => 8]);

        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'status' => 'active',
            'difficulty' => 'łatwy',
            'min_scenery' => 7,
        ]);

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertCount(2, $data);
        foreach ($data as $trail) {
            $this->assertEquals('active', $trail['status']);
            $this->assertEquals('łatwy', $trail['difficulty']);
            $this->assertGreaterThanOrEqual(7, $trail['scenery']);
        }
    }

    /** @test */
    public function test_can_paginate_trails()
    {
        // Arrange
        Trail::factory()->count(25)->create();

        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'per_page' => 10,
            'page' => 1,
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'current_page' => 1,
                    'per_page' => 10,
                    'total' => 25,
                    'last_page' => 3,
                ]
            ]);

        $this->assertCount(10, $response->json('data'));
    }

    /** @test */
    public function test_can_sort_trails()
    {
        // Arrange
        Trail::factory()->create(['trail_name' => 'Zebra Trail', 'created_at' => now()->subDays(1)]);
        Trail::factory()->create(['trail_name' => 'Alpha Trail', 'created_at' => now()->subDays(2)]);
        Trail::factory()->create(['trail_name' => 'Beta Trail', 'created_at' => now()->subDays(3)]);

        // Act - Sort by trail_name ascending
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'sort_by' => 'trail_name',
            'sort_order' => 'asc',
        ]);

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertEquals('Alpha Trail', $data[0]['trail_name']);
        $this->assertEquals('Beta Trail', $data[1]['trail_name']);
        $this->assertEquals('Zebra Trail', $data[2]['trail_name']);
    }

    /** @test */
    public function test_can_get_single_trail()
    {
        // Arrange
        $trail = Trail::factory()->create();

        // Act
        $response = $this->authenticatedGet("/api/v1/dashboard/trails/{$trail->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'river_name',
                    'trail_name',
                    'slug',
                    'description',
                    'start_lat',
                    'start_lng',
                    'end_lat',
                    'end_lng',
                    'trail_length',
                    'author',
                    'difficulty',
                    'difficulty_detailed',
                    'scenery',
                    'rating',
                    'status',
                    'created_at',
                    'updated_at',
                    'status_label',
                    'status_color',
                    'difficulty_label',
                    'difficulty_color',
                ]
            ])
            ->assertJson([
                'data' => [
                    'id' => $trail->id,
                    'trail_name' => $trail->trail_name,
                    'slug' => $trail->slug,
                ]
            ]);
    }

    /** @test */
    public function test_can_get_trail_statistics()
    {
        // Arrange
        Trail::factory()->active()->count(10)->create();
        Trail::factory()->inactive()->count(3)->create();
        Trail::factory()->draft()->count(2)->create();
        Trail::factory()->easy()->count(5)->create();
        Trail::factory()->moderate()->count(7)->create();
        Trail::factory()->hard()->count(3)->create();

        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails/statistics');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'total',
                'by_status' => [
                    'active',
                    'inactive',
                    'draft',
                    'archived',
                ],
                'by_difficulty' => [
                    'easy',
                    'moderate',
                    'hard',
                ],
                'averages' => [
                    'rating',
                    'scenery',
                ],
                'total_length_km',
                'recent_count',
            ]);

        $data = $response->json();
        $this->assertEquals(15, $data['total']);
        $this->assertEquals(10, $data['by_status']['active']);
        $this->assertEquals(3, $data['by_status']['inactive']);
        $this->assertEquals(2, $data['by_status']['draft']);
    }

    /** @test */
    public function test_can_change_trail_status()
    {
        // Arrange
        $trail = Trail::factory()->active()->create();

        // Act
        $response = $this->authenticatedPatch("/api/v1/dashboard/trails/{$trail->id}/status", [
            'status' => 'inactive'
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Status szlaku został zaktualizowany',
                'data' => [
                    'status' => 'inactive'
                ]
            ]);

        $this->assertDatabaseHas('trails', [
            'id' => $trail->id,
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function test_cannot_change_status_to_invalid_value()
    {
        // Arrange
        $trail = Trail::factory()->create();

        // Act
        $response = $this->authenticatedPatch("/api/v1/dashboard/trails/{$trail->id}/status", [
            'status' => 'invalid_status'
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function test_requires_authentication_for_trails_list()
    {
        // Act
        $response = $this->getJson('/api/v1/dashboard/trails');

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function test_requires_authentication_for_single_trail()
    {
        // Arrange
        $trail = Trail::factory()->create();

        // Act
        $response = $this->getJson("/api/v1/dashboard/trails/{$trail->id}");

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function test_requires_authentication_for_statistics()
    {
        // Act
        $response = $this->getJson('/api/v1/dashboard/trails/statistics');

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function test_requires_authentication_for_status_change()
    {
        // Arrange
        $trail = Trail::factory()->create();

        // Act
        $response = $this->patchJson("/api/v1/dashboard/trails/{$trail->id}/status", [
            'status' => 'inactive'
        ]);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function test_returns_404_for_non_existent_trail()
    {
        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails/99999');

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function test_validates_pagination_parameters()
    {
        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'per_page' => 150, // Exceeds max of 100
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);
    }

    /** @test */
    public function test_validates_date_range_parameters()
    {
        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'start_date' => '2024-12-31',
            'end_date' => '2024-01-01', // End before start
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    /** @test */
    public function test_can_load_relationships_with_with_parameter()
    {
        // Arrange
        $region = Region::factory()->create();
        $trail = Trail::factory()->create();
        $trail->regions()->attach($region->id);

        // Act
        $response = $this->authenticatedGet('/api/v1/dashboard/trails', [
            'with' => ['regions']
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'regions' => [
                            '*' => [
                                'id',
                                'name',
                                'slug',
                            ]
                        ]
                    ]
                ]
            ]);
    }
}