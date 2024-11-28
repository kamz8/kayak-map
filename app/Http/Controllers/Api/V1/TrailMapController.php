<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Trail;
use App\Services\TrailMapGeneratorService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrailMapController extends Controller
{
    private TrailMapGeneratorService $mapGenerator;

    public function __construct(TrailMapGeneratorService $mapGenerator)
    {
        $this->mapGenerator = $mapGenerator;
    }

    public function getStaticMap(string $slug): Response|StreamedResponse
    {
        try {
            $imageContent = $this->mapGenerator->getMapImage($slug);

            return response($imageContent)
                ->header('Content-Type', 'image/png')
                ->header('Cache-Control', 'public, max-age=86400') // Cache for 24 hours
                ->header('Content-Length', strlen($imageContent));
        } catch (\Exception $e) {
            return response([
                'error' => 'Nie udało się wygenerować mapy'
            ], 500);
        }
    }

    public function testMap(string $slug)
    {
        $trail = Trail::with(['riverTrack', 'points'])
            ->where('slug', $slug)
            ->firstOrFail();

        try {
            $trail->load(['riverTrack', 'points']);
            Log::info('Trail data for map:', [
                'trail_id' => $trail->id,
                'start_lat' => $trail->start_lat,
                'start_lng' => $trail->start_lng,
                'has_river_track' => $trail->riverTrack !== null,
                'points_count' => $trail->points->count(),
                'full_data' => $trail->toArray()
            ]);

            $trailArray = $trail->toArray();
            $trailArray['difficulty'] = $trail->difficulty->value;
            $trailArray['riverTrack'] = $trail->riverTrack?->toArray();
            $trailArray['points'] = $trail->points?->toArray() ?? [];

            // Generuj HTML
            $html = view('maps.maps-map', [
                'trail' => (object)$trailArray,
                'assetUrl' => asset(''),
            ])->render();

            return response($html)
                ->header('Content-Type', 'text/html');

        } catch (\Exception $e) {
            Log::error('Test map generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}
