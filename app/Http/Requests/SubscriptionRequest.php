<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SubscriptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'in:INR,USD'],
            'frequency' => ['required', 'in:monthly,quarterly,yearly'],
        ];
    }
}
