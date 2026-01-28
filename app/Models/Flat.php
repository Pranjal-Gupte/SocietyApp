<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flat extends Model
{
    use HasFactory;

    protected $fillable = [
        'block_id',
        'flat_number',
        'full_number',
        'floor',
        'carpet_area',
        'bedrooms',
        'status',
        'owner_id',
    ];

    /**
     * Get the block this flat belongs to
     */
    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * Get the owner of this flat
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get all residents (including tenants) of this flat
     */
    public function residents()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if flat is occupied
     */
    public function isOccupied()
    {
        return $this->status === 'occupied';
    }

    /**
     * Check if flat is vacant
     */
    public function isVacant()
    {
        return $this->status === 'vacant';
    }
}