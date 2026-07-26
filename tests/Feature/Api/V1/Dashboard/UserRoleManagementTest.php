<?php

namespace Tests\Feature\Api\V1\Dashboard;

use App\Models\User;
use Database\Seeders\Dashboard\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

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

    private function headers(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Client-Type' => 'web'
        ];
    }

    /** @test */
    public function it_denies_unauthenticated_access()
    {
        $response = $this->getJson('/api/v1/dashboard/users', $this->headers());
        $response->assertUnauthorized();
    }

    /** @test */
    public function it_denies_regular_user_access()
    {
        $response = $this->actingAs($this->regularUser, 'api')
            ->getJson('/api/v1/dashboard/users', $this->headers());

        $response->assertForbidden();
    }

    /** @test */
    public function it_lists_users_for_admin()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/v1/dashboard/users', $this->headers());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'first_name', 'last_name', 'email', 'roles', 'status']
                ],
                'links',
                'meta'
            ]);
    }

    /** @test */
    public function it_creates_user_with_role()
    {
        $userData = [
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'newuser@test.com',
            'roles' => ['Editor']
        ];

        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/v1/dashboard/users', $userData, $this->headers());

        $response->assertCreated()
            ->assertJsonPath('data.email', 'newuser@test.com');

        $user = User::where('email', 'newuser@test.com')->first();
        $this->assertTrue($user->hasRole('Editor'));
    }

    /** @test */
    public function it_prevents_admin_from_creating_super_admin()
    {
        $userData = [
            'first_name' => 'Hacker',
            'last_name' => 'User',
            'email' => 'hacker@test.com',
            'roles' => ['Super Admin']
        ];

        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/v1/dashboard/users', $userData, $this->headers());

        $response->assertForbidden();
    }

    /** @test */
    public function it_allows_super_admin_to_create_super_admin()
    {
        $userData = [
            'first_name' => 'New',
            'last_name' => 'SuperAdmin',
            'email' => 'newsuperadmin@test.com',
            'roles' => ['Super Admin']
        ];

        $response = $this->actingAs($this->superAdmin, 'api')
            ->postJson('/api/v1/dashboard/users', $userData, $this->headers());

        $response->assertCreated();

        $user = User::where('email', 'newsuperadmin@test.com')->first();
        $this->assertTrue($user->hasRole('Super Admin'));
    }

    /** @test */
    public function it_updates_user_details()
    {
        $user = User::factory()->create(['first_name' => 'Original']);

        $response = $this->actingAs($this->admin, 'api')
            ->putJson("/api/v1/dashboard/users/{$user->id}", [
                'first_name' => 'Updated',
                'last_name' => 'Name'
            ], $this->headers());

        $response->assertOk();

        $user->refresh();
        $this->assertEquals('Updated', $user->first_name);
    }

    /** @test */
    public function it_prevents_admin_from_deleting_themselves()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->deleteJson("/api/v1/dashboard/users/{$this->admin->id}", [], $this->headers());

        $response->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'deleted_at' => null
        ]);
    }

    /** @test */
    public function it_soft_deletes_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin, 'api')
            ->deleteJson("/api/v1/dashboard/users/{$user->id}", [], $this->headers());

        $response->assertOk();
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }
}
