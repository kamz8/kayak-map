<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrailRequest;
use App\Http\Resources\TrailCollection;
use App\Services\TrailService;
use App\Services\RegionService;
use Illuminate\Http\JsonResponse;
use MatanYadaev\EloquentSpatial\Objects\Point;

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
}
