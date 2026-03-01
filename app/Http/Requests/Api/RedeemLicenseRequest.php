<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RedeemLicenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public access
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Ensure PIN is exact format and length expected (e.g. alphanumeric 12 chars limit)
            'pin_code' => ['required', 'string', 'size:12', 'regex:/^[A-Z0-9]+$/'],
            'device_id' => ['required', 'string', 'min:10', 'max:255'],
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'pin_code.regex' => 'The PIN code format is invalid.',
            'pin_code.size' => 'The PIN code must be exactly 12 characters.',
        ];
    }
}
