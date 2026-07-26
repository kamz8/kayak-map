<?php

use Illuminate\Support\Facades\Route;
use Kamz\LaravelBRouter\Http\Controllers\RouteController;

Route::prefix('api/brouter')->group(function () {
    Route::post('/route', [RouteController::class, 'findRoute']);
    Route::get('/nearest-waterway', [RouteController::class, 'findNearestWaterway']);
    Route::get('/health', [RouteController::class, 'health']);
});
