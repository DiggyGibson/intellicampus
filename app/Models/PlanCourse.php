<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanCourse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'plan_term_id',
        'course_id',
        'section_id',
        'credits',
        'status',
        'satisfies_requirements',
        'prerequisites_met',
        'corequisites_met',
        'validation_warnings',
        'alternative_courses',
        'priority',
        'is_required',
        'is_backup',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'credits' => 'decimal:1',
        'satisfies_requirements' => 'array',
        'prerequisites_met' => 'boolean',
        'corequisites_met' => 'boolean',
        'validation_warnings' => 'array',
        'alternative_courses' => 'array',
        'priority' => 'integer',
        'is_required' => 'boolean',
        'is_backup' => 'boolean'
    ];

    /**
     * Course statuses
     */
    const STATUS_PLANNED = 'planned';
    const STATUS_REGISTERED = 'registered';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_DROPPED = 'dropped';
    const STATUS_FAILED = 'failed';
    const STATUS_WITHDRAWN = 'withdrawn';

    /**
     * Get the plan term
     */
    public function planTerm(): BelongsTo
    {
        return $this->belongsTo(PlanTerm::class);
    }

    /**
     * Get the course
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the section
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class);
    }

    /**
     * Scope for planned courses
     */
    public function scopePlanned($query)
    {
        return $query->where('status', self::STATUS_PLANNED);
    }

    /**
     * Scope for registered courses
     */
    public function scopeRegistered($query)
    {
        return $query->where('status', self::STATUS_REGISTERED);
    }

    /**
     * Scope for completed courses
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for required courses
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope for backup courses
     */
    public function scopeBackup($query)
    {
        return $query->where('is_backup', true);
    }

    /**
     * Check if course is planned
     */
    public function isPlanned(): bool
    {
        return $this->status === self::STATUS_PLANNED;
    }

    /**
     * Check if course is registered
     */
    public function isRegistered(): bool
    {
        return $this->status === self::STATUS_REGISTERED;
    }

    /**
     * Check if course is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if prerequisites are met
     */
    public function hasPrerequisitesMet(): bool
    {
        return $this->prerequisites_met;
    }

    /**
     * Check if corequisites are met
     */
    public function hasCorequisitesMet(): bool
    {
        return $this->corequisites_met;
    }

    /**
     * Check if course has validation warnings
     */
    public function hasValidationWarnings(): bool
    {
        return !empty($this->validation_warnings);
    }

    /**
     * Get validation warning count
     */
    public function getValidationWarningCount(): int
    {
        return is_array($this->validation_warnings) ? count($this->validation_warnings) : 0;
    }

    /**
     * Get requirements this course satisfies
     */
    public function getSatisfiedRequirements(): array
    {
        return $this->satisfies_requirements ?? [];
    }

    /**
     * Check if course satisfies a specific requirement
     */
    public function satisfiesRequirement(int $requirementId): bool
    {
        return in_array($requirementId, $this->getSatisfiedRequirements());
    }

    /**
     * Get alternative courses
     */
    public function getAlternativeCourses(): array
    {
        return $this->alternative_courses ?? [];
    }

    /**
     * Mark as registered
     */
    public function markAsRegistered(int $sectionId = null): void
    {
        $this->status = self::STATUS_REGISTERED;
        
        if ($sectionId) {
            $this->section_id = $sectionId;
        }
        
        $this->save();
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->save();
    }

    /**
     * Mark as dropped
     */
    public function markAsDropped(): void
    {
        $this->status = self::STATUS_DROPPED;
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
            case self::STATUS_REGISTERED:
                return 'Registered';
            case self::STATUS_IN_PROGRESS:
                return 'In Progress';
            case self::STATUS_COMPLETED:
                return 'Completed';
            case self::STATUS_DROPPED:
                return 'Dropped';
            case self::STATUS_FAILED:
                return 'Failed';
            case self::STATUS_WITHDRAWN:
                return 'Withdrawn';
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
            case self::STATUS_PLANNED:
                return 'info';
            case self::STATUS_REGISTERED:
                return 'primary';
            case self::STATUS_IN_PROGRESS:
                return 'warning';
            case self::STATUS_COMPLETED:
                return 'success';
            case self::STATUS_DROPPED:
            case self::STATUS_WITHDRAWN:
                return 'secondary';
            case self::STATUS_FAILED:
                return 'danger';
            default:
                return 'light';
        }
    }
}