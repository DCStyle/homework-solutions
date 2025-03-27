<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WikiQuestion;
use Illuminate\Auth\Access\HandlesAuthorization;

class WikiQuestionPolicy
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
        return true; // Anyone can view questions (list view)
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User|null  $user
     * @param  \App\Models\WikiQuestion  $question
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(?User $user, WikiQuestion $question)
    {
        // Anyone can view published questions
        if ($question->status === 'published') {
            return true;
        }

        // Users can view their own pending or rejected questions
        if ($user && $user->id === $question->user_id) {
            return true;
        }

        // Admins can view any question
        if ($user && $user->is_admin) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true; // Any authenticated user can create questions
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiQuestion  $question
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, WikiQuestion $question)
    {
        // Only the author can update their own question
        if ($user->id === $question->user_id) {
            // Only allow updates if the question is pending or rejected
            return in_array($question->status, ['pending', 'rejected']);
        }

        // Admins can update any question
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiQuestion  $question
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, WikiQuestion $question)
    {
        // Users can only delete their own questions that are pending or rejected
        if ($user->id === $question->user_id) {
            return in_array($question->status, ['pending', 'rejected']);
        }

        // Admins can delete any question
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiQuestion  $question
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, WikiQuestion $question)
    {
        // Only admins can restore questions
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiQuestion  $question
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, WikiQuestion $question)
    {
        // Only admins can permanently delete questions
        return $user->is_admin;
    }

    /**
     * Determine whether the user can publish the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiQuestion  $question
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function publish(User $user, WikiQuestion $question)
    {
        // Only admins can publish questions
        return $user->is_admin;
    }

    /**
     * Determine whether the user can reject the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiQuestion  $question
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function reject(User $user, WikiQuestion $question)
    {
        // Only admins can reject questions
        return $user->is_admin;
    }

    /**
     * Determine whether the user can add answers to the question.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiQuestion  $question
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function answer(User $user, WikiQuestion $question)
    {
        // Users can answer any published question except their own
        if ($question->status !== 'published') {
            return false;
        }

        // Optionally disallow users from answering their own questions
        // return $user->id !== $question->user_id;

        return true;
    }

    /**
     * Determine whether the user can add comments to the question.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\WikiQuestion  $question
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function comment(User $user, WikiQuestion $question)
    {
        // Users can comment on any published question
        return $question->status === 'published';
    }
}
