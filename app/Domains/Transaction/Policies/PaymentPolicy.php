<?php

namespace App\Domains\Transaction\Policies;

use App\Models\User;
use App\Policies\BasePolicy;

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
        if ($user->hasRole('Administrator')) {
            return true;
        }

        if ($user->hasAnyRole(['Finance Officer', 'Property Manager', 'Agent'])) {
            return true;
        }

        if (method_exists($user, 'hasPermissionTo') && (
            $user->hasPermissionTo('finance.payouts.manage') ||
            $user->hasPermissionTo('finance.escrow.release')
        )) {
            return true;
        }

        // Payee/owner if payable has user_id
        if ($payment->payable && isset($payment->payable->user_id) && $payment->payable->user_id === $user->id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, $payment): bool
    {
        return false; // Payments are immutable financial records
    }
}
