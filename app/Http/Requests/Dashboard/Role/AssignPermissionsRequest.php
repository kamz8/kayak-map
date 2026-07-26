<?php

namespace App\Http\Requests\Dashboard\Role;

use Illuminate\Foundation\Http\FormRequest;

class AssignPermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('roles.assign_permissions');
    }

    public function rules(): array
    {
        return [
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id'
        ];
    }

    public function messages(): array
    {
        return [
            'permissions.required' => 'Lista uprawnień jest wymagana.',
            'permissions.array' => 'Uprawnienia muszą być przekazane jako tablica.',
            'permissions.*.exists' => 'Wybrane uprawnienie nie istnieje.',
        ];
    }
}