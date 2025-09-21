<?php

namespace App\Http\Requests\Dashboard\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('users.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($userId)
            ],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female,other'],
            'preferences' => ['nullable', 'array'],
            'preferences.email_notifications' => ['boolean'],
            'preferences.language' => ['string', 'in:pl,en'],
            'notification_settings' => ['nullable', 'array'],
            'notification_settings.enabled' => ['boolean'],
            'notification_settings.email' => ['boolean'],
            'notification_settings.push' => ['boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'imię',
            'last_name' => 'nazwisko',
            'email' => 'adres e-mail',
            'phone' => 'numer telefonu',
            'password' => 'hasło',
            'bio' => 'biografia',
            'location' => 'lokalizacja',
            'birth_date' => 'data urodzenia',
            'gender' => 'płeć',
            'is_active' => 'status aktywności',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Użytkownik z tym adresem e-mail już istnieje.',
            'phone.unique' => 'Użytkownik z tym numerem telefonu już istnieje.',
            'password.min' => 'Hasło musi mieć co najmniej 8 znaków.',
            'birth_date.before' => 'Data urodzenia musi być datą z przeszłości.',
            'gender.in' => 'Wybierz prawidłową płeć.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean phone number if provided
        if ($this->has('phone') && $this->phone) {
            $this->merge([
                'phone' => preg_replace('/[^+\d]/', '', $this->phone)
            ]);
        }

        // Remove empty string values and convert to null for nullable fields
        $nullableFields = ['phone', 'bio', 'location', 'birth_date', 'gender'];

        foreach ($nullableFields as $field) {
            if ($this->has($field) && $this->$field === '') {
                $this->merge([$field => null]);
            }
        }
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Błędy walidacji',
                'errors' => $validator->errors(),
                'data' => null
            ], 422)
        );
    }
}