<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use phpGPX\phpGPX;
use DateTimeInterface;
use DateTime;

class GpxProcessor
{
    public function __construct(
        private readonly GeodataService $geodataService
    ) {}

    /**
     * Process a GPX file and extract trail data with extended statistics.
     *
     * @param string $filePath
     * @return array{
     *     start_lat: float,
     *     start_lng: float,
     *     end_lat: float,
     *     end_lng: float,
     *     path: LineString,
     *     distance: float,
     *     duration: int,
     *     avg_speed: float,
     *     max_speed: float,
     *     start_time: ?DateTimeInterface,
     *     end_time: ?DateTimeInterface,
     *     elevation_gain: float,
     *     elevation_loss: float,
     *     max_elevation: float,
     *     min_elevation: float
     * }
     * @throws Exception
     */
    public function process(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception('File does not exist');
        }

        $fileContent = file_get_contents($filePath);
        if (empty($fileContent)) {
            throw new Exception('Invalid GPX file');
        }

        try {
            $gpx = new phpGPX();
            $file = $gpx->load($filePath);

            if (empty($file->tracks)) {
                throw new Exception('No tracks found in GPX file');
            }

            $points = [];
            $spatialPoints = [];
            $times = [];
            $elevations = [];
            $speeds = [];
            $distance = 0;
            $lastPoint = null;
            $lastTime = null;

            foreach ($file->tracks as $track) {
                foreach ($track->segments as $segment) {
                    foreach ($segment->points as $point) {
                        // Podstawowe dane o położeniu
                        $points[] = [
                            'lat' => $point->latitude,
                            'lng' => $point->longitude
                        ];

                        $spatialPoints[] = new Point($point->latitude, $point->longitude);

                        // Zbieranie danych o czasie
                        if ($point->time) {
                            $times[] = $point->time;
                        }

                        // Zbieranie danych o wysokości
                        if ($point->elevation !== null) {
                            $elevations[] = $point->elevation;
                        }

                        // Obliczanie odległości i prędkości
                        if ($lastPoint) {
                            $segmentDistance = $this->geodataService->calculateDistance(
                                $lastPoint['lat'],
                                $lastPoint['lng'],
                                $point->latitude,
                                $point->longitude
                            );

                            $distance += $segmentDistance;

                            // Obliczanie prędkości jeśli mamy czas
                            if ($lastTime && $point->time) {
                                $timeDiff = $point->time->getTimestamp() - $lastTime->getTimestamp();
                                if ($timeDiff > 0) {
                                    // Prędkość w m/s
                                    $speeds[] = $segmentDistance / $timeDiff;
                                }
                            }
                        }

                        $lastPoint = [
                            'lat' => $point->latitude,
                            'lng' => $point->longitude
                        ];
                        $lastTime = $point->time;
                    }
                }
            }

            if (empty($points)) {
                throw new Exception('No points found in tracks');
            }

            // Obliczanie statystyk wysokości
            $elevationStats = $this->calculateElevationStats($elevations);

            // Obliczanie czasu trwania
            $duration = !empty($times) ?
                end($times)->getTimestamp() - reset($times)->getTimestamp() :
                0;

            // Obliczanie prędkości
            $avgSpeed = $duration > 0 ? ($distance / $duration) : 0;
            $maxSpeed = !empty($speeds) ? max($speeds) : 0;

            return [
                'start_lat' => $points[0]['lat'],
                'start_lng' => $points[0]['lng'],
                'end_lat' => end($points)['lat'],
                'end_lng' => end($points)['lng'],
                'path' => new LineString($spatialPoints),
                'distance' => $distance,
                'duration' => $duration,
                'avg_speed' => $avgSpeed,
                'max_speed' => $maxSpeed,
                'start_time' => !empty($times) ? reset($times) : null,
                'end_time' => !empty($times) ? end($times) : null,
                'elevation_gain' => $elevationStats['gain'],
                'elevation_loss' => $elevationStats['loss'],
                'max_elevation' => $elevationStats['max'],
                'min_elevation' => $elevationStats['min']
            ];

        } catch (\Exception $e) {
            if ($e->getMessage() === 'No tracks found in GPX file' ||
                $e->getMessage() === 'No points found in tracks') {
                throw $e;
            }
            throw new Exception('Invalid GPX file');
        }
    }

    /**
     * Calculate elevation statistics from array of elevation points.
     *
     * @param array<int, float> $elevations
     * @return array{gain: float, loss: float, max: float, min: float}
     */
    private function calculateElevationStats(array $elevations): array
    {
        if (empty($elevations)) {
            return [
                'gain' => 0,
                'loss' => 0,
                'max' => 0,
                'min' => 0
            ];
        }

        $gain = 0;
        $loss = 0;
        $max = max($elevations);
        $min = min($elevations);
        $lastElevation = reset($elevations);

        foreach ($elevations as $elevation) {
            $diff = $elevation - $lastElevation;
            if ($diff > 0) {
                $gain += $diff;
            } else {
                $loss += abs($diff);
            }
            $lastElevation = $elevation;
        }

        return [
            'gain' => $gain,
            'loss' => $loss,
            'max' => $max,
            'min' => $min
        ];
    }
}
