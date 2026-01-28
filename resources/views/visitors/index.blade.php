@extends('layouts.app')

@section('title', 'Visitor Management')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Visitor Management</h1>
            <p class="text-gray-600 mt-1">Manage and track all visitor entries</p>
        </div>
        <div class="flex gap-3">
            @can('viewSecurity', App\Models\Visitor::class)
                <a href="{{ route('visitors.security') }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Security Dashboard
                </a>
            @endcan
            @can('create', App\Models\Visitor::class)
                <a href="{{ route('visitors.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Register Visitor
                </a>
            @endcan
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Expected Today</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['today_expected'] }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Currently Inside</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['currently_inside'] }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Today's Total</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $stats['today_total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow p-4">
        <form method="GET" action="{{ route('visitors.index') }}" class="flex flex-wrap gap-4">
            <div>
                <select name="status" class="px-4 py-2 border rounded-lg" onchange="this.form.submit()">
                    <option value="all">All Status</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                    <option value="checked_out" {{ request('status') == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div>
                <select name="type" class="px-4 py-2 border rounded-lg" onchange="this.form.submit()">
                    <option value="all">All Types</option>
                    @foreach(App\Models\Visitor::visitorTypes() as $key => $value)
                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="date" class="px-4 py-2 border rounded-lg" onchange="this.form.submit()">
                    <option value="">All Dates</option>
                    <option value="today" {{ request('date') == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ request('date') == 'week' ? 'selected' : '' }}>This Week</option>
                </select>
            </div>

            @if(request()->hasAny(['status', 'type', 'date']))
                <a href="{{ route('visitors.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800">
                    Clear Filters
                </a>
            @endif
        </form>
    </div>

    {{-- Visitors List --}}
    <div class="space-y-4">
        @forelse($visitors as $visitor)
            <div class="bg-white rounded-lg shadow hover:shadow-md transition cursor-pointer" onclick="window.location='{{ route('visitors.show', $visitor) }}'">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-gray-800">{{ $visitor->visitor_name }}</h3>
                                <span class="px-2 py-1 text-xs rounded-full bg-{{ $visitor->statusColor }}-100 text-{{ $visitor->statusColor }}-700">
                                    {{ ucwords(str_replace('_', ' ', $visitor->status)) }}
                                </span>
                                <span class="px-2 py-1 text-xs rounded-full bg-{{ $visitor->typeColor }}-100 text-{{ $visitor->typeColor }}-700">
                                    {{ App\Models\Visitor::visitorTypes()[$visitor->visitor_type] }}
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                                <div>
                                    <span class="font-medium">Gate Pass:</span> {{ $visitor->gate_pass_code }}
                                </div>
                                <div>
                                    <span class="font-medium">Flat:</span> {{ $visitor->flat->full_number }}
                                </div>
                                <div>
                                    <span class="font-medium">Phone:</span> {{ $visitor->visitor_phone }}
                                </div>
                                <div>
                                    <span class="font-medium">Expected:</span> {{ $visitor->expected_date->format('M d, Y') }}
                                    @if($visitor->expected_time)
                                        at {{ $visitor->expected_time->format('h:i A') }}
                                    @endif
                                </div>
                                @if($visitor->vehicle_number)
                                    <div>
                                        <span class="font-medium">Vehicle:</span> {{ $visitor->vehicle_number }}
                                    </div>
                                @endif
                                @if($visitor->purpose)
                                    <div>
                                        <span class="font-medium">Purpose:</span> {{ $visitor->purpose }}
                                    </div>
                                @endif
                            </div>

                            @if($visitor->check_in_time)
                                <div class="mt-3 pt-3 border-t text-sm">
                                    <span class="text-green-600 font-medium">✓ Checked In:</span> 
                                    {{ $visitor->check_in_time->format('M d, Y h:i A') }}
                                    @if($visitor->check_out_time)
                                        <span class="ml-4 text-gray-600">• Checked Out:</span>
                                        {{ $visitor->check_out_time->format('M d, Y h:i A') }}
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0 ml-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="text-gray-500 text-lg">No visitors found</p>
                <p class="text-gray-400 text-sm mt-1">Register a new visitor to get started</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($visitors->hasPages())
        <div class="mt-6">
            {{ $visitors->links() }}
        </div>
    @endif
</div>
@endsection