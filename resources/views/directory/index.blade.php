@extends('layouts.app')

@section('title', 'Member Directory')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Member Directory</h1>
        <p class="text-gray-600 mt-1">Connect with your neighbors and society members</p>
    </div>

    {{-- Search & Filter --}}
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" action="{{ route('directory.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Search by name or flat number..."
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <select name="block" class="px-4 py-2 border rounded-lg" onchange="this.form.submit()">
                    <option value="all">All Blocks</option>
                    @php
                        $blocks = \App\Models\Block::all();
                    @endphp
                    @foreach($blocks as $block)
                        <option value="{{ $block->id }}" {{ request('block') == $block->id ? 'selected' : '' }}>
                            {{ $block->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Search
            </button>

            @if(request()->has('search') || request('block') != 'all')
                <a href="{{ route('directory.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Member Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @php
            $query = \App\Models\User::with(['role', 'flat.block'])
                ->where('is_active', true)
                ->whereNotNull('flat_id');
            
            if (request('search')) {
                $search = request('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhereHas('flat', function($q2) use ($search) {
                          $q2->where('full_number', 'like', "%{$search}%");
                      });
                });
            }
            
            if (request('block') && request('block') != 'all') {
                $query->whereHas('flat', function($q) {
                    $q->where('block_id', request('block'));
                });
            }
            
            $members = $query->orderBy('name')->paginate(12);
        @endphp

        @forelse($members as $member)
            <div class="bg-white rounded-lg shadow hover:shadow-lg transition p-6 cursor-pointer" onclick="window.location='{{ route('members.show', $member) }}'">
                <div class="flex items-start gap-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-700 rounded-full flex items-center justify-center text-white font-bold text-xl flex-shrink-0">
                        {{ $member->initials() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-800 truncate">{{ $member->name }}</h3>
                        <p class="text-sm text-gray-600">
                            {{ $member->flat ? $member->flat->full_number : 'N/A' }}
                        </p>
                        <div class="mt-2 flex flex-wrap gap-1">
                            <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700">
                                {{ $member->role->display_name ?? 'Resident' }}
                            </span>
                            <span class="px-2 py-0.5 text-xs rounded-full {{ $member->resident_type == 'owner' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($member->resident_type) }}
                            </span>
                        </div>
                    </div>
                </div>
                
                @if($member->phone || $member->email)
                    <div class="mt-4 pt-4 border-t space-y-2">
                        @if($member->phone)
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <a href="tel:{{ $member->phone }}" class="hover:text-blue-600" onclick="event.stopPropagation()">{{ $member->phone }}</a>
                            </div>
                        @endif
                        
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <a href="mailto:{{ $member->email }}" class="hover:text-blue-600 truncate" onclick="event.stopPropagation()">{{ $member->email }}</a>
                        </div>
                    </div>
                @endif
                
                {{-- View Profile Button --}}
                <div class="mt-4 pt-4 border-t">
                    <a href="{{ route('members.show', $member) }}" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium" onclick="event.stopPropagation()">
                        View Profile
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-lg shadow p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <p class="text-gray-500">No members found</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($members->hasPages())
        <div class="mt-6">
            {{ $members->links() }}
        </div>
    @endif
</div>
@endsection