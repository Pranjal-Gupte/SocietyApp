<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine if the user can view any payments
     */
    public function viewAny(User $user): bool
    {
        return true; // All users can access payments page (filtered in controller)
    }

    /**
     * Determine if the user can view the payment
     */
    public function view(User $user, Payment $payment): bool
    {
        // Admins and chairmen can view all
        if ($user->canManageSociety() || $user->isBlockChairman()) {
            return true;
        }
        
        // Users can view their own payments
        return $payment->user_id === $user->id;
    }

    /**
     * Determine if the user can create payments
     */
    public function create(User $user): bool
    {
        // Only admins and chairmen can create bills
        return $user->canManageSociety();
    }

    /**
     * Determine if the user can update the payment
     */
    public function update(User $user, Payment $payment): bool
    {
        // Only admins and chairmen can update/record payments
        return $user->canManageSociety();
    }

    /**
     * Determine if the user can delete the payment
     */
    public function delete(User $user, Payment $payment): bool
    {
        // Only admins can delete payments
        return $user->isAdmin();
    }
}