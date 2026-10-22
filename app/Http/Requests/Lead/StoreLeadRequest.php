<?php

namespace App\Http\Requests\Lead;

use App\Models\OrganizationMember;
use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organizationId = $this->input('organization_id');
        $user = $this->user();

        return OrganizationMember::where('organization_id', $organizationId)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function rules(): array
    {
        return [
            'organization_id' => 'required|uuid|exists:organizations,id',
            'assigned_to' => 'nullable|uuid|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'source' => 'nullable|string|in:web,walk_in,referral,inquiry',
            'listing_id' => 'nullable|uuid|exists:listings,id',
            'notes' => 'nullable|string',
        ];
    }
}
