<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class NoticeController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of notices
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $blockId = $user->flat ? $user->flat->block_id : null;
        
        $query = Notice::with(['author', 'block'])
            ->published()
            ->when($blockId && !$user->canManageSociety(), function($q) use ($blockId) {
                return $q->forBlock($blockId);
            });
        
        // Filter by priority if specified
        if ($request->has('priority') && $request->priority != 'all') {
            $query->where('priority', $request->priority);
        }
        
        // Filter by block if specified (for admins/chairmen)
        if ($request->has('block') && $request->block != 'all' && $user->canManageSociety()) {
            if ($request->block == 'general') {
                $query->whereNull('block_id');
            } else {
                $query->where('block_id', $request->block);
            }
        }
        
        $notices = $query->latest('published_at')->paginate(10);
        
        // Get blocks for filter (if user can manage)
        $blocks = $user->canManageSociety() ? Block::all() : collect();
        
        return view('notices.index', compact('notices', 'blocks'));
    }

    /**
     * Display the specified notice
     */
    public function show(Notice $notice)
    {
        $user = auth()->user();
        
        // Check if user has access to this notice
        if ($notice->block_id && $user->flat) {
            if ($notice->block_id != $user->flat->block_id && !$user->canManageSociety()) {
                abort(403, 'You do not have access to this notice.');
            }
        }
        
        $notice->load(['author', 'block']);
        
        return view('notices.show', compact('notice'));
    }

    /**
     * Show the form for creating a new notice
     */
    public function create()
    {
        $this->authorize('create', Notice::class);
        
        $blocks = Block::all();
        
        return view('notices.create', compact('blocks'));
    }

    /**
     * Store a newly created notice
     */
    public function store(Request $request)
    {
        $this->authorize('create', Notice::class);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'block_id' => 'nullable|exists:blocks,id',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
        ]);
        
        $validated['author_id'] = auth()->id();
        $validated['status'] = 'published';
        $validated['published_at'] = now();
        
        $notice = Notice::create($validated);
        
        return redirect()->route('notices.index')
            ->with('success', 'Notice created successfully!');
    }
}