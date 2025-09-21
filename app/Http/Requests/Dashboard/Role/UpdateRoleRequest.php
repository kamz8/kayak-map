<?php

namespace App\Http\Requests\Dashboard\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('roles.update');
    }

    public function rules(): array
    {
        $roleId = $this->route('role')->id ?? null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($roleId)
            ],
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