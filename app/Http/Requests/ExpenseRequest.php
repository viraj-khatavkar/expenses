<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ExpenseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
            'is_one_time' => ['required', 'boolean'],
        ];
    }

    /**
     * An unchecked checkbox is absent from the request, so default it to false.
     */
    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing(['is_one_time' => false]);
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'The category field is required',
        ];
    }
}
