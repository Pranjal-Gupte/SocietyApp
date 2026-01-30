<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->canManageSociety() || $user->isBlockChairman();
        
        // Get user's block (if they have a flat)
        $blockId = $user->flat ? $user->flat->block_id : null;
        
        // Get the actual Notice list for the feed
        $recentNotices = Notice::published()
            ->when($blockId && !$isAdmin, function($query) use ($blockId) {
                return $query->forBlock($blockId);
            })
            ->latest('published_at')
            ->take(5)
            ->get();
        
        // Count "New" notices (Published in the last 7 days)
        $newNotices = Notice::published()
            ->when($blockId && !$isAdmin, function($query) use ($blockId) {
                return $query->forBlock($blockId);
            })
            ->where('published_at', '>=', now()->subDays(7))
            ->count();
        
        // Get active complaints count
        $activeStatuses = ['pending', 'open', 'in_progress'];

        if ($user->canManageSociety() || $user->isBlockChairman()) {
            // Admin sees everything
            $activeComplaints = \App\Models\Complaint::whereIn('status', $activeStatuses)->count();
        } else {
            // Resident sees only theirs
            $activeComplaints = $user->complaints()
                ->whereIn('status', $activeStatuses)
                ->count();
        }
        
        // Calculate Pending Payments Total
        if ($isAdmin) {
            // Total money owed to the society by everyone
            $pendingPayments = \App\Models\Payment::whereIn('status', ['pending', 'partial'])
                ->sum('due_amount');
        } else {
            // Total money owed by this specific resident
            $pendingPayments = \App\Models\Payment::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'partial'])
                ->sum('due_amount');
        }

        // Fetch Upcoming Bills
        $upcomingBills = \App\Models\Payment::whereIn('status', ['pending', 'partial'])
            ->when(!$isAdmin, function($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();
        
        return view('dashboard', compact(
            'recentNotices',
            'newNotices',
            'activeComplaints',
            'upcomingBills',
            'pendingPayments'
        ));
    }
}