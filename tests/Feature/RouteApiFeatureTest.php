<?php

namespace Tests\Feature;

use App\Models\Trail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Kamz\LaravelBRouter\Contracts\RouterInterface;
use Kamz\LaravelBRouter\DTO\RouteRequestData;
use Kamz\LaravelBRouter\Exceptions\SnapDistanceExceededException;
use Kamz\LaravelBRouter\Models\RouteResult;
use Tests\TestCase;

class RouteApiFeatureTest extends TestCase
{
    use DatabaseTransactions;

    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->headers = [
            'X-Client-Type' => 'web',
        ];
    }

    /** @test */
    public function it_returns_a_successful_api_response(): void
    {
        $response = $this->get('api/v1/', $this->headers);

        $response->assertOk();
    }

    /** @test */
    public function it_handles_invalid_api_endpoints_correctly(): void
    {
        $response = $this->getJson('/api/v1/invalid/endpoint', $this->headers);

        $response->assertNotFound()
            ->assertJson([
                'error' => [
                    'code' => '404',
                    'message' => 'API endpoint not found',
                ],
            ]);
    }

    /** @test */
    public function it_generates_a_river_route_preview_without_mutating_the_trail_or_river_track(): void
    {
        $trail = Trail::factory()->create([
            'river_name' => 'Wda',
            'start_lat' => 53.0,
            'start_lng' => 18.0,
            'end_lat' => 53.5,
            'end_lng' => 18.5,
        ]);

        $router = new class implements RouterInterface
        {
            public ?RouteRequestData $request = null;

            public function findRoute(RouteRequestData $request): RouteResult
            {
                $this->request = $request;

                return new RouteResult(
                    path: [[18.1, 53.1], [18.2, 53.2]],
                    startSnap: ['input' => [18.1, 53.1], 'snapped' => [18.1, 53.1], 'distance_m' => 0.0],
                    endSnap: ['input' => [18.2, 53.2], 'snapped' => [18.2, 53.2], 'distance_m' => 0.0],
                    distanceMeters: 123.0,
                    cache: ['route' => 'miss'],
                );
            }
        };

        app()->instance(RouterInterface::class, $router);

        $response = $this->postJson("/api/v1/dashboard/trails/{$trail->id}/river-route", [
            'start' => ['lat' => 53.1, 'lng' => 18.1],
            'end' => ['lat' => 53.2, 'lng' => 18.2],
            'snap_tolerance_m' => 250,
        ], $this->headers);

        $response->assertOk();
        $response->assertJsonPath('data.path.0', [18.1, 53.1]);
        $response->assertJsonPath('data.distance_m', 123);

        $this->assertSame('Wda', $router->request->riverName);
        $this->assertSame(['lat' => 53.1, 'lng' => 18.1], $router->request->start);
        $this->assertSame(['lat' => 53.2, 'lng' => 18.2], $router->request->end);
        $this->assertSame(250.0, $router->request->snapToleranceMeters);

        $trail->refresh();

        $this->assertSame(53.0, (float) $trail->start_lat);
        $this->assertSame(18.0, (float) $trail->start_lng);
        $this->assertSame(53.5, (float) $trail->end_lat);
        $this->assertSame(18.5, (float) $trail->end_lng);
        $this->assertFalse($trail->riverTrack()->exists());
    }

    /** @test */
    public function it_validates_river_route_override_coordinates(): void
    {
        $trail = Trail::factory()->create();

        $response = $this->postJson("/api/v1/dashboard/trails/{$trail->id}/river-route", [
            'start' => ['lat' => 120, 'lng' => 18.1],
        ], $this->headers);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['start.lat']);
    }

    /** @test */
    public function it_returns_unprocessable_when_river_route_points_are_outside_snap_tolerance(): void
    {
        $trail = Trail::factory()->create();

        app()->instance(RouterInterface::class, new class implements RouterInterface
        {
            public function findRoute(RouteRequestData $request): RouteResult
            {
                throw new SnapDistanceExceededException('No waterway edge found within snap tolerance.');
            }
        });

        $response = $this->postJson("/api/v1/dashboard/trails/{$trail->id}/river-route", [
            'start' => ['lat' => 52.09, 'lng' => 15.91],
            'end' => ['lat' => 52.60, 'lng' => 15.47],
            'snap_tolerance_m' => 150,
        ], $this->headers);

        $response->assertUnprocessable()
            ->assertJsonPath('error.code', 'SNAP_DISTANCE_EXCEEDED')
            ->assertJsonPath('error.message', 'No waterway edge found within snap tolerance.');
    }

    /** @test */
    public function it_generates_a_widawa_river_route_with_the_production_routing_stack(): void
    {
        if (! filter_var(getenv('BROUTER_PRODUCTION_ROUTING_TEST'), FILTER_VALIDATE_BOOLEAN)) {
            $this->markTestSkipped('Set BROUTER_PRODUCTION_ROUTING_TEST=1 to run the production BRouter integration test.');
        }

        config()->set('brouter.cache.store', 'array');

        $trail = Trail::factory()->create([
            'river_name' => 'Widawa',
            'start_lat' => 51.271693980792,
            'start_lng' => 17.652153968811,
            'end_lat' => 51.215271343707,
            'end_lng' => 17.713394165039,
        ]);

        $response = $this->postJson("/api/v1/dashboard/trails/{$trail->id}/river-route", [
            'snap_tolerance_m' => 50,
        ], $this->headers);

        $response->assertOk();
        $this->assertGreaterThan(2, count($response->json('data.path')));
        $this->assertGreaterThan(0, $response->json('data.distance_m'));
    }
}
