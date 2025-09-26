<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseRequirementMapping extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'course_id',
        'requirement_id',
        'fulfillment_type',
        'credit_value',
        'min_grade',
        'effective_from',
        'effective_until',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'credit_value' => 'decimal:1',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean'
    ];

    /**
     * Fulfillment types
     */
    const FULFILLMENT_FULL = 'full';
    const FULFILLMENT_PARTIAL = 'partial';
    const FULFILLMENT_ELECTIVE = 'elective';
    const FULFILLMENT_CHOICE = 'choice';

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
     * Scope for active mappings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            });
    }

    /**
     * Scope by fulfillment type
     */
    public function scopeByFulfillmentType($query, $type)
    {
        return $query->where('fulfillment_type', $type);
    }

    /**
     * Check if this mapping is currently effective
     */
    public function isEffective(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->effective_from && $this->effective_from > $now) {
            return false;
        }

        if ($this->effective_until && $this->effective_until < $now) {
            return false;
        }

        return true;
    }

    /**
     * Check if this course fully satisfies the requirement
     */
    public function fullySatisfies(): bool
    {
        return $this->fulfillment_type === self::FULFILLMENT_FULL;
    }

    /**
     * Check if this course partially satisfies the requirement
     */
    public function partiallySatisfies(): bool
    {
        return $this->fulfillment_type === self::FULFILLMENT_PARTIAL;
    }

    /**
     * Get the effective credit value for this mapping
     */
    public function getEffectiveCreditValue(): float
    {
        // If override credit value is set, use it
        if ($this->credit_value !== null) {
            return $this->credit_value;
        }

        // Otherwise use the course's credit value
        return $this->course->credits ?? 0;
    }

    /**
     * Check if a grade meets the minimum requirement
     */
    public function meetsMinimumGrade(string $grade): bool
    {
        if (!$this->min_grade) {
            return true; // No minimum grade requirement
        }

        $gradeValues = [
            'A' => 12, 'A-' => 11,
            'B+' => 10, 'B' => 9, 'B-' => 8,
            'C+' => 7, 'C' => 6, 'C-' => 5,
            'D+' => 4, 'D' => 3, 'D-' => 2,
            'F' => 1
        ];

        $studentGradeValue = $gradeValues[$grade] ?? 0;
        $minGradeValue = $gradeValues[$this->min_grade] ?? 0;

        return $studentGradeValue >= $minGradeValue;
    }

    /**
     * Get display text for fulfillment type
     */
    public function getFulfillmentTypeLabel(): string
    {
        switch ($this->fulfillment_type) {
            case self::FULFILLMENT_FULL:
                return 'Fully Satisfies';
            case self::FULFILLMENT_PARTIAL:
                return 'Partially Satisfies';
            case self::FULFILLMENT_ELECTIVE:
                return 'Counts as Elective';
            case self::FULFILLMENT_CHOICE:
                return 'One of Several Choices';
            default:
                return 'Unknown';
        }
    }
}