@extends('layouts.app')

@section('title', $notice->title)

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Back Button --}}
    <div class="mb-6">
        <a href="{{ route('notices.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Notices
        </a>
    </div>

    {{-- Notice Card --}}
    <div class="bg-white rounded-lg shadow-lg">
        {{-- Header --}}
        <div class="p-6 border-b">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <h1 class="text-2xl font-bold text-gray-800">{{ $notice->title }}</h1>
                        <span class="px-3 py-1 text-sm rounded-full {{ $notice->priority === 'high' ? 'bg-red-100 text-red-700' : ($notice->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') }}">
                            {{ ucfirst($notice->priority) }} Priority
                        </span>
                    </div>
                    
                    <div class="flex items-center gap-4 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>{{ $notice->author->name }}</span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span>{{ $notice->published_at->format('F d, Y') }}</span>
                        </div>

                        @if($notice->block)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <span>{{ $notice->block->name }}</span>
                            </div>
                        @else
                            <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700">
                                All Blocks
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            @if($notice->valid_until)
                <div class="flex items-center gap-2 text-sm {{ $notice->isValid() ? 'text-green-600' : 'text-red-600' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>
                        @if($notice->isValid())
                            Valid until {{ $notice->valid_until->format('F d, Y') }}
                        @else
                            Expired on {{ $notice->valid_until->format('F d, Y') }}
                        @endif
                    </span>
                </div>
            @endif
        </div>

        {{-- Content --}}
        <div class="p-6">
            <div class="prose max-w-none text-gray-700">
                {!! nl2br(e($notice->content)) !!}
            </div>
        </div>

        {{-- Attachment (if any) --}}
        @if($notice->attachment)
            <div class="p-6 border-t bg-gray-50">
                <h3 class="font-semibold text-gray-800 mb-3">Attachment</h3>
                <a href="{{ asset('storage/' . $notice->attachment) }}" 
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Attachment
                </a>
            </div>
        @endif

        {{-- Actions (for authorized users) --}}
        @canany(['update', 'delete'], $notice)
            <div class="p-6 border-t bg-gray-50 flex gap-3">
                @can('update', $notice)
                    <a href="{{ route('notices.edit', $notice->id) }}" 
                       class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                        Edit Notice
                    </a>
                @endcan
                
                @can('delete', $notice)
                    <form action="{{ route('notices.destroy', $notice->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this notice?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            Delete Notice
                        </button>
                    </form>
                @endcan
            </div>
        @endcanany
    </div>
</div>
@endsection