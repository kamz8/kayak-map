<?php

namespace Kamz\LaravelBRouter\DTO;

use InvalidArgumentException;

class RouteRequestData
{
    public function __construct(
        public string $riverName,
        public array $start,
        public array $end,
        public float $snapToleranceMeters = 500.0,
        public bool $simplify = true,
        public string $mode = 'snap',
    ) {
    }

    public static function fromArray(array $data): self
    {
        foreach (['river_name', 'start', 'end'] as $field) {
            if (! array_key_exists($field, $data)) {
                throw new InvalidArgumentException("Missing route field: {$field}");
            }
        }

        return new self(
            riverName: (string) $data['river_name'],
            start: self::normalizePoint($data['start']),
            end: self::normalizePoint($data['end']),
            snapToleranceMeters: (float) ($data['snap_tolerance_m'] ?? 500),
            simplify: (bool) ($data['simplify'] ?? true),
            mode: (string) ($data['mode'] ?? 'snap'),
        );
    }

    private static function normalizePoint(array $point): array
    {
        if (! array_key_exists('lat', $point) || ! array_key_exists('lng', $point)) {
            throw new InvalidArgumentException('Route point must contain lat and lng.');
        }

        return [
            'lat' => (float) $point['lat'],
            'lng' => (float) $point['lng'],
        ];
    }
}
