<?php

namespace App\Http\Requests\Development;

use App\Models\DevelopmentBuilding;
use App\Models\OrganizationMember;
use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        $buildingId = $this->route('building');
        $building = DevelopmentBuilding::find($buildingId);

        if (! $building) {
            return false;
        }

        $user = $this->user();

        // User must be a member of the building's project organization
        return OrganizationMember::where('organization_id', $building->project->organization_id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function rules(): array
    {
        return [
            'floor_name' => 'nullable|string',
            'unit_number' => 'required|string',
            'status' => 'nullable|string|in:available,reserved,sold',
            'price' => 'nullable|numeric',
        ];
    }
}
