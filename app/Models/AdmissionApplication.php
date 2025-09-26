<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdmissionApplication extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'admission_applications';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // Application Identifier
        'application_number',
        'application_uuid',
        
        // Personal Information
        'first_name',
        'middle_name',
        'last_name',
        'preferred_name',
        'date_of_birth',
        'gender',
        'nationality',
        'country_of_birth',
        'passport_number',
        'national_id',
        
        // Contact Information
        'email',
        'phone_primary',
        'phone_secondary',
        'current_address',
        'permanent_address',
        'city',
        'state_province',
        'postal_code',
        'country',
        
        // Emergency Contact
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'emergency_contact_email',
        
        // Parent/Guardian Information
        'parent_guardian_name',
        'parent_guardian_occupation',
        'parent_guardian_phone',
        'parent_guardian_email',
        'parent_guardian_income',
        
        // Application Details
        'application_type',
        'term_id',
        'program_id',
        'alternate_program_id',
        'intended_major',
        'intended_minor',
        'entry_type',
        'entry_year',
        
        // Educational Background
        'previous_institution',
        'previous_institution_country',
        'previous_institution_graduation_date',
        'previous_degree',
        'previous_major',
        'previous_gpa',
        'gpa_scale',
        'class_rank',
        'class_size',
        
        // High School Information
        'high_school_name',
        'high_school_country',
        'high_school_graduation_date',
        'high_school_diploma_type',
        
        // Test Scores
        'test_scores',
        
        // Essays
        'personal_statement',
        'statement_of_purpose',
        'additional_essay_1',
        'additional_essay_2',
        'research_interests',
        
        // Activities
        'extracurricular_activities',
        'awards_honors',
        'work_experience',
        'volunteer_experience',
        
        // References
        'references',
        
        // Document
        'documents',

        // Status
        'status',
        'decision',
        'decision_date',
        'decision_by',
        'decision_reason',
        'admission_conditions',
        
        // Enrollment
        'enrollment_confirmed',
        'enrollment_confirmation_date',
        'enrollment_deposit_paid',
        'enrollment_deposit_amount',
        'enrollment_deposit_date',
        'enrollment_deposit_receipt',
        'enrollment_deadline',
        'enrollment_declined',
        'enrollment_declined_date',
        'enrollment_declined_reason',
        
        // Fees
        'application_fee_paid',
        'application_fee_amount',
        'application_fee_date',
        'application_fee_receipt',
        'fee_waiver_requested',
        'fee_waiver_approved',
        'fee_waiver_reason',
        
        // Timestamps
        'started_at',
        'submitted_at',
        'completed_at',
        'last_updated_at',
        'expires_at',
        
        // User Link
        'user_id',
        
        // Audit
        'ip_address',
        'user_agent',
        'activity_log'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'previous_institution_graduation_date' => 'date',
        'high_school_graduation_date' => 'date',
        'decision_date' => 'date',
        'enrollment_confirmation_date' => 'date',
        'enrollment_deposit_date' => 'date',
        'enrollment_deadline' => 'date',
        'enrollment_declined_date' => 'date',
        'application_fee_date' => 'date',
        'test_scores' => 'array',
        'extracurricular_activities' => 'array',
        'awards_honors' => 'array',
        'work_experience' => 'array',
        'volunteer_experience' => 'array',
        'references' => 'array',
        'documents' => 'array',
        'activity_log' => 'array',
        'enrollment_confirmed' => 'boolean',
        'enrollment_deposit_paid' => 'boolean',
        'enrollment_declined' => 'boolean',
        'application_fee_paid' => 'boolean',
        'fee_waiver_requested' => 'boolean',
        'fee_waiver_approved' => 'boolean',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_updated_at' => 'datetime',
        'expires_at' => 'datetime',
        'previous_gpa' => 'decimal:2',
        'parent_guardian_income' => 'decimal:2',
        'enrollment_deposit_amount' => 'decimal:2',
        'application_fee_amount' => 'decimal:2',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'ip_address',
        'user_agent',
        'passport_number',
        'national_id',
        'parent_guardian_income'
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate application number and UUID on creation
        static::creating(function ($application) {
            if (empty($application->application_number)) {
                $application->application_number = self::generateApplicationNumber();
            }
            if (empty($application->application_uuid)) {
                $application->application_uuid = Str::uuid();
            }
            if (empty($application->started_at)) {
                $application->started_at = now();
            }
            if (empty($application->expires_at)) {
                $application->expires_at = now()->addDays(90); // 90 days to complete
            }
        });

        // Update last_updated_at timestamp
        static::updating(function ($application) {
            $application->last_updated_at = now();
            
            // Log status changes
            if ($application->isDirty('status')) {
                $log = $application->activity_log ?? [];
                $log[] = [
                    'timestamp' => now()->toIso8601String(),
                    'action' => 'status_change',
                    'from' => $application->getOriginal('status'),
                    'to' => $application->status,
                    'user_id' => auth()->id()
                ];
                $application->activity_log = $log;
            }
        });
    }

    /**
     * Generate unique application number.
     */
    public static function generateApplicationNumber(): string
    {
        $year = date('Y');
        $lastApplication = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastApplication) {
            $lastNumber = intval(substr($lastApplication->application_number, -6));
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }
        
        return "APP-{$year}-{$newNumber}";
    }

    /**
     * Relationships
     */

    /**
     * Get the user associated with the application.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the academic term for the application.
     */
    public function term()
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    /**
     * Get the primary program for the application.
     */
    public function program()
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    /**
     * Get the alternate program for the application.
     */
    public function alternateProgram()
    {
        return $this->belongsTo(AcademicProgram::class, 'alternate_program_id');
    }

    /**
     * Get the user who made the admission decision.
     */
    public function decisionMaker()
    {
        return $this->belongsTo(User::class, 'decision_by');
    }

    /**
     * =====================================
     * UPDATED DOCUMENT RELATIONSHIPS
     * Using Unified Document System
     * =====================================
     */

    /**
     * Get documents through the unified document system
     * This replaces the old documents() method that used ApplicationDocument
     */
    public function documents()
    {
        return $this->belongsToMany(
            Document::class, 
            'document_relationships', 
            'owner_id', 
            'document_id'
        )
        ->wherePivot('owner_type', 'application')
        ->withPivot(['purpose', 'is_verified', 'is_required', 'sort_order'])
        ->withTimestamps();
    }

    /**
     * Get legacy documents (for backward compatibility during migration)
     * This can be removed once migration is complete
     */
    public function legacyDocuments()
    {
        return $this->hasMany(ApplicationDocument::class, 'application_id');
    }

    /**
     * Get documents of a specific type/purpose
     */
    public function getDocumentsByType($type)
    {
        return $this->documents()
            ->wherePivot('purpose', $type)
            ->get();
    }

    /**
     * Get verified documents only
     */
    public function verifiedDocuments()
    {
        return $this->documents()
            ->where('verification_status', 'verified')
            ->get();
    }

    /**
     * Get pending verification documents
     */
    public function pendingDocuments()
    {
        return $this->documents()
            ->where('verification_status', 'pending')
            ->get();
    }

    /**
     * Check if has a specific document type
     */
    public function hasDocumentType($type): bool
    {
        return $this->documents()
            ->wherePivot('purpose', $type)
            ->exists();
    }

    /**
     * Check if has all required documents
     */
    public function hasAllRequiredDocuments(): bool
    {
        $requiredTypes = $this->getRequiredDocumentTypes();
        
        foreach ($requiredTypes as $type) {
            if (!$this->hasDocumentType($type)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check if all documents are verified
     */
    public function allDocumentsVerified(): bool
    {
        return $this->documents()
            ->where('verification_status', '!=', 'verified')
            ->count() === 0;
    }

    /**
     * Get required document types based on application type
     */
    public function getRequiredDocumentTypes(): array
    {
        $baseDocuments = ['transcript', 'personal_statement'];
        
        switch ($this->application_type) {
            case 'freshman':
                return array_merge($baseDocuments, [
                    'high_school_transcript',
                    'recommendation_letter'
                ]);
                
            case 'transfer':
                return array_merge($baseDocuments, [
                    'university_transcript',
                    'course_descriptions'
                ]);
                
            case 'graduate':
                return array_merge($baseDocuments, [
                    'university_transcript',
                    'recommendation_letter',
                    'resume',
                    'statement_of_purpose'
                ]);
                
            case 'international':
                return array_merge($baseDocuments, [
                    'passport',
                    'english_proficiency',
                    'financial_statement',
                    'recommendation_letter'
                ]);
                
            default:
                return $baseDocuments;
        }
    }

    /**
     * Get document upload progress
     */
    public function documentUploadProgress(): array
    {
        $required = $this->getRequiredDocumentTypes();
        $uploaded = $this->documents()->pluck('purpose')->toArray();
        $verified = $this->verifiedDocuments()->pluck('purpose')->toArray();
        
        return [
            'required' => $required,
            'uploaded' => $uploaded,
            'verified' => $verified,
            'missing' => array_diff($required, $uploaded),
            'pending_verification' => array_diff($uploaded, $verified),
            'progress_percentage' => count($required) > 0 
                ? round((count($uploaded) / count($required)) * 100) 
                : 0,
            'verification_percentage' => count($uploaded) > 0 
                ? round((count($verified) / count($uploaded)) * 100) 
                : 0
        ];
    }

    /**
     * =====================================
     * END OF DOCUMENT SYSTEM UPDATES
     * =====================================
     */

    /**
     * Get the reviews for the application.
     */
    public function reviews()
    {
        return $this->hasMany(ApplicationReview::class, 'application_id');
    }

    /**
     * Get the checklist items for the application.
     */
    public function checklistItems()
    {
        return $this->hasMany(ApplicationChecklistItem::class, 'application_id');
    }

    /**
     * Get the communications for the application.
     */
    public function communications()
    {
        return $this->hasMany(ApplicationCommunication::class, 'application_id');
    }

    /**
     * Get the enrollment confirmation for the application.
     */
    public function enrollmentConfirmation()
    {
        return $this->hasOne(EnrollmentConfirmation::class, 'application_id');
    }

    /**
     * Get the fees for the application.
     */
    public function fees()
    {
        return $this->hasMany(ApplicationFee::class, 'application_id');
    }

    /**
     * Get the entrance exam registrations for the application.
     */
    public function examRegistrations()
    {
        return $this->hasMany(EntranceExamRegistration::class, 'application_id');
    }

    /**
     * Get the interviews for the application.
     */
    public function interviews()
    {
        return $this->hasMany(AdmissionInterview::class, 'application_id');
    }

    /**
     * Get the waitlist entry for the application.
     */
    public function waitlist()
    {
        return $this->hasOne(AdmissionWaitlist::class, 'application_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for filtering by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for submitted applications.
     */
    public function scopeSubmitted($query)
    {
        return $query->whereNotNull('submitted_at');
    }

    /**
     * Scope for pending review applications.
     */
    public function scopePendingReview($query)
    {
        return $query->whereIn('status', ['submitted', 'under_review', 'documents_pending']);
    }

    /**
     * Scope for admitted applications.
     */
    public function scopeAdmitted($query)
    {
        return $query->where('decision', 'admit');
    }

    /**
     * Scope for current term applications.
     */
    public function scopeCurrentTerm($query)
    {
        $currentTermId = AcademicTerm::current()->first()->id ?? null;
        return $query->where('term_id', $currentTermId);
    }

    /**
     * Scope for incomplete applications.
     */
    public function scopeIncomplete($query)
    {
        return $query->where('status', 'draft')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope for expired applications.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'draft')
            ->where('expires_at', '<=', now());
    }

    /**
     * Helper Methods
     */

    /**
     * Check if application is complete.
     * Updated to use unified document system
     */
    public function isComplete(): bool
    {
        // Check required fields
        $requiredFields = [
            'first_name', 'last_name', 'date_of_birth', 'email', 
            'phone_primary', 'current_address', 'program_id',
            'previous_institution', 'previous_gpa', 'personal_statement'
        ];
        
        foreach ($requiredFields as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }
        
        // Check required documents using new system
        if (!$this->hasAllRequiredDocuments()) {
            return false;
        }
        
        // Check checklist items
        $requiredChecklistItems = $this->checklistItems()
            ->where('is_required', true)
            ->where('is_completed', false)
            ->count();
        
        return $requiredChecklistItems === 0 && $this->status !== 'draft';
    }

    /**
     * Check if application can be submitted.
     */
    public function canSubmit(): bool
    {
        return $this->status === 'draft' && 
               $this->application_fee_paid && 
               $this->isComplete();
    }

    /**
     * Check if enrollment can be confirmed.
     */
    public function canConfirmEnrollment(): bool
    {
        return $this->decision === 'admit' && 
               !$this->enrollment_confirmed && 
               $this->enrollment_deadline > now();
    }

    /**
     * Calculate application completion percentage.
     * Updated to use unified document system
     */
    public function completionPercentage(): int
    {
        $totalWeight = 100;
        $completedWeight = 0;
        
        // Personal information (30%)
        $personalFields = [
            'first_name', 'last_name', 'date_of_birth', 'email', 
            'phone_primary', 'current_address', 'nationality'
        ];
        $personalCompleted = 0;
        foreach ($personalFields as $field) {
            if (!empty($this->$field)) {
                $personalCompleted++;
            }
        }
        $completedWeight += ($personalCompleted / count($personalFields)) * 30;
        
        // Academic information (30%)
        $academicFields = [
            'program_id', 'previous_institution', 'previous_gpa',
            'personal_statement'
        ];
        $academicCompleted = 0;
        foreach ($academicFields as $field) {
            if (!empty($this->$field)) {
                $academicCompleted++;
            }
        }
        $completedWeight += ($academicCompleted / count($academicFields)) * 30;
        
        // Documents (30%) - Using new unified system
        $documentProgress = $this->documentUploadProgress();
        $completedWeight += ($documentProgress['progress_percentage'] / 100) * 30;
        
        // Payment (10%)
        if ($this->application_fee_paid || $this->fee_waiver_approved) {
            $completedWeight += 10;
        }
        
        return min(round($completedWeight), 100);
    }

    /**
     * Get the status badge color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'submitted' => 'blue',
            'under_review' => 'yellow',
            'documents_pending' => 'orange',
            'committee_review' => 'purple',
            'interview_scheduled' => 'indigo',
            'decision_pending' => 'pink',
            'admitted' => 'green',
            'conditional_admit' => 'lime',
            'waitlisted' => 'amber',
            'denied' => 'red',
            'deferred' => 'cyan',
            'withdrawn' => 'gray',
            'expired' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get formatted test scores.
     */
    public function getFormattedTestScores(): array
    {
        $scores = $this->test_scores ?? [];
        $formatted = [];
        
        foreach ($scores as $test => $data) {
            $formatted[$test] = match($test) {
                'SAT' => "SAT: {$data['total']} (M: {$data['math']}, V: {$data['verbal']})",
                'ACT' => "ACT: {$data['composite']}",
                'TOEFL' => "TOEFL: {$data['total']}",
                'IELTS' => "IELTS: {$data['overall']}",
                'GRE' => "GRE: V: {$data['verbal']}, Q: {$data['quantitative']}, A: {$data['analytical']}",
                default => "$test: " . json_encode($data)
            };
        }
        
        return $formatted;
    }

    /**
     * Send status notification to applicant.
     */
    public function sendStatusNotification(): void
    {
        // Implementation for sending email/SMS notification
        // This would use the ApplicationNotificationService
    }

    /**
     * Generate application summary for review.
     * Updated to include document status from unified system
     */
    public function generateSummary(): array
    {
        $documentProgress = $this->documentUploadProgress();
        
        return [
            'personal_info' => [
                'name' => $this->first_name . ' ' . $this->last_name,
                'email' => $this->email,
                'phone' => $this->phone_primary,
                'nationality' => $this->nationality,
            ],
            'academic_info' => [
                'program' => $this->program->name ?? 'N/A',
                'previous_gpa' => $this->previous_gpa,
                'previous_institution' => $this->previous_institution,
                'test_scores' => $this->getFormattedTestScores(),
            ],
            'application_status' => [
                'status' => $this->status,
                'completion' => $this->completionPercentage() . '%',
                'submitted_at' => $this->submitted_at?->format('Y-m-d H:i:s'),
                'documents_uploaded' => count($documentProgress['uploaded']),
                'documents_verified' => count($documentProgress['verified']),
                'documents_missing' => count($documentProgress['missing']),
                'reviews' => $this->reviews()->count(),
            ],
            'decision_info' => [
                'decision' => $this->decision,
                'decision_date' => $this->decision_date?->format('Y-m-d'),
                'enrollment_confirmed' => $this->enrollment_confirmed,
            ],
            'document_status' => [
                'progress' => $documentProgress['progress_percentage'] . '%',
                'verification' => $documentProgress['verification_percentage'] . '%',
                'required' => $documentProgress['required'],
                'uploaded' => $documentProgress['uploaded'],
                'verified' => $documentProgress['verified'],
                'missing' => $documentProgress['missing']
            ]
        ];
    }

    /**
     * Override setAttribute to handle JSON fields properly
     */
    public function setAttribute($key, $value)
    {
        // List of JSON fields
        $jsonFields = [
            'test_scores',
            'extracurricular_activities', 
            'awards_honors',
            'work_experience',
            'volunteer_experience',
            'references',
            'documents',
            'activity_log',
            'custom_requirements'
        ];
        
        // If this is a JSON field and value is already an array, let parent handle it
        if (in_array($key, $jsonFields) && is_array($value)) {
            return parent::setAttribute($key, $value);
        }
        
        // If this is a JSON field and value is a string, decode it first
        if (in_array($key, $jsonFields) && is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return parent::setAttribute($key, $decoded);
            }
        }
        
        return parent::setAttribute($key, $value);
    }

    /**
     * Override the getter to ensure we always return arrays for JSON fields
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        
        $jsonFields = [
            'test_scores',
            'extracurricular_activities',
            'awards_honors',
            'work_experience',
            'volunteer_experience',
            'references',
            'documents',
            'activity_log',
            'custom_requirements'
        ];
        
        if (in_array($key, $jsonFields)) {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                return $decoded ?: [];
            }
            return $value ?: [];
        }
        
        return $value;
    }
}