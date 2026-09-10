<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class ExpenseIndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'month' => ['nullable', 'date_format:Y-m', 'prohibits:start_date,end_date'],
            'start_date' => ['nullable', 'required_with:end_date', 'date_format:Y-m-d', 'prohibits:month'],
            'end_date' => ['nullable', 'required_with:start_date', 'date_format:Y-m-d', 'after_or_equal:start_date', 'prohibits:month'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }
}
