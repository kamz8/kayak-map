<?php

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed permissions and roles
    $this->seed(PermissionSeeder::class);
    $this->seed(Database\Seeders\Dashboard\RoleSeeder::class);

    // Create Super Admin user
    $this->superAdmin = User::factory()->create([
        'email' => 'superadmin@test.com',
        'is_active' => true,
        'email_verified_at' => now()
    ]);
    $this->superAdmin->assignRole('Super Admin');
    $this->superAdmin->givePermissionTo('users.assign_roles');

    // Create test user (regular user)
    $this->testUser = User::factory()->create([
        'email' => 'testuser@test.com',
        'is_active' => true,
        'email_verified_at' => now()
    ]);
});

test('super admin can sync single role to user via API', function () {
    $adminRole = Role::where('name', 'Admin')->first();

    $response = $this->actingAs($this->superAdmin, 'web')
        ->putJson("/api/v1/dashboard/users/{$this->testUser->id}/sync-roles", [
            'roles' => [$adminRole->id]
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Client-Type' => 'web'
        ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'message',
        'data' => [
            'user',
            'roles'
        ]
    ]);

    $this->testUser->refresh();
    expect($this->testUser->roles->pluck('name')->toArray())->toContain('Admin');
});
