<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
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
    public function viewAny(User $user): Response
    {
        return $user->hasPermissionTo('read users')
            ? Response::allow()
            : Response::deny('No tienes permiso para listar usuarios');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): Response
    {
        return $user->hasPermissionTo('read users')
            ? Response::allow()
            : Response::deny('No tienes permiso para listar usuarios');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // return $user->hasPermissionTo('create users')
        //     ? Response::allow()
        //     : Response::deny('No tienes permiso para crear usuarios');
        return $user->can('create users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // return $user->hasPermissionTo('update users')
        //     ? Response::allow()
        //     : Response::deny('No tienes permiso para actualizar usuarios');
        return $user->can('update users');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // return $user->hasPermissionTo('delete users')
        //     ? Response::allow()
        //     : Response::deny('No tienes permiso para eliminar usuarios');
        return $user->can('delete users');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        // return $user->hasPermissionTo('restore users')
        //     ? Response::allow()
        //     : Response::deny('No tienes permiso para restaurar usuarios');
        return $user->can('restore users');

    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // return $user->hasPermissionTo('force delete users')
        //     ? Response::allow()
        //     : Response::deny('No tienes permiso para eliminar permanentemente usuarios');
        return $user->can('force delete users');
    }
}
