<?php

namespace App\Policies;

use App\Models\Lease;
use App\Models\User;

class LeasePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, $lease): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        // Check if user is the tenant
        if ($lease->tenantProfile && $lease->tenantProfile->user_id === $user->id) {
            return true;
        }

        // Check if user is the property owner or part of managing organization
        if ($lease->property) {
            if ($lease->property->owner_user_id === $user->id) {
                return true;
            }
            if ($lease->property->organization_id && $user->hasRole('Administrator', $lease->property->organization_id)) {
                return true;
            }
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Administrator') || $user->hasRole('Seller') || $user->hasRole('Agent');
    }

    public function update(User $user, $lease): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        if ($lease->property) {
            if ($lease->property->owner_user_id === $user->id) {
                return true;
            }
            if ($lease->property->organization_id && $user->hasRole('Administrator', $lease->property->organization_id)) {
                return true;
            }
        }

        return false;
    }

    public function delete(User $user, $lease): bool
    {
        return $this->update($user, $lease);
    }
}
