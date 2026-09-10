<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receipt' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'receipt.required' => 'يرجى إرفاق صورة إشعار الدفع.',
            'receipt.image' => 'إشعار الدفع يجب أن يكون صورة.',
            'receipt.mimes' => 'صيغة الصورة يجب أن تكون jpg أو jpeg أو png أو webp.',
            'receipt.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميغابايت.',
        ];
    }
}