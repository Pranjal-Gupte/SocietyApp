<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_id',
        'user_id',
        'message',
        'old_status',
        'new_status',
    ];

    /**
     * Get the complaint this update belongs to
     */
    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    /**
     * Get the user who created this update
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is a status change update
     */
    public function isStatusChange()
    {
        return $this->old_status !== null && $this->new_status !== null;
    }
}