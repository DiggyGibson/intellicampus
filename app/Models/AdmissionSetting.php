<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionSetting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'admission_settings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'term_id',
        'program_id',
        'application_open_date',
        'application_close_date',
        'early_decision_deadline',
        'regular_decision_deadline',
        'decision_release_date',
        'enrollment_deadline',
        'application_fee',
        'enrollment_deposit',
        'international_application_fee',
        'max_applications',
        'target_enrollment',
        'waitlist_size',
        'required_documents',
        'admission_criteria',
        'auto_decision_rules',
        'rolling_admissions',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'application_open_date' => 'date',
        'application_close_date' => 'date',
        'early_decision_deadline' => 'date',
        'regular_decision_deadline' => 'date',
        'decision_release_date' => 'date',
        'enrollment_deadline' => 'date',
        'application_fee' => 'decimal:2',
        'enrollment_deposit' => 'decimal:2',
        'international_application_fee' => 'decimal:2',
        'max_applications' => 'integer',
        'target_enrollment' => 'integer',
        'waitlist_size' => 'integer',
        'required_documents' => 'array',
        'admission_criteria' => 'array',
        'auto_decision_rules' => 'array',
        'rolling_admissions' => 'boolean',
        'is_active' => 'boolean'
    ];

    /**
     * Default settings.
     */
    const DEFAULT_SETTINGS = [
        'application_fee' => 50.00,
        'enrollment_deposit' => 500.00,
        'international_application_fee' => 75.00,
        'rolling_admissions' => false,
        'required_documents' => [
            'transcript' => [
                'name' => 'Official Transcript',
                'required' => true,
                'description' => 'Official transcript from all previously attended institutions'
            ],
            'test_scores' => [
                'name' => 'Test Scores',
                'required' => true,
                'description' => 'SAT or ACT scores for undergraduate, GRE/GMAT for graduate'
            ],
            'personal_statement' => [
                'name' => 'Personal Statement',
                'required' => true,
                'description' => 'Essay describing your goals and reasons for applying'
            ],
            'recommendations' => [
                'name' => 'Letters of Recommendation',
                'required' => true,
                'count' => 2,
                'description' => 'Letters from teachers or employers who know you well'
            ],
            'resume' => [
                'name' => 'Resume/CV',
                'required' => false,
                'description' => 'Current resume or curriculum vitae'
            ]
        ],
        'admission_criteria' => [
            'min_gpa' => 2.5,
            'min_sat' => 1000,
            'min_act' => 21,
            'min_toefl' => 80,
            'min_ielts' => 6.5,
            'auto_admit_gpa' => 3.8,
            'auto_deny_gpa' => 2.0
        ],
        'auto_decision_rules' => [
            'auto_admit' => [
                'gpa' => ['>=', 3.8],
                'test_score' => ['>=', 1400]
            ],
            'auto_deny' => [
                'gpa' => ['<', 2.0]
            ],
            'auto_waitlist' => [
                'gpa' => ['between', [2.5, 3.0]]
            ]
        ]
    ];

    /**
     * Relationships
     */

    /**
     * Get the academic term.
     */
    public function term()
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    /**
     * Get the academic program.
     */
    public function program()
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for active settings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for open applications.
     */
    public function scopeOpen($query)
    {
        return $query->where('application_open_date', '<=', now())
            ->where('application_close_date', '>=', now())
            ->where('is_active', true);
    }

    /**
     * Scope for upcoming application periods.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('application_open_date', '>', now())
            ->where('is_active', true);
    }

    /**
     * Scope for closed application periods.
     */
    public function scopeClosed($query)
    {
        return $query->where('application_close_date', '<', now());
    }

    /**
     * Helper Methods
     */

    /**
     * Check if applications are open.
     */
    public function isOpen(): bool
    {
        return $this->is_active && 
               $this->application_open_date <= now() && 
               $this->application_close_date >= now();
    }

    /**
     * Check if early decision deadline has passed.
     */
    public function isEarlyDecisionClosed(): bool
    {
        return $this->early_decision_deadline && 
               $this->early_decision_deadline < now();
    }

    /**
     * Check if regular decision deadline has passed.
     */
    public function isRegularDecisionClosed(): bool
    {
        return $this->regular_decision_deadline && 
               $this->regular_decision_deadline < now();
    }

    /**
     * Check if application capacity has been reached.
     */
    public function hasReachedCapacity(): bool
    {
        if (!$this->max_applications) {
            return false;
        }

        $applicationCount = AdmissionApplication::where('term_id', $this->term_id)
            ->where('program_id', $this->program_id)
            ->whereNotIn('status', ['draft', 'withdrawn'])
            ->count();
        
        return $applicationCount >= $this->max_applications;
    }

    /**
     * Get available application spots.
     */
    public function getAvailableSpots(): int
    {
        if (!$this->max_applications) {
            return PHP_INT_MAX; // Unlimited
        }

        $applicationCount = AdmissionApplication::where('term_id', $this->term_id)
            ->where('program_id', $this->program_id)
            ->whereNotIn('status', ['draft', 'withdrawn'])
            ->count();
        
        return max(0, $this->max_applications - $applicationCount);
    }

    /**
     * Get enrollment rate.
     */
    public function getEnrollmentRate(): float
    {
        $admitted = AdmissionApplication::where('term_id', $this->term_id)
            ->where('program_id', $this->program_id)
            ->where('decision', 'admit')
            ->count();
        
        if ($admitted === 0) {
            return 0;
        }

        $enrolled = AdmissionApplication::where('term_id', $this->term_id)
            ->where('program_id', $this->program_id)
            ->where('decision', 'admit')
            ->where('enrollment_confirmed', true)
            ->count();
        
        return round(($enrolled / $admitted) * 100, 2);
    }

    /**
     * Get admission rate.
     */
    public function getAdmissionRate(): float
    {
        $totalApplications = AdmissionApplication::where('term_id', $this->term_id)
            ->where('program_id', $this->program_id)
            ->where('status', '!=', 'draft')
            ->count();
        
        if ($totalApplications === 0) {
            return 0;
        }

        $admitted = AdmissionApplication::where('term_id', $this->term_id)
            ->where('program_id', $this->program_id)
            ->whereIn('decision', ['admit', 'conditional_admit'])
            ->count();
        
        return round(($admitted / $totalApplications) * 100, 2);
    }

    /**
     * Get days until application closes.
     */
    public function getDaysUntilClose(): ?int
    {
        if (!$this->application_close_date) {
            return null;
        }
        
        if ($this->application_close_date < now()) {
            return 0;
        }
        
        return now()->diffInDays($this->application_close_date);
    }

    /**
     * Get days since application opened.
     */
    public function getDaysSinceOpen(): ?int
    {
        if (!$this->application_open_date) {
            return null;
        }
        
        if ($this->application_open_date > now()) {
            return 0;
        }
        
        return $this->application_open_date->diffInDays(now());
    }

    /**
     * Get application period status.
     */
    public function getStatus(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }
        
        if ($this->application_open_date > now()) {
            return 'upcoming';
        }
        
        if ($this->application_close_date < now()) {
            return 'closed';
        }
        
        if ($this->hasReachedCapacity()) {
            return 'at_capacity';
        }
        
        return 'open';
    }

    /**
     * Get status label.
     */
    public function getStatusLabel(): string
    {
        return match($this->getStatus()) {
            'inactive' => 'Inactive',
            'upcoming' => 'Opening Soon',
            'open' => 'Open for Applications',
            'at_capacity' => 'At Capacity',
            'closed' => 'Closed',
            default => 'Unknown'
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->getStatus()) {
            'inactive' => 'gray',
            'upcoming' => 'blue',
            'open' => 'green',
            'at_capacity' => 'orange',
            'closed' => 'red',
            default => 'gray'
        };
    }

    /**
     * Check if auto-decision rules apply.
     */
    public function checkAutoDecision(AdmissionApplication $application): ?string
    {
        if (!$this->auto_decision_rules) {
            return null;
        }

        // Check auto-admit rules
        if ($this->meetsAutoAdmitCriteria($application)) {
            return 'admit';
        }

        // Check auto-deny rules
        if ($this->meetsAutoDenyCriteria($application)) {
            return 'deny';
        }

        // Check auto-waitlist rules
        if ($this->meetsAutoWaitlistCriteria($application)) {
            return 'waitlist';
        }

        return null;
    }

    /**
     * Check if application meets auto-admit criteria.
     */
    protected function meetsAutoAdmitCriteria(AdmissionApplication $application): bool
    {
        $rules = $this->auto_decision_rules['auto_admit'] ?? [];
        
        if (empty($rules)) {
            return false;
        }

        foreach ($rules as $field => $condition) {
            if (!$this->evaluateCondition($application, $field, $condition)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if application meets auto-deny criteria.
     */
    protected function meetsAutoDenyCriteria(AdmissionApplication $application): bool
    {
        $rules = $this->auto_decision_rules['auto_deny'] ?? [];
        
        if (empty($rules)) {
            return false;
        }

        foreach ($rules as $field => $condition) {
            if ($this->evaluateCondition($application, $field, $condition)) {
                return true; // Any matching condition triggers auto-deny
            }
        }

        return false;
    }

    /**
     * Check if application meets auto-waitlist criteria.
     */
    protected function meetsAutoWaitlistCriteria(AdmissionApplication $application): bool
    {
        $rules = $this->auto_decision_rules['auto_waitlist'] ?? [];
        
        if (empty($rules)) {
            return false;
        }

        foreach ($rules as $field => $condition) {
            if (!$this->evaluateCondition($application, $field, $condition)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate a condition against application data.
     */
    protected function evaluateCondition(AdmissionApplication $application, $field, $condition): bool
    {
        $value = match($field) {
            'gpa' => $application->previous_gpa,
            'test_score' => $this->getHighestTestScore($application),
            default => null
        };

        if ($value === null) {
            return false;
        }

        [$operator, $threshold] = $condition;

        return match($operator) {
            '>=' => $value >= $threshold,
            '>' => $value > $threshold,
            '<=' => $value <= $threshold,
            '<' => $value < $threshold,
            '=' => $value == $threshold,
            'between' => $value >= $threshold[0] && $value <= $threshold[1],
            default => false
        };
    }

    /**
     * Get highest test score from application.
     */
    protected function getHighestTestScore(AdmissionApplication $application): ?int
    {
        $scores = [];
        $testScores = $application->test_scores ?? [];

        if (isset($testScores['SAT']['total'])) {
            $scores[] = $testScores['SAT']['total'];
        }

        if (isset($testScores['ACT']['composite'])) {
            // Convert ACT to SAT equivalent (rough conversion)
            $scores[] = $testScores['ACT']['composite'] * 40 + 400;
        }

        return !empty($scores) ? max($scores) : null;
    }

    /**
     * Get statistics for the admission period.
     */
    public function getStatistics(): array
    {
        $applications = AdmissionApplication::where('term_id', $this->term_id)
            ->where('program_id', $this->program_id);

        return [
            'total_applications' => $applications->count(),
            'submitted' => $applications->where('status', '!=', 'draft')->count(),
            'under_review' => $applications->where('status', 'under_review')->count(),
            'admitted' => $applications->where('decision', 'admit')->count(),
            'denied' => $applications->where('decision', 'deny')->count(),
            'waitlisted' => $applications->where('decision', 'waitlist')->count(),
            'enrolled' => $applications->where('enrollment_confirmed', true)->count(),
            'admission_rate' => $this->getAdmissionRate(),
            'enrollment_rate' => $this->getEnrollmentRate(),
            'available_spots' => $this->getAvailableSpots(),
            'days_until_close' => $this->getDaysUntilClose()
        ];
    }

    /**
     * Create default settings for a new term/program.
     */
    public static function createDefaults($termId, $programId = null): self
    {
        return self::create([
            'term_id' => $termId,
            'program_id' => $programId,
            'application_open_date' => now(),
            'application_close_date' => now()->addMonths(3),
            'decision_release_date' => now()->addMonths(4),
            'enrollment_deadline' => now()->addMonths(5),
            'application_fee' => self::DEFAULT_SETTINGS['application_fee'],
            'enrollment_deposit' => self::DEFAULT_SETTINGS['enrollment_deposit'],
            'required_documents' => self::DEFAULT_SETTINGS['required_documents'],
            'admission_criteria' => self::DEFAULT_SETTINGS['admission_criteria'],
            'auto_decision_rules' => self::DEFAULT_SETTINGS['auto_decision_rules'],
            'is_active' => true
        ]);
    }
}