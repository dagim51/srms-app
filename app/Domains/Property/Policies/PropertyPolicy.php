<?php

namespace App\Domains\Property\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Property $property): bool
    {
        if (isset($property->organization_id) && $property->organization_id) {
            return $user->hasRole('Administrator', $property->organization_id) || $user->id === $property->owner_user_id;
        }

        return $user->id === $property->owner_user_id || $user->hasRole('Administrator');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Property $property): bool
    {
        if (isset($property->organization_id) && $property->organization_id) {
            return $user->hasRole('Administrator', $property->organization_id) || $user->id === $property->owner_user_id;
        }

        return $user->id === $property->owner_user_id || $user->hasRole('Administrator');
    }

    public function delete(User $user, Property $property): bool
    {
        return $this->update($user, $property);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('properties.create') || $user->hasAnyRole([
            'Administrator',
            'Property Owner',
            'Seller',
            'Landlord',
            'Host',
            'Agent',
            'Developer',
            'Real Estate Company',
            'Property Manager',
        ]);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Property $property): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Property $property): bool
    {
        return false;
    }
}
