<?php

namespace App\Policies;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PlanPolicy
{
    const UPDATE_PLANS_PERMISSION = 'update plans';

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
    public function view(User $user, Plan $plan): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create plans');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Plan $plan): bool
    {
        return $user->can(self::UPDATE_PLANS_PERMISSION);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Plan $plan): bool
    {
        return $user->can('delete plans');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Plan $plan): bool
    {
        return $user->can('restore plans');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Plan $plan): bool
    {
        return $user->can('force delete plans');
    }

    /* -------------------------------------------------------------------------- */
    /*                       Policies for Goal relationship                       */
    /* -------------------------------------------------------------------------- */

    /**
     * Determine whether the user can view the plan's goal.
     */
    public function viewGoal(User $user, Plan $plan): bool
    {
        return $user->can('read goals');
    }

    /**
     * Determine whether the user can attach plans to the model's goal relationship.
     */
    public function attachGoal(User $user, Plan $plan): bool
    {
        return $user->can(self::UPDATE_PLANS_PERMISSION);
    }

    /* -------------------------------------------------------------------------- */
    /*                     Policies for Frequency relationship                    */
    /* -------------------------------------------------------------------------- */

    /**
     * Determine whether the user can view the plan's frequency.
     */
    public function viewFrequency(User $user, Plan $plan): bool
    {
        return $user->can('read frequencies');
    }

    /**
     * Determine whether the user can attach plans to the model's frequency relationship.
     */
    public function attachFrequency(User $user, Plan $plan): bool
    {
        return $user->can(self::UPDATE_PLANS_PERMISSION);
    }

    /* -------------------------------------------------------------------------- */
    /*                Policies for PhysicalCondition relationship                 */
    /* -------------------------------------------------------------------------- */

    /**
     * Determine whether the user can view the plan's physicalCondition.
     */
    public function viewPhysicalCondition(User $user, Plan $plan): bool
    {
        return $user->can('read physical conditions');
    }

    /**
     * Determine whether the user can attach plans to the model's physicalCondition relationship.
     */
    public function attachPhysicalCondition(User $user, Plan $plan): bool
    {
        return $user->can(self::UPDATE_PLANS_PERMISSION);
    }
}
