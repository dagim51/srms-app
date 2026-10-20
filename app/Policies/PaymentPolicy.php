<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, $payment): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        // Payer
        if ($payment->user_id === $user->id) {
            return true;
        }

        // Payee/owner if payable has property/owner
        if ($payment->payable && isset($payment->payable->user_id) && $payment->payable->user_id === $user->id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, $payment): bool
    {
        return $user->hasRole('Administrator');
    }

    public function delete(User $user, $payment): bool
    {
        return false; // Payments are immutable financial records
    }
}
