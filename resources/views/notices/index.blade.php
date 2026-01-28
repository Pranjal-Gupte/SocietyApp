@extends('layouts.app')

@section('title', 'Notices & Announcements')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Notices & Announcements</h1>
            <p class="text-gray-600 mt-1">Stay updated with society news and announcements</p>
        </div>
        @can('create', App\Models\Notice::class)
            <a href="{{ route('notices.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Notice
            </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" action="{{ route('notices.index') }}" class="flex flex-wrap gap-4">
            {{-- Priority Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select name="priority" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                    <option value="all" {{ request('priority') == 'all' ? 'selected' : '' }}>All Priorities</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>

            @if(auth()->user()->canManageSociety() && $blocks->count() > 0)
                {{-- Block Filter (for admins/chairmen) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Block</label>
                    <select name="block" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                        <option value="all" {{ request('block') == 'all' ? 'selected' : '' }}>All Blocks</option>
                        <option value="general" {{ request('block') == 'general' ? 'selected' : '' }}>General (All Blocks)</option>
                        @foreach($blocks as $block)
                            <option value="{{ $block->id }}" {{ request('block') == $block->id ? 'selected' : '' }}>
                                {{ $block->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if(request('priority') != 'all' || request('block') != 'all')
                <div class="flex items-end">
                    <a href="{{ route('notices.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition">
                        Clear Filters
                    </a>
                </div>
            @endif
        </form>
    </div>

    {{-- Notices List --}}
    <div class="space-y-4">
        @forelse($notices as $notice)
            <div class="bg-white rounded-lg shadow hover:shadow-md transition cursor-pointer" onclick="window.location='{{ route('notices.show', $notice->id) }}'">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3 flex-1">
                            {{-- Priority Indicator --}}
                            <div class="w-1 h-16 rounded {{ $notice->priority === 'high' ? 'bg-red-500' : ($notice->priority === 'medium' ? 'bg-yellow-500' : 'bg-gray-300') }}"></div>
                            
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-lg font-semibold text-gray-800">{{ $notice->title }}</h3>
                                    <span class="px-2 py-0.5 text-xs rounded-full {{ $notice->priority === 'high' ? 'bg-red-100 text-red-700' : ($notice->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') }}">
                                        {{ ucfirst($notice->priority) }}
                                    </span>
                                    @if($notice->block)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700">
                                            {{ $notice->block->name }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500">
                                    By {{ $notice->author->name }} • {{ $notice->published_at->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                        
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                    
                    <p class="text-gray-600 line-clamp-2 ml-7">{{ Str::limit($notice->content, 200) }}</p>
                    
                    @if($notice->valid_until)
                        <div class="mt-3 ml-7">
                            <span class="text-xs text-gray-500">
                                Valid until: {{ $notice->valid_until->format('M d, Y') }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <p class="text-gray-500 text-lg">No notices found</p>
                <p class="text-gray-400 text-sm mt-1">Check back later for updates</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($notices->hasPages())
        <div class="mt-6">
            {{ $notices->links() }}
        </div>
    @endif
</div>
@endsection