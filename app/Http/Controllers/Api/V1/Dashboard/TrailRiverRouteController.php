<?php

namespace App\Http\Controllers\Api\V1\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Trail\GenerateRiverRouteRequest;
use App\Models\Trail;
use Illuminate\Http\JsonResponse;
use Kamz\LaravelBRouter\Contracts\RouterInterface;
use Kamz\LaravelBRouter\DTO\RouteRequestData;
use Kamz\LaravelBRouter\Exceptions\OverpassException;
use Kamz\LaravelBRouter\Exceptions\NoWaterwayFoundException;
use Kamz\LaravelBRouter\Exceptions\SnapDistanceExceededException;

class TrailRiverRouteController extends Controller
{
    public function __invoke(GenerateRiverRouteRequest $request, int|string $trail, RouterInterface $router): JsonResponse
    {
        return $this->route($request, $trail, $router, null);
    }

    public function snap(GenerateRiverRouteRequest $request, int|string $trail, RouterInterface $router): JsonResponse
    {
        return $this->route($request, $trail, $router, 'snap');
    }

    public function auto(GenerateRiverRouteRequest $request, int|string $trail, RouterInterface $router): JsonResponse
    {
        return $this->route($request, $trail, $router, 'auto');
    }

    private function route(GenerateRiverRouteRequest $request, int|string $trail, RouterInterface $router, ?string $forcedMode): JsonResponse
    {
        $trail = Trail::query()->findOrFail($trail);
        $validated = $request->validated();
        $mode = $forcedMode ?? ($validated['mode'] ?? 'snap');
        $snapToleranceMeters = (float) ($validated['snap_tolerance_m'] ?? config('brouter.routing.snap_tolerance_m', 500));

        try {
            $route = $router->findRoute(new RouteRequestData(
                riverName: $validated['river_name'] ?? $trail->river_name,
                start: $validated['start'] ?? ['lat' => $trail->start_lat, 'lng' => $trail->start_lng],
                end: $validated['end'] ?? ['lat' => $trail->end_lat, 'lng' => $trail->end_lng],
                snapToleranceMeters: $snapToleranceMeters,
                simplify: (bool) ($validated['simplify'] ?? true),
                mode: $mode,
            ));
        } catch (SnapDistanceExceededException $exception) {
            return response()->json(['error' => [
                'code' => 'SNAP_DISTANCE_EXCEEDED',
                'message' => $exception->getMessage(),
                'river' => $trail->river_name,
                'snap_tolerance_m' => $snapToleranceMeters,
                'suggested_action' => 'Increase snap tolerance or import the river corridor into BRouter.',
            ]], 422);
        } catch (OverpassException $exception) {
            return response()->json(['error' => [
                'code' => 'OVERPASS_UNAVAILABLE',
                'message' => $exception->getMessage(),
                'river' => $trail->river_name,
                'suggested_action' => 'Retry the request or run river indexing first.',
            ]], 503);
        } catch (NoWaterwayFoundException $exception) {
            return response()->json(['error' => [
                'code' => 'RIVER_GRAPH_UNAVAILABLE',
                'message' => $exception->getMessage(),
                'river' => $trail->river_name,
                'suggested_action' => 'Retry to create a temporary river micro-graph.',
            ]], 503);
        }

        return response()->json($route->toArray());
    }
}
