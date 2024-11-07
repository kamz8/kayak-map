<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TrailCollection extends ResourceCollection
{
    protected $additionalMeta;

    public function __construct($resource, $additionalMeta = [])
    {
        parent::__construct($resource);
        $this->additionalMeta = $additionalMeta;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $criteria = $request->only(['difficulty', 'scenery']);

        $meta = [
            'total_trails' => $this->collection->count(),
            'bounding_box' => $this->additionalMeta['bounding_box'] ?? null,
/*            'criteria' => $criteria,
            'main_region' => $this->additionalMeta['main_region'] ?? null,
            'regions' => $this->collection->flatMap->regions->unique('id')->values(),*/
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
