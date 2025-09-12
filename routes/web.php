<?php

use Illuminate\Support\Facades\Route;

// Dashboard routes - separate SPA
Route::get('/dashboard/{any?}', function () {
    return view('dashboard');
})->where('any', '.*');

// Main app - catch-all route to handle all requests and direct them to the Vue.js application
Route::get('/{any}', function () {
    return view('index');
})->where('any', '^(?!api/|dashboard/).*$');

