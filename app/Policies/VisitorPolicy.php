<?php

namespace App\Policies;

use App\Models\Visitor;
use App\Models\User;

class VisitorPolicy
{
    /**
     * Determine if the user can view any visitors
     */
    public function viewAny(User $user): bool
    {
        return true; // All users can access visitors page (filtered in controller)
    }

    /**
     * Determine if the user can view the visitor
     */
    public function view(User $user, Visitor $visitor): bool
    {
        // Admins and chairmen can view all
        if ($user->canManageSociety() || $user->isBlockChairman()) {
            return true;
        }
        
        // Users can view their own visitor entries
        return $visitor->user_id === $user->id;
    }

    /**
     * Determine if the user can create visitors
     */
    public function create(User $user): bool
    {
        // All users with a flat can register visitors
        return $user->flat_id !== null;
    }

    /**
     * Determine if the user can manage visitor check-in/out
     */
    public function manage(User $user, Visitor $visitor): bool
    {
        // Only admins and security can check in/out
        return $user->canManageSociety();
    }

    /**
     * Determine if the user can view security dashboard
     */
    public function viewSecurity(User $user): bool
    {
        // Only admins can view security dashboard
        return $user->canManageSociety();
    }

    /**
     * Determine if the user can delete the visitor
     */
    public function delete(User $user, Visitor $visitor): bool
    {
        // Users can delete their own entries if not checked in
        if ($visitor->user_id === $user->id && $visitor->status !== 'checked_in') {
            return true;
        }
        
        // Admins can delete any
        return $user->isAdmin();
    }
}