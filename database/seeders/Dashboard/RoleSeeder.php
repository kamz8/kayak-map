<?php

namespace Database\Seeders\Dashboard;

use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        config(['permission.cache.store' => 'array']);
        config(['cache.default' => 'array']);

        $this->call(PermissionSeeder::class);
        $permissionRegistrar = app(PermissionRegistrar::class);
        $permissionRegistrar->initializeCache();
        $permissionRegistrar->forgetCachedPermissions();

        // Create roles
        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web'
        ]);

        $superAdmin->syncPermissions(Permission::all());

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

        $admin->syncPermissions(Permission::all());

        // Editor permissions - content management focused
        $editor->syncPermissions([
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
        $user->syncPermissions([
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
