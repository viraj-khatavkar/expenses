<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class IncomeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'source_id' => ['required', 'exists:sources,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
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
