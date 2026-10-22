<?php

namespace App\Http\Requests\Lead;

use App\Models\OrganizationMember;
use Illuminate\Foundation\Http\FormRequest;

class AddActivityRequest extends FormRequest
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
            'type' => 'required|string|in:call,email,meeting,note',
            'description' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
        ];
    }
}
