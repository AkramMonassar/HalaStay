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
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
            'rooms' => ['required', 'integer', 'min:1'],
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
            'check_in.required' => 'تاريخ الدخول مطلوب.',
            'check_in.after_or_equal' => 'تاريخ الدخول يجب أن يكون اليوم أو لاحقاً.',
            'check_out.required' => 'تاريخ الخروج مطلوب.',
            'check_out.after' => 'تاريخ الخروج يجب أن يكون بعد تاريخ الدخول.',
            'adults.required' => 'عدد البالغين مطلوب.',
            'adults.min' => 'يجب أن يكون عدد البالغين واحداً على الأقل.',
            'rooms.required' => 'عدد الغرف مطلوب.',
            'rooms.min' => 'يجب أن يكون عدد الغرف واحداً على الأقل.',
        ];
    }
}