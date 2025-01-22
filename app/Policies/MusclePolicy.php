<?php

namespace App\Policies;

use App\Models\Muscle;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MusclePolicy
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
    public function view(User $user, Muscle $muscle): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create muscles');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Muscle $muscle): bool
    {
        return $user->can('update muscles');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Muscle $muscle): bool
    {
        return $user->can('delete muscles');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Muscle $muscle): bool
    {
        return $user->can('restore muscles');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Muscle $muscle): bool
    {
        return $user->can('force delete muscles');
    }

    /**
     * Determine whether the user can view the muscle's workouts.
     */
    public function viewWorkouts(User $user, Muscle $muscle): bool
    {
        return $user->can('read workouts');
    }

    /**
     * Determine whether the user can view the muscle's variations.
     */
    public function viewVariations(User $user, Muscle $muscle): bool
    {
        return $user->can('read variations');
    }

    /**
     * Determine whether the user can view the muscle's translations.
     */
    public function viewTranslations(User $user, Muscle $muscle): bool
    {
        return $user->can('read muscles');
    }

    public function viewMedias(User $user, Muscle $muscle): bool
    {
        return $user->can('read muscles');
    }
}
