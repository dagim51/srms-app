<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class RefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('payment'));
    }

    public function rules(): array
    {
        $payment = $this->route('payment');

        return [
            'amount' => 'required|numeric|min:0.01|max:'.($payment?->amount ?? 999999),
            'reason' => 'required|string',
        ];
    }
}
