<?php

namespace App\Http\Requests\Dashboard\Role;

use Illuminate\Foundation\Http\FormRequest;

class CreateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('roles.create');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nazwa roli jest wymagana.',
            'name.unique' => 'Rola o tej nazwie już istnieje.',
            'permissions.array' => 'Uprawnienia muszą być przekazane jako tablica.',
            'permissions.*.exists' => 'Wybrane uprawnienie nie istnieje.',
        ];
    }
}