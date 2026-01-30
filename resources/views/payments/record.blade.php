@extends('layouts.app')

@section('title', 'Record Payment')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('payments.show', ['bill' => $payment->id]) }}" class="p-2 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Record Payment</h1>
            <p class="text-gray-600">{{ $payment->payment_number }}</p>
        </div>
    </div>

    <!-- Bill Summary -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-blue-800 mb-1">Flat {{ $payment->flat->full_number }} • {{ $payment->user->name }}</p>
                <p class="text-2xl font-bold text-blue-900">₹{{ number_format($payment->due_amount, 2) }}</p>
                <p class="text-sm text-blue-700 mt-1">Outstanding Amount</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-blue-800">{{ $payment->payment_type_display }}</p>
                <p class="text-sm text-blue-700">{{ $payment->paymenting_period }}</p>
            </div>
        </div>
    </div>

    <!-- Payment Form -->
    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('payments.store-payment', ['bill' => $payment->id]) }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Amount -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Amount <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">₹</span>
                    <input 
                        type="number" 
                        name="amount" 
                        step="0.01"
                        max="{{ $payment->due_amount }}"
                        value="{{ old('amount', $payment->due_amount) }}"
                        class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('amount') border-red-500 @enderror"
                        required
                    >
                </div>
                @error('amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Maximum: ₹{{ number_format($payment->due_amount, 2) }}</p>
            </div>

            <!-- Payment Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Date <span class="text-red-500">*</span>
                </label>
                <input 
                    type="date" 
                    name="transaction_date" 
                    value="{{ old('transaction_date', now()->format('Y-m-d')) }}"
                    max="{{ now()->format('Y-m-d') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('transaction_date') border-red-500 @enderror"
                    required
                >
                @error('transaction_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Payment Method -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Method <span class="text-red-500">*</span>
                </label>
                <select 
                    name="payment_method" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('payment_method') border-red-500 @enderror"
                    required
                >
                    <option value="">Select method</option>
                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="online" {{ old('payment_method') == 'online' ? 'selected' : '' }}>Online Transfer</option>
                    <option value="upi" {{ old('payment_method') == 'upi' ? 'selected' : '' }}>UPI</option>
                    <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                </select>
                @error('payment_method')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Payment Reference -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Reference
                </label>
                <input 
                    type="text" 
                    name="reference_number" 
                    value="{{ old('reference_number') }}"
                    placeholder="Cheque number, Transaction ID, UPI reference, etc."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('reference_number') border-red-500 @enderror"
                >
                @error('reference_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Notes (Optional)
                </label>
                <textarea 
                    name="notes" 
                    rows="3"
                    placeholder="Any additional notes about this payment..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('notes') border-red-500 @enderror"
                >{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                    Record Payment
                </button>
                <a href="{{ route('payments.show', ['bill' => $payment->id]) }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection