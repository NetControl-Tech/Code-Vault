<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
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
        $permissionId = $this->route('permission')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->ignore($permissionId)
            ],
            'name_ar' => [
                'nullable',
                'string',
                'max:255',
            ],
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
            'name' => 'اسم الصلاحية',
            'name_ar' => 'الاسم بالعربية',
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
            'name.required' => 'اسم الصلاحية مطلوب.',
            'name.string' => 'اسم الصلاحية يجب أن يكون نصاً.',
            'name.max' => 'اسم الصلاحية يجب ألا يتجاوز 255 حرفاً.',
            'name.unique' => 'اسم الصلاحية مستخدم بالفعل.',
            'name_ar.string' => 'الاسم بالعربية يجب أن يكون نصاً.',
            'name_ar.max' => 'الاسم بالعربية يجب ألا يتجاوز 255 حرفاً.',
        ];
    }
}
