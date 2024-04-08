<?php

namespace App\Policies;

use App\Models\Frequency;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FrequencyPolicy
{
    /**
     * Determine whether the user have all permissions.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function before($user, $ability)
    {
        if ($user->hasRole('superAdmin')) {
            return true;
        }
    }

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
    public function view(User $user, Frequency $frequency): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create frequencies');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Frequency $frequency): bool
    {
        return $user->can('update frequencies');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Frequency $frequency): bool
    {
        return $user->can('delete frequencies');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Frequency $frequency): bool
    {
        return $user->can('restore frequencies');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Frequency $frequency): bool
    {
        return $user->can('force delete frequencies');
    }
}
