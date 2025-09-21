<?php

namespace App\Http\Requests\Dashboard\Permission;

use Illuminate\Foundation\Http\FormRequest;

class CreatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('permissions.create');
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:permissions,name',
            'roles' => 'nullable|array',
            'roles.*' => 'integer|exists:roles,id'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nazwa uprawnienia jest wymagana.',
            'name.unique' => 'Uprawnienie o tej nazwie już istnieje.',
            'roles.array' => 'Role muszą być przekazane jako tablica.',
            'roles.*.exists' => 'Wybrana rola nie istnieje.',
        ];
    }
}