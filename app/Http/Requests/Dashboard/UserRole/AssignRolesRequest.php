<?php

namespace App\Http\Requests\Dashboard\UserRole;

use Illuminate\Foundation\Http\FormRequest;

class AssignRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.assign_roles');
    }

    public function rules(): array
    {
        return [
            'roles' => 'required|array',
            'roles.*' => 'integer|exists:roles,id'
        ];
    }

    public function messages(): array
    {
        return [
            'roles.required' => 'Lista ról jest wymagana.',
            'roles.array' => 'Role muszą być przekazane jako tablica.',
            'roles.*.exists' => 'Wybrana rola nie istnieje.',
        ];
    }
}