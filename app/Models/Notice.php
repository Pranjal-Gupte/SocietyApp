<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'priority',
        'status',
        'author_id',
        'block_id',
        'valid_from',
        'valid_until',
        'attachment',
        'send_notification',
        'published_at',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
        'published_at' => 'datetime',
        'send_notification' => 'boolean',
    ];

    /**
     * Get the author of the notice
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the block this notice is for (null = all blocks)
     */
    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * Scope: Get only published notices
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    /**
     * Scope: Get recent notices
     */
    public function scopeRecent($query, $limit = 5)
    {
        return $query->published()
                     ->latest('published_at')
                     ->limit($limit);
    }

    /**
     * Scope: Get notices for a specific block or all blocks
     */
    public function scopeForBlock($query, $blockId)
    {
        return $query->where(function($q) use ($blockId) {
            $q->whereNull('block_id')
              ->orWhere('block_id', $blockId);
        });
    }

    /**
     * Check if notice is high priority
     */
    public function isHighPriority()
    {
        return $this->priority === 'high';
    }

    /**
     * Check if notice is still valid
     */
    public function isValid()
    {
        if (!$this->valid_until) {
            return true;
        }
        return $this->valid_until->isFuture();
    }
}