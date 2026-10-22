<?php

namespace App\Http\Requests\Development;

use App\Models\OrganizationMember;
use Illuminate\Foundation\Http\FormRequest;

class StorePaymentPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');
        $user = $this->user();

        // User must be a member of the project's organization
        return OrganizationMember::where('organization_id', $project->organization_id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
