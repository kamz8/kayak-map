<?php

namespace Kamz\LaravelBRouter\DTO;

class SnapResultData
{
    public function __construct(
        public array $input,
        public array $snapped,
        public float $distanceMeters,
        public string $edgeId,
    ) {
    }

    public function toArray(): array
    {
        return [
            'input' => [$this->input['lng'], $this->input['lat']],
            'snapped' => [$this->snapped['lng'], $this->snapped['lat']],
            'distance_m' => $this->distanceMeters,
            'edge_id' => $this->edgeId,
        ];
    }
}
