<?php

namespace App\Http\Requests\Dispute;

use Illuminate\Foundation\Http\FormRequest;

class FlagUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can flag another user
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|uuid|exists:users,id',
            'reason' => 'required|in:abusive_communication,harassment,fake_identity,spam,scam_attempt,payment_fraud,contract_violation,other',
            'description' => 'required|string|min:10|max:2000',
            'severity' => 'nullable|in:low,medium,high,critical',
            'context_type' => 'nullable|string',
            'context_id' => 'nullable|uuid',
        ];
    }
}
