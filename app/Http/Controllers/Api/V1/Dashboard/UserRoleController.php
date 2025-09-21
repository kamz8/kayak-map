<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UserRole\AssignRolesRequest;
use App\Http\Responses\Dashboard\UserRoleResponse;
use App\Models\User;
use App\Services\Dashboard\UserRoleService;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Dashboard - User Roles",
 *     description="Zarządzanie rolami użytkowników w panelu administracyjnym"
 * )
 */
class UserRoleController extends Controller
{
    public function __construct(
        private readonly UserRoleService $userRoleService
    ) {
        $this->middleware('auth:api');
        $this->middleware('permission:dashboard.view');
        $this->middleware('permission:users.view')->only(['getUserRoles']);
        $this->middleware('permission:users.assign_roles')->only(['assignRoles', 'syncRoles']);
        $this->middleware('permission:users.revoke_roles')->only(['revokeRoles']);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/users/{user}/roles",
     *     tags={"Dashboard - User Roles"},
     *     summary="Pobierz role użytkownika",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role użytkownika pobrane pomyślnie"
     *     )
     * )
     */
    public function getUserRoles(User $user): JsonResponse
    {
        try {
            $userWithRoles = $this->userRoleService->getUserWithRoles($user);
            return UserRoleResponse::userWithRoles($userWithRoles);

        } catch (\Exception $e) {
            return UserRoleResponse::userWithRoles(new User());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/users/{user}/assign-roles",
     *     tags={"Dashboard - User Roles"},
     *     summary="Przypisz role użytkownikowi",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role przypisane pomyślnie"
     *     )
     * )
     */
    public function assignRoles(AssignRolesRequest $request, User $user): JsonResponse
    {
        try {
            $userWithRoles = $this->userRoleService->assignRoles(
                $user,
                $request->validated()['roles'],
                $request->user()
            );
            return UserRoleResponse::rolesAssigned($userWithRoles);

        } catch (\Exception $e) {
            return $this->handleUserRoleException($e);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/users/{user}/revoke-roles",
     *     tags={"Dashboard - User Roles"},
     *     summary="Odbierz role użytkownikowi",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role odebrane pomyślnie"
     *     )
     * )
     */
    public function revokeRoles(AssignRolesRequest $request, User $user): JsonResponse
    {
        try {
            $userWithRoles = $this->userRoleService->revokeRoles(
                $user,
                $request->validated()['roles'],
                $request->user()
            );
            return UserRoleResponse::rolesRevoked($userWithRoles);

        } catch (\Exception $e) {
            return $this->handleUserRoleException($e);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/users/{user}/sync-roles",
     *     tags={"Dashboard - User Roles"},
     *     summary="Zsynchronizuj role użytkownika (zastąp wszystkie)",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role zsynchronizowane pomyślnie"
     *     )
     * )
     */
    public function syncRoles(AssignRolesRequest $request, User $user): JsonResponse
    {
        try {
            $userWithRoles = $this->userRoleService->syncRoles(
                $user,
                $request->validated()['roles'],
                $request->user()
            );
            return UserRoleResponse::rolesSynchronized($userWithRoles);

        } catch (\Exception $e) {
            return $this->handleUserRoleException($e);
        }
    }

    private function handleUserRoleException(\Exception $e): JsonResponse
    {
        $message = $e->getMessage();

        return match (true) {
            str_contains($message, 'Only Super Admin can assign') => UserRoleResponse::onlySuperAdminCanAssignSuperAdmin(),
            str_contains($message, 'cannot modify your own') => UserRoleResponse::cannotModifyOwnRoles(),
            str_contains($message, 'Only Super Admin can modify other') => UserRoleResponse::onlySuperAdminCanModifyOtherSuperAdmin(),
            str_contains($message, 'Cannot revoke the last') => UserRoleResponse::cannotRevokeLastSuperAdmin(),
            str_contains($message, 'Cannot remove the last') => UserRoleResponse::cannotRemoveLastSuperAdmin(),
            default => UserRoleResponse::userWithRoles(new User())
        };
    }
}
