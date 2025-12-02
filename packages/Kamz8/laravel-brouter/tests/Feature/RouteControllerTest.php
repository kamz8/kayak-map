<?php
// tests/Feature/RouteControllerTest.php

namespace Kamz\LaravelBRouter\Tests\Feature;

use Kamz\LaravelBRouter\Tests\TestCase;
use Kamz\LaravelBRouter\Facades\BRouter;
use Mockery;

class RouteControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock the BRouter facade
        BRouter::shouldReceive('findRoute')
            ->andReturn([
                'type' => 'FeatureCollection',
                'features' => []
            ]);
    }

    /** @test */
    public function it_returns_success_response_for_valid_route_request()
    {
        $response = $this->getJson('/api/brouter/route?' . http_build_query([
                'start_lat' => 51.136986,
                'start_lon' => 16.989449,
                'end_lat' => 51.144048,
                'end_lon' => 16.977333,
            ]));

        $response->assertStatus(200)
            ->assertJsonStructure(['type', 'features']);
    }

    /** @test */
    public function it_validates_required_parameters()
    {
        $response = $this->getJson('/api/brouter/route');

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'start_lat',
                'start_lon',
                'end_lat',
                'end_lon'
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
