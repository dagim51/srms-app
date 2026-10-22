<?php

namespace App\Domains\Trust\Policies;

use App\Models\Dispute;
use App\Models\User;
use App\Policies\BasePolicy;

class DisputePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, $dispute): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        // Complainant, Respondent, or assigned moderator
        if ($dispute->complainant_id === $user->id || $dispute->respondent_id === $user->id) {
            return true;
        }

        if ($dispute->assigned_moderator_id === $user->id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, $dispute): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        if ($dispute->assigned_moderator_id === $user->id) {
            return true;
        }

        // Parties can update if dispute is still open/awaiting evidence
        if ($dispute->isOpen() && ($dispute->complainant_id === $user->id || $dispute->respondent_id === $user->id)) {
            return true;
        }

        return false;
    }

    public function resolve(User $user, $dispute): bool
    {
        return $user->hasRole('Administrator') || $dispute->assigned_moderator_id === $user->id;
    }

    public function delete(User $user, $dispute): bool
    {
        return $user->hasRole('Administrator');
    }
}
