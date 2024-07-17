<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TrailCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $meta = [
            'total_trails' => $this->collection->count(),
        ];

        if ($this->collection->isEmpty()) {
            $meta['message'] = 'No trails found for the given criteria.';
        }

        return [
            'data' => $this->collection,
            'meta' => $meta,
        ];
    }
}
