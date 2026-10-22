<?php

namespace App\Domains\Booking\Policies;

use App\Models\User;
use App\Policies\BasePolicy;

class AccommodationBookingPolicy extends BasePolicy
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

        // Guest who made the booking
        if ($booking->user_id === $user->id) {
            return true;
        }

        // Host who owns the accommodation or underlying property
        if ($booking->accommodation) {
            if ($booking->accommodation->property && $booking->accommodation->property->owner_user_id === $user->id) {
                return true;
            }
            if ($booking->accommodation->property && $booking->accommodation->property->organization_id && $user->hasRole('Administrator', $booking->accommodation->property->organization_id)) {
                return true;
            }
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, $booking): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        // Host can update booking status
        if ($booking->accommodation && $booking->accommodation->property && $booking->accommodation->property->owner_user_id === $user->id) {
            return true;
        }

        // Guest can update if booking is still pending
        return $booking->user_id === $user->id && $booking->status === 'pending';
    }

    public function cancel(User $user, $booking): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        // Guest or Host can cancel
        if ($booking->user_id === $user->id) {
            return true;
        }

        if ($booking->accommodation && $booking->accommodation->property && $booking->accommodation->property->owner_user_id === $user->id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, $booking): bool
    {
        return $user->hasRole('Administrator');
    }
}
