<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Lub twoja logika autoryzacji
    }

    public function rules(): array
    {
        return [
            'url' => ['sometimes', 'required', 'string', 'url'],
            'meta_data' => ['nullable', 'string', 'json'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('meta_data') && $this->meta_data !== null) {
                $metaData = json_decode($this->meta_data, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $validator->errors()->add('meta_data', 'Meta data must be valid JSON.');
                    return;
                }

                // Walidacja pól w JSON
                if (!isset($metaData['title']) || empty(trim($metaData['title']))) {
                    $validator->errors()->add('meta_data.title', 'The title field is required in meta data.');
                }

                if (isset($metaData['description']) && strlen($metaData['description']) > 300) {
                    $validator->errors()->add('meta_data.description', 'The description may not be greater than 300 characters.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'meta_data.json' => 'Meta data must be a valid JSON string.',
            'meta_data.title.required' => 'The title field is required in meta data.',
            'meta_data.description.max' => 'The description may not be greater than 300 characters.',
        ];
    }
}
