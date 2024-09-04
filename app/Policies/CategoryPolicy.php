<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
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
    public function view(User $user, Category $category): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create categories');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Category $category): bool
    {
        return $user->can('update categories');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Category $category): bool
    {
        return $user->can('delete categories');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Category $category): bool
    {
        return $user->can('restore categories');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Category $category): bool
    {
        return $user->can('force delete categories');
    }

    /**
     * Determine whether the user can view the category's workouts.
     */
    public function viewWorkouts(User $user, Category $category): bool
    {
        return $user->can('read workouts');
    }

    /**
     * Determine whether the user can view the category's translations.
     */
    public function viewTranslations(User $user, Category $category): bool
    {
        return $user->can('read categories');
    }
}
