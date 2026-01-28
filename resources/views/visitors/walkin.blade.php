@extends('layouts.app')

@section('title', 'Walk-in Visitor Registration')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('visitors.security') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Security Dashboard
        </a>
    </div>

    <div class="bg-gradient-to-r from-orange-50 to-red-50 border-2 border-orange-200 rounded-lg shadow-lg p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">⚡ Walk-in Visitor Registration</h1>
                <p class="text-gray-600">Quick registration for unannounced visitors</p>
            </div>
        </div>

        <form action="{{ route('visitors.walkin.store') }}" method="POST">
            @csrf

            {{-- Quick Info Banner --}}
            <div class="mb-6 bg-orange-100 border border-orange-300 rounded-lg p-4">
                <p class="text-sm text-orange-800">
                    <strong>⚠️ Walk-in Registration:</strong> For visitors arriving without pre-approval (deliveries, surprise visits, emergency services, etc.)
                </p>
            </div>

            {{-- Visitor Details --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Visitor Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Visitor Name --}}
                    <div>
                        <label for="visitor_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Visitor Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="visitor_name" id="visitor_name" value="{{ old('visitor_name') }}" required autofocus
                            placeholder="Enter visitor's name"
                            class="w-full px-4 py-3 border-2 rounded-lg focus:ring-2 focus:ring-orange-500 @error('visitor_name') border-red-500 @enderror">
                        @error('visitor_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Phone Number --}}
                    <div>
                        <label for="visitor_phone" class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="visitor_phone" id="visitor_phone" value="{{ old('visitor_phone') }}" required
                            placeholder="+91 9876543210"
                            class="w-full px-4 py-3 border-2 rounded-lg focus:ring-2 focus:ring-orange-500 @error('visitor_phone') border-red-500 @enderror">
                        @error('visitor_phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Visiting Details --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Visiting Details</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Flat Number --}}
                    <div>
                        <label for="flat_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Visiting Flat <span class="text-red-500">*</span>
                        </label>
                        <select name="flat_id" id="flat_id" required
                            class="w-full px-4 py-3 border-2 rounded-lg focus:ring-2 focus:ring-orange-500 @error('flat_id') border-red-500 @enderror">
                            <option value="">Select Flat</option>
                            @php
                                $blocks = \App\Models\Block::with('flats')->get();
                            @endphp
                            @foreach($blocks as $block)
                                <optgroup label="{{ $block->name }}">
                                    @foreach($block->flats as $flat)
                                        <option value="{{ $flat->id }}" {{ old('flat_id') == $flat->id ? 'selected' : '' }}>
                                            {{ $flat->full_number }} @if($flat->owner) - {{ $flat->owner->name }}@endif
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('flat_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Visitor Type --}}
                    <div>
                        <label for="visitor_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Visitor Type <span class="text-red-500">*</span>
                        </label>
                        <select name="visitor_type" id="visitor_type" required
                            class="w-full px-4 py-3 border-2 rounded-lg focus:ring-2 focus:ring-orange-500 @error('visitor_type') border-red-500 @enderror">
                            <option value="delivery" {{ old('visitor_type', 'delivery') == 'delivery' ? 'selected' : '' }}>Delivery Person</option>
                            <option value="guest" {{ old('visitor_type') == 'guest' ? 'selected' : '' }}>Guest</option>
                            <option value="service" {{ old('visitor_type') == 'service' ? 'selected' : '' }}>Service Provider</option>
                            <option value="cab" {{ old('visitor_type') == 'cab' ? 'selected' : '' }}>Cab/Taxi</option>
                            <option value="family" {{ old('visitor_type') == 'family' ? 'selected' : '' }}>Family Member</option>
                            <option value="other" {{ old('visitor_type') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('visitor_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Purpose --}}
                    <div>
                        <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">
                            Purpose (Optional)
                        </label>
                        <input type="text" name="purpose" id="purpose" value="{{ old('purpose') }}"
                            placeholder="e.g., Amazon delivery, Food delivery"
                            class="w-full px-4 py-3 border-2 rounded-lg focus:ring-2 focus:ring-orange-500 @error('purpose') border-red-500 @enderror">
                        @error('purpose')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Vehicle Number --}}
                    <div>
                        <label for="vehicle_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Vehicle Number (Optional)
                        </label>
                        <input type="text" name="vehicle_number" id="vehicle_number" value="{{ old('vehicle_number') }}"
                            placeholder="MH12AB1234"
                            class="w-full px-4 py-3 border-2 rounded-lg focus:ring-2 focus:ring-orange-500 @error('vehicle_number') border-red-500 @enderror">
                        @error('vehicle_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Hidden fields --}}
            <input type="hidden" name="expected_date" value="{{ today()->format('Y-m-d') }}">
            <input type="hidden" name="expected_time" value="{{ now()->format('H:i') }}">
            <input type="hidden" name="number_of_persons" value="1">
            <input type="hidden" name="vehicle_type" value="none">

            {{-- Info Box --}}
            <div class="mb-6 bg-yellow-50 border-2 border-yellow-300 rounded-lg p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="text-sm text-yellow-800">
                        <p class="font-semibold mb-1">⚠️ Resident Approval Required</p>
                        <p>After registration, the <strong>resident will be notified</strong> and must <strong>approve</strong> the visitor before you can check them in.</p>
                    </div>
                </div>
            </div>

            {{-- Submit Buttons --}}
            <div class="flex gap-4 pt-6 border-t">
                <button type="submit" class="flex-1 px-6 py-4 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:from-orange-600 hover:to-red-600 transition font-bold text-lg shadow-lg">
                    📝 Register Visitor (Pending Approval)
                </button>
                <a href="{{ route('visitors.security') }}" class="px-6 py-4 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    {{-- Quick Tips --}}
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h4 class="font-semibold text-blue-900 mb-2">💡 How Walk-in Registration Works:</h4>
        <ol class="text-sm text-blue-800 space-y-2 list-decimal list-inside">
            <li><strong>Security registers visitor</strong> - Enter details at gate</li>
            <li><strong>Status: Pending Approval</strong> - Visitor waits at gate</li>
            <li><strong>Resident gets notified</strong> - Alert in their visitors page</li>
            <li><strong>Resident approves/rejects</strong> - They decide to allow entry</li>
            <li><strong>If approved:</strong> Security can check them in → Barrier opens!</li>
            <li><strong>If rejected:</strong> Visitor denied entry</li>
        </ol>
    </div>
</div>
@endsection