<?php

namespace App\Domains\Identity\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public password reset request
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
        ];
    }
}
