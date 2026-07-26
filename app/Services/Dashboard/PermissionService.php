<?php

namespace App\Services\Dashboard;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    public function getAllPermissions(array $filters = []): Collection
    {
        $query = Permission::query();

        if (isset($filters['module']) && $filters['module']) {
            $query->where('name', 'like', $filters['module'] . '.%');
        }

        if (isset($filters['with_roles']) && $filters['with_roles']) {
            $query->with('roles');
        }

        return $query->orderBy('name')->get();
    }

    public function getGroupedPermissions(): array
    {
        $permissions = Permission::orderBy('name')->get();

        return $permissions->groupBy(function ($permission) {
            return explode('.', $permission->name)[0] ?? 'other';
        })->toArray();
    }

    public function findPermission(int $permissionId, array $relations = []): Permission
    {
        $query = Permission::where('id', $permissionId);

        if (!empty($relations)) {
            $query->with($relations);
        }

        $permission = $query->first();

        if (!$permission) {
            throw new \Exception('Permission not found', 404);
        }

        return $permission;
    }

    public function createPermission(array $data): Permission
    {
        $permission = Permission::create([
            'name' => $data['name'],
            'guard_name' => 'web'
        ]);

        if (isset($data['roles']) && !empty($data['roles'])) {
            $roles = \Spatie\Permission\Models\Role::whereIn('id', $data['roles'])->get();
            foreach ($roles as $role) {
                $role->givePermissionTo($permission);
            }
        }

        return $permission->fresh(['roles']);
    }

    public function updatePermission(Permission $permission, array $data): Permission
    {
        $permission->update(['name' => $data['name']]);
        return $permission->fresh(['roles']);
    }

    public function deletePermission(Permission $permission): bool
    {
        $this->validatePermissionDeletion($permission);
        return $permission->delete();
    }

    public function getAvailableModules(): array
    {
        return Permission::pluck('name')
            ->map(function ($name) {
                return explode('.', $name)[0] ?? 'other';
            })
            ->unique()
            ->values()
            ->sort()
            ->toArray();
    }

    protected function validatePermissionDeletion(Permission $permission): void
    {
        $coreModules = ['dashboard', 'users', 'roles', 'permissions', 'system'];
        $permissionModule = explode('.', $permission->name)[0] ?? '';

        if (in_array($permissionModule, $coreModules)) {
            throw new \Exception('Cannot delete core system permissions.', 403);
        }
    }
}