<?php

namespace App\Policies;

use App\Models\PhysicalCondition;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PhysicalConditionPolicy
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
    public function view(User $user, PhysicalCondition $physicalCondition): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create physical conditions');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PhysicalCondition $physicalCondition): bool
    {
        return $user->can('update physical conditions');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PhysicalCondition $physicalCondition): bool
    {
        return $user->can('delete physical conditions');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PhysicalCondition $physicalCondition): bool
    {
        return $user->can('restore physical conditions');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PhysicalCondition $physicalCondition): bool
    {
        return $user->can('force delete physical conditions');
    }

    /**
     * Determine whether the user can view physical condition's translations.
     */
    public function viewTranslations(User $user, PhysicalCondition $physicalCondition): bool
    {
        return $user->can('read physical conditions');
    }
}
