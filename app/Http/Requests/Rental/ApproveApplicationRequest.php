<?php

namespace App\Http\Requests\Rental;

use Illuminate\Foundation\Http\FormRequest;

class ApproveApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $application = $this->route('application');
        $user = $this->user();

        // Allow if user is the property owner, or has property management role
        $property = $application->property;

        if ($property && $property->owner_user_id === $user->id) {
            return true;
        }

        // Allow if user has property management permissions
        // In real app, this would check for property manager role
        return true; // For now, allow any authenticated user (simulating property manager)
    }

    public function rules(): array
    {
        return [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'rent_amount' => 'required|numeric',
            'deposit_amount' => 'required|numeric',
        ];
    }
}
