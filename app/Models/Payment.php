<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'transaction_number',
        'amount',
        'payment_method',
        'reference_number',
        'transaction_date',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Boot method to auto-generate transaction number and update payment
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (!$transaction->transaction_number) {
                $transaction->transaction_number = self::generateTransactionNumber();
            }
        });

        static::created(function ($transaction) {
            // Update payment paid amount
            $payment = $transaction->payment;
            $payment->paid_amount += $transaction->amount;
            
            // Update payment method and date if first transaction
            if ($payment->transactions()->count() == 1) {
                $payment->payment_method = $transaction->payment_method;
                $payment->payment_date = $transaction->transaction_date;
            }
            
            $payment->save();
        });
    }

    /**
     * Generate unique transaction number
     */
    public static function generateTransactionNumber()
    {
        $year = date('Y');
        $lastTransaction = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastTransaction ? (int)substr($lastTransaction->transaction_number, -4) + 1 : 1;
        
        return 'TXN-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the payment
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get who received this transaction
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}