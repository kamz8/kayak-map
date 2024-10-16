<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\NearbyTrailsRequest;
use App\Http\Requests\TrailRequest;
use App\Http\Resources\NearbyTrailResource;
use App\Http\Resources\NearbyTrailsCollection;
use App\Http\Resources\TrailCollection;
use App\Http\Resources\TrailResource;
use App\Services\TrailService;
use App\Services\RegionService;
use Illuminate\Http\JsonResponse;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Symfony\Component\HttpFoundation\Response;

class TrailController extends Controller
{
    protected TrailService $trailService;
    protected RegionService $regionService;

    public function __construct(TrailService $trailService, RegionService $regionService)
    {
        $this->trailService = $trailService;
        $this->regionService = $regionService;
    }

    public function index(TrailRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $trails = $this->trailService->getTrails($filters);

        $boundingBox = [
            'start_lat' => $filters['start_lat'] ?? null,
            'end_lat' => $filters['end_lat'] ?? null,
            'start_lng' => $filters['start_lng'] ?? null,
            'end_lng' => $filters['end_lng'] ?? null,
        ];

        $centerPoint = null;
        if (!in_array(null, $boundingBox, true)) {
            $centerPoint = new Point(
                ($boundingBox['start_lat'] + $boundingBox['end_lat']) / 2,
                ($boundingBox['start_lng'] + $boundingBox['end_lng']) / 2
            );
        }

        $mainRegion = $centerPoint ? $this->regionService->getMainRegion($centerPoint) : null;
        $fullSlug = $mainRegion ? $this->regionService->getFullSlug($mainRegion) : null;
        $regionPath = $mainRegion ? $this->regionService->getRegionPath($mainRegion) : null;

        $additionalMeta = [
            'bounding_box' => $boundingBox,
            'criteria' => $filters,
            'main_region' => $mainRegion ? [
                'name' => $mainRegion->name,
                'type' => $mainRegion->type,
                'full_slug' => $fullSlug,
                'path' => $regionPath,
            ] : null,
        ];

        return (new TrailCollection($trails, $additionalMeta))->response();
    }

    public function show($slug)
    {

        try {
            $trail = $this->trailService->getTrailDetails($slug);
            return TrailResource::make($trail);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Trail not found'], 404);
        }
    }

    /**
     * Pobiera trasy w pobliżu na podstawie lokalizacji.
     *
     * @param NearbyTrailsRequest $request
     * @return NearbyTrailsCollection
     */
    public function getNearbyTrails(NearbyTrailsRequest $request): NearbyTrailsCollection
    {
        $request->validated();
        $latitude = $request->input('lat');
        $longitude = $request->input('long');
        $locationName = $request->input('location_name');

        $trails = $this->trailService->getNearbyTrails($latitude, $longitude, $locationName);

        return new NearbyTrailsCollection($trails);
    }
}
