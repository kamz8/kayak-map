<?php

namespace App\Http\Resources\BaseResource;

use App\Http\Resources\RecommendedTrailResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BaseTrailsCollection extends ResourceCollection
{
    protected string $message;

    public function __construct($resource, string $message)
    {
        parent::__construct($resource);
        $this->message = $message;
    }

    public function toArray($request): array
    {
        return [
            'status' => 'success',
            'data' => $this->collection,
            'message' => $this->message,
            'total_trails' => $this->collection->count(),
        ];
    }

    public function getResourceClass(): string
    {
        return RecommendedTrailResource::class;
    }
}
