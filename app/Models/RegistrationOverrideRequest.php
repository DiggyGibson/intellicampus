<?php
// app/Models/RegistrationOverrideRequest.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RegistrationOverrideRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'term_id',
        'request_type',
        'status',
        'requested_credits',
        'current_credits',
        'section_id',
        'course_id',
        'student_justification',
        'supporting_documents',
        'approver_id',
        'approver_role',
        'approval_date',
        'approver_notes',
        'conditions',
        'override_code',
        'override_used',
        'override_expires_at',
        'priority_level',
        'is_graduating_senior'
    ];

    protected $casts = [
        'supporting_documents' => 'array',
        'override_used' => 'boolean',
        'is_graduating_senior' => 'boolean',
        'approval_date' => 'datetime',
        'override_expires_at' => 'datetime'
    ];

    // Request type constants
    const TYPE_CREDIT_OVERLOAD = 'credit_overload';
    const TYPE_PREREQUISITE = 'prerequisite';
    const TYPE_CAPACITY = 'capacity';
    const TYPE_TIME_CONFLICT = 'time_conflict';
    const TYPE_LATE_REGISTRATION = 'late_registration';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_DENIED = 'denied';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the student that owns the request
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the academic term
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    /**
     * Get the course section (if applicable)
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class);
    }

    /**
     * Get the course (if applicable)
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Get human-readable type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->request_type) {
            self::TYPE_CREDIT_OVERLOAD => 'Credit Overload',
            self::TYPE_PREREQUISITE => 'Prerequisite Waiver',
            self::TYPE_CAPACITY => 'Capacity Override',
            self::TYPE_TIME_CONFLICT => 'Time Conflict Override',
            self::TYPE_LATE_REGISTRATION => 'Late Registration',
            default => ucfirst(str_replace('_', ' ', $this->request_type))
        };
    }

    /**
     * Get badge class for type
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return match($this->request_type) {
            self::TYPE_CREDIT_OVERLOAD => 'info',
            self::TYPE_PREREQUISITE => 'warning',
            self::TYPE_CAPACITY => 'primary',
            self::TYPE_TIME_CONFLICT => 'danger',
            self::TYPE_LATE_REGISTRATION => 'secondary',
            default => 'light'
        };
    }

    /**
     * Check if override code is valid
     */
    public function isOverrideCodeValid(): bool
    {
        return $this->status === self::STATUS_APPROVED 
            && !$this->override_used 
            && $this->override_expires_at 
            && $this->override_expires_at->isFuture();
    }

    /**
     * Mark override as used
     */
    public function markAsUsed(): void
    {
        $this->override_used = true;
        $this->save();
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for requests needing attention (old pending requests)
     */
    public function scopeNeedingAttention($query, $days = 3)
    {
        return $query->pending()
            ->where('created_at', '<=', Carbon::now()->subDays($days));
    }
}