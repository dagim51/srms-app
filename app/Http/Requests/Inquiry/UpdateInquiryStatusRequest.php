<?php

namespace App\Http\Requests\Inquiry;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInquiryStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inquiry = $this->route('inquiry');
        $user = $this->user();

        // Only the seller/agent can update inquiry status
        return $inquiry->seller_agent_id === $user->id;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,responded,closed',
            'response' => 'nullable|string',
        ];
    }
}
