<?php

namespace App\Http\Requests\Admin;

use App\Enums\BlocklistCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUploadRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:txt,csv', 'max:10240'], // 10MB max
            'category' => ['required', 'string', Rule::enum(BlocklistCategory::class)],
        ];
    }
}
