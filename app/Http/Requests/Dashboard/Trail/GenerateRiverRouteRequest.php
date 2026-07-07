<?php

namespace App\Http\Requests\Dashboard\Trail;

use Illuminate\Foundation\Http\FormRequest;

class GenerateRiverRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start' => ['sometimes', 'array'],
            'start.lat' => ['required_with:start', 'numeric', 'between:-90,90'],
            'start.lng' => ['required_with:start', 'numeric', 'between:-180,180'],
            'end' => ['sometimes', 'array'],
            'end.lat' => ['required_with:end', 'numeric', 'between:-90,90'],
            'end.lng' => ['required_with:end', 'numeric', 'between:-180,180'],
            'snap_tolerance_m' => ['sometimes', 'numeric', 'min:1'],
            'simplify' => ['sometimes', 'boolean'],
        ];
    }
}
