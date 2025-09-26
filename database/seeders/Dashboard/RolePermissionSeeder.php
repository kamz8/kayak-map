<?php

namespace Database\Seeders\Dashboard;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Dashboard permissions
            'dashboard.view',
            'dashboard.admin',

            // User management permissions
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.restore',
            'users.force-delete',
            'users.assign-roles',
            'users.revoke-roles',

            // Trail management permissions
            'trails.view',
            'trails.create',
            'trails.edit',
            'trails.delete',
            'trails.publish',
            'trails.moderate',

            // Region management permissions
            'regions.view',
            'regions.create',
            'regions.edit',
            'regions.delete',

            // Content management permissions
            'content.view',
            'content.create',
            'content.edit',
            'content.delete',
            'content.moderate',

            // System permissions
            'system.settings',
            'system.backup',
            'system.logs',
            'system.cache',

            // Analytics permissions
            'analytics.view',
            'analytics.export',

            // API permissions
            'api.access',
            'api.admin',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // Create roles and assign permissions
        $this->createRoles();

        $this->command->info('Roles and permissions created successfully!');
    }

    private function createRoles(): void
    {
        // Super Admin - has all permissions
        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web'
        ]);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - has most permissions except super admin ones
        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web'
        ]);
        $admin->givePermissionTo([
            'dashboard.view',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'users.assign-roles',
            'trails.view',
            'trails.create',
            'trails.edit',
            'trails.delete',
            'trails.publish',
            'trails.moderate',
            'regions.view',
            'regions.create',
            'regions.edit',
            'regions.delete',
            'content.view',
            'content.create',
            'content.edit',
            'content.delete',
            'content.moderate',
            'analytics.view',
            'analytics.export',
            'api.access',
        ]);

        // Editor - content management focused
        $editor = Role::firstOrCreate([
            'name' => 'Editor',
            'guard_name' => 'web'
        ]);
        $editor->givePermissionTo([
            'dashboard.view',
            'trails.view',
            'trails.create',
            'trails.edit',
            'trails.moderate',
            'regions.view',
            'regions.create',
            'regions.edit',
            'content.view',
            'content.create',
            'content.edit',
            'content.moderate',
            'api.access',
        ]);

        // Moderator - moderation focused
        $moderator = Role::firstOrCreate([
            'name' => 'Moderator',
            'guard_name' => 'web'
        ]);
        $moderator->givePermissionTo([
            'dashboard.view',
            'trails.view',
            'trails.moderate',
            'content.view',
            'content.moderate',
            'users.view',
            'api.access',
        ]);

        // User - basic permissions
        $user = Role::firstOrCreate([
            'name' => 'User',
            'guard_name' => 'web'
        ]);
        $user->givePermissionTo([
            'trails.view',
            'regions.view',
            'api.access',
        ]);
    }
}
