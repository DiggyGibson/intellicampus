<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DegreeAuditReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'program_id',
        'term_id',
        'report_type',
        'catalog_year',
        'total_credits_required',
        'total_credits_completed',
        'total_credits_in_progress',
        'total_credits_remaining',
        'overall_completion_percentage',
        'cumulative_gpa',
        'major_gpa',
        'minor_gpa',
        'graduation_eligible',
        'terms_to_completion',
        'expected_graduation_date',
        'requirements_summary',
        'completed_requirements',
        'in_progress_requirements',
        'remaining_requirements',
        'recommendations',
        'generated_by',
        'generated_at',
        'is_official'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'total_credits_required' => 'decimal:1',
        'total_credits_completed' => 'decimal:1',
        'total_credits_in_progress' => 'decimal:1',
        'total_credits_remaining' => 'decimal:1',
        'overall_completion_percentage' => 'decimal:2',
        'cumulative_gpa' => 'decimal:2',
        'major_gpa' => 'decimal:2',
        'minor_gpa' => 'decimal:2',
        'graduation_eligible' => 'boolean',
        'terms_to_completion' => 'integer',
        'expected_graduation_date' => 'date',
        'requirements_summary' => 'array',
        'completed_requirements' => 'array',
        'in_progress_requirements' => 'array',
        'remaining_requirements' => 'array',
        'recommendations' => 'array',
        'generated_at' => 'datetime',
        'is_official' => 'boolean'
    ];

    /**
     * Report types
     */
    const TYPE_OFFICIAL = 'official';
    const TYPE_UNOFFICIAL = 'unofficial';
    const TYPE_WHAT_IF = 'what_if';

    /**
     * Get the student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the program
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class);
    }

    /**
     * Get the term
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    /**
     * Get the user who generated the report
     */
    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Scope for official reports
     */
    public function scopeOfficial($query)
    {
        return $query->where('is_official', true);
    }

    /**
     * Scope for unofficial reports
     */
    public function scopeUnofficial($query)
    {
        return $query->where('is_official', false);
    }

    /**
     * Scope by report type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Scope for recent reports
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('generated_at', '>=', now()->subHours($hours));
    }

    /**
     * Check if report is official
     */
    public function isOfficial(): bool
    {
        return $this->is_official;
    }

    /**
     * Check if report is a what-if scenario
     */
    public function isWhatIf(): bool
    {
        return $this->report_type === self::TYPE_WHAT_IF;
    }

    /**
     * Check if student is eligible for graduation
     */
    public function isGraduationEligible(): bool
    {
        return $this->graduation_eligible;
    }

    /**
     * Get progress towards completion as a percentage
     */
    public function getProgressPercentage(): float
    {
        return $this->overall_completion_percentage;
    }

    /**
     * Get number of requirements completed
     */
    public function getCompletedRequirementsCount(): int
    {
        return is_array($this->completed_requirements) ? count($this->completed_requirements) : 0;
    }

    /**
     * Get number of requirements in progress
     */
    public function getInProgressRequirementsCount(): int
    {
        return is_array($this->in_progress_requirements) ? count($this->in_progress_requirements) : 0;
    }

    /**
     * Get number of requirements remaining
     */
    public function getRemainingRequirementsCount(): int
    {
        return is_array($this->remaining_requirements) ? count($this->remaining_requirements) : 0;
    }

    /**
     * Get total number of requirements
     */
    public function getTotalRequirementsCount(): int
    {
        return $this->getCompletedRequirementsCount() + 
               $this->getInProgressRequirementsCount() + 
               $this->getRemainingRequirementsCount();
    }

    /**
     * Get high priority recommendations
     */
    public function getHighPriorityRecommendations(): array
    {
        if (!is_array($this->recommendations)) {
            return [];
        }

        return array_filter($this->recommendations, function ($rec) {
            return isset($rec['priority']) && $rec['priority'] === 'high';
        });
    }

    /**
     * Check if report is recent (within last 24 hours)
     */
    public function isRecent(): bool
    {
        return $this->generated_at && $this->generated_at->isAfter(now()->subHours(24));
    }

    /**
     * Get formatted summary for display
     */
    public function getFormattedSummary(): array
    {
        return [
            'student_name' => $this->student->full_name ?? 'Unknown',
            'program' => $this->program->name ?? 'Unknown',
            'catalog_year' => $this->catalog_year,
            'progress' => [
                'percentage' => $this->overall_completion_percentage . '%',
                'credits_completed' => $this->total_credits_completed . '/' . $this->total_credits_required,
                'credits_remaining' => $this->total_credits_remaining
            ],
            'gpa' => [
                'cumulative' => $this->cumulative_gpa,
                'major' => $this->major_gpa,
                'minor' => $this->minor_gpa
            ],
            'graduation' => [
                'eligible' => $this->graduation_eligible ? 'Yes' : 'No',
                'expected_date' => $this->expected_graduation_date?->format('F Y'),
                'terms_remaining' => $this->terms_to_completion
            ],
            'requirements' => [
                'completed' => $this->getCompletedRequirementsCount(),
                'in_progress' => $this->getInProgressRequirementsCount(),
                'remaining' => $this->getRemainingRequirementsCount(),
                'total' => $this->getTotalRequirementsCount()
            ]
        ];
    }
}