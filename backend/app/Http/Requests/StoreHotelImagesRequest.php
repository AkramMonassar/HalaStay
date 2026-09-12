<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHotelImagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => ['required', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => 'يرجى إرفاق صورة واحدة على الأقل.',
            'images.array' => 'الصور يجب أن تُرسل كمصفوفة.',
            'images.*.image' => 'الملفات المرفوعة يجب أن تكون صوراً.',
            'images.*.mimes' => 'صيغ الصور المسموحة: jpg، jpeg، png، webp.',
            'images.*.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميغابايت.',
        ];
    }
}