@extends('layouts.app')

@section('title', $feature . ' - Coming Soon')

@section('content')
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="text-center">
        <div class="mb-6">
            <svg class="w-24 h-24 mx-auto text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">{{ $feature }}</h1>
        <p class="text-gray-600 mb-6">This feature is coming soon in Phase 3!</p>
        <a href="{{ route('dashboard') }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Back to Dashboard
        </a>
    </div>
</div>
@endsection