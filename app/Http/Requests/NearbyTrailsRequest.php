<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NearbyTrailsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'lat' => 'nullable|numeric|between:-90,90',
            'long' => 'nullable|numeric|between:-180,180',
            'location_name' => 'nullable|string'
        ];
    }

    public function messages(): array
    {
        return [
            'lat.required' => 'Współrzędna szerokości geograficznej jest wymagana.',
            'long.required' => 'Współrzędna długości geograficznej jest wymagana.',
        ];
    }
}
