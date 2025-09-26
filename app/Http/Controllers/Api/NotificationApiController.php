<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApplicationNotificationService;
use App\Models\ApplicationCommunication;
use App\Models\AdmissionApplication;
use App\Models\EntranceExamRegistration;
use App\Models\EmailTemplate;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\NotificationCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendBulkNotification;
use App\Jobs\ProcessNotificationQueue;
use Carbon\Carbon;
use Exception;

class NotificationApiController extends Controller
{
    protected $notificationService;

    /**
     * API response codes
     */
    private const RESPONSE_CODES = [
        'SUCCESS' => 200,
        'CREATED' => 201,
        'ACCEPTED' => 202,
        'NO_CONTENT' => 204,
        'BAD_REQUEST' => 400,
        'UNAUTHORIZED' => 401,
        'FORBIDDEN' => 403,
        'NOT_FOUND' => 404,
        'TOO_MANY_REQUESTS' => 429,
        'SERVER_ERROR' => 500,
    ];

    /**
     * Notification types
     */
    private const NOTIFICATION_TYPES = [
        'email' => 'Email',
        'sms' => 'SMS',
        'push' => 'Push Notification',
        'in_app' => 'In-App Message',
        'portal' => 'Portal Message',
    ];

    /**
     * Notification priorities
     */
    private const NOTIFICATION_PRIORITIES = [
        'urgent' => 1,
        'high' => 2,
        'normal' => 3,
        'low' => 4,
    ];

    /**
     * Maximum recipients for bulk send
     */
    private const MAX_BULK_RECIPIENTS = 500;

    /**
     * Rate limits
     */
    private const RATE_LIMITS = [
        'email' => 100,  // per hour
        'sms' => 50,     // per hour
        'push' => 200,   // per hour
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(ApplicationNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        
        // API authentication middleware
        $this->middleware('auth:sanctum');
        
        // Role-based access
        $this->middleware('role:admin,admissions_officer,exam_coordinator')
            ->except(['getDeliveryStatus', 'getMyNotifications']);
        
        // Rate limiting
        $this->middleware('throttle:notifications');
    }

    /**
     * Send a notification.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'recipient_type' => 'required|in:application,registration,email,user',
                'recipient_id' => 'required_unless:recipient_type,email',
                'recipient_email' => 'required_if:recipient_type,email|email',
                'type' => 'required|in:' . implode(',', array_keys(self::NOTIFICATION_TYPES)),
                'template' => 'required_if:use_template,true|string|max:100',
                'subject' => 'required_unless:use_template,true|string|max:255',
                'message' => 'required_unless:use_template,true|string',
                'variables' => 'nullable|array',
                'priority' => 'nullable|in:' . implode(',', array_keys(self::NOTIFICATION_PRIORITIES)),
                'schedule_at' => 'nullable|date|after:now',
                'attachments' => 'nullable|array|max:3',
                'attachments.*' => 'file|max:5120', // 5MB max per file
                'cc' => 'nullable|array|max:5',
                'cc.*' => 'email',
                'bcc' => 'nullable|array|max:5',
                'bcc.*' => 'email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], self::RESPONSE_CODES['VALIDATION_ERROR']);
            }

            $validated = $validator->validated();

            // Check rate limits
            if (!$this->checkRateLimit($validated['type'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded for ' . $validated['type'],
                    'retry_after' => $this->getRetryAfter($validated['type']),
                ], self::RESPONSE_CODES['TOO_MANY_REQUESTS']);
            }

            // Get recipient information
            $recipient = $this->getRecipient($validated);
            if (!$recipient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recipient not found',
                ], self::RESPONSE_CODES['NOT_FOUND']);
            }

            // Process attachments if any
            $attachmentPaths = [];
            if (!empty($validated['attachments'])) {
                foreach ($validated['attachments'] as $attachment) {
                    $path = $attachment->store('notifications/attachments');
                    $attachmentPaths[] = [
                        'path' => $path,
                        'name' => $attachment->getClientOriginalName(),
                        'size' => $attachment->getSize(),
                        'mime' => $attachment->getMimeType(),
                    ];
                }
            }

            // Prepare notification data
            $notificationData = [
                'type' => $validated['type'],
                'recipient' => $recipient,
                'subject' => $validated['subject'] ?? null,
                'message' => $validated['message'] ?? null,
                'template' => $validated['template'] ?? null,
                'variables' => $validated['variables'] ?? [],
                'priority' => self::NOTIFICATION_PRIORITIES[$validated['priority'] ?? 'normal'],
                'attachments' => $attachmentPaths,
                'cc' => $validated['cc'] ?? [],
                'bcc' => $validated['bcc'] ?? [],
                'sender_id' => Auth::id(),
            ];

            // Schedule or send immediately
            if (!empty($validated['schedule_at'])) {
                $notification = $this->scheduleNotification($notificationData, $validated['schedule_at']);
                $message = 'Notification scheduled successfully';
            } else {
                $notification = $this->sendNotification($notificationData);
                $message = 'Notification sent successfully';
            }

            // Update rate limit counter
            $this->updateRateLimit($validated['type']);

            // Log the action
            $this->logNotificationAction('send', $notification, Auth::user());

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => new NotificationResource($notification),
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to send notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Get delivery status of a notification.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getDeliveryStatus($id): JsonResponse
    {
        try {
            $notification = ApplicationCommunication::findOrFail($id);

            // Check authorization
            if (!$this->canAccessNotification($notification)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to access this notification',
                ], self::RESPONSE_CODES['FORBIDDEN']);
            }

            // Get delivery tracking information
            $tracking = $this->getDeliveryTracking($notification);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $notification->id,
                    'status' => $notification->status,
                    'sent_at' => $notification->sent_at?->toIso8601String(),
                    'delivered_at' => $notification->delivered_at?->toIso8601String(),
                    'opened_at' => $notification->opened_at?->toIso8601String(),
                    'clicked_at' => $notification->clicked_at?->toIso8601String(),
                    'tracking' => $tracking,
                ],
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to get delivery status', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get delivery status',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Resend a notification.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function resend($id, Request $request): JsonResponse
    {
        try {
            $notification = ApplicationCommunication::findOrFail($id);

            // Check if notification can be resent
            if (!in_array($notification->status, ['failed', 'bounced'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only failed or bounced notifications can be resent',
                ], self::RESPONSE_CODES['BAD_REQUEST']);
            }

            // Check rate limits
            if (!$this->checkRateLimit($notification->communication_type)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded',
                    'retry_after' => $this->getRetryAfter($notification->communication_type),
                ], self::RESPONSE_CODES['TOO_MANY_REQUESTS']);
            }

            // Resend the notification
            $result = $this->notificationService->resendNotification($notification->id);

            if ($result) {
                // Update status
                $notification->status = 'pending';
                $notification->resent_at = now();
                $notification->resent_by = Auth::id();
                $notification->save();

                // Log the action
                $this->logNotificationAction('resend', $notification, Auth::user());

                return response()->json([
                    'success' => true,
                    'message' => 'Notification resent successfully',
                    'data' => new NotificationResource($notification->fresh()),
                ], self::RESPONSE_CODES['SUCCESS']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend notification',
            ], self::RESPONSE_CODES['SERVER_ERROR']);

        } catch (Exception $e) {
            Log::error('Failed to resend notification', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend notification',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Send bulk notifications.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkSend(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'recipient_type' => 'required|in:applications,registrations,custom_list',
                'recipient_ids' => 'required_if:recipient_type,custom_list|array|max:' . self::MAX_BULK_RECIPIENTS,
                'recipient_ids.*' => 'integer',
                'filters' => 'required_unless:recipient_type,custom_list|array',
                'filters.status' => 'nullable|array',
                'filters.program_id' => 'nullable|integer',
                'filters.term_id' => 'nullable|integer',
                'filters.decision' => 'nullable|string',
                'type' => 'required|in:' . implode(',', array_keys(self::NOTIFICATION_TYPES)),
                'template' => 'required|string|exists:email_templates,slug',
                'variables' => 'nullable|array',
                'priority' => 'nullable|in:' . implode(',', array_keys(self::NOTIFICATION_PRIORITIES)),
                'batch_size' => 'nullable|integer|min:10|max:100',
                'delay_between_batches' => 'nullable|integer|min:1|max:60', // seconds
                'test_mode' => 'nullable|boolean',
                'test_recipients' => 'required_if:test_mode,true|array|max:5',
                'test_recipients.*' => 'email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], self::RESPONSE_CODES['VALIDATION_ERROR']);
            }

            $validated = $validator->validated();

            // Get recipients based on type and filters
            $recipients = $this->getBulkRecipients($validated);

            if ($recipients->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No recipients found matching the criteria',
                ], self::RESPONSE_CODES['NOT_FOUND']);
            }

            // Check if exceeds maximum
            if ($recipients->count() > self::MAX_BULK_RECIPIENTS) {
                return response()->json([
                    'success' => false,
                    'message' => sprintf('Too many recipients (%d). Maximum allowed is %d', 
                        $recipients->count(), 
                        self::MAX_BULK_RECIPIENTS
                    ),
                ], self::RESPONSE_CODES['BAD_REQUEST']);
            }

            // Prepare bulk notification job
            $jobData = [
                'recipients' => $recipients->pluck('id')->toArray(),
                'recipient_type' => $validated['recipient_type'],
                'notification_type' => $validated['type'],
                'template' => $validated['template'],
                'variables' => $validated['variables'] ?? [],
                'priority' => self::NOTIFICATION_PRIORITIES[$validated['priority'] ?? 'normal'],
                'batch_size' => $validated['batch_size'] ?? 50,
                'delay_between_batches' => $validated['delay_between_batches'] ?? 5,
                'test_mode' => $validated['test_mode'] ?? false,
                'test_recipients' => $validated['test_recipients'] ?? [],
                'initiated_by' => Auth::id(),
            ];

            // Queue the bulk notification job
            $job = new SendBulkNotification($jobData);
            
            if ($validated['priority'] === 'urgent') {
                dispatch($job)->onQueue('high');
            } else {
                dispatch($job)->onQueue('notifications');
            }

            // Create tracking record
            $tracking = $this->createBulkNotificationTracking($jobData, $recipients->count());

            // Log the action
            Log::info('Bulk notification initiated', [
                'tracking_id' => $tracking->id,
                'recipient_count' => $recipients->count(),
                'type' => $validated['type'],
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => sprintf('Bulk notification queued for %d recipients', $recipients->count()),
                'data' => [
                    'tracking_id' => $tracking->id,
                    'recipient_count' => $recipients->count(),
                    'estimated_completion' => $this->estimateCompletionTime($recipients->count(), $jobData['batch_size']),
                    'status_url' => route('api.notifications.bulk-status', $tracking->id),
                ],
            ], self::RESPONSE_CODES['ACCEPTED']);

        } catch (Exception $e) {
            Log::error('Failed to initiate bulk notification', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate bulk notification',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Get bulk notification status.
     *
     * @param int $trackingId
     * @return JsonResponse
     */
    public function bulkStatus($trackingId): JsonResponse
    {
        try {
            $tracking = DB::table('bulk_notification_tracking')
                ->where('id', $trackingId)
                ->first();

            if (!$tracking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tracking record not found',
                ], self::RESPONSE_CODES['NOT_FOUND']);
            }

            // Get detailed statistics
            $stats = $this->getBulkNotificationStats($trackingId);

            return response()->json([
                'success' => true,
                'data' => [
                    'tracking_id' => $tracking->id,
                    'status' => $tracking->status,
                    'total_recipients' => $tracking->total_recipients,
                    'processed' => $tracking->processed_count,
                    'successful' => $tracking->success_count,
                    'failed' => $tracking->failed_count,
                    'progress_percentage' => round(($tracking->processed_count / $tracking->total_recipients) * 100, 2),
                    'started_at' => Carbon::parse($tracking->started_at)->toIso8601String(),
                    'completed_at' => $tracking->completed_at ? Carbon::parse($tracking->completed_at)->toIso8601String() : null,
                    'statistics' => $stats,
                ],
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to get bulk notification status', [
                'tracking_id' => $trackingId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get bulk notification status',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Get notification templates.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTemplates(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type');
            $category = $request->input('category');

            $query = EmailTemplate::where('is_active', true);

            if ($type) {
                $query->where('type', $type);
            }

            if ($category) {
                $query->where('category', $category);
            }

            $templates = $query->select('id', 'name', 'slug', 'description', 'type', 'category')
                ->orderBy('category')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $templates,
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to get templates', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get templates',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Get my notifications (for authenticated user).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getMyNotifications(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $perPage = $request->input('per_page', 20);
            $type = $request->input('type');
            $unreadOnly = $request->boolean('unread_only');

            // Get notifications for the user
            $query = ApplicationCommunication::where(function ($q) use ($user) {
                    $q->where('recipient_email', $user->email)
                      ->orWhere('sender_id', $user->id);
                })
                ->where('direction', 'inbound');

            if ($type) {
                $query->where('communication_type', $type);
            }

            if ($unreadOnly) {
                $query->whereNull('opened_at');
            }

            $notifications = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => new NotificationCollection($notifications),
                'meta' => [
                    'total' => $notifications->total(),
                    'per_page' => $notifications->perPage(),
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'unread_count' => $this->getUnreadCount($user),
                ],
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to get user notifications', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get notifications',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Mark notification as read.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function markAsRead($id): JsonResponse
    {
        try {
            $notification = ApplicationCommunication::findOrFail($id);

            // Check authorization
            if (!$this->canAccessNotification($notification)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], self::RESPONSE_CODES['FORBIDDEN']);
            }

            if (!$notification->opened_at) {
                $notification->opened_at = now();
                $notification->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Helper: Get recipient information.
     */
    private function getRecipient(array $data): ?array
    {
        switch ($data['recipient_type']) {
            case 'application':
                $application = AdmissionApplication::find($data['recipient_id']);
                return $application ? [
                    'id' => $application->id,
                    'type' => 'application',
                    'email' => $application->email,
                    'phone' => $application->phone_primary,
                    'name' => $application->first_name . ' ' . $application->last_name,
                ] : null;

            case 'registration':
                $registration = EntranceExamRegistration::find($data['recipient_id']);
                return $registration ? [
                    'id' => $registration->id,
                    'type' => 'registration',
                    'email' => $registration->candidate_email,
                    'phone' => $registration->candidate_phone,
                    'name' => $registration->candidate_name,
                ] : null;

            case 'email':
                return [
                    'id' => null,
                    'type' => 'email',
                    'email' => $data['recipient_email'],
                    'phone' => null,
                    'name' => null,
                ];

            case 'user':
                $user = User::find($data['recipient_id']);
                return $user ? [
                    'id' => $user->id,
                    'type' => 'user',
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'name' => $user->name,
                ] : null;

            default:
                return null;
        }
    }

    /**
     * Helper: Check rate limits.
     */
    private function checkRateLimit(string $type): bool
    {
        $key = "notification_rate_limit:{$type}:" . Auth::id();
        $count = Cache::get($key, 0);
        
        return $count < (self::RATE_LIMITS[$type] ?? 100);
    }

    /**
     * Helper: Update rate limit counter.
     */
    private function updateRateLimit(string $type): void
    {
        $key = "notification_rate_limit:{$type}:" . Auth::id();
        $count = Cache::get($key, 0);
        
        Cache::put($key, $count + 1, now()->addHour());
    }

    /**
     * Helper: Get retry after time.
     */
    private function getRetryAfter(string $type): int
    {
        $key = "notification_rate_limit:{$type}:" . Auth::id();
        $ttl = Cache::getStore()->getRedis()->ttl($key);
        
        return $ttl > 0 ? $ttl : 3600;
    }

    /**
     * Helper: Check if user can access notification.
     */
    private function canAccessNotification(ApplicationCommunication $notification): bool
    {
        $user = Auth::user();
        
        // Admin can access all
        if ($user->hasRole(['admin', 'admissions_officer'])) {
            return true;
        }
        
        // Check if user is recipient or sender
        return $notification->recipient_email === $user->email || 
               $notification->sender_id === $user->id;
    }

    /**
     * Helper: Get delivery tracking information.
     */
    private function getDeliveryTracking(ApplicationCommunication $notification): array
    {
        // This would integrate with email/SMS service providers
        // For now, return basic tracking
        return [
            'provider' => 'internal',
            'message_id' => $notification->message_id ?? null,
            'events' => [],
        ];
    }

    /**
     * Helper: Send notification immediately.
     */
    private function sendNotification(array $data): ApplicationCommunication
    {
        // Create communication record
        $notification = ApplicationCommunication::create([
            'application_id' => $data['recipient']['type'] === 'application' ? $data['recipient']['id'] : null,
            'communication_type' => $data['type'],
            'direction' => 'outbound',
            'subject' => $data['subject'],
            'message' => $data['message'],
            'recipient_email' => $data['recipient']['email'] ?? null,
            'recipient_phone' => $data['recipient']['phone'] ?? null,
            'sender_id' => $data['sender_id'],
            'template_used' => $data['template'] ?? null,
            'template_variables' => $data['variables'],
            'status' => 'pending',
        ]);

        // Send based on type
        switch ($data['type']) {
            case 'email':
                $this->notificationService->sendEmail($notification);
                break;
            case 'sms':
                $this->notificationService->sendSMS($notification);
                break;
            case 'push':
                $this->notificationService->sendPushNotification($notification);
                break;
        }

        return $notification;
    }

    /**
     * Helper: Schedule notification for later.
     */
    private function scheduleNotification(array $data, string $scheduleAt): ApplicationCommunication
    {
        $notification = ApplicationCommunication::create([
            'application_id' => $data['recipient']['type'] === 'application' ? $data['recipient']['id'] : null,
            'communication_type' => $data['type'],
            'direction' => 'outbound',
            'subject' => $data['subject'],
            'message' => $data['message'],
            'recipient_email' => $data['recipient']['email'] ?? null,
            'recipient_phone' => $data['recipient']['phone'] ?? null,
            'sender_id' => $data['sender_id'],
            'template_used' => $data['template'] ?? null,
            'template_variables' => $data['variables'],
            'status' => 'scheduled',
            'scheduled_at' => $scheduleAt,
        ]);

        // Queue the job for scheduled time
        dispatch(new ProcessNotificationQueue($notification->id))
            ->delay(Carbon::parse($scheduleAt));

        return $notification;
    }

    /**
     * Helper: Get bulk recipients.
     */
    private function getBulkRecipients(array $data): Collection
    {
        switch ($data['recipient_type']) {
            case 'applications':
                $query = AdmissionApplication::query();
                
                if (!empty($data['filters']['status'])) {
                    $query->whereIn('status', $data['filters']['status']);
                }
                if (!empty($data['filters']['program_id'])) {
                    $query->where('program_id', $data['filters']['program_id']);
                }
                if (!empty($data['filters']['term_id'])) {
                    $query->where('term_id', $data['filters']['term_id']);
                }
                if (!empty($data['filters']['decision'])) {
                    $query->where('decision', $data['filters']['decision']);
                }
                
                return $query->select('id', 'email', 'phone_primary', 'first_name', 'last_name')->get();

            case 'registrations':
                $query = EntranceExamRegistration::query();
                
                if (!empty($data['filters']['exam_id'])) {
                    $query->where('exam_id', $data['filters']['exam_id']);
                }
                if (!empty($data['filters']['status'])) {
                    $query->whereIn('registration_status', $data['filters']['status']);
                }
                
                return $query->select('id', 'candidate_email', 'candidate_phone', 'candidate_name')->get();

            case 'custom_list':
                return AdmissionApplication::whereIn('id', $data['recipient_ids'])
                    ->select('id', 'email', 'phone_primary', 'first_name', 'last_name')
                    ->get();

            default:
                return collect();
        }
    }

    /**
     * Helper: Create bulk notification tracking record.
     */
    private function createBulkNotificationTracking(array $jobData, int $recipientCount)
    {
        return DB::table('bulk_notification_tracking')->insertGetId([
            'type' => $jobData['notification_type'],
            'template' => $jobData['template'],
            'total_recipients' => $recipientCount,
            'processed_count' => 0,
            'success_count' => 0,
            'failed_count' => 0,
            'status' => 'pending',
            'initiated_by' => $jobData['initiated_by'],
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Helper: Estimate completion time for bulk notification.
     */
    private function estimateCompletionTime(int $recipientCount, int $batchSize): string
    {
        $batches = ceil($recipientCount / $batchSize);
        $estimatedSeconds = $batches * 5; // Assuming 5 seconds per batch
        
        return now()->addSeconds($estimatedSeconds)->toIso8601String();
    }

    /**
     * Helper: Get bulk notification statistics.
     */
    private function getBulkNotificationStats(int $trackingId): array
    {
        $stats = ApplicationCommunication::where('bulk_tracking_id', $trackingId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'sent' => $stats['sent'] ?? 0,
            'delivered' => $stats['delivered'] ?? 0,
            'failed' => $stats['failed'] ?? 0,
            'bounced' => $stats['bounced'] ?? 0,
            'opened' => $stats['opened'] ?? 0,
        ];
    }

    /**
     * Helper: Get unread notification count for user.
     */
    private function getUnreadCount($user): int
    {
        return ApplicationCommunication::where('recipient_email', $user->email)
            ->where('direction', 'inbound')
            ->whereNull('opened_at')
            ->count();
    }

    /**
     * Helper: Log notification action.
     */
    private function logNotificationAction(string $action, $notification, $user): void
    {
        Log::info('Notification action performed', [
            'action' => $action,
            'notification_id' => $notification->id ?? null,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}