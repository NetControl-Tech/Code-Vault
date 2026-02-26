<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userId)],
            'password' => ['sometimes', 'nullable', Password::defaults()],
            'role' => ['sometimes', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.string' => 'الاسم يجب أن يكون نصاً',
            'name.max' => 'الاسم يجب ألا يتجاوز 255 حرفاً',
            'email.string' => 'البريد الإلكتروني يجب أن يكون نصاً',
            'email.max' => 'البريد الإلكتروني يجب ألا يتجاوز 255 حرفاً',
            'email.email' => 'البريد الإلكتروني غير صالح',
            'email.unique' => 'البريد الإلكتروني مستخدم بالفعل',
            'role.string' => 'الدور يجب أن يكون نصاً',
            'role.in' => 'الدور غير صالح',
        ];
    }
}
