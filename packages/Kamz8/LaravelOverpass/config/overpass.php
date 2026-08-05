<?php

return [
    'endpoint' => env('OVERPASS_API_ENDPOINT', 'https://overpass-api.de/api/interpreter'),
    'timeout' => env('OVERPASS_API_TIMEOUT', 60),
    'throttle' => env('OVERPASS_API_THROTTLE', true),
    'throttle_limit' => env('OVERPASS_API_THROTTLE_LIMIT', 1), // Max requests per second
    'app_name' => env('OVERPASS_API_APP_NAME', 'YourAppName'),
    'app_author' => env('OVERPASS_API_APP_AUTHOR', 'YourName'),
];
