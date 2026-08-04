<?php

namespace Kamz\LaravelBRouter\DTO;

class SnapResultData
{
    public function __construct(
        public array $input,
        public array $snapped,
        public float $distanceMeters,
        public string $edgeId,
        public ?float $position = null,
        public ?float $offsetMeters = null,
        public ?string $fromNodeId = null,
        public ?string $toNodeId = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'input' => [$this->input['lng'], $this->input['lat']],
            'snapped' => [$this->snapped['lng'], $this->snapped['lat']],
            'distance_m' => $this->distanceMeters,
            'edge_id' => $this->edgeId,
            'position' => $this->position,
            'offset_m' => $this->offsetMeters,
            'from_node_id' => $this->fromNodeId,
            'to_node_id' => $this->toNodeId,
        ];
    }
}
