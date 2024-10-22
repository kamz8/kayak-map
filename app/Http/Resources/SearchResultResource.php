<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SearchResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $result = [
            'id' => $this['id'],
            'name' => $this['name'],
            'type' => $this['type'],
            'icon' => $this['icon'],
            'location' => $this['location'],
            'slug' => $this['slug'],
        ];

        if ($this['type'] !== 'country') {
            $result['state_name'] = $this['state_name'] ?? null;
            $result['country_name'] = $this['country_name'] ?? null;
        }

        return $result;
    }
}
