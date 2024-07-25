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
            'difficulty' => 'nullable|in:łatwy,umiarkowany,trudny',
            'scenery' => 'nullable|integer|min:0|max:10',
            'start_lat' => 'required|numeric',
            'end_lat' => 'required|numeric',
            'start_lng' => 'required|numeric',
            'end_lng' => 'required|numeric',
        ];
    }
}
