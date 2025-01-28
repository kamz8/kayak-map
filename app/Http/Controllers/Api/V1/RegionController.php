<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegionRequest;
use App\Http\Requests\UpdateRegionRequest;
use App\Http\Resources\PaginatedRegionCollection;
use App\Http\Resources\RegionCollection;
use App\Http\Resources\RegionDetailsResource;
use App\Http\Resources\RegionIndexResource;
use App\Http\Resources\RegionResource;
use App\Http\Resources\SimpleRegionResource;
use App\Models\Region;


use App\Http\Resources\TrailResource;
use App\Services\GeodataService;
use App\Services\RegionService;
use App\Traits\NotFoundResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    use NotFoundResponse;
    public function __construct(
        private readonly RegionService $regionService,
    ) {}

    public function getTrailsByRegion($country, $state = null, $city = null): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $regionSlug = $city ?? $state ?? $country;
        $region = $this->regionService->findRegionBySlug($regionSlug);

        if (!$region) {
            return response()->json(['message' => 'Region not found'], 404);
        }

        return TrailResource::collection($this->regionService->getTrailsInRegion($region));
    }

    public function getRegion($slug): RegionResource|\Illuminate\Http\JsonResponse
    {
        $region = $this->regionService->findRegionBySlug($slug);

        if (!$region) {
            return response()->json(['message' => 'Region not found'], 404);
        }

        return new RegionResource($region);
    }

    public function getTopTrailsNearby(string $slug): JsonResponse
    {
        $region = $this->regionService->findRegionBySlug($slug);

        if (!$region) {
            return $this->notFoundResponse('Region not found');
        }

        $trails = $this->regionService->getTopTrailsInRegion($region);

        // Debug info w trybie deweloperskim
        $debugInfo = [];
        if (config('app.debug')) {
            $debugInfo = [
                'region_id' => $region->id,
                'trail_count' => $trails->count(),
                'center_point' => $region->center_point ? [
                    'lat' => $region->center_point->latitude,
                    'lng' => $region->center_point->longitude
                ] : null,
                'type' => $region->type,
            ];
        }

        return response()->json([
            'data' => TrailResource::collection($trails),
            'meta' => [
                'region' => [
                    'name' => $region->name,
                    'type' => $region->type,
                    'center' => $region->center_point ? [
                        'latitude' => $region->center_point->latitude,
                        'longitude' => $region->center_point->longitude
                    ] : null
                ],
                'debug' => $debugInfo
            ]
        ]);
    }

    public function index(): JsonResponse
    {
        $regions = $this->regionService->getRootRegions();
        return response()->json([
            'data' => RegionIndexResource::collection($regions),
            'meta' => [
                'total_regions' => $regions->count()
            ]
        ]);
    }


    public function show(string $slug): JsonResponse
    {
        $region = $this->regionService->getRegionDetails($slug);
        return response()->json(new RegionDetailsResource($region));
    }

    /**
     * Zwraca spłaszczoną, paginowaną listę wszystkich regionów dla danego kraju
     */
    public function countryRegions(Request $request, string $countrySlug): JsonResponse
    {
        $perPage = min($request->input('per_page', 15), 30);

        try {
            $regions = $this->regionService->getFlatRegionsForCountry($countrySlug, $perPage);
            if (!$regions->count()) { return $this->notFoundResponse('Regions for this country not found'); }

            return response()->json(
                new PaginatedRegionCollection($regions->through(function ($region) {
                    return new SimpleRegionResource($region);
                }))
            );

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch regions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
