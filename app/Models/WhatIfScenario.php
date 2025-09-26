<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatIfScenario extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'scenario_name',
        'description',
        'scenario_type',
        'new_program_id',
        'add_minor_id',
        'add_second_major_id',
        'new_catalog_year',
        'transfer_courses',
        'transfer_credits',
        'analysis_results',
        'current_credits_required',
        'scenario_credits_required',
        'credit_difference',
        'current_terms_remaining',
        'scenario_terms_remaining',
        'is_feasible',
        'feasibility_issues',
        'is_saved',
        'is_applied',
        'applied_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'transfer_courses' => 'array',
        'transfer_credits' => 'decimal:1',
        'analysis_results' => 'array',
        'current_credits_required' => 'decimal:1',
        'scenario_credits_required' => 'decimal:1',
        'credit_difference' => 'decimal:1',
        'current_terms_remaining' => 'integer',
        'scenario_terms_remaining' => 'integer',
        'is_feasible' => 'boolean',
        'feasibility_issues' => 'array',
        'is_saved' => 'boolean',
        'is_applied' => 'boolean',
        'applied_at' => 'datetime'
    ];

    /**
     * Scenario types
     */
    const TYPE_CHANGE_MAJOR = 'change_major';
    const TYPE_ADD_MINOR = 'add_minor';
    const TYPE_ADD_DOUBLE_MAJOR = 'add_double_major';
    const TYPE_CHANGE_CATALOG = 'change_catalog';
    const TYPE_TRANSFER_CREDITS = 'transfer_credits';
    const TYPE_COURSE_SUBSTITUTION = 'course_substitution';

    /**
     * Get the student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the new program
     */
    public function newProgram(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'new_program_id');
    }

    /**
     * Get the minor program to add
     */
    public function addMinor(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'add_minor_id');
    }

    /**
     * Get the second major to add
     */
    public function addSecondMajor(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'add_second_major_id');
    }

    /**
     * Scope for saved scenarios
     */
    public function scopeSaved($query)
    {
        return $query->where('is_saved', true);
    }

    /**
     * Scope for applied scenarios
     */
    public function scopeApplied($query)
    {
        return $query->where('is_applied', true);
    }

    /**
     * Scope for feasible scenarios
     */
    public function scopeFeasible($query)
    {
        return $query->where('is_feasible', true);
    }

    /**
     * Scope by scenario type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('scenario_type', $type);
    }

    /**
     * Check if scenario is saved
     */
    public function isSaved(): bool
    {
        return $this->is_saved;
    }

    /**
     * Check if scenario is applied
     */
    public function isApplied(): bool
    {
        return $this->is_applied;
    }

    /**
     * Check if scenario is feasible
     */
    public function isFeasible(): bool
    {
        return $this->is_feasible;
    }

    /**
     * Get additional credits required
     */
    public function getAdditionalCreditsRequired(): float
    {
        return max(0, $this->credit_difference);
    }

    /**
     * Get additional terms required
     */
    public function getAdditionalTermsRequired(): int
    {
        $currentTerms = $this->current_terms_remaining ?? 0;
        $scenarioTerms = $this->scenario_terms_remaining ?? 0;
        
        return max(0, $scenarioTerms - $currentTerms);
    }

    /**
     * Get impact summary
     */
    public function getImpactSummary(): array
    {
        return [
            'credits' => [
                'current' => $this->current_credits_required,
                'scenario' => $this->scenario_credits_required,
                'difference' => $this->credit_difference,
                'impact' => $this->credit_difference > 0 ? 'increase' : ($this->credit_difference < 0 ? 'decrease' : 'none')
            ],
            'terms' => [
                'current' => $this->current_terms_remaining,
                'scenario' => $this->scenario_terms_remaining,
                'additional' => $this->getAdditionalTermsRequired()
            ],
            'feasible' => $this->is_feasible,
            'issues' => $this->feasibility_issues ?? []
        ];
    }

    /**
     * Save scenario
     */
    public function saveScenario(): void
    {
        $this->is_saved = true;
        $this->save();
    }

    /**
     * Apply scenario to student
     */
    public function apply(): bool
    {
        if (!$this->is_feasible) {
            return false;
        }

        // This would contain logic to actually apply the scenario
        // Update student's program, catalog year, etc.
        
        $this->is_applied = true;
        $this->applied_at = now();
        $this->save();
        
        return true;
    }

    /**
     * Get scenario type label
     */
    public function getScenarioTypeLabel(): string
    {
        switch ($this->scenario_type) {
            case self::TYPE_CHANGE_MAJOR:
                return 'Change Major';
            case self::TYPE_ADD_MINOR:
                return 'Add Minor';
            case self::TYPE_ADD_DOUBLE_MAJOR:
                return 'Add Double Major';
            case self::TYPE_CHANGE_CATALOG:
                return 'Change Catalog Year';
            case self::TYPE_TRANSFER_CREDITS:
                return 'Transfer Credits';
            case self::TYPE_COURSE_SUBSTITUTION:
                return 'Course Substitution';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get feasibility status color
     */
    public function getFeasibilityColor(): string
    {
        if ($this->is_feasible) {
            return 'success';
        }
        
        if (empty($this->feasibility_issues)) {
            return 'warning';
        }
        
        return 'danger';
    }
}