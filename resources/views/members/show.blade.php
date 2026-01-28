@extends('layouts.app')

@section('title', $member->name . ' - Profile')

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Back Button --}}
    <div class="mb-6">
        <a href="{{ route('members.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Members
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Profile Sidebar --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Profile Card --}}
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="text-center">
                    <div class="w-32 h-32 bg-gradient-to-br from-blue-600 to-blue-700 rounded-full flex items-center justify-center text-white font-bold text-4xl mx-auto mb-4">
                        {{ $member->initials() }}
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $member->name }}</h2>
                    <p class="text-gray-600">{{ $member->role->display_name ?? 'Member' }}</p>
                    
                    <div class="mt-4 flex justify-center gap-2">
                        @if($member->is_active)
                            <span class="px-3 py-1 text-sm rounded-full bg-green-100 text-green-700">Active</span>
                        @else
                            <span class="px-3 py-1 text-sm rounded-full bg-red-100 text-red-700">Inactive</span>
                        @endif
                        <span class="px-3 py-1 text-sm rounded-full {{ $member->resident_type == 'owner' ? 'bg-blue-100 text-blue-700' : ($member->resident_type == 'tenant' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') }}">
                            {{ ucfirst(str_replace('_', ' ', $member->resident_type)) }}
                        </span>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t space-y-4">
                    @if($member->email)
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <p class="text-xs text-gray-500">Email</p>
                                <a href="mailto:{{ $member->email }}" class="text-blue-600 hover:underline">{{ $member->email }}</a>
                            </div>
                        </div>
                    @endif

                    @if($member->phone)
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <div>
                                <p class="text-xs text-gray-500">Phone</p>
                                <a href="tel:{{ $member->phone }}" class="text-blue-600 hover:underline">{{ $member->phone }}</a>
                            </div>
                        </div>
                    @endif

                    @if($member->flat)
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <div>
                                <p class="text-xs text-gray-500">Flat Number</p>
                                <p class="font-medium text-gray-800">{{ $member->flat->full_number }}</p>
                                <p class="text-sm text-gray-600">{{ $member->flat->block->name }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                @can('update', $member)
                    <div class="mt-6 pt-6 border-t">
                        <a href="{{ route('members.edit', $member) }}" class="block w-full px-4 py-2 bg-blue-600 text-white text-center rounded-lg hover:bg-blue-700 transition">
                            Edit Profile
                        </a>
                    </div>
                @endcan
            </div>

            {{-- Quick Stats --}}
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Quick Stats</h3>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Complaints Filed</span>
                        <span class="font-medium text-gray-800">{{ $member->complaints->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Notices Posted</span>
                        <span class="font-medium text-gray-800">{{ $member->notices->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Member Since</span>
                        <span class="font-medium text-gray-800">{{ $member->created_at->format('M Y') }}</span>
                    </div>
                    @if($member->move_in_date)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Move-in Date</span>
                            <span class="font-medium text-gray-800">{{ $member->move_in_date->format('M d, Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Personal Information --}}
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($member->date_of_birth)
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Date of Birth</p>
                            <p class="font-medium text-gray-800">{{ $member->date_of_birth->format('F d, Y') }}</p>
                            <p class="text-sm text-gray-600">{{ $member->date_of_birth->age }} years old</p>
                        </div>
                    @endif

                    @if($member->gender)
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Gender</p>
                            <p class="font-medium text-gray-800">{{ ucfirst($member->gender) }}</p>
                        </div>
                    @endif

                    @if($member->address)
                        <div class="md:col-span-2">
                            <p class="text-sm text-gray-500 mb-1">Permanent Address</p>
                            <p class="font-medium text-gray-800">{{ $member->address }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Flat Information --}}
            @if($member->flat)
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Flat Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Flat Number</p>
                            <p class="font-medium text-gray-800">{{ $member->flat->full_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Block</p>
                            <p class="font-medium text-gray-800">{{ $member->flat->block->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Floor</p>
                            <p class="font-medium text-gray-800">{{ $member->flat->floor }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Bedrooms</p>
                            <p class="font-medium text-gray-800">{{ $member->flat->bedrooms }} BHK</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Carpet Area</p>
                            <p class="font-medium text-gray-800">{{ $member->flat->carpet_area }} sq.ft</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Status</p>
                            <span class="px-2 py-1 text-xs rounded-full {{ $member->flat->status == 'occupied' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($member->flat->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Recent Complaints --}}
            @if($member->complaints->count() > 0)
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-800">Recent Complaints</h3>
                        <a href="{{ route('complaints.index', ['user' => $member->id]) }}" class="text-blue-600 text-sm hover:underline">View All</a>
                    </div>
                    <div class="space-y-3">
                        @foreach($member->complaints->take(5) as $complaint)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800">{{ $complaint->subject }}</p>
                                    <p class="text-sm text-gray-600">{{ $complaint->created_at->diffForHumans() }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full bg-{{ $complaint->statusColor }}-100 text-{{ $complaint->statusColor }}-700">
                                    {{ ucwords(str_replace('_', ' ', $complaint->status)) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Recent Notices (if any) --}}
            @if($member->notices->count() > 0)
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-800">Notices Posted</h3>
                        <a href="{{ route('notices.index', ['author' => $member->id]) }}" class="text-blue-600 text-sm hover:underline">View All</a>
                    </div>
                    <div class="space-y-3">
                        @foreach($member->notices->take(5) as $notice)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800">{{ $notice->title }}</p>
                                    <p class="text-sm text-gray-600">{{ $notice->published_at->diffForHumans() }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full bg-{{ $notice->priority === 'high' ? 'red' : ($notice->priority === 'medium' ? 'yellow' : 'gray') }}-100 text-{{ $notice->priority === 'high' ? 'red' : ($notice->priority === 'medium' ? 'yellow' : 'gray') }}-700">
                                    {{ ucfirst($notice->priority) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection