<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Block;
use App\Models\Flat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MemberController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of members
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        
        $query = User::with(['role', 'flat.block']);
        
        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Filter by role
        if ($request->has('role') && $request->role != 'all') {
            $query->where('role_id', $request->role);
        }
        
        // Filter by block
        if ($request->has('block') && $request->block != 'all') {
            $query->whereHas('flat', function($q) use ($request) {
                $q->where('block_id', $request->block);
            });
        }
        
        // Filter by resident type
        if ($request->has('resident_type') && $request->resident_type != 'all') {
            $query->where('resident_type', $request->resident_type);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('is_active', $request->status === 'active');
        }
        
        $members = $query->latest()->paginate(20);
        
        // Get data for filters
        $roles = Role::all();
        $blocks = Block::all();
        
        return view('members.index', compact('members', 'roles', 'blocks'));
    }

    /**
     * Show the form for creating a new member
     */
    public function create()
    {
        $this->authorize('create', User::class);
        
        $roles = Role::all();
        $blocks = Block::all();
        $flats = Flat::whereNull('owner_id')->orWhere('status', 'vacant')->get();
        
        return view('members.create', compact('roles', 'blocks', 'flats'));
    }

    /**
     * Store a newly created member
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:15',
            'role_id' => 'required|exists:roles,id',
            'flat_id' => 'nullable|exists:flats,id',
            'resident_type' => 'required|in:owner,tenant,family_member',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'move_in_date' => 'nullable|date',
            'address' => 'nullable|string',
        ]);
        
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;
        
        $member = User::create($validated);
        
        // If flat is assigned and user is owner, update flat's owner_id
        if ($member->flat_id && $member->resident_type === 'owner') {
            $flat = Flat::find($member->flat_id);
            $flat->owner_id = $member->id;
            $flat->status = 'occupied';
            $flat->save();
        }
        
        return redirect()->route('members.index')
            ->with('success', 'Member added successfully!');
    }

    /**
     * Display the specified member
     */
    public function show(User $member)
    {
        $this->authorize('view', $member);
        
        $member->load(['role', 'flat.block', 'complaints', 'notices']);
        
        return view('members.show', compact('member'));
    }

    /**
     * Show the form for editing the member
     */
    public function edit(User $member)
    {
        $this->authorize('update', $member);
        
        $roles = Role::all();
        $blocks = Block::all();
        $flats = Flat::where(function($query) use ($member) {
            $query->whereNull('owner_id')
                  ->orWhere('status', 'vacant')
                  ->orWhere('id', $member->flat_id);
        })->get();
        
        return view('members.edit', compact('member', 'roles', 'blocks', 'flats'));
    }

    /**
     * Update the specified member
     */
    public function update(Request $request, User $member)
    {
        $this->authorize('update', $member);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $member->id,
            'phone' => 'nullable|string|max:15',
            'role_id' => 'required|exists:roles,id',
            'flat_id' => 'nullable|exists:flats,id',
            'resident_type' => 'required|in:owner,tenant,family_member',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'move_in_date' => 'nullable|date',
            'address' => 'nullable|string',
        ]);

        // Explicitly set the boolean based on the request
        $validated['is_active'] = $request->boolean('is_active');
        
        
        // Handle password update if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $validated['password'] = Hash::make($request->password);
        }
        
        $oldFlatId = $member->flat_id;
        $member->update($validated);
        
        // Update flat ownership if changed
        if ($oldFlatId != $member->flat_id) {
            // Clear old flat ownership
            if ($oldFlatId && $member->resident_type === 'owner') {
                $oldFlat = Flat::find($oldFlatId);
                if ($oldFlat && $oldFlat->owner_id === $member->id) {
                    $oldFlat->owner_id = null;
                    $oldFlat->status = 'vacant';
                    $oldFlat->save();
                }
            }
            
            // Set new flat ownership
            if ($member->flat_id && $member->resident_type === 'owner') {
                $newFlat = Flat::find($member->flat_id);
                $newFlat->owner_id = $member->id;
                $newFlat->status = 'occupied';
                $newFlat->save();
            }
        }
        
        return redirect()->route('directory.index')
            ->with('success', 'Member updated successfully!');
    }

    /**
     * Remove the specified member
     */
    public function destroy(User $member)
    {
        $this->authorize('delete', $member);
        
        // Clear flat ownership if any
        if ($member->flat_id && $member->resident_type === 'owner') {
            $flat = Flat::find($member->flat_id);
            if ($flat && $flat->owner_id === $member->id) {
                $flat->owner_id = null;
                $flat->status = 'vacant';
                $flat->save();
            }
        }
        
        $member->delete();
        
        return redirect()->route('members.index')
            ->with('success', 'Member deleted successfully!');
    }

    /**
     * Toggle member active status
     */
    public function toggleStatus(User $member)
    {
        $this->authorize('update', $member);
        
        $member->is_active = !$member->is_active;
        $member->save();
        
        $status = $member->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Member {$status} successfully!");
    }
}