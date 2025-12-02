<?php

namespace Kamz\LaravelBRouter\Http\Controllers;

use Illuminate\Http\Request;
use Kamz\LaravelBRouter\Facades\BRouter;

class RouteController
{
    public function findRoute(Request $request)
    {
        $request->validate([
            'start_lat' => 'required|numeric|between:-90,90',
            'start_lon' => 'required|numeric|between:-180,180',
            'end_lat' => 'required|numeric|between:-90,90',
            'end_lon' => 'required|numeric|between:-180,180',
            'profile' => 'sometimes|string|in:river,canoe,ship',
        ]);

        try {
            $route = BRouter::findRoute(
                [$request->start_lon, $request->start_lat],
                [$request->end_lon, $request->end_lat],
                $request->profile
            );

            return response()->json($route);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function findNearestWaterway(Request $request)
    {
        // Implementation here
        return response()->json(['message' => 'Not implemented yet']);
    }

    public function health()
    {
        return response()->json(['status' => 'healthy']);
    }
}
