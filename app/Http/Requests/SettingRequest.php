<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
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
        return [
            'key' => 'nullable|string',
            'value' => 'nullable|string',
            'settings' => 'nullable|array',
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
            'key' => 'مفتاح الإعداد',
            'value' => 'قيمة الإعداد',
            'settings' => 'الإعدادات',
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
            'key.string' => 'مفتاح الإعداد يجب أن يكون نصاً.',
            'value.string' => 'قيمة الإعداد يجب أن تكون نصاً.',
            'settings.array' => 'الإعدادات يجب أن تكون مصفوفة.',
        ];
    }
}
