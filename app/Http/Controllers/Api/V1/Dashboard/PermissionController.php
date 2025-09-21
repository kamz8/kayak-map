<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Permission\CreatePermissionRequest;
use App\Http\Requests\Dashboard\Permission\UpdatePermissionRequest;
use App\Http\Responses\Dashboard\PermissionResponse;
use App\Services\Dashboard\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

/**
 * @OA\Tag(
 *     name="Dashboard - Permissions",
 *     description="Zarządzanie uprawnieniami w panelu administracyjnym"
 * )
 */
class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionService $permissionService
    ) {
        $this->middleware('auth:api');
        $this->middleware('permission:dashboard.view');
        $this->middleware('permission:permissions.view')->only(['index', 'show', 'modules']);
        $this->middleware('permission:permissions.create')->only(['store']);
        $this->middleware('permission:permissions.update')->only(['update']);
        $this->middleware('permission:permissions.delete')->only(['destroy']);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/permissions",
     *     tags={"Dashboard - Permissions"},
     *     summary="Pobierz listę uprawnień",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="module",
     *         in="query",
     *         description="Filtruj według modułu",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="with_roles",
     *         in="query",
     *         description="Dołącz role",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="grouped",
     *         in="query",
     *         description="Pogrupuj według modułów",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista uprawnień pobrana pomyślnie"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            if ($request->boolean('grouped')) {
                $permissions = $this->permissionService->getGroupedPermissions();
                return PermissionResponse::grouped($permissions);
            }

            $filters = [
                'module' => $request->get('module'),
                'with_roles' => $request->boolean('with_roles'),
            ];

            $permissions = $this->permissionService->getAllPermissions($filters);
            return PermissionResponse::collection($permissions);

        } catch (\Exception $e) {
            return PermissionResponse::collection(collect([]));
        }
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/permissions",
     *     tags={"Dashboard - Permissions"},
     *     summary="Utwórz nowe uprawnienie",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=201,
     *         description="Uprawnienie utworzone pomyślnie"
     *     )
     * )
     */
    public function store(CreatePermissionRequest $request): JsonResponse
    {
        try {
            $permission = $this->permissionService->createPermission($request->validated());
            return PermissionResponse::created($permission);

        } catch (\Exception $e) {
            return PermissionResponse::single(new Permission());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/permissions/{permission}",
     *     tags={"Dashboard - Permissions"},
     *     summary="Pobierz szczegóły uprawnienia",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Uprawnienie pobrane pomyślnie"
     *     )
     * )
     */
    public function show(Permission $permission): JsonResponse
    {
        try {
            $permissionWithRelations = $this->permissionService->findPermission($permission->id, ['roles']);
            return PermissionResponse::single($permissionWithRelations);

        } catch (\Exception $e) {
            return PermissionResponse::single(new Permission());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/permissions/{permission}",
     *     tags={"Dashboard - Permissions"},
     *     summary="Zaktualizuj uprawnienie",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Uprawnienie zaktualizowane pomyślnie"
     *     )
     * )
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        try {
            $updatedPermission = $this->permissionService->updatePermission($permission, $request->validated());
            return PermissionResponse::updated($updatedPermission);

        } catch (\Exception $e) {
            return PermissionResponse::single(new Permission());
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/permissions/{permission}",
     *     tags={"Dashboard - Permissions"},
     *     summary="Usuń uprawnienie",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="permission",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Uprawnienie usunięte pomyślnie"
     *     )
     * )
     */
    public function destroy(Permission $permission): JsonResponse
    {
        try {
            $this->permissionService->deletePermission($permission);
            return PermissionResponse::deleted();

        } catch (\Exception $e) {
            return PermissionResponse::cannotDeleteCorePermission();
        }
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/permissions/modules",
     *     tags={"Dashboard - Permissions"},
     *     summary="Pobierz dostępne moduły uprawnień",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista modułów pobrana pomyślnie"
     *     )
     * )
     */
    public function modules(): JsonResponse
    {
        try {
            $modules = $this->permissionService->getAvailableModules();
            return PermissionResponse::modules($modules);

        } catch (\Exception $e) {
            return PermissionResponse::modules([]);
        }
    }
}
