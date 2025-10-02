<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class CheckPermissions extends Command
{
    protected $signature = 'check:permissions';
    protected $description = 'Check permissions and roles in database';

    public function handle()
    {
        $this->info('=== PERMISSIONS & ROLES REPORT ===');

        // Permissions count
        $permissionsCount = Permission::count();
        $this->info("Total Permissions: {$permissionsCount}");

        if ($permissionsCount > 0) {
            $this->line('');
            $this->info('Available Permissions:');
            Permission::orderBy('name')->get()->each(function($permission) {
                $this->line("  - {$permission->name}");
            });
        }

        $this->line('');

        // Roles count
        $rolesCount = Role::count();
        $this->info("Total Roles: {$rolesCount}");

        if ($rolesCount > 0) {
            $this->line('');
            $this->info('Roles and their permissions:');
            Role::with('permissions')->orderBy('name')->get()->each(function($role) {
                $permCount = $role->permissions->count();
                $this->line("  - {$role->name}: {$permCount} permissions");

                if ($permCount > 0) {
                    $role->permissions->each(function($permission) {
                        $this->line("    * {$permission->name}");
                    });
                }
            });
        }

        $this->line('');

        // Users with roles
        $usersCount = User::count();
        $this->info("Total Users: {$usersCount}");

        if ($usersCount > 0) {
            $this->line('');
            $this->info('Users and their roles:');
            User::with('roles.permissions')->limit(10)->get()->each(function($user) {
                $rolesCount = $user->roles->count();
                $this->line("  - {$user->email}: {$rolesCount} roles");

                if ($rolesCount > 0) {
                    $user->roles->each(function($role) {
                        $this->line("    * {$role->name} ({$role->permissions->count()} permissions)");
                    });
                }
            });
        }

        // Check specific role permissions we need
        $this->line('');
        $this->info('=== REQUIRED PERMISSIONS CHECK ===');

        $requiredPermissions = [
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'users.view',
            'users.create',
            'dashboard.view'
        ];

        foreach ($requiredPermissions as $permissionName) {
            $exists = Permission::where('name', $permissionName)->exists();
            $status = $exists ? '✓' : '✗';
            $this->line("  {$status} {$permissionName}");
        }

        return 0;
    }
}
