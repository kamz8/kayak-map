<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegionRequest;
use App\Http\Requests\UpdateRegionRequest;
use App\Http\Resources\RegionCollection;
use App\Http\Resources\RegionResource;
use App\Models\Region;


use App\Http\Resources\TrailResource;
use App\Services\RegionService;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    protected $regionService;

    public function __construct(RegionService $regionService)
    {
        $this->regionService = $regionService;
    }

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

    public function index(): RegionCollection
    {
        $regions = $this->regionService->getRootRegions();
        return new RegionCollection($regions);
    }

    public function show($slug)
    {
        $region = $this->regionService->getRegionBySlug($slug);
        return new RegionResource($region);
    }

}
