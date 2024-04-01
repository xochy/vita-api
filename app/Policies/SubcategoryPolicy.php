<?php

namespace App\Policies;

use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SubcategoryPolicy
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
    public function view(User $user, Subcategory $subcategory): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create subcategories');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Subcategory $subcategory): bool
    {
        return $user->can('update subcategories');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Subcategory $subcategory): bool
    {
        return $user->can('delete subcategories');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Subcategory $subcategory): bool
    {
        return $user->can('restore subcategories');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Subcategory $subcategory): bool
    {
        return $user->can('force delete subcategories');
    }

    /**
     * Determine whether the user can view the model category.
     */
    public function viewCategory(User $user, Subcategory $subcategory): bool
    {
        return $user->can('read categories');
    }

    /**
     * Determine whether the user can update the model's category relationship.
     */
    public function updateCategory(User $user, Subcategory $subcategory): bool
    {
        return $user->can('update subcategories');
    }

    /**
     * Determine whether the user can attach tags to the model's category relationship.
     */
    public function attachCategory(User $user, Subcategory $subcategory): bool
    {
        return $user->can('update subcategories');
    }

    /**
     * Determine whether the user can detach tags from the model's category relationship.
     */
    public function detachCategory(User $user, Subcategory $subcategory): bool
    {
        return $user->can('update subcategories');
    }
}
