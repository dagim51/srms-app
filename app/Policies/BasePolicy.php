<?php

namespace App\Policies;

use App\Models\User;

abstract class BasePolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, $model)
    {
        if (isset($model->organization_id) && $model->organization_id) {
            return $user->hasRole('Administrator', $model->organization_id) || $user->id === $model->user_id;
        }

        return $user->id === $model->user_id || $user->hasRole('Administrator');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, $model)
    {
        if (isset($model->organization_id) && $model->organization_id) {
            return $user->hasRole('Administrator', $model->organization_id) || $user->id === $model->user_id;
        }

        return $user->id === $model->user_id || $user->hasRole('Administrator');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, $model)
    {
        return $this->update($user, $model);
    }
}
