<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FixAdminPermissions extends Command
{
    protected $signature = 'fix:admin-permissions';
    protected $description = 'Add missing permissions to Admin role';

    public function handle()
    {
        $this->info('=== FIXING ADMIN PERMISSIONS ===');

        $adminRole = Role::where('name', 'Admin')->first();
        if (!$adminRole) {
            $this->error('Admin role not found!');
            return 1;
        }

        // Missing permissions for Admin role
        $missingPermissions = [
            'roles.create',
            'roles.update',
            'roles.delete',
            'roles.assign_permissions',
            'roles.revoke_permissions',
            'permissions.create',
            'permissions.update',
            'permissions.delete'
        ];

        $added = 0;
        $skipped = 0;

        foreach ($missingPermissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();

            if (!$permission) {
                $this->warn("Permission '{$permissionName}' does not exist in database");
                continue;
            }

            if ($adminRole->hasPermissionTo($permission)) {
                $this->line("✓ Admin already has '{$permissionName}'");
                $skipped++;
            } else {
                $adminRole->givePermissionTo($permission);
                $this->info("✓ Added '{$permissionName}' to Admin role");
                $added++;
            }
        }

        $this->line('');
        $this->info("=== SUMMARY ===");
        $this->info("Added: {$added} permissions");
        $this->info("Skipped: {$skipped} permissions (already had them)");

        // Show current admin permissions count
        $currentCount = $adminRole->permissions()->count();
        $this->info("Admin role now has {$currentCount} permissions total");

        return 0;
    }
}
