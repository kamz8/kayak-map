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
use Kamz\LaravelBRouter\Models\RouteResult;

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
            if ($fallback = $this->routeFromSavedTrack($trail, $validated)) {
                return response()->json($fallback->toArray());
            }

            return response()->json(['error' => [
                'code' => 'SNAP_DISTANCE_EXCEEDED',
                'message' => $exception->getMessage(),
                'river' => $trail->river_name,
                'snap_tolerance_m' => $snapToleranceMeters,
                'suggested_action' => 'Increase snap tolerance or import the river corridor into BRouter.',
            ]], 422);
        } catch (OverpassException $exception) {
            if ($fallback = $this->routeFromSavedTrack($trail, $validated)) {
                return response()->json($fallback->toArray());
            }

            return response()->json(['error' => [
                'code' => 'OVERPASS_UNAVAILABLE',
                'message' => $exception->getMessage(),
                'river' => $trail->river_name,
                'suggested_action' => 'Retry the request or run river indexing first.',
            ]], 503);
        } catch (NoWaterwayFoundException $exception) {
            if ($fallback = $this->routeFromSavedTrack($trail, $validated)) {
                return response()->json($fallback->toArray());
            }

            return response()->json(['error' => [
                'code' => 'RIVER_GRAPH_UNAVAILABLE',
                'message' => $exception->getMessage(),
                'river' => $trail->river_name,
                'suggested_action' => 'Retry to create a temporary river micro-graph.',
            ]], 503);
        }

        return response()->json($route->toArray());
    }

    private function routeFromSavedTrack(Trail $trail, array $validated): ?RouteResult
    {
        $coordinates = $trail->riverTrack?->track_points?->toArray()['coordinates'] ?? [];

        if (count($coordinates) < 2) {
            return null;
        }

        $start = $validated['start'] ?? ['lat' => $trail->start_lat, 'lng' => $trail->start_lng];
        $end = $validated['end'] ?? ['lat' => $trail->end_lat, 'lng' => $trail->end_lng];
        $startIndex = $this->nearestTrackIndex($coordinates, $start);
        $endIndex = $this->nearestTrackIndex($coordinates, $end);

        if ($startIndex > $endIndex) {
            $coordinates = array_reverse($coordinates);
            $startIndex = count($coordinates) - $startIndex - 1;
            $endIndex = count($coordinates) - $endIndex - 1;
        }

        $path = array_slice($coordinates, $startIndex, $endIndex - $startIndex + 1);
        $distance = 0.0;

        foreach (array_slice($path, 1) as $index => $point) {
            $distance += $this->distanceMeters($path[$index], $point);
        }

        return new RouteResult(
            path: $path,
            startSnap: [
                'input' => [$start['lng'], $start['lat']],
                'snapped' => $path[0],
                'distance_m' => $this->distanceMeters([$start['lng'], $start['lat']], $path[0]),
            ],
            endSnap: [
                'input' => [$end['lng'], $end['lat']],
                'snapped' => $path[array_key_last($path)],
                'distance_m' => $this->distanceMeters([$end['lng'], $end['lat']], $path[array_key_last($path)]),
            ],
            distanceMeters: $distance,
            warnings: ['Used the saved trail river track while the PostGIS graph is unavailable.'],
            cache: ['engine' => 'saved_track', 'algorithm' => 'polyline', 'graph' => 'trail:'.$trail->id],
        );
    }

    private function nearestTrackIndex(array $coordinates, array $point): int
    {
        $nearestIndex = 0;
        $nearestDistance = INF;

        foreach ($coordinates as $index => $coordinate) {
            $distance = (($coordinate[0] - $point['lng']) ** 2) + (($coordinate[1] - $point['lat']) ** 2);

            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearestIndex = $index;
            }
        }

        return $nearestIndex;
    }

    private function distanceMeters(array $from, array $to): float
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($to[1] - $from[1]);
        $lngDelta = deg2rad($to[0] - $from[0]);
        $latitude = deg2rad(($from[1] + $to[1]) / 2);
        $a = ($latDelta / 2) ** 2 + cos(deg2rad($from[1])) * cos(deg2rad($to[1])) * ($lngDelta / 2) ** 2;

        return $earthRadius * 2 * asin(min(1, sqrt($a)));
    }
}
