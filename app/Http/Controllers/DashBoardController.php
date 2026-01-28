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
        
        // Get user's block (if they have a flat)
        $blockId = $user->flat ? $user->flat->block_id : null;
        
        // Get recent notices for user's block or all blocks
        $recentNotices = Notice::published()
            ->when($blockId, function($query) use ($blockId) {
                return $query->forBlock($blockId);
            })
            ->latest('published_at')
            ->take(5)
            ->get();
        
        // Count new notices from last 7 days
        $newNotices = Notice::published()
            ->when($blockId, function($query) use ($blockId) {
                return $query->forBlock($blockId);
            })
            ->where('published_at', '>=', now()->subDays(7))
            ->count();
        
        // Get active complaints count (real data now!)
        if ($user->canManageSociety() || $user->isBlockChairman()) {
            $activeComplaints = \App\Models\Complaint::active()->count();
        } else {
            $activeComplaints = $user->complaints()->active()->count();
        }
        
        // Dummy data for now (we'll implement these in Phase 3B)
        $pendingPayments = 3950; // Will be calculated from payments table later
        $upcomingBills = []; // Will fetch from bills table later
        
        return view('dashboard', compact(
            'recentNotices',
            'newNotices',
            'pendingPayments',
            'activeComplaints',
            'upcomingBills'
        ));
    }
}