<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'stay_type' => ['required', 'in:room,apartment,suite,hall'],
            'description' => ['nullable', 'string'],
            'max_adults' => ['required', 'integer', 'min:1'],
            'max_children' => ['nullable', 'integer', 'min:0'],
            'total_units' => ['required', 'integer', 'min:1'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم نوع الإقامة مطلوب.',
            'stay_type.required' => 'يرجى تحديد تصنيف الإقامة.',
            'stay_type.in' => 'التصنيف يجب أن يكون: غرفة، شقة، جناح، أو قاعة.',
            'max_adults.required' => 'يرجى تحديد الحد الأقصى للبالغين.',
            'total_units.required' => 'يرجى تحديد عدد الوحدات المتاحة.',
            'total_units.min' => 'عدد الوحدات يجب أن يكون واحداً على الأقل.',
            'base_price.required' => 'يرجى تحديد سعر الليلة.',
            'base_price.min' => 'السعر يجب ألا يكون سالباً.',
        ];
    }
}