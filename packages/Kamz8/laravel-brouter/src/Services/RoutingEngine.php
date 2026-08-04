<?php

namespace Kamz\LaravelBRouter\Services;

use Kamz\LaravelBRouter\Contracts\DataProviderInterface;
use Kamz\LaravelBRouter\Contracts\RouterInterface;
use Kamz\LaravelBRouter\DTO\RouteRequestData;
use Kamz\LaravelBRouter\Models\RouteResult;

class RoutingEngine implements RouterInterface
{
    public function __construct(
        protected DataProviderInterface $dataProvider,
        protected RouteCache $cache,
        protected WaterwayNormalizer $normalizer,
        protected WaterwayGraphBuilder $graphBuilder,
        protected EdgeSnapper $snapper,
        protected GraphRouter $graphRouter,
        protected ?GraphCache $graphCache = null,
        protected ?BrouterGraphRepository $graphRepository = null,
        protected ?PostgisEdgeSnapper $postgisSnapper = null,
    ) {
        $this->graphCache ??= new GraphCache();
    }

    public function findRoute(RouteRequestData $request): RouteResult
    {
        $bbox = $this->bbox($request);
        $activeGraph = $this->graphRepository?->activeGraphForRoute($request->start, $request->end);
        $graphVersion = $activeGraph['version'] ?? 'runtime';
        $graphPayload = $this->graphCache->remember($graphVersion, function () use ($request, $bbox, $activeGraph): array {
            if ($this->graphRepository !== null && $activeGraph !== null) {
                return $this->graphRepository->activeGraphPayload($activeGraph['import_id']);
            }

            $osm = $this->cache->rememberOsm($request->riverName, $bbox, fn (): array => $this->dataProvider->getWaterwayByName($request->riverName, $bbox));
            $normalized = $this->normalizer->normalize($osm, $request->riverName);

            return $this->graphBuilder->build($normalized);
        });

        $usesPersistedGraph = $activeGraph !== null;

        $route = $this->cache->rememberRoute($graphVersion, [
            'river' => $request->riverName,
            'start' => $request->start,
            'end' => $request->end,
            'snap_tolerance_m' => $request->snapToleranceMeters,
        ], function () use ($request, $graphPayload, $usesPersistedGraph, $activeGraph): array {
            if ($usesPersistedGraph && $this->postgisSnapper !== null) {
                $startSnap = $this->postgisSnapper->snap($request->start, $request->snapToleranceMeters, $activeGraph['import_id']);
                $endSnap = $this->postgisSnapper->snap($request->end, $request->snapToleranceMeters, $activeGraph['import_id']);
            } else {
                $startSnap = $this->snapper->snap($request->start, $graphPayload['edges'], $request->snapToleranceMeters);
                $endSnap = $this->snapper->snap($request->end, $graphPayload['edges'], $request->snapToleranceMeters);
            }
            $route = $this->graphRouter->route($graphPayload, $startSnap, $endSnap);

            return ['route' => $route, 'start_snap' => $startSnap->toArray(), 'end_snap' => $endSnap->toArray()];
        });

        return new RouteResult(
            path: $route['route']['coordinates'],
            startSnap: $route['start_snap'],
            endSnap: $route['end_snap'],
            distanceMeters: $route['route']['distance_m'],
            cache: ['osm' => 'versioned', 'graph' => $graphVersion, 'route' => 'versioned'],
        );
    }

    private function bbox(RouteRequestData $request): array
    {
        $bufferKm = (float) config('brouter.routing.bbox_buffer_km', 5);
        $bufferLat = $bufferKm / 111.32;
        $centerLat = ($request->start['lat'] + $request->end['lat']) / 2;
        $bufferLng = $bufferKm / (111.32 * max(cos(deg2rad($centerLat)), 0.01));

        return [
            'south' => min($request->start['lat'], $request->end['lat']) - $bufferLat,
            'west' => min($request->start['lng'], $request->end['lng']) - $bufferLng,
            'north' => max($request->start['lat'], $request->end['lat']) + $bufferLat,
            'east' => max($request->start['lng'], $request->end['lng']) + $bufferLng,
        ];
    }
}
