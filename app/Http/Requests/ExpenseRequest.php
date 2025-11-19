<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ExpenseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric'],
            'date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'The category field is required',
        ];
    }
}
