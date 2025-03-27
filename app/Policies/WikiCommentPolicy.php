<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WikiComment;
use Illuminate\Auth\Access\HandlesAuthorization;

class WikiCommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User|null  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(?User $user)
    {
        return true; // Anyone can view comments
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User|null  $user
     * @param  \App\Models\WikiComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(?User $user, WikiComment $comment)
    {
        return true; // Anyone can view individual comments
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true; // Any authenticated user can create comments
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, WikiComment $comment)
    {
        // Users can only update their own comments
        return $user->id === $comment->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, WikiComment $comment)
    {
        // Users can only delete their own comments, administrators can delete any comment
        return $user->id === $comment->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can reply to the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function reply(User $user, WikiComment $comment)
    {
        // Anyone can reply to comments
        return true;
    }

    /**
     * Determine whether the user can report the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function report(User $user, WikiComment $comment)
    {
        // Users can report any comment except their own
        return $user->id !== $comment->user_id;
    }

    /**
     * Determine whether the user can moderate comments.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function moderate(User $user)
    {
        // Only administrators can moderate comments
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore a soft-deleted comment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, WikiComment $comment)
    {
        // Only administrators can restore deleted comments
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiComment  $comment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, WikiComment $comment)
    {
        // Only administrators can permanently delete comments
        return $user->is_admin;
    }
}
