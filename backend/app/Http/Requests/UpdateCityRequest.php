<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'اسم المدينة يجب ألا يتجاوز 100 حرف.',
            'is_active.boolean' => 'حالة النشاط يجب أن تكون true أو false.',
        ];
    }
}