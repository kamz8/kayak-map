<?php

namespace Kamz\LaravelBRouter\Http\Controllers;

use Illuminate\Http\Request;
use Kamz\LaravelBRouter\Contracts\RouterInterface;
use Kamz\LaravelBRouter\DTO\RouteRequestData;

class RouteController
{
    public function findRoute(Request $request, RouterInterface $router)
    {
        $validated = $request->validate([
            'river_name' => ['required', 'string', 'max:255'],
            'start' => ['required', 'array'],
            'start.lat' => ['required', 'numeric', 'between:-90,90'],
            'start.lng' => ['required', 'numeric', 'between:-180,180'],
            'end' => ['required', 'array'],
            'end.lat' => ['required', 'numeric', 'between:-90,90'],
            'end.lng' => ['required', 'numeric', 'between:-180,180'],
            'snap_tolerance_m' => ['sometimes', 'numeric', 'min:1'],
            'simplify' => ['sometimes', 'boolean'],
        ]);

        try {
            $route = $router->findRoute(RouteRequestData::fromArray($validated));

            return response()->json($route->toArray());

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    public function findNearestWaterway(Request $request)
    {
        // Implementation here
        return response()->json(['message' => 'Not implemented yet']);
    }

    public function health(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['status' => 'healthy']);
    }
}
