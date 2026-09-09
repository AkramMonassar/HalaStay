<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'accommodation_type_id' => ['required', 'exists:accommodation_types,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'rooms' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'accommodation_type_id.required' => 'يرجى اختيار نوع الإقامة.',
            'accommodation_type_id.exists' => 'نوع الإقامة غير موجود.',
            'check_in.required' => 'تاريخ الدخول مطلوب.',
            'check_in.after_or_equal' => 'تاريخ الدخول يجب أن يكون اليوم أو لاحقاً.',
            'check_out.required' => 'تاريخ الخروج مطلوب.',
            'check_out.after' => 'تاريخ الخروج يجب أن يكون بعد تاريخ الدخول.',
            'adults.required' => 'عدد البالغين مطلوب.',
            'rooms.required' => 'عدد الغرف مطلوب.',
            'rooms.min' => 'يجب أن يكون عدد الغرف واحداً على الأقل.',
        ];
    }
}