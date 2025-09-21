<?php

namespace App\Services\Dashboard;

use App\Models\User;
use Spatie\Permission\Models\Role;

class UserRoleService
{
    public function __construct(
        private readonly SystemSecurityService $securityService
    ) {}
    public function getUserWithRoles(User $user): User
    {
        return $user->load(['roles.permissions']);
    }

    public function assignRoles(User $user, array $roleIds, User $currentUser): User
    {
        $this->securityService->validateSystemSecurity($user, $roleIds, $currentUser, 'assign');

        $roles = Role::whereIn('id', $roleIds)->get();
        $user->assignRole($roles);

        return $user->fresh(['roles.permissions']);
    }

    public function revokeRoles(User $user, array $roleIds, User $currentUser): User
    {
        $this->securityService->validateSystemSecurity($user, $roleIds, $currentUser, 'revoke');

        $roles = Role::whereIn('id', $roleIds)->get();
        $user->removeRole($roles);

        return $user->fresh(['roles.permissions']);
    }

    public function syncRoles(User $user, array $roleIds, User $currentUser): User
    {
        $this->securityService->validateSystemSecurity($user, $roleIds, $currentUser, 'sync');

        $roles = Role::whereIn('id', $roleIds)->get();
        $user->syncRoles($roles);

        return $user->fresh(['roles.permissions']);
    }

    public function getSystemSecurityStatus(): array
    {
        return $this->securityService->getSystemSecurityStatus();
    }

    public function canDeleteUser(User $user): array
    {
        return $this->securityService->canDeleteUser($user);
    }

    public function getEmergencyRecoveryInfo(): array
    {
        return $this->securityService->getEmergencyRecoveryInfo();
    }

    protected function validateRoleAssignment(User $user, array $roleIds, User $currentUser): void
    {
        $roles = Role::whereIn('id', $roleIds)->get();
        $superAdminRole = $roles->firstWhere('name', 'Super Admin');

        if ($superAdminRole && !$currentUser->isSuperAdmin()) {
            throw new \Exception('Only Super Admin can assign the Super Admin role.', 403);
        }

        if ($user->id === $currentUser->id && !$currentUser->isSuperAdmin()) {
            throw new \Exception('You cannot modify your own roles.', 403);
        }

        if ($user->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            throw new \Exception('Only Super Admin can modify other Super Admin users.', 403);
        }
    }

    protected function validateRoleRevocation(User $user, array $roleIds, User $currentUser): void
    {
        $roles = Role::whereIn('id', $roleIds)->get();
        $superAdminRole = $roles->firstWhere('name', 'Super Admin');

        if ($superAdminRole && !$currentUser->isSuperAdmin()) {
            throw new \Exception('Only Super Admin can revoke the Super Admin role.', 403);
        }

        if ($user->id === $currentUser->id && !$currentUser->isSuperAdmin()) {
            throw new \Exception('You cannot modify your own roles.', 403);
        }

        if ($user->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            throw new \Exception('Only Super Admin can modify other Super Admin users.', 403);
        }

        if ($superAdminRole && $user->isSuperAdmin()) {
            $superAdminCount = User::role('Super Admin')->count();
            if ($superAdminCount <= 1) {
                throw new \Exception('Cannot revoke the last Super Admin role.', 403);
            }
        }
    }

    protected function validateRoleSync(User $user, array $roleIds, User $currentUser): void
    {
        $roles = Role::whereIn('id', $roleIds)->get();
        $superAdminRole = $roles->firstWhere('name', 'Super Admin');

        if ($superAdminRole && !$currentUser->isSuperAdmin()) {
            throw new \Exception('Only Super Admin can assign the Super Admin role.', 403);
        }

        if ($user->id === $currentUser->id && !$currentUser->isSuperAdmin()) {
            throw new \Exception('You cannot modify your own roles.', 403);
        }

        if ($user->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            throw new \Exception('Only Super Admin can modify other Super Admin users.', 403);
        }

        if ($user->isSuperAdmin() && !$superAdminRole) {
            $superAdminCount = User::role('Super Admin')->count();
            if ($superAdminCount <= 1) {
                throw new \Exception('Cannot remove the last Super Admin role.', 403);
            }
        }
    }
}