<?php

use Illuminate\Support\Facades\Route;

/*// Login route - redirect to dashboard login
Route::get('/login', function () {
    return redirect('/dashboard/login');
})->name('login');*/

// Dashboard routes - separate SPA
Route::get('/dashboard/{any?}', function () {
    // For web routes, we should check authentication differently
    // Since this is SPA, authentication will be handled by Vue.js
    return view('dashboard');
})->where('any', '.*');

// Main app - catch-all route to handle all requests and direct them to the Vue.js application
Route::get('/{any}', function () {
    return view('index');
})->where('any', '^(?!api/|dashboard/).*$');

