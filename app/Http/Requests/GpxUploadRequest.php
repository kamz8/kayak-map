<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GpxUploadRequest extends FormRequest
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
            'gpx_file' => ['required', 'file', 'mimes:gpx,xml', 'max:10240'],
            'trail_id' => ['required', 'exists:trails,id']
        ];
    }
}
