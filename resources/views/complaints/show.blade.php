@extends('layouts.app')

@section('title', 'Complaint Details')

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Back Button --}}
    <div class="mb-6">
        <a href="{{ route('complaints.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Complaints
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Complaint Details Card --}}
            <div class="bg-white rounded-lg shadow-lg">
                <div class="p-6 border-b">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <h1 class="text-2xl font-bold text-gray-800">{{ $complaint->subject }}</h1>
                                <span class="px-3 py-1 text-sm rounded-full bg-{{ $complaint->statusColor }}-100 text-{{ $complaint->statusColor }}-700 font-medium">
                                    {{ ucwords(str_replace('_', ' ', $complaint->status)) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">
                                Complaint #{{ $complaint->complaint_number }}
                            </p>
                        </div>
                        <span class="px-3 py-1 text-sm rounded-full bg-{{ $complaint->priorityColor }}-100 text-{{ $complaint->priorityColor }}-700 font-medium">
                            {{ ucfirst($complaint->priority) }} Priority
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Submitted by</p>
                            <p class="font-medium text-gray-800">{{ $complaint->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Flat Number</p>
                            <p class="font-medium text-gray-800">{{ $complaint->flat->full_number }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Category</p>
                            <p class="font-medium text-gray-800">{{ App\Models\Complaint::categories()[$complaint->category] ?? ucfirst($complaint->category) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Submitted On</p>
                            <p class="font-medium text-gray-800">{{ $complaint->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        @if($complaint->location)
                            <div class="col-span-2">
                                <p class="text-gray-500">Location</p>
                                <p class="font-medium text-gray-800">{{ $complaint->location }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-6">
                    <h3 class="font-semibold text-gray-800 mb-3">Description</h3>
                    <p class="text-gray-700 whitespace-pre-line">{{ $complaint->description }}</p>
                </div>

                @if($complaint->admin_notes && (auth()->user()->canManageSociety() || auth()->user()->isBlockChairman()))
                    <div class="p-6 border-t bg-yellow-50">
                        <h3 class="font-semibold text-gray-800 mb-2">Admin Notes (Private)</h3>
                        <p class="text-gray-700">{{ $complaint->admin_notes }}</p>
                    </div>
                @endif

                @if($complaint->resolution_notes)
                    <div class="p-6 border-t bg-green-50">
                        <h3 class="font-semibold text-gray-800 mb-2">Resolution Notes</h3>
                        <p class="text-gray-700">{{ $complaint->resolution_notes }}</p>
                    </div>
                @endif
            </div>

            {{-- Updates Timeline --}}
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Activity Timeline</h2>
                
                <div class="space-y-6">
                    @forelse($complaint->updates as $update)
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-bold text-sm">{{ substr($update->user->name, 0, 1) }}</span>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-start justify-between mb-2">
                                        <p class="font-medium text-gray-800">{{ $update->user->name }}</p>
                                        <span class="text-xs text-gray-500">{{ $update->created_at->diffForHumans() }}</span>
                                    </div>
                                    
                                    @if($update->isStatusChange())
                                        <div class="mb-2 text-sm">
                                            <span class="text-gray-600">Changed status from</span>
                                            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs">{{ ucwords(str_replace('_', ' ', $update->old_status)) }}</span>
                                            <span class="text-gray-600">to</span>
                                            <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-xs">{{ ucwords(str_replace('_', ' ', $update->new_status)) }}</span>
                                        </div>
                                    @endif
                                    
                                    <p class="text-gray-700">{{ $update->message }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-gray-500 py-8">No updates yet</p>
                    @endforelse
                </div>

                {{-- Add Update Form --}}
                <div class="mt-6 pt-6 border-t">
                    <form action="{{ route('complaints.add-update', $complaint) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Add Update</label>
                            <textarea name="message" rows="3" required
                                placeholder="Add a comment or update..."
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Post Update
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Quick Info --}}
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Quick Info</h3>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Created</span>
                        <span class="font-medium text-gray-800">{{ $complaint->created_at->diffForHumans() }}</span>
                    </div>
                    
                    @if($complaint->resolved_at)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Resolved</span>
                            <span class="font-medium text-gray-800">{{ $complaint->resolved_at->diffForHumans() }}</span>
                        </div>
                    @endif
                    
                    @if($complaint->assignedTo)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Assigned To</span>
                            <span class="font-medium text-gray-800">{{ $complaint->assignedTo->name }}</span>
                        </div>
                    @endif
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Updates</span>
                        <span class="font-medium text-gray-800">{{ $complaint->updates->count() }}</span>
                    </div>
                </div>
            </div>

            {{-- Change Status (Admin/Chairman Only) --}}
            @can('update', $complaint)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Update Status</h3>
                    
                    <form action="{{ route('complaints.update-status', $complaint) }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="pending" {{ $complaint->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $complaint->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="on_hold" {{ $complaint->status == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                <option value="resolved" {{ $complaint->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $complaint->status == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Message (Optional)</label>
                            <textarea name="message" rows="3" 
                                placeholder="Add a note about this status change..."
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Admin Notes (Optional)</label>
                            <textarea name="admin_notes" rows="2" 
                                placeholder="Internal notes..."
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">{{ $complaint->admin_notes }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Resolution Notes (Optional)</label>
                            <textarea name="resolution_notes" rows="2" 
                                placeholder="How was this resolved..."
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">{{ $complaint->resolution_notes }}</textarea>
                        </div>

                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Update Status
                        </button>
                    </form>
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection