<?php

namespace Tests\Feature\Api\V1\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $superAdmin;
    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'users.view']);
        Permission::create(['name' => 'users.create']);
        Permission::create(['name' => 'users.edit']);
        Permission::create(['name' => 'users.delete']);
        Permission::create(['name' => 'dashboard.view']);

        // Create roles
        $superAdminRole = Role::create(['name' => 'Super Admin']);
        $adminRole = Role::create(['name' => 'Admin']);
        $userRole = Role::create(['name' => 'User']);

        // Assign permissions to roles
        $superAdminRole->givePermissionTo(['users.view', 'users.create', 'users.edit', 'users.delete', 'dashboard.view']);
        $adminRole->givePermissionTo(['users.view', 'users.create', 'users.edit', 'users.delete', 'dashboard.view']);

        // Create test users with proper headers
        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin@test.com',
            'is_active' => true,
            'email_verified_at' => now()
        ]);
        $this->superAdmin->assignRole('Super Admin');

        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'is_active' => true,
            'email_verified_at' => now()
        ]);
        $this->admin->assignRole('Admin');

        $this->regularUser = User::factory()->create([
            'email' => 'user@test.com',
            'is_active' => true,
            'email_verified_at' => now()
        ]);
        $this->regularUser->assignRole('User');
    }

    private function getApiHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Client-Type' => 'dashboard'
        ];
    }

    /** @test */
    public function unauthenticated_user_cannot_access_users_endpoint()
    {
        $response = $this->getJson('/api/v1/dashboard/users', $this->getApiHeaders());
        $response->assertStatus(401);
    }

    /** @test */
    public function regular_user_cannot_access_user_management()
    {
        $response = $this->actingAs($this->regularUser, 'api')
            ->getJson('/api/v1/dashboard/users', $this->getApiHeaders());

        // Debug the actual response
        if ($response->status() !== 403) {
            dump('Expected 403, got: ' . $response->status());
            dump('Response body: ' . $response->getContent());
        }

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_list_users_with_correct_structure()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/v1/dashboard/users', $this->getApiHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'preferences',
                        'notification_settings',
                        'roles',
                        'status'
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    /** @test */
    public function admin_can_create_user_with_preferences()
    {
        $userData = [
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'newuser@test.com',
            'preferences' => [
                'email_notifications' => true,
                'language' => 'pl'
            ],
            'notification_settings' => [
                'enabled' => true,
                'email' => true,
                'push' => false
            ],
            'roles' => ['User']
        ];

        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/v1/dashboard/users', $userData, $this->getApiHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('data.first_name', 'New')
            ->assertJsonPath('data.last_name', 'User')
            ->assertJsonPath('data.email', 'newuser@test.com')
            ->assertJsonPath('data.preferences.email_notifications', 1) // Should be 1, not true
            ->assertJsonPath('data.preferences.language', 'pl')
            ->assertJsonPath('data.notification_settings.enabled', 1);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@test.com',
            'first_name' => 'New',
            'last_name' => 'User'
        ]);

        // Verify role assignment
        $user = User::where('email', 'newuser@test.com')->first();
        $this->assertTrue($user->hasRole('User'));
    }

    /** @test */
    public function admin_can_view_user_details()
    {
        $user = User::factory()->create([
            'preferences' => ['email_notifications' => 1, 'language' => 'pl'],
            'notification_settings' => ['enabled' => 1, 'email' => 0, 'push' => 1]
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson("/api/v1/dashboard/users/{$user->id}", $this->getApiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.preferences.email_notifications', 1)
            ->assertJsonPath('data.preferences.language', 'pl')
            ->assertJsonPath('data.notification_settings.enabled', 1)
            ->assertJsonPath('data.notification_settings.email', 0)
            ->assertJsonPath('data.notification_settings.push', 1);
    }

    /** @test */
    public function admin_can_update_user_preferences()
    {
        $user = User::factory()->create([
            'first_name' => 'Original',
            'preferences' => ['email_notifications' => 0, 'language' => 'en']
        ]);

        $updateData = [
            'first_name' => 'Updated',
            'preferences' => [
                'email_notifications' => true,
                'language' => 'pl'
            ],
            'notification_settings' => [
                'enabled' => false,
                'email' => true,
                'push' => false
            ]
        ];

        $response = $this->actingAs($this->admin, 'api')
            ->putJson("/api/v1/dashboard/users/{$user->id}", $updateData, $this->getApiHeaders());

        $response->assertStatus(200)
            ->assertJsonPath('data.first_name', 'Updated')
            ->assertJsonPath('data.preferences.email_notifications', 1)
            ->assertJsonPath('data.preferences.language', 'pl')
            ->assertJsonPath('data.notification_settings.enabled', 0)
            ->assertJsonPath('data.notification_settings.email', 1);

        // Verify in database
        $user->refresh();
        $this->assertEquals('Updated', $user->first_name);
        $this->assertEquals(1, $user->preferences['email_notifications']);
        $this->assertEquals('pl', $user->preferences['language']);
    }

    /** @test */
    public function validation_fails_with_invalid_email()
    {
        $invalidData = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'invalid-email-format'
        ];

        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/v1/dashboard/users', $invalidData, $this->getApiHeaders());

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'email'
                ]
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Błędy walidacji'
            ])
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function validation_fails_with_duplicate_email()
    {
        $userData = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $this->admin->email // Using existing email
        ];

        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/v1/dashboard/users', $userData, $this->getApiHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function admin_can_delete_regular_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin, 'api')
            ->deleteJson("/api/v1/dashboard/users/{$user->id}", [], $this->getApiHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Użytkownik usunięty pomyślnie'
            ]);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /** @test */
    public function admin_cannot_delete_themselves()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->deleteJson("/api/v1/dashboard/users/{$this->admin->id}", [], $this->getApiHeaders());

        // Should return 500 due to UserService exception
        $response->assertStatus(500);

        // Verify user is not deleted
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function preferences_boolean_casting_works_correctly()
    {
        $user = User::factory()->create();

        $updateData = [
            'preferences' => [
                'email_notifications' => true,
                'push_notifications' => false,
                'sms_notifications' => true,
                'language' => 'pl'
            ]
        ];

        $response = $this->actingAs($this->admin, 'api')
            ->putJson("/api/v1/dashboard/users/{$user->id}", $updateData, $this->getApiHeaders());

        $response->assertStatus(200);

        // Verify in database that booleans are stored as 1/0
        $user->refresh();
        $this->assertEquals(1, $user->preferences['email_notifications']);
        $this->assertEquals(0, $user->preferences['push_notifications']);
        $this->assertEquals(1, $user->preferences['sms_notifications']);
        $this->assertEquals('pl', $user->preferences['language']);

        // Also verify in JSON response
        $response->assertJsonPath('data.preferences.email_notifications', 1)
            ->assertJsonPath('data.preferences.push_notifications', 0)
            ->assertJsonPath('data.preferences.sms_notifications', 1)
            ->assertJsonPath('data.preferences.language', 'pl');
    }

    /** @test */
    public function user_resource_returns_correct_status()
    {
        // Test active user
        $activeUser = User::factory()->create([
            'is_active' => true,
            'email_verified_at' => now()
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson("/api/v1/dashboard/users/{$activeUser->id}", $this->getApiHeaders());

        $response->assertJsonPath('data.status', 'active');

        // Test inactive user
        $inactiveUser = User::factory()->create([
            'is_active' => false,
            'email_verified_at' => now()
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson("/api/v1/dashboard/users/{$inactiveUser->id}", $this->getApiHeaders());

        $response->assertJsonPath('data.status', 'inactive');

        // Test unverified user
        $unverifiedUser = User::factory()->create([
            'is_active' => true,
            'email_verified_at' => null
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson("/api/v1/dashboard/users/{$unverifiedUser->id}", $this->getApiHeaders());

        $response->assertJsonPath('data.status', 'unverified');
    }

    /** @test */
    public function only_super_admin_can_create_super_admin_user()
    {
        $userData = [
            'first_name' => 'New',
            'last_name' => 'SuperAdmin',
            'email' => 'newsuperadmin@test.com',
            'roles' => ['Super Admin']
        ];

        // Regular admin cannot create super admin
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/v1/dashboard/users', $userData, $this->getApiHeaders());

        $response->assertStatus(500); // Should throw exception

        // Super admin can create super admin
        $response = $this->actingAs($this->superAdmin, 'api')
            ->postJson('/api/v1/dashboard/users', $userData, $this->getApiHeaders());

        $response->assertStatus(201);

        $user = User::where('email', 'newsuperadmin@test.com')->first();
        $this->assertTrue($user->hasRole('Super Admin'));
    }
}
