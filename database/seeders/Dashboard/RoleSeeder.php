<?php

namespace Database\Seeders\Dashboard;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web'
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web'
        ]);

        $editor = Role::firstOrCreate([
            'name' => 'Editor',
            'guard_name' => 'web'
        ]);

        $user = Role::firstOrCreate([
            'name' => 'User',
            'guard_name' => 'web'
        ]);

        // Super Admin doesn't need explicit permissions - they bypass all checks
        // This is handled by the Gate::before callback (see AppServiceProvider)

        // Admin permissions - full dashboard access except system-level operations
        $admin->givePermissionTo([
            // Dashboard access
            'dashboard.view',
            'dashboard.analytics.view',
            'dashboard.settings.view',
            'dashboard.settings.update',

            // Users management (except force delete)
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.restore',
            'users.assign_roles',
            'users.revoke_roles',

            // Trails management
            'trails.view',
            'trails.create',
            'trails.update',
            'trails.delete',
            'trails.restore',
            'trails.publish',
            'trails.unpublish',
            'trails.approve',
            'trails.reject',

            // Regions management
            'regions.view',
            'regions.create',
            'regions.update',
            'regions.delete',
            'regions.restore',

            // Roles (view only)
            'roles.view',
            'permissions.view',

            // Media management
            'media.view',
            'media.upload',
            'media.delete',
            'media.optimize',

            // Limited system access
            'system.logs.view',
            'system.cache.clear',
            'system.notifications.send',
            'system.security.view',
        ]);

        // Editor permissions - content management focused
        $editor->givePermissionTo([
            // Dashboard access
            'dashboard.view',
            'dashboard.analytics.view',

            // Limited user management
            'users.view',
            'users.update', // Only their own profile

            // Trails management (no delete/force delete)
            'trails.view',
            'trails.create',
            'trails.update',
            'trails.publish',
            'trails.unpublish',

            // Regions (view only)
            'regions.view',

            // Media management
            'media.view',
            'media.upload',
            'media.delete',
        ]);

        // User permissions - minimal, for public API access and reading public resources
        $user->givePermissionTo([
            'trails.view',      // View trails (public API)
            'regions.view',     // View regions (public API)
            'api.access',       // General API access
        ]);

        $this->command->info('Created roles and assigned permissions');
        $this->command->info('Super Admin: All permissions (bypasses checks)');
        $this->command->info('Admin: ' . $admin->permissions->count() . ' permissions');
        $this->command->info('Editor: ' . $editor->permissions->count() . ' permissions');
        $this->command->info('User: ' . $user->permissions->count() . ' permissions');
    }
}
