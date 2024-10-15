<?php
use Kamz8\LaravelOverpass\Overpass;
use Illuminate\Support\Facades\Http;
use function Pest\Laravel\{config};

it('executes a built Overpass query correctly', function () {
    // Set up mock response for the Overpass API
    Http::fake([
        'https://overpass-api.de/api/interpreter' => Http::response([
            'elements' => [
                ['id' => 1, 'type' => 'node', 'tags' => ['amenity' => 'cafe']],
                ['id' => 2, 'type' => 'node', 'tags' => ['amenity' => 'cafe']],
            ]
        ], 200)
    ]);

    // Set up the configuration for the Overpass package
    config()->set('overpass.endpoint', 'https://overpass-api.de/api/interpreter');
    config()->set('overpass.timeout', 60);
    config()->set('overpass.throttle', false);
    config()->set('overpass.app_name', 'TestApp');
    config()->set('overpass.app_author', 'Tester');

    // Create an instance of the Overpass client
    $overpass = new Overpass();

    // Build a query using the Eloquent-like syntax
    $response = $overpass->query()
        ->node()
        ->where('amenity', 'cafe')
        ->bbox(51.5, -0.1, 51.6, 0.1)
        ->limit(2)
        ->get();

    // Validate the structure and content of the response
    expect($response)->toBeArray();
    expect($response['elements'])->toHaveCount(2);
    expect($response['elements'][0]['tags']['amenity'])->toBe('cafe');
});
