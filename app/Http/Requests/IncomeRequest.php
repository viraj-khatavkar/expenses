<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class IncomeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'source_id' => ['required', 'exists:sources,id'],
            'amount' => ['required', 'numeric'],
            'date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'source_id.required' => 'The source field is required',
        ];
    }
}
