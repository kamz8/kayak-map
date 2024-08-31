<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegionResource;
use App\Models\Trail;
use App\Models\Region;
use App\Services\ReverseGeocodingService;
use Illuminate\Http\JsonResponse;

class TrailGeocodingController extends Controller
{
    public function __construct(protected ReverseGeocodingService $geocodingService)
    {}

    public function getStartPointRegion($id): JsonResponse
    {
        $trail = Trail::findOrFail($id);
        $regionData = $this->geocodingService->getRegionData($trail->start_lat, $trail->start_lng);

        if (!$regionData) {
            return response()->json(['error' => 'Unable to geocode the provided lat lang'], 404);
        }

        $region = Region::firstOrCreate(
            ['slug' => $regionData['slug']],
            [
                'name' => $regionData['name'],
                'type' => $regionData['type'],
                'center_point' => new \MatanYadaev\EloquentSpatial\Objects\Point($regionData['lat'], $regionData['lng']),
            ]
        );

        return response()->json([
            'trail_id' => $trail->id,
            'trail_name' => $trail->trail_name,
            'start_point' => [
                'latitude' => $trail->start_lat,
                'longitude' => $trail->start_lng,
            ],
            'region' => new RegionResource($region)
        ]);
    }
}
