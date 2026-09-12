<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city_id' => ['required', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'star_rating' => ['required', 'integer', 'between:1,5'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'city_id.required' => 'يرجى اختيار المدينة.',
            'city_id.exists' => 'المدينة غير موجودة.',
            'name.required' => 'اسم الفندق مطلوب.',
            'name.max' => 'اسم الفندق يجب ألا يتجاوز 150 حرفاً.',
            'star_rating.required' => 'يرجى تحديد تصنيف النجوم.',
            'star_rating.between' => 'التصنيف يجب أن يكون بين 1 و 5 نجوم.',
            'email.email' => 'البريد الإلكتروني غير صالح.',
            'images.*.image' => 'الملفات المرفوعة يجب أن تكون صوراً.',
            'images.*.mimes' => 'صيغ الصور المسموحة: jpg، jpeg، png، webp.',
            'images.*.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميغابايت.',
        ];
    }
}