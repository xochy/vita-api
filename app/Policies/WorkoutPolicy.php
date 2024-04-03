<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workout;
use Illuminate\Auth\Access\Response;

class WorkoutPolicy
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
    public function view(User $user, Workout $workout): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create workouts');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Workout $workout): bool
    {
        return $user->can('update workouts');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Workout $workout): bool
    {
        return $user->can('delete workouts');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Workout $workout): bool
    {
        return $user->can('restore workouts');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Workout $workout): bool
    {
        return $user->can('force delete workouts');
    }

    /**
     * Determine whether the user can view the model category.
     */
    public function viewSubcategory(User $user, Workout $workout): bool
    {
        return $user->can('read subcategories');
    }
}
