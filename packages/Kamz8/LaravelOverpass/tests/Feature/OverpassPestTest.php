<?php

use Kamz8\LaravelOverpass\Overpass;

beforeEach(function () {
    $config = [
        'endpoint' => 'https://overpass-api.de/api/interpreter',
        'timeout' => 60,
        'throttle' => false,
        'app_name' => 'LaravelOverpassTest',
        'app_author' => 'Tester',
    ];
    Overpass::setConfig($config);
});

it('executes a simple query and returns valid response', function () {
    $response = Overpass::query()
        ->node()
        ->where('amenity', 'cafe')
        ->bbox(51.5, -0.1, 51.6, 0.1)
        ->limit(5)
        ->get();

    expect($response)
        ->not->toBeNull()
        ->toBeArray()
        ->toHaveKey('elements')
        ->and($response['elements'])->toHaveCount(5);
});

it('executes a simple query and returns valid response', function () {
    $response = $this->overpass->query()
        ->node()
        ->where('amenity', 'cafe')
        ->bbox(51.5, -0.1, 51.6, 0.1)
        ->limit(5)
        ->get();

    expect($response)
        ->not->toBeNull()
        ->toBeArray()
        ->toHaveCount(5);
});

it('throws an exception when query is malformed', function () {
    expect(fn() => $this->overpass->query()
        ->node()
        ->where('invalidKey', 'invalidValue')
        ->get())
        ->toThrow(Exception::class);
});

it('handles empty result set correctly', function () {
    $response = $this->overpass->query()
        ->node()
        ->where('amenity', 'nonexistent')
        ->bbox(51.5, -0.1, 51.6, 0.1)
        ->limit(5)
        ->get();

    expect($response)
        ->toBeArray()
        ->toBeEmpty();
});
