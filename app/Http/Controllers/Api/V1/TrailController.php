<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrailRequest;
use App\Http\Resources\TrailCollection;
use App\Services\TrailService;
use Illuminate\Http\JsonResponse;

class TrailController extends Controller
{
    protected TrailService $trailService;

    public function __construct(TrailService $trailService)
    {
        $this->trailService = $trailService;
    }

    public function index(TrailRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $trails = $this->trailService->getTrails($filters);
        return (new TrailCollection($trails))->response();
    }
}
