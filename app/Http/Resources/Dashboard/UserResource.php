<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'name' => $this->first_name . ' ' . $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'location' => $this->location,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'gender' => $this->gender,
            'preferences' => $this->preferences,
            'notification_settings' => $this->notification_settings,

            // Status information
            'is_active' => $this->is_active,
            'is_admin' => $this->is_admin,
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
            'phone_verified' => $this->phone_verified,
            'last_login_at' => $this->last_login_at?->format('Y-m-d H:i:s'),

            // Roles and permissions
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $this->getRoleDisplayName($role->name),
                        'color' => $this->getRoleColor($role->name),
                    ];
                });
            }),

            'permissions' => $this->whenLoaded('roles', function () {
                try {
                    return $this->getAllPermissions()->pluck('name')->unique()->values();
                } catch (\Exception $e) {
                    return collect();
                }
            }),

            // Avatar
            'avatar' => $this->whenLoaded('avatar', function () {
                return $this->avatar ? [
                    'id' => $this->avatar->id,
                    'path' => $this->avatar->path,
                    'url' => asset('storage/' . $this->avatar->path),
                ] : null;
            }),

            // Social accounts (when loaded for details view)
            'social_accounts' => $this->whenLoaded('socialAccounts', function () {
                return $this->socialAccounts->map(function ($account) {
                    return [
                        'id' => $account->id,
                        'provider' => $account->provider,
                        'provider_nickname' => $account->provider_nickname,
                        'created_at' => $account->created_at?->format('Y-m-d H:i:s'),
                    ];
                });
            }),

            // Recent devices (when loaded for details view)
            'devices' => $this->whenLoaded('devices', function () {
                return $this->devices->map(function ($device) {
                    return [
                        'id' => $device->id,
                        'device_name' => $device->device_name,
                        'device_type' => $device->device_type,
                        'last_used_at' => $device->last_used_at?->format('Y-m-d H:i:s'),
                    ];
                });
            }),

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),

            // Computed properties
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'is_verified' => $this->email_verified_at !== null,
            'is_super_admin' => $this->isSuperAdmin(),
            'status' => $this->getStatus(),
            'role_names' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name')->toArray();
            }),
            'primary_role' => $this->whenLoaded('roles', function () {
                return $this->getPrimaryRole();
            }),
        ];
    }

    /**
     * Get user status based on various conditions
     */
    private function getStatus(): string
    {
        if ($this->deleted_at) {
            return 'deleted';
        }

        if (!$this->is_active) {
            return 'inactive';
        }

        if (!$this->email_verified_at) {
            return 'unverified';
        }

        return 'active';
    }

    /**
     * Get primary role (highest priority role)
     */
    private function getPrimaryRole(): ?array
    {
        if (!$this->relationLoaded('roles') || $this->roles->isEmpty()) {
            return null;
        }

        $roleHierarchy = ['Super Admin', 'Admin', 'Editor', 'User'];

        foreach ($roleHierarchy as $roleName) {
            $role = $this->roles->firstWhere('name', $roleName);
            if ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $this->getRoleDisplayName($role->name),
                    'color' => $this->getRoleColor($role->name),
                ];
            }
        }

        // If no standard role found, return first role
        $firstRole = $this->roles->first();
        return [
            'id' => $firstRole->id,
            'name' => $firstRole->name,
            'display_name' => $this->getRoleDisplayName($firstRole->name),
            'color' => $this->getRoleColor($firstRole->name),
        ];
    }

    /**
     * Get role display name in Polish
     */
    private function getRoleDisplayName(string $roleName): string
    {
        return match ($roleName) {
            'Super Admin' => 'Super Administrator',
            'Admin' => 'Administrator',
            'Editor' => 'Edytor',
            'User' => 'Użytkownik',
            default => $roleName
        };
    }

    /**
     * Get role color for UI badges
     */
    private function getRoleColor(string $roleName): string
    {
        return match ($roleName) {
            'Super Admin' => 'destructive',
            'Admin' => 'warning',
            'Editor' => 'secondary',
            'User' => 'default',
            default => 'default'
        };
    }
}