<?php

namespace App\Services\Dashboard;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function getAllRoles(array $filters = []): Collection
    {
        $query = Role::query();

        if (isset($filters['with_permissions']) && $filters['with_permissions']) {
            $query->with('permissions');
        }

        if (isset($filters['with_users_count']) && $filters['with_users_count']) {
            $query->withCount('users');
        }

        return $query->orderBy('name')->get();
    }

    public function findRole(int $roleId, array $relations = []): Role
    {
        $query = Role::where('id', $roleId);

        if (!empty($relations)) {
            $query->with($relations);
        }

        $role = $query->first();

        if (!$role) {
            throw new \Exception('Role not found', 404);
        }

        return $role;
    }

    public function createRole(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web'
        ]);

        if (isset($data['permissions']) && !empty($data['permissions'])) {
            $permissions = Permission::whereIn('id', $data['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        return $role->fresh(['permissions']);
    }

    public function updateRole(Role $role, array $data, User $currentUser): Role
    {
        $this->validateRoleModification($role, $currentUser);

        $role->update(['name' => $data['name']]);

        if (isset($data['permissions'])) {
            $permissions = Permission::whereIn('id', $data['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        return $role->fresh(['permissions']);
    }

    public function deleteRole(Role $role): bool
    {
        $this->validateRoleDeletion($role);
        return $role->delete();
    }

    public function assignPermissions(Role $role, array $permissionIds): Role
    {
        if ($role->name === 'Super Admin') {
            throw new \Exception('Super Admin role bypasses all permission checks.', 422);
        }

        $permissions = Permission::whereIn('id', $permissionIds)->get();
        $role->givePermissionTo($permissions);

        return $role->fresh(['permissions']);
    }

    public function revokePermissions(Role $role, array $permissionIds): Role
    {
        if ($role->name === 'Super Admin') {
            throw new \Exception('Super Admin role bypasses all permission checks.', 422);
        }

        $permissions = Permission::whereIn('id', $permissionIds)->get();
        $role->revokePermissionTo($permissions);

        return $role->fresh(['permissions']);
    }

    protected function validateRoleModification(Role $role, User $currentUser): void
    {
        if ($role->name === 'Super Admin' && !$currentUser->isSuperAdmin()) {
            throw new \Exception('Only Super Admin can modify the Super Admin role.', 403);
        }
    }

    protected function validateRoleDeletion(Role $role): void
    {
        $systemRoles = ['Super Admin', 'Admin', 'Editor', 'User'];

        if (in_array($role->name, $systemRoles)) {
            throw new \Exception('Cannot delete system roles.', 403);
        }
    }
}