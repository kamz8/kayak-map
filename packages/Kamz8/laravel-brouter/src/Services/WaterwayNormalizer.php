<?php

namespace Kamz\LaravelBRouter\Services;

class WaterwayNormalizer
{
    public function normalize(array $overpassData, string $riverName = ''): array
    {
        $nodes = [];
        $edges = [];
        $nodeIdsByCoordinate = [];
        $waterways = [];
        $features = [];
        $waterBodies = [];

        foreach ($overpassData['elements'] ?? [] as $element) {
            $type = $element['type'] ?? null;
            $tags = $element['tags'] ?? [];

            if ($type === 'node') {
                $this->addNode($nodes, (string) ($element['id'] ?? ''), $element['lat'] ?? null, $element['lon'] ?? null, $tags, $nodeIdsByCoordinate);
                if ($this->isFeature($tags)) {
                    $features[] = $this->feature($element, $tags, [['lat' => $element['lat'], 'lon' => $element['lon']]]);
                }
                continue;
            }

            $geometry = $this->elementGeometry($element);
            if (count($geometry) < 2) {
                continue;
            }

            $elementId = (string) ($element['id'] ?? '');
            $waterway = $tags['waterway'] ?? null;
            $waterways[] = [
                'id' => $elementId,
                'name' => $tags['name'] ?? $riverName,
                'waterway' => $waterway,
                'geometry' => $geometry,
                'source_tags' => $tags,
            ];

            if ($this->isFeature($tags)) {
                $features[] = $this->feature($element, $tags, $geometry);
            }

            if ($this->isWaterBody($tags)) {
                $waterBodies[] = [
                    'id' => $elementId,
                    'water_type' => $tags['water'] ?? $tags['natural'] ?? null,
                    'name' => $tags['name'] ?? null,
                    'geometry' => $geometry,
                    'source_tags' => $tags,
                ];
            }

            $wayNodeIds = [];

            foreach ($geometry as $index => $point) {
                $coordinateKey = $this->coordinateKey((float) $point['lat'], (float) $point['lon']);
                $nodeId = $nodeIdsByCoordinate[$coordinateKey] ?? "{$type}:{$elementId}:{$index}";
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
                    'osm_id' => $nodeId,
                    'source_tags' => [],
                ];
            }

            for ($index = 0; $index < count($wayNodeIds) - 1; $index++) {
                $from = $nodes[$wayNodeIds[$index]];
                $to = $nodes[$wayNodeIds[$index + 1]];
                $edgeId = $type === 'way'
                    ? "edge:{$elementId}:{$index}"
                    : "edge:{$type}:{$elementId}:{$index}";

                $edges[$edgeId] = [
                    'id' => $edgeId,
                    'from_node' => $from['id'],
                    'to_node' => $to['id'],
                    'geometry' => [
                        ['lat' => $from['lat'], 'lng' => $from['lng']],
                        ['lat' => $to['lat'], 'lng' => $to['lng']],
                    ],
                    'distance_m' => $this->distanceMeters($from['lat'], $from['lng'], $to['lat'], $to['lng']),
                    'way_id' => $elementId,
                    'river_name' => $tags['name'] ?? $riverName,
                    'waterway' => $waterway,
                    'source_tags' => $tags,
                ];
            }
        }

        return [
            'nodes' => array_values($nodes),
            'edges' => array_values($edges),
            'waterways' => $waterways,
            'features' => $features,
            'water_bodies' => $waterBodies,
        ];
    }

    private function addNode(array &$nodes, string $id, mixed $lat, mixed $lng, array $tags, array &$nodeIdsByCoordinate): void
    {
        if ($id === '' || ! is_numeric($lat) || ! is_numeric($lng)) {
            return;
        }

        $coordinateKey = $this->coordinateKey((float) $lat, (float) $lng);
        $nodeId = $nodeIdsByCoordinate[$coordinateKey] ?? "node:{$id}";
        $nodeIdsByCoordinate[$coordinateKey] = $nodeId;
        $nodes[$nodeId] = [
            'id' => $nodeId,
            'osm_id' => $id,
            'lat' => (float) $lat,
            'lng' => (float) $lng,
            'virtual' => false,
            'source_tags' => $tags,
        ];
    }

    private function elementGeometry(array $element): array
    {
        if ($element['type'] === 'way') {
            return array_values($element['geometry'] ?? []);
        }

        $geometry = [];
        foreach ($element['members'] ?? [] as $member) {
            foreach ($member['geometry'] ?? [] as $point) {
                if ($geometry === [] || $geometry[array_key_last($geometry)] !== $point) {
                    $geometry[] = $point;
                }
            }
        }

        return $geometry;
    }

    private function isFeature(array $tags): bool
    {
        return in_array($tags['waterway'] ?? null, ['dam', 'weir', 'lock_gate', 'sluice_gate', 'watermill', 'rapids', 'waterfall'], true)
            || in_array($tags['barrier'] ?? null, ['dam', 'weir'], true)
            || ($tags['lock'] ?? null) === 'yes';
    }

    private function isWaterBody(array $tags): bool
    {
        return ($tags['natural'] ?? null) === 'water'
            || in_array($tags['water'] ?? null, ['reservoir', 'lake'], true);
    }

    private function feature(array $element, array $tags, array $geometry): array
    {
        return [
            'id' => (string) ($element['id'] ?? ''),
            'feature_type' => $tags['waterway'] ?? $tags['barrier'] ?? 'lock',
            'name' => $tags['name'] ?? null,
            'geometry' => $geometry,
            'source_tags' => $tags,
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
