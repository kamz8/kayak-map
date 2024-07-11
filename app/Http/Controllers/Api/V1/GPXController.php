<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\RiverTrack;
use Illuminate\Http\Request;
use phpGPX\phpGPX;

class GPXController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'gpx_file' => 'required|file|mimes:gpx,xml',
            'trail_id' => 'required|exists:trails,id',
        ]);

        $gpxFile = $request->file('gpx_file');
        $gpx = new phpGPX();
        $file = $gpx->load($gpxFile->getPathname());

        $points = [];
        foreach ($file->tracks as $track) {
            foreach ($track->segments as $segment) {
                foreach ($segment->points as $point) {
                    $points[] = ['lat' => $point->latitude, 'lng' => $point->longitude];
                }
            }
        }

        // Simplify points if needed
        $simplifiedPoints = $this->simplifyPoints($points);

        $riverTrack = new RiverTrack();
        $riverTrack->trail_id = $request->trail_id;
        $riverTrack->track_points = json_encode($simplifiedPoints);
        $riverTrack->save();

        return response()->json(['message' => 'GPX file uploaded and track saved successfully']);
    }

    private function simplifyPoints(array $points, $tolerance = 0.0001)
    {
        // Implement your point simplification algorithm here
        return $points;
    }
}
