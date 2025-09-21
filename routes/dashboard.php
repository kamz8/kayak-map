<?php

use App\Http\Controllers\Api\V1\Dashboard\PermissionController;
use App\Http\Controllers\Api\V1\Dashboard\RoleController;
use App\Http\Controllers\Api\V1\Dashboard\SystemSecurityController;
use App\Http\Controllers\Api\V1\Dashboard\UserController;
use App\Http\Controllers\Api\V1\Dashboard\UserRoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dashboard API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes specifically for the dashboard administration panel.
| All routes require authentication and dashboard access permission.
|
*/

Route::prefix('/dashboard')
    ->middleware(['api'])
    ->group(function () {

        // Users Management
        Route::apiResource('users', UserController::class);

        // Roles Management
        Route::apiResource('roles', RoleController::class);
        Route::post('roles/{role}/assign-permissions', [RoleController::class, 'assignPermissions'])
            ->name('dashboard.roles.assign-permissions');
        Route::delete('roles/{role}/revoke-permissions', [RoleController::class, 'revokePermissions'])
            ->name('dashboard.roles.revoke-permissions');

        // Permissions Management
        Route::apiResource('permissions', PermissionController::class);
        Route::get('permissions/modules', [PermissionController::class, 'modules'])
            ->name('dashboard.permissions.modules');

        // User Role Management
        Route::prefix('users/{user}')->group(function () {
            Route::get('roles', [UserRoleController::class, 'getUserRoles'])
                ->name('dashboard.users.roles');
            Route::post('assign-roles', [UserRoleController::class, 'assignRoles'])
                ->name('dashboard.users.assign-roles');
            Route::delete('revoke-roles', [UserRoleController::class, 'revokeRoles'])
                ->name('dashboard.users.revoke-roles');
            Route::put('sync-roles', [UserRoleController::class, 'syncRoles'])
                ->name('dashboard.users.sync-roles');
            Route::get('can-delete', [SystemSecurityController::class, 'canDeleteUser'])
                ->name('dashboard.users.can-delete');
        });

        // System Security Monitoring
        Route::prefix('security')->group(function () {
            Route::get('status', [SystemSecurityController::class, 'status'])
                ->name('dashboard.security.status');
            Route::get('admin-summary', [SystemSecurityController::class, 'adminSummary'])
                ->name('dashboard.security.admin-summary');
            Route::get('emergency-info', [SystemSecurityController::class, 'emergencyInfo'])
                ->name('dashboard.security.emergency-info');
        });
    });
