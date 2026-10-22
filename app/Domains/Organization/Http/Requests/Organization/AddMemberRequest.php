<?php

namespace App\Domains\Organization\Http\Requests\Organization;

use App\Domains\Organization\Models\OrganizationMember;
use Illuminate\Foundation\Http\FormRequest;

class AddMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $organization = $this->route('organization');
        $user = $this->user();

        return OrganizationMember::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|uuid|exists:users,id',
        ];
    }
}
