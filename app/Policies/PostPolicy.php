<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    /**
     * Determine whether the user have all permissions.
     *
     * @param User $user
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
        return $user->can('read posts');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        return $user->can('show posts');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create posts');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->can('update posts') && $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->can('delete posts') && $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->can('restore posts') && $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $user->can('force delete posts') && $user->id === $post->user_id;
    }

    /* -------------------------------------------------------------------------- */
    /*                       Policies for Post relationships                      */
    /* -------------------------------------------------------------------------- */

    /**
     * Determine whether the user can view the user of the post.
     */
    public function viewUser(User $user, Post $post): bool
    {
        return $user->can('read posts');
    }

    /* -------------------------------------------------------------------------- */
    /*                      Policies for medias relationships                     */
    /* -------------------------------------------------------------------------- */

    /**
     * Determine whether the user can view the post's medias.
     */
    public function viewMedias(User $user, Post $post): bool
    {
        return $user->can('read posts');
    }
}
