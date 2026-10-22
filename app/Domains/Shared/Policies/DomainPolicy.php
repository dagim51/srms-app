<?php

namespace App\Domains\Shared\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class DomainPolicy
{
    use HandlesAuthorization;

    /**
     * The domain this policy belongs to.
     */
    abstract public static function domain(): string;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, mixed $model): bool
    {
        return $this->isOwnerOrAdmin($user, $model);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(static::domain().'.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, mixed $model): bool
    {
        return $this->isOwnerOrAdmin($user, $model);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, mixed $model): bool
    {
        return $this->isOwnerOrAdmin($user, $model);
    }

    /**
     * Check if user is owner or administrator.
     */
    protected function isOwnerOrAdmin(User $user, mixed $model): bool
    {
        if ($user->hasRole('Administrator')) {
            return true;
        }

        return method_exists($model, 'user_id') && $model->user_id === $user->id
            || method_exists($model, 'owner_user_id') && $model->owner_user_id === $user->id;
    }
}
