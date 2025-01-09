<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\SocialAuthController;
use App\Http\Controllers\Api\V1\GPXController;
use App\Http\Controllers\Api\V1\RegionController;
use App\Http\Controllers\Api\V1\ReverseGeocodingController;
use App\Http\Controllers\Api\V1\RiverTrackController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\TrailController;
use App\Http\Controllers\Api\V1\TrailGeocodingController;
use App\Http\Controllers\Api\V1\TrailMapController;
use App\Http\Controllers\Api\V1\WeatherProxyController;
use App\Http\Middleware\Auth\CheckRegistrationEnabled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');*/

Route::middleware('api')->group(function () {
    Route::get('/', function () {
        return ['message'=>'Witamy w naszym api', ];
    });
    Route::get('trails', [TrailController::class, 'index']);
    Route::get('/trails/nearby', [TrailController::class, 'getNearbyTrails']);
    Route::get('trail/{slug}', [TrailController::class, 'show']);
    Route::get('/trails/{slug}/recommended', [TrailController::class, 'getRecommendedTrails']);
    Route::get('/river-track/{id}', [RiverTrackController::class, 'show']);
    Route::post('/upload-gpx', [GPXController::class, 'upload']);

    Route::prefix('regions')->group(function () {
        Route::get('/', [RegionController::class, 'index']);
        Route::get('{slug}', [RegionController::class, 'show']);
        Route::get('{slug}/top-trails', [RegionController::class, 'topTrails']);
    });
    Route::get('regions/{slug}/top-trails-nearby', [RegionController::class, 'getTopTrailsNearby']);
    Route::get('regions/{slug}/trails', [RegionController::class, 'getTrailsByRegion']);
    Route::get('/weather', [WeatherProxyController::class, 'getWeather']);

    // reverse geocoding
    Route::middleware('throttle.nominatim')->group(function (){
        Route::post('/geocoding/reverse', [ReverseGeocodingController::class, 'reverseGeocode']);
        Route::get('/trails/{id}/reverse_geocode', [TrailGeocodingController::class, 'getStartPointRegion']);

    });

    Route::get('search', [SearchController::class, 'search'])->middleware('throttle:60,1');

    Route::get('/trails/{slug}/static-map', [TrailMapController::class, 'getStaticMap'])
        ->name('api.trails.static-map');

    Route::get('/trails/{slug}/test-map', [TrailMapController::class, 'testMap']);

    Route::prefix('auth')->group(function () {
        Route::middleware('throttle:6,1')->group(function () {
            Route::post('login', [AuthController::class, 'login']);
            Route::post('refresh', [AuthController::class, 'refresh']);
        });
        Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink']);
        Route::post('reset-password', [PasswordResetController::class, 'reset']);
        // Public authentication routes
        Route::middleware([
            'throttle:registration',
            CheckRegistrationEnabled::class,
        ])->group(function () {
            Route::post('register', RegisterController::class);
        });

        // Protected routes
        Route::middleware('api.auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });

        Route::prefix('social')->group(function () {
            Route::get('{provider}/redirect', [SocialAuthController::class, 'getAuthUrl']);

            Route::post('{provider}/callback', [SocialAuthController::class, 'callback'])
                ->where('provider', 'google|facebook');
        });
    });

});

Route::fallback(function() {
    return response()->json([
        'error' => [
            'code' => 404,
            'message' => 'API endpoint not found'
        ]
    ], 404);
})->middleware('api');
