<?php

namespace App\Services;

use App\Models\AdmissionApplication;
use App\Models\RecommendationLetter;
use App\Models\ApplicationDocument;
use App\Models\ApplicationCommunication;
use App\Models\ApplicationChecklistItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use App\Mail\RecommendationRequestMail;
use App\Mail\RecommendationReminderMail;
use App\Mail\RecommendationThankYouMail;
use Carbon\Carbon;
use Exception;

class RecommendationService
{
    /**
     * Recommendation statuses
     */
    private const STATUSES = [
        'pending' => 'Pending',
        'invited' => 'Invited',
        'in_progress' => 'In Progress',
        'submitted' => 'Submitted',
        'declined' => 'Declined',
        'waived' => 'Waived',
        'expired' => 'Expired',
    ];

    /**
     * Recommendation types
     */
    private const RECOMMENDATION_TYPES = [
        'academic' => 'Academic Reference',
        'professional' => 'Professional Reference',
        'personal' => 'Personal Reference',
        'research' => 'Research Supervisor',
        'employer' => 'Employer Reference',
    ];

    /**
     * Maximum reminders
     */
    private const MAX_REMINDERS = 3;
    private const REMINDER_INTERVAL_DAYS = 7;
    private const RECOMMENDATION_EXPIRY_DAYS = 30;

    /**
     * Required recommendations by application type
     */
    private const REQUIRED_RECOMMENDATIONS = [
        'freshman' => 2,
        'transfer' => 1,
        'graduate' => 3,
        'doctoral' => 3,
        'international' => 2,
    ];

    /**
     * Request recommendation from a recommender
     *
     * @param int $applicationId
     * @param array $recommenderData
     * @return RecommendationLetter
     * @throws Exception
     */
    public function requestRecommendation(int $applicationId, array $recommenderData): RecommendationLetter
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);

            // Validate application status
            if (!in_array($application->status, ['draft', 'documents_pending', 'submitted'])) {
                throw new Exception("Cannot request recommendations for application in status: {$application->status}");
            }

            // Check recommendation limit
            $existingCount = RecommendationLetter::where('application_id', $applicationId)
                ->whereNotIn('status', ['declined', 'expired'])
                ->count();

            $maxRequired = self::REQUIRED_RECOMMENDATIONS[$application->application_type] ?? 2;
            
            if ($existingCount >= $maxRequired + 1) { // Allow one extra
                throw new Exception("Maximum number of recommendations ({$maxRequired}) already requested");
            }

            // Check for duplicate recommender
            $duplicate = RecommendationLetter::where('application_id', $applicationId)
                ->where('recommender_email', $recommenderData['email'])
                ->whereNotIn('status', ['declined', 'expired'])
                ->first();

            if ($duplicate) {
                throw new Exception("A recommendation has already been requested from this email address");
            }

            // Create recommendation request
            $recommendation = new RecommendationLetter();
            $recommendation->application_id = $applicationId;
            $recommendation->recommender_name = $recommenderData['name'];
            $recommendation->recommender_email = $recommenderData['email'];
            $recommendation->recommender_title = $recommenderData['title'] ?? null;
            $recommendation->recommender_institution = $recommenderData['institution'] ?? null;
            $recommendation->recommender_phone = $recommenderData['phone'] ?? null;
            $recommendation->relationship_to_applicant = $recommenderData['relationship'] ?? null;
            $recommendation->recommendation_type = $recommenderData['type'] ?? 'academic';
            $recommendation->status = 'pending';
            $recommendation->token = $this->generateSecureToken();
            $recommendation->expires_at = Carbon::now()->addDays(self::RECOMMENDATION_EXPIRY_DAYS);
            $recommendation->requested_at = now();
            $recommendation->requested_by = auth()->id() ?? $application->user_id;
            
            // Add custom questions if provided
            if (isset($recommenderData['questions'])) {
                $recommendation->custom_questions = $recommenderData['questions'];
            }

            $recommendation->save();

            // Send invitation email
            $this->sendRecommenderInvite($recommendation->id);

            // Update application checklist
            $this->updateRecommendationChecklist($application);

            // Log the request
            $this->logRecommendationActivity($recommendation, 'requested', 'Recommendation requested');

            DB::commit();

            Log::info('Recommendation requested', [
                'application_id' => $applicationId,
                'recommendation_id' => $recommendation->id,
                'recommender_email' => $recommendation->recommender_email,
            ]);

            return $recommendation;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to request recommendation', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send invitation to recommender
     *
     * @param int $recommendationId
     * @return array
     * @throws Exception
     */
    public function sendRecommenderInvite(int $recommendationId): array
    {
        try {
            $recommendation = RecommendationLetter::with('application')->findOrFail($recommendationId);

            // Check if already invited
            if ($recommendation->status !== 'pending' && $recommendation->status !== 'invited') {
                throw new Exception("Invitation already sent or recommendation completed");
            }

            // Generate submission URL
            $submissionUrl = $this->generateSubmissionUrl($recommendation);

            // Prepare email data
            $emailData = [
                'recommender_name' => $recommendation->recommender_name,
                'applicant_name' => $recommendation->application->first_name . ' ' . $recommendation->application->last_name,
                'program' => $recommendation->application->program->name ?? 'N/A',
                'submission_url' => $submissionUrl,
                'deadline' => $recommendation->expires_at->format('F d, Y'),
                'application_number' => $recommendation->application->application_number,
            ];

            // Send email
            Mail::to($recommendation->recommender_email)
                ->send(new RecommendationRequestMail($emailData));

            // Update status
            $recommendation->status = 'invited';
            $recommendation->invited_at = now();
            $recommendation->invitation_sent_count = ($recommendation->invitation_sent_count ?? 0) + 1;
            $recommendation->save();

            // Log communication
            $this->logCommunication($recommendation, 'invitation_sent', $submissionUrl);

            Log::info('Recommendation invitation sent', [
                'recommendation_id' => $recommendationId,
                'email' => $recommendation->recommender_email,
            ]);

            return [
                'status' => 'success',
                'message' => 'Invitation sent successfully',
                'submission_url' => $submissionUrl,
                'expires_at' => $recommendation->expires_at->format('Y-m-d'),
            ];

        } catch (Exception $e) {
            Log::error('Failed to send recommendation invitation', [
                'recommendation_id' => $recommendationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Submit recommendation letter
     *
     * @param string $token
     * @param array $letterData
     * @return RecommendationLetter
     * @throws Exception
     */
    public function submitRecommendation(string $token, array $letterData): RecommendationLetter
    {
        DB::beginTransaction();

        try {
            // Find recommendation by token
            $recommendation = RecommendationLetter::where('token', $token)
                ->with('application')
                ->firstOrFail();

            // Validate token hasn't expired
            if ($recommendation->expires_at < now()) {
                throw new Exception("This recommendation link has expired");
            }

            // Validate status
            if (!in_array($recommendation->status, ['invited', 'in_progress'])) {
                throw new Exception("This recommendation has already been submitted or declined");
            }

            // Store letter content
            $recommendation->letter_content = $letterData['letter_content'];
            $recommendation->rating_academic = $letterData['rating_academic'] ?? null;
            $recommendation->rating_character = $letterData['rating_character'] ?? null;
            $recommendation->rating_leadership = $letterData['rating_leadership'] ?? null;
            $recommendation->rating_overall = $letterData['rating_overall'] ?? null;
            
            // Store answers to custom questions
            if (isset($letterData['question_answers'])) {
                $recommendation->question_answers = $letterData['question_answers'];
            }

            // Additional recommender info
            if (isset($letterData['recommender_position'])) {
                $recommendation->recommender_position = $letterData['recommender_position'];
            }
            if (isset($letterData['years_known'])) {
                $recommendation->years_known_applicant = $letterData['years_known'];
            }
            if (isset($letterData['capacity_known'])) {
                $recommendation->capacity_known = $letterData['capacity_known'];
            }

            // Generate PDF if letter provided as HTML
            if (isset($letterData['generate_pdf']) && $letterData['generate_pdf']) {
                $pdfPath = $this->generateRecommendationPDF($recommendation, $letterData);
                $recommendation->pdf_path = $pdfPath;
            }

            // Upload attached file if provided
            if (isset($letterData['attachment'])) {
                $attachmentPath = $this->storeAttachment($recommendation, $letterData['attachment']);
                $recommendation->attachment_path = $attachmentPath;
            }

            // Update status and timestamps
            $recommendation->status = 'submitted';
            $recommendation->submitted_at = now();
            $recommendation->ip_address = request()->ip();
            $recommendation->user_agent = request()->userAgent();
            
            // Sign the recommendation
            if (isset($letterData['signature'])) {
                $recommendation->signature = $letterData['signature'];
                $recommendation->signed_at = now();
            }

            $recommendation->save();

            // Create document record for the application
            $this->createDocumentRecord($recommendation);

            // Update application checklist
            $this->updateRecommendationChecklist($recommendation->application);

            // Send thank you email
            $this->sendThankYouEmail($recommendation);

            // Log the submission
            $this->logRecommendationActivity($recommendation, 'submitted', 'Recommendation submitted successfully');

            DB::commit();

            Log::info('Recommendation submitted', [
                'recommendation_id' => $recommendation->id,
                'application_id' => $recommendation->application_id,
            ]);

            return $recommendation;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit recommendation', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send reminder to recommender
     *
     * @param int $recommendationId
     * @return array
     * @throws Exception
     */
    public function remindRecommender(int $recommendationId): array
    {
        try {
            $recommendation = RecommendationLetter::with('application')->findOrFail($recommendationId);

            // Check if recommendation is still pending
            if (!in_array($recommendation->status, ['invited', 'in_progress'])) {
                throw new Exception("Cannot send reminder for recommendation with status: {$recommendation->status}");
            }

            // Check reminder limit
            if ($recommendation->reminder_count >= self::MAX_REMINDERS) {
                throw new Exception("Maximum number of reminders ({self::MAX_REMINDERS}) already sent");
            }

            // Check last reminder date
            if ($recommendation->last_reminder_at) {
                $daysSinceLastReminder = Carbon::parse($recommendation->last_reminder_at)->diffInDays(now());
                if ($daysSinceLastReminder < self::REMINDER_INTERVAL_DAYS) {
                    throw new Exception("Please wait {self::REMINDER_INTERVAL_DAYS} days between reminders");
                }
            }

            // Generate submission URL
            $submissionUrl = $this->generateSubmissionUrl($recommendation);

            // Prepare reminder data
            $reminderData = [
                'recommender_name' => $recommendation->recommender_name,
                'applicant_name' => $recommendation->application->first_name . ' ' . $recommendation->application->last_name,
                'submission_url' => $submissionUrl,
                'deadline' => $recommendation->expires_at->format('F d, Y'),
                'days_remaining' => now()->diffInDays($recommendation->expires_at),
            ];

            // Send reminder email
            Mail::to($recommendation->recommender_email)
                ->send(new RecommendationReminderMail($reminderData));

            // Update reminder tracking
            $recommendation->reminder_count = ($recommendation->reminder_count ?? 0) + 1;
            $recommendation->last_reminder_at = now();
            $recommendation->save();

            // Log communication
            $this->logCommunication($recommendation, 'reminder_sent', 'Reminder #' . $recommendation->reminder_count);

            Log::info('Recommendation reminder sent', [
                'recommendation_id' => $recommendationId,
                'reminder_count' => $recommendation->reminder_count,
            ]);

            return [
                'status' => 'success',
                'message' => 'Reminder sent successfully',
                'reminder_count' => $recommendation->reminder_count,
                'max_reminders' => self::MAX_REMINDERS,
            ];

        } catch (Exception $e) {
            Log::error('Failed to send recommendation reminder', [
                'recommendation_id' => $recommendationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Waive recommendation requirement
     *
     * @param int $applicationId
     * @param string $reason
     * @param int|null $recommendationId
     * @return array
     * @throws Exception
     */
    public function waiveRecommendation(int $applicationId, string $reason, ?int $recommendationId = null): array
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);

            // Check authorization
            if (!$this->canWaiveRecommendation(auth()->user())) {
                throw new Exception("You are not authorized to waive recommendations");
            }

            if ($recommendationId) {
                // Waive specific recommendation
                $recommendation = RecommendationLetter::where('application_id', $applicationId)
                    ->where('id', $recommendationId)
                    ->firstOrFail();

                $recommendation->status = 'waived';
                $recommendation->waived_at = now();
                $recommendation->waived_by = auth()->id();
                $recommendation->waiver_reason = $reason;
                $recommendation->save();

                $waivedCount = 1;
            } else {
                // Waive all pending recommendations
                $waivedCount = RecommendationLetter::where('application_id', $applicationId)
                    ->whereIn('status', ['pending', 'invited'])
                    ->update([
                        'status' => 'waived',
                        'waived_at' => now(),
                        'waived_by' => auth()->id(),
                        'waiver_reason' => $reason,
                    ]);
            }

            // Update application notes
            $this->addApplicationNote($application, "Recommendation requirement waived. Reason: {$reason}");

            // Update checklist
            $this->updateRecommendationChecklist($application, true);

            DB::commit();

            Log::info('Recommendation waived', [
                'application_id' => $applicationId,
                'waived_count' => $waivedCount,
                'reason' => $reason,
            ]);

            return [
                'status' => 'success',
                'message' => 'Recommendation requirement waived successfully',
                'waived_count' => $waivedCount,
                'waived_by' => auth()->user()->name,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to waive recommendation', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify recommender email/identity
     *
     * @param string $email
     * @return array
     */
    public function verifyRecommender(string $email): array
    {
        $verificationResult = [
            'email_valid' => false,
            'domain_valid' => false,
            'institution_verified' => false,
            'previous_recommendations' => 0,
            'blacklisted' => false,
            'warnings' => [],
        ];

        // Validate email format
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $verificationResult['email_valid'] = true;
        } else {
            $verificationResult['warnings'][] = 'Invalid email format';
            return $verificationResult;
        }

        // Extract and verify domain
        $domain = substr(strrchr($email, "@"), 1);
        
        // Check if educational/professional domain
        $educationalDomains = ['.edu', '.ac.uk', '.edu.au', '.ac.in'];
        foreach ($educationalDomains as $eduDomain) {
            if (str_ends_with($domain, $eduDomain)) {
                $verificationResult['domain_valid'] = true;
                $verificationResult['institution_verified'] = true;
                break;
            }
        }

        // Check DNS records
        if (!$verificationResult['domain_valid'] && checkdnsrr($domain, 'MX')) {
            $verificationResult['domain_valid'] = true;
        }

        // Check blacklist
        $blacklisted = $this->checkBlacklist($email);
        if ($blacklisted) {
            $verificationResult['blacklisted'] = true;
            $verificationResult['warnings'][] = 'This email address has been flagged';
        }

        // Check previous recommendations
        $previousCount = RecommendationLetter::where('recommender_email', $email)
            ->where('status', 'submitted')
            ->count();
        
        $verificationResult['previous_recommendations'] = $previousCount;

        // Add warnings based on checks
        if (!$verificationResult['domain_valid']) {
            $verificationResult['warnings'][] = 'Could not verify email domain';
        }

        if ($previousCount > 10) {
            $verificationResult['warnings'][] = 'This recommender has submitted many recommendations';
        }

        // Check for free email providers
        $freeProviders = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
        if (in_array($domain, $freeProviders)) {
            $verificationResult['warnings'][] = 'Using personal email address (institutional email preferred)';
        }

        Log::info('Recommender verification performed', [
            'email' => $email,
            'result' => $verificationResult,
        ]);

        return $verificationResult;
    }

    /**
     * Track recommendation status for application
     *
     * @param int $applicationId
     * @return array
     */
    public function trackRecommendationStatus(int $applicationId): array
    {
        $application = AdmissionApplication::with('recommendationLetters')->findOrFail($applicationId);
        
        $recommendations = $application->recommendationLetters;
        $requiredCount = self::REQUIRED_RECOMMENDATIONS[$application->application_type] ?? 2;

        $status = [
            'application_id' => $applicationId,
            'required_count' => $requiredCount,
            'total_requested' => $recommendations->count(),
            'submitted' => $recommendations->where('status', 'submitted')->count(),
            'pending' => $recommendations->whereIn('status', ['pending', 'invited', 'in_progress'])->count(),
            'declined' => $recommendations->where('status', 'declined')->count(),
            'waived' => $recommendations->where('status', 'waived')->count(),
            'expired' => $recommendations->where('status', 'expired')->count(),
            'completion_percentage' => 0,
            'is_complete' => false,
            'recommendations' => [],
        ];

        // Calculate completion percentage
        $effectiveSubmitted = $status['submitted'] + $status['waived'];
        $status['completion_percentage'] = $requiredCount > 0 
            ? min(100, round(($effectiveSubmitted / $requiredCount) * 100))
            : 0;
        
        $status['is_complete'] = $effectiveSubmitted >= $requiredCount;

        // Detail for each recommendation
        foreach ($recommendations as $rec) {
            $status['recommendations'][] = [
                'id' => $rec->id,
                'recommender_name' => $rec->recommender_name,
                'recommender_email' => $rec->recommender_email,
                'type' => $rec->recommendation_type,
                'status' => $rec->status,
                'requested_at' => $rec->requested_at?->format('Y-m-d'),
                'submitted_at' => $rec->submitted_at?->format('Y-m-d'),
                'expires_at' => $rec->expires_at?->format('Y-m-d'),
                'reminder_count' => $rec->reminder_count ?? 0,
                'days_pending' => $rec->status === 'invited' 
                    ? $rec->invited_at->diffInDays(now())
                    : null,
            ];
        }

        // Check for expiring recommendations
        $expiringCount = $recommendations
            ->whereIn('status', ['invited', 'in_progress'])
            ->where('expires_at', '<=', now()->addDays(7))
            ->count();

        if ($expiringCount > 0) {
            $status['warning'] = "{$expiringCount} recommendation(s) expiring soon";
        }

        return $status;
    }

    /**
     * Private helper methods
     */

    /**
     * Generate secure token for recommendation
     */
    private function generateSecureToken(): string
    {
        return Str::random(32) . '-' . time();
    }

    /**
     * Generate submission URL
     */
    private function generateSubmissionUrl(RecommendationLetter $recommendation): string
    {
        $baseUrl = config('app.url');
        return "{$baseUrl}/recommendation/submit/{$recommendation->token}";
    }

    /**
     * Check if user can waive recommendations
     */
    private function canWaiveRecommendation(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $allowedRoles = [
            'admissions_director',
            'admissions_officer',
            'academic_administrator',
            'dean',
        ];

        return $user->hasAnyRole($allowedRoles);
    }

    /**
     * Update recommendation checklist
     */
    private function updateRecommendationChecklist(AdmissionApplication $application, bool $waived = false): void
    {
        $checklistItem = ApplicationChecklistItem::firstOrCreate(
            [
                'application_id' => $application->id,
                'item_type' => 'recommendation',
                'item_name' => 'Letters of Recommendation',
            ],
            [
                'is_required' => !$waived,
                'sort_order' => 50,
            ]
        );

        if ($waived) {
            $checklistItem->is_completed = true;
            $checklistItem->completed_at = now();
            $checklistItem->notes = 'Requirement waived';
        } else {
            $requiredCount = self::REQUIRED_RECOMMENDATIONS[$application->application_type] ?? 2;
            $submittedCount = RecommendationLetter::where('application_id', $application->id)
                ->whereIn('status', ['submitted', 'waived'])
                ->count();

            $checklistItem->is_completed = ($submittedCount >= $requiredCount);
            $checklistItem->completed_at = $checklistItem->is_completed ? now() : null;
            $checklistItem->notes = "{$submittedCount} of {$requiredCount} received";
        }

        $checklistItem->save();
    }

    /**
     * Log recommendation activity
     */
    private function logRecommendationActivity(
        RecommendationLetter $recommendation,
        string $action,
        string $details
    ): void {
        $activityLog = $recommendation->activity_log ?? [];
        
        $activityLog[] = [
            'timestamp' => now()->toIso8601String(),
            'action' => $action,
            'details' => $details,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
        ];

        $recommendation->activity_log = $activityLog;
        $recommendation->save();
    }

    /**
     * Log communication
     */
    private function logCommunication(
        RecommendationLetter $recommendation,
        string $type,
        string $details
    ): void {
        ApplicationCommunication::create([
            'application_id' => $recommendation->application_id,
            'communication_type' => 'email',
            'direction' => 'outbound',
            'subject' => $this->getCommunicationSubject($type),
            'message' => $details,
            'recipient_email' => $recommendation->recommender_email,
            'status' => 'sent',
            'sent_at' => now(),
            'metadata' => [
                'recommendation_id' => $recommendation->id,
                'type' => $type,
            ],
        ]);
    }

    /**
     * Get communication subject based on type
     */
    private function getCommunicationSubject(string $type): string
    {
        $subjects = [
            'invitation_sent' => 'Letter of Recommendation Request',
            'reminder_sent' => 'Reminder: Letter of Recommendation Request',
            'thank_you' => 'Thank You for Your Recommendation',
            'expired' => 'Recommendation Request Expired',
        ];

        return $subjects[$type] ?? 'Recommendation Communication';
    }

    /**
     * Generate recommendation PDF
     */
    private function generateRecommendationPDF(
        RecommendationLetter $recommendation,
        array $letterData
    ): string {
        $data = [
            'recommendation' => $recommendation,
            'application' => $recommendation->application,
            'letter_content' => $letterData['letter_content'],
            'ratings' => [
                'academic' => $recommendation->rating_academic,
                'character' => $recommendation->rating_character,
                'leadership' => $recommendation->rating_leadership,
                'overall' => $recommendation->rating_overall,
            ],
            'submitted_date' => now()->format('F d, Y'),
        ];

        $pdf = \PDF::loadView('recommendations.letter-template', $data);
        
        $filename = "recommendation_{$recommendation->application_id}_{$recommendation->id}.pdf";
        $path = "applications/{$recommendation->application_id}/recommendations/{$filename}";
        
        Storage::put($path, $pdf->output());

        return $path;
    }

    /**
     * Store recommendation attachment
     */
    private function storeAttachment(
        RecommendationLetter $recommendation,
        $file
    ): string {
        $filename = "attachment_{$recommendation->id}_" . time() . '.' . $file->getClientOriginalExtension();
        $path = "applications/{$recommendation->application_id}/recommendations/attachments";
        
        return $file->storeAs($path, $filename);
    }

    /**
     * Create document record for application
     */
    private function createDocumentRecord(RecommendationLetter $recommendation): void
    {
        ApplicationDocument::create([
            'application_id' => $recommendation->application_id,
            'document_type' => 'recommendation_letter',
            'document_name' => 'Recommendation from ' . $recommendation->recommender_name,
            'original_filename' => $recommendation->pdf_path ? basename($recommendation->pdf_path) : 'recommendation.pdf',
            'file_path' => $recommendation->pdf_path ?? $recommendation->attachment_path,
            'file_type' => 'application/pdf',
            'file_size' => $recommendation->pdf_path ? Storage::size($recommendation->pdf_path) : 0,
            'status' => 'verified',
            'is_verified' => true,
            'verified_at' => now(),
            'recommender_name' => $recommendation->recommender_name,
            'recommender_email' => $recommendation->recommender_email,
            'recommender_title' => $recommendation->recommender_title,
            'recommender_institution' => $recommendation->recommender_institution,
        ]);
    }

    /**
     * Send thank you email to recommender
     */
    private function sendThankYouEmail(RecommendationLetter $recommendation): void
    {
        try {
            $data = [
                'recommender_name' => $recommendation->recommender_name,
                'applicant_name' => $recommendation->application->first_name . ' ' . $recommendation->application->last_name,
                'submitted_date' => now()->format('F d, Y'),
            ];

            Mail::to($recommendation->recommender_email)
                ->send(new RecommendationThankYouMail($data));

            $this->logCommunication($recommendation, 'thank_you', 'Thank you email sent');
        } catch (Exception $e) {
            Log::error('Failed to send thank you email', [
                'recommendation_id' => $recommendation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Add note to application
     */
    private function addApplicationNote(AdmissionApplication $application, string $note): void
    {
        if (class_exists(\App\Models\ApplicationNote::class)) {
            \App\Models\ApplicationNote::create([
                'application_id' => $application->id,
                'note' => $note,
                'created_by' => auth()->id(),
                'type' => 'recommendation',
            ]);
        }
    }

    /**
     * Check if email is blacklisted
     */
    private function checkBlacklist(string $email): bool
    {
        // Check against blacklist table or service
        // This is a placeholder implementation
        $blacklistedDomains = [
            'tempmail.com',
            'guerrillamail.com',
            '10minutemail.com',
        ];

        $domain = substr(strrchr($email, "@"), 1);
        
        return in_array($domain, $blacklistedDomains);
    }

    /**
     * Auto-expire old recommendations
     */
    public function processExpiredRecommendations(): int
    {
        $expired = RecommendationLetter::whereIn('status', ['invited', 'in_progress'])
            ->where('expires_at', '<', now())
            ->update([
                'status' => 'expired',
                'expired_at' => now(),
            ]);

        if ($expired > 0) {
            Log::info('Expired recommendations processed', [
                'count' => $expired,
            ]);
        }

        return $expired;
    }

    /**
     * Resend invitation for expired recommendation
     */
    public function resendExpiredInvitation(int $recommendationId): RecommendationLetter
    {
        DB::beginTransaction();

        try {
            $oldRecommendation = RecommendationLetter::findOrFail($recommendationId);

            if ($oldRecommendation->status !== 'expired') {
                throw new Exception("Can only resend invitations for expired recommendations");
            }

            // Create new recommendation with same details
            $newRecommendation = $oldRecommendation->replicate();
            $newRecommendation->status = 'pending';
            $newRecommendation->token = $this->generateSecureToken();
            $newRecommendation->expires_at = Carbon::now()->addDays(self::RECOMMENDATION_EXPIRY_DAYS);
            $newRecommendation->requested_at = now();
            $newRecommendation->invited_at = null;
            $newRecommendation->submitted_at = null;
            $newRecommendation->reminder_count = 0;
            $newRecommendation->last_reminder_at = null;
            $newRecommendation->parent_id = $oldRecommendation->id;
            $newRecommendation->save();

            // Send new invitation
            $this->sendRecommenderInvite($newRecommendation->id);

            DB::commit();

            Log::info('Expired recommendation resent', [
                'old_id' => $recommendationId,
                'new_id' => $newRecommendation->id,
            ]);

            return $newRecommendation;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to resend expired recommendation', [
                'recommendation_id' => $recommendationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get recommendation statistics for an application
     */
    public function getRecommendationStatistics(int $applicationId): array
    {
        $recommendations = RecommendationLetter::where('application_id', $applicationId)->get();

        $stats = [
            'total_requested' => $recommendations->count(),
            'submitted' => $recommendations->where('status', 'submitted')->count(),
            'pending' => $recommendations->whereIn('status', ['invited', 'in_progress'])->count(),
            'expired' => $recommendations->where('status', 'expired')->count(),
            'waived' => $recommendations->where('status', 'waived')->count(),
            'average_submission_time' => null,
            'average_rating' => null,
            'ratings_distribution' => [],
        ];

        // Calculate average submission time
        $submittedRecs = $recommendations->where('status', 'submitted');
        if ($submittedRecs->count() > 0) {
            $totalDays = 0;
            foreach ($submittedRecs as $rec) {
                if ($rec->invited_at && $rec->submitted_at) {
                    $totalDays += $rec->invited_at->diffInDays($rec->submitted_at);
                }
            }
            $stats['average_submission_time'] = round($totalDays / $submittedRecs->count(), 1);
        }

        // Calculate average ratings
        $ratingFields = ['rating_overall', 'rating_academic', 'rating_character', 'rating_leadership'];
        $ratingTotals = [];
        $ratingCounts = [];

        foreach ($submittedRecs as $rec) {
            foreach ($ratingFields as $field) {
                if ($rec->$field !== null) {
                    $ratingTotals[$field] = ($ratingTotals[$field] ?? 0) + $rec->$field;
                    $ratingCounts[$field] = ($ratingCounts[$field] ?? 0) + 1;
                }
            }
        }

        if (!empty($ratingCounts)) {
            $stats['average_rating'] = [];
            foreach ($ratingFields as $field) {
                if (isset($ratingCounts[$field]) && $ratingCounts[$field] > 0) {
                    $stats['average_rating'][$field] = round($ratingTotals[$field] / $ratingCounts[$field], 2);
                }
            }
        }

        return $stats;
    }
}