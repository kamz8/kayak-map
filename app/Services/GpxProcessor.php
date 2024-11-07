<?php
namespace App\Services;

use Sibyx\phpGPX\phpGPX;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;

class GpxProcessor
{
    /**
     * @throws \Exception
     */
    public function process(string $filePath): array
    {
        $gpx = new phpGPX();
        $file = $gpx->load($filePath);

        if (empty($file->tracks)) {
            throw new \Exception('No tracks found in GPX file');
        }

        $track = $file->tracks[0];
        $points = [];
        $distance = 0;

        foreach ($track->segments[0]->points as $point) {
            $points[] = new Point($point->latitude, $point->longitude);

            if (isset($lastPoint)) {
                $distance += $point->difference($lastPoint);
            }

            $lastPoint = $point;
        }

        if (empty($points)) {
            throw new \Exception('No points found in track');
        }

        return [
            'start_point' => $points[0],
            'end_point' => end($points),
            'track_line' => new LineString($points),
            'distance' => $distance
        ];
    }
}
