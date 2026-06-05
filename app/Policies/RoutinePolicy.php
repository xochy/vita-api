<?php

namespace App\Policies;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RoutinePolicy
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
    public function view(User $user, Routine $routine): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create routines');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Routine $routine): bool
    {
        return $user->can('update routines');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Routine $routine): bool
    {
        return $user->can('delete routines');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Routine $routine): bool
    {
        return $user->can('restore routines');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Routine $routine): bool
    {
        return $user->can('force delete routines');
    }

    /**
     * Determine whether the user can view the workout's routines.
     */
    public function viewWorkouts(User $user, Routine $routine): bool
    {
        return $user->can('read workouts');
    }

    /**
     * Determine whether the user can view the routine's translations.
     */
    public function viewTranslations(User $user, Routine $routine): bool
    {
        return $user->can('read routines');
    }
}
