@extends('layouts.app')

@section('title', 'Dashboard - Society Management')

@section('content')
<div class="space-y-6">
    {{-- Welcome Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">Welcome back, {{ auth()->user()->name }}!</h1>
        <p class="text-blue-100">
            {{ auth()->user()->flat_number ?? 'Resident' }} • {{ auth()->user()->block->name ?? 'Sunrise Apartments' }}
        </p>
    </div>

    {{-- Quick Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Pending Payments --}}
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending Payments</p>
                    <p class="text-2xl font-bold text-gray-800">₹{{ number_format($pendingPayments ?? 0, 0) }}</p>
                    <a href="{{ route('payments.index') }}" class="text-blue-600 text-sm hover:underline mt-1 inline-block">
                        View Details →
                    </a>
                </div>
                <svg class="w-10 h-10 text-blue-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        {{-- Active Complaints --}}
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Active Complaints</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $activeComplaints ?? 0 }}</p>
                    <a href="{{ route('complaints.index') }}" class="text-green-600 text-sm hover:underline mt-1 inline-block">
                        View All →
                    </a>
                </div>
                <svg class="w-10 h-10 text-green-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                </svg>
            </div>
        </div>

        {{-- New Notices --}}
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">New Notices</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $newNotices ?? 0 }}</p>
                    <a href="{{ route('notices.index') }}" class="text-orange-600 text-sm hover:underline mt-1 inline-block">
                        View All →
                    </a>
                </div>
                <svg class="w-10 h-10 text-orange-500 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Pending Visitor Approvals --}}
    @php
        $pendingVisitors = auth()->user()->visitors()->pendingApproval()->latest()->get();
    @endphp

    @if($pendingVisitors->count() > 0)
        <div class="bg-orange-50 border-2 border-orange-300 rounded-lg shadow-lg p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center animate-pulse">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">⚠️ Visitors Waiting at Gate!</h3>
                    <p class="text-sm text-orange-700">{{ $pendingVisitors->count() }} visitor(s) need your approval</p>
                </div>
            </div>

            <div class="space-y-3">
                @foreach($pendingVisitors as $visitor)
                    <div class="bg-white rounded-lg p-4 border-2 border-orange-200">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="font-bold text-gray-800">{{ $visitor->visitor_name }}</h4>
                                <p class="text-sm text-gray-600">{{ $visitor->visitor_phone }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ App\Models\Visitor::visitorTypes()[$visitor->visitor_type] }}
                                    @if($visitor->purpose) • {{ $visitor->purpose }}@endif
                                </p>
                            </div>
                            <span class="text-xs text-orange-600 font-medium">{{ $visitor->created_at->diffForHumans() }}</span>
                        </div>

                        <div class="flex gap-2">
                            <form action="{{ route('visitors.approve', $visitor) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                                    ✓ Approve Entry
                                </button>
                            </form>
                            <form action="{{ route('visitors.reject', $visitor) }}" method="POST" class="flex-1" onsubmit="return confirm('Reject this visitor?')">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                                    ✗ Reject
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Recent Notices --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Recent Notices</h2>
            <a href="{{ route('notices.index') }}" class="text-blue-600 text-sm font-medium hover:underline">
                View All
            </a>
        </div>
        <div class="divide-y">
            @forelse($recentNotices ?? [] as $notice)
                <div class="p-4 hover:bg-gray-50 cursor-pointer transition" onclick="window.location='{{ route('notices.show', $notice->id) }}'">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-medium text-gray-800">{{ $notice->title }}</h3>
                                @if($notice->priority === 'high')
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700">High</span>
                                @elseif($notice->priority === 'medium')
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-700">Medium</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700">Low</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500">
                                {{ $notice->author->name ?? 'Society Committee' }} • {{ $notice->created_at->format('M d, Y') }}
                            </p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <p>No recent notices</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Upcoming Bills --}}
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold text-gray-800">Upcoming Bills</h2>
        </div>
        <div class="divide-y">
            @forelse($upcomingBills ?? [] as $bill)
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <h3 class="font-medium text-gray-800">{{ $bill->type }}</h3>
                        <p class="text-sm text-gray-500">Due: {{ $bill->due_date->format('M d, Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-gray-800">₹{{ number_format($bill->amount, 0) }}</p>
                        @if($bill->status === 'unpaid')
                            <a href="{{ route('payments.show', $bill->id) }}" 
                               class="mt-1 inline-block px-4 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                                Pay Now
                            </a>
                        @else
                            <span class="px-3 py-1 bg-green-100 text-green-700 text-sm rounded">Paid</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p>No upcoming bills</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Quick Actions (for residents) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="#" 
           class="bg-white rounded-lg shadow p-6 hover:shadow-md transition group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-medium text-gray-800">Register Complaint</h3>
                    <p class="text-sm text-gray-500">Report an issue</p>
                </div>
            </div>
        </a>

        <a href="#" 
           class="bg-white rounded-lg shadow p-6 hover:shadow-md transition group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-medium text-gray-800">Book Facility</h3>
                    <p class="text-sm text-gray-500">Reserve amenities</p>
                </div>
            </div>
        </a>

        <a href="{{ route('directory.index') }}" 
           class="bg-white rounded-lg shadow p-6 hover:shadow-md transition group">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-medium text-gray-800">View Directory</h3>
                    <p class="text-sm text-gray-500">Contact members</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection