<?php

namespace App\Http\Requests\Dashboard\Trail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTrailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->is_admin || $this->user()->can('trails.update'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'trail_name' => ['required', 'string', 'max:255'],
            'river_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'trail_length' => ['required', 'integer', 'min:1', 'max:99999999'],
            'difficulty' => ['required', 'string', Rule::in(['łatwy', 'umiarkowany', 'trudny'])],
            'author' => ['nullable', 'string', 'max:255'],
            'scenery' => ['nullable', 'integer', 'min:0', 'max:10'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'difficulty_detailed' => ['nullable', 'string'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'draft', 'archived'])],
            'start_lat' => ['nullable', 'numeric', 'min:-90', 'max:90'],
            'start_lng' => ['nullable', 'numeric', 'min:-180', 'max:180'],
            'end_lat' => ['nullable', 'numeric', 'min:-90', 'max:90'],
            'end_lng' => ['nullable', 'numeric', 'min:-180', 'max:180'],
        ];
    }
}
