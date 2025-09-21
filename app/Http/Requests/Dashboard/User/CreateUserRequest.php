<?php

namespace App\Http\Requests\Dashboard\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('users.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
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
            'is_active' => ['boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
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
            'roles' => 'role',
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
            'roles.*.exists' => 'Wybrana rola nie istnieje.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default values if not provided
        if (!$this->has('preferences')) {
            $this->merge([
                'preferences' => [
                    'email_notifications' => true,
                    'language' => 'pl'
                ]
            ]);
        }

        if (!$this->has('notification_settings')) {
            $this->merge([
                'notification_settings' => [
                    'enabled' => true,
                    'email' => true,
                    'push' => false
                ]
            ]);
        }

        // Default to active if not specified
        if (!$this->has('is_active')) {
            $this->merge(['is_active' => true]);
        }

        // Clean phone number
        if ($this->has('phone') && $this->phone) {
            $this->merge([
                'phone' => preg_replace('/[^+\d]/', '', $this->phone)
            ]);
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