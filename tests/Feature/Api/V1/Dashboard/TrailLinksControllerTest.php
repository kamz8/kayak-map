<?php

namespace Tests\Feature\Api\V1\Dashboard;

use App\Models\Link;
use App\Models\Trail;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class TrailLinksControllerTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    private User $adminUser;
    private string $token;
    private Trail $trail;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user with roles
        $this->adminUser = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Create permissions using firstOrCreate to avoid duplicates
        $viewPermission = Permission::firstOrCreate(
            ['name' => 'trails.view', 'guard_name' => 'api'],
            ['name' => 'trails.view', 'guard_name' => 'api']
        );

        // Create and assign admin role
        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'api'],
            ['name' => 'Admin', 'guard_name' => 'api']
        );

        $adminRole->permissions()->syncWithoutDetaching([$viewPermission->id]);
        $this->adminUser->roles()->sync([$adminRole->id]);

        // Generate JWT token
        $this->token = JWTAuth::fromUser($this->adminUser);

        // Create test trail
        $this->trail = Trail::factory()->create();

        $this->checkDatabaseState();

    }

    // 🎯 USUŃ tearDown - DatabaseTransactions robi automatyczny rollback
    // protected function tearDown(): void
    // {
    //     $this->adminUser->delete();
    //     parent::tearDown();
    // }

    /**
     * Helper method to make authenticated GET request
     */
    private function authenticatedGet(string $uri)
    {
        return $this->getJson($uri, [
            'Authorization' => 'Bearer ' . $this->token,
            'X-Client-Type' => 'web',
        ]);
    }

    /**
     * Helper method to make authenticated POST request
     */
    private function authenticatedPost(string $uri, array $data = [])
    {
        return $this->postJson($uri, $data, [
            'Authorization' => 'Bearer ' . $this->token,
            'X-Client-Type' => 'web',
        ]);
    }

    /**
     * Helper method to make authenticated PUT request
     */
    private function authenticatedPut(string $uri, array $data = [])
    {
        return $this->putJson($uri, $data, [
            'Authorization' => 'Bearer ' . $this->token,
            'X-Client-Type' => 'web',
        ]);
    }

    /**
     * Helper method to make authenticated DELETE request
     */
    private function authenticatedDelete(string $uri)
    {
        return $this->deleteJson($uri, [], [
            'Authorization' => 'Bearer ' . $this->token,
            'X-Client-Type' => 'web',
        ]);
    }

    /** @test */
    public function test_can_get_trail_links_list()
    {
        // Arrange
        $link1 = Link::factory()->youtube()->create();
        $link2 = Link::factory()->facebook()->create();
        $link3 = Link::factory()->wikipedia()->create();

        $this->trail->links()->attach([$link1->id, $link2->id, $link3->id]);

        // Act
        $response = $this->authenticatedGet("/api/v1/dashboard/trails/{$this->trail->id}/links");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'url',
                        'meta_data',
                        'title',
                        'description',
                        'icon',
                        'created_at',
                        'updated_at',
                        'domain'
                    ]
                ],
                'trail' => [
                    'id',
                    'trail_name'
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function test_can_get_empty_links_list_for_trail_without_links()
    {
        // Act
        $response = $this->authenticatedGet("/api/v1/dashboard/trails/{$this->trail->id}/links");

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(0, 'data')
            ->assertJson([
                'trail' => [
                    'id' => $this->trail->id,
                    'trail_name' => $this->trail->trail_name
                ]
            ]);
    }

    /** @test */
    public function test_returns_404_for_nonexistent_trail()
    {
        // Act
        $response = $this->authenticatedGet("/api/v1/dashboard/trails/999999/links");

        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => 404,
                    'message' => 'Szlak nie został znaleziony'
                ]
            ]);
    }

    /** @test */
    public function test_can_create_link_for_trail()
    {
        // Arrange
        $linkData = [
            'url' => 'https://youtube.com/watch?v=test123',
            'meta_data' => json_encode([
                'title' => 'Kayaking Tutorial',
                'description' => 'How to kayak on this trail',
                'icon' => 'mdi-youtube'
            ])
        ];

        // Act
        $response = $this->authenticatedPost("/api/v1/dashboard/trails/{$this->trail->id}/links", $linkData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'url',
                    'meta_data',
                    'title',
                    'description',
                    'icon'
                ]
            ])
            ->assertJson([
                'message' => 'Link został utworzony'
            ]);

        $this->assertDatabaseHas('links', [
            'url' => 'https://youtube.com/watch?v=test123'
        ]);

        $linkId = $response->json('data.id');
        $this->assertDatabaseHas('linkables', [
            'link_id' => $linkId,
            'linkable_type' => Trail::class,
            'linkable_id' => $this->trail->id
        ]);
    }

    /** @test */
    public function test_create_link_validates_required_url()
    {
        // Arrange
        $linkData = [
            'meta_data' => json_encode(['title' => 'Test', 'description' => '', 'icon' => ''])
        ];

        // Act
        $response = $this->authenticatedPost("/api/v1/dashboard/trails/{$this->trail->id}/links", $linkData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    /** @test */
    public function test_create_link_validates_url_format()
    {
        // Arrange
        $linkData = [
            'url' => 'not-a-valid-url',
            'meta_data' => json_encode(['title' => 'Test', 'description' => '', 'icon' => ''])
        ];

        // Act
        $response = $this->authenticatedPost("/api/v1/dashboard/trails/{$this->trail->id}/links", $linkData);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['url']);
    }

    /** @test */
    public function test_create_link_returns_404_for_nonexistent_trail()
    {
        // Arrange
        $linkData = [
            'url' => 'https://example.com',
            'meta_data' => json_encode(['title' => 'Test', 'description' => '', 'icon' => ''])
        ];

        // Act
        $response = $this->authenticatedPost("/api/v1/dashboard/trails/999999/links", $linkData);

        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => 404,
                    'message' => 'Szlak nie został znaleziony'
                ]
            ]);
    }

    /** @test */
    public function test_can_update_trail_link()
    {
        // Arrange
        $link = Link::factory()->create([
            'url' => 'https://old-url.com',
            'meta_data' => json_encode(['title' => 'Old Title', 'description' => '', 'icon' => ''])
        ]);
        $this->trail->links()->attach($link->id);

        $updateData = [
            'url' => 'https://new-url.com',
            'meta_data' => json_encode(['title' => 'New Title', 'description' => 'New Description', 'icon' => 'mdi-web'])
        ];

        // Act
        $response = $this->authenticatedPut(
            "/api/v1/dashboard/trails/{$this->trail->id}/links/{$link->id}",
            $updateData
        );

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'url',
                    'meta_data',
                    'title',
                    'description',
                    'icon'
                ]
            ])
            ->assertJson([
                'message' => 'Link został zaktualizowany',
                'data' => [
                    'url' => 'https://new-url.com',
                    'title' => 'New Title',
                    'description' => 'New Description'
                ]
            ]);

        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'url' => 'https://new-url.com'
        ]);
    }

    /** @test */
    public function test_update_link_returns_404_for_nonexistent_link()
    {
        // Arrange
        $updateData = [
            'url' => 'https://new-url.com'
        ];

        // Act
        $response = $this->authenticatedPut(
            "/api/v1/dashboard/trails/{$this->trail->id}/links/999999",
            $updateData
        );

        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => 404,
                    'message' => 'Link nie został znaleziony'
                ]
            ]);
    }

    /** @test */
    public function test_update_link_returns_404_if_link_does_not_belong_to_trail()
    {
        // Arrange
        $link = Link::factory()->create();
        $anotherTrail = Trail::factory()->create();
        $anotherTrail->links()->attach($link->id);

        $updateData = [
            'url' => 'https://new-url.com'
        ];

        // Act
        $response = $this->authenticatedPut(
            "/api/v1/dashboard/trails/{$this->trail->id}/links/{$link->id}",
            $updateData
        );

        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => 404,
                    'message' => 'Link nie należy do tego szlaku'
                ]
            ]);
    }

    /** @test */
    public function test_can_delete_trail_link()
    {
        // Arrange
        $link = Link::factory()->create();
        $this->trail->links()->attach($link->id);

        $linkId = $link->id;

        // Act
        $response = $this->authenticatedDelete("/api/v1/dashboard/trails/{$this->trail->id}/links/{$linkId}");

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Link został usunięty'
            ]);

        $this->assertDatabaseMissing('links', ['id' => $linkId]);
        $this->assertDatabaseMissing('linkables', ['link_id' => $linkId]);
    }

    /** @test */
    public function test_delete_link_returns_404_for_nonexistent_link()
    {
        // Act
        $response = $this->authenticatedDelete("/api/v1/dashboard/trails/{$this->trail->id}/links/999999");

        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => 404,
                    'message' => 'Link nie został znaleziony'
                ]
            ]);
    }

    /** @test */
    public function test_delete_link_returns_404_if_link_does_not_belong_to_trail()
    {
        // Arrange
        $link = Link::factory()->create();
        $anotherTrail = Trail::factory()->create();
        $anotherTrail->links()->attach($link->id);

        // Act
        $response = $this->authenticatedDelete("/api/v1/dashboard/trails/{$this->trail->id}/links/{$link->id}");

        // Assert
        $response->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => 404,
                    'message' => 'Link nie należy do tego szlaku'
                ]
            ]);
    }

    /** @test */
    public function test_link_can_be_shared_between_multiple_trails()
    {
        // Arrange
        $link = Link::factory()->wikipedia()->create();
        $trail2 = Trail::factory()->create();

        $this->trail->links()->attach($link->id);
        $trail2->links()->attach($link->id);

        // Act
        $response1 = $this->authenticatedGet("/api/v1/dashboard/trails/{$this->trail->id}/links");
        $response2 = $this->authenticatedGet("/api/v1/dashboard/trails/{$trail2->id}/links");

        // Assert
        $response1->assertStatus(200)->assertJsonCount(1, 'data');
        $response2->assertStatus(200)->assertJsonCount(1, 'data');

        $this->assertEquals(
            $response1->json('data.0.id'),
            $response2->json('data.0.id')
        );
    }

    /** @test */
    public function test_deleting_link_from_one_trail_does_not_affect_other_trails()
    {
        // Arrange
        $link = Link::factory()->create();
        $trail2 = Trail::factory()->create();

        $this->trail->links()->attach($link->id);
        $trail2->links()->attach($link->id);

        // Act - Delete from trail 1
        $this->authenticatedDelete("/api/v1/dashboard/trails/{$this->trail->id}/links/{$link->id}");

        // Assert - Link should be completely deleted (not just detached)
        $this->assertDatabaseMissing('links', ['id' => $link->id]);
        $this->assertDatabaseMissing('linkables', ['link_id' => $link->id]);
    }

    private function checkDatabaseState()
    {
        echo "\n=== DEBUG DATABASE STATE ===\n";
        echo "Total trails in database: " . Trail::count() . "\n";
        echo "Total users in database: " . User::count() . "\n";

        if (Trail::count() > 0) {
            $trail = Trail::first();
            echo "First trail - ID: {$trail->id}, Status: {$trail->status}, Name: {$trail->trail_name}\n";
        }

        if (User::count() > 0) {
            $user = User::first();
            echo "First user - ID: {$user->id}, Is Admin: {$user->is_admin}\n";
        }
        echo "============================\n";
    }

// Dodaj też test diagnostyczny
    public function test_database_connection_and_factories()
    {
        // Sprawdź czy mamy połączenie z bazą
        $this->assertDatabaseCount('trails', 1);
        $this->assertDatabaseCount('users', 1);

        // Sprawdź konkretny rekord
        $this->assertDatabaseHas('trails', [
            'id' => $this->trail->id,
            'status' => 'active'
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->adminUser->id,
            'is_admin' => true
        ]);

        echo "Database check passed - trails and users created successfully\n";
    }

    public function test_debug_routing()
    {
        $url = "/api/v1/dashboard/trails/79/links"; // Użyj prawdziwego ID z debug output

        // Sprawdź ręcznie jaki route jest matchowany
        $request = Request::create($url, 'GET');
        $routes = Route::getRoutes();

        echo "Testing URL: " . $url . "\n";

        try {
            $route = $routes->match($request);
            echo "Matched route: " . $route->getName() . "\n";
            echo "Controller: " . $route->getActionName() . "\n";
            echo "Parameters: " . json_encode($route->parameters()) . "\n";
        } catch (NotFoundHttpException $e) {
            echo "NO ROUTE MATCHED - This is falling back to fallback route\n";
        }

        // Test z autentykacją
        $response = $this->authenticatedGet($url);
        echo "Response: " . $response->getContent() . "\n";
    }

}
