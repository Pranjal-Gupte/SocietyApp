<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Flat;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Payment::with(['flat.block', 'user']);
        
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
            $query->whereMonth('bill_date', $request->month);
        }
        
        $payments = $query->latest('bill_date')->paginate(20);
        
        // Get stats
        $stats = [
            'total_pending' => Payment::where('status', 'pending')->sum('due_amount'),
            'total_overdue' => Payment::overdue()->sum('due_amount'),
            'total_collected' => Payment::whereMonth('payment_date', now()->month)->sum('paid_amount'),
        ];
        
        return view('payments.index', compact('payments', 'stats'));
    }

    /**
     * Show the form for creating a new payment
     */
    public function create()
    {
        $this->authorize('create', Payment::class);
        
        $flats = Flat::with('owner')->get();
        
        return view('payments.create', compact('flats'));
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request)
    {
        $this->authorize('create', Payment::class);
        
        $validated = $request->validate([
            'flat_id' => 'required|exists:flats,id',
            'payment_type' => 'required|in:maintenance,water,parking,penalty,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
        ]);
        
        $flat = Flat::find($validated['flat_id']);
        
        if (!$flat->owner_id) {
            return back()->with('error', 'This flat has no registered owner. Cannot create bill.');
        }
        
        $validated['user_id'] = $flat->owner_id;
        $validated['created_by'] = auth()->id();
        $validated['paid_amount'] = 0;
        $validated['due_amount'] = $validated['amount'];
        $validated['status'] = 'pending';
        
        $payment = Payment::create($validated);
        
        return redirect()->route('payments.show', $payment)
            ->with('success', 'Payment bill created successfully! Bill Number: ' . $payment->payment_number);
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment)
    {
        $user = auth()->user();
        
        // Check permission
        if (!$user->canManageSociety() && !$user->isBlockChairman() && $payment->user_id != $user->id) {
            abort(403, 'You do not have permission to view this payment.');
        }
        
        $payment->load(['flat.block', 'user', 'creator', 'receiver', 'transactions.receiver']);
        
        return view('payments.show', compact('payment'));
    }

    /**
     * Record a payment transaction
     */
    public function recordPayment(Request $request, Payment $payment)
    {
        $this->authorize('update', $payment);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $payment->due_amount,
            'payment_method' => 'required|in:online,cash,cheque,bank_transfer,upi,other',
            'reference_number' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);
        
        $validated['payment_id'] = $payment->id;
        $validated['received_by'] = auth()->id();
        
        PaymentTransaction::create($validated);
        
        return back()->with('success', 'Payment recorded successfully!');
    }

    /**
     * Bulk bill generation for all flats
     */
    public function bulkCreate()
    {
        $this->authorize('create', Payment::class);
        
        return view('payments.bulk-create');
    }

    /**
     * Store bulk bills
     */
    public function bulkStore(Request $request)
    {
        $this->authorize('create', Payment::class);
        
        $validated = $request->validate([
            'payment_type' => 'required|in:maintenance,water,parking,penalty,other',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
            'block_id' => 'nullable|exists:blocks,id',
        ]);
        
        $query = Flat::with('owner')->whereNotNull('owner_id');
        
        if ($request->block_id) {
            $query->where('block_id', $request->block_id);
        }
        
        $flats = $query->get();
        
        $count = 0;
        foreach ($flats as $flat) {
            Payment::create([
                'flat_id' => $flat->id,
                'user_id' => $flat->owner_id,
                'payment_type' => $validated['payment_type'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'amount' => $validated['amount'],
                'bill_date' => $validated['bill_date'],
                'due_date' => $validated['due_date'],
                'created_by' => auth()->id(),
                'paid_amount' => 0,
                'due_amount' => $validated['amount'],
                'status' => 'pending',
            ]);
            $count++;
        }
        
        return redirect()->route('payments.index')
            ->with('success', "Successfully created {$count} bills!");
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
}