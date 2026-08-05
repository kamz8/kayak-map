<?php

namespace Tests\Feature;

use App\Models\Trail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Kamz\LaravelBRouter\Contracts\RouterInterface;
use Kamz\LaravelBRouter\DTO\RouteRequestData;
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
        $this->assertSame('snap', $router->request->mode);

        $trail->refresh();

        $this->assertSame(53.0, (float) $trail->start_lat);
        $this->assertSame(18.0, (float) $trail->start_lng);
        $this->assertSame(53.5, (float) $trail->end_lat);
        $this->assertSame(18.5, (float) $trail->end_lng);
        $this->assertFalse($trail->riverTrack()->exists());
    }

    /** @test */
    public function it_supports_the_explicit_auto_route_endpoint(): void
    {
        $trail = Trail::factory()->create([
            'river_name' => 'Obra',
            'start_lat' => 52.0,
            'start_lng' => 15.0,
            'end_lat' => 52.5,
            'end_lng' => 15.5,
        ]);
        $router = new class implements RouterInterface
        {
            public ?RouteRequestData $request = null;

            public function findRoute(RouteRequestData $request): RouteResult
            {
                $this->request = $request;

                return new RouteResult(path: [[15.0, 52.0], [15.5, 52.5]], distanceMeters: 1000.0);
            }
        };
        app()->instance(RouterInterface::class, $router);

        $response = $this->postJson("/api/v1/dashboard/trails/{$trail->id}/auto-route", [], $this->headers);

        $response->assertOk();
        $this->assertSame('auto', $router->request->mode);
        $this->assertSame('Obra', $router->request->riverName);
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
    public function it_returns_a_widawa_river_route_response(): void
    {
        $trail = Trail::factory()->create([
            'river_name' => 'Widawa',
            'start_lat' => 51.271693980792,
            'start_lng' => 17.652153968811,
            'end_lat' => 51.215271343707,
            'end_lng' => 17.713394165039,
        ]);

        $router = new class implements RouterInterface
        {
            public function findRoute(RouteRequestData $request): RouteResult
            {
                return new RouteResult(
                    path: [[17.652153968811, 51.271693980792], [17.68, 51.24], [17.713394165039, 51.215271343707]],
                    distanceMeters: 7200.0,
                );
            }
        };

        app()->instance(RouterInterface::class, $router);

        $response = $this->postJson("/api/v1/dashboard/trails/{$trail->id}/river-route", [
            'snap_tolerance_m' => 50,
        ], $this->headers);

        $response->assertOk();
        $this->assertGreaterThan(2, count($response->json('data.path')));
        $this->assertGreaterThan(0, $response->json('data.distance_m'));
    }
}
