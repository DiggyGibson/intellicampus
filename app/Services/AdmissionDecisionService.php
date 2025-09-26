<?php

namespace App\Services;

use App\Models\AdmissionApplication;
use App\Models\ApplicationReview;
use App\Models\EnrollmentConfirmation;
use App\Models\ApplicationCommunication;
use App\Models\ApplicationStatusHistory;
use App\Models\ApplicationNote;
use App\Models\AdmissionWaitlist;
use App\Models\Student;
use App\Models\User;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;

class AdmissionDecisionService
{
    /**
     * Decision types
     */
    private const DECISION_TYPES = [
        'admit' => 'Admitted',
        'conditional_admit' => 'Conditionally Admitted',
        'waitlist' => 'Waitlisted',
        'deny' => 'Denied',
        'defer' => 'Deferred',
    ];

    /**
     * Admission letter templates
     */
    private const LETTER_TEMPLATES = [
        'admit' => 'admissions.letters.admission',
        'conditional_admit' => 'admissions.letters.conditional',
        'waitlist' => 'admissions.letters.waitlist',
        'deny' => 'admissions.letters.rejection',
        'defer' => 'admissions.letters.deferral',
    ];

    /**
     * Make admission decision for an application
     *
     * @param int $applicationId
     * @param string $decision
     * @param string $reason
     * @param array $additionalData
     * @return AdmissionApplication
     * @throws Exception
     */
    public function makeDecision(
        int $applicationId, 
        string $decision, 
        string $reason,
        array $additionalData = []
    ): AdmissionApplication {
        DB::beginTransaction();

        try {
            // Validate decision type
            if (!array_key_exists($decision, self::DECISION_TYPES)) {
                throw new Exception("Invalid decision type: {$decision}");
            }

            $application = AdmissionApplication::with(['program', 'term'])->findOrFail($applicationId);

            // Check if decision can be made
            if (!$this->canMakeDecision($application)) {
                throw new Exception("Decision cannot be made for application in status: {$application->status}");
            }

            // Check authorization
            if (!$this->isAuthorizedToMakeDecision(auth()->user(), $decision)) {
                throw new Exception("You are not authorized to make this decision");
            }

            // Update application with decision
            $previousStatus = $application->status;
            $application->decision = $decision;
            $application->decision_date = now();
            $application->decision_by = auth()->id();
            $application->decision_reason = $reason;
            
            // Handle decision-specific data
            switch ($decision) {
                case 'admit':
                    $application->status = 'admitted';
                    $this->processAdmission($application, $additionalData);
                    break;
                    
                case 'conditional_admit':
                    $application->status = 'conditional_admit';
                    $application->admission_conditions = $additionalData['conditions'] ?? null;
                    $this->processConditionalAdmission($application, $additionalData);
                    break;
                    
                case 'waitlist':
                    $application->status = 'waitlisted';
                    $this->addToWaitlist($application, $additionalData['rank'] ?? null);
                    break;
                    
                case 'deny':
                    $application->status = 'denied';
                    $this->processRejection($application);
                    break;
                    
                case 'defer':
                    $application->status = 'deferred';
                    $this->processDeferral($application, $additionalData['defer_to_term'] ?? null);
                    break;
            }

            // Update activity log
            $activityLog = $application->activity_log ?? [];
            $activityLog[] = [
                'timestamp' => now()->toIso8601String(),
                'action' => 'decision_made',
                'decision' => $decision,
                'reason' => $reason,
                'made_by' => auth()->user()->name,
            ];
            $application->activity_log = $activityLog;

            $application->save();

            // Log status change
            $this->logStatusChange($application, $previousStatus, $application->status, 
                "Decision: {$decision}. Reason: {$reason}");

            // Generate decision letter
            $letterPath = $this->generateDecisionLetter($application);
            
            // Send decision notification
            $this->sendDecisionNotification($application, $letterPath);

            // Update program statistics
            $this->updateProgramStatistics($application->program_id, $application->term_id);

            DB::commit();

            Log::info('Admission decision made', [
                'application_id' => $applicationId,
                'decision' => $decision,
                'made_by' => auth()->id(),
            ]);

            return $application->fresh(['enrollmentConfirmation', 'waitlist']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to make admission decision', [
                'application_id' => $applicationId,
                'decision' => $decision,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Make bulk decisions for multiple applications
     *
     * @param array $decisions
     * @return array
     */
    public function bulkDecisions(array $decisions): array
    {
        $results = [
            'successful' => [],
            'failed' => [],
        ];

        foreach ($decisions as $decisionData) {
            try {
                $application = $this->makeDecision(
                    $decisionData['application_id'],
                    $decisionData['decision'],
                    $decisionData['reason'] ?? 'Bulk decision',
                    $decisionData['additional_data'] ?? []
                );

                $results['successful'][] = [
                    'application_id' => $application->id,
                    'application_number' => $application->application_number,
                    'decision' => $application->decision,
                ];
            } catch (Exception $e) {
                $results['failed'][] = [
                    'application_id' => $decisionData['application_id'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        Log::info('Bulk decisions processed', [
            'successful' => count($results['successful']),
            'failed' => count($results['failed']),
        ]);

        return $results;
    }

    /**
     * Defer decision to another term
     *
     * @param int $applicationId
     * @param int $toTermId
     * @return AdmissionApplication
     * @throws Exception
     */
    public function deferDecision(int $applicationId, int $toTermId): AdmissionApplication
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);
            $newTerm = AcademicTerm::findOrFail($toTermId);

            // Check if deferral is allowed
            if (!in_array($application->status, ['submitted', 'under_review', 'committee_review', 'decision_pending'])) {
                throw new Exception("Cannot defer application in current status");
            }

            // Check if new term is valid
            if ($newTerm->application_close_date < now()) {
                throw new Exception("Cannot defer to a term with closed applications");
            }

            // Create deferred application
            $deferredApplication = $application->replicate();
            $deferredApplication->term_id = $toTermId;
            $deferredApplication->status = 'deferred';
            $deferredApplication->decision = 'defer';
            $deferredApplication->decision_date = now();
            $deferredApplication->decision_by = auth()->id();
            $deferredApplication->decision_reason = "Deferred from {$application->term->name} to {$newTerm->name}";
            $deferredApplication->application_number = $this->generateApplicationNumber();
            $deferredApplication->save();

            // Copy documents
            foreach ($application->documents as $document) {
                $newDocument = $document->replicate();
                $newDocument->application_id = $deferredApplication->id;
                $newDocument->save();
            }

            // Update original application
            $application->status = 'deferred';
            $application->decision = 'defer';
            $application->decision_date = now();
            $application->decision_by = auth()->id();
            $application->save();

            // Send deferral notification
            $this->sendDeferralNotification($application, $newTerm);

            DB::commit();

            Log::info('Application deferred', [
                'original_id' => $applicationId,
                'deferred_id' => $deferredApplication->id,
                'to_term' => $toTermId,
            ]);

            return $deferredApplication;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to defer application', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Move application to waitlist
     *
     * @param int $applicationId
     * @param int|null $rank
     * @return AdmissionWaitlist
     * @throws Exception
     */
    public function moveToWaitlist(int $applicationId, ?int $rank = null): AdmissionWaitlist
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::with(['program', 'term'])->findOrFail($applicationId);

            // Check if already on waitlist
            $existingWaitlist = AdmissionWaitlist::where('application_id', $applicationId)
                ->where('status', 'active')
                ->first();

            if ($existingWaitlist) {
                throw new Exception("Application is already on waitlist");
            }

            // Determine rank if not provided
            if ($rank === null) {
                $rank = $this->calculateWaitlistRank($application);
            }

            // Create waitlist entry
            $waitlist = new AdmissionWaitlist();
            $waitlist->application_id = $applicationId;
            $waitlist->term_id = $application->term_id;
            $waitlist->program_id = $application->program_id;
            $waitlist->rank = $rank;
            $waitlist->original_rank = $rank;
            $waitlist->status = 'active';
            $waitlist->save();

            // Update application
            $application->status = 'waitlisted';
            $application->decision = 'waitlist';
            $application->decision_date = now();
            $application->decision_by = auth()->id();
            $application->save();

            // Adjust other waitlist ranks if needed
            $this->adjustWaitlistRanks($application->program_id, $application->term_id, $waitlist->id, $rank);

            // Send waitlist notification
            $this->sendWaitlistNotification($application, $rank);

            DB::commit();

            Log::info('Application moved to waitlist', [
                'application_id' => $applicationId,
                'rank' => $rank,
            ]);

            return $waitlist;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to move to waitlist', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process conditional admission
     *
     * @param int $applicationId
     * @param array $conditions
     * @return AdmissionApplication
     * @throws Exception
     */
    public function conditionalAdmission(int $applicationId, array $conditions): AdmissionApplication
    {
        return $this->makeDecision(
            $applicationId,
            'conditional_admit',
            'Conditional admission based on specified requirements',
            ['conditions' => $conditions]
        );
    }

    /**
     * Generate admission letter
     *
     * @param int $applicationId
     * @return string
     * @throws Exception
     */
    public function generateAdmissionLetter(int $applicationId): string
    {
        $application = AdmissionApplication::with([
            'program',
            'term',
            'enrollmentConfirmation'
        ])->findOrFail($applicationId);

        if ($application->decision !== 'admit') {
            throw new Exception("Cannot generate admission letter for non-admitted application");
        }

        return $this->generateDecisionLetter($application);
    }

    /**
     * Generate rejection letter
     *
     * @param int $applicationId
     * @return string
     * @throws Exception
     */
    public function generateRejectionLetter(int $applicationId): string
    {
        $application = AdmissionApplication::with(['program', 'term'])->findOrFail($applicationId);

        if ($application->decision !== 'deny') {
            throw new Exception("Cannot generate rejection letter for non-denied application");
        }

        return $this->generateDecisionLetter($application);
    }

    /**
     * Review and process appeal
     *
     * @param int $applicationId
     * @param array $appealData
     * @return array
     * @throws Exception
     */
    public function reviewAppeal(int $applicationId, array $appealData): array
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);

            // Check if appeal is allowed
            if (!in_array($application->decision, ['deny', 'waitlist'])) {
                throw new Exception("Appeals are only allowed for denied or waitlisted applications");
            }

            // Check appeal deadline
            $appealDeadline = Carbon::parse($application->decision_date)->addDays(30);
            if (now() > $appealDeadline) {
                throw new Exception("Appeal deadline has passed");
            }

            // Create appeal record
            $appeal = [
                'application_id' => $applicationId,
                'appeal_date' => now(),
                'appeal_reason' => $appealData['reason'],
                'new_information' => $appealData['new_information'] ?? null,
                'supporting_documents' => $appealData['documents'] ?? [],
                'status' => 'under_review',
            ];

            // Store appeal in application data
            $application->appeal_data = $appeal;
            $application->status = 'appeal_review';
            $application->save();

            // Assign appeal reviewer
            $this->assignAppealReviewer($application);

            // Add note
            $this->addNote($application, "Appeal submitted: {$appealData['reason']}");

            // Send appeal acknowledgment
            $this->sendAppealAcknowledgment($application);

            DB::commit();

            Log::info('Appeal submitted', [
                'application_id' => $applicationId,
            ]);

            return [
                'status' => 'success',
                'message' => 'Appeal has been submitted and is under review',
                'appeal_id' => $applicationId,
                'review_timeline' => '7-10 business days',
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to process appeal', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate admission statistics
     *
     * @param int $termId
     * @param int|null $programId
     * @return array
     */
    public function calculateAdmissionStatistics(int $termId, ?int $programId = null): array
    {
        $query = AdmissionApplication::where('term_id', $termId);
        
        if ($programId) {
            $query->where('program_id', $programId);
        }

        $applications = $query->get();

        // Calculate basic statistics
        $stats = [
            'total_applications' => $applications->count(),
            'completed_applications' => $applications->whereNotNull('submitted_at')->count(),
            'decisions_made' => $applications->whereNotNull('decision')->count(),
            'pending_decisions' => $applications->whereNull('decision')
                ->whereNotNull('submitted_at')->count(),
        ];

        // Decision breakdown
        $decisions = $applications->whereNotNull('decision')->groupBy('decision');
        $stats['decisions'] = [];
        
        foreach ($decisions as $decision => $apps) {
            $stats['decisions'][$decision] = [
                'count' => $apps->count(),
                'percentage' => $stats['decisions_made'] > 0 
                    ? round(($apps->count() / $stats['decisions_made']) * 100, 2)
                    : 0,
            ];
        }

        // Calculate acceptance rate
        $admitted = $decisions['admit'] ?? collect();
        $conditionalAdmit = $decisions['conditional_admit'] ?? collect();
        $totalAdmitted = $admitted->count() + $conditionalAdmit->count();
        
        $stats['acceptance_rate'] = $stats['completed_applications'] > 0
            ? round(($totalAdmitted / $stats['completed_applications']) * 100, 2)
            : 0;

        // Calculate yield rate (enrolled / admitted)
        $enrolled = $admitted->merge($conditionalAdmit)
            ->where('enrollment_confirmed', true)
            ->count();
        
        $stats['yield_rate'] = $totalAdmitted > 0
            ? round(($enrolled / $totalAdmitted) * 100, 2)
            : 0;

        // Demographics
        $stats['demographics'] = [
            'gender' => $applications->groupBy('gender')->map->count(),
            'nationality' => $applications->groupBy('nationality')->map->count()->take(10),
            'application_type' => $applications->groupBy('application_type')->map->count(),
        ];

        // Academic metrics
        $gpas = $applications->whereNotNull('previous_gpa')->pluck('previous_gpa');
        $stats['academic_metrics'] = [
            'average_gpa' => $gpas->avg() ? round($gpas->avg(), 2) : null,
            'median_gpa' => $gpas->median() ? round($gpas->median(), 2) : null,
            'min_gpa' => $gpas->min(),
            'max_gpa' => $gpas->max(),
        ];

        // Timeline metrics
        $reviewTimes = [];
        foreach ($applications->whereNotNull('decision_date') as $app) {
            if ($app->submitted_at) {
                $reviewTimes[] = Carbon::parse($app->submitted_at)
                    ->diffInDays(Carbon::parse($app->decision_date));
            }
        }
        
        $stats['processing_time'] = [
            'average_days' => !empty($reviewTimes) ? round(array_sum($reviewTimes) / count($reviewTimes), 1) : null,
            'min_days' => !empty($reviewTimes) ? min($reviewTimes) : null,
            'max_days' => !empty($reviewTimes) ? max($reviewTimes) : null,
        ];

        // Waitlist statistics
        $waitlistCount = AdmissionWaitlist::where('term_id', $termId)
            ->where('status', 'active');
        
        if ($programId) {
            $waitlistCount->where('program_id', $programId);
        }
        
        $stats['waitlist'] = [
            'active' => $waitlistCount->count(),
            'offered' => $waitlistCount->clone()->where('status', 'offered')->count(),
            'accepted' => $waitlistCount->clone()->where('status', 'accepted')->count(),
        ];

        return $stats;
    }

    /**
     * Private helper methods
     */

    /**
     * Check if decision can be made for application
     */
    private function canMakeDecision(AdmissionApplication $application): bool
    {
        $allowedStatuses = [
            'under_review',
            'committee_review',
            'decision_pending',
            'appeal_review',
        ];

        return in_array($application->status, $allowedStatuses);
    }

    /**
     * Check if user is authorized to make decision
     */
    private function isAuthorizedToMakeDecision(User $user, string $decision): bool
    {
        $requiredRoles = [
            'admit' => ['admissions_director', 'dean', 'admissions_committee'],
            'conditional_admit' => ['admissions_director', 'dean', 'admissions_committee'],
            'waitlist' => ['admissions_director', 'admissions_officer', 'admissions_committee'],
            'deny' => ['admissions_director', 'admissions_officer', 'admissions_committee'],
            'defer' => ['admissions_director', 'admissions_officer'],
        ];

        $roles = $requiredRoles[$decision] ?? [];
        
        return $user->hasAnyRole($roles);
    }

    /**
     * Process admission
     */
    private function processAdmission(AdmissionApplication $application, array $data): void
    {
        // Create enrollment confirmation record
        $enrollment = EnrollmentConfirmation::firstOrCreate(
            ['application_id' => $application->id],
            [
                'decision' => 'pending',
                'enrollment_deadline' => Carbon::now()->addDays(30),
                'deposit_amount' => $data['deposit_amount'] ?? 500.00,
            ]
        );

        // Set enrollment dates
        $term = $application->term;
        if ($term) {
            $enrollment->orientation_date = $term->orientation_date;
            $enrollment->move_in_date = $term->move_in_date;
            $enrollment->classes_start_date = $term->start_date;
            $enrollment->save();
        }
    }

    /**
     * Process conditional admission
     */
    private function processConditionalAdmission(AdmissionApplication $application, array $data): void
    {
        // Similar to regular admission but with conditions
        $this->processAdmission($application, $data);

        // Store conditions
        $enrollment = $application->enrollmentConfirmation;
        if ($enrollment) {
            $enrollment->conditions = $data['conditions'] ?? [];
            $enrollment->save();
        }
    }

    /**
     * Process rejection
     */
    private function processRejection(AdmissionApplication $application): void
    {
        // Close any open items
        $application->checklistItems()->update(['is_completed' => false]);
        
        // Cancel any pending reviews
        $application->reviews()
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);
    }

    /**
     * Process deferral
     */
    private function processDeferral(AdmissionApplication $application, ?int $deferToTermId): void
    {
        if ($deferToTermId) {
            $application->deferred_to_term_id = $deferToTermId;
            $application->save();
        }
    }

    /**
     * Add to waitlist
     */
    private function addToWaitlist(AdmissionApplication $application, ?int $rank): void
    {
        // Create waitlist entry
        AdmissionWaitlist::create([
            'application_id' => $application->id,
            'term_id' => $application->term_id,
            'program_id' => $application->program_id,
            'rank' => $rank ?? $this->calculateWaitlistRank($application),
            'status' => 'active',
        ]);
    }

    /**
     * Calculate waitlist rank based on application strength
     */
    private function calculateWaitlistRank(AdmissionApplication $application): int
    {
        // Get current waitlist count
        $currentCount = AdmissionWaitlist::where('term_id', $application->term_id)
            ->where('program_id', $application->program_id)
            ->where('status', 'active')
            ->count();

        // Calculate score based on application strength
        $score = 0;
        
        // GPA contribution (max 40 points)
        if ($application->previous_gpa) {
            $score += min(($application->previous_gpa / 4.0) * 40, 40);
        }

        // Test scores contribution (max 30 points)
        $testScores = $application->test_scores ?? [];
        if (!empty($testScores)) {
            // Normalize test scores (simplified)
            $score += 20;
        }

        // Review ratings contribution (max 30 points)
        $avgRating = $application->reviews()
            ->where('status', 'completed')
            ->avg('overall_rating');
        if ($avgRating) {
            $score += ($avgRating / 5) * 30;
        }

        // Sort applicants by score and assign rank
        // For now, place at end of waitlist
        return $currentCount + 1;
    }

    /**
     * Adjust waitlist ranks after insertion
     */
    private function adjustWaitlistRanks(int $programId, int $termId, int $excludeId, int $insertedRank): void
    {
        AdmissionWaitlist::where('program_id', $programId)
            ->where('term_id', $termId)
            ->where('id', '!=', $excludeId)
            ->where('rank', '>=', $insertedRank)
            ->where('status', 'active')
            ->increment('rank');
    }

    /**
     * Generate application number
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
     * Generate decision letter PDF
     */
    private function generateDecisionLetter(AdmissionApplication $application): string
    {
        $template = self::LETTER_TEMPLATES[$application->decision] ?? 'admissions.letters.default';
        
        $data = [
            'application' => $application,
            'applicant_name' => $application->first_name . ' ' . $application->last_name,
            'program' => $application->program->name ?? 'N/A',
            'term' => $application->term->name ?? 'N/A',
            'decision' => self::DECISION_TYPES[$application->decision] ?? 'N/A',
            'decision_date' => $application->decision_date?->format('F d, Y'),
            'enrollment_deadline' => $application->enrollmentConfirmation?->enrollment_deadline?->format('F d, Y'),
            'conditions' => $application->admission_conditions,
            'waitlist_rank' => $application->waitlist?->rank,
        ];

        $pdf = PDF::loadView($template, $data);
        
        $filename = "decision_letter_{$application->application_number}_{$application->decision}.pdf";
        $path = "admissions/letters/{$application->id}/{$filename}";
        
        Storage::put($path, $pdf->output());

        return $path;
    }

    /**
     * Update program statistics cache
     */
    private function updateProgramStatistics(int $programId, int $termId): void
    {
        $stats = $this->calculateAdmissionStatistics($termId, $programId);
        
        // Cache the statistics
        $cacheKey = "admission_stats_{$termId}_{$programId}";
        cache()->put($cacheKey, $stats, now()->addHours(1));
    }

    /**
     * Log status change
     */
    private function logStatusChange(
        AdmissionApplication $application,
        string $fromStatus,
        string $toStatus,
        string $notes
    ): void {
        if (class_exists(ApplicationStatusHistory::class)) {
            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'changed_by' => auth()->id(),
                'notes' => $notes,
            ]);
        }
    }

    /**
     * Add note to application
     */
    private function addNote(AdmissionApplication $application, string $note): void
    {
        if (class_exists(ApplicationNote::class)) {
            ApplicationNote::create([
                'application_id' => $application->id,
                'note' => $note,
                'created_by' => auth()->id(),
                'type' => 'decision',
            ]);
        }
    }

    /**
     * Assign appeal reviewer
     */
    private function assignAppealReviewer(AdmissionApplication $application): void
    {
        // Find senior reviewer for appeals
        $reviewer = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admissions_director', 'dean']);
        })->first();

        if ($reviewer) {
            ApplicationReview::create([
                'application_id' => $application->id,
                'reviewer_id' => $reviewer->id,
                'review_stage' => 'appeal_review',
                'status' => 'pending',
                'assigned_at' => now(),
            ]);
        }
    }

    /**
     * Send notifications
     */
    private function sendDecisionNotification(AdmissionApplication $application, string $letterPath): void
    {
        try {
            ApplicationCommunication::create([
                'application_id' => $application->id,
                'communication_type' => 'email',
                'direction' => 'outbound',
                'subject' => 'Admission Decision - ' . $application->application_number,
                'message' => $this->getDecisionMessage($application),
                'recipient_email' => $application->email,
                'status' => 'sent',
                'sent_at' => now(),
                'attachment_path' => $letterPath,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send decision notification', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendWaitlistNotification(AdmissionApplication $application, int $rank): void
    {
        try {
            $message = "Your application has been placed on the waitlist. Current position: {$rank}. 
                       We will notify you if a space becomes available.";
            
            ApplicationCommunication::create([
                'application_id' => $application->id,
                'communication_type' => 'email',
                'direction' => 'outbound',
                'subject' => 'Waitlist Notification - ' . $application->application_number,
                'message' => $message,
                'recipient_email' => $application->email,
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send waitlist notification', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendDeferralNotification(AdmissionApplication $application, AcademicTerm $newTerm): void
    {
        try {
            $message = "Your application has been deferred to {$newTerm->name}. 
                       Your application will be automatically considered for the new term.";
            
            ApplicationCommunication::create([
                'application_id' => $application->id,
                'communication_type' => 'email',
                'direction' => 'outbound',
                'subject' => 'Application Deferred - ' . $application->application_number,
                'message' => $message,
                'recipient_email' => $application->email,
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send deferral notification', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendAppealAcknowledgment(AdmissionApplication $application): void
    {
        try {
            ApplicationCommunication::create([
                'application_id' => $application->id,
                'communication_type' => 'email',
                'direction' => 'outbound',
                'subject' => 'Appeal Received - ' . $application->application_number,
                'message' => 'Your appeal has been received and will be reviewed within 7-10 business days.',
                'recipient_email' => $application->email,
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send appeal acknowledgment', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get decision message based on decision type
     */
    private function getDecisionMessage(AdmissionApplication $application): string
    {
        $messages = [
            'admit' => "Congratulations! You have been admitted to {$application->program->name}. 
                       Please review the attached admission letter for next steps.",
            'conditional_admit' => "You have been conditionally admitted. Please review the conditions 
                                  in the attached letter that must be met to finalize your admission.",
            'waitlist' => "Your application has been placed on the waitlist. We will contact you 
                         if a position becomes available.",
            'deny' => "After careful review, we regret to inform you that we are unable to offer you 
                      admission at this time. Please see the attached letter for more information.",
            'defer' => "Your application has been deferred to a future term. You will be automatically 
                       considered when applications for that term are reviewed.",
        ];

        return $messages[$application->decision] ?? "A decision has been made on your application. 
                                                     Please review the attached letter.";
    }
}