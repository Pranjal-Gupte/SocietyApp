<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintUpdate;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ComplaintController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of complaints
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Complaint::with(['user', 'flat.block', 'assignedTo']);
        
        // Regular users only see their own complaints
        if (!$user->canManageSociety() && !$user->isBlockChairman()) {
            $query->where('user_id', $user->id);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        // Filter by category
        if ($request->has('category') && $request->category != 'all') {
            $query->where('category', $request->category);
        }
        
        // Filter by priority
        if ($request->has('priority') && $request->priority != 'all') {
            $query->where('priority', $request->priority);
        }
        
        $complaints = $query->latest()->paginate(15);
        
        return view('complaints.index', compact('complaints'));
    }

    /**
     * Show the form for creating a new complaint
     */
    public function create()
    {
        $categories = Complaint::categories();
        return view('complaints.create', compact('categories'));
    }

    /**
     * Store a newly created complaint
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'location' => 'nullable|string|max:255',
        ]);
        
        $user = auth()->user();
        
        if (!$user->flat_id) {
            return back()->with('error', 'You must be assigned to a flat to register a complaint.');
        }
        
        $validated['user_id'] = $user->id;
        $validated['flat_id'] = $user->flat_id;
        $validated['status'] = 'pending';
        
        $complaint = Complaint::create($validated);
        
        // Create initial update
        ComplaintUpdate::create([
            'complaint_id' => $complaint->id,
            'user_id' => $user->id,
            'message' => 'Complaint registered',
        ]);
        
        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Complaint registered successfully! Your complaint number is: ' . $complaint->complaint_number);
    }

    /**
     * Display the specified complaint
     */
    public function show(Complaint $complaint)
    {
        $user = auth()->user();
        
        // Check if user has permission to view this complaint
        if (!$user->canManageSociety() && !$user->isBlockChairman() && $complaint->user_id != $user->id) {
            abort(403, 'You do not have permission to view this complaint.');
        }
        
        $complaint->load(['user', 'flat.block', 'assignedTo', 'updates.user']);
        
        return view('complaints.show', compact('complaint'));
    }

    /**
     * Update complaint status
     */
    public function updateStatus(Request $request, Complaint $complaint)
    {
        $this->authorize('update', $complaint);
        
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,on_hold,resolved,closed',
            'message' => 'nullable|string',
            'admin_notes' => 'nullable|string',
            'resolution_notes' => 'nullable|string',
        ]);
        
        $oldStatus = $complaint->status;
        $complaint->status = $validated['status'];
        
        if ($validated['status'] === 'resolved' && !$complaint->resolved_at) {
            $complaint->resolved_at = now();
        }
        
        if ($validated['status'] === 'closed' && !$complaint->closed_at) {
            $complaint->closed_at = now();
        }
        
        if (isset($validated['admin_notes'])) {
            $complaint->admin_notes = $validated['admin_notes'];
        }
        
        if (isset($validated['resolution_notes'])) {
            $complaint->resolution_notes = $validated['resolution_notes'];
        }
        
        $complaint->save();
        
        // Create status update
        ComplaintUpdate::create([
            'complaint_id' => $complaint->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'] ?? 'Status updated',
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
        ]);
        
        return back()->with('success', 'Complaint status updated successfully!');
    }

    /**
     * Add a comment/update to complaint
     */
    public function addUpdate(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);
        
        ComplaintUpdate::create([
            'complaint_id' => $complaint->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ]);
        
        return back()->with('success', 'Update added successfully!');
    }
}