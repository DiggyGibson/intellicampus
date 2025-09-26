<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecommendationService;
use App\Services\ApplicationNotificationService;
use App\Models\RecommendationLetter;
use App\Models\AdmissionApplication;
use App\Models\ApplicationDocument;
use App\Http\Resources\RecommendationResource;
use App\Http\Resources\RecommendationCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class RecommendationApiController extends Controller
{
    protected $recommendationService;
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
        'CONFLICT' => 409,
        'VALIDATION_ERROR' => 422,
        'TOO_MANY_REQUESTS' => 429,
        'SERVER_ERROR' => 500,
    ];

    /**
     * Recommendation statuses
     */
    private const RECOMMENDATION_STATUSES = [
        'pending' => 'Invitation Sent',
        'in_progress' => 'In Progress',
        'submitted' => 'Submitted',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'waived' => 'Waived',
        'expired' => 'Expired',
    ];

    /**
     * Maximum file size for recommendation letter (MB)
     */
    private const MAX_FILE_SIZE = 5;

    /**
     * Allowed file types
     */
    private const ALLOWED_FILE_TYPES = ['pdf', 'doc', 'docx'];

    /**
     * Token expiry days
     */
    private const TOKEN_EXPIRY_DAYS = 30;

    /**
     * Maximum reminder attempts
     */
    private const MAX_REMINDER_ATTEMPTS = 3;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        RecommendationService $recommendationService,
        ApplicationNotificationService $notificationService
    ) {
        $this->recommendationService = $recommendationService;
        $this->notificationService = $notificationService;
        
        // API authentication middleware (except for submit with token)
        $this->middleware('auth:sanctum')->except(['submit', 'getByToken', 'declineByToken']);
        
        // Rate limiting
        $this->middleware('throttle:api');
    }

    /**
     * Request a recommendation letter.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function request(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'application_id' => 'required|integer|exists:admission_applications,id',
                'recommender_name' => 'required|string|max:200',
                'recommender_email' => 'required|email|max:255',
                'recommender_title' => 'required|string|max:100',
                'recommender_institution' => 'required|string|max:255',
                'recommender_phone' => 'nullable|string|max:20',
                'relationship_to_applicant' => 'required|string|max:100',
                'relationship_duration' => 'nullable|string|max:50',
                'recommendation_type' => 'required|in:academic,professional,character,general',
                'custom_message' => 'nullable|string|max:1000',
                'deadline' => 'nullable|date|after:today',
                'send_immediately' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], self::RESPONSE_CODES['VALIDATION_ERROR']);
            }

            $validated = $validator->validated();

            // Check if application belongs to user or user has permission
            $application = AdmissionApplication::findOrFail($validated['application_id']);
            
            if (!$this->canManageApplication($application)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to manage this application',
                ], self::RESPONSE_CODES['FORBIDDEN']);
            }

            // Check for duplicate recommendation request
            $existing = RecommendationLetter::where('application_id', $validated['application_id'])
                ->where('recommender_email', $validated['recommender_email'])
                ->whereNotIn('status', ['rejected', 'expired'])
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'A recommendation request already exists for this recommender',
                    'data' => new RecommendationResource($existing),
                ], self::RESPONSE_CODES['CONFLICT']);
            }

            // Check recommendation limit
            $currentCount = RecommendationLetter::where('application_id', $validated['application_id'])
                ->whereNotIn('status', ['rejected', 'expired', 'waived'])
                ->count();

            $maxRecommendations = $this->getMaxRecommendations($application);
            
            if ($currentCount >= $maxRecommendations) {
                return response()->json([
                    'success' => false,
                    'message' => "Maximum number of recommendations ({$maxRecommendations}) already requested",
                ], self::RESPONSE_CODES['BAD_REQUEST']);
            }

            DB::beginTransaction();

            try {
                // Create recommendation request
                $recommendation = $this->recommendationService->requestRecommendation(
                    $validated['application_id'],
                    $validated
                );

                // Send invitation if requested
                if ($validated['send_immediately'] ?? true) {
                    $this->recommendationService->sendRecommenderInvite($recommendation->id);
                    $recommendation->invitation_sent_at = now();
                    $recommendation->save();
                }

                DB::commit();

                // Log the action
                $this->logAction('recommendation_requested', $recommendation, Auth::user());

                return response()->json([
                    'success' => true,
                    'message' => 'Recommendation request created successfully',
                    'data' => new RecommendationResource($recommendation),
                ], self::RESPONSE_CODES['CREATED']);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            Log::error('Failed to request recommendation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to request recommendation',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Submit a recommendation letter (via token).
     *
     * @param Request $request
     * @param string $token
     * @return JsonResponse
     */
    public function submit(Request $request, string $token): JsonResponse
    {
        try {
            // Validate the token
            $recommendation = $this->validateToken($token);
            
            if (!$recommendation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token',
                ], self::RESPONSE_CODES['UNAUTHORIZED']);
            }

            // Check if already submitted
            if ($recommendation->status === 'submitted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Recommendation has already been submitted',
                ], self::RESPONSE_CODES['CONFLICT']);
            }

            $validator = Validator::make($request->all(), [
                'letter_content' => 'required_without:letter_file|string|min:500',
                'letter_file' => 'required_without:letter_content|file|mimes:' . implode(',', self::ALLOWED_FILE_TYPES) . '|max:' . (self::MAX_FILE_SIZE * 1024),
                'rating_academic' => 'nullable|integer|min:1|max:5',
                'rating_character' => 'nullable|integer|min:1|max:5',
                'rating_potential' => 'nullable|integer|min:1|max:5',
                'rating_overall' => 'required|integer|min:1|max:5',
                'strengths' => 'nullable|string|max:1000',
                'weaknesses' => 'nullable|string|max:1000',
                'additional_comments' => 'nullable|string|max:2000',
                'recommender_signature' => 'required|string|max:200',
                'consent_to_share' => 'required|boolean|accepted',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], self::RESPONSE_CODES['VALIDATION_ERROR']);
            }

            $validated = $validator->validated();

            DB::beginTransaction();

            try {
                // Handle file upload if provided
                $filePath = null;
                if ($request->hasFile('letter_file')) {
                    $file = $request->file('letter_file');
                    $fileName = 'recommendation_' . $recommendation->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs('recommendations/' . $recommendation->application_id, $fileName);
                    
                    // Create document record
                    ApplicationDocument::create([
                        'application_id' => $recommendation->application_id,
                        'document_type' => 'recommendation_letter',
                        'document_name' => 'Recommendation from ' . $recommendation->recommender_name,
                        'original_filename' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'status' => 'uploaded',
                        'recommender_name' => $recommendation->recommender_name,
                        'recommender_email' => $recommendation->recommender_email,
                        'recommender_title' => $recommendation->recommender_title,
                        'recommender_institution' => $recommendation->recommender_institution,
                    ]);
                }

                // Submit the recommendation
                $result = $this->recommendationService->submitRecommendation($token, [
                    'letter_content' => $validated['letter_content'] ?? null,
                    'letter_file_path' => $filePath,
                    'ratings' => [
                        'academic' => $validated['rating_academic'] ?? null,
                        'character' => $validated['rating_character'] ?? null,
                        'potential' => $validated['rating_potential'] ?? null,
                        'overall' => $validated['rating_overall'],
                    ],
                    'strengths' => $validated['strengths'] ?? null,
                    'weaknesses' => $validated['weaknesses'] ?? null,
                    'additional_comments' => $validated['additional_comments'] ?? null,
                    'recommender_signature' => $validated['recommender_signature'],
                    'consent_to_share' => $validated['consent_to_share'],
                    'submitted_at' => now(),
                    'submitted_ip' => $request->ip(),
                ]);

                DB::commit();

                // Send confirmation to recommender
                $this->notificationService->sendRecommendationConfirmation($recommendation);

                // Notify applicant
                $this->notificationService->notifyApplicantOfRecommendation($recommendation);

                // Log the submission
                $this->logAction('recommendation_submitted', $recommendation, null);

                return response()->json([
                    'success' => true,
                    'message' => 'Recommendation submitted successfully',
                    'data' => [
                        'recommendation_id' => $recommendation->id,
                        'applicant_name' => $recommendation->application->first_name . ' ' . $recommendation->application->last_name,
                        'submitted_at' => now()->toIso8601String(),
                    ],
                ], self::RESPONSE_CODES['SUCCESS']);

            } catch (Exception $e) {
                DB::rollBack();
                
                // Clean up uploaded file if exists
                if ($filePath && Storage::exists($filePath)) {
                    Storage::delete($filePath);
                }
                
                throw $e;
            }

        } catch (Exception $e) {
            Log::error('Failed to submit recommendation', [
                'token' => substr($token, 0, 10) . '...',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit recommendation',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Send reminder to recommender.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function remind($id): JsonResponse
    {
        try {
            $recommendation = RecommendationLetter::findOrFail($id);

            // Check authorization
            if (!$this->canManageRecommendation($recommendation)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to manage this recommendation',
                ], self::RESPONSE_CODES['FORBIDDEN']);
            }

            // Check if already submitted
            if (in_array($recommendation->status, ['submitted', 'approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recommendation has already been submitted',
                ], self::RESPONSE_CODES['BAD_REQUEST']);
            }

            // Check reminder limit
            if ($recommendation->reminder_count >= self::MAX_REMINDER_ATTEMPTS) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum number of reminders already sent',
                ], self::RESPONSE_CODES['TOO_MANY_REQUESTS']);
            }

            // Check last reminder time (minimum 48 hours between reminders)
            if ($recommendation->last_reminder_at && $recommendation->last_reminder_at->diffInHours(now()) < 48) {
                $nextReminderTime = $recommendation->last_reminder_at->addHours(48);
                return response()->json([
                    'success' => false,
                    'message' => 'Please wait before sending another reminder',
                    'next_reminder_available' => $nextReminderTime->toIso8601String(),
                ], self::RESPONSE_CODES['TOO_MANY_REQUESTS']);
            }

            // Send reminder
            $result = $this->recommendationService->remindRecommender($id);

            if ($result) {
                // Update reminder tracking
                $recommendation->reminder_count++;
                $recommendation->last_reminder_at = now();
                $recommendation->save();

                // Log the action
                $this->logAction('reminder_sent', $recommendation, Auth::user());

                return response()->json([
                    'success' => true,
                    'message' => 'Reminder sent successfully',
                    'data' => [
                        'reminder_count' => $recommendation->reminder_count,
                        'max_reminders' => self::MAX_REMINDER_ATTEMPTS,
                        'last_reminder_at' => $recommendation->last_reminder_at->toIso8601String(),
                    ],
                ], self::RESPONSE_CODES['SUCCESS']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to send reminder',
            ], self::RESPONSE_CODES['SERVER_ERROR']);

        } catch (Exception $e) {
            Log::error('Failed to send reminder', [
                'recommendation_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send reminder',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Get recommendation status for an application.
     *
     * @param int $applicationId
     * @return JsonResponse
     */
    public function status($applicationId): JsonResponse
    {
        try {
            $application = AdmissionApplication::findOrFail($applicationId);

            // Check authorization
            if (!$this->canManageApplication($application)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to view this application',
                ], self::RESPONSE_CODES['FORBIDDEN']);
            }

            // Get all recommendations for the application
            $recommendations = RecommendationLetter::where('application_id', $applicationId)
                ->with(['application:id,first_name,last_name,application_number'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Calculate statistics
            $stats = [
                'total_requested' => $recommendations->count(),
                'submitted' => $recommendations->where('status', 'submitted')->count(),
                'pending' => $recommendations->where('status', 'pending')->count(),
                'approved' => $recommendations->where('status', 'approved')->count(),
                'waived' => $recommendations->where('status', 'waived')->count(),
                'required' => $this->getRequiredRecommendations($application),
                'maximum' => $this->getMaxRecommendations($application),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'recommendations' => new RecommendationCollection($recommendations),
                    'statistics' => $stats,
                    'is_complete' => $stats['submitted'] >= $stats['required'],
                ],
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to get recommendation status', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get recommendation status',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Waive recommendation requirement.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function waive(Request $request, $id): JsonResponse
    {
        try {
            $recommendation = RecommendationLetter::findOrFail($id);

            // Check authorization (only admins can waive)
            if (!Auth::user()->hasRole(['admin', 'admissions_director', 'registrar'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to waive recommendations',
                ], self::RESPONSE_CODES['FORBIDDEN']);
            }

            $validator = Validator::make($request->all(), [
                'waiver_reason' => 'required|string|max:500',
                'approved_by_notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], self::RESPONSE_CODES['VALIDATION_ERROR']);
            }

            $validated = $validator->validated();

            // Waive the recommendation
            $result = $this->recommendationService->waiveRecommendation(
                $recommendation->application_id,
                $validated['waiver_reason']
            );

            if ($result) {
                // Update recommendation record
                $recommendation->status = 'waived';
                $recommendation->waiver_reason = $validated['waiver_reason'];
                $recommendation->waived_by = Auth::id();
                $recommendation->waived_at = now();
                $recommendation->approved_by_notes = $validated['approved_by_notes'] ?? null;
                $recommendation->save();

                // Log the action
                $this->logAction('recommendation_waived', $recommendation, Auth::user());

                return response()->json([
                    'success' => true,
                    'message' => 'Recommendation requirement waived successfully',
                    'data' => new RecommendationResource($recommendation),
                ], self::RESPONSE_CODES['SUCCESS']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to waive recommendation',
            ], self::RESPONSE_CODES['SERVER_ERROR']);

        } catch (Exception $e) {
            Log::error('Failed to waive recommendation', [
                'recommendation_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to waive recommendation',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Resend invitation to recommender.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function resendInvitation($id): JsonResponse
    {
        try {
            $recommendation = RecommendationLetter::findOrFail($id);

            // Check authorization
            if (!$this->canManageRecommendation($recommendation)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], self::RESPONSE_CODES['FORBIDDEN']);
            }

            // Generate new token if expired
            if ($recommendation->token_expires_at < now()) {
                $recommendation->token = $this->generateSecureToken();
                $recommendation->token_expires_at = now()->addDays(self::TOKEN_EXPIRY_DAYS);
                $recommendation->save();
            }

            // Resend invitation
            $result = $this->recommendationService->sendRecommenderInvite($recommendation->id);

            if ($result) {
                $recommendation->invitation_sent_at = now();
                $recommendation->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Invitation resent successfully',
                    'data' => [
                        'sent_at' => now()->toIso8601String(),
                        'expires_at' => $recommendation->token_expires_at->toIso8601String(),
                    ],
                ], self::RESPONSE_CODES['SUCCESS']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend invitation',
            ], self::RESPONSE_CODES['SERVER_ERROR']);

        } catch (Exception $e) {
            Log::error('Failed to resend invitation', [
                'recommendation_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend invitation',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Get recommendation by token (for recommenders).
     *
     * @param string $token
     * @return JsonResponse
     */
    public function getByToken(string $token): JsonResponse
    {
        try {
            $recommendation = $this->validateToken($token);
            
            if (!$recommendation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token',
                ], self::RESPONSE_CODES['UNAUTHORIZED']);
            }

            // Load application details
            $recommendation->load(['application:id,first_name,last_name,application_number,program_id']);

            return response()->json([
                'success' => true,
                'data' => [
                    'recommendation_id' => $recommendation->id,
                    'applicant_name' => $recommendation->application->first_name . ' ' . $recommendation->application->last_name,
                    'application_number' => $recommendation->application->application_number,
                    'program' => $recommendation->application->program->name ?? 'N/A',
                    'recommendation_type' => $recommendation->recommendation_type,
                    'deadline' => $recommendation->deadline?->toIso8601String(),
                    'status' => $recommendation->status,
                    'custom_message' => $recommendation->custom_message,
                    'token_expires_at' => $recommendation->token_expires_at->toIso8601String(),
                ],
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to get recommendation by token', [
                'token' => substr($token, 0, 10) . '...',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get recommendation details',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Decline to provide recommendation (via token).
     *
     * @param Request $request
     * @param string $token
     * @return JsonResponse
     */
    public function declineByToken(Request $request, string $token): JsonResponse
    {
        try {
            $recommendation = $this->validateToken($token);
            
            if (!$recommendation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired token',
                ], self::RESPONSE_CODES['UNAUTHORIZED']);
            }

            $validator = Validator::make($request->all(), [
                'decline_reason' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], self::RESPONSE_CODES['VALIDATION_ERROR']);
            }

            $validated = $validator->validated();

            // Update recommendation status
            $recommendation->status = 'rejected';
            $recommendation->decline_reason = $validated['decline_reason'];
            $recommendation->declined_at = now();
            $recommendation->save();

            // Notify applicant and admissions office
            $this->notificationService->notifyRecommendationDeclined($recommendation);

            // Log the action
            $this->logAction('recommendation_declined', $recommendation, null);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your response. The applicant has been notified.',
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to decline recommendation', [
                'token' => substr($token, 0, 10) . '...',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process your response',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Delete recommendation request.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function delete($id): JsonResponse
    {
        try {
            $recommendation = RecommendationLetter::findOrFail($id);

            // Check authorization
            if (!$this->canManageRecommendation($recommendation)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], self::RESPONSE_CODES['FORBIDDEN']);
            }

            // Can only delete pending recommendations
            if (!in_array($recommendation->status, ['pending', 'rejected'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete a recommendation that has been submitted',
                ], self::RESPONSE_CODES['BAD_REQUEST']);
            }

            // Delete the recommendation
            $recommendation->delete();

            // Log the action
            $this->logAction('recommendation_deleted', $recommendation, Auth::user());

            return response()->json([
                'success' => true,
                'message' => 'Recommendation request deleted successfully',
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to delete recommendation', [
                'recommendation_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete recommendation',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Get all recommendations (admin).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Admin only
            if (!Auth::user()->hasRole(['admin', 'admissions_officer'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], self::RESPONSE_CODES['FORBIDDEN']);
            }

            $perPage = $request->input('per_page', 20);
            $status = $request->input('status');
            $applicationId = $request->input('application_id');
            $search = $request->input('search');

            $query = RecommendationLetter::with([
                'application:id,application_number,first_name,last_name',
                'waivedBy:id,name',
            ]);

            if ($status) {
                $query->where('status', $status);
            }

            if ($applicationId) {
                $query->where('application_id', $applicationId);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('recommender_name', 'like', "%{$search}%")
                      ->orWhere('recommender_email', 'like', "%{$search}%")
                      ->orWhere('recommender_institution', 'like', "%{$search}%");
                });
            }

            $recommendations = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => new RecommendationCollection($recommendations),
                'meta' => [
                    'total' => $recommendations->total(),
                    'per_page' => $recommendations->perPage(),
                    'current_page' => $recommendations->currentPage(),
                    'last_page' => $recommendations->lastPage(),
                ],
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to get recommendations', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get recommendations',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Get recommendation statistics.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            // Admin only
            if (!Auth::user()->hasRole(['admin', 'admissions_officer'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], self::RESPONSE_CODES['FORBIDDEN']);
            }

            $termId = $request->input('term_id');
            $programId = $request->input('program_id');

            $query = RecommendationLetter::query();

            if ($termId) {
                $query->whereHas('application', function ($q) use ($termId) {
                    $q->where('term_id', $termId);
                });
            }

            if ($programId) {
                $query->whereHas('application', function ($q) use ($programId) {
                    $q->where('program_id', $programId);
                });
            }

            $stats = [
                'total_requested' => $query->count(),
                'submitted' => (clone $query)->where('status', 'submitted')->count(),
                'pending' => (clone $query)->where('status', 'pending')->count(),
                'approved' => (clone $query)->where('status', 'approved')->count(),
                'rejected' => (clone $query)->where('status', 'rejected')->count(),
                'waived' => (clone $query)->where('status', 'waived')->count(),
                'average_submission_time' => $this->calculateAverageSubmissionTime($query),
                'response_rate' => $this->calculateResponseRate($query),
                'top_recommenders' => $this->getTopRecommenders($query),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to get recommendation statistics', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Helper: Validate token.
     */
    private function validateToken(string $token): ?RecommendationLetter
    {
        try {
            $recommendation = RecommendationLetter::where('token', $token)
                ->where('token_expires_at', '>', now())
                ->first();

            return $recommendation;
        } catch (Exception $e) {
            Log::error('Token validation failed', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Helper: Check if user can manage application.
     */
    private function canManageApplication(AdmissionApplication $application): bool
    {
        $user = Auth::user();
        
        // Admin can manage all
        if ($user->hasRole(['admin', 'admissions_officer'])) {
            return true;
        }
        
        // Check if application belongs to user
        return $application->user_id === $user->id || 
               $application->email === $user->email;
    }

    /**
     * Helper: Check if user can manage recommendation.
     */
    private function canManageRecommendation(RecommendationLetter $recommendation): bool
    {
        $user = Auth::user();
        
        // Admin can manage all
        if ($user->hasRole(['admin', 'admissions_officer'])) {
            return true;
        }
        
        // Check if recommendation's application belongs to user
        $application = $recommendation->application;
        return $application->user_id === $user->id || 
               $application->email === $user->email;
    }

    /**
     * Helper: Get required recommendations count.
     */
    private function getRequiredRecommendations(AdmissionApplication $application): int
    {
        return match($application->application_type) {
            'freshman' => 2,
            'transfer' => 1,
            'graduate' => 3,
            'international' => 2,
            default => 2,
        };
    }

    /**
     * Helper: Get maximum recommendations allowed.
     */
    private function getMaxRecommendations(AdmissionApplication $application): int
    {
        return match($application->application_type) {
            'freshman' => 3,
            'transfer' => 2,
            'graduate' => 4,
            'international' => 3,
            default => 3,
        };
    }

    /**
     * Helper: Generate secure token.
     */
    private function generateSecureToken(): string
    {
        return Str::random(64);
    }

    /**
     * Helper: Calculate average submission time.
     */
    private function calculateAverageSubmissionTime($query): ?float
    {
        $submitted = (clone $query)->where('status', 'submitted')
            ->whereNotNull('submitted_at')
            ->get();

        if ($submitted->isEmpty()) {
            return null;
        }

        $totalDays = 0;
        foreach ($submitted as $rec) {
            $totalDays += $rec->created_at->diffInDays($rec->submitted_at);
        }

        return round($totalDays / $submitted->count(), 1);
    }

    /**
     * Helper: Calculate response rate.
     */
    private function calculateResponseRate($query): float
    {
        $total = (clone $query)->count();
        if ($total === 0) {
            return 0;
        }

        $responded = (clone $query)->whereIn('status', ['submitted', 'approved', 'rejected'])->count();
        
        return round(($responded / $total) * 100, 2);
    }

    /**
     * Helper: Get top recommenders.
     */
    private function getTopRecommenders($query): array
    {
        return (clone $query)->where('status', 'submitted')
            ->selectRaw('recommender_email, recommender_name, COUNT(*) as count')
            ->groupBy('recommender_email', 'recommender_name')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->toArray();
    }

    /**
     * Helper: Log action.
     */
    private function logAction(string $action, $recommendation, $user): void
    {
        Log::info('Recommendation action performed', [
            'action' => $action,
            'recommendation_id' => $recommendation->id,
            'application_id' => $recommendation->application_id,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}