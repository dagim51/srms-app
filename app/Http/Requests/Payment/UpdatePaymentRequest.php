<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('payment'));
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:completed,failed,refunded',
            'transaction_reference' => 'nullable|string',
            'metadata' => 'nullable|array',
        ];
    }
}
