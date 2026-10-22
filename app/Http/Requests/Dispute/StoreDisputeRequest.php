<?php

namespace App\Http\Requests\Dispute;

use Illuminate\Foundation\Http\FormRequest;

class StoreDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Any authenticated user can create a dispute
    }

    public function rules(): array
    {
        return [
            'disputable_type' => 'required|string|in:App\Models\Listing,App\Models\User,App\Models\Offer,App\Models\Reservation,App\Models\Lease',
            'disputable_id' => 'required|uuid',
            'type' => 'required|string|in:deposit_dispute,misrepresentation,non_delivery,quality_issue,contract_breach,fraud_suspected,communication_abuse,other',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'amount_in_dispute' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'evidence' => 'nullable|array',
            'evidence.*.type' => 'required_with:evidence|in:document,screenshot,communication,transaction_record,contract,other',
            'evidence.*.title' => 'required_with:evidence|string|max:255',
            'evidence.*.description' => 'nullable|string',
            'evidence.*.file_path' => 'nullable|string',
            'evidence.*.file_url' => 'nullable|url',
        ];
    }
}
