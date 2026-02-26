<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $roleId = $this->route('role')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($roleId),
                Rule::notIn(['Super Admin'])
            ],
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم الدور',
            'permissions' => 'الصلاحيات',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم الدور مطلوب.',
            'name.string' => 'اسم الدور يجب أن يكون نصاً.',
            'name.max' => 'اسم الدور يجب ألا يتجاوز 255 حرفاً.',
            'name.unique' => 'اسم الدور مستخدم بالفعل.',
            'name.not_in' => 'لا يمكن استخدام هذا الاسم للدور.',
            'permissions.array' => 'الصلاحيات يجب أن تكون مصفوفة.',
            'permissions.*.exists' => 'إحدى الصلاحيات المحددة غير موجودة.',
        ];
    }
}
