<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Dashboard permissions (dashboard.*)
        $dashboardPermissions = [
            'dashboard.view',
            'dashboard.analytics.view',
            'dashboard.settings.view',
            'dashboard.settings.update',
        ];

        // Users management permissions (users.*)
        $userPermissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.force_delete',
            'users.restore',
            'users.assign_roles',
            'users.revoke_roles',
        ];

        // Trails management permissions (trails.*)
        $trailPermissions = [
            'trails.view',
            'trails.create',
            'trails.update',
            'trails.delete',
            'trails.force_delete',
            'trails.restore',
            'trails.publish',
            'trails.unpublish',
            'trails.approve',
            'trails.reject',
        ];

        // Regions management permissions (regions.*)
        $regionPermissions = [
            'regions.view',
            'regions.create',
            'regions.update',
            'regions.delete',
            'regions.force_delete',
            'regions.restore',
        ];

        // Roles and Permissions management (roles.*, permissions.*)
        $rolePermissions = [
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'roles.assign_permissions',
            'roles.revoke_permissions',
        ];

        $permissionPermissions = [
            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',
        ];

        // System management permissions (system.*)
        $systemPermissions = [
            'system.backup',
            'system.restore',
            'system.maintenance',
            'system.logs.view',
            'system.logs.delete',
            'system.cache.clear',
            'system.notifications.send',
            'system.security.view',
        ];

        // Media management permissions (media.*)
        $mediaPermissions = [
            'media.view',
            'media.upload',
            'media.delete',
            'media.optimize',
        ];

        // API permissions (api.*)
        $apiPermissions = [
            'api.access',       // General API access for authenticated users
        ];

        // All permissions combined
        $allPermissions = array_merge(
            $dashboardPermissions,
            $userPermissions,
            $trailPermissions,
            $regionPermissions,
            $rolePermissions,
            $permissionPermissions,
            $systemPermissions,
            $mediaPermissions,
            $apiPermissions
        );

        // Create permissions
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        $this->command->info('Created ' . count($allPermissions) . ' permissions');
    }
}