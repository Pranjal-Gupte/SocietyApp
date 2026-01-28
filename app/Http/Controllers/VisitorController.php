<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class VisitorController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of visitors
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Visitor::with(['user', 'flat.block', 'checkedInBy', 'checkedOutBy']);
        
        // Search by gate pass code or visitor name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('gate_pass_code', 'like', "%{$search}%")
                  ->orWhere('visitor_name', 'like', "%{$search}%")
                  ->orWhere('visitor_phone', 'like', "%{$search}%");
            });
        }
        
        // Regular users only see their own visitors
        if (!$user->canManageSociety() && !$user->isBlockChairman()) {
            $query->where('user_id', $user->id);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter by date
        if ($request->has('date')) {
            if ($request->date === 'today') {
                $query->today();
            } elseif ($request->date === 'week') {
                $query->whereBetween('expected_date', [now()->startOfWeek(), now()->endOfWeek()]);
            }
        }
        
        // Filter by visitor type
        if ($request->has('type') && $request->type != 'all') {
            $query->where('visitor_type', $request->type);
        }
        
        $visitors = $query->latest('expected_date')->latest('id')->paginate(20);
        
        // Get stats for dashboard
        $stats = [
            'today_expected' => Visitor::today()->expected()->count(),
            'currently_inside' => Visitor::inside()->count(),
            'today_total' => Visitor::today()->count(),
        ];
        
        return view('visitors.index', compact('visitors', 'stats'));
    }

    /**
     * Show the form for creating a new visitor
     */
    public function create()
    {
        return view('visitors.create');
    }

    /**
     * Store a newly created visitor
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'visitor_name' => 'required|string|max:255',
            'visitor_phone' => 'required|string|max:15',
            'visitor_email' => 'nullable|email',
            'visitor_type' => 'required|in:guest,delivery,service,cab,family,other',
            'purpose' => 'nullable|string|max:255',
            'number_of_persons' => 'required|integer|min:1|max:20',
            'vehicle_number' => 'nullable|string|max:20',
            'vehicle_type' => 'required|in:two_wheeler,four_wheeler,none',
            'expected_date' => 'required|date|after_or_equal:today',
            'expected_time' => 'nullable',
            'is_frequent_visitor' => 'boolean',
        ]);
        
        $user = auth()->user();
        
        if (!$user->flat_id) {
            return back()->with('error', 'You must be assigned to a flat to register visitors.');
        }
        
        $validated['user_id'] = $user->id;
        $validated['flat_id'] = $user->flat_id;
        $validated['status'] = 'approved';
        
        $visitor = Visitor::create($validated);
        
        return redirect()->route('visitors.show', $visitor)
            ->with('success', 'Visitor registered successfully! Gate Pass Code: ' . $visitor->gate_pass_code);
    }

    /**
     * Display the specified visitor
     */
    public function show(Visitor $visitor)
    {
        $user = auth()->user();
        
        // Check permission
        if (!$user->canManageSociety() && !$user->isBlockChairman() && $visitor->user_id != $user->id) {
            abort(403, 'You do not have permission to view this visitor.');
        }
        
        $visitor->load(['user', 'flat.block', 'checkedInBy', 'checkedOutBy']);
        
        return view('visitors.show', compact('visitor'));
    }

    /**
     * Check in a visitor
     */
    public function checkIn(Request $request, Visitor $visitor)
    {
        $this->authorize('manage', $visitor);
        
        if ($visitor->status !== 'approved') {
            return back()->with('error', 'Visitor cannot be checked in. Current status: ' . $visitor->status);
        }
        
        $visitor->status = 'checked_in';
        $visitor->check_in_time = now();
        $visitor->checked_in_by = auth()->id();
        
        if ($request->filled('notes')) {
            $visitor->notes = $request->notes;
        }
        
        $visitor->save();
        
        return back()->with('success', 'Visitor checked in successfully!');
    }

    /**
     * Check out a visitor
     */
    public function checkOut(Request $request, Visitor $visitor)
    {
        $this->authorize('manage', $visitor);
        
        if ($visitor->status !== 'checked_in') {
            return back()->with('error', 'Visitor is not checked in.');
        }
        
        $visitor->status = 'checked_out';
        $visitor->check_out_time = now();
        $visitor->checked_out_by = auth()->id();
        
        if ($request->filled('notes')) {
            $visitor->notes .= "\n" . $request->notes;
        }
        
        $visitor->save();
        
        return back()->with('success', 'Visitor checked out successfully!');
    }

    /**
     * Cancel a visitor entry
     */
    public function cancel(Visitor $visitor)
    {
        $user = auth()->user();
        
        if ($visitor->user_id != $user->id && !$user->canManageSociety()) {
            abort(403, 'You can only cancel your own visitor entries.');
        }
        
        if ($visitor->status === 'checked_in') {
            return back()->with('error', 'Cannot cancel. Visitor is already inside.');
        }
        
        $visitor->status = 'cancelled';
        $visitor->save();
        
        return back()->with('success', 'Visitor entry cancelled.');
    }

    /**
     * Security dashboard - Today's expected visitors
     */
    public function security()
    {
        $this->authorize('viewSecurity', Visitor::class);
        
        $pendingApprovals = Visitor::with(['user', 'flat.block'])
            ->pendingApproval()
            ->latest()
            ->get();
        
        $expectedVisitors = Visitor::with(['user', 'flat.block'])
            ->today()
            ->expected()
            ->orderBy('expected_time')
            ->get();
        
        $insideVisitors = Visitor::with(['user', 'flat.block', 'checkedInBy'])
            ->inside()
            ->latest('check_in_time')
            ->get();
        
        $stats = [
            'pending_count' => $pendingApprovals->count(),
            'expected_count' => $expectedVisitors->count(),
            'inside_count' => $insideVisitors->count(),
            'checked_out_today' => Visitor::today()->where('status', 'checked_out')->count(),
        ];
        
        return view('visitors.security', compact('pendingApprovals', 'expectedVisitors', 'insideVisitors', 'stats'));
    }

    /**
     * Show walk-in registration form
     */
    public function walkinCreate()
    {
        $this->authorize('viewSecurity', Visitor::class);
        
        return view('visitors.walkin');
    }

    /**
     * Store walk-in visitor
     */
    public function walkinStore(Request $request)
    {
        $this->authorize('viewSecurity', Visitor::class);
        
        $validated = $request->validate([
            'visitor_name' => 'required|string|max:255',
            'visitor_phone' => 'required|string|max:15',
            'flat_id' => 'required|exists:flats,id',
            'visitor_type' => 'required|in:guest,delivery,service,cab,family,other',
            'purpose' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:20',
        ]);
        
        // Get the flat and its owner
        $flat = \App\Models\Flat::find($validated['flat_id']);
        
        if (!$flat->owner_id) {
            return back()->with('error', 'This flat has no registered owner. Cannot register visitor.');
        }
        
        // Create visitor entry
        $validated['user_id'] = $flat->owner_id; // Assign to flat owner
        $validated['expected_date'] = today();
        $validated['expected_time'] = now();
        $validated['number_of_persons'] = 1;
        $validated['vehicle_type'] = $validated['vehicle_number'] ? 'two_wheeler' : 'none';
        
        // Set as pending approval (resident must approve)
        $validated['status'] = 'pending_approval';
        
        $visitor = Visitor::create($validated);
        
        return redirect()->route('visitors.security')
            ->with('success', 'Walk-in visitor registered! Waiting for resident approval. Gate Pass: ' . $visitor->gate_pass_code);
    }

    /**
     * Approve visitor entry (by resident)
     */
    public function approve(Visitor $visitor)
    {
        $user = auth()->user();
        
        // Check if this visitor is for user's flat
        if ($visitor->user_id != $user->id && !$user->canManageSociety()) {
            abort(403, 'You can only approve visitors for your own flat.');
        }
        
        if ($visitor->status !== 'pending_approval') {
            return back()->with('error', 'This visitor is not pending approval.');
        }
        
        $visitor->status = 'approved';
        $visitor->save();
        
        return back()->with('success', 'Visitor approved! Security can now check them in.');
    }

    /**
     * Reject visitor entry (by resident)
     */
    public function reject(Request $request, Visitor $visitor)
    {
        $user = auth()->user();
        
        // Check if this visitor is for user's flat
        if ($visitor->user_id != $user->id && !$user->canManageSociety()) {
            abort(403, 'You can only reject visitors for your own flat.');
        }
        
        if ($visitor->status !== 'pending_approval') {
            return back()->with('error', 'This visitor is not pending approval.');
        }
        
        $visitor->status = 'rejected';
        if ($request->filled('rejection_reason')) {
            $visitor->notes = 'Rejected by resident: ' . $request->rejection_reason;
        }
        $visitor->save();
        
        return back()->with('success', 'Visitor entry rejected.');
    }
}