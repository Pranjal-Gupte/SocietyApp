<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'flat_id',
        'user_id',
        'payment_type',
        'title',
        'description',
        'amount',
        'paid_amount',
        'due_amount',
        'bill_date',
        'due_date',
        'status',
        'payment_method',
        'transaction_id',
        'payment_date',
        'notes',
        'created_by',
        'received_by',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    /**
     * Boot method to auto-generate payment number and update due amount
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->payment_number) {
                $payment->payment_number = self::generatePaymentNumber();
            }
            
            // Set initial due amount to full amount
            if (!isset($payment->due_amount)) {
                $payment->due_amount = $payment->amount;
            }
        });

        static::updating(function ($payment) {
            // Update due amount
            $payment->due_amount = $payment->amount - $payment->paid_amount;
            
            // Update status based on payment
            if ($payment->paid_amount >= $payment->amount) {
                $payment->status = 'paid';
            } elseif ($payment->paid_amount > 0) {
                $payment->status = 'partial';
            } elseif ($payment->due_date < now() && $payment->status != 'cancelled') {
                $payment->status = 'overdue';
            }
        });
    }

    /**
     * Generate unique payment number
     */
    public static function generatePaymentNumber()
    {
        $year = date('Y');
        $lastPayment = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastPayment ? (int)substr($lastPayment->payment_number, -4) + 1 : 1;
        
        return 'PAY-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the flat this payment belongs to
     */
    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    /**
     * Get the user (flat owner) this payment is for
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get who created this payment
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get who received this payment
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get all transactions for this payment
     */
    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /**
     * Check if payment is fully paid
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    /**
     * Check if payment is overdue
     */
    public function isOverdue()
    {
        return $this->status === 'overdue' || 
               ($this->status !== 'paid' && $this->due_date < now());
    }

    /**
     * Check if payment is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Scope: Get overdue payments
     */
    public function scopeOverdue($query)
    {
        return $query->where(function($q) {
            $q->where('status', 'overdue')
              ->orWhere(function($q2) {
                  $q2->whereIn('status', ['pending', 'partial'])
                     ->where('due_date', '<', now());
              });
        });
    }

    /**
     * Scope: Get pending payments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get paid payments
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'paid' => 'green',
            'partial' => 'yellow',
            'pending' => 'blue',
            'overdue' => 'red',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get payment type display name
     */
    public function getPaymentTypeDisplayAttribute()
    {
        return match($this->payment_type) {
            'maintenance' => 'Maintenance',
            'water' => 'Water Charges',
            'parking' => 'Parking Charges',
            'penalty' => 'Penalty',
            'other' => 'Other Charges',
            default => ucfirst($this->payment_type),
        };
    }

    /**
     * Get outstanding amount (same as due_amount for compatibility)
     */
    public function getOutstandingAmountAttribute()
    {
        return $this->due_amount;
    }

    /**
     * Get total paid amount (same as paid_amount for compatibility)
     */
    public function getTotalPaidAttribute()
    {
        return $this->paid_amount;
    }
}