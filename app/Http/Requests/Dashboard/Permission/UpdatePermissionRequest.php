<?php

namespace App\Http\Requests\Dashboard\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('permissions.update');
    }

    public function rules(): array
    {
        $permissionId = $this->route('permission')->id ?? null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->ignore($permissionId)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nazwa uprawnienia jest wymagana.',
            'name.unique' => 'Uprawnienie o tej nazwie już istnieje.',
        ];
    }
}