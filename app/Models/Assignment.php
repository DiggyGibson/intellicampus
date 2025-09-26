<?php

// ===================================================================
// File: app/Models/Assignment.php
// ===================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_site_id',
        'title',
        'instructions',
        'max_points',
        'submission_type',
        'allow_late',
        'late_penalty_percent',
        'due_date',
        'available_from',
        'available_until',
        'max_attempts',
        'is_group_assignment',
        'group_size',
        'allowed_file_types',
        'max_file_size',
        'is_visible',
        'use_rubric'
    ];

    protected $casts = [
        'max_points' => 'decimal:2',
        'allow_late' => 'boolean',
        'late_penalty_percent' => 'integer',
        'due_date' => 'datetime',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
        'max_attempts' => 'integer',
        'is_group_assignment' => 'boolean',
        'group_size' => 'integer',
        'allowed_file_types' => 'array',
        'max_file_size' => 'integer',
        'is_visible' => 'boolean',
        'use_rubric' => 'boolean'
    ];

    public function courseSite(): BelongsTo
    {
        return $this->belongsTo(CourseSite::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(AssignmentGroup::class);
    }

    public function rubric(): HasOne
    {
        return $this->hasOne(Rubric::class);
    }

    public function getSubmissionForStudent($studentId)
    {
        return $this->submissions()
            ->where('student_id', $studentId)
            ->latest('attempt_number')
            ->first();
    }

    public function isAvailable(): bool
    {
        $now = now();
        
        if (!$this->is_visible) {
            return false;
        }
        
        if ($this->available_from && $now->lt($this->available_from)) {
            return false;
        }
        
        if ($this->available_until && $now->gt($this->available_until)) {
            return false;
        }
        
        return true;
    }

    public function isOverdue(): bool
    {
        return now()->gt($this->due_date);
    }

    public function canSubmit($studentId): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }
        
        if ($this->isOverdue() && !$this->allow_late) {
            return false;
        }
        
        $submissionCount = $this->submissions()
            ->where('student_id', $studentId)
            ->count();
            
        if ($submissionCount >= $this->max_attempts) {
            return false;
        }
        
        return true;
    }

    public function calculateLatePenalty($submittedAt = null): float
    {
        if (!$this->allow_late || !$this->late_penalty_percent) {
            return 0;
        }
        
        $submittedAt = $submittedAt ?? now();
        
        if ($submittedAt->lte($this->due_date)) {
            return 0;
        }
        
        // Calculate days late
        $daysLate = $submittedAt->diffInDays($this->due_date);
        
        // Apply penalty per day (max 100%)
        $penalty = min($daysLate * $this->late_penalty_percent, 100);
        
        return $penalty / 100;
    }

    public function getStatistics(): array
    {
        $submissions = $this->submissions()->where('status', 'submitted');
        
        return [
            'total_submissions' => $submissions->count(),
            'graded_submissions' => $submissions->where('status', 'graded')->count(),
            'average_score' => $submissions->avg('score') ?? 0,
            'highest_score' => $submissions->max('score') ?? 0,
            'lowest_score' => $submissions->min('score') ?? 0,
            'on_time_submissions' => $submissions->where('is_late', false)->count(),
            'late_submissions' => $submissions->where('is_late', true)->count()
        ];
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>', now())
            ->orderBy('due_date');
    }

    public function scopePast($query)
    {
        return $query->where('due_date', '<=', now())
            ->orderBy('due_date', 'desc');
    }
}
