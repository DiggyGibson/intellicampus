<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // System Fields
        'student_id',
        'user_id',
        
        // Personal Information
        'first_name',
        'middle_name',
        'last_name',
        'preferred_name',
        'email',
        'secondary_email',
        'phone',
        'home_phone',
        'work_phone',
        'date_of_birth',
        'place_of_birth',
        'gender',
        'marital_status',
        'religion',
        'ethnicity',
        'nationality',
        'national_id_number',
        'passport_number',
        
        // Addresses
        'address',
        'permanent_address',
        
        // Academic Information
        'program_name',
        'program_id',
        'department',
        'major',
        'minor',
        'academic_level',
        'enrollment_status',
        'academic_standing',
        'admission_status',
        'admission_date',
        'expected_graduation_year',
        'expected_graduation_date',
        'graduation_date',
        'degree_awarded',
        'current_gpa',
        'cumulative_gpa',
        'semester_gpa',
        'major_gpa',
        'credits_earned',
        'credits_completed',
        'credits_required',
        'total_credits_earned',
        'total_credits_attempted',
        
        // Previous Education
        'high_school_name',
        'high_school_graduation_year',
        'high_school_gpa',
        'previous_university',
        'transfer_credits_info',
        'previous_education',
        
        // Advisory
        'advisor_name',
        'advisor_id',  // Added for relationship
        
        // Guardian/Emergency Contacts
        'guardian_name',
        'guardian_phone',
        'guardian_email',
        'emergency_contact_name',
        'emergency_contact_phone',
        'next_of_kin_name',
        'next_of_kin_relationship',
        'next_of_kin_phone',
        
        // Medical Information
        'blood_group',
        'medical_conditions',
        'insurance_provider',
        'insurance_policy_number',
        'insurance_expiry',
        
        // Document Flags
        'profile_photo',
        'has_profile_photo',
        'has_national_id_copy',
        'has_high_school_certificate',
        'has_high_school_transcript',
        'has_immunization_records',
        
        // Special Status
        'is_athlete',
        'is_honors',
        'has_disability_accommodation',
        
        // International Student
        'is_international',
        'visa_status',
        'visa_expiry',
        
        // Enrollment Lifecycle
        'application_date',
        'admission_decision_date',
        'enrollment_confirmation_date',
        'last_enrollment_date',
        'leave_start_date',
        'leave_end_date',
        'leave_reason',
        'withdrawal_date',
        'withdrawal_reason',
        'readmission_date',
        'is_alumni',
        
        // System Tracking
        'created_by',
        'updated_by',
        'change_history',
        'last_activity_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'expected_graduation_date' => 'date',
        'visa_expiry' => 'date',
        'insurance_expiry' => 'date',
        'application_date' => 'date',
        'admission_decision_date' => 'date',
        'enrollment_confirmation_date' => 'date',
        'last_enrollment_date' => 'date',
        'leave_start_date' => 'date',
        'leave_end_date' => 'date',
        'withdrawal_date' => 'date',
        'readmission_date' => 'date',
        'graduation_date' => 'date',
        'last_activity_at' => 'datetime',
        'current_gpa' => 'decimal:2',
        'cumulative_gpa' => 'decimal:2',
        'semester_gpa' => 'decimal:2',
        'major_gpa' => 'decimal:2',
        'high_school_gpa' => 'decimal:2',
        'is_international' => 'boolean',
        'is_alumni' => 'boolean',
        'is_athlete' => 'boolean',
        'is_honors' => 'boolean',
        'has_disability_accommodation' => 'boolean',
        'has_profile_photo' => 'boolean',
        'has_national_id_copy' => 'boolean',
        'has_high_school_certificate' => 'boolean',
        'has_high_school_transcript' => 'boolean',
        'has_immunization_records' => 'boolean',
        'change_history' => 'array',
    ];

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate student ID on creation
        static::creating(function ($student) {
            // Only generate if not already set
            if (empty($student->student_id)) {
                // Format: YYXXXXXX (YY = year, XXXXXX = sequential number)
                $year = date('y');
                
                // Get the last student created this year
                $lastStudent = self::whereYear('created_at', date('Y'))
                    ->orderBy('id', 'desc')
                    ->first();
                
                if ($lastStudent && substr($lastStudent->student_id, 0, 2) == $year) {
                    // Extract the sequence number and increment
                    $sequence = intval(substr($lastStudent->student_id, 2)) + 1;
                } else {
                    // First student of the year
                    $sequence = 1;
                }
                
                $student->student_id = $year . str_pad($sequence, 6, '0', STR_PAD_LEFT);
            }

            // Set created_by if auth user exists
            if (auth()->check()) {
                $student->created_by = auth()->id();
            }
        });

        // Auto-create user account after student is created
        static::created(function ($student) {
            // Only create user if not already linked
            if (!$student->user_id) {
                $student->createUserAccount();
            }
        });

        // Track updates
        static::updating(function ($student) {
            if (auth()->check()) {
                $student->updated_by = auth()->id();
                
                // Add to change history
                $history = $student->change_history ?? [];
                $history[] = [
                    'user_id' => auth()->id(),
                    'timestamp' => now()->toIso8601String(),
                    'changes' => $student->getDirty()
                ];
                $student->change_history = $history;
            }
        });

        // Sync user data when student is updated
        static::updated(function ($student) {
            if ($student->user_id && $student->user) {
                // Update user data to match student data
                $student->syncUserData();
            }
        });
    }

    /**
     * Create a user account for this student
     */
    public function createUserAccount()
    {
        try {
            // Check if user with this email already exists
            $existingUser = User::where('email', $this->email)->first();
            
            if ($existingUser) {
                // Link to existing user
                $this->user_id = $existingUser->id;
                $this->saveQuietly(); // Save without triggering events
                
                // Update user type if needed
                if ($existingUser->user_type !== 'student') {
                    $existingUser->user_type = 'student';
                    $existingUser->save();
                }
                
                $this->assignStudentRole($existingUser);
                $this->recordActivity('Linked to existing user account');
                
                return $existingUser;
            }
            
            // Generate unique username
            $username = $this->generateUniqueUsername();
            
            // Create new user
            $user = User::create([
                'name' => $this->full_name,
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'username' => $username,
                'password' => Hash::make($this->student_id), // Default password is student ID
                'user_type' => 'student',
                'status' => $this->mapEnrollmentStatusToUserStatus(),
                'phone' => $this->phone,
                'alternate_phone' => $this->home_phone,
                'date_of_birth' => $this->date_of_birth,
                'gender' => ucfirst($this->gender),
                'nationality' => $this->nationality,
                'national_id' => $this->national_id_number,
                'passport_number' => $this->passport_number,
                'address' => $this->address,
                'emergency_contact_name' => $this->emergency_contact_name,
                'emergency_contact_phone' => $this->emergency_contact_phone,
                'emergency_contact_relationship' => $this->next_of_kin_relationship,
                'must_change_password' => true, // Force password change on first login
                'email_verified_at' => now(), // Auto-verify since we trust student data
            ]);
            
            // Link student to user
            $this->user_id = $user->id;
            $this->saveQuietly();
            
            // Assign student role
            $this->assignStudentRole($user);
            
            // Record activity
            $this->recordActivity('User account created automatically');
            
            return $user;
            
        } catch (\Exception $e) {
            \Log::error('Failed to create user account for student ' . $this->student_id . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Sync user data with student data
     */
    public function syncUserData()
    {
        if (!$this->user) {
            return;
        }

        $this->user->update([
            'name' => $this->full_name,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'alternate_phone' => $this->home_phone,
            'date_of_birth' => $this->date_of_birth,
            'gender' => ucfirst($this->gender),
            'nationality' => $this->nationality,
            'national_id' => $this->national_id_number,
            'passport_number' => $this->passport_number,
            'address' => $this->address,
            'status' => $this->mapEnrollmentStatusToUserStatus(),
        ]);
    }

    /**
     * Generate a unique username for the student
     */
    protected function generateUniqueUsername()
    {
        // Start with first letter of first name + last name
        $base = strtolower(substr($this->first_name, 0, 1) . $this->last_name);
        $base = preg_replace('/[^a-z0-9]/', '', $base);
        
        // If too short, use full first name
        if (strlen($base) < 4) {
            $base = strtolower($this->first_name . $this->last_name);
            $base = preg_replace('/[^a-z0-9]/', '', $base);
        }
        
        // Make it unique
        $username = $base;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }
        
        return $username;
    }

    /**
     * Map enrollment status to user status
     */
    protected function mapEnrollmentStatusToUserStatus()
    {
        return match($this->enrollment_status) {
            'active', 'enrolled' => 'active',
            'inactive' => 'inactive',
            'suspended' => 'suspended',
            'graduated', 'alumni' => 'inactive',
            'withdrawn' => 'inactive',
            default => 'pending'
        };
    }

    /**
     * Assign student role to user
     */
    protected function assignStudentRole($user)
    {
        $studentRole = Role::where('slug', 'student')
                          ->orWhere('name', 'Student')
                          ->first();
        
        if ($studentRole && !$user->hasRole($studentRole)) {
            $user->assignRole($studentRole);
        }
    }

    /**
     * Check if student has a user account
     */
    public function hasUserAccount()
    {
        return $this->user_id && $this->user;
    }

    /**
     * Ensure student has a user account (create if doesn't exist)
     */
    public function ensureUserAccount()
    {
        if (!$this->hasUserAccount()) {
            return $this->createUserAccount();
        }
        return $this->user;
    }

    /**
     * ==========================================
     * RELATIONSHIPS - EXISTING AND NEW
     * ==========================================
     */

    /**
     * Get the program - UPDATED to use AcademicProgram model
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    /**
     * Get the user associated with the student
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get enrollments
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get registrations
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Get grades through enrollments
     */
    public function grades()
    {
        return $this->hasManyThrough(Grade::class, Enrollment::class);
    }

    /**
     * Get enrollment histories
     */
    public function enrollmentHistories(): HasMany
    {
        return $this->hasMany(EnrollmentHistory::class)->orderBy('effective_date', 'desc');
    }

    /**
     * Get creator user
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get updater user
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the advisor for this student
     */
    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    /**
     * ==========================================
     * DEGREE AUDIT SYSTEM RELATIONSHIPS - NEW
     * ==========================================
     */

    /**
     * Get the degree progress records for this student
     * THIS IS THE CRITICAL MISSING RELATIONSHIP
     */
    public function degreeProgress(): HasMany
    {
        return $this->hasMany(StudentDegreeProgress::class);
    }

    /**
     * Get the course applications for this student
     */
    public function courseApplications(): HasMany
    {
        return $this->hasMany(StudentCourseApplication::class);
    }

    /**
     * Get the degree audit reports for this student
     */
    public function auditReports(): HasMany
    {
        return $this->hasMany(DegreeAuditReport::class);
    }

    /**
     * Get the most recent audit report
     */
    public function latestAuditReport(): HasOne
    {
        return $this->hasOne(DegreeAuditReport::class)->latestOfMany('generated_at');
    }

    /**
     * Get the academic plans for this student
     */
    public function academicPlans(): HasMany
    {
        return $this->hasMany(AcademicPlan::class);
    }

    /**
     * Get the current academic plan
     */
    public function currentAcademicPlan(): HasOne
    {
        return $this->hasOne(AcademicPlan::class)->where('is_current', true);
    }

    /**
     * Get what-if scenarios for this student
     */
    public function whatIfScenarios(): HasMany
    {
        return $this->hasMany(WhatIfScenario::class);
    }

    /**
     * Get graduation applications for this student
     */
    public function graduationApplications(): HasMany
    {
        return $this->hasMany(GraduationApplication::class);
    }

    /**
     * Get requirement substitutions for this student
     */
    public function requirementSubstitutions(): HasMany
    {
        return $this->hasMany(RequirementSubstitution::class);
    }

    /**
     * ==========================================
     * ACCESSORS - EXISTING AND NEW
     * ==========================================
     */

    public function getFullNameAttribute()
    {
        $name = $this->preferred_name ?: $this->first_name;
        return trim("{$name} {$this->middle_name} {$this->last_name}");
    }

    public function getDisplayNameAttribute()
    {
        if ($this->preferred_name) {
            return "{$this->preferred_name} ({$this->first_name}) {$this->last_name}";
        }
        return $this->full_name;
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getIsActiveAttribute()
    {
        return $this->enrollment_status === 'active';
    }

    public function getIsOnLeaveAttribute()
    {
        return $this->leave_start_date && 
               $this->leave_end_date && 
               now()->between($this->leave_start_date, $this->leave_end_date);
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->credits_required > 0) {
            return round(($this->credits_earned / $this->credits_required) * 100, 2);
        }
        return 0;
    }

    /**
     * Get the catalog year for this student (NEW)
     */
    public function getCatalogYearAttribute(): string
    {
        // Based on admission year
        if ($this->admission_date) {
            $year = date('Y', strtotime($this->admission_date));
            return $year . '-' . ($year + 1);
        }
        
        // Default to current year
        $currentYear = date('Y');
        return $currentYear . '-' . ($currentYear + 1);
    }

    /**
     * Calculate total completed credits from enrollments (NEW)
     */
    public function getTotalCompletedCreditsAttribute(): float
    {
        return $this->enrollments()
            ->where('enrollment_status', 'completed')
            ->whereHas('section.course')
            ->get()
            ->sum(function ($enrollment) {
                return $enrollment->section->course->credits ?? 0;
            });
    }

    /**
     * Calculate credits in progress from current enrollments (NEW)
     */
    public function getCreditsInProgressAttribute(): float
    {
        return $this->enrollments()
            ->whereIn('enrollment_status', ['enrolled', 'in_progress'])
            ->whereHas('section.course')
            ->get()
            ->sum(function ($enrollment) {
                return $enrollment->section->course->credits ?? 0;
            });
    }

    /**
     * ==========================================
     * SCOPES
     * ==========================================
     */

    public function scopeActive($query)
    {
        return $query->where('enrollment_status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('enrollment_status', 'inactive');
    }

    public function scopeGraduated($query)
    {
        return $query->where('enrollment_status', 'graduated');
    }

    public function scopeOnProbation($query)
    {
        return $query->where('academic_standing', 'probation');
    }

    public function scopeInGoodStanding($query)
    {
        return $query->where('academic_standing', 'good');
    }

    public function scopeInternational($query)
    {
        return $query->where('is_international', true);
    }

    public function scopeByProgram($query, $program)
    {
        return $query->where('program_name', $program);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByAcademicLevel($query, $level)
    {
        return $query->where('academic_level', $level);
    }

    public function scopeInProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function scopeWithAdvisor($query, $advisorId)
    {
        return $query->where('advisor_id', $advisorId);
    }

    /**
     * ==========================================
     * METHODS
     * ==========================================
     */

    public function canRegister()
    {
        return $this->enrollment_status === 'active' && 
               $this->academic_standing !== 'suspension' &&
               $this->academic_standing !== 'dismissal';
    }

    public function updateAcademicStanding()
    {
        if ($this->cumulative_gpa >= 2.0) {
            $this->academic_standing = 'good';
        } elseif ($this->cumulative_gpa >= 1.5) {
            $this->academic_standing = 'probation';
        } else {
            $this->academic_standing = 'suspension';
        }
        $this->save();
    }

    public function recordActivity($action)
    {
        $this->last_activity_at = now();
        $this->save();
        
        // You could also log this to an activity log table if you have one
        \Log::info("Student {$this->student_id}: {$action}");
    }

    public function processGraduation()
    {
        if ($this->credits_earned >= $this->credits_required && $this->cumulative_gpa >= 2.0) {
            $this->enrollment_status = 'graduated';
            $this->graduation_date = now();
            $this->is_alumni = true;
            $this->save();
            
            // Update user status
            if ($this->user) {
                $this->user->update(['status' => 'inactive']);
            }
            
            return true;
        }
        return false;
    }

    /**
     * Check if student has a hold (NEW)
     */
    public function hasHold(): bool
    {
        // Check if student_holds table exists and has records
        if (\Schema::hasTable('student_holds')) {
            return \DB::table('student_holds')
                ->where('student_id', $this->id)
                ->where('is_active', true)
                ->exists();
        }
        return false;
    }

    public function startLeaveOfAbsence($endDate, $reason)
    {
        $this->enrollment_status = 'inactive';
        $this->leave_start_date = now();
        $this->leave_end_date = $endDate;
        $this->leave_reason = $reason;
        $this->save();
        
        // Update user status
        if ($this->user) {
            $this->user->update(['status' => 'inactive']);
        }
    }

    public function withdraw($reason)
    {
        $this->enrollment_status = 'withdrawn';
        $this->withdrawal_date = now();
        $this->withdrawal_reason = $reason;
        $this->save();
        
        // Update user status
        if ($this->user) {
            $this->user->update(['status' => 'inactive']);
        }
    }

    // Enrollment workflow methods
    public function requestLeaveOfAbsence($reason, $startDate, $endDate, $notes = null)
    {
        $history = EnrollmentHistory::create([
            'student_id' => $this->id,
            'action_type' => 'leave_request',
            'from_status' => $this->enrollment_status,
            'to_status' => 'inactive',
            'reason' => $reason,
            'notes' => $notes,
            'effective_date' => $startDate,
            'end_date' => $endDate,
            'created_by' => auth()->user()->name ?? 'System',
        ]);
        
        $this->enrollment_status = 'inactive';
        $this->leave_start_date = $startDate;
        $this->leave_end_date = $endDate;
        $this->leave_reason = $reason;
        $this->save();
        
        // Update user status
        if ($this->user) {
            $this->user->update(['status' => 'inactive']);
        }
        
        return $history;
    }

    public function returnFromLeave($notes = null)
    {
        $history = EnrollmentHistory::create([
            'student_id' => $this->id,
            'action_type' => 'return_from_leave',
            'from_status' => 'inactive',
            'to_status' => 'active',
            'notes' => $notes,
            'effective_date' => now(),
            'created_by' => auth()->user()->name ?? 'System',
        ]);
        
        $this->enrollment_status = 'active';
        $this->leave_start_date = null;
        $this->leave_end_date = null;
        $this->leave_reason = null;
        $this->last_enrollment_date = now();
        $this->save();
        
        // Update user status
        if ($this->user) {
            $this->user->update(['status' => 'active']);
        }
        
        return $history;
    }

    public function processWithdrawal($reason, $effectiveDate = null, $notes = null)
    {
        $history = EnrollmentHistory::create([
            'student_id' => $this->id,
            'action_type' => 'withdrawal',
            'from_status' => $this->enrollment_status,
            'to_status' => 'withdrawn',
            'reason' => $reason,
            'notes' => $notes,
            'effective_date' => $effectiveDate ?? now(),
            'created_by' => auth()->user()->name ?? 'System',
        ]);
        
        $this->enrollment_status = 'withdrawn';
        $this->withdrawal_date = $effectiveDate ?? now();
        $this->withdrawal_reason = $reason;
        $this->save();
        
        // Update user status
        if ($this->user) {
            $this->user->update(['status' => 'inactive']);
        }
        
        return $history;
    }

    public function processReadmission($notes = null)
    {
        $history = EnrollmentHistory::create([
            'student_id' => $this->id,
            'action_type' => 'readmission',
            'from_status' => $this->enrollment_status,
            'to_status' => 'active',
            'notes' => $notes,
            'effective_date' => now(),
            'created_by' => auth()->user()->name ?? 'System',
            'approved_by' => auth()->user()->name ?? 'System',
            'approved_at' => now(),
        ]);
        
        $this->enrollment_status = 'active';
        $this->readmission_date = now();
        $this->last_enrollment_date = now();
        $this->save();
        
        // Update user status
        if ($this->user) {
            $this->user->update(['status' => 'active']);
        }
        
        return $history;
    }

    public function graduateStudent($degreeAwarded, $graduationDate = null)
    {
        $history = EnrollmentHistory::create([
            'student_id' => $this->id,
            'action_type' => 'graduation',
            'from_status' => $this->enrollment_status,
            'to_status' => 'graduated',
            'notes' => "Degree Awarded: {$degreeAwarded}",
            'effective_date' => $graduationDate ?? now(),
            'created_by' => auth()->user()->name ?? 'System',
        ]);
        
        $this->enrollment_status = 'graduated';
        $this->graduation_date = $graduationDate ?? now();
        $this->degree_awarded = $degreeAwarded;
        $this->is_alumni = true;
        $this->save();
        
        // Update user status to inactive (alumni)
        if ($this->user) {
            $this->user->update(['status' => 'inactive']);
        }
        
        return $history;
    }

    public function suspendStudent($reason, $effectiveDate = null)
    {
        $history = EnrollmentHistory::create([
            'student_id' => $this->id,
            'action_type' => 'suspension',
            'from_status' => $this->enrollment_status,
            'to_status' => 'suspended',
            'reason' => $reason,
            'effective_date' => $effectiveDate ?? now(),
            'created_by' => auth()->user()->name ?? 'System',
        ]);
        
        $this->enrollment_status = 'suspended';
        $this->save();
        
        // Update user status
        if ($this->user) {
            $this->user->update(['status' => 'suspended']);
        }
        
        return $history;
    }
}