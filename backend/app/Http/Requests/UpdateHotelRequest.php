<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city_id' => ['sometimes', 'required', 'exists:cities,id'],
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'star_rating' => ['sometimes', 'required', 'integer', 'between:1,5'],
        ];
    }

    public function messages(): array
    {
        return [
            'city_id.exists' => 'المدينة غير موجودة.',
            'name.max' => 'اسم الفندق يجب ألا يتجاوز 150 حرفاً.',
            'star_rating.between' => 'التصنيف يجب أن يكون بين 1 و 5 نجوم.',
            'email.email' => 'البريد الإلكتروني غير صالح.',
        ];
    }
}