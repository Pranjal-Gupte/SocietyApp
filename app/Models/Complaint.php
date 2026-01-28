<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'complaint_number',
        'user_id',
        'flat_id',
        'category',
        'subject',
        'description',
        'priority',
        'status',
        'location',
        'attachments',
        'assigned_to',
        'admin_notes',
        'resolution_notes',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Boot method to auto-generate complaint number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($complaint) {
            if (!$complaint->complaint_number) {
                $complaint->complaint_number = self::generateComplaintNumber();
            }
        });
    }

    /**
     * Generate unique complaint number
     */
    public static function generateComplaintNumber()
    {
        $year = date('Y');
        $lastComplaint = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = $lastComplaint ? (int)substr($lastComplaint->complaint_number, -4) + 1 : 1;
        
        return 'CMP-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the user who filed the complaint
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the flat associated with complaint
     */
    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    /**
     * Get the user assigned to this complaint
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get all updates for this complaint
     */
    public function updates()
    {
        return $this->hasMany(ComplaintUpdate::class)->latest();
    }

    /**
     * Scope: Active complaints
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress', 'on_hold']);
    }

    /**
     * Scope: Resolved complaints
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope: By category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Check if complaint is active
     */
    public function isActive()
    {
        return in_array($this->status, ['pending', 'in_progress', 'on_hold']);
    }

    /**
     * Check if complaint is resolved
     */
    public function isResolved()
    {
        return $this->status === 'resolved' || $this->status === 'closed';
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'yellow',
            'in_progress' => 'blue',
            'on_hold' => 'orange',
            'resolved' => 'green',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get priority badge color
     */
    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Available categories
     */
    public static function categories()
    {
        return [
            'maintenance' => 'Maintenance',
            'plumbing' => 'Plumbing',
            'electrical' => 'Electrical',
            'cleaning' => 'Cleaning',
            'security' => 'Security',
            'parking' => 'Parking',
            'lift' => 'Lift/Elevator',
            'water_supply' => 'Water Supply',
            'garbage' => 'Garbage Collection',
            'noise' => 'Noise Complaint',
            'other' => 'Other',
        ];
    }
}