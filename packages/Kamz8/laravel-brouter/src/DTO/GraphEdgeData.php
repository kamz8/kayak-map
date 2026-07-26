<?php

namespace Kamz\LaravelBRouter\DTO;

class GraphEdgeData
{
    public function __construct(
        public string $id,
        public string $fromNodeId,
        public string $toNodeId,
        public array $geometry,
        public float $distanceMeters,
        public ?string $wayId = null,
        public ?string $riverName = null,
        public ?string $waterway = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'from_node' => $this->fromNodeId,
            'to_node' => $this->toNodeId,
            'geometry' => $this->geometry,
            'distance_m' => $this->distanceMeters,
            'way_id' => $this->wayId,
            'river_name' => $this->riverName,
            'waterway' => $this->waterway,
        ];
    }
}
