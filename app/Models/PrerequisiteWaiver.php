<?php

// ==============================================
// app/Models/PrerequisiteWaiver.php
// ==============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrerequisiteWaiver extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'waived_prerequisite_id',
        'term_id',
        'reason',
        'justification',
        'supporting_evidence',
        'approved_by',
        'approved_at',
        'expires_at',
        'is_active'
    ];

    protected $casts = [
        'supporting_evidence' => 'array',
        'approved_at' => 'datetime',
        'expires_at' => 'date',
        'is_active' => 'boolean'
    ];

    // Reason constants
    const REASON_EQUIVALENT = 'equivalent_course';
    const REASON_EXPERIENCE = 'professional_experience';
    const REASON_PLACEMENT = 'placement_test';
    const REASON_DEPARTMENT = 'department_override';

    /**
     * Get the student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the course
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the waived prerequisite course
     */
    public function waivedPrerequisite(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'waived_prerequisite_id');
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
     * Check if waiver is currently valid
     */
    public function isValid(): bool
    {
        return $this->is_active 
            && (!$this->expires_at || $this->expires_at->isFuture());
    }

    /**
     * Get human-readable reason label
     */
    public function getReasonLabelAttribute(): string
    {
        return match($this->reason) {
            self::REASON_EQUIVALENT => 'Equivalent Course',
            self::REASON_EXPERIENCE => 'Professional Experience',
            self::REASON_PLACEMENT => 'Placement Test',
            self::REASON_DEPARTMENT => 'Department Override',
            default => ucfirst(str_replace('_', ' ', $this->reason))
        };
    }
}
