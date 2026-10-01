<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchHotelsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city_id' => ['required', 'exists:cities,id'],
            'check_in' => ['nullable', 'date', 'after_or_equal:today', 'required_with:check_out'],
            'check_out' => ['nullable', 'date', 'after:check_in', 'required_with:check_in'],
            'adults' => ['nullable', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'rooms' => ['nullable', 'integer', 'min:1'],
            'stay_type' => ['nullable', 'array'],
            'stay_type.*' => ['in:room,apartment,suite,hall'],
            'star_rating' => ['nullable', 'array'],
            'star_rating.*' => ['integer', 'between:1,5'],
            'min_review' => ['nullable', 'numeric', 'between:0,10'],
        ];
    }

    public function messages(): array
    {
        return [
            'city_id.required' => 'يرجى اختيار المدينة.',
            'city_id.exists' => 'المدينة المحددة غير موجودة.',
            'check_in.date' => 'صيغة تاريخ الدخول غير صحيحة.',
            'check_in.after_or_equal' => 'تاريخ الدخول يجب أن يكون اليوم أو لاحقاً.',
            'check_in.required_with' => 'أضفت تاريخ خروج — أكمل تاريخ الدخول.',
            'check_out.date' => 'صيغة تاريخ الخروج غير صحيحة.',
            'check_out.after' => 'تاريخ الخروج يجب أن يكون بعد تاريخ الدخول.',
            'check_out.required_with' => 'أضفت تاريخ دخول — أكمل تاريخ الخروج.',
            'adults.min' => 'يجب أن يكون عدد البالغين واحداً على الأقل.',
            'rooms.min' => 'يجب أن يكون عدد الغرف واحداً على الأقل.',
        ];
    }
}