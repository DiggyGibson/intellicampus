<?php

// ==============================================
// app/Models/SpecialRegistrationFlag.php
// ==============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialRegistrationFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'term_id',
        'flag_type',
        'flag_value',
        'authorized_by',
        'valid_from',
        'valid_until',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'flag_value' => 'array',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean'
    ];

    // Flag type constants
    const FLAG_LATE_REGISTRATION = 'late_registration';
    const FLAG_CONCURRENT_ENROLLMENT = 'concurrent_enrollment';
    const FLAG_AUDIT_ALLOWED = 'audit_allowed';
    const FLAG_TIME_CONFLICT_ALLOWED = 'time_conflict_allowed';
    const FLAG_CAPACITY_OVERRIDE = 'capacity_override';

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
     * Get the authorizer
     */
    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    /**
     * Check if flag is currently valid
     */
    public function isValid(): bool
    {
        $now = now();
        
        return $this->is_active 
            && (!$this->valid_from || $this->valid_from <= $now)
            && (!$this->valid_until || $this->valid_until >= $now);
    }

    /**
     * Scope for active flags
     */
    public function scopeActive($query)
    {
        $now = now();
        
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', $now);
            });
    }

    /**
     * Get human-readable flag type label
     */
    public function getFlagTypeLabelAttribute(): string
    {
        return match($this->flag_type) {
            self::FLAG_LATE_REGISTRATION => 'Late Registration',
            self::FLAG_CONCURRENT_ENROLLMENT => 'Concurrent Enrollment',
            self::FLAG_AUDIT_ALLOWED => 'Audit Allowed',
            self::FLAG_TIME_CONFLICT_ALLOWED => 'Time Conflict Allowed',
            self::FLAG_CAPACITY_OVERRIDE => 'Capacity Override',
            default => ucfirst(str_replace('_', ' ', $this->flag_type))
        };
    }
}