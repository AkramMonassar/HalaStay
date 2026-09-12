<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:approve,reject'],
            'admin_note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'يرجى تحديد الإجراء (اعتماد أو رفض).',
            'action.in' => 'الإجراء يجب أن يكون approve أو reject فقط.',
            'admin_note.max' => 'الملاحظة يجب ألا تتجاوز 255 حرفاً.',
        ];
    }
}