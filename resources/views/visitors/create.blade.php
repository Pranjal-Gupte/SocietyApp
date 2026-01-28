@extends('layouts.app')

@section('title', 'Register Visitor')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('visitors.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Visitors
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Register New Visitor</h1>

        <form action="{{ route('visitors.store') }}" method="POST">
            @csrf

            {{-- Visitor Information --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Visitor Information</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Visitor Name --}}
                    <div>
                        <label for="visitor_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Visitor Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="visitor_name" id="visitor_name" value="{{ old('visitor_name') }}" required
                            placeholder="John Doe"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('visitor_name') border-red-500 @enderror">
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
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('visitor_phone') border-red-500 @enderror">
                        @error('visitor_phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email (Optional) --}}
                    <div>
                        <label for="visitor_email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address (Optional)
                        </label>
                        <input type="email" name="visitor_email" id="visitor_email" value="{{ old('visitor_email') }}"
                            placeholder="visitor@example.com"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('visitor_email') border-red-500 @enderror">
                        @error('visitor_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Visitor Type --}}
                    <div>
                        <label for="visitor_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Visitor Type <span class="text-red-500">*</span>
                        </label>
                        <select name="visitor_type" id="visitor_type" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('visitor_type') border-red-500 @enderror">
                            <option value="">Select Type</option>
                            @foreach(App\Models\Visitor::visitorTypes() as $key => $value)
                                <option value="{{ $key }}" {{ old('visitor_type') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                        @error('visitor_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Number of Persons --}}
                    <div>
                        <label for="number_of_persons" class="block text-sm font-medium text-gray-700 mb-2">
                            Number of Persons <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="number_of_persons" id="number_of_persons" value="{{ old('number_of_persons', 1) }}" required min="1" max="20"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('number_of_persons') border-red-500 @enderror">
                        @error('number_of_persons')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Purpose --}}
                    <div class="md:col-span-2">
                        <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">
                            Purpose of Visit (Optional)
                        </label>
                        <input type="text" name="purpose" id="purpose" value="{{ old('purpose') }}"
                            placeholder="e.g., Birthday party, Repair work, Business meeting"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('purpose') border-red-500 @enderror">
                        @error('purpose')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Vehicle Details --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Vehicle Details</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Vehicle Type --}}
                    <div>
                        <label for="vehicle_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Vehicle Type <span class="text-red-500">*</span>
                        </label>
                        <select name="vehicle_type" id="vehicle_type" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('vehicle_type') border-red-500 @enderror">
                            <option value="none" {{ old('vehicle_type', 'none') == 'none' ? 'selected' : '' }}>No Vehicle</option>
                            <option value="two_wheeler" {{ old('vehicle_type') == 'two_wheeler' ? 'selected' : '' }}>Two Wheeler</option>
                            <option value="four_wheeler" {{ old('vehicle_type') == 'four_wheeler' ? 'selected' : '' }}>Four Wheeler</option>
                        </select>
                        @error('vehicle_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Vehicle Number --}}
                    <div id="vehicle_number_field">
                        <label for="vehicle_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Vehicle Number
                        </label>
                        <input type="text" name="vehicle_number" id="vehicle_number" value="{{ old('vehicle_number') }}"
                            placeholder="MH12AB1234"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('vehicle_number') border-red-500 @enderror">
                        @error('vehicle_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Visit Schedule --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Visit Schedule</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Expected Date --}}
                    <div>
                        <label for="expected_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Expected Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="expected_date" id="expected_date" value="{{ old('expected_date', today()->format('Y-m-d')) }}" required
                            min="{{ today()->format('Y-m-d') }}"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('expected_date') border-red-500 @enderror">
                        @error('expected_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Expected Time --}}
                    <div>
                        <label for="expected_time" class="block text-sm font-medium text-gray-700 mb-2">
                            Expected Time (Optional)
                        </label>
                        <input type="time" name="expected_time" id="expected_time" value="{{ old('expected_time') }}"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 @error('expected_time') border-red-500 @enderror">
                        @error('expected_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Additional Options --}}
            <div class="mb-8">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_frequent_visitor" value="1" {{ old('is_frequent_visitor') ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Mark as Frequent Visitor</span>
                </label>
                <p class="mt-1 text-xs text-gray-500 ml-6">For regular visitors like maids, delivery persons, etc.</p>
            </div>

            {{-- Info Box --}}
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">Visitor will be registered for:</p>
                        <p><strong>Your Flat:</strong> {{ auth()->user()->flat->full_number ?? 'Not Assigned' }}</p>
                        <p class="mt-2">A unique <strong>Gate Pass Code</strong> will be generated automatically after registration.</p>
                    </div>
                </div>
            </div>

            {{-- Submit Buttons --}}
            <div class="flex gap-4 pt-6 border-t">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    Register Visitor
                </button>
                <a href="{{ route('visitors.index') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Show/hide vehicle number field based on vehicle type
    document.getElementById('vehicle_type').addEventListener('change', function() {
        const vehicleNumberField = document.getElementById('vehicle_number_field');
        if (this.value === 'none') {
            vehicleNumberField.style.display = 'none';
            document.getElementById('vehicle_number').required = false;
        } else {
            vehicleNumberField.style.display = 'block';
            document.getElementById('vehicle_number').required = false; // Keep it optional
        }
    });
    
    // Trigger on page load
    document.getElementById('vehicle_type').dispatchEvent(new Event('change'));
</script>
@endpush
@endsection