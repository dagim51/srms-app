<?php

namespace App\Domains\Trust\Policies;

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

        return $dispute->complainant_id === $user->id || $dispute->respondent_id === $user->id;
    }

    public function submitEvidence(User $user, $dispute): bool
    {
        return $this->view($user, $dispute);
    }

    public function resolve(User $user, $dispute): bool
    {
        return $user->hasRole('Administrator');
    }
}
