<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class InvestmentTransactionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($category = $this->input('category'))) {
            $this->merge(['category' => Str::squish($category)]);
        }
    }

    public function rules(): array
    {
        return [
            'direction' => ['required', Rule::in(['deposit', 'redemption'])],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['bail', 'required', 'numeric', 'decimal:0,2', 'between:0.01,9999999999.99'],
            'date' => ['required', $this->isMethod('post') ? 'date_format:Y-m-d' : 'date_format:Y-m-d,Y-m', 'regex:/^[1-9]\d{3}-/'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
