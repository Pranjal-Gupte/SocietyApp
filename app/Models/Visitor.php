<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visitor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'gate_pass_code',
        'user_id',
        'flat_id',
        'visitor_name',
        'visitor_phone',
        'visitor_email',
        'visitor_type',
        'purpose',
        'number_of_persons',
        'vehicle_number',
        'vehicle_type',
        'expected_date',
        'expected_time',
        'status',
        'check_in_time',
        'check_out_time',
        'checked_in_by',
        'checked_out_by',
        'notes',
        'is_frequent_visitor',
    ];

    protected $casts = [
        'expected_date' => 'date',
        'expected_time' => 'datetime',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'is_frequent_visitor' => 'boolean',
    ];

    /**
     * Boot method to auto-generate gate pass code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($visitor) {
            if (!$visitor->gate_pass_code) {
                $visitor->gate_pass_code = self::generateGatePassCode();
            }
        });
    }

    /**
     * Generate unique gate pass code
     */
    public static function generateGatePassCode()
    {
        $year = date('Y');
        $lastVisitor = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastVisitor ? (int)substr($lastVisitor->gate_pass_code, -4) + 1 : 1;
        
        return 'GP-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the resident who registered this visitor
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the flat
     */
    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    /**
     * Get the security guard who checked in
     */
    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * Get the security guard who checked out
     */
    public function checkedOutBy()
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    /**
     * Scope: Today's visitors
     */
    public function scopeToday($query)
    {
        return $query->whereDate('expected_date', today());
    }

    /**
     * Scope: Expected visitors (approved and ready to check in)
     */
    public function scopeExpected($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Pending approval from resident
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    /**
     * Scope: Currently inside (checked in but not out)
     */
    public function scopeInside($query)
    {
        return $query->where('status', 'checked_in');
    }

    /**
     * Check if visitor is currently inside
     */
    public function isInside()
    {
        return $this->status === 'checked_in';
    }

    /**
     * Check if visitor has left
     */
    public function hasLeft()
    {
        return $this->status === 'checked_out';
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending_approval' => 'orange',
            'approved' => 'blue',
            'checked_in' => 'green',
            'checked_out' => 'gray',
            'rejected' => 'red',
            'cancelled' => 'red',
            'expired' => 'yellow',
            default => 'gray',
        };
    }

    /**
     * Get visitor type badge color
     */
    public function getTypeColorAttribute()
    {
        return match($this->visitor_type) {
            'guest' => 'purple',
            'delivery' => 'yellow',
            'service' => 'blue',
            'cab' => 'green',
            'family' => 'pink',
            'other' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Available visitor types
     */
    public static function visitorTypes()
    {
        return [
            'guest' => 'Guest',
            'delivery' => 'Delivery Person',
            'service' => 'Service Provider',
            'cab' => 'Cab/Taxi',
            'family' => 'Family Member',
            'other' => 'Other',
        ];
    }

    /**
     * Check if visitor is expected today
     */
    public function isExpectedToday()
    {
        return $this->expected_date->isToday();
    }

    /**
     * Check if visitor pass is expired
     */
    public function isExpired()
    {
        return $this->expected_date->isPast() && $this->status === 'approved';
    }
}