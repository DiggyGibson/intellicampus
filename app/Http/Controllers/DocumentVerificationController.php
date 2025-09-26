<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DocumentVerificationService;
use App\Services\ApplicationService;
use App\Services\ApplicationNotificationService;
use App\Models\ApplicationDocument;
use App\Models\AdmissionApplication;
use App\Models\ApplicationChecklistItem;
use App\Models\ApplicationNote;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Exception;

class DocumentVerificationController extends Controller
{
    protected $documentService;
    protected $applicationService;
    protected $notificationService;

    /**
     * Document status types
     */
    private const DOCUMENT_STATUSES = [
        'uploaded' => 'Awaiting Review',
        'pending_verification' => 'Under Review',
        'verified' => 'Verified',
        'rejected' => 'Rejected',
        'expired' => 'Expired',
    ];

    /**
     * Verification actions
     */
    private const VERIFICATION_ACTIONS = [
        'verify' => 'Verify Document',
        'reject' => 'Reject Document',
        'request_reupload' => 'Request Re-upload',
        'mark_pending' => 'Mark as Pending',
    ];

    /**
     * Rejection reasons
     */
    private const REJECTION_REASONS = [
        'poor_quality' => 'Poor Quality/Illegible',
        'wrong_document' => 'Wrong Document Type',
        'incomplete' => 'Incomplete Document',
        'expired' => 'Document Expired',
        'not_official' => 'Not an Official Document',
        'tampered' => 'Document Appears Tampered',
        'wrong_format' => 'Incorrect File Format',
        'other' => 'Other (Specify)',
    ];

    /**
     * Items per page for pagination
     */
    private const ITEMS_PER_PAGE = 25;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        DocumentVerificationService $documentService,
        ApplicationService $applicationService,
        ApplicationNotificationService $notificationService
    ) {
        $this->documentService = $documentService;
        $this->applicationService = $applicationService;
        $this->notificationService = $notificationService;
        
        // Middleware for verification authority
        $this->middleware(['auth', 'role:admin,admissions_officer,registrar,document_verifier']);
    }

    /**
     * Display documents pending verification.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function pendingVerification(Request $request)
    {
        try {
            // Build query for pending documents
            $query = ApplicationDocument::with([
                'application.program',
                'application.term',
                'verifiedBy'
            ])
            ->whereIn('status', ['uploaded', 'pending_verification']);

            // Apply filters
            $this->applyFilters($query, $request);

            // Sort by priority
            $query->orderByRaw("
                CASE 
                    WHEN document_type IN ('transcript', 'diploma', 'test_scores') THEN 1
                    WHEN document_type IN ('recommendation_letter', 'financial_statement') THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('created_at', 'asc');

            // Get statistics
            $statistics = $this->getVerificationStatistics();

            // Paginate results
            $documents = $query->paginate(self::ITEMS_PER_PAGE)
                ->appends($request->all());

            // Get filter options
            $documentTypes = $this->getDocumentTypes();
            $programs = $this->getPrograms();
            $verifiers = $this->getVerifiers();

            return view('admissions.documents.pending', compact(
                'documents',
                'statistics',
                'documentTypes',
                'programs',
                'verifiers'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load pending documents', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('admin.dashboard')
                ->with('error', 'Failed to load pending documents.');
        }
    }

    /**
     * Verify a document.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyDocument(Request $request, $id)
    {
        $validated = $request->validate([
            'verification_notes' => 'nullable|string|max:500',
            'mark_official' => 'boolean',
            'expiry_date' => 'nullable|date|after:today',
        ]);

        DB::beginTransaction();

        try {
            $document = ApplicationDocument::findOrFail($id);

            // Verify the document
            $verified = $this->documentService->verifyDocument(
                $id,
                'verified',
                $validated['verification_notes'] ?? null
            );

            // Update additional fields
            $document->verified_by = Auth::id();
            $document->verified_at = now();
            
            if ($request->has('mark_official')) {
                $document->is_official = $validated['mark_official'];
            }
            
            if ($request->has('expiry_date')) {
                $document->expiry_date = $validated['expiry_date'];
            }
            
            $document->save();

            // Update checklist item
            $this->updateChecklistItem($document);

            // Check if all required documents are verified
            $this->checkApplicationCompleteness($document->application_id);

            // Log the action
            $this->logVerificationAction($document, 'verified', $validated['verification_notes'] ?? null);

            DB::commit();

            // Send notification
            $this->notificationService->sendDocumentVerifiedNotification($document->application_id, $document->id);

            // Clear cache
            Cache::forget("application_{$document->application_id}_documents");

            return redirect()->back()
                ->with('success', 'Document verified successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to verify document', [
                'document_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to verify document. Please try again.');
        }
    }

    /**
     * Reject a document.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function rejectDocument(Request $request, $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|in:' . implode(',', array_keys(self::REJECTION_REASONS)),
            'rejection_notes' => 'required_if:rejection_reason,other|string|max:500',
            'request_reupload' => 'boolean',
            'reupload_deadline' => 'required_if:request_reupload,true|date|after:today',
        ]);

        DB::beginTransaction();

        try {
            $document = ApplicationDocument::findOrFail($id);

            // Build rejection message
            $rejectionMessage = self::REJECTION_REASONS[$validated['rejection_reason']];
            if ($validated['rejection_reason'] === 'other' && !empty($validated['rejection_notes'])) {
                $rejectionMessage = $validated['rejection_notes'];
            }

            // Reject the document
            $rejected = $this->documentService->verifyDocument(
                $id,
                'rejected',
                $rejectionMessage
            );

            // Update document fields
            $document->verified_by = Auth::id();
            $document->verified_at = now();
            $document->rejection_reason = $rejectionMessage;
            $document->save();

            // Request reupload if needed
            if ($validated['request_reupload']) {
                $this->documentService->requestAdditionalDocuments(
                    $document->application_id,
                    [$document->document_type],
                    $validated['reupload_deadline']
                );
            }

            // Update checklist item
            $this->updateChecklistItem($document, false);

            // Update application status
            $this->updateApplicationStatus($document->application_id, 'documents_pending');

            // Log the action
            $this->logVerificationAction($document, 'rejected', $rejectionMessage);

            DB::commit();

            // Send notification
            $this->notificationService->sendDocumentRejectedNotification(
                $document->application_id,
                $document->id,
                $rejectionMessage
            );

            // Clear cache
            Cache::forget("application_{$document->application_id}_documents");

            return redirect()->back()
                ->with('success', 'Document rejected and applicant notified.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to reject document', [
                'document_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to reject document. Please try again.');
        }
    }

    /**
     * Bulk verify documents.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkVerify(Request $request)
    {
        $validated = $request->validate([
            'document_ids' => 'required|array|max:50',
            'document_ids.*' => 'integer|exists:application_documents,id',
            'action' => 'required|in:verify,reject',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($validated['document_ids'] as $documentId) {
                try {
                    $document = ApplicationDocument::find($documentId);
                    
                    if (!$document) {
                        $failedCount++;
                        continue;
                    }

                    if ($validated['action'] === 'verify') {
                        $this->documentService->verifyDocument(
                            $documentId,
                            'verified',
                            $validated['notes']
                        );
                    } else {
                        $this->documentService->verifyDocument(
                            $documentId,
                            'rejected',
                            $validated['notes'] ?? 'Bulk rejection'
                        );
                    }

                    $document->verified_by = Auth::id();
                    $document->verified_at = now();
                    $document->save();

                    // Update checklist
                    $this->updateChecklistItem($document, $validated['action'] === 'verify');

                    $successCount++;

                } catch (Exception $e) {
                    $failedCount++;
                    $errors[] = "Document #{$documentId}: " . $e->getMessage();
                }
            }

            // Check application completeness for affected applications
            $applicationIds = ApplicationDocument::whereIn('id', $validated['document_ids'])
                ->pluck('application_id')
                ->unique();

            foreach ($applicationIds as $appId) {
                $this->checkApplicationCompleteness($appId);
                Cache::forget("application_{$appId}_documents");
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully processed {$successCount} documents.",
                'results' => [
                    'success' => $successCount,
                    'failed' => $failedCount,
                    'errors' => $errors,
                ],
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Bulk verification failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk verification.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fraud detection for documents.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function fraudDetection(Request $request)
    {
        try {
            // Get documents flagged for potential fraud
            $suspiciousDocuments = ApplicationDocument::with([
                'application.program',
                'application.term'
            ])
            ->where(function ($query) {
                $query->where('fraud_score', '>', 0.7)
                    ->orWhere('is_flagged', true)
                    ->orWhereNotNull('fraud_indicators');
            })
            ->orderBy('fraud_score', 'desc')
            ->paginate(self::ITEMS_PER_PAGE);

            // Get fraud detection statistics
            $statistics = [
                'total_flagged' => ApplicationDocument::where('is_flagged', true)->count(),
                'high_risk' => ApplicationDocument::where('fraud_score', '>', 0.8)->count(),
                'medium_risk' => ApplicationDocument::whereBetween('fraud_score', [0.5, 0.8])->count(),
                'verified_fraudulent' => ApplicationDocument::where('fraud_confirmed', true)->count(),
            ];

            // Get common fraud indicators
            $fraudIndicators = [
                'metadata_mismatch' => 'Document metadata doesn\'t match file content',
                'duplicate_document' => 'Same document used in multiple applications',
                'tampered_content' => 'Document appears to be edited or tampered',
                'invalid_institution' => 'Institution not recognized or verified',
                'date_inconsistency' => 'Dates don\'t align with application timeline',
                'format_anomaly' => 'Document format doesn\'t match institution standards',
            ];

            return view('admissions.documents.fraud-detection', compact(
                'suspiciousDocuments',
                'statistics',
                'fraudIndicators'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load fraud detection', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('admissions.documents.pending')
                ->with('error', 'Failed to load fraud detection.');
        }
    }

    /**
     * View document details.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function viewDocument($id)
    {
        try {
            $document = ApplicationDocument::with([
                'application.program',
                'application.term',
                'verifiedBy'
            ])->findOrFail($id);

            // Get document history
            $history = $this->getDocumentHistory($id);

            // Get similar documents for comparison
            $similarDocuments = $this->findSimilarDocuments($document);

            // Get verification checklist
            $verificationChecklist = $this->getVerificationChecklist($document->document_type);

            // Check if document can be previewed
            $canPreview = in_array($document->file_type, ['pdf', 'jpg', 'jpeg', 'png']);
            $previewUrl = null;

            if ($canPreview) {
                $previewUrl = route('admissions.documents.preview', $id);
            }

            return view('admissions.documents.view', compact(
                'document',
                'history',
                'similarDocuments',
                'verificationChecklist',
                'canPreview',
                'previewUrl'
            ));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admissions.documents.pending')
                ->with('error', 'Document not found.');
        } catch (Exception $e) {
            Log::error('Failed to load document details', [
                'document_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admissions.documents.pending')
                ->with('error', 'Failed to load document details.');
        }
    }

    /**
     * Preview document file.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function previewDocument($id)
    {
        try {
            $document = ApplicationDocument::findOrFail($id);

            // Check permission
            if (!$this->canViewDocument($document)) {
                abort(403, 'Unauthorized to view this document.');
            }

            // Get file path
            $filePath = Storage::path($document->file_path);

            if (!file_exists($filePath)) {
                abort(404, 'Document file not found.');
            }

            // Return file response
            return Response::file($filePath, [
                'Content-Type' => $document->file_type,
                'Content-Disposition' => 'inline; filename="' . $document->original_filename . '"',
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Document not found.');
        } catch (Exception $e) {
            Log::error('Failed to preview document', [
                'document_id' => $id,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Failed to preview document.');
        }
    }

    /**
     * Request document reupload.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function requestReupload(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'deadline' => 'required|date|after:today',
            'instructions' => 'nullable|string|max:1000',
        ]);

        try {
            $document = ApplicationDocument::findOrFail($id);

            // Mark document as needing reupload
            $document->status = 'rejected';
            $document->rejection_reason = $validated['reason'];
            $document->reupload_requested = true;
            $document->reupload_deadline = $validated['deadline'];
            $document->save();

            // Send notification
            $this->notificationService->sendReuploadRequest(
                $document->application_id,
                $document->id,
                $validated['reason'],
                $validated['deadline'],
                $validated['instructions'] ?? null
            );

            // Log the action
            $this->logVerificationAction($document, 'reupload_requested', $validated['reason']);

            return redirect()->back()
                ->with('success', 'Reupload request sent to applicant.');

        } catch (Exception $e) {
            Log::error('Failed to request reupload', [
                'document_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to request reupload.');
        }
    }

    /**
     * Download verification report.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function downloadReport(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'status' => 'nullable|in:' . implode(',', array_keys(self::DOCUMENT_STATUSES)),
            'verifier_id' => 'nullable|exists:users,id',
        ]);

        try {
            $query = ApplicationDocument::with(['application', 'verifiedBy'])
                ->whereBetween('created_at', [$validated['date_from'], $validated['date_to']]);

            if ($request->has('status')) {
                $query->where('status', $validated['status']);
            }

            if ($request->has('verifier_id')) {
                $query->where('verified_by', $validated['verifier_id']);
            }

            $documents = $query->get();

            // Generate report
            $report = $this->generateVerificationReport($documents, $validated);

            // Return as download
            return response()->download($report['path'], $report['filename']);

        } catch (Exception $e) {
            Log::error('Failed to generate report', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to generate report.');
        }
    }

    /**
     * Helper Methods
     */

    /**
     * Apply filters to query.
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->has('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        if ($request->has('program_id')) {
            $query->whereHas('application', function ($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('priority')) {
            if ($request->priority === 'urgent') {
                $query->whereHas('application', function ($q) {
                    $q->where('priority', 'high')
                      ->orWhere('status', 'decision_pending');
                });
            }
        }
    }

    /**
     * Get verification statistics.
     */
    private function getVerificationStatistics(): array
    {
        return Cache::remember('document_verification_stats', 300, function () {
            return [
                'pending_verification' => ApplicationDocument::where('status', 'uploaded')->count(),
                'under_review' => ApplicationDocument::where('status', 'pending_verification')->count(),
                'verified_today' => ApplicationDocument::where('status', 'verified')
                    ->whereDate('verified_at', today())
                    ->count(),
                'rejected_today' => ApplicationDocument::where('status', 'rejected')
                    ->whereDate('verified_at', today())
                    ->count(),
                'total_pending' => ApplicationDocument::whereIn('status', ['uploaded', 'pending_verification'])->count(),
                'average_verification_time' => $this->calculateAverageVerificationTime(),
            ];
        });
    }

    /**
     * Calculate average verification time.
     */
    private function calculateAverageVerificationTime(): string
    {
        $avgHours = ApplicationDocument::where('status', 'verified')
            ->whereNotNull('verified_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, verified_at)) as avg_hours')
            ->value('avg_hours');

        if ($avgHours < 24) {
            return round($avgHours) . ' hours';
        } else {
            return round($avgHours / 24) . ' days';
        }
    }

    /**
     * Update checklist item.
     */
    private function updateChecklistItem(ApplicationDocument $document, bool $completed = true): void
    {
        $checklistItem = ApplicationChecklistItem::where('application_id', $document->application_id)
            ->where('item_type', 'document')
            ->where('item_name', 'LIKE', '%' . $document->document_type . '%')
            ->first();

        if ($checklistItem) {
            $checklistItem->is_completed = $completed;
            $checklistItem->completed_at = $completed ? now() : null;
            $checklistItem->save();
        }
    }

    /**
     * Check application completeness.
     */
    private function checkApplicationCompleteness(int $applicationId): void
    {
        $application = AdmissionApplication::find($applicationId);
        
        if (!$application) {
            return;
        }

        // Check if all required documents are verified
        $requiredDocuments = ApplicationChecklistItem::where('application_id', $applicationId)
            ->where('item_type', 'document')
            ->where('is_required', true)
            ->where('is_completed', false)
            ->count();

        if ($requiredDocuments === 0 && $application->status === 'documents_pending') {
            $application->status = 'under_review';
            $application->save();

            // Notify applicant
            $this->notificationService->sendApplicationCompleteNotification($applicationId);
        }
    }

    /**
     * Update application status.
     */
    private function updateApplicationStatus(int $applicationId, string $status): void
    {
        $application = AdmissionApplication::find($applicationId);
        
        if ($application) {
            $application->status = $status;
            $application->save();
        }
    }

    /**
     * Log verification action.
     */
    private function logVerificationAction(ApplicationDocument $document, string $action, ?string $notes): void
    {
        ApplicationNote::create([
            'application_id' => $document->application_id,
            'note' => "Document {$action}: {$document->document_type}. " . ($notes ?? ''),
            'type' => 'document_verification',
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Check if user can view document.
     */
    private function canViewDocument(ApplicationDocument $document): bool
    {
        $user = Auth::user();
        
        return $user->hasRole(['admin', 'admissions_officer', 'registrar', 'document_verifier']) ||
               $document->verified_by === $user->id;
    }

    /**
     * Get document types.
     */
    private function getDocumentTypes(): array
    {
        return [
            'transcript' => 'Transcript',
            'diploma' => 'Diploma/Certificate',
            'test_scores' => 'Test Scores',
            'recommendation_letter' => 'Recommendation Letter',
            'resume' => 'Resume/CV',
            'passport' => 'Passport/ID',
            'financial_statement' => 'Financial Statement',
            'other' => 'Other',
        ];
    }

    /**
     * Get programs for filter.
     */
    private function getPrograms()
    {
        return \App\Models\AcademicProgram::where('is_active', true)
            ->pluck('name', 'id');
    }

    /**
     * Get verifiers for filter.
     */
    private function getVerifiers()
    {
        return User::role(['admissions_officer', 'document_verifier', 'registrar'])
            ->where('is_active', true)
            ->pluck('name', 'id');
    }

    /**
     * Get document history.
     */
    private function getDocumentHistory(int $documentId): array
    {
        // This would typically come from an audit log table
        return [];
    }

    /**
     * Find similar documents.
     */
    private function findSimilarDocuments(ApplicationDocument $document)
    {
        return ApplicationDocument::where('document_type', $document->document_type)
            ->where('id', '!=', $document->id)
            ->where('file_hash', $document->file_hash)
            ->with('application')
            ->limit(5)
            ->get();
    }

    /**
     * Get verification checklist.
     */
    private function getVerificationChecklist(string $documentType): array
    {
        $checklists = [
            'transcript' => [
                'Check institution authenticity',
                'Verify GPA calculation',
                'Confirm course listings',
                'Check for tampering',
                'Verify official seal/signature',
            ],
            'test_scores' => [
                'Verify test date',
                'Check score validity',
                'Confirm test taker identity',
                'Verify reporting institution',
            ],
            'recommendation_letter' => [
                'Verify recommender identity',
                'Check letterhead authenticity',
                'Confirm signature',
                'Verify contact information',
            ],
        ];

        return $checklists[$documentType] ?? [
            'Check document authenticity',
            'Verify completeness',
            'Confirm relevance to application',
        ];
    }

    /**
     * Generate verification report.
     */
    private function generateVerificationReport($documents, array $filters): array
    {
        // This would generate an actual report file
        $filename = 'verification_report_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        $path = storage_path('app/reports/' . $filename);

        // Generate PDF report (placeholder)
        // ... PDF generation logic ...

        return [
            'path' => $path,
            'filename' => $filename,
        ];
    }
}