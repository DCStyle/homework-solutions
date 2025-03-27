<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WikiAnswer;
use Illuminate\Auth\Access\HandlesAuthorization;

class WikiAnswerPolicy
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
        return true; // Anyone can view answers
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User|null  $user
     * @param  \App\Models\WikiAnswer  $answer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(?User $user, WikiAnswer $answer)
    {
        return true; // Anyone can view individual answers
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true; // Any authenticated user can create answers
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiAnswer  $answer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, WikiAnswer $answer)
    {
        // Users can only update their own non-AI answers
        return $user->id === $answer->user_id && !$answer->is_ai;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiAnswer  $answer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, WikiAnswer $answer)
    {
        // Users can only delete their own answers, administrators can delete any answer
        // AI answers can only be deleted by administrators
        if ($answer->is_ai) {
            return $user->is_admin;
        }

        return $user->id === $answer->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can approve the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiAnswer  $answer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function approve(User $user, WikiAnswer $answer)
    {
        // Only administrators can approve answers
        return $user->is_admin;
    }

    /**
     * Determine whether the user can vote on the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiAnswer  $answer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function vote(User $user, WikiAnswer $answer)
    {
        // Users can vote on any answer except their own
        return $user->id !== $answer->user_id;
    }

    /**
     * Determine whether the user can mark an answer as accepted.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiAnswer  $answer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function accept(User $user, WikiAnswer $answer)
    {
        // Only the question author or administrators can mark an answer as accepted
        $questionAuthorId = $answer->question->user_id ?? null;
        return $user->id === $questionAuthorId || $user->is_admin;
    }

    /**
     * Determine whether the user can report the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiAnswer  $answer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function report(User $user, WikiAnswer $answer)
    {
        // Users can report any answer except their own
        // AI answers can be reported by anyone
        if ($answer->is_ai) {
            return true;
        }

        return $user->id !== $answer->user_id;
    }

    /**
     * Determine whether the user can restore a soft-deleted answer.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiAnswer  $answer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, WikiAnswer $answer)
    {
        // Only administrators can restore deleted answers
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiAnswer  $answer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, WikiAnswer $answer)
    {
        // Only administrators can permanently delete answers
        return $user->is_admin;
    }
}
