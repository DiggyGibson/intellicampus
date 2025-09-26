<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequirementSubstitution extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'requirement_id',
        'original_course_id',
        'substitute_course_id',
        'reason',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'approval_notes'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'approved_at' => 'datetime'
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_DENIED = 'denied';
    const STATUS_REVOKED = 'revoked';

    /**
     * Get the student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the requirement
     */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(DegreeRequirement::class);
    }

    /**
     * Get the original course
     */
    public function originalCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'original_course_id');
    }

    /**
     * Get the substitute course
     */
    public function substituteCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'substitute_course_id');
    }

    /**
     * Get the user who requested the substitution
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved the substitution
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for pending substitutions
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved substitutions
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for denied substitutions
     */
    public function scopeDenied($query)
    {
        return $query->where('status', self::STATUS_DENIED);
    }

    /**
     * Check if substitution is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if substitution is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if substitution is denied
     */
    public function isDenied(): bool
    {
        return $this->status === self::STATUS_DENIED;
    }

    /**
     * Check if substitution is revoked
     */
    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    /**
     * Approve the substitution
     */
    public function approve(User $approver, string $notes = null): void
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        
        if ($notes) {
            $this->approval_notes = $notes;
        }
        
        $this->save();
    }

    /**
     * Deny the substitution
     */
    public function deny(User $approver, string $notes = null): void
    {
        $this->status = self::STATUS_DENIED;
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        
        if ($notes) {
            $this->approval_notes = $notes;
        }
        
        $this->save();
    }

    /**
     * Revoke the substitution
     */
    public function revoke(string $notes = null): void
    {
        $this->status = self::STATUS_REVOKED;
        
        if ($notes) {
            $this->approval_notes = $notes;
        }
        
        $this->save();
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'Pending Approval';
            case self::STATUS_APPROVED:
                return 'Approved';
            case self::STATUS_DENIED:
                return 'Denied';
            case self::STATUS_REVOKED:
                return 'Revoked';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'warning';
            case self::STATUS_APPROVED:
                return 'success';
            case self::STATUS_DENIED:
                return 'danger';
            case self::STATUS_REVOKED:
                return 'secondary';
            default:
                return 'light';
        }
    }
}