<?php

use Illuminate\Support\Facades\Route;

// Catch-all route to handle all requests and direct them to the Vue.js application
Route::get('/{any}', function () {
    return view('index'); // 'app' should be the name of your Blade template, like 'resources/views/app.blade.php'
})->where('any', '.*');
