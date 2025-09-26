<?php

// ===================================================================
// File: app/Models/Quiz.php
// ===================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_site_id',
        'title',
        'instructions',
        'type',
        'time_limit',
        'max_attempts',
        'max_points',
        'shuffle_questions',
        'shuffle_answers',
        'show_results',
        'show_correct_answers',
        'available_from',
        'available_until',
        'is_visible',
        'use_lockdown_browser',
        'password'
    ];

    protected $casts = [
        'time_limit' => 'integer',
        'max_attempts' => 'integer',
        'max_points' => 'decimal:2',
        'shuffle_questions' => 'boolean',
        'shuffle_answers' => 'boolean',
        'show_results' => 'boolean',
        'show_correct_answers' => 'boolean',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
        'is_visible' => 'boolean',
        'use_lockdown_browser' => 'boolean'
    ];

    protected $hidden = ['password'];

    public function courseSite(): BelongsTo
    {
        return $this->belongsTo(CourseSite::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function getAttemptForStudent($studentId)
    {
        return $this->attempts()
            ->where('student_id', $studentId)
            ->latest('attempt_number')
            ->first();
    }

    public function canAttempt($studentId): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }
        
        $attemptCount = $this->attempts()
            ->where('student_id', $studentId)
            ->where('status', 'completed')
            ->count();
            
        return $attemptCount < $this->max_attempts;
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

    public function startAttempt($studentId): QuizAttempt
    {
        $attemptNumber = $this->attempts()
            ->where('student_id', $studentId)
            ->max('attempt_number') ?? 0;
            
        return $this->attempts()->create([
            'student_id' => $studentId,
            'attempt_number' => $attemptNumber + 1,
            'started_at' => now(),
            'status' => 'in_progress',
            'answers' => []
        ]);
    }

    public function getStatistics(): array
    {
        $completedAttempts = $this->attempts()->where('status', 'completed');
        
        return [
            'total_attempts' => $completedAttempts->count(),
            'unique_students' => $completedAttempts->distinct('student_id')->count('student_id'),
            'average_score' => $completedAttempts->avg('score') ?? 0,
            'average_percentage' => $completedAttempts->avg('percentage') ?? 0,
            'highest_score' => $completedAttempts->max('score') ?? 0,
            'lowest_score' => $completedAttempts->min('score') ?? 0,
            'average_time' => $completedAttempts->avg('time_taken') ?? 0
        ];
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeAvailable($query)
    {
        $now = now();
        return $query->where('is_visible', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('available_from')
                  ->orWhere('available_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('available_until')
                  ->orWhere('available_until', '>=', $now);
            });
    }
}
