@extends('layouts.app')

@section('title', 'Bill Details - ' . $payment->payment_number)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('payments.index') }}" class="p-2 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $payment->payment_number }}</h1>
                <p class="text-gray-600">{{ $payment->title }}</p>
            </div>
        </div>

        <div class="flex gap-2">
            @if($payment->status != 'paid' && $payment->status != 'cancelled')
                @can('update', $payment)
                <a href="{{ route('payments.record', ['bill' => $payment->id]) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Record Payment
                </a>
                @endcan
            @endif

            @can('delete', $payment)
                @if($payment->status != 'paid' && $payment->transactions->count() == 0)
                <form action="{{ route('payments.cancel', $payment) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this bill?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Cancel Bill
                    </button>
                </form>
                @endif
            @endcan
        </div>
    </div>

    <!-- Status Alert -->
    @if($payment->isOverdue())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <p class="font-medium">This bill is overdue</p>
                <p class="text-sm">Due date was {{ $payment->due_date->format('d M Y') }} ({{ $payment->due_date->diffForHumans() }})</p>
            </div>
        </div>
    @elseif($payment->status == 'paid')
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <span>This bill has been fully paid</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Bill Information -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Bill Details Card -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold text-gray-800">Bill Information</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Bill Number</p>
                            <p class="text-base font-medium text-gray-900">{{ $payment->payment_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-{{ $payment->status_color }}-100 text-{{ $payment->status_color }}-800">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Flat Number</p>
                            <p class="text-base font-medium text-gray-900">{{ $payment->flat->full_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Resident</p>
                            <p class="text-base font-medium text-gray-900">{{ $payment->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Bill Type</p>
                            <p class="text-base font-medium text-gray-900">{{ $payment->payment_type_display }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Title</p>
                            <p class="text-base font-medium text-gray-900">{{ $payment->title }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Bill Date</p>
                            <p class="text-base font-medium text-gray-900">{{ $payment->bill_date->format('d M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Due Date</p>
                            <p class="text-base font-medium text-gray-900">{{ $payment->due_date->format('d M Y') }}</p>
                        </div>
                    </div>

                    @if($payment->description)
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Description</p>
                            <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $payment->description }}</p>
                        </div>
                    @endif

                    @if($payment->notes)
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Notes</p>
                            <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $payment->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment History -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold text-gray-800">Payment History</h2>
                </div>
                <div class="p-6">
                    @if($payment->transactions->count() > 0)
                        <div class="space-y-4">
                            @foreach($payment->transactions as $transaction)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">₹{{ number_format($transaction->amount, 2) }}</p>
                                        <p class="text-sm text-gray-600">{{ $transaction->transaction_number }} • {{ ucfirst($transaction->payment_method) }}</p>
                                        <p class="text-xs text-gray-500">{{ $transaction->transaction_date->format('d M Y') }}</p>
                                        @if($transaction->receiver)
                                            <p class="text-xs text-gray-500">Received by: {{ $transaction->receiver->name }}</p>
                                        @endif
                                        @if($transaction->reference_number)
                                            <p class="text-xs text-gray-500">Ref: {{ $transaction->reference_number }}</p>
                                        @endif
                                    </div>
                                </div>
                                @can('create', App\Models\Payment::class)
                                <a href="{{ route('payments.receipt', $transaction) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                    View Receipt
                                </a>
                                @endcan
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-2">No payments recorded yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Amount Summary Sidebar -->
        <div class="space-y-6">
            <!-- Amount Summary Card -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b">
                    <h2 class="text-lg font-semibold text-gray-800">Amount Summary</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Bill Amount</span>
                        <span class="font-medium text-gray-900">₹{{ number_format($payment->amount, 2) }}</span>
                    </div>

                    @if($payment->paid_amount > 0)
                    <div class="pt-4 border-t space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Paid Amount</span>
                            <span class="font-medium text-green-600">₹{{ number_format($payment->paid_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-900">Outstanding</span>
                            <span class="text-xl font-bold {{ $payment->due_amount > 0 ? 'text-orange-600' : 'text-green-600' }}">
                                ₹{{ number_format($payment->due_amount, 2) }}
                            </span>
                        </div>
                    </div>
                    @else
                    <div class="pt-4 border-t">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-900">Amount Due</span>
                            <span class="text-xl font-bold text-orange-600">₹{{ number_format($payment->due_amount, 2) }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            @if($payment->status != 'paid' && $payment->status != 'cancelled')
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-medium text-blue-900 mb-2">Quick Actions</h3>
                <div class="space-y-2">
                    @can('update', $payment)
                    <a href="{{ route('payments.record', $payment) }}" class="block w-full px-4 py-2 bg-blue-600 text-white text-center rounded-lg hover:bg-blue-700 transition">
                        Record Payment
                    </a>
                    @endcan
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection