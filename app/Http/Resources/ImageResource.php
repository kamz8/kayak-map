<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'path' => $this->path,
            'alt' => $this->alt,
            'is_main' => $this->pivot ? $this->pivot->is_main : false,
            'order' => $this->pivot ? $this->pivot->order : null,
        ];
    }
}
