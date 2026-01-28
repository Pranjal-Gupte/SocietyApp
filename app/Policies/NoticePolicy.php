<?php

namespace App\Policies;

use App\Models\Notice;
use App\Models\User;

class NoticePolicy
{
    /**
     * Determine if the user can create notices
     */
    public function create(User $user): bool
    {
        return $user->canManageSociety() || $user->isBlockChairman();
    }

    /**
     * Determine if the user can update the notice
     */
    public function update(User $user, Notice $notice): bool
    {
        // Admin and Chairman can update any notice
        if ($user->canManageSociety()) {
            return true;
        }
        
        // Block chairman can update their own notices
        if ($user->isBlockChairman() && $notice->author_id === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine if the user can delete the notice
     */
    public function delete(User $user, Notice $notice): bool
    {
        return $user->canManageSociety() || 
               ($user->isBlockChairman() && $notice->author_id === $user->id);
    }
}