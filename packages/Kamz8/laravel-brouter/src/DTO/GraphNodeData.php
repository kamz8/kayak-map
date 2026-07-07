<?php

namespace Kamz\LaravelBRouter\DTO;

class GraphNodeData
{
    public function __construct(
        public string $id,
        public float $lat,
        public float $lng,
        public bool $virtual = false,
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'virtual' => $this->virtual,
        ];
    }
}
