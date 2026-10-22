<?php

namespace App\Http\Requests\Lead;

use App\Models\OrganizationMember;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $lead = $this->route('lead');
        $user = $this->user();

        return OrganizationMember::where('organization_id', $lead->organization_id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:new,contacted,qualified,lost,won',
            'assigned_to' => 'nullable|uuid|exists:users,id',
        ];
    }
}
