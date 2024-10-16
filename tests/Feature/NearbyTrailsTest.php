<?php

// tests/Feature/NearbyTrailsTest.php

use App\Models\Trail;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

it('returns nearby trails based on coordinates', function () {
    // Tworzymy region
    $region = Region::factory()->create(['name' => 'Wrocław']);

    // Tworzymy trasę i przypisujemy ją do regionu
    $trail = Trail::factory()->create([
        'trail_name' => 'Sobótka - Ślęża',
        'start_lat' => 51.107885,
        'start_lng' => 17.038538,
        'rating' => 4.5
    ]);

    // Przypisujemy trasę do regionu
    $trail->regions()->attach($region->id);

    // Wywołujemy endpoint z współrzędnymi
    $response = getJson('/api/trails/nearby?lat=51.107885&long=17.038538');

    // Sprawdzamy, czy odpowiedź zawiera trasę
    $response->assertStatus(200)
        ->assertJsonFragment([
            'trail_name' => 'Sobótka - Ślęża',
            'rating' => 4.5,
        ]);
});

it('filters trails based on location name', function () {
    // Tworzymy dwa regiony
    $region1 = Region::factory()->create(['name' => 'Wrocław']);
    $region2 = Region::factory()->create(['name' => 'Warszawa']);

    // Tworzymy trasę i przypisujemy ją do dwóch regionów
    $trail = Trail::factory()->create([
        'trail_name' => 'Tapada - Ślęża Nature Reserve',
        'start_lat' => 51.107885,
        'start_lng' => 17.038538,
        'rating' => 4.2
    ]);

    // Przypisujemy trasę do dwóch regionów
    $trail->regions()->attach([$region1->id, $region2->id]);

    // Wywołujemy endpoint z współrzędnymi i filtrem na nazwę lokalizacji (Wrocław)
    $response = getJson('/api/trails/nearby?lat=51.107885&long=17.038538&location_name=Wrocław');

    // Sprawdzamy, czy odpowiedź zawiera trasę przypisaną do Wrocławia
    $response->assertStatus(200)
        ->assertJsonFragment([
            'trail_name' => 'Tapada - Ślęża Nature Reserve',
            'rating' => 4.2,
        ]);

    // Wywołujemy endpoint z filtrem na nazwę lokalizacji (Warszawa)
    $response2 = getJson('/api/trails/nearby?lat=51.107885&long=17.038538&location_name=Warszawa');

    // Sprawdzamy, czy odpowiedź również zawiera trasę przypisaną do Warszawy
    $response2->assertStatus(200)
        ->assertJsonFragment([
            'trail_name' => 'Tapada - Ślęża Nature Reserve',
            'rating' => 4.2,
        ]);
});

