<?php

namespace Kamz\LaravelBRouter\Services;

class WaterwayNormalizer
{
    public function normalize(array $overpassData, string $riverName): array
    {
        $nodes = [];
        $edges = [];
        $nodeIdsByCoordinate = [];

        foreach ($overpassData['elements'] ?? [] as $element) {
            if (($element['type'] ?? null) !== 'way' || empty($element['geometry'])) {
                continue;
            }

            $wayId = (string) $element['id'];
            $waterway = $element['tags']['waterway'] ?? null;
            $wayNodeIds = [];

            foreach (array_values($element['geometry']) as $index => $point) {
                $coordinateKey = $this->coordinateKey((float) $point['lat'], (float) $point['lon']);
                $nodeId = $nodeIdsByCoordinate[$coordinateKey] ?? "way:{$wayId}:{$index}";
                $wayNodeIds[] = $nodeId;

                if (isset($nodes[$nodeId])) {
                    continue;
                }

                $nodeIdsByCoordinate[$coordinateKey] = $nodeId;
                $nodes[$nodeId] = [
                    'id' => $nodeId,
                    'lat' => (float) $point['lat'],
                    'lng' => (float) $point['lon'],
                    'virtual' => false,
                ];
            }

            for ($index = 0; $index < count($wayNodeIds) - 1; $index++) {
                $from = $nodes[$wayNodeIds[$index]];
                $to = $nodes[$wayNodeIds[$index + 1]];
                $edgeId = "edge:{$wayId}:{$index}";

                $edges[$edgeId] = [
                    'id' => $edgeId,
                    'from_node' => $from['id'],
                    'to_node' => $to['id'],
                    'geometry' => [
                        ['lat' => $from['lat'], 'lng' => $from['lng']],
                        ['lat' => $to['lat'], 'lng' => $to['lng']],
                    ],
                    'distance_m' => $this->distanceMeters($from['lat'], $from['lng'], $to['lat'], $to['lng']),
                    'way_id' => $wayId,
                    'river_name' => $element['tags']['name'] ?? $riverName,
                    'waterway' => $waterway,
                ];
            }
        }

        return [
            'nodes' => array_values($nodes),
            'edges' => array_values($edges),
        ];
    }

    private function coordinateKey(float $lat, float $lng): string
    {
        return round($lat, 7).':'.round($lng, 7);
    }

    private function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
