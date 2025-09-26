<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanTerm extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'plan_id',
        'term_id',
        'sequence_number',
        'term_name',
        'term_type',
        'year',
        'planned_credits',
        'min_credits',
        'max_credits',
        'status',
        'notes'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'sequence_number' => 'integer',
        'year' => 'integer',
        'planned_credits' => 'decimal:1',
        'min_credits' => 'decimal:1',
        'max_credits' => 'decimal:1'
    ];

    /**
     * Term types
     */
    const TYPE_FALL = 'fall';
    const TYPE_SPRING = 'spring';
    const TYPE_SUMMER = 'summer';
    const TYPE_WINTER = 'winter';
    const TYPE_INTERSESSION = 'intersession';

    /**
     * Term statuses
     */
    const STATUS_PLANNED = 'planned';
    const STATUS_CURRENT = 'current';
    const STATUS_COMPLETED = 'completed';
    const STATUS_SKIPPED = 'skipped';

    /**
     * Get the academic plan
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(AcademicPlan::class);
    }

    /**
     * Get the academic term
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    /**
     * Get the courses in this term
     */
    public function planCourses(): HasMany
    {
        return $this->hasMany(PlanCourse::class, 'plan_term_id');
    }

    /**
     * Scope for planned terms
     */
    public function scopePlanned($query)
    {
        return $query->where('status', self::STATUS_PLANNED);
    }

    /**
     * Scope for current term
     */
    public function scopeCurrent($query)
    {
        return $query->where('status', self::STATUS_CURRENT);
    }

    /**
     * Scope for completed terms
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Check if term is planned
     */
    public function isPlanned(): bool
    {
        return $this->status === self::STATUS_PLANNED;
    }

    /**
     * Check if term is current
     */
    public function isCurrent(): bool
    {
        return $this->status === self::STATUS_CURRENT;
    }

    /**
     * Check if term is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get total credits for this term
     */
    public function getTotalCredits(): float
    {
        return $this->planCourses()->sum('credits');
    }

    /**
     * Check if term is within credit limits
     */
    public function isWithinCreditLimits(): bool
    {
        $totalCredits = $this->getTotalCredits();
        
        return $totalCredits >= $this->min_credits && $totalCredits <= $this->max_credits;
    }

    /**
     * Get credit status
     */
    public function getCreditStatus(): string
    {
        $totalCredits = $this->getTotalCredits();
        
        if ($totalCredits < $this->min_credits) {
            return 'under';
        } elseif ($totalCredits > $this->max_credits) {
            return 'over';
        }
        
        return 'ok';
    }

    /**
     * Mark as current
     */
    public function markAsCurrent(): void
    {
        // Remove current status from other terms in same plan
        self::where('plan_id', $this->plan_id)
            ->where('id', '!=', $this->id)
            ->update(['status' => self::STATUS_PLANNED]);
        
        $this->status = self::STATUS_CURRENT;
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
     * Get term type label
     */
    public function getTermTypeLabel(): string
    {
        switch ($this->term_type) {
            case self::TYPE_FALL:
                return 'Fall';
            case self::TYPE_SPRING:
                return 'Spring';
            case self::TYPE_SUMMER:
                return 'Summer';
            case self::TYPE_WINTER:
                return 'Winter';
            case self::TYPE_INTERSESSION:
                return 'Intersession';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get full term name with year
     */
    public function getFullTermName(): string
    {
        return $this->getTermTypeLabel() . ' ' . $this->year;
    }
}