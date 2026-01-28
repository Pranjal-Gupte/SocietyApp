<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'flat_id',
        'phone',
        'address',
        'date_of_birth',
        'gender',
        'resident_type',
        'move_in_date',
        'is_active',
        'profile_photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'move_in_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the role of the user
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the flat of the user
     */
    public function flat()
    {
        return $this->belongsTo(Flat::class);
    }

    /**
     * Get the block through flat (as attribute, not relationship)
     */
    public function getBlockAttribute()
    {
        return $this->flat ? $this->flat->block : null;
    }

    /**
     * Get notices created by this user
     */
    public function notices()
    {
        return $this->hasMany(Notice::class, 'author_id');
    }

    /**
     * Get complaints filed by this user
     */
    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    /**
     * Get visitors registered by this user (for their flat)
     */
    public function visitors()
    {
        return $this->hasMany(Visitor::class);
    }

    /**
     * Get the block where user is chairman
     */
    public function managedBlock()
    {
        return $this->hasOne(Block::class, 'chairman_id');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is chairman
     */
    public function isChairman()
    {
        return $this->hasRole('chairman');
    }

    /**
     * Check if user is block chairman
     */
    public function isBlockChairman()
    {
        return $this->hasRole('block_chairman');
    }

    /**
     * Check if user is resident
     */
    public function isResident()
    {
        return $this->hasRole('resident');
    }

    /**
     * Check if user is tenant
     */
    public function isTenant()
    {
        return $this->hasRole('tenant');
    }

    /**
     * Check if user can manage society
     */
    public function canManageSociety()
    {
        return $this->isAdmin() || $this->isChairman();
    }

    /**
     * Get flat number with block
     */
    public function getFlatNumberAttribute()
    {
        return $this->flat ? $this->flat->full_number : null;
    }

    /**
     * Get user initials for avatar
     */
    public function initials()
    {
        $names = explode(' ', $this->name);
        if (count($names) > 1) {
            return strtoupper(substr($names[0], 0, 1) . substr($names[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
    }
}