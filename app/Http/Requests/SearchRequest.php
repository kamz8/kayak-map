<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\RegionType;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
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
            'query' => ['required', 'string', 'min:2', 'max:100'],
            'type' => ['sometimes', 'string', Rule::in(['all', 'trail', ...array_column(RegionType::cases(), 'value')])],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'query.required' => 'A search query is required.',
            'query.min' => 'The search query must be at least :min characters.',
            'query.max' => 'The search query may not be greater than :max characters.',
            'type.in' => 'The selected search type is invalid.',
            'limit.integer' => 'The limit must be an integer.',
            'limit.min' => 'The limit must be at least :min.',
            'limit.max' => 'The limit may not be greater than :max.',
        ];
    }
}
