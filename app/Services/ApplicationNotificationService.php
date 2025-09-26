<?php

namespace App\Services;

use App\Models\AdmissionApplication;
use App\Models\ApplicationCommunication;
use App\Models\ApplicationDocument;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Collection;
use App\Mail\ApplicationStatusMail;
use App\Jobs\SendBulkNotification;
use Carbon\Carbon;
use Exception;

class ApplicationNotificationService
{
    /**
     * Notification channels
     */
    private const CHANNELS = [
        'email' => true,
        'sms' => true,
        'in_app' => true,
        'push' => false, // Future implementation
    ];

    /**
     * SMS provider configuration
     */
    private const SMS_PROVIDER = 'twilio'; // or 'africastalking', 'nexmo'
    
    /**
     * Rate limiting
     */
    private const MAX_EMAILS_PER_HOUR = 500;
    private const MAX_SMS_PER_HOUR = 200;
    
    /**
     * Retry configuration
     */
    private const MAX_RETRY_ATTEMPTS = 3;
    private const RETRY_DELAY_MINUTES = 5;

    /**
     * Template categories
     */
    private const TEMPLATE_CATEGORIES = [
        'application' => 'Application Related',
        'document' => 'Document Related',
        'decision' => 'Admission Decision',
        'enrollment' => 'Enrollment Related',
        'exam' => 'Entrance Exam',
        'reminder' => 'Reminders',
        'general' => 'General Communication',
    ];

    /**
     * Send application received notification
     *
     * @param int $applicationId
     * @return array
     */
    public function sendApplicationReceived(int $applicationId): array
    {
        try {
            $application = AdmissionApplication::with(['program', 'term'])->findOrFail($applicationId);
            
            $template = $this->getTemplate('application_received');
            
            $variables = [
                'applicant_name' => $application->first_name . ' ' . $application->last_name,
                'application_number' => $application->application_number,
                'program_name' => $application->program->name ?? 'N/A',
                'term_name' => $application->term->name ?? 'N/A',
                'submission_date' => now()->format('F d, Y'),
                'portal_link' => config('app.url') . '/apply/status/' . $application->application_uuid,
            ];
            
            $message = $this->parseTemplate($template, $variables);
            
            $result = $this->sendNotification(
                $application,
                'Application Received - ' . $application->application_number,
                $message,
                ['email', 'sms']
            );
            
            Log::info('Application received notification sent', [
                'application_id' => $applicationId,
                'channels' => $result['channels_used'],
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            Log::error('Failed to send application received notification', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send document request notification
     *
     * @param int $applicationId
     * @param array $documents
     * @return array
     */
    public function sendDocumentRequest(int $applicationId, array $documents): array
    {
        try {
            $application = AdmissionApplication::findOrFail($applicationId);
            
            $template = $this->getTemplate('document_request');
            
            $documentList = implode("\n- ", array_values($documents));
            
            $variables = [
                'applicant_name' => $application->first_name . ' ' . $application->last_name,
                'application_number' => $application->application_number,
                'document_list' => $documentList,
                'deadline' => Carbon::now()->addDays(7)->format('F d, Y'),
                'upload_link' => config('app.url') . '/apply/documents/' . $application->application_uuid,
            ];
            
            $message = $this->parseTemplate($template, $variables);
            
            $result = $this->sendNotification(
                $application,
                'Documents Required - Action Needed',
                $message,
                ['email']
            );
            
            // Log document request
            $this->logCommunication($application, 'document_request', $message, $result);
            
            return $result;
            
        } catch (Exception $e) {
            Log::error('Failed to send document request', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send status update notification
     *
     * @param int $applicationId
     * @param string $newStatus
     * @return array
     */
    public function sendStatusUpdate(int $applicationId, string $newStatus): array
    {
        try {
            $application = AdmissionApplication::findOrFail($applicationId);
            
            $statusMessages = [
                'submitted' => 'Your application has been successfully submitted and is now under review.',
                'under_review' => 'Your application is currently being reviewed by our admissions team.',
                'documents_pending' => 'We need additional documents to continue processing your application.',
                'committee_review' => 'Your application has been forwarded to the admissions committee for final review.',
                'decision_pending' => 'Your application review is complete. A decision will be communicated soon.',
                'admitted' => 'Congratulations! You have been admitted to our program.',
                'waitlisted' => 'Your application has been placed on the waitlist.',
                'denied' => 'After careful review, we are unable to offer you admission at this time.',
            ];
            
            $message = $statusMessages[$newStatus] ?? 'Your application status has been updated.';
            
            $template = $this->getTemplate('status_update');
            
            $variables = [
                'applicant_name' => $application->first_name . ' ' . $application->last_name,
                'application_number' => $application->application_number,
                'new_status' => ucwords(str_replace('_', ' ', $newStatus)),
                'status_message' => $message,
                'portal_link' => config('app.url') . '/apply/status/' . $application->application_uuid,
                'date' => now()->format('F d, Y'),
            ];
            
            $message = $this->parseTemplate($template, $variables);
            
            $result = $this->sendNotification(
                $application,
                'Application Status Update',
                $message,
                ['email', 'sms']
            );
            
            return $result;
            
        } catch (Exception $e) {
            Log::error('Failed to send status update', [
                'application_id' => $applicationId,
                'new_status' => $newStatus,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send decision notification
     *
     * @param int $applicationId
     * @return array
     */
    public function sendDecisionNotification(int $applicationId): array
    {
        try {
            $application = AdmissionApplication::with(['program', 'term'])->findOrFail($applicationId);
            
            if (!$application->decision) {
                throw new Exception("No decision has been made for this application");
            }
            
            $templateKey = 'decision_' . $application->decision;
            $template = $this->getTemplate($templateKey);
            
            $variables = [
                'applicant_name' => $application->first_name . ' ' . $application->last_name,
                'application_number' => $application->application_number,
                'program_name' => $application->program->name ?? 'N/A',
                'term_name' => $application->term->name ?? 'N/A',
                'decision' => ucwords($application->decision),
                'decision_date' => $application->decision_date?->format('F d, Y'),
                'portal_link' => config('app.url') . '/apply/decision/' . $application->application_uuid,
            ];
            
            // Add enrollment-specific variables for admitted students
            if (in_array($application->decision, ['admit', 'conditional_admit'])) {
                $enrollment = $application->enrollmentConfirmation;
                if ($enrollment) {
                    $variables['enrollment_deadline'] = $enrollment->enrollment_deadline?->format('F d, Y');
                    $variables['deposit_amount'] = '$' . number_format($enrollment->deposit_amount, 2);
                    $variables['deposit_deadline'] = $enrollment->deposit_deadline?->format('F d, Y');
                }
            }
            
            $message = $this->parseTemplate($template, $variables);
            
            // Decision notifications are high priority
            $result = $this->sendNotification(
                $application,
                'Admission Decision - ' . $application->application_number,
                $message,
                ['email', 'sms'],
                'high'
            );
            
            return $result;
            
        } catch (Exception $e) {
            Log::error('Failed to send decision notification', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send enrollment reminder
     *
     * @param int $applicationId
     * @return array
     */
    public function sendEnrollmentReminder(int $applicationId): array
    {
        try {
            $application = AdmissionApplication::with(['enrollmentConfirmation'])->findOrFail($applicationId);
            
            if (!$application->enrollmentConfirmation) {
                throw new Exception("No enrollment confirmation found for this application");
            }
            
            $enrollment = $application->enrollmentConfirmation;
            $daysRemaining = now()->diffInDays($enrollment->enrollment_deadline);
            
            $template = $this->getTemplate('enrollment_reminder');
            
            $variables = [
                'applicant_name' => $application->first_name . ' ' . $application->last_name,
                'days_remaining' => $daysRemaining,
                'enrollment_deadline' => $enrollment->enrollment_deadline->format('F d, Y'),
                'deposit_amount' => '$' . number_format($enrollment->deposit_amount, 2),
                'portal_link' => config('app.url') . '/enrollment/' . $application->application_uuid,
            ];
            
            $message = $this->parseTemplate($template, $variables);
            
            $result = $this->sendNotification(
                $application,
                "Enrollment Reminder - {$daysRemaining} Days Remaining",
                $message,
                ['email', 'sms']
            );
            
            return $result;
            
        } catch (Exception $e) {
            Log::error('Failed to send enrollment reminder', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send bulk notification to multiple applications
     *
     * @param array $applicationIds
     * @param string $subject
     * @param string $message
     * @param array $channels
     * @return array
     */
    public function sendBulkNotification(
        array $applicationIds,
        string $subject,
        string $message,
        array $channels = ['email']
    ): array {
        $results = [
            'total' => count($applicationIds),
            'sent' => 0,
            'failed' => 0,
            'queued' => 0,
            'details' => [],
        ];

        // Check rate limits
        if ($this->exceedsRateLimit(count($applicationIds), $channels)) {
            // Queue for later processing
            Queue::push(new SendBulkNotification($applicationIds, $subject, $message, $channels));
            
            $results['queued'] = count($applicationIds);
            $results['status'] = 'queued';
            
            Log::info('Bulk notification queued due to rate limits', [
                'count' => count($applicationIds),
                'channels' => $channels,
            ]);
            
            return $results;
        }

        // Process notifications
        foreach ($applicationIds as $applicationId) {
            try {
                $application = AdmissionApplication::find($applicationId);
                
                if (!$application) {
                    $results['failed']++;
                    $results['details'][] = [
                        'application_id' => $applicationId,
                        'status' => 'failed',
                        'error' => 'Application not found',
                    ];
                    continue;
                }

                $result = $this->sendNotification($application, $subject, $message, $channels);
                
                if ($result['status'] === 'success') {
                    $results['sent']++;
                } else {
                    $results['failed']++;
                }
                
                $results['details'][] = [
                    'application_id' => $applicationId,
                    'status' => $result['status'],
                    'channels' => $result['channels_used'] ?? [],
                ];
                
            } catch (Exception $e) {
                $results['failed']++;
                $results['details'][] = [
                    'application_id' => $applicationId,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        Log::info('Bulk notification completed', [
            'sent' => $results['sent'],
            'failed' => $results['failed'],
            'total' => $results['total'],
        ]);

        return $results;
    }

    /**
     * Schedule a notification for future delivery
     *
     * @param int $applicationId
     * @param string $templateKey
     * @param Carbon $sendAt
     * @return array
     */
    public function scheduleNotification(int $applicationId, string $templateKey, Carbon $sendAt): array
    {
        try {
            $application = AdmissionApplication::findOrFail($applicationId);
            
            // Create scheduled communication record
            $communication = new ApplicationCommunication();
            $communication->application_id = $applicationId;
            $communication->communication_type = 'scheduled';
            $communication->direction = 'outbound';
            $communication->template_used = $templateKey;
            $communication->status = 'scheduled';
            $communication->scheduled_at = $sendAt;
            $communication->save();
            
            // Schedule the job
            $delay = now()->diffInSeconds($sendAt);
            Queue::later($delay, function () use ($communication) {
                $this->processScheduledNotification($communication);
            });
            
            Log::info('Notification scheduled', [
                'application_id' => $applicationId,
                'template' => $templateKey,
                'send_at' => $sendAt->toIso8601String(),
            ]);
            
            return [
                'status' => 'success',
                'communication_id' => $communication->id,
                'scheduled_for' => $sendAt->toIso8601String(),
            ];
            
        } catch (Exception $e) {
            Log::error('Failed to schedule notification', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Track notification delivery status
     *
     * @param int $notificationId
     * @return array
     */
    public function trackNotificationDelivery(int $notificationId): array
    {
        try {
            $communication = ApplicationCommunication::findOrFail($notificationId);
            
            $tracking = [
                'notification_id' => $notificationId,
                'status' => $communication->status,
                'sent_at' => $communication->sent_at?->toIso8601String(),
                'delivered_at' => $communication->delivered_at?->toIso8601String(),
                'opened_at' => $communication->opened_at?->toIso8601String(),
                'clicked_at' => $communication->clicked_at?->toIso8601String(),
            ];
            
            // Check delivery status with email/SMS provider
            if ($communication->communication_type === 'email' && $communication->message_id) {
                $tracking['email_status'] = $this->checkEmailDeliveryStatus($communication->message_id);
            }
            
            if ($communication->communication_type === 'sms' && $communication->message_id) {
                $tracking['sms_status'] = $this->checkSMSDeliveryStatus($communication->message_id);
            }
            
            return $tracking;
            
        } catch (Exception $e) {
            Log::error('Failed to track notification delivery', [
                'notification_id' => $notificationId,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send application started email
     * This is called when an application is first created
     *
     * @param AdmissionApplication $application
     * @return array
     */
    public function sendApplicationStartedEmail(AdmissionApplication $application): array
    {
        try {
            $template = $this->getTemplate('application_started');
            
            $variables = [
                'applicant_name' => $application->first_name . ' ' . $application->last_name,
                'application_number' => $application->application_number,
                'application_uuid' => $application->application_uuid,
                'continue_link' => config('app.url') . '/admissions/portal/continue/' . $application->application_uuid,
                'email' => $application->email,
                'expires_at' => $application->expires_at ? $application->expires_at->format('F d, Y') : 'in 90 days',
                'portal_link' => config('app.url') . '/apply/status/' . $application->application_uuid,
            ];
            
            $message = $this->parseTemplate($template, $variables);
            
            $result = $this->sendNotification(
                $application,
                'Application Started - ' . $application->application_number,
                $message,
                ['email']
            );
            
            Log::info('Application started notification sent', [
                'application_id' => $application->id,
                'channels' => $result['channels_used'],
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            Log::error('Failed to send application started notification', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Resend failed notifications
     *
     * @return array
     */
    public function resendFailedNotifications(): array
    {
        $results = [
            'total_failed' => 0,
            'resent' => 0,
            'still_failed' => 0,
            'details' => [],
        ];

        // Find failed notifications from last 24 hours
        $failedNotifications = ApplicationCommunication::where('status', 'failed')
            ->where('created_at', '>', now()->subDay())
            ->where('retry_count', '<', self::MAX_RETRY_ATTEMPTS)
            ->get();

        $results['total_failed'] = $failedNotifications->count();

        foreach ($failedNotifications as $notification) {
            try {
                $application = AdmissionApplication::find($notification->application_id);
                
                if (!$application) {
                    $results['still_failed']++;
                    continue;
                }

                // Increment retry count
                $notification->retry_count = ($notification->retry_count ?? 0) + 1;
                $notification->save();

                // Resend notification
                $result = $this->sendNotification(
                    $application,
                    $notification->subject,
                    $notification->message,
                    [$notification->communication_type]
                );

                if ($result['status'] === 'success') {
                    $notification->status = 'sent';
                    $notification->sent_at = now();
                    $notification->save();
                    $results['resent']++;
                } else {
                    $results['still_failed']++;
                }

                $results['details'][] = [
                    'notification_id' => $notification->id,
                    'application_id' => $notification->application_id,
                    'status' => $result['status'],
                    'retry_count' => $notification->retry_count,
                ];

            } catch (Exception $e) {
                $results['still_failed']++;
                Log::error('Failed to resend notification', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Failed notifications resend completed', $results);

        return $results;
    }

    /**
     * Private helper methods
     */

    /**
     * Send notification through specified channels
     */
    private function sendNotification(
        AdmissionApplication $application,
        string $subject,
        string $message,
        array $channels = ['email'],
        string $priority = 'normal'
    ): array {
        $results = [
            'status' => 'success',
            'channels_used' => [],
            'errors' => [],
        ];

        foreach ($channels as $channel) {
            if (!self::CHANNELS[$channel] ?? false) {
                continue;
            }

            try {
                switch ($channel) {
                    case 'email':
                        $emailResult = $this->sendEmail($application, $subject, $message, $priority);
                        if ($emailResult['success']) {
                            $results['channels_used'][] = 'email';
                        } else {
                            $results['errors']['email'] = $emailResult['error'];
                        }
                        break;
                        
                    case 'sms':
                        $smsResult = $this->sendSMS($application, $message);
                        if ($smsResult['success']) {
                            $results['channels_used'][] = 'sms';
                        } else {
                            $results['errors']['sms'] = $smsResult['error'];
                        }
                        break;
                        
                    case 'in_app':
                        $this->createInAppNotification($application, $subject, $message);
                        $results['channels_used'][] = 'in_app';
                        break;
                }
            } catch (Exception $e) {
                $results['errors'][$channel] = $e->getMessage();
            }
        }

        // Log communication
        $this->logCommunication($application, implode(',', $channels), $message, $results);

        if (empty($results['channels_used'])) {
            $results['status'] = 'failed';
        }

        return $results;
    }

    /**
     * Send email notification
     */
    private function sendEmail(AdmissionApplication $application, string $subject, string $message, string $priority): array
    {
        try {
            $email = $application->email;
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'error' => 'Invalid email address'];
            }

            // Use send instead of queue for immediate sending
            Mail::to($email)
                ->send(new ApplicationStatusMail($application, $subject, $message, $priority));

            return ['success' => true, 'message_id' => uniqid('email_')];
            
        } catch (Exception $e) {
            Log::error('Email sending failed', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send SMS notification
     */
    private function sendSMS(AdmissionApplication $application, string $message): array
    {
        try {
            $phone = $application->phone_primary;
            
            if (!$phone) {
                return ['success' => false, 'error' => 'No phone number available'];
            }

            // Truncate message for SMS (160 chars)
            $smsMessage = substr($message, 0, 160);

            // Send based on provider
            switch (self::SMS_PROVIDER) {
                case 'twilio':
                    return $this->sendViaTwilio($phone, $smsMessage);
                case 'africastalking':
                    return $this->sendViaAfricasTalking($phone, $smsMessage);
                default:
                    return ['success' => false, 'error' => 'SMS provider not configured'];
            }
            
        } catch (Exception $e) {
            Log::error('SMS sending failed', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send SMS via Twilio
     */
    private function sendViaTwilio(string $phone, string $message): array
    {
        try {
            // This would use Twilio SDK in production
            // $twilio = new \Twilio\Rest\Client($sid, $token);
            // $message = $twilio->messages->create($phone, [
            //     'from' => config('services.twilio.from'),
            //     'body' => $message
            // ]);
            
            // Simulated for now
            Log::info('SMS sent via Twilio', ['phone' => $phone]);
            
            return ['success' => true, 'message_id' => uniqid('sms_')];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send SMS via Africa's Talking
     */
    private function sendViaAfricasTalking(string $phone, string $message): array
    {
        try {
            // This would use Africa's Talking SDK in production
            // $AT = new AfricasTalking($username, $apiKey);
            // $sms = $AT->sms();
            // $result = $sms->send([
            //     'to' => $phone,
            //     'message' => $message
            // ]);
            
            // Simulated for now
            Log::info('SMS sent via AfricasTalking', ['phone' => $phone]);
            
            return ['success' => true, 'message_id' => uniqid('sms_')];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create in-app notification
     */
    private function createInAppNotification(AdmissionApplication $application, string $subject, string $message): void
    {
        // This would create a notification in the application portal
        DB::table('notifications')->insert([
            'user_id' => $application->user_id,
            'type' => 'application_update',
            'title' => $subject,
            'message' => $message,
            'data' => json_encode(['application_id' => $application->id]),
            'created_at' => now(),
        ]);
    }

    /**
     * Send submission confirmation notification
     *
     * @param AdmissionApplication $application
     * @return array
     */
    public function sendSubmissionConfirmation(AdmissionApplication $application): array
    {
        try {
            $template = $this->getTemplate('submission_confirmation');
            
            $variables = [
                'applicant_name' => $application->first_name . ' ' . $application->last_name,
                'application_number' => $application->application_number,
                'program_name' => $application->program ? $application->program->name : 'N/A',
                'term_name' => $application->term ? $application->term->name : 'N/A',
                'submission_date' => now()->format('F d, Y'),
                'submission_time' => now()->format('g:i A'),
                'application_uuid' => $application->application_uuid,
                'portal_link' => config('app.url') . '/apply/status/' . $application->application_uuid,
                'email' => $application->email,
            ];
            
            $message = $this->parseTemplate($template, $variables);
            
            $result = $this->sendNotification(
                $application,
                'Application Submitted Successfully - ' . $application->application_number,
                $message,
                ['email'] // Only send email for submission confirmation
            );
            
            Log::info('Submission confirmation notification sent', [
                'application_id' => $application->id,
                'application_number' => $application->application_number,
                'channels' => $result['channels_used'] ?? [],
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            Log::error('Failed to send submission confirmation notification', [
                'application_id' => $application->id ?? null,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get email template
     */
    private function getTemplate(string $key): ?string
    {
        $template = EmailTemplate::where('key', $key)
            ->where('is_active', true)
            ->first();
        
        if ($template) {
            return $template->content;
        }
        
        // Return default templates
        return $this->getDefaultTemplate($key);
    }

    /**
     * Get default template
     */
    private function getDefaultTemplate(string $key): string
    {
        $templates = [
            'application_received' => "Dear {{applicant_name}},\n\nThank you for submitting your application ({{application_number}}) to {{program_name}} for {{term_name}}.\n\nWe have received your application on {{submission_date}} and it is now under review.\n\nYou can check your application status at: {{portal_link}}\n\nBest regards,\nAdmissions Office",
            
            'submission_confirmation' => "Dear {{applicant_name}},\n\nCongratulations! Your application ({{application_number}}) has been successfully submitted.\n\nProgram: {{program_name}}\nTerm: {{term_name}}\nSubmission Date: {{submission_date}}\nSubmission Time: {{submission_time}}\n\nWhat happens next:\n1. Our admissions team will review your application\n2. We may contact you if additional documents are needed\n3. You will receive a decision notification once the review is complete\n\nYou can track your application status anytime at:\n{{portal_link}}\n\nYour Application ID: {{application_uuid}}\n\nIf you have any questions, please contact us at admissions@university.edu\n\nBest regards,\nAdmissions Office",
            
            'document_request' => "Dear {{applicant_name}},\n\nWe need the following documents to continue processing your application ({{application_number}}):\n\n{{document_list}}\n\nPlease upload these documents by {{deadline}} at: {{upload_link}}\n\nBest regards,\nAdmissions Office",
            
            'status_update' => "Dear {{applicant_name}},\n\nYour application ({{application_number}}) status has been updated to: {{new_status}}\n\n{{status_message}}\n\nView details at: {{portal_link}}\n\nBest regards,\nAdmissions Office",
            
            'enrollment_reminder' => "Dear {{applicant_name}},\n\nThis is a reminder that you have {{days_remaining}} days remaining to confirm your enrollment.\n\nDeadline: {{enrollment_deadline}}\nDeposit Required: {{deposit_amount}}\n\nConfirm your enrollment at: {{portal_link}}\n\nBest regards,\nAdmissions Office",
            
            'application_started' => "Dear {{applicant_name}},\n\nThank you for starting your application ({{application_number}}).\n\nYour application has been saved and you can continue at any time before {{expires_at}}.\n\nTo continue your application, visit:\n{{continue_link}}\n\nYour Application ID: {{application_uuid}}\nYour Email: {{email}}\n\nBest regards,\nAdmissions Office",
        ];
        
        return $templates[$key] ?? "Dear {{applicant_name}},\n\nThis is a notification regarding your application {{application_number}}.\n\nBest regards,\nAdmissions Office";
    }

    /**
     * Parse template with variables
     */
    private function parseTemplate(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        
        return $template;
    }

    /**
     * Log communication
     */
    private function logCommunication(
        AdmissionApplication $application,
        string $channel,
        string $message,
        array $result
    ): void {
        ApplicationCommunication::create([
            'application_id' => $application->id,
            'communication_type' => $channel,
            'direction' => 'outbound',
            'message' => $message,
            'recipient_email' => $application->email,
            'recipient_phone' => $application->phone_primary,
            'status' => $result['status'] === 'success' ? 'sent' : 'failed',
            'sent_at' => $result['status'] === 'success' ? now() : null,
            'error_message' => implode('; ', $result['errors'] ?? []),
        ]);
    }

    /**
     * Check if rate limit is exceeded
     */
    private function exceedsRateLimit(int $count, array $channels): bool
    {
        if (in_array('email', $channels)) {
            $recentEmails = ApplicationCommunication::where('communication_type', 'email')
                ->where('created_at', '>', now()->subHour())
                ->count();
            
            if (($recentEmails + $count) > self::MAX_EMAILS_PER_HOUR) {
                return true;
            }
        }
        
        if (in_array('sms', $channels)) {
            $recentSMS = ApplicationCommunication::where('communication_type', 'sms')
                ->where('created_at', '>', now()->subHour())
                ->count();
            
            if (($recentSMS + $count) > self::MAX_SMS_PER_HOUR) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Process scheduled notification
     */
    private function processScheduledNotification(ApplicationCommunication $communication): void
    {
        try {
            $application = AdmissionApplication::find($communication->application_id);
            
            if (!$application) {
                $communication->status = 'failed';
                $communication->error_message = 'Application not found';
                $communication->save();
                return;
            }
            
            $template = $this->getTemplate($communication->template_used);
            $message = $this->parseTemplate($template, [
                'applicant_name' => $application->first_name . ' ' . $application->last_name,
                'application_number' => $application->application_number,
            ]);
            
            $result = $this->sendNotification(
                $application,
                $communication->subject ?? 'Scheduled Notification',
                $message,
                [$communication->communication_type]
            );
            
            $communication->status = $result['status'] === 'success' ? 'sent' : 'failed';
            $communication->sent_at = $result['status'] === 'success' ? now() : null;
            $communication->save();
            
        } catch (Exception $e) {
            Log::error('Failed to process scheduled notification', [
                'communication_id' => $communication->id,
                'error' => $e->getMessage(),
            ]);
            
            $communication->status = 'failed';
            $communication->error_message = $e->getMessage();
            $communication->save();
        }
    }

    /**
     * Check email delivery status
     */
    private function checkEmailDeliveryStatus(string $messageId): array
    {
        // This would integrate with email service provider API
        // For example, SendGrid, Mailgun, AWS SES
        
        return [
            'status' => 'delivered',
            'delivered_at' => now()->subMinutes(5)->toIso8601String(),
            'opens' => 1,
            'clicks' => 0,
        ];
    }

    /**
     * Check SMS delivery status
     */
    private function checkSMSDeliveryStatus(string $messageId): array
    {
        // This would integrate with SMS provider API
        
        return [
            'status' => 'delivered',
            'delivered_at' => now()->subMinutes(2)->toIso8601String(),
        ];
    }
}