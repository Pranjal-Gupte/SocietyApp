@extends('layouts.app')

@section('title', 'Create New Bill')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('payments.index') }}" class="p-2 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Create New Bill</h1>
            <p class="text-gray-600">Generate a bill for a specific flat</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('payments.store') }}" method="POST" class="p-6 space-y-6" x-data="{ 
            selectedFlat: null,
            flats: {{ json_encode($flats->map(function($f) { 
                return ['id' => $f->id, 'full_number' => $f->full_number, 'owner' => $f->owner ? $f->owner->name : null]; 
            })) }}
        }">
            @csrf

            @if ($errors->any())
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50">
                <strong>Whoops!</strong> Please fix the following:
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Flat Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Select Flat <span class="text-red-500">*</span>
                </label>
                <select 
                    name="flat_id" 
                    x-model="selectedFlat"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('flat_id') border-red-500 @enderror"
                    required
                >
                    <option value="">Choose a flat</option>
                    @foreach($flats->groupBy('block.name') as $blockName => $blockFlats)
                        <optgroup label="Block {{ $blockName }}">
                            @foreach($blockFlats as $flat)
                                <option value="{{ $flat->id }}">
                                    {{ $flat->full_number }} - {{ $flat->owner->name ?? 'No owner' }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('flat_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Bill Type and Billing Month -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Bill Type <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="payment_type" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('payment_type') border-red-500 @enderror"
                        required
                    >
                        <option value="maintenance">Maintenance</option>
                        <option value="water">Water Charges</option>
                        <option value="electricity">Electricity</option>
                        <option value="parking">Parking</option>
                        <option value="amenity">Amenity Charges</option>
                        <option value="penalty">Penalty</option>
                        <option value="other">Other</option>
                    </select>
                    @error('payment_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Billing Month <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="month" 
                        name="billing_month" 
                        value="{{ old('billing_month', now()->format('Y-m')) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('billing_month') border-red-500 @enderror"
                        required
                    >
                    @error('billing_month')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Amount Details -->
            <div class="space-y-4 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-800">Amount Details</h3>
                
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Amount <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₹</span>
                            <input 
                                type="number" 
                                name="amount" 
                                step="0.01"
                                value="{{ old('amount') }}"
                                class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg @error('amount') border-red-500 @enderror"
                                required
                            >
                        </div>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Late Fee</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₹</span>
                            <input 
                                type="number" 
                                name="late_fee" 
                                step="0.01"
                                value="{{ old('late_fee', 0) }}"
                                class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Discount</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₹</span>
                            <input 
                                type="number" 
                                name="discount" 
                                step="0.01"
                                value="{{ old('discount', 0) }}"
                                class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Due Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Due Date <span class="text-red-500">*</span>
                </label>
                <input 
                    type="date" 
                    name="due_date" 
                    value="{{ old('due_date', now()->addDays(15)->format('Y-m-d')) }}"
                    min="{{ now()->format('Y-m-d') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('due_date') border-red-500 @enderror"
                    required
                >
                @error('due_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                <textarea 
                    name="notes" 
                    rows="3"
                    placeholder="Any additional notes for this bill..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >{{ old('notes') }}</textarea>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                    Create Bill
                </button>
                <a href="{{ route('payments.index') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection