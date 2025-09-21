<?php

namespace App\Services\Dashboard;

use App\Models\User;
use Spatie\Permission\Models\Role;

class SystemSecurityService
{
    /**
     * Zabezpieczenia przed utratą kontroli nad systemem
     */

    const MINIMUM_SUPER_ADMINS = 2;
    const MINIMUM_ADMINS = 1;

    public function validateSystemSecurity(User $targetUser, array $roleIds, User $currentUser, string $operation): void
    {
        $this->validateSuperAdminCount($targetUser, $roleIds, $operation);
        $this->validateAdminCount($targetUser, $roleIds, $operation);
        $this->validateCriticalRoleModification($targetUser, $roleIds, $currentUser);
        $this->validateSelfModification($targetUser, $currentUser, $roleIds);
    }

    protected function validateSuperAdminCount(User $targetUser, array $roleIds, string $operation): void
    {
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $currentSuperAdminCount = User::role('Super Admin')->count();

        // Sprawdź czy operacja spowoduje spadek poniżej minimum Super Adminów
        if ($targetUser->hasRole('Super Admin') && !in_array($superAdminRole->id, $roleIds)) {
            if (($operation === 'sync' || $operation === 'revoke') && $currentSuperAdminCount <= self::MINIMUM_SUPER_ADMINS) {
                throw new \Exception(
                    'Nie można wykonać operacji: Musi pozostać przynajmniej ' . self::MINIMUM_SUPER_ADMINS . ' Super Admin w systemie. Obecnie: ' . $currentSuperAdminCount,
                    403
                );
            }
        }
    }

    protected function validateAdminCount(User $targetUser, array $roleIds, string $operation): void
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        $currentAdminCount = User::role(['Admin', 'Super Admin'])->count();

        // Sprawdź czy użytkownik ma rolę Admin lub Super Admin
        $hasAdminRights = $targetUser->hasAnyRole(['Admin', 'Super Admin']);
        $willHaveAdminRights = in_array($adminRole->id, $roleIds) || in_array($superAdminRole->id, $roleIds);

        if ($hasAdminRights && !$willHaveAdminRights) {
            if (($operation === 'sync' || $operation === 'revoke') && $currentAdminCount <= self::MINIMUM_ADMINS) {
                throw new \Exception(
                    'Nie można wykonać operacji: Musi pozostać przynajmniej ' . self::MINIMUM_ADMINS . ' administrator w systemie. Obecnie: ' . $currentAdminCount,
                    403
                );
            }
        }
    }

    protected function validateCriticalRoleModification(User $targetUser, array $roleIds, User $currentUser): void
    {
        $roles = Role::whereIn('id', $roleIds)->get();
        $superAdminRole = $roles->firstWhere('name', 'Super Admin');

        // Tylko Super Admin może przypisywać rolę Super Admin
        if ($superAdminRole && !$currentUser->isSuperAdmin()) {
            throw new \Exception('Tylko Super Admin może przypisywać rolę Super Admin.', 403);
        }

        // Tylko Super Admin może modyfikować innych Super Adminów
        if ($targetUser->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            throw new \Exception('Tylko Super Admin może modyfikować innych Super Adminów.', 403);
        }
    }

    protected function validateSelfModification(User $targetUser, User $currentUser, array $roleIds): void
    {
        // Super Admin może modyfikować swoje własne role
        if ($currentUser->isSuperAdmin()) {
            return;
        }

        // Zwykli użytkownicy nie mogą modyfikować własnych ról
        if ($targetUser->id === $currentUser->id) {
            throw new \Exception('Nie możesz modyfikować własnych ról.', 403);
        }
    }

    public function getSystemSecurityStatus(): array
    {
        $superAdminCount = User::role('Super Admin')->count();
        $adminCount = User::role(['Admin', 'Super Admin'])->count();

        return [
            'super_admin_count' => $superAdminCount,
            'admin_count' => $adminCount,
            'minimum_super_admins' => self::MINIMUM_SUPER_ADMINS,
            'minimum_admins' => self::MINIMUM_ADMINS,
            'super_admin_status' => $superAdminCount >= self::MINIMUM_SUPER_ADMINS ? 'safe' : 'critical',
            'admin_status' => $adminCount >= self::MINIMUM_ADMINS ? 'safe' : 'critical',
            'warnings' => $this->getSecurityWarnings($superAdminCount, $adminCount)
        ];
    }

    protected function getSecurityWarnings(int $superAdminCount, int $adminCount): array
    {
        $warnings = [];

        if ($superAdminCount < self::MINIMUM_SUPER_ADMINS) {
            $warnings[] = 'KRYTYCZNE: Zbyt mała liczba Super Adminów! Zalecane minimum: ' . self::MINIMUM_SUPER_ADMINS;
        } elseif ($superAdminCount === self::MINIMUM_SUPER_ADMINS) {
            $warnings[] = 'UWAGA: Masz dokładnie minimum Super Adminów. Rozważ dodanie kolejnego.';
        }

        if ($adminCount < self::MINIMUM_ADMINS) {
            $warnings[] = 'KRYTYCZNE: Brak administratorów w systemie!';
        } elseif ($adminCount === self::MINIMUM_ADMINS) {
            $warnings[] = 'UWAGA: Masz dokładnie minimum administratorów. Rozważ dodanie kolejnego.';
        }

        return $warnings;
    }

    public function canDeleteUser(User $user): array
    {
        $canDelete = true;
        $reasons = [];

        if ($user->isSuperAdmin()) {
            $superAdminCount = User::role('Super Admin')->count();
            if ($superAdminCount <= self::MINIMUM_SUPER_ADMINS) {
                $canDelete = false;
                $reasons[] = 'Nie można usunąć: To jeden z ostatnich Super Adminów';
            }
        }

        if ($user->hasAnyRole(['Admin', 'Super Admin'])) {
            $adminCount = User::role(['Admin', 'Super Admin'])->count();
            if ($adminCount <= self::MINIMUM_ADMINS) {
                $canDelete = false;
                $reasons[] = 'Nie można usunąć: To jeden z ostatnich administratorów';
            }
        }

        return [
            'can_delete' => $canDelete,
            'reasons' => $reasons
        ];
    }

    public function validateRoleAssignment(array $roles, User $creator): void
    {
        // Simple validation for role assignment
        if (in_array('Super Admin', $roles) && !$creator->isSuperAdmin()) {
            throw new \Exception('Only Super Admin can assign Super Admin role', 403);
        }
    }

    public function getEmergencyRecoveryInfo(): array
    {
        return [
            'super_admin_recovery' => [
                'description' => 'Jeśli utracisz dostęp do wszystkich Super Admin:',
                'steps' => [
                    '1. Uruchom: php artisan tinker',
                    '2. Wykonaj: $user = User::where(\'email\', \'twoj@email.pl\')->first();',
                    '3. Wykonaj: $user->assignRole(\'Super Admin\');',
                    '4. Lub uruchom seeder: php artisan db:seed --class=SuperAdminSeeder'
                ]
            ],
            'admin_recovery' => [
                'description' => 'Jeśli utracisz dostęp do dashboard:',
                'steps' => [
                    '1. Sprawdź czy masz użytkownika z rolą Admin lub Super Admin',
                    '2. W razie potrzeby użyj powyższej procedury dla Super Admin',
                    '3. Sprawdź uprawnienie dashboard.view'
                ]
            ]
        ];
    }
}