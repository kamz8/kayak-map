<?php

use App\Http\Controllers\Api\V1\GPXController;
use App\Http\Controllers\Api\V1\RegionController;
use App\Http\Controllers\Api\V1\ReverseGeocodingController;
use App\Http\Controllers\Api\V1\RiverTrackController;
use App\Http\Controllers\Api\V1\TrailController;
use App\Http\Controllers\Api\V1\TrailGeocodingController;
use App\Http\Controllers\Api\V1\WeatherProxyController;
use App\Http\Controllers\Api\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');*/


Route::middleware('api')->prefix('v1')->group(function () {
    Route::get('/', function () {
        return ['message'=>'Witamy w naszym api', ];
    });
    Route::get('trails', [TrailController::class, 'index']);
    Route::get('/trails/nearby', [TrailController::class, 'getNearbyTrails']);
    Route::get('trail/{slug}', [TrailController::class, 'show']);
    Route::get('/river-track/{id}', [RiverTrackController::class, 'show']);
    Route::post('/upload-gpx', [GPXController::class, 'upload']);

    Route::prefix('regions')->group(function () {
        Route::get('/', [RegionController::class, 'index']);
        Route::get('/{slug}', [RegionController::class, 'show']);
    });

    Route::get('regions/{slug}/trails', [TrailController::class, 'getTrailsByRegion']);
    Route::get('/weather', [WeatherProxyController::class, 'getWeather']);

    // reverse geocoding
    Route::middleware('throttle.nominatim')->group(function (){
        Route::post('/geocoding/reverse', [ReverseGeocodingController::class, 'reverseGeocode']);
        Route::get('/trails/{id}/reverse_geocode', [TrailGeocodingController::class, 'getStartPointRegion']);
    });

});


