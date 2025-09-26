<?php

namespace App\Services;

use App\Models\AdmissionApplication;
use App\Models\ApplicationDocument;
use App\Models\ApplicationChecklistItem;
use App\Models\ApplicationStatusHistory;
use App\Models\ApplicationNote;
use App\Models\ApplicationCommunication;
use App\Models\ApplicationFee;
use App\Models\AcademicTerm;
use App\Models\AcademicProgram;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class ApplicationService
{
    /**
     * Document requirements by application type
     */
    private const REQUIRED_DOCUMENTS = [
        'freshman' => [
            'high_school_transcript' => 'High School Transcript',
            'high_school_diploma' => 'High School Diploma/Certificate',
            'test_scores' => 'SAT/ACT Scores',
            'personal_statement' => 'Personal Statement',
            'recommendation_letter' => 'Letter of Recommendation (2)',
            'passport' => 'Passport/National ID',
        ],
        'transfer' => [
            'university_transcript' => 'University Transcript',
            'high_school_transcript' => 'High School Transcript',
            'course_descriptions' => 'Course Descriptions/Syllabi',
            'personal_statement' => 'Personal Statement',
            'recommendation_letter' => 'Letter of Recommendation',
            'passport' => 'Passport/National ID',
        ],
        'graduate' => [
            'university_transcript' => 'Bachelor\'s Degree Transcript',
            'degree_certificate' => 'Degree Certificate',
            'test_scores' => 'GRE/GMAT Scores',
            'statement_of_purpose' => 'Statement of Purpose',
            'recommendation_letter' => 'Letters of Recommendation (3)',
            'resume' => 'Resume/CV',
            'research_proposal' => 'Research Proposal (PhD only)',
            'passport' => 'Passport/National ID',
        ],
        'international' => [
            'university_transcript' => 'Academic Transcripts (Translated)',
            'degree_certificate' => 'Degree Certificate (Translated)',
            'english_proficiency' => 'TOEFL/IELTS Scores',
            'financial_statement' => 'Financial Support Statement',
            'passport' => 'Passport',
            'visa_documents' => 'Visa Support Documents',
            'personal_statement' => 'Personal Statement',
            'recommendation_letter' => 'Letters of Recommendation (2)',
        ],
    ];

    /**
     * Application fee amounts by type
     */
    private const APPLICATION_FEES = [
        'freshman' => 50.00,
        'transfer' => 50.00,
        'graduate' => 75.00,
        'international' => 100.00,
        'readmission' => 25.00,
    ];

    /**
     * Start a new application
     *
     * @param array $applicantData
     * @return AdmissionApplication
     * @throws Exception
     */

    public function startNewApplication(array $applicantData): AdmissionApplication
    {
        DB::beginTransaction();
        
        try {
            // Modified validation - make personal details optional for initial creation
            $validator = Validator::make($applicantData, [
                'email' => 'required|email',  // Remove unique check here to allow checking for existing
                'application_type' => 'required|in:freshman,transfer,graduate,international,readmission',
                'term_id' => 'required|exists:academic_terms,id',
                'program_id' => 'required|exists:academic_programs,id',
                // Make these optional for initial creation:
                'first_name' => 'nullable|string|max:100',
                'last_name' => 'nullable|string|max:100',
                'phone_primary' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Check for duplicate applications
            $existingApplication = $this->checkForDuplicateApplication(
                $applicantData['email'],
                $applicantData['term_id'],
                $applicantData['program_id']
            );

            if ($existingApplication) {
                // Return the existing application instead of throwing error
                return $existingApplication;
            }

            // Verify term is open for applications
            $term = AcademicTerm::find($applicantData['term_id']);
            
            // Check using the actual fields in your database
            if (!$term->is_admission_open) {
                throw new Exception('Applications are not open for the selected term.');
            }
            
            if ($term->admission_deadline && $term->admission_deadline < now()) {
                throw new Exception('The admission deadline has passed.');
            }

            // Create the application - rest of your existing code continues...
            $application = new AdmissionApplication();
            $application->fill($applicantData);
            $application->application_number = $this->generateApplicationNumber();
            $application->application_uuid = Str::uuid();
            $application->status = 'draft';
            $application->started_at = now();
            $application->expires_at = now()->addDays(90);
            $application->ip_address = request()->ip();
            $application->user_agent = request()->userAgent();
            
            // Initialize activity log
            $application->activity_log = [[
                'timestamp' => now()->toIso8601String(),
                'action' => 'application_started',
                'details' => 'Application initiated',
                'ip_address' => request()->ip(),
            ]];

            $application->save();

            // Create checklist items
            $this->createChecklistItems($application);

            // Create initial application fee record
            $this->createApplicationFeeRecord($application);

            // Log the creation
            $this->logStatusChange($application, null, 'draft', 'Application started');

            // Send welcome email
            $this->sendApplicationStartedNotification($application);

            DB::commit();

            Log::info('New application started', [
                'application_id' => $application->id,
                'application_number' => $application->application_number,
                'email' => $application->email,
            ]);

            return $application;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to start application', [
                'error' => $e->getMessage(),
                'data' => $applicantData,
            ]);
            throw $e;
        }
    }

    /**
     * Save application as draft
     *
     * @param int $applicationId
     * @param array $data
     * @return AdmissionApplication
     * @throws Exception
     */
    public function saveAsDraft(int $applicationId, array $data): AdmissionApplication
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);

            // Check if application can be edited
            if (!in_array($application->status, ['draft', 'documents_pending'])) {
                throw new Exception('Application cannot be edited in current status.');
            }

            // Check if application has expired
            if ($application->expires_at < now()) {
                throw new Exception('Application has expired. Please start a new application.');
            }

            // Update application data
            $application->fill($data);
            $application->last_updated_at = now();

            // Update activity log
            $activityLog = $application->activity_log ?? [];
            $activityLog[] = [
                'timestamp' => now()->toIso8601String(),
                'action' => 'draft_saved',
                'details' => 'Application progress saved',
                'fields_updated' => array_keys($data),
            ];
            $application->activity_log = $activityLog;

            $application->save();

            // Update checklist based on completed fields
            $this->updateChecklistProgress($application);

            // Calculate and store completion percentage
            $completionPercentage = $this->calculateCompletionPercentage($applicationId);
            
            DB::commit();

            Log::info('Application draft saved', [
                'application_id' => $application->id,
                'completion' => $completionPercentage,
            ]);

            return $application;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to save draft', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate application for submission
     *
     * @param int $applicationId
     * @return array
     */
    public function validateApplication(int $applicationId): array
    {
        $application = AdmissionApplication::with(['documents', 'checklistItems', 'fees'])
            ->findOrFail($applicationId);

        $errors = [];
        $warnings = [];

        // Check required fields
        $requiredFields = $this->getRequiredFields($application->application_type);
        foreach ($requiredFields as $field => $label) {
            if (empty($application->$field)) {
                $errors[] = "$label is required.";
            }
        }

        // Check required documents
        $requiredDocuments = self::REQUIRED_DOCUMENTS[$application->application_type] ?? [];
        $uploadedDocuments = $application->documents->pluck('document_type')->toArray();
        
        foreach ($requiredDocuments as $docType => $docLabel) {
            if (!in_array($docType, $uploadedDocuments)) {
                $errors[] = "$docLabel is missing.";
            }
        }

        // Check document verification status
        $unverifiedDocs = $application->documents
            ->where('status', '!=', 'verified')
            ->count();
        
        if ($unverifiedDocs > 0) {
            $warnings[] = "$unverifiedDocs document(s) pending verification.";
        }

        // Check application fee
        $applicationFee = $application->fees
            ->where('fee_type', 'application_fee')
            ->first();
        
        if (!$applicationFee || $applicationFee->status !== 'paid') {
            $errors[] = "Application fee has not been paid.";
        }

        // Check test scores for certain application types
        if (in_array($application->application_type, ['freshman', 'graduate', 'international'])) {
            if (empty($application->test_scores)) {
                $warnings[] = "Test scores have not been provided.";
            }
        }

        // Check references/recommendations
        $references = $application->references ?? [];
        $requiredReferences = $this->getRequiredReferencesCount($application->application_type);
        
        if (count($references) < $requiredReferences) {
            $errors[] = "At least $requiredReferences reference(s) required.";
        }

        // Check essays
        if (empty($application->personal_statement) && 
            empty($application->statement_of_purpose)) {
            $errors[] = "Personal statement or statement of purpose is required.";
        }

        // International specific checks
        if ($application->application_type === 'international') {
            if (empty($application->passport_number)) {
                $errors[] = "Passport number is required for international applicants.";
            }
            
            // Check English proficiency scores
            $testScores = $application->test_scores ?? [];
            if (!isset($testScores['TOEFL']) && !isset($testScores['IELTS'])) {
                $errors[] = "English proficiency test scores (TOEFL/IELTS) required.";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'completion_percentage' => $this->calculateCompletionPercentage($applicationId),
        ];
    }

    /**
     * Submit application for review
     *
     * @param int $applicationId
     * @return AdmissionApplication
     * @throws Exception
     */
    public function submitApplication(int $applicationId): AdmissionApplication
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);

            // Validate application
            $validation = $this->validateApplication($applicationId);
            
            if (!$validation['valid']) {
                throw new ValidationException(null, $validation['errors']);
            }

            // Check current status
            if (!in_array($application->status, ['draft', 'documents_pending'])) {
                throw new Exception('Application has already been submitted.');
            }

            // Update application status
            $previousStatus = $application->status;
            $application->status = 'submitted';
            $application->submitted_at = now();
            $application->completed_at = now(); // Mark as complete

            // Update activity log
            $activityLog = $application->activity_log ?? [];
            $activityLog[] = [
                'timestamp' => now()->toIso8601String(),
                'action' => 'application_submitted',
                'details' => 'Application submitted for review',
                'validation_warnings' => $validation['warnings'],
            ];
            $application->activity_log = $activityLog;

            $application->save();

            // Update all checklist items to complete
            $application->checklistItems()->update(['is_completed' => true, 'completed_at' => now()]);

            // Log status change
            $this->logStatusChange($application, $previousStatus, 'submitted', 'Application submitted');

            // Send confirmation email
            $this->sendSubmissionConfirmation($application);

            // Trigger initial review assignment (async job would be better)
            $this->assignInitialReviewer($application);

            // Generate submission receipt
            $this->generateSubmissionReceipt($application);

            DB::commit();

            Log::info('Application submitted', [
                'application_id' => $application->id,
                'application_number' => $application->application_number,
            ]);

            return $application->fresh(['documents', 'checklistItems', 'fees']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit application', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Withdraw application
     *
     * @param int $applicationId
     * @param string $reason
     * @return AdmissionApplication
     * @throws Exception
     */
    public function withdrawApplication(int $applicationId, string $reason): AdmissionApplication
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);

            // Check if application can be withdrawn
            if (in_array($application->status, ['admitted', 'enrolled', 'withdrawn'])) {
                throw new Exception('Application cannot be withdrawn in current status.');
            }

            $previousStatus = $application->status;
            $application->status = 'withdrawn';
            $application->withdrawal_reason = $reason;
            $application->withdrawal_date = now();

            // Update activity log
            $activityLog = $application->activity_log ?? [];
            $activityLog[] = [
                'timestamp' => now()->toIso8601String(),
                'action' => 'application_withdrawn',
                'details' => 'Application withdrawn by applicant',
                'reason' => $reason,
            ];
            $application->activity_log = $activityLog;

            $application->save();

            // Log status change
            $this->logStatusChange($application, $previousStatus, 'withdrawn', $reason);

            // Process refund if applicable
            $this->processWithdrawalRefund($application);

            // Send withdrawal confirmation
            $this->sendWithdrawalConfirmation($application);

            DB::commit();

            Log::info('Application withdrawn', [
                'application_id' => $application->id,
                'reason' => $reason,
            ]);

            return $application;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to withdraw application', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function checkDuplicateApplication(string $email, int $termId, int $programId): ?AdmissionApplication
    {
        return $this->checkForDuplicateApplication($email, $termId, $programId);
    }

    /**
     * Calculate application completion percentage
     *
     * @param int $applicationId
     * @return int
     */
    public function calculateCompletionPercentage(int $applicationId): int
    {
        $application = AdmissionApplication::with(['documents', 'checklistItems'])
            ->findOrFail($applicationId);

        $totalWeight = 100;
        $completedWeight = 0;

        // Personal Information (20%)
        $personalFields = ['first_name', 'last_name', 'date_of_birth', 'email', 'phone_primary', 'current_address'];
        $personalComplete = 0;
        foreach ($personalFields as $field) {
            if (!empty($application->$field)) {
                $personalComplete++;
            }
        }
        $completedWeight += ($personalComplete / count($personalFields)) * 20;

        // Educational Background (20%)
        $educationFields = ['previous_institution', 'previous_gpa', 'previous_degree'];
        $educationComplete = 0;
        foreach ($educationFields as $field) {
            if (!empty($application->$field)) {
                $educationComplete++;
            }
        }
        $completedWeight += ($educationComplete / count($educationFields)) * 20;

        // Test Scores (15%)
        if (!empty($application->test_scores)) {
            $completedWeight += 15;
        }

        // Essays (15%)
        $essayComplete = 0;
        if (!empty($application->personal_statement)) $essayComplete++;
        if (!empty($application->statement_of_purpose)) $essayComplete++;
        $completedWeight += ($essayComplete / 2) * 15;

        // Documents (20%)
        $requiredDocuments = count(self::REQUIRED_DOCUMENTS[$application->application_type] ?? []);
        $uploadedDocuments = $application->documents->count();
        if ($requiredDocuments > 0) {
            $documentCompletion = min($uploadedDocuments / $requiredDocuments, 1);
            $completedWeight += $documentCompletion * 20;
        }

        // References (5%)
        $references = $application->references ?? [];
        $requiredReferences = $this->getRequiredReferencesCount($application->application_type);
        if ($requiredReferences > 0) {
            $referenceCompletion = min(count($references) / $requiredReferences, 1);
            $completedWeight += $referenceCompletion * 5;
        }

        // Application Fee (5%)
        if ($application->application_fee_paid) {
            $completedWeight += 5;
        }

        return min(round($completedWeight), 100);
    }

    /**
     * Check eligibility for the program
     *
     * @param int $applicationId
     * @return array
     */
    public function checkEligibility(int $applicationId): array
    {
        $application = AdmissionApplication::with(['program', 'term'])->findOrFail($applicationId);
        $program = $application->program;
        
        $eligible = true;
        $reasons = [];
        $recommendations = [];

        // Check GPA requirement
        if ($program->minimum_gpa && $application->previous_gpa) {
            if ($application->previous_gpa < $program->minimum_gpa) {
                $eligible = false;
                $reasons[] = "GPA {$application->previous_gpa} is below minimum requirement of {$program->minimum_gpa}";
                $recommendations[] = "Consider applying to programs with lower GPA requirements";
            }
        }

        // Check test score requirements
        $testRequirements = $program->test_requirements ?? [];
        $testScores = $application->test_scores ?? [];

        foreach ($testRequirements as $test => $minScore) {
            if (!isset($testScores[$test])) {
                $eligible = false;
                $reasons[] = "$test scores are required but not provided";
            } elseif ($testScores[$test]['total'] < $minScore) {
                $eligible = false;
                $reasons[] = "$test score {$testScores[$test]['total']} is below minimum of $minScore";
            }
        }

        // Check prerequisite courses
        $prerequisites = $program->prerequisites ?? [];
        // This would need to check against transcripts - simplified for now
        if (!empty($prerequisites)) {
            $recommendations[] = "Ensure all prerequisite courses are completed";
        }

        // Check application deadline
        if ($application->term->application_deadline < now()) {
            $eligible = false;
            $reasons[] = "Application deadline has passed";
        }

        // Check capacity
        $currentEnrollment = AdmissionApplication::where('term_id', $application->term_id)
            ->where('program_id', $application->program_id)
            ->where('decision', 'admit')
            ->count();

        if ($program->capacity && $currentEnrollment >= $program->capacity) {
            $recommendations[] = "Program is at capacity - you may be placed on waitlist";
        }

        return [
            'eligible' => $eligible,
            'reasons' => $reasons,
            'recommendations' => $recommendations,
            'program_requirements' => [
                'minimum_gpa' => $program->minimum_gpa,
                'test_requirements' => $testRequirements,
                'prerequisites' => $prerequisites,
                'capacity' => $program->capacity,
                'current_enrollment' => $currentEnrollment,
            ],
        ];
    }

    /**
     * Generate application PDF
     *
     * @param int $applicationId
     * @return string Path to generated PDF
     */
    public function generateApplicationPDF(int $applicationId): string
    {
        $application = AdmissionApplication::with([
            'program',
            'term',
            'documents',
            'checklistItems',
        ])->findOrFail($applicationId);

        $data = [
            'application' => $application,
            'generated_at' => now(),
            'completion_percentage' => $this->calculateCompletionPercentage($applicationId),
            'test_scores' => $application->getFormattedTestScores(),
        ];

        $pdf = PDF::loadView('pdf.application', $data);
        
        $filename = "application_{$application->application_number}_{$application->id}.pdf";
        $path = "applications/pdf/{$filename}";
        
        Storage::put($path, $pdf->output());

        // Log the generation
        Log::info('Application PDF generated', [
            'application_id' => $applicationId,
            'path' => $path,
        ]);

        return $path;
    }

    /**
     * Check for duplicate applications
     *
     * @param string $email
     * @param string|null $nationalId
     * @return AdmissionApplication|null
     */
    public function duplicateCheck(string $email, ?string $nationalId = null): ?AdmissionApplication
    {
        $query = AdmissionApplication::where('email', $email)
            ->whereNotIn('status', ['withdrawn', 'denied', 'expired']);

        if ($nationalId) {
            $query->orWhere('national_id', $nationalId);
        }

        return $query->first();
    }

    /**
     * Merge duplicate applications
     *
     * @param int $primaryId
     * @param array $duplicateIds
     * @return AdmissionApplication
     * @throws Exception
     */
    public function mergeApplications(int $primaryId, array $duplicateIds): AdmissionApplication
    {
        DB::beginTransaction();

        try {
            $primary = AdmissionApplication::findOrFail($primaryId);
            $duplicates = AdmissionApplication::whereIn('id', $duplicateIds)->get();

            foreach ($duplicates as $duplicate) {
                // Merge documents
                $duplicate->documents()->update(['application_id' => $primaryId]);
                
                // Merge communications
                $duplicate->communications()->update(['application_id' => $primaryId]);
                
                // Merge notes
                if (Schema::hasTable('application_notes')) {
                    ApplicationNote::where('application_id', $duplicate->id)
                        ->update(['application_id' => $primaryId]);
                }

                // Merge test scores
                if (!empty($duplicate->test_scores) && empty($primary->test_scores)) {
                    $primary->test_scores = $duplicate->test_scores;
                }

                // Mark duplicate as merged
                $duplicate->status = 'withdrawn';
                $duplicate->withdrawal_reason = "Merged with application {$primary->application_number}";
                $duplicate->save();
            }

            // Update primary application
            $primary->activity_log = array_merge($primary->activity_log ?? [], [[
                'timestamp' => now()->toIso8601String(),
                'action' => 'applications_merged',
                'details' => 'Merged duplicate applications',
                'merged_ids' => $duplicateIds,
            ]]);
            
            $primary->save();

            DB::commit();

            Log::info('Applications merged', [
                'primary_id' => $primaryId,
                'merged_ids' => $duplicateIds,
            ]);

            return $primary;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to merge applications', [
                'primary_id' => $primaryId,
                'duplicate_ids' => $duplicateIds,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Private helper methods
     */

    /**
     * Check if there's a duplicate application for the same term and program
     */
    private function checkForDuplicateApplication(string $email, int $termId, int $programId): ?AdmissionApplication
    {
        return AdmissionApplication::where('email', $email)
            ->where('term_id', $termId)
            ->where('program_id', $programId)
            ->whereNotIn('status', ['withdrawn', 'denied', 'expired'])
            ->first();
    }

    /**
     * Check if term is open for applications
     */
    private function isTermOpenForApplications(AcademicTerm $term): bool
    {
        $now = now();
        
        // Check if we have application open/close dates
        if ($term->application_open_date && $term->application_close_date) {
            return $now->between($term->application_open_date, $term->application_close_date);
        }
        
        // Fallback to checking if term hasn't started yet
        return $term->start_date > $now;
    }

    /**
     * Generate unique application number
     */
    private function generateApplicationNumber(): string
    {
        $year = date('Y');
        $lastApplication = AdmissionApplication::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastApplication && preg_match('/APP-\d{4}-(\d{6})/', $lastApplication->application_number, $matches)) {
            $lastNumber = intval($matches[1]);
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }
        
        return "APP-{$year}-{$newNumber}";
    }

    /**
     * Create checklist items for the application
     */
    private function createChecklistItems(AdmissionApplication $application): void
    {
        $checklistItems = [];
        
        // Add document checklist items
        $requiredDocuments = self::REQUIRED_DOCUMENTS[$application->application_type] ?? [];
        $order = 1;
        
        foreach ($requiredDocuments as $docType => $docLabel) {
            $checklistItems[] = [
                'application_id' => $application->id,
                'item_name' => $docLabel,
                'item_type' => 'document',
                'is_required' => true,
                'is_completed' => false,
                'sort_order' => $order++,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Add form sections
        $formSections = [
            'personal_info' => 'Personal Information',
            'education' => 'Educational Background',
            'test_scores' => 'Test Scores',
            'essays' => 'Essays/Statements',
            'activities' => 'Activities & Experience',
            'references' => 'References',
            'fee' => 'Application Fee',
        ];

        foreach ($formSections as $section => $label) {
            $checklistItems[] = [
                'application_id' => $application->id,
                'item_name' => $label,
                'item_type' => 'form',
                'is_required' => true,
                'is_completed' => false,
                'sort_order' => $order++,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        ApplicationChecklistItem::insert($checklistItems);
    }

    /**
     * Create application fee record
     */
    private function createApplicationFeeRecord(AdmissionApplication $application): void
    {
        ApplicationFee::create([
            'application_id' => $application->id,
            'fee_type' => 'application_fee',
            'amount' => self::APPLICATION_FEES[$application->application_type] ?? 50.00,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => $application->expires_at,
        ]);
    }

    /**
     * Get required fields based on application type
     */
    private function getRequiredFields(string $applicationType): array
    {
        $commonFields = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'date_of_birth' => 'Date of Birth',
            'email' => 'Email',
            'phone_primary' => 'Primary Phone',
            'current_address' => 'Current Address',
            'city' => 'City',
            'country' => 'Country',
            'previous_institution' => 'Previous Institution',
            'previous_gpa' => 'Previous GPA',
        ];

        $typeSpecificFields = [
            'freshman' => [
                'high_school_name' => 'High School Name',
                'high_school_graduation_date' => 'High School Graduation Date',
            ],
            'transfer' => [
                'previous_degree' => 'Previous Degree',
                'previous_major' => 'Previous Major',
            ],
            'graduate' => [
                'previous_degree' => 'Bachelor\'s Degree',
                'previous_major' => 'Undergraduate Major',
                'research_interests' => 'Research Interests',
            ],
            'international' => [
                'passport_number' => 'Passport Number',
                'nationality' => 'Nationality',
                'country_of_birth' => 'Country of Birth',
            ],
        ];

        return array_merge($commonFields, $typeSpecificFields[$applicationType] ?? []);
    }

    /**
     * Get required number of references
     */
    private function getRequiredReferencesCount(string $applicationType): int
    {
        return match($applicationType) {
            'freshman' => 2,
            'transfer' => 1,
            'graduate' => 3,
            'international' => 2,
            default => 1,
        };
    }

    /**
     * Update checklist progress based on application data
     */
    private function updateChecklistProgress(AdmissionApplication $application): void
    {
        $checklistItems = $application->checklistItems;

        foreach ($checklistItems as $item) {
            $isCompleted = false;

            switch ($item->item_type) {
                case 'form':
                    // Check if form section is complete
                    if (str_contains($item->item_name, 'Personal Information')) {
                        $isCompleted = !empty($application->first_name) && 
                                     !empty($application->last_name) && 
                                     !empty($application->email);
                    } elseif (str_contains($item->item_name, 'Educational Background')) {
                        $isCompleted = !empty($application->previous_institution) && 
                                     !empty($application->previous_gpa);
                    } elseif (str_contains($item->item_name, 'Test Scores')) {
                        $isCompleted = !empty($application->test_scores);
                    } elseif (str_contains($item->item_name, 'Essays')) {
                        $isCompleted = !empty($application->personal_statement) || 
                                     !empty($application->statement_of_purpose);
                    } elseif (str_contains($item->item_name, 'Application Fee')) {
                        $isCompleted = $application->application_fee_paid;
                    }
                    break;

                case 'document':
                    // Check if document is uploaded
                    $documentType = $this->getDocumentTypeFromLabel($item->item_name);
                    $isCompleted = $application->documents()
                        ->where('document_type', $documentType)
                        ->exists();
                    break;
            }

            if ($isCompleted && !$item->is_completed) {
                $item->update([
                    'is_completed' => true,
                    'completed_at' => now(),
                ]);
            }
        }
    }

    /**
     * Get document type from label
     */
    private function getDocumentTypeFromLabel(string $label): string
    {
        $mapping = [
            'High School Transcript' => 'high_school_transcript',
            'University Transcript' => 'university_transcript',
            'Personal Statement' => 'personal_statement',
            'Statement of Purpose' => 'statement_of_purpose',
            'Letter of Recommendation' => 'recommendation_letter',
            'Resume/CV' => 'resume',
            'Passport/National ID' => 'passport',
            // Add more mappings as needed
        ];

        foreach ($mapping as $key => $value) {
            if (str_contains($label, $key)) {
                return $value;
            }
        }

        return 'other';
    }

    /**
     * Log status change
     */
    private function logStatusChange(
        AdmissionApplication $application,
        ?string $fromStatus,
        string $toStatus,
        string $notes = ''
    ): void {
        if (Schema::hasTable('application_status_histories')) {
            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'changed_by' => auth()->id(),
                'notes' => $notes,
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Send application started notification
     */
    private function sendApplicationStartedNotification(AdmissionApplication $application): void
    {
        try {
            // This would integrate with your notification service
            // For now, just log it
            ApplicationCommunication::create([
                'application_id' => $application->id,
                'communication_type' => 'email',
                'direction' => 'outbound',
                'subject' => 'Application Started - ' . $application->application_number,
                'message' => 'Thank you for starting your application. You have 90 days to complete it.',
                'recipient_email' => $application->email,
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send application started notification', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send submission confirmation
     */
    private function sendSubmissionConfirmation(AdmissionApplication $application): void
    {
        try {
            ApplicationCommunication::create([
                'application_id' => $application->id,
                'communication_type' => 'email',
                'direction' => 'outbound',
                'subject' => 'Application Submitted Successfully - ' . $application->application_number,
                'message' => 'Your application has been successfully submitted and is now under review.',
                'recipient_email' => $application->email,
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send submission confirmation', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send withdrawal confirmation
     */
    private function sendWithdrawalConfirmation(AdmissionApplication $application): void
    {
        try {
            ApplicationCommunication::create([
                'application_id' => $application->id,
                'communication_type' => 'email',
                'direction' => 'outbound',
                'subject' => 'Application Withdrawn - ' . $application->application_number,
                'message' => 'Your application has been withdrawn as requested.',
                'recipient_email' => $application->email,
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send withdrawal confirmation', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Assign initial reviewer
     */
    private function assignInitialReviewer(AdmissionApplication $application): void
    {
        // This would be implemented based on your reviewer assignment logic
        // For now, just log it
        Log::info('Initial reviewer assignment needed', [
            'application_id' => $application->id,
        ]);
    }

    /**
     * Generate submission receipt
     */
    private function generateSubmissionReceipt(AdmissionApplication $application): void
    {
        // Generate a PDF receipt of submission
        $receiptPath = $this->generateApplicationPDF($application->id);
        
        // Store receipt reference
        $application->submission_receipt_path = $receiptPath;
        $application->save();
    }

    /**
     * Process withdrawal refund if applicable
     */
    private function processWithdrawalRefund(AdmissionApplication $application): void
    {
        $applicationFee = $application->fees()
            ->where('fee_type', 'application_fee')
            ->where('status', 'paid')
            ->first();

        if ($applicationFee) {
            // Check refund policy
            $daysSincePayment = $applicationFee->paid_date 
                ? now()->diffInDays($applicationFee->paid_date) 
                : null;

            // Example: Full refund if withdrawn within 7 days
            if ($daysSincePayment && $daysSincePayment <= 7) {
                $applicationFee->status = 'refunded';
                $applicationFee->refunded_date = now();
                $applicationFee->save();

                Log::info('Application fee refunded', [
                    'application_id' => $application->id,
                    'amount' => $applicationFee->amount,
                ]);
            }
        }
    }
}