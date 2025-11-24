<?php

namespace App\Http\Requests\Dashboard\Trail;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkStatusChangeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admin users or users with permission can change trail status
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
            'ids' => [
                'required',
                'array',
                'min:1',
                'max:500', // Limit to 500 trails at once to prevent abuse
            ],
            'ids.*' => [
                'required',
                'integer',
                'exists:trails,id',
            ],
            'status' => [
                'required',
                'string',
                Rule::in(['active', 'inactive', 'draft', 'archived']),
            ],
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
            'ids.required' => 'Lista ID szlaków jest wymagana.',
            'ids.array' => 'Lista ID musi być tablicą.',
            'ids.min' => 'Musisz wybrać przynajmniej jeden szlak.',
            'ids.max' => 'Możesz zmienić status maksymalnie 500 szlaków naraz.',
            'ids.*.required' => 'Każde ID szlaku jest wymagane.',
            'ids.*.integer' => 'ID szlaku musi być liczbą całkowitą.',
            'ids.*.exists' => 'Jeden lub więcej szlaków nie istnieje.',
            'status.required' => 'Nowy status jest wymagany.',
            'status.string' => 'Status musi być tekstem.',
            'status.in' => 'Nieprawidłowy status. Dozwolone wartości: active, inactive, draft, archived.',
        ];
    }

    /**
     * Get validated data with trail IDs and status
     *
     * @return array{ids: array<int>, status: string}
     */
    public function validatedData(): array
    {
        return [
            'ids' => $this->validated('ids'),
            'status' => $this->validated('status'),
        ];
    }
}
