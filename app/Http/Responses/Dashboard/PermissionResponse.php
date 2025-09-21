<?php

namespace App\Http\Responses\Dashboard;

use App\Http\Resources\PermissionResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;

class PermissionResponse
{
    public static function collection(Collection $permissions): JsonResponse
    {
        return ApiResponse::success(
            PermissionResource::collection($permissions),
            'Lista uprawnień została pobrana pomyślnie'
        );
    }

    public static function grouped(array $permissions): JsonResponse
    {
        return ApiResponse::success(
            $permissions,
            'Pogrupowane uprawnienia zostały pobrane pomyślnie'
        );
    }

    public static function single(Permission $permission): JsonResponse
    {
        return ApiResponse::success(
            new PermissionResource($permission),
            'Uprawnienie zostało pobrane pomyślnie'
        );
    }

    public static function created(Permission $permission): JsonResponse
    {
        return ApiResponse::created(
            new PermissionResource($permission),
            'Uprawnienie zostało utworzone pomyślnie'
        );
    }

    public static function updated(Permission $permission): JsonResponse
    {
        return ApiResponse::success(
            new PermissionResource($permission),
            'Uprawnienie zostało zaktualizowane pomyślnie'
        );
    }

    public static function deleted(): JsonResponse
    {
        return ApiResponse::noContent(
            'Uprawnienie zostało usunięte pomyślnie'
        );
    }

    public static function modules(array $modules): JsonResponse
    {
        return ApiResponse::success(
            ['modules' => $modules],
            'Lista modułów została pobrana pomyślnie'
        );
    }

    public static function cannotDeleteCorePermission(): JsonResponse
    {
        return ApiResponse::forbidden(
            'Nie można usunąć podstawowych uprawnień systemowych'
        );
    }
}