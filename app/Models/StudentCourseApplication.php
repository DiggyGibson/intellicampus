<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCourseApplication extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'enrollment_id',
        'course_id',
        'requirement_id',
        'program_requirement_id',
        'credits_applied',
        'grade',
        'status',
        'is_override',
        'override_reason',
        'override_by',
        'override_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'credits_applied' => 'decimal:1',
        'is_override' => 'boolean',
        'override_at' => 'datetime'
    ];

    /**
     * Status constants
     */
    const STATUS_PLANNED = 'planned';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_WITHDRAWN = 'withdrawn';
    const STATUS_REPEATED = 'repeated';

    /**
     * Get the student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the enrollment
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the course
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the requirement
     */
    public function requirement(): BelongsTo
    {
        return $this->belongsTo(DegreeRequirement::class);
    }

    /**
     * Get the program requirement
     */
    public function programRequirement(): BelongsTo
    {
        return $this->belongsTo(ProgramRequirement::class);
    }

    /**
     * Get the user who created the override
     */
    public function overrideBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'override_by');
    }

    /**
     * Scope for completed applications
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for in-progress applications
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope for planned applications
     */
    public function scopePlanned($query)
    {
        return $query->where('status', self::STATUS_PLANNED);
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for overridden applications
     */
    public function scopeOverridden($query)
    {
        return $query->where('is_override', true);
    }

    /**
     * Check if this application is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if this application is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if this application failed
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if the grade meets requirement minimum
     */
    public function meetsGradeRequirement(): bool
    {
        if (!$this->grade || !$this->requirement) {
            return false;
        }

        $minGrade = $this->requirement->parameters['min_grade'] ?? null;
        
        if (!$minGrade) {
            return true; // No minimum grade requirement
        }

        $gradeValues = [
            'A' => 12, 'A-' => 11,
            'B+' => 10, 'B' => 9, 'B-' => 8,
            'C+' => 7, 'C' => 6, 'C-' => 5,
            'D+' => 4, 'D' => 3, 'D-' => 2,
            'F' => 1
        ];

        $studentGradeValue = $gradeValues[$this->grade] ?? 0;
        $minGradeValue = $gradeValues[$minGrade] ?? 0;

        return $studentGradeValue >= $minGradeValue;
    }

    /**
     * Mark as override
     */
    public function markAsOverride(User $user, string $reason): void
    {
        $this->is_override = true;
        $this->override_reason = $reason;
        $this->override_by = $user->id;
        $this->override_at = now();
        $this->save();
    }

    /**
     * Update status based on enrollment
     */
    public function updateStatusFromEnrollment(): void
    {
        if (!$this->enrollment) {
            return;
        }

        switch ($this->enrollment->enrollment_status) {
            case 'enrolled':
            case 'in_progress':
                $this->status = self::STATUS_IN_PROGRESS;
                break;
            
            case 'completed':
                if ($this->meetsGradeRequirement()) {
                    $this->status = self::STATUS_COMPLETED;
                } else {
                    $this->status = self::STATUS_FAILED;
                }
                break;
            
            case 'withdrawn':
                $this->status = self::STATUS_WITHDRAWN;
                break;
            
            case 'dropped':
                $this->status = self::STATUS_WITHDRAWN;
                break;
        }

        // Update grade if available
        if ($this->enrollment->final_grade) {
            $this->grade = $this->enrollment->final_grade;
        }

        $this->save();
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        switch ($this->status) {
            case self::STATUS_PLANNED:
                return 'Planned';
            case self::STATUS_IN_PROGRESS:
                return 'In Progress';
            case self::STATUS_COMPLETED:
                return 'Completed';
            case self::STATUS_FAILED:
                return 'Failed';
            case self::STATUS_WITHDRAWN:
                return 'Withdrawn';
            case self::STATUS_REPEATED:
                return 'Repeated';
            default:
                return 'Unknown';
        }
    }
}