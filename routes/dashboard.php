<?php

use App\Http\Controllers\Api\V1\Dashboard\LinkController;
use App\Http\Controllers\Api\V1\Dashboard\PermissionController;
use App\Http\Controllers\Api\V1\Dashboard\RoleController;
use App\Http\Controllers\Api\V1\Dashboard\SystemSecurityController;
use App\Http\Controllers\Api\V1\Dashboard\TrailController;
use App\Http\Controllers\Api\V1\Dashboard\UserController;
use App\Http\Controllers\Api\V1\Dashboard\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::prefix('/dashboard')
    ->middleware(['api'])
    ->group(function () {

        // Trails Management
        Route::get('trails/statistics', [TrailController::class, 'statistics'])
            ->name('dashboard.trails.statistics');

        // Bulk Operations (must be before apiResource)
        Route::post('trails/bulk-status', [TrailController::class, 'bulkChangeStatus'])
            ->name('dashboard.trails.bulk-status');
        Route::get('trails/batch-status/{batchId}', [TrailController::class, 'getBatchStatus'])
            ->name('dashboard.trails.batch-status');

        // SPECYFICZNE TRASY Z {trail} PRZED apiResource
        Route::patch('trails/{trail}/status', [TrailController::class, 'changeStatus'])
            ->name('dashboard.trails.change-status');
        Route::get('trails/{trail}', [TrailController::class, 'show'])
            ->name('dashboard.trails.show');
        Route::put('trails/{trail}', [TrailController::class, 'update'])
            ->name('dashboard.trails.update');
        Route::delete('trails/{trail}', [TrailController::class, 'destroy'])
            ->name('dashboard.trails.destroy');

        // Pozostałe metody z apiResource
        Route::get('trails', [TrailController::class, 'index'])
            ->name('dashboard.trails.index');
        Route::post('trails', [TrailController::class, 'store'])
            ->name('dashboard.trails.store');

        // Trail Links Management
        Route::prefix('trails/{id}')->group(function () {
            Route::get('links', [LinkController::class, 'indexForTrail'])
                ->name('dashboard.trails.links.index');
            Route::post('links', [LinkController::class, 'storeForTrail'])
                ->name('dashboard.trails.links.store');
            Route::put('links/{linkId}', [LinkController::class, 'updateForTrail'])
                ->name('dashboard.trails.links.update');
            Route::delete('links/{linkId}', [LinkController::class, 'destroyForTrail'])
                ->name('dashboard.trails.links.destroy');
        });

        // Section Links Management
        Route::prefix('trails/{trailId}/sections/{sectionId}')->group(function () {
            Route::get('links', [LinkController::class, 'indexForSection'])
                ->name('dashboard.trails.sections.links.index');
            Route::post('links', [LinkController::class, 'storeForSection'])
                ->name('dashboard.trails.sections.links.store');
            Route::put('links/{linkId}', [LinkController::class, 'updateForSection'])
                ->name('dashboard.trails.sections.links.update');
            Route::delete('links/{linkId}', [LinkController::class, 'destroyForSection'])
                ->name('dashboard.trails.sections.links.destroy');
        });

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

Route::fallback(function() {
    return response()->json([
        'error' => [
            'code' => 404,
            'message' => 'API endpoint not found'
        ]
    ], 404);
});
