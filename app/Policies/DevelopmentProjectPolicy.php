<?php

namespace App\Policies;

use App\Models\DevelopmentProject;
use App\Models\User;

class DevelopmentProjectPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, $project): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Administrator') || $user->hasRole('Developer');
    }

    public function update(User $user, $project): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        if ($project->organization_id) {
            return $user->hasRole('Administrator', $project->organization_id) || $user->hasRole('Developer', $project->organization_id);
        }

        return false;
    }

    public function delete(User $user, $project): bool
    {
        return $this->update($user, $project);
    }
}
