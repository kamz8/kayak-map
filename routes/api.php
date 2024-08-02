<?php

use App\Http\Controllers\Api\RiverTrackController;
use App\Http\Controllers\Api\V1\GPXController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




use App\Http\Controllers\Api\V1\TrailController;

Route::prefix('v1')->group(function () {
    Route::get('/', function () {
        return ['message'=>'Witamy w naszym api', ];
    });
    Route::get('trails', [TrailController::class, 'index']);
    Route::get('/river-track/{id}', [RiverTrackController::class, 'show']);
    Route::post('/upload-gpx', [GPXController::class, 'upload']);

});
