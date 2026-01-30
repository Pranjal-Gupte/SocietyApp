@extends('layouts.app')

@section('title', 'Bulk Generate Bills')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('payments.index') }}" class="p-2 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Bulk Generate Bills</h1>
            <p class="text-gray-600">Create bills for multiple flats at once</p>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg flex items-start gap-3">
        <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
        </svg>
        <div>
            <p class="font-medium">Bulk Bill Generation</p>
            <p class="text-sm">Generate bills for all flats, specific blocks, or selected flats. Existing bills for the same month will be skipped.</p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('payments.bulk-store') }}" method="POST" class="p-6 space-y-6" x-data="{ 
            selectionType: 'all',
            selectedBlock: '',
            selectedFlats: []
        }">
            @csrf

            <!-- Bill Details -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 pb-2 border-b">Bill Details</h3>

                <!-- Billing Month -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Billing Month <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="month" 
                            name="billing_month" 
                            value="{{ old('billing_month', now()->format('Y-m')) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('billing_month') border-red-500 @enderror"
                            required
                        >
                        @error('billing_month')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bill Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Bill Type <span class="text-red-500">*</span>
                        </label>
                        <select 
                            name="payment_type" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('payment_type') border-red-500 @enderror"
                            required
                        >
                            <option value="maintenance">Maintenance</option>
                            <option value="water">Water Charges</option>
                            <option value="electricity">Electricity</option>
                            <option value="parking">Parking</option>
                            <option value="amenity">Amenity Charges</option>
                            <option value="other">Other</option>
                        </select>
                        @error('payment_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Amount and Due Date -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Amount per Flat <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₹</span>
                            <input 
                                type="number" 
                                name="amount" 
                                step="0.01"
                                value="{{ old('amount') }}"
                                class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('amount') border-red-500 @enderror"
                                required
                            >
                        </div>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Due Date <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            name="due_date" 
                            value="{{ old('due_date', now()->addDays(15)->format('Y-m-d')) }}"
                            min="{{ now()->format('Y-m-d') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('due_date') border-red-500 @enderror"
                            required
                        >
                        @error('due_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <textarea 
                        name="notes" 
                        rows="2"
                        placeholder="Any notes for all bills..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Flat Selection -->
            <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-800 pb-2 border-b">Select Flats</h3>

                <!-- Selection Type -->
                <div class="space-y-3">
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="selection_type" value="all" x-model="selectionType" class="text-blue-600">
                        <div>
                            <p class="font-medium">All Flats</p>
                            <p class="text-sm text-gray-600">Generate bills for all flats with owners ({{ $flats->count() }} flats)</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="selection_type" value="block" x-model="selectionType" class="mt-1 text-blue-600">
                        <div class="flex-1">
                            <p class="font-medium">By Block</p>
                            <p class="text-sm text-gray-600 mb-3">Generate bills for all flats in selected blocks</p>
                            
                            <div 
                                x-show="selectionType === 'block'" 
                                class="grid grid-cols-2 gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200"
                            >
                                @foreach($blocks as $block)
                                    <label class="flex items-center gap-2 group">
                                        <input 
                                            type="checkbox" 
                                            name="block_ids[]" 
                                            value="{{ $block->id }}"
                                            :disabled="selectionType !== 'block'"
                                            class="text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                        >
                                        <span class="text-sm text-gray-700 group-hover:text-blue-600 transition">
                                            Block {{ $block->name }} 
                                            <span class="text-xs text-gray-400">({{ $block->flats()->whereNotNull('owner_id')->count() }})</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('block_ids')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="selection_type" value="specific" x-model="selectionType" class="text-blue-600">
                        <div class="flex-1">
                            <p class="font-medium">Specific Flats</p>
                            <p class="text-sm text-gray-600 mb-2">Choose individual flats</p>
                            <div 
                                x-show="selectionType === 'specific'"
                                class="max-h-48 overflow-y-auto border rounded-lg p-3 space-y-2"
                            >
                                @foreach($flats->groupBy('block.name') as $blockName => $blockFlats)
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Block {{ $blockName }}</p>
                                        <div class="grid grid-cols-3 gap-2">
                                            @foreach($blockFlats as $flat)
                                                <label class="flex items-center gap-2">
                                                    <input 
                                                        type="checkbox" 
                                                        name="flat_ids[]" 
                                                        value="{{ $flat->id }}"
                                                        :disabled="selectionType !== 'specific'"
                                                        class="text-blue-600 rounded"
                                                    >
                                                    <span class="text-sm">{{ $flat->full_number }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    Generate Bills
                </button>
                <a href="{{ route('payments.index') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Alpine.js is already loaded in the layout
</script>
@endpush
@endsection