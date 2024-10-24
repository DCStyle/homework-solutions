<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    // Ensure the user has the 'Create post' permission
    public function createPost(User $user)
    {
        // Check if the user has the 'Create post' permission via their roles
        return $user->roles->contains(function ($role) {
            return $role->permissions->contains('name', 'Create post');
        });
    }
}
