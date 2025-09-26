<?php

// ==============================================
// app/Models/CreditOverloadPermission.php
// ==============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditOverloadPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'term_id',
        'max_credits',
        'approved_by',
        'approved_at',
        'reason',
        'conditions',
        'valid_until',
        'is_active'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'valid_until' => 'date',
        'is_active' => 'boolean'
    ];

    /**
     * Get the student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the term
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if permission is currently valid
     */
    public function isValid(): bool
    {
        return $this->is_active 
            && (!$this->valid_until || $this->valid_until->isFuture());
    }

    /**
     * Scope for active permissions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', now());
            });
    }
}
