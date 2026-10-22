<?php

namespace App\Http\Requests\Development;

use App\Models\OrganizationMember;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be a member of the organization
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:planning,construction,completed',
            'handover_date' => 'nullable|date',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
        ];
    }
}
