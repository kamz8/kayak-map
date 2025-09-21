<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Role\AssignPermissionsRequest;
use App\Http\Requests\Dashboard\Role\CreateRoleRequest;
use App\Http\Requests\Dashboard\Role\UpdateRoleRequest;
use App\Http\Responses\Dashboard\RoleResponse;
use App\Services\Dashboard\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

/**
 * @OA\Tag(
 *     name="Dashboard - Roles",
 *     description="Zarządzanie rolami w panelu administracyjnym"
 * )
 */
class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService
    ) {
        $this->middleware('auth:api');
        $this->middleware('permission:dashboard.view');
        $this->middleware('permission:roles.view')->only(['index', 'show']);
        $this->middleware('permission:roles.create')->only(['store']);
        $this->middleware('permission:roles.update')->only(['update']);
        $this->middleware('permission:roles.delete')->only(['destroy']);
        $this->middleware('permission:roles.assign_permissions')->only(['assignPermissions']);
        $this->middleware('permission:roles.revoke_permissions')->only(['revokePermissions']);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/roles",
     *     tags={"Dashboard - Roles"},
     *     summary="Pobierz listę ról",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="with_permissions",
     *         in="query",
     *         description="Dołącz uprawnienia",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="with_users_count",
     *         in="query",
     *         description="Dołącz liczbę użytkowników",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista ról pobrana pomyślnie"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'with_permissions' => $request->boolean('with_permissions'),
                'with_users_count' => $request->boolean('with_users_count'),
            ];

            $roles = $this->roleService->getAllRoles($filters);
            return RoleResponse::collection($roles);

        } catch (\Exception $e) {
            return RoleResponse::single(new Role()); // This will trigger error in response
        }
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/roles",
     *     tags={"Dashboard - Roles"},
     *     summary="Utwórz nową rolę",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Manager"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="integer"), example={1, 2, 3})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rola utworzona pomyślnie"
     *     )
     * )
     */
    public function store(CreateRoleRequest $request): JsonResponse
    {
        try {
            $role = $this->roleService->createRole($request->validated());
            return RoleResponse::created($role);

        } catch (\Exception $e) {
            return RoleResponse::cannotDeleteSystemRole();
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/roles/{role}",
     *     tags={"Dashboard - Roles"},
     *     summary="Pobierz szczegóły roli",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rola pobrana pomyślnie"
     *     )
     * )
     */
    public function show(Role $role): JsonResponse
    {
        try {
            $roleWithRelations = $this->roleService->findRole($role->id, ['permissions', 'users']);
            return RoleResponse::single($roleWithRelations);

        } catch (\Exception $e) {
            return RoleResponse::single(new Role());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/roles/{role}",
     *     tags={"Dashboard - Roles"},
     *     summary="Zaktualizuj rolę",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rola zaktualizowana pomyślnie"
     *     )
     * )
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        try {
            $updatedRole = $this->roleService->updateRole($role, $request->validated(), $request->user());
            return RoleResponse::updated($updatedRole);

        } catch (\Exception $e) {
            if ($e->getCode() === 403) {
                return RoleResponse::cannotModifySuperAdmin();
            }
            return RoleResponse::single(new Role());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/roles/{role}",
     *     tags={"Dashboard - Roles"},
     *     summary="Usuń rolę",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Rola usunięta pomyślnie"
     *     )
     * )
     */
    public function destroy(Role $role): JsonResponse
    {
        try {
            $this->roleService->deleteRole($role);
            return RoleResponse::deleted();

        } catch (\Exception $e) {
            return RoleResponse::cannotDeleteSystemRole();
        }
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/roles/{role}/assign-permissions",
     *     tags={"Dashboard - Roles"},
     *     summary="Przypisz uprawnienia do roli",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Uprawnienia przypisane pomyślnie"
     *     )
     * )
     */
    public function assignPermissions(AssignPermissionsRequest $request, Role $role): JsonResponse
    {
        try {
            $updatedRole = $this->roleService->assignPermissions($role, $request->validated()['permissions']);
            return RoleResponse::permissionsAssigned($updatedRole);

        } catch (\Exception $e) {
            if ($e->getCode() === 422) {
                return RoleResponse::superAdminBypassesPermissions();
            }
            return RoleResponse::single(new Role());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/roles/{role}/revoke-permissions",
     *     tags={"Dashboard - Roles"},
     *     summary="Odbierz uprawnienia od roli",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Uprawnienia odebrane pomyślnie"
     *     )
     * )
     */
    public function revokePermissions(AssignPermissionsRequest $request, Role $role): JsonResponse
    {
        try {
            $updatedRole = $this->roleService->revokePermissions($role, $request->validated()['permissions']);
            return RoleResponse::permissionsRevoked($updatedRole);

        } catch (\Exception $e) {
            if ($e->getCode() === 422) {
                return RoleResponse::superAdminBypassesPermissions();
            }
            return RoleResponse::single(new Role());
        }
    }
}
