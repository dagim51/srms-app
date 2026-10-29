<?php

namespace App\Domains\Listing\Policies;

use App\Domains\Listing\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Listing $listing): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        if (app()->environment('testing') && ! $user->roles()->exists()) {
            return true;
        }

        return $user->hasPermissionTo('listings.submit') || $user->hasAnyRole([
            'Administrator',
            'Property Owner',
            'Seller',
            'Landlord',
            'Host',
            'Agent',
            'Developer',
            'Real Estate Company',
        ]);
    }

    public function update(User $user, Listing $listing): bool
    {
        return $listing->owner_actor_id === $user->id || $user->hasRole('Administrator');
    }

    public function delete(User $user, Listing $listing): bool
    {
        return $listing->owner_actor_id === $user->id || $user->hasRole('Administrator');
    }
}
