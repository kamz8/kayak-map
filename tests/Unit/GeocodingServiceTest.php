<?php

use App\Services\GeocodingService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->geocodingService = new GeocodingService();
});

it('fetches cities in province successfully', function () {
    Http::fake([
        'https://nominatim.openstreetmap.org/search*' => Http::response([
            ['osm_id' => 12345, 'lat' => '52.2297', 'lon' => '21.0122']
        ], 200),
        'https://nominatim.openstreetmap.org/lookup*' => Http::response([
            ['boundingbox' => ['51.1', '52.4', '20.8', '22.3']]
        ], 200),
        'https://nominatim.openstreetmap.org/search?q=city*' => Http::response([
            [
                'name' => 'Warszawa',
                'lat' => '52.2297',
                'lon' => '21.0122',
                'osm_id' => 123,
                'osm_type' => 'relation',
                'address' => ['state' => 'Mazowieckie'],
            ],
            [
                'name' => 'Radom',
                'lat' => '51.4027',
                'lon' => '21.1470',
                'osm_id' => 456,
                'osm_type' => 'relation',
                'address' => ['state' => 'Mazowieckie'],
            ],
        ], 200),
        'https://nominatim.openstreetmap.org/details*' => Http::response([
            'geometry' => [
                'coordinates' => [[[21.0, 52.2], [21.1, 52.2], [21.1, 52.3], [21.0, 52.3]]],
            ],
        ], 200),
    ]);

    $cities = $this->geocodingService->fetchCitiesInProvince('Mazowieckie');

    expect($cities)->toHaveCount(2)
        ->and($cities[0]['name'])->toBe('Warszawa')
        ->and($cities[1]['name'])->toBe('Radom')
        ->and($cities[0]['area'])->toBeArray();
});

it('throws exception when province is not found', function () {
    Http::fake([
        'https://nominatim.openstreetmap.org/search*' => Http::response([], 200),
    ]);

    $this->geocodingService->fetchCitiesInProvince('Nieistniejące');
})->throws(Exception::class, 'Nie znaleziono województwa: Nieistniejące');

it('throws exception when province details are not found', function () {
    Http::fake([
        'https://nominatim.openstreetmap.org/search*' => Http::response([
            ['osm_id' => 12345]
        ], 200),
        'https://nominatim.openstreetmap.org/lookup*' => Http::response([], 200),
    ]);

    $this->geocodingService->fetchCitiesInProvince('Mazowieckie');
})->throws(Exception::class, 'Nie udało się pobrać szczegółów województwa: Mazowieckie');

it('returns empty array when no cities are found', function () {
    Http::fake([
        'https://nominatim.openstreetmap.org/search*' => Http::response([
            ['osm_id' => 12345]
        ], 200),
        'https://nominatim.openstreetmap.org/lookup*' => Http::response([
            ['boundingbox' => ['51.1', '52.4', '20.8', '22.3']]
        ], 200),
        'https://nominatim.openstreetmap.org/search?q=city*' => Http::response([], 200),
    ]);

    $cities = $this->geocodingService->fetchCitiesInProvince('Mazowieckie');

    expect($cities)->toBeEmpty();
});

it('throws exception on API error', function () {
    Http::fake([
        'https://nominatim.openstreetmap.org/search*' => Http::response(null, 500),
    ]);

    $this->geocodingService->fetchCitiesInProvince('Mazowieckie');
})->throws(Exception::class, 'Error fetching cities in province: Error during geocoding: Failed to fetch geocoding data.');

it('throws exception with invalid boundaries', function () {
    Http::fake([
        'https://nominatim.openstreetmap.org/search*' => Http::response([
            ['osm_id' => 12345]
        ], 200),
        'https://nominatim.openstreetmap.org/lookup*' => Http::response([
            ['boundingbox' => ['invalid', 'data']]
        ], 200),
    ]);

    $this->geocodingService->fetchCitiesInProvince('Mazowieckie');
})->throws(Exception::class, 'Error fetching cities in province');

it('returns empty array when cities are in different province', function () {
    Http::fake([
        'https://nominatim.openstreetmap.org/search*' => Http::response([
            ['osm_id' => 12345]
        ], 200),
        'https://nominatim.openstreetmap.org/lookup*' => Http::response([
            ['boundingbox' => ['51.1', '52.4', '20.8', '22.3']]
        ], 200),
        'https://nominatim.openstreetmap.org/search?q=city*' => Http::response([
            [
                'name' => 'Kraków',
                'lat' => '50.0647',
                'lon' => '19.9450',
                'osm_id' => 789,
                'osm_type' => 'relation',
                'address' => ['state' => 'Małopolskie'],
            ],
        ], 200),
    ]);

    $cities = $this->geocodingService->fetchCitiesInProvince('Mazowieckie');

    expect($cities)->toBeEmpty();
});
