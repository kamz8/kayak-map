<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Pest\Laravel\getJson;
use Illuminate\Testing\Fluent\AssertableJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed the database before each test
    $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
});

it('returns trails within visible area', function () {
    $response = getJson('/api/v1/trails', [
        'start_lat' => 51.0,
        'end_lat' => 53.0,
        'start_lng' => 15.0,
        'end_lng' => 17.0,
        'difficulty' => 'łatwy',
        'scenery' => 5
    ]);

    $response->assertStatus(200)
        ->assertJsonCount(1) // Ensure at least one trail is returned
        ->assertJsonStructure([
            '*' => [
                'id',
                'river_name',
                'trail_name',
                'description',
                'start_lat',
                'start_lng',
                'end_lat',
                'end_lng',
                'trail_length',
                'author',
                'difficulty',
                'scenery',
                'created_at',
                'updated_at',
                'river_track' => [
                    'id',
                    'trail_id',
                    'track_points',
                    'created_at',
                    'updated_at'
                ],
                'sections' => [
                    '*' => [
                        'id',
                        'trail_id',
                        'name',
                        'description',
                        'polygon_coordinates',
                        'scenery',
                        'created_at',
                        'updated_at'
                    ]
                ],
                'points' => [
                    '*' => [
                        'id',
                        'trail_id',
                        'point_type_id',
                        'name',
                        'description',
                        'lat',
                        'lng',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]
        ]);
});
