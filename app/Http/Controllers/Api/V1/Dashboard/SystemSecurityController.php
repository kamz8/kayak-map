<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Responses\Dashboard\ApiResponse;
use App\Models\User;
use App\Services\Dashboard\UserRoleService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Dashboard - System Security",
 *     description="Monitorowanie bezpieczeństwa systemu i zabezpieczenia przed utratą kontroli"
 * )
 */
class SystemSecurityController extends Controller
{
    public function __construct(
        private readonly UserRoleService $userRoleService
    ) {
        $this->middleware('auth:api');
        $this->middleware('permission:dashboard.view');
        $this->middleware('permission:system.security.view')->only(['status', 'emergencyInfo']);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/security/status",
     *     tags={"Dashboard - System Security"},
     *     summary="Sprawdź status bezpieczeństwa systemu",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Status bezpieczeństwa systemu"
     *     )
     * )
     */
    public function status(): JsonResponse
    {
        try {
            $status = $this->userRoleService->getSystemSecurityStatus();

            return ApiResponse::success(
                $status,
                'Status bezpieczeństwa systemu został pobrany pomyślnie'
            );

        } catch (\Exception $e) {
            return ApiResponse::serverError('Nie udało się pobrać statusu bezpieczeństwa');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/users/{user}/can-delete",
     *     tags={"Dashboard - System Security"},
     *     summary="Sprawdź czy użytkownika można bezpiecznie usunąć",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informacja o możliwości usunięcia użytkownika"
     *     )
     * )
     */
    public function canDeleteUser(User $user): JsonResponse
    {
        try {
            $canDelete = $this->userRoleService->canDeleteUser($user);

            return ApiResponse::success(
                $canDelete,
                $canDelete['can_delete']
                    ? 'Użytkownika można bezpiecznie usunąć'
                    : 'Użytkownika nie można usunąć ze względów bezpieczeństwa'
            );

        } catch (\Exception $e) {
            return ApiResponse::serverError('Nie udało się sprawdzić możliwości usunięcia użytkownika');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/security/emergency-info",
     *     tags={"Dashboard - System Security"},
     *     summary="Pobierz informacje o odzyskiwaniu dostępu w nagłych wypadkach",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Instrukcje odzyskiwania dostępu"
     *     )
     * )
     */
    public function emergencyInfo(): JsonResponse
    {
        try {
            $info = $this->userRoleService->getEmergencyRecoveryInfo();

            return ApiResponse::success(
                $info,
                'Informacje o odzyskiwaniu dostępu zostały pobrane pomyślnie'
            );

        } catch (\Exception $e) {
            return ApiResponse::serverError('Nie udało się pobrać informacji o odzyskiwaniu dostępu');
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/security/admin-summary",
     *     tags={"Dashboard - System Security"},
     *     summary="Pobierz podsumowanie administratorów w systemie",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Podsumowanie administratorów"
     *     )
     * )
     */
    public function adminSummary(): JsonResponse
    {
        try {
            $superAdmins = User::role('Super Admin')
                ->select('id', 'first_name', 'last_name', 'email', 'created_at', 'last_login_at')
                ->with('roles:id,name')
                ->get();

            $admins = User::role('Admin')
                ->select('id', 'first_name', 'last_name', 'email', 'created_at', 'last_login_at')
                ->with('roles:id,name')
                ->get();

            $securityStatus = $this->userRoleService->getSystemSecurityStatus();

            $data = [
                'super_admins' => $superAdmins,
                'admins' => $admins,
                'counts' => [
                    'super_admin_count' => $superAdmins->count(),
                    'admin_count' => $admins->count(),
                    'total_admin_accounts' => $superAdmins->count() + $admins->count()
                ],
                'security_status' => $securityStatus
            ];

            return ApiResponse::success(
                $data,
                'Podsumowanie administratorów zostało pobrane pomyślnie'
            );

        } catch (\Exception $e) {
            return ApiResponse::serverError('Nie udało się pobrać podsumowania administratorów');
        }
    }
}