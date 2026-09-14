<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'exists:bookings,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'receipt' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'booking_id.required' => 'يرجى تحديد الحجز.',
            'booking_id.exists' => 'الحجز غير موجود.',
            'payment_method_id.required' => 'يرجى اختيار طريقة الدفع.',
            'payment_method_id.exists' => 'طريقة الدفع غير موجودة.',
            'receipt.image' => 'إشعار الدفع يجب أن يكون صورة.',
            'receipt.mimes' => 'صيغة الصورة يجب أن تكون jpg أو jpeg أو png أو webp.',
            'receipt.max' => 'حجم الصورة يجب ألا يتجاوز 5 ميغابايت.',
            'receipt.uploaded' => 'تعذر رفع الملف — تحقق من حجمه وصيغته وحاول مجدداً.',
        ];
    }
}
