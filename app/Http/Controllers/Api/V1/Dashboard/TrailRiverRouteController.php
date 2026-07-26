<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Trail\GenerateRiverRouteRequest;
use App\Models\Trail;
use Illuminate\Http\JsonResponse;
use Kamz\LaravelBRouter\Contracts\RouterInterface;
use Kamz\LaravelBRouter\DTO\RouteRequestData;

class TrailRiverRouteController extends Controller
{
    public function __invoke(GenerateRiverRouteRequest $request, int|string $trail, RouterInterface $router): JsonResponse
    {
        $trail = Trail::query()->findOrFail($trail);
        $validated = $request->validated();
        $route = $router->findRoute(new RouteRequestData(
            riverName: $trail->river_name,
            start: $validated['start'] ?? ['lat' => $trail->start_lat, 'lng' => $trail->start_lng],
            end: $validated['end'] ?? ['lat' => $trail->end_lat, 'lng' => $trail->end_lng],
            snapToleranceMeters: (float) ($validated['snap_tolerance_m'] ?? config('brouter.routing.snap_tolerance_m', 500)),
            simplify: (bool) ($validated['simplify'] ?? true),
        ));

        return response()->json($route->toArray());
    }
}
