<?php

namespace Kamz\LaravelBRouter\Tests\Feature;

use Kamz\LaravelBRouter\Contracts\RouterInterface;
use Kamz\LaravelBRouter\DTO\RouteRequestData;
use Kamz\LaravelBRouter\Models\RouteResult;
use Kamz\LaravelBRouter\Tests\TestCase;

class RouteControllerTest extends TestCase
{
    /** @test */
    public function it_validates_route_request_payload(): void
    {
        $response = $this->postJson('/api/brouter/route', [
            'start' => ['lat' => 100, 'lng' => 0],
            'end' => ['lat' => 0, 'lng' => 0],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['river_name', 'start.lat']);
    }

    /** @test */
    public function it_returns_route_result_json(): void
    {
        $this->app->instance(RouterInterface::class, new class implements RouterInterface
        {
            public function findRoute(RouteRequestData $request): RouteResult
            {
                return new RouteResult(
                    path: [[0.0, 0.0], [1.0, 0.0]],
                    startSnap: ['input' => [0.0, 0.0], 'snapped' => [0.0, 0.0], 'distance_m' => 0.0],
                    endSnap: ['input' => [1.0, 0.0], 'snapped' => [1.0, 0.0], 'distance_m' => 0.0],
                    distanceMeters: 100.0,
                    cache: ['route' => 'miss'],
                );
            }
        });

        $response = $this->postJson('/api/brouter/route', [
            'river_name' => 'Test River',
            'start' => ['lat' => 0, 'lng' => 0],
            'end' => ['lat' => 0, 'lng' => 1],
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.path.1', [1, 0]);
        $response->assertJsonPath('data.distance_m', 100);
    }
}
