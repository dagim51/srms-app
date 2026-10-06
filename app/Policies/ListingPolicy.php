<?php

namespace App\Policies;

use App\Models\User;

use App\Models\Listing;

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
        return true;
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
