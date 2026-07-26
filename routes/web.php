<?php

use Illuminate\Support\Facades\Route;

$dashboardView = function () {
    return view('dashboard');
};

Route::domain('dashboard.wartkinurt.pl')
    ->get('/{any?}', $dashboardView)
    ->where('any', '.*');

Route::get('/dashboard/{any?}', $dashboardView)->where('any', '.*');

// Main app - catch-all route to handle all requests and direct them to the Vue.js application
Route::get('/{any}', function () {
    return view('index');
})->where('any', '^(?!api/|dashboard/).*$');
