<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest\BaseFormRequest;

class RegisterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'unique:users'],
            'password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
            ],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => trans('auth.email_taken'),
            'email.email' => trans('validation.email'),
            'email.required' => trans('validation.required'),
            'password.required' => trans('validation.required'),
            'password.min' => trans('validation.min.string'),
            'password.confirmed' => trans('validation.confirmed'),
            'password.regex' => trans('auth.password_requirements'),
            'first_name.max' => trans('validation.max.string'),
            'last_name.max' => trans('validation.max.string')
        ];
    }
}
