<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApplicationService;
use App\Services\DocumentVerificationService;
use App\Services\ApplicationNotificationService;
use App\Services\AdmissionsAnalyticsService;
use App\Models\AdmissionApplication;
use App\Models\ApplicationDocument;
use App\Models\ApplicationChecklistItem;
use App\Models\ApplicationNote;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Http\Requests\ApplicationSubmitRequest;
use App\Http\Requests\DocumentUploadRequest;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\ApplicationCollection;
use App\Http\Resources\DocumentResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Exception;

class ApplicationApiController extends Controller
{
    protected $applicationService;
    protected $documentService;
    protected $notificationService;
    protected $analyticsService;

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
        'VALIDATION_ERROR' => 422,
        'SERVER_ERROR' => 500,
    ];

    /**
     * Maximum file upload size (MB)
     */
    private const MAX_FILE_SIZE = 10;

    /**
     * Allowed file types for documents
     */
    private const ALLOWED_FILE_TYPES = [
        'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(
        ApplicationService $applicationService,
        DocumentVerificationService $documentService,
        ApplicationNotificationService $notificationService,
        AdmissionsAnalyticsService $analyticsService
    ) {
        $this->applicationService = $applicationService;
        $this->documentService = $documentService;
        $this->notificationService = $notificationService;
        $this->analyticsService = $analyticsService;
        
        // API authentication middleware
        $this->middleware('auth:sanctum')->except(['checkStatus', 'validateField']);
        
        // Rate limiting
        $this->middleware('throttle:api');
    }

    /**
     * Store new application.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate basic application data
            $validated = $this->validateApplicationData($request);

            // Check for duplicate application
            if ($this->checkDuplicateApplication($validated['email'], $validated['term_id'], $validated['program_id'])) {
                return $this->errorResponse(
                    'Duplicate application found for this term and program.',
                    self::RESPONSE_CODES['BAD_REQUEST']
                );
            }

            DB::beginTransaction();

            // Start new application
            $application = $this->applicationService->startNewApplication($validated);

            // Create checklist items
            $this->createChecklistItems($application);

            // Send welcome email
            $this->notificationService->sendApplicationStarted($application->id);

            DB::commit();

            // Log analytics event
            $this->logAnalyticsEvent('application_started', $application);

            return $this->successResponse(
                new ApplicationResource($application),
                'Application created successfully.',
                self::RESPONSE_CODES['CREATED']
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->validationErrorResponse($e->errors());
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create application via API', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return $this->errorResponse(
                'Failed to create application. Please try again.',
                self::RESPONSE_CODES['SERVER_ERROR']
            );
        }
    }

    /**
     * Update application data.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $application = AdmissionApplication::where('id', $id)
                ->where(function ($query) {
                    $query->where('user_id', Auth::id())
                          ->orWhere('email', Auth::user()->email);
                })
                ->firstOrFail();

            // Check if application can be edited
            if (!in_array($application->status, ['draft', 'documents_pending'])) {
                return $this->errorResponse(
                    'Application cannot be edited in current status.',
                    self::RESPONSE_CODES['FORBIDDEN']
                );
            }

            // Validate update data
            $validated = $this->validateUpdateData($request, $application);

            DB::beginTransaction();

            // Save as draft
            $application = $this->applicationService->saveAsDraft($id, $validated);

            // Update completion percentage
            $application->completion_percentage = $this->applicationService->calculateCompletionPercentage($id);
            $application->save();

            // Update checklist
            $this->updateChecklistItems($application, $request->input('section'));

            DB::commit();

            // Clear cache
            Cache::forget("application_{$id}");

            return $this->successResponse(
                new ApplicationResource($application->fresh()),
                'Application updated successfully.'
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                'Application not found.',
                self::RESPONSE_CODES['NOT_FOUND']
            );
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->validationErrorResponse($e->errors());
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update application via API', [
                'error' => $e->getMessage(),
                'application_id' => $id,
            ]);

            return $this->errorResponse(
                'Failed to update application.',
                self::RESPONSE_CODES['SERVER_ERROR']
            );
        }
    }

    /**
     * Upload document for application.
     *
     * @param DocumentUploadRequest $request
     * @return JsonResponse
     */
    public function uploadDocument(DocumentUploadRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            $application = AdmissionApplication::where('id', $validated['application_id'])
                ->where(function ($query) {
                    $query->where('user_id', Auth::id())
                          ->orWhere('email', Auth::user()->email);
                })
                ->firstOrFail();

            // Check file size
            if ($request->file('document')->getSize() > self::MAX_FILE_SIZE * 1024 * 1024) {
                return $this->errorResponse(
                    'File size exceeds maximum allowed size of ' . self::MAX_FILE_SIZE . 'MB.',
                    self::RESPONSE_CODES['BAD_REQUEST']
                );
            }

            DB::beginTransaction();

            // Upload document
            $document = $this->documentService->uploadDocument(
                $application->id,
                $request->file('document'),
                $validated['document_type']
            );

            // Update checklist
            $this->updateDocumentChecklist($application, $validated['document_type']);

            DB::commit();

            // Log analytics
            $this->logAnalyticsEvent('document_uploaded', $application, [
                'document_type' => $validated['document_type']
            ]);

            return $this->successResponse(
                new DocumentResource($document),
                'Document uploaded successfully.',
                self::RESPONSE_CODES['CREATED']
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                'Application not found.',
                self::RESPONSE_CODES['NOT_FOUND']
            );
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to upload document via API', [
                'error' => $e->getMessage(),
                'application_id' => $request->input('application_id'),
            ]);

            return $this->errorResponse(
                'Failed to upload document.',
                self::RESPONSE_CODES['SERVER_ERROR']
            );
        }
    }

    /**
     * Remove document from application.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function removeDocument($id): JsonResponse
    {
        try {
            $document = ApplicationDocument::findOrFail($id);
            
            // Check ownership
            $application = $document->application;
            if ($application->user_id !== Auth::id() && $application->email !== Auth::user()->email) {
                return $this->errorResponse(
                    'Unauthorized to remove this document.',
                    self::RESPONSE_CODES['FORBIDDEN']
                );
            }

            // Check if document can be removed
            if ($document->is_verified) {
                return $this->errorResponse(
                    'Verified documents cannot be removed.',
                    self::RESPONSE_CODES['FORBIDDEN']
                );
            }

            DB::beginTransaction();

            // Delete file from storage
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }

            // Delete document record
            $documentType = $document->document_type;
            $document->delete();

            // Update checklist
            $this->updateDocumentChecklist($application, $documentType, false);

            DB::commit();

            return $this->successResponse(
                null,
                'Document removed successfully.',
                self::RESPONSE_CODES['NO_CONTENT']
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                'Document not found.',
                self::RESPONSE_CODES['NOT_FOUND']
            );
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to remove document via API', [
                'error' => $e->getMessage(),
                'document_id' => $id,
            ]);

            return $this->errorResponse(
                'Failed to remove document.',
                self::RESPONSE_CODES['SERVER_ERROR']
            );
        }
    }

    /**
     * Check application status by UUID.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function checkStatus($uuid): JsonResponse
    {
        try {
            $application = AdmissionApplication::where('application_uuid', $uuid)
                ->with(['program', 'term', 'documents', 'checklistItems'])
                ->firstOrFail();

            // Prepare status information
            $statusInfo = [
                'application_number' => $application->application_number,
                'status' => $application->status,
                'status_message' => $this->getStatusMessage($application->status),
                'completion_percentage' => $application->completionPercentage(),
                'submitted_at' => $application->submitted_at,
                'decision' => $application->decision,
                'decision_date' => $application->decision_date,
                'enrollment_status' => [
                    'confirmed' => $application->enrollment_confirmed,
                    'deadline' => $application->enrollment_deadline,
                    'deposit_paid' => $application->enrollment_deposit_paid,
                ],
                'next_steps' => $this->getNextSteps($application),
                'checklist' => $this->getChecklistStatus($application),
            ];

            return $this->successResponse($statusInfo, 'Application status retrieved successfully.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                'Application not found.',
                self::RESPONSE_CODES['NOT_FOUND']
            );
            
        } catch (Exception $e) {
            Log::error('Failed to check application status via API', [
                'error' => $e->getMessage(),
                'uuid' => $uuid,
            ]);

            return $this->errorResponse(
                'Failed to retrieve application status.',
                self::RESPONSE_CODES['SERVER_ERROR']
            );
        }
    }

    /**
     * Submit application for review.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function submitApplication(Request $request, $id): JsonResponse
    {
        try {
            $application = AdmissionApplication::where('id', $id)
                ->where(function ($query) {
                    $query->where('user_id', Auth::id())
                          ->orWhere('email', Auth::user()->email);
                })
                ->firstOrFail();

            // Validate application can be submitted
            if (!$application->canSubmit()) {
                $errors = $this->applicationService->validateApplication($id);
                return $this->errorResponse(
                    'Application is not ready for submission.',
                    self::RESPONSE_CODES['BAD_REQUEST'],
                    ['validation_errors' => $errors]
                );
            }

            // Validate submission data
            $validated = $request->validate([
                'signature' => 'required|string',
                'certify_information' => 'required|boolean|accepted',
                'agree_terms' => 'required|boolean|accepted',
                'submission_ip' => 'nullable|ip',
            ]);

            DB::beginTransaction();

            // Submit application
            $application = $this->applicationService->submitApplication($id);

            // Store submission metadata
            $application->submission_signature = $validated['signature'];
            $application->submission_ip = $validated['submission_ip'] ?? $request->ip();
            $application->submission_user_agent = $request->userAgent();
            $application->save();

            // Send confirmation email
            $this->notificationService->sendApplicationSubmitted($application->id);

            // Log submission
            ApplicationNote::create([
                'application_id' => $application->id,
                'note' => 'Application submitted via API',
                'type' => 'system',
                'metadata' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            ]);

            DB::commit();

            // Log analytics
            $this->logAnalyticsEvent('application_submitted', $application);

            // Clear cache
            Cache::forget("application_{$id}");

            return $this->successResponse(
                new ApplicationResource($application->fresh()),
                'Application submitted successfully.'
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                'Application not found.',
                self::RESPONSE_CODES['NOT_FOUND']
            );
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit application via API', [
                'error' => $e->getMessage(),
                'application_id' => $id,
            ]);

            return $this->errorResponse(
                'Failed to submit application.',
                self::RESPONSE_CODES['SERVER_ERROR']
            );
        }
    }

    /**
     * Validate specific field in real-time.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateField(Request $request): JsonResponse
    {
        try {
            $field = $request->input('field');
            $value = $request->input('value');
            $context = $request->input('context', []);

            // Define validation rules for each field
            $rules = $this->getFieldValidationRules($field, $context);

            if (empty($rules)) {
                return $this->errorResponse(
                    'Unknown field for validation.',
                    self::RESPONSE_CODES['BAD_REQUEST']
                );
            }

            $validator = Validator::make(
                [$field => $value],
                [$field => $rules]
            );

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Additional custom validations
            $customValidation = $this->performCustomValidation($field, $value, $context);
            if (!$customValidation['valid']) {
                return $this->errorResponse(
                    $customValidation['message'],
                    self::RESPONSE_CODES['VALIDATION_ERROR']
                );
            }

            return $this->successResponse(
                ['valid' => true],
                'Field is valid.'
            );

        } catch (Exception $e) {
            Log::error('Failed to validate field via API', [
                'error' => $e->getMessage(),
                'field' => $request->input('field'),
            ]);

            return $this->errorResponse(
                'Failed to validate field.',
                self::RESPONSE_CODES['SERVER_ERROR']
            );
        }
    }

    /**
     * Get application form data.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getApplication($id): JsonResponse
    {
        try {
            $application = AdmissionApplication::where('id', $id)
                ->where(function ($query) {
                    $query->where('user_id', Auth::id())
                          ->orWhere('email', Auth::user()->email);
                })
                ->with(['program', 'term', 'documents', 'checklistItems', 'fees'])
                ->firstOrFail();

            return $this->successResponse(
                new ApplicationResource($application),
                'Application retrieved successfully.'
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                'Application not found.',
                self::RESPONSE_CODES['NOT_FOUND']
            );
            
        } catch (Exception $e) {
            Log::error('Failed to retrieve application via API', [
                'error' => $e->getMessage(),
                'application_id' => $id,
            ]);

            return $this->errorResponse(
                'Failed to retrieve application.',
                self::RESPONSE_CODES['SERVER_ERROR']
            );
        }
    }

    /**
     * Get list of user's applications.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function myApplications(Request $request): JsonResponse
    {
        try {
            $query = AdmissionApplication::where(function ($query) {
                    $query->where('user_id', Auth::id())
                          ->orWhere('email', Auth::user()->email);
                })
                ->with(['program', 'term']);

            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('term_id')) {
                $query->where('term_id', $request->input('term_id'));
            }

            // Sort by created date
            $query->orderBy('created_at', 'desc');

            // Paginate results
            $applications = $query->paginate($request->input('per_page', 10));

            return $this->successResponse(
                new ApplicationCollection($applications),
                'Applications retrieved successfully.'
            );

        } catch (Exception $e) {
            Log::error('Failed to retrieve user applications via API', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return $this->errorResponse(
                'Failed to retrieve applications.',
                self::RESPONSE_CODES['SERVER_ERROR']
            );
        }
    }

    /**
     * Withdraw application.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function withdrawApplication(Request $request, $id): JsonResponse
    {
        try {
            $application = AdmissionApplication::where('id', $id)
                ->where(function ($query) {
                    $query->where('user_id', Auth::id())
                          ->orWhere('email', Auth::user()->email);
                })
                ->firstOrFail();

            // Check if application can be withdrawn
            if (in_array($application->status, ['admitted', 'enrolled', 'withdrawn'])) {
                return $this->errorResponse(
                    'Application cannot be withdrawn in current status.',
                    self::RESPONSE_CODES['FORBIDDEN']
                );
            }

            $validated = $request->validate([
                'reason' => 'required|string|max:1000',
            ]);

            DB::beginTransaction();

            // Withdraw application
            $application = $this->applicationService->withdrawApplication($id, $validated['reason']);

            // Send confirmation
            $this->notificationService->sendApplicationWithdrawn($application->id);

            DB::commit();

            return $this->successResponse(
                new ApplicationResource($application),
                'Application withdrawn successfully.'
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                'Application not found.',
                self::RESPONSE_CODES['NOT_FOUND']
            );
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to withdraw application via API', [
                'error' => $e->getMessage(),
                'application_id' => $id,
            ]);

            return $this->errorResponse(
                'Failed to withdraw application.',
                self::RESPONSE_CODES['SERVER_ERROR']
            );
        }
    }

    /**
     * Get available programs for application.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAvailablePrograms(Request $request): JsonResponse
    {
        try {
            $termId = $request->input('term_id');
            $applicationType = $request->input('application_type', 'freshman');

            $programs = AcademicProgram::where('is_active', true)
                ->where('accepts_applications', true)
                ->when($applicationType, function ($query) use ($applicationType) {
                    return $query->whereJsonContains('application_types', $applicationType);
                })
                ->select('id', 'name', 'code', 'degree_type', 'duration_years', 'description')
                ->orderBy('name')
                ->get();

            return $this->successResponse($programs, 'Programs retrieved successfully.');

        } catch (Exception $e) {
            Log::error('Failed to retrieve programs via API', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Failed to retrieve programs.',
                self::RESPONSE_CODES['SERVER_ERROR']
            );
        }
    }

    /**
     * Get available terms for application.
     *
     * @return JsonResponse
     */
    public function getAvailableTerms(): JsonResponse
    {
        try {
            $terms = AcademicTerm::where('accepts_applications', true)
                ->where('application_deadline', '>=', now())
                ->select('id', 'name', 'term_code', 'start_date', 'application_deadline')
                ->orderBy('start_date')
                ->get();

            return $this->successResponse($terms, 'Terms retrieved successfully.');

        } catch (Exception $e) {
            Log::error('Failed to retrieve terms via API', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Failed to retrieve terms.',
                self::RESPONSE_CODES['SERVER_ERROR']
            );
        }
    }

    /**
     * Helper Methods
     */

    /**
     * Validate application data.
     */
    private function validateApplicationData(Request $request): array
    {
        return $request->validate([
            // Personal Information
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'nationality' => 'required|string|max:100',
            
            // Contact Information
            'email' => 'required|email|max:255',
            'phone_primary' => 'required|string|max:20',
            'current_address' => 'required|string',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            
            // Application Details
            'application_type' => 'required|in:freshman,transfer,graduate,international,readmission',
            'term_id' => 'required|exists:academic_terms,id',
            'program_id' => 'required|exists:academic_programs,id',
            'entry_year' => 'required|integer|min:' . date('Y'),
        ]);
    }

    /**
     * Validate update data.
     */
    private function validateUpdateData(Request $request, AdmissionApplication $application): array
    {
        $rules = [];
        
        // Add validation rules based on section being updated
        $section = $request->input('section');
        
        switch ($section) {
            case 'personal_info':
                $rules = [
                    'first_name' => 'string|max:100',
                    'last_name' => 'string|max:100',
                    'date_of_birth' => 'date|before:today',
                    'nationality' => 'string|max:100',
                ];
                break;
                
            case 'contact_info':
                $rules = [
                    'email' => 'email|max:255',
                    'phone_primary' => 'string|max:20',
                    'current_address' => 'string',
                    'city' => 'string|max:100',
                ];
                break;
                
            case 'education':
                $rules = [
                    'previous_institution' => 'string|max:255',
                    'previous_gpa' => 'numeric|min:0|max:5',
                    'graduation_date' => 'date',
                ];
                break;
                
            case 'test_scores':
                $rules = [
                    'test_scores' => 'array',
                    'test_scores.*.type' => 'string',
                    'test_scores.*.score' => 'numeric',
                    'test_scores.*.date' => 'date',
                ];
                break;
        }
        
        return $request->validate($rules);
    }

    /**
     * Check for duplicate application.
     */
    private function checkDuplicateApplication(string $email, int $termId, int $programId): bool
    {
        return AdmissionApplication::where('email', $email)
            ->where('term_id', $termId)
            ->where('program_id', $programId)
            ->whereNotIn('status', ['withdrawn', 'denied'])
            ->exists();
    }

    /**
     * Create checklist items for new application.
     */
    private function createChecklistItems(AdmissionApplication $application): void
    {
        $checklistItems = [
            ['item_name' => 'Personal Information', 'item_type' => 'form', 'sort_order' => 1],
            ['item_name' => 'Contact Information', 'item_type' => 'form', 'sort_order' => 2],
            ['item_name' => 'Educational Background', 'item_type' => 'form', 'sort_order' => 3],
            ['item_name' => 'Test Scores', 'item_type' => 'form', 'sort_order' => 4],
            ['item_name' => 'Personal Statement', 'item_type' => 'form', 'sort_order' => 5],
            ['item_name' => 'Transcript', 'item_type' => 'document', 'sort_order' => 6],
            ['item_name' => 'Recommendation Letters', 'item_type' => 'document', 'sort_order' => 7],
            ['item_name' => 'Application Fee', 'item_type' => 'fee', 'sort_order' => 8],
        ];

        foreach ($checklistItems as $item) {
            ApplicationChecklistItem::create([
                'application_id' => $application->id,
                'item_name' => $item['item_name'],
                'item_type' => $item['item_type'],
                'sort_order' => $item['sort_order'],
                'is_required' => true,
                'is_completed' => false,
            ]);
        }
    }

    /**
     * Update checklist items.
     */
    private function updateChecklistItems(AdmissionApplication $application, ?string $section): void
    {
        if (!$section) {
            return;
        }

        $itemName = match($section) {
            'personal_info' => 'Personal Information',
            'contact_info' => 'Contact Information',
            'education' => 'Educational Background',
            'test_scores' => 'Test Scores',
            'essays' => 'Personal Statement',
            default => null,
        };

        if ($itemName) {
            ApplicationChecklistItem::where('application_id', $application->id)
                ->where('item_name', $itemName)
                ->update([
                    'is_completed' => true,
                    'completed_at' => now(),
                ]);
        }
    }

    /**
     * Update document checklist.
     */
    private function updateDocumentChecklist(AdmissionApplication $application, string $documentType, bool $completed = true): void
    {
        $itemName = match($documentType) {
            'transcript' => 'Transcript',
            'recommendation_letter' => 'Recommendation Letters',
            'test_scores' => 'Test Score Report',
            default => ucfirst(str_replace('_', ' ', $documentType)),
        };

        ApplicationChecklistItem::where('application_id', $application->id)
            ->where('item_name', $itemName)
            ->update([
                'is_completed' => $completed,
                'completed_at' => $completed ? now() : null,
            ]);
    }

    /**
     * Get field validation rules.
     */
    private function getFieldValidationRules(string $field, array $context = []): ?array
    {
        $rules = [
            'email' => 'required|email|max:255',
            'phone_primary' => 'required|regex:/^[+]?[0-9\s\-()]+$/|max:20',
            'date_of_birth' => 'required|date|before:-16 years',
            'previous_gpa' => 'required|numeric|min:0|max:5',
            'national_id' => 'required|string|max:50',
            'passport_number' => 'string|max:50',
            // Add more field rules as needed
        ];

        return $rules[$field] ?? null;
    }

    /**
     * Perform custom validation.
     */
    private function performCustomValidation(string $field, $value, array $context): array
    {
        // Add custom validation logic here
        switch ($field) {
            case 'email':
                // Check if email is not from blocked domains
                $blockedDomains = ['tempmail.com', 'throwaway.email'];
                $domain = substr(strrchr($value, "@"), 1);
                if (in_array($domain, $blockedDomains)) {
                    return ['valid' => false, 'message' => 'Please use a valid email address.'];
                }
                break;
                
            case 'previous_gpa':
                // Validate GPA based on scale
                $scale = $context['gpa_scale'] ?? '4.0';
                $maxGpa = floatval($scale);
                if ($value > $maxGpa) {
                    return ['valid' => false, 'message' => "GPA cannot exceed {$maxGpa} on your selected scale."];
                }
                break;
        }

        return ['valid' => true];
    }

    /**
     * Get status message.
     */
    private function getStatusMessage(string $status): string
    {
        $messages = [
            'draft' => 'Your application is in draft status. Please complete and submit.',
            'submitted' => 'Your application has been submitted and is being processed.',
            'under_review' => 'Your application is under review by the admissions committee.',
            'documents_pending' => 'Additional documents are required. Please check your email.',
            'committee_review' => 'Your application is being reviewed by the selection committee.',
            'interview_scheduled' => 'An interview has been scheduled. Please check your email for details.',
            'decision_pending' => 'The review process is complete. Decision will be released soon.',
            'admitted' => 'Congratulations! You have been admitted.',
            'conditional_admit' => 'You have been conditionally admitted. Please review the conditions.',
            'waitlisted' => 'You have been placed on the waitlist.',
            'denied' => 'We regret to inform you that your application was not successful.',
            'withdrawn' => 'Your application has been withdrawn.',
        ];

        return $messages[$status] ?? 'Application status: ' . $status;
    }

    /**
     * Get next steps for application.
     */
    private function getNextSteps(AdmissionApplication $application): array
    {
        $steps = [];

        switch ($application->status) {
            case 'draft':
                $steps[] = 'Complete all required sections';
                $steps[] = 'Upload required documents';
                $steps[] = 'Pay application fee';
                $steps[] = 'Submit application';
                break;
                
            case 'documents_pending':
                $steps[] = 'Upload missing documents';
                $steps[] = 'Wait for document verification';
                break;
                
            case 'admitted':
                if (!$application->enrollment_confirmed) {
                    $steps[] = 'Accept or decline admission offer';
                    $steps[] = 'Pay enrollment deposit';
                    $steps[] = 'Complete enrollment forms';
                }
                break;
        }

        return $steps;
    }

    /**
     * Get checklist status.
     */
    private function getChecklistStatus(AdmissionApplication $application): array
    {
        return $application->checklistItems->map(function ($item) {
            return [
                'name' => $item->item_name,
                'type' => $item->item_type,
                'required' => $item->is_required,
                'completed' => $item->is_completed,
                'completed_at' => $item->completed_at,
            ];
        })->toArray();
    }

    /**
     * Log analytics event.
     */
    private function logAnalyticsEvent(string $event, AdmissionApplication $application, array $metadata = []): void
    {
        try {
            // Log to analytics service
            $this->analyticsService->logEvent($event, array_merge([
                'application_id' => $application->id,
                'application_uuid' => $application->application_uuid,
                'program_id' => $application->program_id,
                'term_id' => $application->term_id,
                'user_id' => Auth::id(),
                'timestamp' => now(),
            ], $metadata));
        } catch (Exception $e) {
            Log::warning('Failed to log analytics event', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Format success response.
     */
    private function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Format error response.
     */
    private function errorResponse(string $message, int $code = 400, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Format validation error response.
     */
    private function validationErrorResponse(array $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors,
        ], self::RESPONSE_CODES['VALIDATION_ERROR']);
    }
}