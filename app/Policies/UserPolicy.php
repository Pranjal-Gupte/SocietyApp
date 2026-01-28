<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view any members
     */
    public function viewAny(User $user): bool
    {
        // Admins, chairmen, and block chairmen can view members
        return $user->canManageSociety() || $user->isBlockChairman();
    }

    /**
     * Determine if the user can view the member profile
     */
    public function view(User $user, User $member): bool
    {
        // Admins and chairmen can view all
        if ($user->canManageSociety() || $user->isBlockChairman()) {
            return true;
        }
        
        // Users can view their own profile
        return $user->id === $member->id;
    }

    /**
     * Determine if the user can create members
     */
    public function create(User $user): bool
    {
        // Only admins and chairmen can add members
        return $user->canManageSociety();
    }

    /**
     * Determine if the user can update the member
     */
    public function update(User $user, User $member): bool
    {
        // Admins and chairmen can update any member
        if ($user->canManageSociety()) {
            return true;
        }
        
        // Users can update their own profile
        return $user->id === $member->id;
    }

    /**
     * Determine if the user can delete the member
     */
    public function delete(User $user, User $member): bool
    {
        // Only admins can delete members
        // And cannot delete themselves
        return $user->isAdmin() && $user->id !== $member->id;
    }
}