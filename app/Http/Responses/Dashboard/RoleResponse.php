<?php

namespace App\Http\Responses\Dashboard;

use App\Http\Resources\RoleResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class RoleResponse
{
    public static function collection(Collection $roles): JsonResponse
    {
        return ApiResponse::success(
            RoleResource::collection($roles),
            'Lista ról została pobrana pomyślnie'
        );
    }

    public static function single(Role $role): JsonResponse
    {
        return ApiResponse::success(
            new RoleResource($role),
            'Rola została pobrana pomyślnie'
        );
    }

    public static function created(Role $role): JsonResponse
    {
        return ApiResponse::created(
            new RoleResource($role),
            'Rola została utworzona pomyślnie'
        );
    }

    public static function updated(Role $role): JsonResponse
    {
        return ApiResponse::success(
            new RoleResource($role),
            'Rola została zaktualizowana pomyślnie'
        );
    }

    public static function deleted(): JsonResponse
    {
        return ApiResponse::noContent(
            'Rola została usunięta pomyślnie'
        );
    }

    public static function permissionsAssigned(Role $role): JsonResponse
    {
        return ApiResponse::success(
            new RoleResource($role),
            'Uprawnienia zostały przypisane do roli pomyślnie'
        );
    }

    public static function permissionsRevoked(Role $role): JsonResponse
    {
        return ApiResponse::success(
            new RoleResource($role),
            'Uprawnienia zostały odebrane od roli pomyślnie'
        );
    }

    public static function cannotModifySuperAdmin(): JsonResponse
    {
        return ApiResponse::forbidden(
            'Tylko Super Admin może modyfikować rolę Super Admin'
        );
    }

    public static function cannotDeleteSystemRole(): JsonResponse
    {
        return ApiResponse::forbidden(
            'Nie można usunąć ról systemowych'
        );
    }

    public static function superAdminBypassesPermissions(): JsonResponse
    {
        return ApiResponse::error(
            'Rola Super Admin omija wszystkie sprawdzenia uprawnień',
            422
        );
    }
}