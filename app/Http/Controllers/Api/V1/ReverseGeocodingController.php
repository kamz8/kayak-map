<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LatLangRequest;
use App\Http\Resources\RegionResource;
use App\Http\Resources\ReverseGeocodeResource;
use App\Services\GeodataService;
use App\Services\ReverseGeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReverseGeocodingController extends Controller
{
    public function __construct(protected ReverseGeocodingService $geocodingService)
    {}

    public function reverseGeocode(LatLangRequest $request): JsonResponse | ReverseGeocodeResource
    {

        $result = $this->geocodingService->getLocationNameAndSlug($request->validated(['lat']), $request->validated(['lang']));

        return $result
            ? new ReverseGeocodeResource($result)
            : response()->json(['error' => 'Unable to geocode the given coordinates'], 404);
    }
}
