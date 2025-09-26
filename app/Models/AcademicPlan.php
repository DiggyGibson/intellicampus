<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicPlan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'plan_name',
        'description',
        'plan_type',
        'primary_program_id',
        'minor_program_id',
        'catalog_year',
        'start_date',
        'expected_graduation_date',
        'total_terms',
        'status',
        'is_current',
        'is_valid',
        'validation_errors',
        'last_validated_at',
        'advisor_approved',
        'approved_by',
        'approved_at',
        'advisor_notes'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_date' => 'date',
        'expected_graduation_date' => 'date',
        'total_terms' => 'integer',
        'is_current' => 'boolean',
        'is_valid' => 'boolean',
        'validation_errors' => 'array',
        'last_validated_at' => 'datetime',
        'advisor_approved' => 'boolean',
        'approved_at' => 'datetime'
    ];

    /**
     * Plan types
     */
    const TYPE_FOUR_YEAR = 'four_year';
    const TYPE_CUSTOM = 'custom';
    const TYPE_ACCELERATED = 'accelerated';
    const TYPE_PART_TIME = 'part_time';
    const TYPE_TRANSFER = 'transfer';

    /**
     * Plan statuses
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ARCHIVED = 'archived';

    /**
     * Get the student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the primary program
     */
    public function primaryProgram(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'primary_program_id');
    }

    /**
     * Get the minor program
     */
    public function minorProgram(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'minor_program_id');
    }

    /**
     * Get the approving user
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the plan terms
     */
    public function planTerms(): HasMany
    {
        return $this->hasMany(PlanTerm::class, 'plan_id')->orderBy('sequence_number');
    }

    /**
     * Get all courses in the plan
     */
    public function planCourses()
    {
        return $this->hasManyThrough(PlanCourse::class, PlanTerm::class, 'plan_id', 'plan_term_id');
    }

    /**
     * Scope for current plans
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope for active plans
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for draft plans
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope for approved plans
     */
    public function scopeApproved($query)
    {
        return $query->where('advisor_approved', true);
    }

    /**
     * Scope for valid plans
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', true);
    }

    /**
     * Check if plan is draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if plan is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if plan is approved
     */
    public function isApproved(): bool
    {
        return $this->advisor_approved;
    }

    /**
     * Get total credits in plan
     */
    public function getTotalCredits(): float
    {
        return $this->planCourses()->sum('credits');
    }

    /**
     * Get credits per term average
     */
    public function getAverageCreditsPerTerm(): float
    {
        if ($this->total_terms === 0) {
            return 0;
        }
        
        return round($this->getTotalCredits() / $this->total_terms, 1);
    }

    /**
     * Get completed terms count
     */
    public function getCompletedTermsCount(): int
    {
        return $this->planTerms()->where('status', 'completed')->count();
    }

    /**
     * Get current term
     */
    public function getCurrentTerm()
    {
        return $this->planTerms()->where('status', 'current')->first();
    }

    /**
     * Validate the plan
     */
    public function validate(): bool
    {
        $errors = [];
        
        // Check if all required courses are included
        // Check prerequisites are met
        // Check credit distribution
        // Check graduation requirements
        
        // This would contain full validation logic
        
        $this->validation_errors = $errors;
        $this->is_valid = empty($errors);
        $this->last_validated_at = now();
        $this->save();
        
        return $this->is_valid;
    }

    /**
     * Approve the plan
     */
    public function approve(User $advisor, string $notes = null): void
    {
        $this->advisor_approved = true;
        $this->approved_by = $advisor->id;
        $this->approved_at = now();
        
        if ($notes) {
            $this->advisor_notes = $notes;
        }
        
        $this->save();
    }

    /**
     * Set as current plan
     */
    public function setAsCurrent(): void
    {
        // Remove current flag from other plans
        self::where('student_id', $this->student_id)
            ->where('id', '!=', $this->id)
            ->update(['is_current' => false]);
        
        // Set this plan as current
        $this->is_current = true;
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    /**
     * Archive the plan
     */
    public function archive(): void
    {
        $this->status = self::STATUS_ARCHIVED;
        $this->is_current = false;
        $this->save();
    }

    /**
     * Get plan type label
     */
    public function getPlanTypeLabel(): string
    {
        switch ($this->plan_type) {
            case self::TYPE_FOUR_YEAR:
                return 'Four Year Plan';
            case self::TYPE_CUSTOM:
                return 'Custom Plan';
            case self::TYPE_ACCELERATED:
                return 'Accelerated Plan';
            case self::TYPE_PART_TIME:
                return 'Part-Time Plan';
            case self::TYPE_TRANSFER:
                return 'Transfer Student Plan';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        switch ($this->status) {
            case self::STATUS_DRAFT:
                return 'Draft';
            case self::STATUS_ACTIVE:
                return 'Active';
            case self::STATUS_COMPLETED:
                return 'Completed';
            case self::STATUS_ARCHIVED:
                return 'Archived';
            default:
                return 'Unknown';
        }
    }
}