<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'country_id' => ['required', 'exists:countries,id'],
            'name' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'country_id.required' => 'يرجى تحديد الدولة.',
            'country_id.exists' => 'الدولة غير موجودة.',
            'name.required' => 'اسم المدينة مطلوب.',
            'name.max' => 'اسم المدينة يجب ألا يتجاوز 100 حرف.',
        ];
    }
}