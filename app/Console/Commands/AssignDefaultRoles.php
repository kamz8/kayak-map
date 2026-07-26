<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use App\Models\User;

class AssignDefaultRoles extends Command
{
    protected $signature = 'assign:default-roles';
    protected $description = 'Assign default roles to users without roles';

    public function handle()
    {
        $this->info('=== ASSIGNING DEFAULT ROLES ===');

        // Get users without roles
        $usersWithoutRoles = User::doesntHave('roles')->get();

        if ($usersWithoutRoles->isEmpty()) {
            $this->info('All users already have roles assigned.');
            return 0;
        }

        $this->info("Found {$usersWithoutRoles->count()} users without roles:");

        foreach ($usersWithoutRoles as $user) {
            $this->line("  - {$user->email}");
        }

        $this->line('');

        // Get available roles
        $adminRole = Role::where('name', 'Admin')->first();
        $editorRole = Role::where('name', 'Editor')->first();
        $userRole = Role::where('name', 'User')->first();

        if (!$adminRole || !$editorRole || !$userRole) {
            $this->error('Required roles (Admin, Editor, User) not found in database!');
            return 1;
        }

        // Assign roles based on email patterns
        foreach ($usersWithoutRoles as $user) {
            $assignedRole = null;

            // Admin emails
            if (str_contains($user->email, 'admin@') || str_contains($user->email, '@admin')) {
                $user->assignRole($adminRole);
                $assignedRole = 'Admin';
            }
            // Test/example emails get Admin for development
            elseif (str_contains($user->email, 'test@') || str_contains($user->email, '@example.com')) {
                $user->assignRole($adminRole);
                $assignedRole = 'Admin (dev)';
            }
            // Editor emails
            elseif (str_contains($user->email, 'editor@') || str_contains($user->email, '@editor')) {
                $user->assignRole($editorRole);
                $assignedRole = 'Editor';
            }
            // Default to User role
            else {
                $user->assignRole($userRole);
                $assignedRole = 'User';
            }

            $this->info("✓ {$user->email} → {$assignedRole}");
        }

        $this->line('');
        $this->info('All users now have roles assigned!');

        // Show summary
        $this->line('');
        $this->info('=== ROLE SUMMARY ===');
        $roles = Role::withCount('users')->get();
        foreach ($roles as $role) {
            $this->line("{$role->name}: {$role->users_count} users");
        }

        return 0;
    }
}
