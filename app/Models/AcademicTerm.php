<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicTerm extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'academic_terms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
        'type',
        'academic_year',
        'start_date',
        'end_date',
        'registration_start',
        'registration_end',
        'add_drop_deadline',
        'withdrawal_deadline',
        'grades_due_date',
        'drop_deadline',
        'is_current',
        'is_active',
        'important_dates',
        // Admission-related fields
        'is_admission_open',
        'admission_deadline',
        'admission_start_date',
        'early_admission_deadline',
        'admission_notification_date',
        'total_spots'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'academic_year' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'registration_start' => 'date',
        'registration_end' => 'date',
        'add_drop_deadline' => 'date',
        'withdrawal_deadline' => 'date',
        'grades_due_date' => 'date',
        'drop_deadline' => 'date',
        'is_current' => 'boolean',
        'is_active' => 'boolean',
        'important_dates' => 'array',
        // Admission date casts
        'is_admission_open' => 'boolean',
        'admission_deadline' => 'date',
        'admission_start_date' => 'date',
        'early_admission_deadline' => 'date',
        'admission_notification_date' => 'date',
        'total_spots' => 'integer'
    ];

    /**
     * Get the course sections for this term.
     */
    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class, 'term_id');
    }

    /**
     * Get enrollments for this term.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'academic_term_id');
    }

    /**
     * Get billing items for this term.
     */
    public function billingItems(): HasMany
    {
        return $this->hasMany(BillingItem::class, 'academic_term_id');
    }

    /**
     * Get financial aid for this term.
     */
    public function financialAid(): HasMany
    {
        return $this->hasMany(FinancialAid::class, 'academic_term_id');
    }

    /**
     * Get admission applications for this term.
     */
    public function admissionApplications(): HasMany
    {
        return $this->hasMany(AdmissionApplication::class, 'term_id');
    }

    /**
     * Get admission settings for this term.
     */
    public function admissionSettings(): HasMany
    {
        return $this->hasMany(AdmissionSetting::class, 'term_id');
    }

    /**
     * Scope a query to only include current terms.
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope a query to only include active terms.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to get terms by academic year.
     */
    public function scopeByYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    /**
     * Scope for terms with open admissions.
     */
    public function scopeAdmissionsOpen($query)
    {
        return $query->where('is_admission_open', true)
            ->where('admission_deadline', '>', now());
    }

    /**
     * Get the formatted name with year.
     */
    public function getFullNameAttribute()
    {
        return "{$this->name} {$this->academic_year}";
    }

    /**
     * Check if registration is open.
     */
    public function isRegistrationOpen()
    {
        $now = now();
        return $this->registration_start && $this->registration_end 
            && $now->between($this->registration_start, $this->registration_end);
    }

    /**
     * Check if term is ongoing.
     */
    public function isOngoing()
    {
        $now = now();
        return $this->start_date && $this->end_date 
            && $now->between($this->start_date, $this->end_date);
    }

    /**
     * Check if add/drop period is active.
     */
    public function isAddDropPeriod()
    {
        return $this->add_drop_deadline && now()->lte($this->add_drop_deadline);
    }

    /**
     * Check if withdrawal period is active.
     */
    public function canWithdraw()
    {
        return $this->withdrawal_deadline && now()->lte($this->withdrawal_deadline);
    }

    /**
     * Check if admissions are open.
     */
    public function isAdmissionOpen()
    {
        if (!$this->is_admission_open) {
            return false;
        }

        $now = now();
        
        // Check if we're within the admission period
        if ($this->admission_start_date && $now->lt($this->admission_start_date)) {
            return false;
        }
        
        if ($this->admission_deadline && $now->gt($this->admission_deadline)) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if early admission deadline has passed.
     */
    public function isEarlyAdmissionOpen()
    {
        return $this->early_admission_deadline 
            && now()->lte($this->early_admission_deadline);
    }

    /**
     * Get days until admission deadline.
     */
    public function getDaysUntilAdmissionDeadlineAttribute()
    {
        if (!$this->admission_deadline) {
            return null;
        }
        
        return now()->diffInDays($this->admission_deadline, false);
    }

    /**
     * Get admission deadline status.
     */
    public function getAdmissionDeadlineStatusAttribute()
    {
        if (!$this->admission_deadline) {
            return 'Not Set';
        }

        $days = $this->days_until_admission_deadline;
        
        if ($days < 0) {
            return 'Closed';
        } elseif ($days == 0) {
            return 'Today';
        } elseif ($days <= 7) {
            return 'Closing Soon';
        } else {
            return 'Open';
        }
    }

    /**
     * Get available admission spots.
     */
    public function getAvailableSpotsAttribute()
    {
        if (!$this->total_spots) {
            return null;
        }

        $confirmed = $this->admissionApplications()
            ->where('enrollment_confirmed', true)
            ->count();

        return max(0, $this->total_spots - $confirmed);
    }
}