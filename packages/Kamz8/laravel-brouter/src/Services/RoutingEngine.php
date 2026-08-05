<?php

namespace Kamz\LaravelBRouter\Services;

use Kamz\LaravelBRouter\Contracts\DataProviderInterface;
use Kamz\LaravelBRouter\Contracts\RouterInterface;
use Kamz\LaravelBRouter\Exceptions\NoWaterwayFoundException;
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
        protected ?PgRoutingService $pgRouting = null,
        protected ?RiverMicroGraphService $microGraphs = null,
    ) {
        $this->graphCache ??= new GraphCache();
    }

    public function findRoute(RouteRequestData $request): RouteResult
    {
        $bbox = $this->bbox($request);

        if ($this->pgRouting !== null && $this->graphRepository !== null) {
            $publishedTile = $this->microGraphs?->findPublishedTile($request->riverName, $request->start, $request->end);
            $graphVersion = $publishedTile?->version;
            $importId = $publishedTile?->import_id;

            if ($graphVersion === null && method_exists($this->graphRepository, 'activeGraphVersionForRiver')) {
                $graphVersion = $this->graphRepository->activeGraphVersionForRiver($request->riverName, $bbox);
                $importId = $this->graphRepository->activeGraphImportIdForRiver($request->riverName, $bbox);
            }

            if ($graphVersion === null) {
                $graphVersion = $this->graphRepository->activeGraphVersion($bbox);
                $importId = $this->graphRepository->activeGraphImportId($bbox);
            }

            $indexing = ['status' => 'published'];

            if ($graphVersion === null || $importId === null) {
                if ($this->microGraphs === null) {
                    throw new NoWaterwayFoundException('No published PostGIS waterway graph covers the requested route.');
                }

                $this->microGraphs->queueImport($request->riverName, $bbox, [
                    'name' => $request->riverName,
                    'source' => 'lazy-route',
                ]);

                return $this->findLegacyRoute($request, $bbox, [
                    'status' => 'queued',
                ]);
            }

            $result = $this->pgRouting->route($request, $importId);

            return new RouteResult(
                path: $result->path,
                startSnap: $result->startSnap,
                endSnap: $result->endSnap,
                distanceMeters: $result->distanceMeters,
                cache: $result->cache + ['graph' => $graphVersion, 'route' => 'versioned', 'indexing' => $indexing],
            );
        }

        return $this->findLegacyRoute($request, $bbox);
    }

    private function findLegacyRoute(RouteRequestData $request, array $bbox, array $indexing = []): RouteResult
    {
        $graphVersion = $this->graphRepository?->activeGraphVersion() ?? 'runtime';
        $graphPayload = $this->graphCache->remember($graphVersion, function () use ($request, $bbox): array {
            if ($this->graphRepository !== null && $this->graphRepository->activeGraphVersion() !== null) {
                return $this->graphRepository->activeGraphPayload();
            }

            $osm = $this->cache->rememberOsm($request->riverName, $bbox, fn (): array => $this->dataProvider->getWaterwayByName($request->riverName, $bbox));
            $normalized = $this->normalizer->normalize($osm, $request->riverName);

            return $this->graphBuilder->build($normalized);
        });

        $usesPersistedGraph = $this->graphRepository !== null && $this->graphRepository->activeGraphVersion() !== null;

        $route = $this->cache->rememberRoute($graphVersion, [
            'river' => $request->riverName,
            'start' => $request->start,
            'end' => $request->end,
            'snap_tolerance_m' => $request->snapToleranceMeters,
        ], function () use ($request, $graphPayload, $usesPersistedGraph): array {
            if ($usesPersistedGraph && $this->postgisSnapper !== null) {
                $startSnap = $this->postgisSnapper->snap($request->start, $request->snapToleranceMeters);
                $endSnap = $this->postgisSnapper->snap($request->end, $request->snapToleranceMeters);
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
            cache: [
                'engine' => 'php',
                'osm' => 'versioned',
                'graph' => $graphVersion,
                'route' => 'versioned',
                'indexing' => $indexing,
            ],
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
