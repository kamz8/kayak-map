<?php

namespace App\Services\Dashboard;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    public function __construct(
        private readonly SystemSecurityService $securityService
    ) {}

    /**
     * Pobierz listę użytkowników z filtrami i paginacją
     */
    public function getUsersList(array $filters = []): LengthAwarePaginator
    {
        $query = User::with(['roles']);

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        // Role filter
        if (!empty($filters['role'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            switch ($filters['status']) {
                case 'active':
                    $query->where('is_active', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'email_verified':
                    $query->whereNotNull('email_verified_at');
                    break;
                case 'email_unverified':
                    $query->whereNull('email_verified_at');
                    break;
                case 'phone_verified':
                    $query->where('phone_verified', true);
                    break;
                case 'phone_unverified':
                    $query->where('phone_verified', false);
                    break;
            }
        }

        // Date filters
        if (!empty($filters['created_from'])) {
            $query->where('created_at', '>=', $filters['created_from']);
        }

        if (!empty($filters['created_to'])) {
            $query->where('created_at', '<=', $filters['created_to']);
        }

        if (!empty($filters['last_login_from'])) {
            $query->where('last_login_at', '>=', $filters['last_login_from']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        $allowedSorts = ['created_at', 'first_name', 'last_name', 'email', 'last_login_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $perPage = min($filters['per_page'] ?? 15, 50); // Max 50 per page

        return $query->paginate($perPage);
    }

    /**
     * Pobierz użytkownika ze szczegółami
     */
    public function getUserWithDetails(User $user): User
    {
        return $user->load([
            'roles.permissions',
            'socialAccounts',
            'devices' => function ($query) {
                $query->orderBy('last_used_at', 'desc')->limit(5);
            }
        ]);
    }

    /**
     * Utwórz nowego użytkownika
     */
    public function createUser(array $data, User $creator): User
    {
        // Validate email uniqueness
        if (User::where('email', $data['email'])->exists()) {
            throw new \Exception('Email already exists');
        }

        // Validate phone uniqueness if provided
        if (!empty($data['phone']) && User::where('phone', $data['phone'])->exists()) {
            throw new \Exception('Phone already exists');
        }

        // Security check for role assignment
        if (!empty($data['roles'])) {
            $this->securityService->validateRoleAssignment($data['roles'], $creator);
        }

        // Generate password if not provided
        if (empty($data['password'])) {
            $data['password'] = Str::random(12);
        }

        // Create user
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'bio' => $data['bio'] ?? null,
            'location' => $data['location'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'gender' => $data['gender'] ?? null,
            'preferences' => $data['preferences'] ?? [],
            'notification_settings' => $data['notification_settings'] ?? [
                'enabled' => true,
                'email' => true,
                'push' => false
            ],
            'is_active' => $data['is_active'] ?? true,
        ]);

        // Assign roles if provided
        if (!empty($data['roles'])) {
            $user->assignRole($data['roles']);
        }

        return $user->load('roles');
    }

    /**
     * Aktualizuj użytkownika
     */
    public function updateUser(User $user, array $data, User $updater): User
    {
        // Security check - cannot modify yourself through this endpoint
        if ($user->id === $updater->id) {
            throw new \Exception('Cannot modify yourself through user management');
        }

        // Security check for Super Admin modification
        if ($user->isSuperAdmin() && !$updater->isSuperAdmin()) {
            throw new \Exception('Only Super Admin can modify other Super Admin users');
        }

        // Validate email uniqueness (excluding current user)
        if (!empty($data['email']) && $data['email'] !== $user->email) {
            if (User::where('email', $data['email'])->where('id', '!=', $user->id)->exists()) {
                throw new \Exception('Email already exists');
            }
        }

        // Validate phone uniqueness (excluding current user)
        if (!empty($data['phone']) && $data['phone'] !== $user->phone) {
            if (User::where('phone', $data['phone'])->where('id', '!=', $user->id)->exists()) {
                throw new \Exception('Phone already exists');
            }
        }

        // Update user data
        $updateData = [];

        $allowedFields = [
            'first_name', 'last_name', 'email', 'phone', 'bio',
            'location', 'birth_date', 'gender', 'preferences',
            'notification_settings', 'is_active'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $updateData[$field] = $data[$field];
            }
        }

        // Handle password update
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return $user->fresh(['roles']);
    }

    /**
     * Usuń użytkownika (soft delete)
     */
    public function deleteUser(User $user, User $deleter): void
    {
        // Security checks
        if ($user->id === $deleter->id) {
            throw new \Exception('Cannot delete yourself');
        }

        if ($user->isSuperAdmin()) {
            // Only Super Admin can delete other Super Admins
            if (!$deleter->isSuperAdmin()) {
                throw new \Exception('Cannot delete Super Admin user');
            }

            // Prevent deleting last Super Admin
            $superAdminCount = User::role('Super Admin')->count();
            if ($superAdminCount <= 1) {
                throw new \Exception('Cannot delete the last Super Admin');
            }
        }

        // Soft delete
        $user->delete();
    }

    /**
     * Przywróć usuniętego użytkownika
     */
    public function restoreUser(User $user, User $restorer): User
    {
        // Security check for Super Admin restoration
        if ($user->isSuperAdmin() && !$restorer->isSuperAdmin()) {
            throw new \Exception('Only Super Admin can restore Super Admin users');
        }

        $user->restore();

        return $user->fresh(['roles']);
    }

    /**
     * Permanently delete user (force delete)
     */
    public function forceDeleteUser(User $user, User $deleter): void
    {
        // Only Super Admin can force delete
        if (!$deleter->isSuperAdmin()) {
            throw new \Exception('Only Super Admin can permanently delete users');
        }

        // Cannot force delete Super Admin
        if ($user->isSuperAdmin()) {
            throw new \Exception('Cannot permanently delete Super Admin user');
        }

        // Cannot force delete yourself
        if ($user->id === $deleter->id) {
            throw new \Exception('Cannot permanently delete yourself');
        }

        $user->forceDelete();
    }

    /**
     * Pobierz statystyki użytkowników
     */
    public function getUsersStats(): array
    {
        return [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'deleted' => User::onlyTrashed()->count(),
            'verified_email' => User::whereNotNull('email_verified_at')->count(),
            'verified_phone' => User::where('phone_verified', true)->count(),
            'super_admins' => User::role('Super Admin')->count(),
            'admins' => User::role('Admin')->count(),
            'editors' => User::role('Editor')->count(),
            'users' => User::role('User')->count(),
            'recent_registrations' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'recent_logins' => User::where('last_login_at', '>=', now()->subDays(7))->count(),
        ];
    }
}