<?php

// ===================================================================
// File: app/Models/AssignmentSubmission.php
// ===================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'group_id',
        'attempt_number',
        'submission_text',
        'submission_files',
        'score',
        'weighted_score',
        'feedback',
        'status',
        'is_late',
        'submitted_at',
        'graded_at',
        'graded_by'
    ];

    protected $casts = [
        'attempt_number' => 'integer',
        'submission_files' => 'array',
        'score' => 'decimal:2',
        'weighted_score' => 'decimal:2',
        'is_late' => 'boolean',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime'
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AssignmentGroup::class, 'group_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function submit(): bool
    {
        $this->status = 'submitted';
        $this->submitted_at = now();
        
        // Check if late
        if ($this->submitted_at->gt($this->assignment->due_date)) {
            $this->is_late = true;
        }
        
        return $this->save();
    }

    public function grade($score, $feedback = null, $graderId = null): bool
    {
        $this->score = $score;
        $this->feedback = $feedback;
        $this->graded_by = $graderId ?? auth()->id();
        $this->graded_at = now();
        $this->status = 'graded';
        
        // Calculate weighted score if late
        if ($this->is_late && $this->assignment->allow_late) {
            $penalty = $this->assignment->calculateLatePenalty($this->submitted_at);
            $this->weighted_score = $score * (1 - $penalty);
        } else {
            $this->weighted_score = $score;
        }
        
        return $this->save();
    }

    public function return(): bool
    {
        $this->status = 'returned';
        return $this->save();
    }

    public function getPercentageScore(): float
    {
        if (!$this->score || !$this->assignment->max_points) {
            return 0;
        }
        
        return ($this->weighted_score ?? $this->score) / $this->assignment->max_points * 100;
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }
}
