<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'difficulty' => 'nullable|array',
            'difficulty.*' => 'in:łatwy,umiarkowany,trudny',
            'scenery' => 'nullable|integer|min:0|max:10',
            'start_lat' => 'required|numeric|between:-90,90',
            'end_lat' => 'required|numeric|between:-90,90',
            'start_lng' => 'required|numeric|between:-180,180',
            'end_lng' => 'required|numeric|between:-180,180',
            'search_query' => 'nullable|string|max:255',
            'min_length' => 'nullable|integer|min:0',
            'max_length' => 'nullable|integer|min:0',
            'rating' => 'nullable|numeric|min:0|max:5',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'min_length' => $this->min_length ? (int) $this->min_length : null,
            'max_length' => $this->max_length ? (int) $this->max_length : null,
        ]);
    }
}
