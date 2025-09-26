<?php

// app/Models/UserDepartmentAffiliation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDepartmentAffiliation extends Model
{
    protected $fillable = [
        'user_id', 'department_id', 'affiliation_type', 'role',
        'appointment_percentage', 'start_date', 'end_date',
        'is_active', 'position_title', 'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'appointment_percentage' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Scope for active affiliations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(function($q) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', now());
                     });
    }

    /**
     * Scope for primary affiliations
     */
    public function scopePrimary($query)
    {
        return $query->where('affiliation_type', 'primary');
    }
}