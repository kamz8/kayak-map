<?php

namespace Kamz\LaravelBRouter\Services;

use Kamz\LaravelBRouter\DTO\SnapResultData;
use Kamz\LaravelBRouter\Exceptions\SnapDistanceExceededException;

class EdgeSnapper
{
    public function snap(array $point, array $edges, float $maxDistanceMeters): SnapResultData
    {
        $best = null;

        foreach ($edges as $edge) {
            foreach ($this->segments($edge['geometry']) as $segment) {
                $projection = $this->projectToSegment($point, $segment[0], $segment[1]);

                if ($best === null || $projection['distance_m'] < $best['distance_m']) {
                    $best = $projection + [
                        'edge_id' => $edge['id'],
                        'from_node' => $edge['from_node'],
                        'to_node' => $edge['to_node'],
                        'edge_distance_m' => (float) $edge['distance_m'],
                    ];
                }
            }
        }

        if ($best === null || $best['distance_m'] > $maxDistanceMeters) {
            throw new SnapDistanceExceededException('No waterway edge found within snap tolerance.');
        }

        return new SnapResultData(
            $point,
            ['lat' => $best['lat'], 'lng' => $best['lng']],
            $best['distance_m'],
            $best['edge_id'],
            $best['ratio'],
            $best['edge_distance_m'] * $best['ratio'],
            $best['from_node'],
            $best['to_node'],
        );
    }

    private function projectToSegment(array $point, array $start, array $end): array
    {
        $metersPerDegreeLat = 111320.0;
        $metersPerDegreeLng = 111320.0 * cos(deg2rad($point['lat']));

        $ax = ($start['lng'] - $point['lng']) * $metersPerDegreeLng;
        $ay = ($start['lat'] - $point['lat']) * $metersPerDegreeLat;
        $bx = ($end['lng'] - $point['lng']) * $metersPerDegreeLng;
        $by = ($end['lat'] - $point['lat']) * $metersPerDegreeLat;

        $dx = $bx - $ax;
        $dy = $by - $ay;
        $lengthSquared = ($dx * $dx) + ($dy * $dy);
        $ratio = $lengthSquared > 0.0 ? max(0.0, min(1.0, - (($ax * $dx) + ($ay * $dy)) / $lengthSquared)) : 0.0;

        $x = $ax + ($ratio * $dx);
        $y = $ay + ($ratio * $dy);

        return [
            'lat' => $point['lat'] + ($y / $metersPerDegreeLat),
            'lng' => $point['lng'] + ($x / $metersPerDegreeLng),
            'distance_m' => sqrt(($x * $x) + ($y * $y)),
            'ratio' => $ratio,
        ];
    }

    private function segments(array $geometry): array
    {
        $segments = [];

        for ($index = 0; $index < count($geometry) - 1; $index++) {
            $segments[] = [$geometry[$index], $geometry[$index + 1]];
        }

        return $segments;
    }
}
