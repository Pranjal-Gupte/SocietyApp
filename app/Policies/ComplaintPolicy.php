<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    /**
     * Determine if the user can view any complaints
     */
    public function viewAny(User $user): bool
    {
        return true; // All users can access the complaints page
    }

    /**
     * Determine if the user can view the complaint
     */
    public function view(User $user, Complaint $complaint): bool
    {
        // Admins and chairmen can view all complaints
        if ($user->canManageSociety() || $user->isBlockChairman()) {
            return true;
        }
        
        // Users can view their own complaints
        return $complaint->user_id === $user->id;
    }

    /**
     * Determine if the user can create complaints
     */
    public function create(User $user): bool
    {
        // All users can create complaints if they have a flat
        return $user->flat_id !== null;
    }

    /**
     * Determine if the user can update the complaint
     */
    public function update(User $user, Complaint $complaint): bool
    {
        // Only admins and chairmen can update complaints
        return $user->canManageSociety() || $user->isBlockChairman();
    }

    /**
     * Determine if the user can delete the complaint
     */
    public function delete(User $user, Complaint $complaint): bool
    {
        // Only admins can delete complaints
        return $user->isAdmin();
    }
}