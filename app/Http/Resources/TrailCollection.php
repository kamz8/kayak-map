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
        // add bounding box from lat lang prop
        $boundingBox = [
            'start_lat' => $request->input('start_lat'),
            'end_lat' => $request->input('end_lat'),
            'start_lng' => $request->input('start_lng'),
            'end_lng' => $request->input('end_lng'),
        ];

        $criteria = $request->only(['difficulty', 'scenery']);

        $meta = [
            'total_trails' => $this->collection->count(),
            'bounding_box' => $boundingBox,
            'criteria' => $criteria,
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
