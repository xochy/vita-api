<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Variation;
use Illuminate\Auth\Access\Response;

class VariationPolicy
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
        return $user->can('read variations');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Variation $variation): bool
    {
        return $user->can('read variations');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create variations');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Variation $variation): bool
    {
        return $user->can('update variations');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Variation $variation): bool
    {
        return $user->can('delete variations');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Variation $variation): bool
    {
        return $user->can('restore variations');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Variation $variation): bool
    {
        return $user->can('forceDelete variations');
    }

    /**
     * Determine whether the user can view the workout's category.
     */
    public function viewWorkout(User $user, Variation $variation): bool
    {
        return $user->can('read workouts');
    }

    /**
     * Determine whether the user can view the workout's muscles.
     */
    public function viewMuscles(User $user, Variation $variation): bool
    {
        dd($user->can('read muscles'));
        return $user->can('read muscles');
    }
}
