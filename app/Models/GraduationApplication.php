<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GraduationApplication extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'program_id',
        'term_id',
        'expected_graduation_date',
        'degree_type',
        'diploma_name',
        'requirements_met',
        'final_gpa',
        'total_credits_earned',
        'pending_requirements',
        'has_holds',
        'holds_list',
        'honors',
        'special_recognitions',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'academic_clearance',
        'financial_clearance',
        'library_clearance',
        'submitted_at',
        'approved_at',
        'graduation_date'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'expected_graduation_date' => 'date',
        'requirements_met' => 'boolean',
        'final_gpa' => 'decimal:2',
        'total_credits_earned' => 'integer',
        'pending_requirements' => 'array',
        'has_holds' => 'boolean',
        'holds_list' => 'array',
        'special_recognitions' => 'array',
        'reviewed_at' => 'datetime',
        'academic_clearance' => 'boolean',
        'financial_clearance' => 'boolean',
        'library_clearance' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'graduation_date' => 'datetime'
    ];

    /**
     * Application statuses
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_CONDITIONAL = 'conditional';
    const STATUS_DENIED = 'denied';
    const STATUS_GRADUATED = 'graduated';

    /**
     * Honors types
     */
    const HONORS_CUM_LAUDE = 'cum_laude';
    const HONORS_MAGNA_CUM_LAUDE = 'magna_cum_laude';
    const HONORS_SUMMA_CUM_LAUDE = 'summa_cum_laude';

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
     * Get the reviewing user
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope for draft applications
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope for submitted applications
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    /**
     * Scope for approved applications
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for applications by term
     */
    public function scopeByTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    /**
     * Check if application is draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if application is submitted
     */
    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    /**
     * Check if application is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if student has graduated
     */
    public function hasGraduated(): bool
    {
        return $this->status === self::STATUS_GRADUATED;
    }

    /**
     * Check if all clearances are obtained
     */
    public function hasAllClearances(): bool
    {
        return $this->academic_clearance && 
               $this->financial_clearance && 
               $this->library_clearance;
    }

    /**
     * Check if student has pending requirements
     */
    public function hasPendingRequirements(): bool
    {
        return !empty($this->pending_requirements);
    }

    /**
     * Get pending requirement count
     */
    public function getPendingRequirementCount(): int
    {
        return is_array($this->pending_requirements) ? count($this->pending_requirements) : 0;
    }

    /**
     * Check if student qualifies for honors
     */
    public function qualifiesForHonors(): ?string
    {
        if (!$this->final_gpa) {
            return null;
        }

        // These thresholds should be configurable
        if ($this->final_gpa >= 3.9) {
            return self::HONORS_SUMMA_CUM_LAUDE;
        } elseif ($this->final_gpa >= 3.7) {
            return self::HONORS_MAGNA_CUM_LAUDE;
        } elseif ($this->final_gpa >= 3.5) {
            return self::HONORS_CUM_LAUDE;
        }

        return null;
    }

    /**
     * Submit the application
     */
    public function submit(): void
    {
        $this->status = self::STATUS_SUBMITTED;
        $this->submitted_at = now();
        $this->save();
    }

    /**
     * Approve the application
     */
    public function approve(User $reviewer, string $notes = null): void
    {
        $this->status = self::STATUS_APPROVED;
        $this->reviewed_by = $reviewer->id;
        $this->reviewed_at = now();
        $this->approved_at = now();
        
        if ($notes) {
            $this->review_notes = $notes;
        }
        
        // Set honors if qualified
        $this->honors = $this->qualifiesForHonors();
        
        $this->save();
    }

    /**
     * Deny the application
     */
    public function deny(User $reviewer, string $notes): void
    {
        $this->status = self::STATUS_DENIED;
        $this->reviewed_by = $reviewer->id;
        $this->reviewed_at = now();
        $this->review_notes = $notes;
        $this->save();
    }

    /**
     * Mark as conditional
     */
    public function markAsConditional(User $reviewer, string $notes): void
    {
        $this->status = self::STATUS_CONDITIONAL;
        $this->reviewed_by = $reviewer->id;
        $this->reviewed_at = now();
        $this->review_notes = $notes;
        $this->save();
    }

    /**
     * Mark as graduated
     */
    public function markAsGraduated(): void
    {
        $this->status = self::STATUS_GRADUATED;
        $this->graduation_date = now();
        $this->save();
    }

    /**
     * Update clearance status
     */
    public function updateClearance(string $type, bool $cleared): void
    {
        switch ($type) {
            case 'academic':
                $this->academic_clearance = $cleared;
                break;
            case 'financial':
                $this->financial_clearance = $cleared;
                break;
            case 'library':
                $this->library_clearance = $cleared;
                break;
        }
        
        $this->save();
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        switch ($this->status) {
            case self::STATUS_DRAFT:
                return 'Draft';
            case self::STATUS_SUBMITTED:
                return 'Submitted';
            case self::STATUS_UNDER_REVIEW:
                return 'Under Review';
            case self::STATUS_APPROVED:
                return 'Approved';
            case self::STATUS_CONDITIONAL:
                return 'Conditional';
            case self::STATUS_DENIED:
                return 'Denied';
            case self::STATUS_GRADUATED:
                return 'Graduated';
            default:
                return 'Unknown';
        }
    }

    /**
     * Get honors label
     */
    public function getHonorsLabel(): ?string
    {
        switch ($this->honors) {
            case self::HONORS_CUM_LAUDE:
                return 'Cum Laude';
            case self::HONORS_MAGNA_CUM_LAUDE:
                return 'Magna Cum Laude';
            case self::HONORS_SUMMA_CUM_LAUDE:
                return 'Summa Cum Laude';
            default:
                return null;
        }
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        switch ($this->status) {
            case self::STATUS_DRAFT:
                return 'secondary';
            case self::STATUS_SUBMITTED:
                return 'info';
            case self::STATUS_UNDER_REVIEW:
                return 'warning';
            case self::STATUS_APPROVED:
            case self::STATUS_GRADUATED:
                return 'success';
            case self::STATUS_CONDITIONAL:
                return 'warning';
            case self::STATUS_DENIED:
                return 'danger';
            default:
                return 'light';
        }
    }
}