<?php

return [
    'endpoint' => env('OVERPASS_API_ENDPOINT', 'https://overpass-api.de/api/interpreter'),
    'timeout' => env('OVERPASS_API_TIMEOUT', 60),
];
