<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'block_number',
        'total_floors',
        'flats_per_floor',
        'chairman_id',
        'description',
    ];

    /**
     * Get the chairman of this block
     */
    public function chairman()
    {
        return $this->belongsTo(User::class, 'chairman_id');
    }

    /**
     * Get all flats in this block
     */
    public function flats()
    {
        return $this->hasMany(Flat::class);
    }

    /**
     * Get all notices for this block
     */
    public function notices()
    {
        return $this->hasMany(Notice::class);
    }

    /**
     * Get occupied flats count
     */
    public function getOccupiedFlatsCountAttribute()
    {
        return $this->flats()->where('status', 'occupied')->count();
    }

    /**
     * Get vacant flats count
     */
    public function getVacantFlatsCountAttribute()
    {
        return $this->flats()->where('status', 'vacant')->count();
    }
}