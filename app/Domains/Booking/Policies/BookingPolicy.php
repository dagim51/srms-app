<?php

namespace App\Domains\Booking\Policies;

use App\Models\User;
use App\Policies\BasePolicy;

class BookingPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, $booking): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        if ($booking->user_id === $user->id) {
            return true;
        }

        if ($booking->accommodation && $booking->accommodation->property) {
            if ($booking->accommodation->property->owner_user_id === $user->id) {
                return true;
            }
        }

        return false;
    }

    public function cancel(User $user, $booking): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        return $booking->user_id === $user->id;
    }
}
