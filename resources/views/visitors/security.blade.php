@extends('layouts.app')

@section('title', 'Security Dashboard - Visitor Management')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🛡️ Security Dashboard</h1>
            <p class="text-gray-600 mt-1">Today's visitor management - {{ now()->format('l, F d, Y') }}</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('visitors.walkin.create') }}" class="px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 transition font-medium flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Walk-in Registration
            </a>
            <a href="{{ route('visitors.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                View All Visitors
            </a>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-orange-50 border-2 border-orange-200 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-orange-700 font-medium">Pending Approval</p>
                    <p class="text-3xl font-bold text-orange-900">{{ $stats['pending_count'] }}</p>
                </div>
                <div class="w-14 h-14 bg-orange-200 rounded-full flex items-center justify-center animate-pulse">
                    <svg class="w-8 h-8 text-orange-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border-2 border-blue-200 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-blue-700 font-medium">Expected Today</p>
                    <p class="text-3xl font-bold text-blue-900">{{ $stats['expected_count'] }}</p>
                </div>
                <div class="w-14 h-14 bg-blue-200 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-green-50 border-2 border-green-200 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-green-700 font-medium">Currently Inside</p>
                    <p class="text-3xl font-bold text-green-900">{{ $stats['inside_count'] }}</p>
                </div>
                <div class="w-14 h-14 bg-green-200 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 border-2 border-gray-200 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-700 font-medium">Checked Out Today</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['checked_out_today'] }}</p>
                </div>
                <div class="w-14 h-14 bg-gray-200 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Search --}}
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">🔍 Quick Search by Gate Pass Code</h3>
        <form action="{{ route('visitors.show', 'SEARCH') }}" method="GET" class="flex gap-3" onsubmit="return handleSearch(event)">
            <input type="text" id="gate_pass_search" placeholder="Enter Gate Pass Code (e.g., GP-2026-0001)"
                class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg">
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                Search
            </button>
        </form>
        <p class="mt-2 text-sm text-gray-500">Visitor can show this code at the gate for quick check-in</p>
    </div>

    {{-- Pending Approvals (Waiting at Gate) --}}
    @if($pendingApprovals->count() > 0)
        <div class="bg-white rounded-lg shadow-lg border-2 border-orange-300">
            <div class="p-6 border-b bg-orange-50">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                    <span class="w-3 h-3 bg-orange-500 rounded-full animate-pulse"></span>
                    ⏳ Waiting for Approval ({{ $pendingApprovals->count() }})
                </h3>
                <p class="text-sm text-orange-700 mt-1">Visitors at gate waiting for resident approval</p>
            </div>
            <div class="divide-y">
                @foreach($pendingApprovals as $visitor)
                    <div class="p-6 bg-orange-50/50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-12 h-12 bg-orange-200 rounded-full flex items-center justify-center animate-pulse">
                                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-800">{{ $visitor->visitor_name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $visitor->visitor_phone }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-3 bg-white p-3 rounded-lg">
                                    <div>
                                        <span class="text-gray-500">Gate Pass:</span>
                                        <p class="font-mono font-bold text-orange-600">{{ $visitor->gate_pass_code }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Visiting:</span>
                                        <p class="font-medium text-gray-800">{{ $visitor->flat->full_number }}</p>
                                        <p class="text-xs text-gray-600">{{ $visitor->user->name }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Registered:</span>
                                        <p class="font-medium text-gray-800">{{ $visitor->created_at->format('h:i A') }}</p>
                                        <p class="text-xs text-gray-600">{{ $visitor->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Type:</span>
                                        <span class="px-2 py-1 text-xs rounded-full bg-{{ $visitor->typeColor }}-100 text-{{ $visitor->typeColor }}-700">
                                            {{ App\Models\Visitor::visitorTypes()[$visitor->visitor_type] }}
                                        </span>
                                    </div>
                                </div>

                                @if($visitor->vehicle_number)
                                    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <span class="font-medium">Vehicle: {{ $visitor->vehicle_number }}</span>
                                    </div>
                                @endif

                                <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <p class="text-sm text-yellow-800">
                                        <strong>⚠️ Waiting for resident to approve.</strong> They will receive notification to approve/reject this visitor.
                                    </p>
                                </div>
                            </div>

                            <div class="ml-4 text-orange-600 font-semibold text-sm">
                                <div class="text-center p-3 bg-orange-100 rounded-lg">
                                    <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    PENDING
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Currently Inside Visitors --}}
    @if($insideVisitors->count() > 0)
        <div class="bg-white rounded-lg shadow-lg">
            <div class="p-6 border-b bg-green-50">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                    <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span>
                    Currently Inside ({{ $insideVisitors->count() }})
                </h3>
            </div>
            <div class="divide-y">
                @foreach($insideVisitors as $visitor)
                    <div class="p-6 hover:bg-gray-50 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-800">{{ $visitor->visitor_name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $visitor->visitor_phone }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-3">
                                    <div>
                                        <span class="text-gray-500">Gate Pass:</span>
                                        <p class="font-mono font-bold text-blue-600">{{ $visitor->gate_pass_code }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Visiting:</span>
                                        <p class="font-medium text-gray-800">{{ $visitor->flat->full_number }} - {{ $visitor->user->name }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Checked In:</span>
                                        <p class="font-medium text-gray-800">{{ $visitor->check_in_time->format('h:i A') }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Duration:</span>
                                        <p class="font-medium text-gray-800">{{ $visitor->check_in_time->diffForHumans(null, true) }}</p>
                                    </div>
                                </div>

                                @if($visitor->vehicle_number)
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <span class="font-medium">Vehicle: {{ $visitor->vehicle_number }}</span>
                                    </div>
                                @endif
                            </div>

                            <form action="{{ route('visitors.check-out', $visitor) }}" method="POST">
                                @csrf
                                <button type="submit" onclick="return confirm('Check out {{ $visitor->visitor_name }}?')"
                                    class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"></path>
                                    </svg>
                                    Check Out
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Expected Visitors Today --}}
    <div class="bg-white rounded-lg shadow-lg">
        <div class="p-6 border-b bg-blue-50">
            <h3 class="text-xl font-semibold text-gray-800">📋 Expected Visitors Today ({{ $expectedVisitors->count() }})</h3>
        </div>
        
        @if($expectedVisitors->count() > 0)
            <div class="divide-y">
                @foreach($expectedVisitors as $visitor)
                    <div class="p-6 hover:bg-gray-50 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-800">{{ $visitor->visitor_name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $visitor->visitor_phone }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-3">
                                    <div>
                                        <span class="text-gray-500">Gate Pass:</span>
                                        <p class="font-mono font-bold text-blue-600">{{ $visitor->gate_pass_code }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Visiting:</span>
                                        <p class="font-medium text-gray-800">{{ $visitor->flat->full_number }} - {{ $visitor->user->name }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Expected Time:</span>
                                        <p class="font-medium text-gray-800">{{ $visitor->expected_time ? $visitor->expected_time->format('h:i A') : 'Anytime' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Type:</span>
                                        <span class="px-2 py-1 text-xs rounded-full bg-{{ $visitor->typeColor }}-100 text-{{ $visitor->typeColor }}-700">
                                            {{ App\Models\Visitor::visitorTypes()[$visitor->visitor_type] }}
                                        </span>
                                    </div>
                                </div>

                                @if($visitor->vehicle_number)
                                    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <span class="font-medium">Vehicle: {{ $visitor->vehicle_number }}</span>
                                    </div>
                                @endif

                                @if($visitor->purpose)
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Purpose:</span> {{ $visitor->purpose }}
                                    </p>
                                @endif
                            </div>

                            <form action="{{ route('visitors.check-in', $visitor) }}" method="POST">
                                @csrf
                                <button type="submit" onclick="return confirm('Check in {{ $visitor->visitor_name }}?')"
                                    class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                    Check In
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-500 text-lg">No visitors expected today</p>
                <p class="text-gray-400 text-sm mt-1">Check back later or search by gate pass code</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function handleSearch(event) {
    event.preventDefault();
    const gatePassCode = document.getElementById('gate_pass_search').value.trim();
    if (!gatePassCode) {
        alert('Please enter a gate pass code');
        return false;
    }
    // Redirect to visitor show page based on gate pass code
    window.location.href = '/visitors?search=' + encodeURIComponent(gatePassCode);
    return false;
}
</script>
@endpush
@endsection