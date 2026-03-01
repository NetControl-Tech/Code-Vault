<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DestroyLicenseCodesRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_serial' => 'required|integer|min:1',
            'to_serial' => 'required|integer|gte:from_serial',
        ];
    }
}
