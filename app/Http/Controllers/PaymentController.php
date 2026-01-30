<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Flat;
use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display payments dashboard
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Payment::with(['flat.block', 'user', 'transactions']);
        
        // Regular users only see their own payments
        if (!$user->canManageSociety() && !$user->isBlockChairman()) {
            $query->where('user_id', $user->id);
        }
        
        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            if ($request->status == 'overdue') {
                $query->overdue();
            } else {
                $query->where('status', $request->status);
            }
        }
        
        // Filter by type
        if ($request->has('type') && $request->type != 'all') {
            $query->where('payment_type', $request->type);
        }
        
        // Filter by month
        if ($request->has('month') && $request->month) {
            $query->whereDate('bill_date', '>=', $request->month . '-01')
                  ->whereDate('bill_date', '<=', date('Y-m-t', strtotime($request->month . '-01')));
        }
        
        // Filter by flat (admin only)
        if ($request->has('flat') && $request->flat != 'all' && $user->canManageSociety()) {
            $query->where('flat_id', $request->flat);
        }
        
        // Search by payment number
        if ($request->has('search') && $request->search) {
            $query->where('payment_number', 'like', '%' . $request->search . '%');
        }
        
        $payments = $query->latest('bill_date')->latest('created_at')->paginate(20);
        
        // Calculate statistics
        $stats = [
            'total_pending' => 0,
            'total_overdue' => 0,
            'total_paid_this_month' => 0,
            'pending_count' => 0,
        ];
        
        if ($user->canManageSociety()) {
            // Admin stats
            $stats['total_pending'] = Payment::whereIn('status', ['pending', 'partial'])->sum('due_amount');
            $stats['total_overdue'] = Payment::overdue()->sum('due_amount');
            $stats['total_paid_this_month'] = PaymentTransaction::whereMonth('transaction_date', now()->month)
                                                               ->whereYear('transaction_date', now()->year)
                                                               ->sum('amount');
            $stats['pending_count'] = Payment::whereIn('status', ['pending', 'partial', 'overdue'])->count();
        } else {
            // User stats
            $userPayments = Payment::where('user_id', $user->id);
            $stats['total_pending'] = $userPayments->clone()->whereIn('status', ['pending', 'partial'])->sum('due_amount');
            $stats['total_overdue'] = $userPayments->clone()->overdue()->sum('due_amount');
            $stats['pending_count'] = $userPayments->clone()->whereIn('status', ['pending', 'partial', 'overdue'])->count();
        }
        
        // Get flats for filter (admin only)
        $flats = $user->canManageSociety() ? Flat::with('block')->get() : collect();
        
        return view('payments.index', compact('payments', 'stats', 'flats'));
    }

    /**
     * Show payment details
     */
    public function show(Payment $bill)
    {
        $user = auth()->user();
        
        // Check permission
        if (!$user->canManageSociety() && !$user->isBlockChairman() && $bill->user_id != $user->id) {
            abort(403, 'You do not have permission to view this payment.');
        }
        
        $bill->load(['flat.block', 'user', 'creator', 'receiver', 'transactions.receiver']);
        
        return view('payments.show', ['payment' => $bill]);
    }

    /**
     * Show create payment form
     */
    public function create()
    {
        $this->authorize('create', Payment::class);
        
        $flats = Flat::with(['block', 'owner'])->get();
        
        return view('payments.create', compact('flats'));
    }

    /**
     * Store a new payment
     */
    public function store(Request $request)
    {
        $this->authorize('create', Payment::class);
        
        $validated = $request->validate([
            'flat_id' => 'required|exists:flats,id',
            'payment_type' => 'required|in:maintenance,water,electricity,parking,amenity,penalty,other',
            'amount' => 'required|numeric|min:0',
            'billing_month' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'notes' => 'nullable|string',
        ]);
        
        $flat = Flat::find($validated['flat_id']);
        
        if (!$flat->owner_id) {
            return back()->with('error', 'This flat has no owner assigned. Cannot create bill.');
        }
        
        $data = [
        'flat_id'      => $validated['flat_id'],
        'payment_type' => $validated['payment_type'],
        'amount'       => $validated['amount'],
        'due_date'     => $validated['due_date'],
        'description'  => $validated['notes'], // mapping notes to description column
        'bill_date'    => $validated['billing_month'] . '-01', // mapping month to a date string
        'title'        => ucfirst($validated['payment_type']) . " - " . $validated['billing_month'], // Generating a title since form doesn't have one
        'user_id'      => $flat->owner_id,
        'created_by'   => auth()->id(),
        'paid_amount'  => 0,
        'due_amount'   => $validated['amount'],
        'status'       => 'pending',
        ];
        
        $payment = Payment::create($data);
        
        return redirect()->route('payments.show', $payment)
            ->with('success', 'Bill created successfully! Bill Number: ' . $payment->payment_number);
    }

    /**
     * Show payment recording form
     */
    public function recordPaymentForm(Payment $bill)
    {
        $this->authorize('update', $bill);
        
        $bill->load(['flat.block', 'user']);
        
        return view('payments.record', ['payment' => $bill]);
    }

    /**
     * Record a payment transaction
     */
    public function recordPayment(Request $request, Payment $bill)
    {
        $this->authorize('update', $bill);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $bill->due_amount,
            'payment_method' => 'required|in:online,cash,cheque,bank_transfer,upi,other',
            'reference_number' => 'nullable|string|max:255',
            'transaction_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string',
        ]);
        
        $validated['payment_id'] = $bill->id;
        $validated['received_by'] = auth()->id();
        
        $transaction = PaymentTransaction::create($validated);
        
        return redirect()->route('payments.show', $bill)
            ->with('success', 'Payment recorded successfully! Transaction ID: ' . $transaction->transaction_number);
    }

    /**
     * Transaction history
     */
    public function transactions(Request $request)
    {
        $user = auth()->user();
        
        $query = PaymentTransaction::with(['payment.flat.block', 'payment.user', 'receiver']);
        
        // Regular users only see their own transactions
        if (!$user->canManageSociety()) {
            $query->whereHas('payment', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        // Filter by payment method
        if ($request->has('method') && $request->method != 'all') {
            $query->where('payment_method', $request->method);
        }
        
        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('transaction_date', '>=', $request->start_date);
        }
        
        if ($request->has('end_date') && $request->end_date) {
            $query->where('transaction_date', '<=', $request->end_date);
        }
        
        // Search by transaction number
        if ($request->has('search') && $request->search) {
            $query->where('transaction_number', 'like', '%' . $request->search . '%');
        }
        
        $transactions = $query->latest('transaction_date')->paginate(20);
        
        // Calculate total
        $totalAmount = $query->sum('amount');
        
        return view('payments.transactions', compact('transactions', 'totalAmount'));
    }

    /**
     * Outstanding report
     */
    public function outstanding(Request $request)
    {
        $this->authorize('create', Payment::class);
        
        $query = Payment::with(['flat.block', 'user'])
            ->whereIn('status', ['pending', 'partial', 'overdue']);
        
        // Filter by block
        if ($request->has('block') && $request->block != 'all') {
            $query->whereHas('flat', function($q) use ($request) {
                $q->where('block_id', $request->block);
            });
        }
        
        $payments = $query->latest('due_date')->get();
        
        $stats = [
            'total_outstanding' => $payments->sum('due_amount'),
            'overdue_count' => $payments->where('status', 'overdue')->count(),
            'pending_count' => $payments->where('status', 'pending')->count(),
        ];
        
        $blocks = Block::all();
        
        return view('payments.outstanding', compact('payments', 'stats', 'blocks'));
    }

    /**
     * Show bulk bill generation form
     */
    public function bulkGenerateForm()
    {
        $this->authorize('create', Payment::class);
        
        $flats = Flat::with(['block', 'owner'])->whereNotNull('owner_id')->get();
        $blocks = Block::all();
        
        return view('payments.bulk-generate', compact('flats', 'blocks'));
    }

    /**
     * Bulk generate bills
     */
    public function bulkGenerate(Request $request)
    {
        $this->authorize('create', Payment::class);
        
        $validated = $request->validate([
            'payment_type' => 'required|in:maintenance,water,parking,penalty,other,electricity,amenity',
            'billing_month' => 'required',
            'notes' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date|',
            'selection_type' => 'required|in:all,block,specific',
            'block_ids' => 'required_if:selection_type,block|array',
            'block_ids.*' => 'exists:blocks,id',
            'flat_ids' => 'nullable|array',
            'flat_ids.*' => 'exists:flats,id',
        ]);
        
        // Determine which flats to bill based on selection_type
        $query = Flat::whereNotNull('owner_id');

        if ($validated['selection_type'] === 'specific') {
            $query->whereIn('id', $validated['flat_ids']);
        } elseif ($validated['selection_type'] === 'block') {
            $query->whereIn('block_id', $validated['block_ids']);
        }

        $flats = $query->get();

        if ($flats->isEmpty()) {
            return back()->with('error', 'No flats with owners found for the selection.');
        }

        // Generate Bills within a Transaction
        $count = \DB::transaction(function () use ($flats, $validated) {
            $count = 0;
            foreach ($flats as $flat) {
                Payment::create([
                    'flat_id' => $flat->id,
                    'user_id' => $flat->owner_id,
                    'payment_type' => $validated['payment_type'],
                    'title' => ucfirst($validated['payment_type']) . " - " . $validated['billing_month'],
                    'description' => $validated['notes'] ?? null,
                    'amount' => $validated['amount'],
                    'bill_date' => now(), 
                    'due_date' => $validated['due_date'],
                    'created_by' => auth()->id(),
                    'paid_amount' => 0,
                    'due_amount' => $validated['amount'],
                    'status' => 'pending',
                ]);
                $count++;
            }
            return $count;
        });

        return redirect()->route('payments.index')
            ->with('success', "{$count} bills generated successfully!");
    }

    /**
     * Cancel a payment
     */
    public function cancel(Payment $payment)
    {
        $this->authorize('delete', $payment);
        
        if ($payment->paid_amount > 0) {
            return back()->with('error', 'Cannot cancel payment with transactions.');
        }
        
        $payment->status = 'cancelled';
        $payment->save();
        
        return back()->with('success', 'Payment cancelled successfully.');
    }

    /**
     * Download payment receipt
     */
    public function downloadReceipt(PaymentTransaction $transaction)
    {
        $user = auth()->user();
        
        if (!$user->canManageSociety() && $transaction->payment->user_id !== $user->id) {
            abort(403);
        }
        
        $transaction->load(['payment.flat.block', 'payment.user', 'receiver']);
        
        return view('payments.receipt', compact('transaction'));
    }
}