<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ReverseGeocodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = (object) $this->resource;
        return [
            "country" => $data->country,
            "state" => $data->state,
            "city" => $data->city,
            "lat" => $data->lat,
            "lang" => $data->lng,
            "slug" => Str::slug("{$data->country}/{$data->state}/{$data->city}", '/')
        ];
    }
}
