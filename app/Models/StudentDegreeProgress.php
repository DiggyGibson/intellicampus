<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDegreeProgress extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'student_degree_progress';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'program_requirement_id',
        'requirement_id',
        'credits_completed',
        'credits_in_progress',
        'credits_remaining',
        'courses_completed',
        'courses_in_progress',
        'courses_remaining',
        'status',
        'completion_percentage',
        'is_satisfied',
        'requirement_gpa',
        'gpa_met',
        'notes',
        'manually_cleared',
        'cleared_by',
        'cleared_at',
        'last_calculated_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'credits_completed' => 'decimal:1',
        'credits_in_progress' => 'decimal:1',
        'credits_remaining' => 'decimal:1',
        'courses_completed' => 'integer',
        'courses_in_progress' => 'integer',
        'courses_remaining' => 'integer',
        'completion_percentage' => 'decimal:2',
        'is_satisfied' => 'boolean',
        'requirement_gpa' => 'decimal:2',
        'gpa_met' => 'boolean',
        'manually_cleared' => 'boolean',
        'cleared_at' => 'datetime',
        'last_calculated_at' => 'datetime'
    ];

    /**
     * Status constants
     */
    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_WAIVED = 'waived';
    const STATUS_SUBSTITUTED = 'substituted';
    const STATUS_TRANSFERRED = 'transferred';

    /**
     * Get the student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the program requirement
     */
    public function programRequirement(): BelongsTo
    {
        return $this->belongsTo(ProgramRequirement::class);
    }

    /**
     * Get the degree requirement
     */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(DegreeRequirement::class);
    }

    /**
     * Get the user who cleared this requirement
     */
    public function clearedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }

    /**
     * Scope for satisfied requirements
     */
    public function scopeSatisfied($query)
    {
        return $query->where('is_satisfied', true);
    }

    /**
     * Scope for unsatisfied requirements
     */
    public function scopeUnsatisfied($query)
    {
        return $query->where('is_satisfied', false);
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for in-progress requirements
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope for completed requirements
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Check if this requirement is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if this requirement is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if this requirement was manually cleared
     */
    public function wasManuallycleared(): bool
    {
        return $this->manually_cleared;
    }

    /**
     * Get the total credits for this requirement (completed + in progress)
     */
    public function getTotalCredits(): float
    {
        return $this->credits_completed + $this->credits_in_progress;
    }

    /**
     * Get the total courses for this requirement (completed + in progress)
     */
    public function getTotalCourses(): int
    {
        return $this->courses_completed + $this->courses_in_progress;
    }

    /**
     * Calculate and update completion percentage
     */
    public function updateCompletionPercentage(): void
    {
        $totalRequired = $this->credits_completed + $this->credits_remaining;
        
        if ($totalRequired > 0) {
            $this->completion_percentage = min(100, ($this->credits_completed / $totalRequired) * 100);
        } else {
            $this->completion_percentage = 0;
        }
        
        $this->save();
    }

    /**
     * Mark as manually cleared
     */
    public function markAsCleared(User $user, string $notes = null): void
    {
        $this->manually_cleared = true;
        $this->cleared_by = $user->id;
        $this->cleared_at = now();
        $this->is_satisfied = true;
        $this->status = self::STATUS_COMPLETED;
        
        if ($notes) {
            $this->notes = $notes;
        }
        
        $this->save();
    }

    /**
     * Update progress from course applications
     */
    public function updateFromCourseApplications(): void
    {
        $applications = StudentCourseApplication::where('student_id', $this->student_id)
            ->where('requirement_id', $this->requirement_id)
            ->get();
        
        $this->credits_completed = $applications->where('status', 'completed')->sum('credits_applied');
        $this->credits_in_progress = $applications->where('status', 'in_progress')->sum('credits_applied');
        $this->courses_completed = $applications->where('status', 'completed')->count();
        $this->courses_in_progress = $applications->where('status', 'in_progress')->count();
        
        // Calculate remaining based on program requirement
        if ($this->programRequirement) {
            $totalRequired = $this->programRequirement->getEffectiveCreditsRequired() ?? 0;
            $this->credits_remaining = max(0, $totalRequired - $this->credits_completed);
            
            $coursesRequired = $this->programRequirement->getEffectiveCoursesRequired() ?? 0;
            $this->courses_remaining = max(0, $coursesRequired - $this->courses_completed);
        }
        
        $this->updateCompletionPercentage();
        $this->last_calculated_at = now();
        $this->save();
    }

    /**
     * Get a descriptive status label
     */
    public function getStatusLabel(): string
    {
        if ($this->manually_cleared) {
            return 'Manually Cleared';
        }
        
        switch ($this->status) {
            case self::STATUS_NOT_STARTED:
                return 'Not Started';
            case self::STATUS_IN_PROGRESS:
                return 'In Progress';
            case self::STATUS_COMPLETED:
                return 'Completed';
            case self::STATUS_WAIVED:
                return 'Waived';
            case self::STATUS_SUBSTITUTED:
                return 'Substituted';
            case self::STATUS_TRANSFERRED:
                return 'Transferred';
            default:
                return 'Unknown';
        }
    }
}