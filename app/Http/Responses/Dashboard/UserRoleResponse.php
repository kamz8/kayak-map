<?php

namespace App\Http\Responses\Dashboard;

use App\Http\Resources\Auth\UserResource;
use App\Http\Resources\RoleResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserRoleResponse
{
    public static function userWithRoles(User $user): JsonResponse
    {
        return ApiResponse::success([
            'user' => new UserResource($user),
            'roles' => RoleResource::collection($user->roles)
        ], 'Role użytkownika zostały pobrane pomyślnie');
    }

    public static function rolesAssigned(User $user): JsonResponse
    {
        return ApiResponse::success([
            'user' => new UserResource($user),
            'roles' => RoleResource::collection($user->roles),
        ], 'Role zostały przypisane do użytkownika pomyślnie');
    }

    public static function rolesRevoked(User $user): JsonResponse
    {
        return ApiResponse::success([
            'user' => new UserResource($user),
            'roles' => RoleResource::collection($user->roles),
        ], 'Role zostały odebrane użytkownikowi pomyślnie');
    }

    public static function rolesSynchronized(User $user): JsonResponse
    {
        return ApiResponse::success([
            'user' => new UserResource($user),
            'roles' => RoleResource::collection($user->roles),
        ], 'Role użytkownika zostały zsynchronizowane pomyślnie');
    }

    public static function onlySuperAdminCanAssignSuperAdmin(): JsonResponse
    {
        return ApiResponse::forbidden(
            'Tylko Super Admin może przypisywać rolę Super Admin'
        );
    }

    public static function cannotModifyOwnRoles(): JsonResponse
    {
        return ApiResponse::forbidden(
            'Nie możesz modyfikować własnych ról'
        );
    }

    public static function onlySuperAdminCanModifyOtherSuperAdmin(): JsonResponse
    {
        return ApiResponse::forbidden(
            'Tylko Super Admin może modyfikować innych użytkowników z rolą Super Admin'
        );
    }

    public static function cannotRevokeLastSuperAdmin(): JsonResponse
    {
        return ApiResponse::forbidden(
            'Nie można odebrać ostatniej roli Super Admin'
        );
    }

    public static function cannotRemoveLastSuperAdmin(): JsonResponse
    {
        return ApiResponse::forbidden(
            'Nie można usunąć ostatniej roli Super Admin'
        );
    }
}