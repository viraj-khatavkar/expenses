<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InvestmentMonthRequest extends FormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'planned' => ['bail', 'present', 'nullable', 'numeric', 'decimal:0,2', 'between:0,9999999999.99'],
        ];
    }
}
