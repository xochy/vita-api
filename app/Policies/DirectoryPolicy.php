<?php

namespace App\Policies;

use App\Models\Directory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DirectoryPolicy
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
        return $user->can('read directories');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Directory $directory): bool
    {
        return $user->can('read directories');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create directories');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Directory $directory): bool
    {
        return $user->can('update directories');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Directory $directory): bool
    {
        return $user->can('delete directories');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Directory $directory): bool
    {
        return $user->can('restore directories');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Directory $directory): bool
    {
        return $user->can('force delete directories');
    }

    public function viewSubdirectories(User $user, Directory $directory): bool
    {
        return $user->can('read directories');
    }

    public function viewParent(User $user, Directory $directory): bool
    {
        return $user->can('read directories');
    }

    public function viewMedias(User $user, Directory $directory): bool
    {
        return $user->can('read directories');
    }
}
