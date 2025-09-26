<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdmissionApplication;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Models\ApplicationDocument;
use App\Models\ApplicationReview;
use App\Models\AdmissionSetting;
use App\Services\Core\UnifiedDocumentService;
use App\Services\ApplicationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

/**
 * AdmissionsController
 * 
 * Handles administrative functions for admission applications
 * This is the internal controller for staff/admin use
 */
class AdmissionsController extends Controller
{
    /**
     * Document service instance
     */
    private $documentService;
    
    /**
     * Notification service instance
     */
    private $notificationService;

    /**
     * Constructor
     */
    public function __construct(
        UnifiedDocumentService $documentService,
        ApplicationNotificationService $notificationService = null
    ) {
        $this->documentService = $documentService;
        $this->notificationService = $notificationService;
        
        // Apply authentication middleware
        $this->middleware('auth');
        
        // Apply role-based middleware for different actions
        $this->middleware('role:admissions-officer,admissions-admin')->except(['show', 'documents']);
        $this->middleware('role:admissions-admin')->only(['destroy', 'settings', 'updateSettings']);
    }

    /**
     * Display listing of applications with filters
     */
    public function index(Request $request)
    {
        $query = AdmissionApplication::with(['program', 'term']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        if ($request->has('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->has('decision')) {
            $query->where('decision', $request->decision);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Date range filters
        if ($request->has('submitted_from')) {
            $query->where('submitted_at', '>=', $request->submitted_from);
        }

        if ($request->has('submitted_to')) {
            $query->where('submitted_at', '<=', $request->submitted_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $applications = $query->paginate(20);

        // Get filter options
        $terms = AcademicTerm::orderBy('start_date', 'desc')->get();
        $programs = AcademicProgram::where('accepts_applications', true)->orderBy('name')->get();

        // Get statistics
        $statistics = $this->getApplicationStatistics($request);

        return view('admissions.index', compact(
            'applications', 
            'terms', 
            'programs', 
            'statistics'
        ));
    }

    /**
     * Show detailed application view
     */
    public function show($id)
    {
        $application = AdmissionApplication::with([
            'program',
            'alternateProgram',
            'term',
            'reviews.reviewer',
            'checklistItems',
            'communications',
            'interviews',
            'fees'
        ])->findOrFail($id);

        // Get documents using the unified document service
        $documents = $this->documentService->getDocumentsFor('application', $application->id);

        // Get review history
        $reviews = ApplicationReview::where('application_id', $id)
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate completion percentage
        $completionPercentage = $application->completionPercentage();

        // Get available actions based on current status
        $availableActions = $this->getAvailableActions($application);

        return view('admissions.show', compact(
            'application',
            'documents',
            'reviews',
            'completionPercentage',
            'availableActions'
        ));
    }

    /**
     * Review an application
     */
    public function review(Request $request, $id)
    {
        $request->validate([
            'academic_rating' => 'required|integer|between:1,5',
            'extracurricular_rating' => 'required|integer|between:1,5',
            'essay_rating' => 'required|integer|between:1,5',
            'overall_rating' => 'required|integer|between:1,5',
            'recommendation' => 'required|in:admit,deny,waitlist,defer',
            'comments' => 'nullable|string|max:5000'
        ]);

        $application = AdmissionApplication::findOrFail($id);

        DB::transaction(function() use ($request, $application) {
            // Create review record
            ApplicationReview::create([
                'application_id' => $application->id,
                'reviewer_id' => Auth::id(),
                'academic_rating' => $request->academic_rating,
                'extracurricular_rating' => $request->extracurricular_rating,
                'essay_rating' => $request->essay_rating,
                'overall_rating' => $request->overall_rating,
                'recommendation' => $request->recommendation,
                'comments' => $request->comments
            ]);

            // Update application status
            if ($application->status === 'submitted') {
                $application->update(['status' => 'under_review']);
            }

            // Log activity
            $this->logActivity($application, 'reviewed', [
                'reviewer' => Auth::user()->name,
                'recommendation' => $request->recommendation
            ]);
        });

        return redirect()->route('admissions.show', $id)
            ->with('success', 'Application review submitted successfully');
    }

    /**
     * Make admission decision
     */
    public function decision(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:admit,conditional_admit,waitlist,deny,defer',
            'decision_reason' => 'nullable|string|max:1000',
            'admission_conditions' => 'nullable|string|max:2000',
            'enrollment_deadline' => 'required_if:decision,admit,conditional_admit|date|after:today',
            'notify_applicant' => 'boolean'
        ]);

        $application = AdmissionApplication::findOrFail($id);

        DB::transaction(function() use ($request, $application) {
            // Update application with decision
            $application->update([
                'decision' => $request->decision,
                'decision_date' => now(),
                'decision_by' => Auth::id(),
                'decision_reason' => $request->decision_reason,
                'admission_conditions' => $request->admission_conditions,
                'enrollment_deadline' => $request->enrollment_deadline,
                'status' => $this->mapDecisionToStatus($request->decision)
            ]);

            // If admitted, create enrollment confirmation record
            if (in_array($request->decision, ['admit', 'conditional_admit'])) {
                $application->enrollmentConfirmation()->create([
                    'deadline' => $request->enrollment_deadline,
                    'deposit_amount' => AdmissionSetting::where('term_id', $application->term_id)
                        ->where('program_id', $application->program_id)
                        ->first()->enrollment_deposit ?? 0
                ]);
            }

            // If waitlisted, add to waitlist
            if ($request->decision === 'waitlist') {
                $application->waitlist()->create([
                    'term_id' => $application->term_id,
                    'program_id' => $application->program_id,
                    'rank' => $this->calculateWaitlistRank($application)
                ]);
            }

            // Send notification if requested
            if ($request->notify_applicant && $this->notificationService) {
                $this->notificationService->sendDecisionNotification($application);
            }

            // Log activity
            $this->logActivity($application, 'decision_made', [
                'decision' => $request->decision,
                'by' => Auth::user()->name
            ]);
        });

        return redirect()->route('admissions.show', $id)
            ->with('success', 'Admission decision recorded successfully');
    }

    /**
     * Upload document for application using unified document service
     */
    public function uploadDocument(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'document_type' => 'required|string',
            'description' => 'nullable|string|max:500'
        ]);

        $application = AdmissionApplication::findOrFail($id);

        try {
            // Use unified document service
            $document = $this->documentService->store(
                $request->file('file'),
                'admission',
                $application->id,
                [
                    'category' => $this->mapDocumentTypeToCategory($request->document_type),
                    'purpose' => $request->document_type,
                    'requires_verification' => true,
                    'metadata' => [
                        'application_number' => $application->application_number,
                        'applicant_name' => $application->first_name . ' ' . $application->last_name,
                        'term' => $application->term->name ?? null,
                        'program' => $application->program->name ?? null,
                        'description' => $request->description,
                        'uploaded_by_staff' => true,
                        'staff_id' => Auth::id()
                    ]
                ]
            );

            // Log activity
            $this->logActivity($application, 'document_uploaded', [
                'document_type' => $request->document_type,
                'document_id' => $document->id,
                'by' => Auth::user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'document' => [
                    'id' => $document->id,
                    'name' => $document->display_name,
                    'type' => $request->document_type,
                    'size' => $document->human_size,
                    'status' => $document->verification_status,
                    'uploaded_at' => $document->created_at->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Document upload failed', [
                'application_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all documents for an application
     */
    public function getDocuments($id)
    {
        $application = AdmissionApplication::findOrFail($id);
        $documents = $this->documentService->getDocumentsFor('application', $id);

        return response()->json([
            'success' => true,
            'documents' => $documents->map(function($doc) {
                $relationship = $doc->relationships->where('owner_type', 'application')->first();
                return [
                    'id' => $doc->id,
                    'name' => $doc->display_name,
                    'type' => $relationship->purpose ?? $doc->category,
                    'size' => $doc->human_size,
                    'status' => $doc->verification_status,
                    'is_verified' => $doc->isVerified(),
                    'uploaded_by' => $doc->uploader->name ?? 'System',
                    'uploaded_at' => $doc->created_at->format('Y-m-d H:i:s'),
                    'verified_by' => $doc->verifier->name ?? null,
                    'verified_at' => $doc->verified_at?->format('Y-m-d H:i:s'),
                    'can_download' => true,
                    'can_verify' => Auth::user()->can('verify-documents')
                ];
            })
        ]);
    }

    /**
     * Download application document
     */
    public function downloadDocument($applicationId, $documentId)
    {
        $application = AdmissionApplication::findOrFail($applicationId);
        
        // Verify the document belongs to this application
        $documents = $this->documentService->getDocumentsFor('application', $applicationId);
        
        if (!$documents->contains('id', $documentId)) {
            abort(404, 'Document not found for this application');
        }

        try {
            $file = $this->documentService->download($documentId);
            
            return response()->download(
                $file['path'],
                $file['name'],
                ['Content-Type' => $file['mime_type']]
            );
        } catch (Exception $e) {
            Log::error('Document download failed', [
                'application_id' => $applicationId,
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);
            
            abort(500, 'Failed to download document');
        }
    }

    /**
     * Verify an application document
     */
    public function verifyDocument(Request $request, $applicationId, $documentId)
    {
        $request->validate([
            'approved' => 'required|boolean',
            'notes' => 'nullable|string|max:500'
        ]);

        $application = AdmissionApplication::findOrFail($applicationId);
        
        // Verify the document belongs to this application
        $documents = $this->documentService->getDocumentsFor('application', $applicationId);
        
        if (!$documents->contains('id', $documentId)) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found for this application'
            ], 404);
        }

        try {
            $document = $this->documentService->verify(
                $documentId,
                $request->approved,
                $request->notes
            );

            // Check if all required documents are verified
            $this->checkDocumentCompleteness($application);

            // Log activity
            $this->logActivity($application, 'document_verified', [
                'document_id' => $documentId,
                'approved' => $request->approved,
                'by' => Auth::user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => $request->approved ? 'Document verified successfully' : 'Document rejected',
                'document' => [
                    'id' => $document->id,
                    'status' => $document->verification_status
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Document verification failed', [
                'application_id' => $applicationId,
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to verify document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send communication to applicant
     */
    public function sendCommunication(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:email,sms,portal_message',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'template' => 'nullable|string'
        ]);

        $application = AdmissionApplication::findOrFail($id);

        try {
            // Create communication record
            $communication = $application->communications()->create([
                'communication_type' => $request->type,
                'direction' => 'outbound',
                'subject' => $request->subject,
                'message' => $request->message,
                'recipient_email' => $application->email,
                'sender_id' => Auth::id(),
                'sender_name' => Auth::user()->name,
                'template_used' => $request->template,
                'status' => 'pending'
            ]);

            // Send actual communication (integrate with notification service)
            if ($this->notificationService) {
                $this->notificationService->sendCustomMessage(
                    $application,
                    $request->subject,
                    $request->message,
                    $request->type
                );
                
                $communication->update([
                    'status' => 'sent',
                    'sent_at' => now()
                ]);
            }

            // Log activity
            $this->logActivity($application, 'communication_sent', [
                'type' => $request->type,
                'subject' => $request->subject
            ]);

            return redirect()->route('admissions.show', $id)
                ->with('success', 'Communication sent successfully');

        } catch (Exception $e) {
            Log::error('Failed to send communication', [
                'application_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admissions.show', $id)
                ->with('error', 'Failed to send communication');
        }
    }

    /**
     * Schedule interview for application
     */
    public function scheduleInterview(Request $request, $id)
    {
        $request->validate([
            'interview_date' => 'required|date|after:now',
            'interview_time' => 'required|date_format:H:i',
            'interview_type' => 'required|in:in_person,phone,video',
            'location' => 'required_if:interview_type,in_person',
            'meeting_link' => 'required_if:interview_type,video',
            'interviewer_id' => 'required|exists:users,id',
            'notes' => 'nullable|string'
        ]);

        $application = AdmissionApplication::findOrFail($id);

        $interview = $application->interviews()->create([
            'scheduled_date' => $request->interview_date,
            'scheduled_time' => $request->interview_time,
            'interview_type' => $request->interview_type,
            'location' => $request->location,
            'meeting_link' => $request->meeting_link,
            'interviewer_id' => $request->interviewer_id,
            'notes' => $request->notes,
            'status' => 'scheduled'
        ]);

        // Update application status
        $application->update(['status' => 'interview_scheduled']);

        // Send interview notification
        if ($this->notificationService) {
            $this->notificationService->sendInterviewScheduled($application, $interview);
        }

        return redirect()->route('admissions.show', $id)
            ->with('success', 'Interview scheduled successfully');
    }

    /**
     * Export applications to Excel/CSV
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'xlsx');
        $query = AdmissionApplication::with(['program', 'term']);

        // Apply same filters as index method
        // ... (filter logic)

        $applications = $query->get();

        // Generate export file
        // This would integrate with a package like Laravel Excel
        // For now, return CSV response
        
        $csv = $this->generateCSV($applications);
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="applications_' . date('Y-m-d') . '.csv"');
    }

    /**
     * Dashboard with statistics
     */
    public function dashboard()
    {
        $currentTerm = AcademicTerm::current()->first();
        
        $statistics = [
            'total_applications' => AdmissionApplication::where('term_id', $currentTerm->id ?? 0)->count(),
            'pending_review' => AdmissionApplication::where('term_id', $currentTerm->id ?? 0)
                ->whereIn('status', ['submitted', 'under_review'])->count(),
            'admitted' => AdmissionApplication::where('term_id', $currentTerm->id ?? 0)
                ->where('decision', 'admit')->count(),
            'enrolled' => AdmissionApplication::where('term_id', $currentTerm->id ?? 0)
                ->where('enrollment_confirmed', true)->count(),
            'recent_applications' => AdmissionApplication::where('term_id', $currentTerm->id ?? 0)
                ->latest()->limit(10)->get(),
            'by_program' => AdmissionApplication::where('term_id', $currentTerm->id ?? 0)
                ->select('program_id', DB::raw('count(*) as total'))
                ->groupBy('program_id')
                ->with('program')
                ->get(),
            'by_status' => AdmissionApplication::where('term_id', $currentTerm->id ?? 0)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->get()
        ];

        return view('admissions.dashboard', compact('statistics', 'currentTerm'));
    }

    // ==================== Helper Methods ====================

    /**
     * Get application statistics for filters
     */
    private function getApplicationStatistics($request)
    {
        $baseQuery = AdmissionApplication::query();
        
        // Apply same filters as index
        if ($request->has('term_id')) {
            $baseQuery->where('term_id', $request->term_id);
        }

        return [
            'total' => (clone $baseQuery)->count(),
            'submitted' => (clone $baseQuery)->whereNotNull('submitted_at')->count(),
            'under_review' => (clone $baseQuery)->where('status', 'under_review')->count(),
            'admitted' => (clone $baseQuery)->where('decision', 'admit')->count(),
            'waitlisted' => (clone $baseQuery)->where('decision', 'waitlist')->count(),
            'denied' => (clone $baseQuery)->where('decision', 'deny')->count()
        ];
    }

    /**
     * Get available actions based on application status
     */
    private function getAvailableActions($application)
    {
        $actions = [];

        switch ($application->status) {
            case 'submitted':
                $actions[] = 'review';
                $actions[] = 'request_documents';
                break;
            case 'under_review':
                $actions[] = 'make_decision';
                $actions[] = 'schedule_interview';
                $actions[] = 'request_documents';
                break;
            case 'interview_scheduled':
                $actions[] = 'record_interview_result';
                $actions[] = 'make_decision';
                break;
            case 'admitted':
            case 'conditional_admit':
                $actions[] = 'send_enrollment_reminder';
                $actions[] = 'extend_deadline';
                break;
            case 'waitlisted':
                $actions[] = 'offer_admission';
                $actions[] = 'update_rank';
                break;
        }

        $actions[] = 'send_communication';
        $actions[] = 'add_note';

        return $actions;
    }

    /**
     * Map document type to category for unified system
     */
    private function mapDocumentTypeToCategory($type)
    {
        $mapping = [
            'transcript' => 'academic_record',
            'high_school_transcript' => 'academic_record',
            'university_transcript' => 'academic_record',
            'diploma' => 'certificate',
            'degree_certificate' => 'certificate',
            'test_scores' => 'test_result',
            'recommendation_letter' => 'recommendation',
            'personal_statement' => 'essay',
            'essay' => 'essay',
            'resume' => 'resume',
            'portfolio' => 'portfolio',
            'financial_statement' => 'financial',
            'bank_statement' => 'financial',
            'sponsor_letter' => 'financial',
            'passport' => 'identity',
            'national_id' => 'identity',
            'birth_certificate' => 'identity',
            'medical_certificate' => 'medical',
            'english_proficiency' => 'test_result',
            'other' => 'other'
        ];

        return $mapping[$type] ?? 'other';
    }

    /**
     * Map decision to status
     */
    private function mapDecisionToStatus($decision)
    {
        $mapping = [
            'admit' => 'admitted',
            'conditional_admit' => 'conditional_admit',
            'waitlist' => 'waitlisted',
            'deny' => 'denied',
            'defer' => 'deferred'
        ];

        return $mapping[$decision] ?? 'decision_pending';
    }

    /**
     * Calculate waitlist rank
     */
    private function calculateWaitlistRank($application)
    {
        // Calculate based on review scores and other factors
        $reviews = $application->reviews;
        
        if ($reviews->isEmpty()) {
            return 999; // Default low rank
        }

        $avgScore = $reviews->avg('overall_rating');
        
        // Get current waitlist count
        $currentCount = DB::table('admission_waitlists')
            ->where('term_id', $application->term_id)
            ->where('program_id', $application->program_id)
            ->where('status', 'active')
            ->count();

        // Simple ranking: higher scores get better ranks
        // This could be more sophisticated
        return $currentCount + 1;
    }

    /**
     * Check if all required documents are verified
     */
    private function checkDocumentCompleteness($application)
    {
        $documents = $this->documentService->getDocumentsFor('application', $application->id);
        
        $requiredTypes = ['transcript', 'personal_statement', 'recommendation_letter'];
        $verifiedTypes = [];

        foreach ($documents as $doc) {
            if ($doc->isVerified()) {
                $relationship = $doc->relationships->where('owner_type', 'application')->first();
                if ($relationship) {
                    $verifiedTypes[] = $relationship->purpose;
                }
            }
        }

        $allVerified = count(array_intersect($requiredTypes, $verifiedTypes)) === count($requiredTypes);

        if ($allVerified && $application->status === 'documents_pending') {
            $application->update(['status' => 'under_review']);
        }

        return $allVerified;
    }

    /**
     * Log activity for application
     */
    private function logActivity($application, $action, $details = [])
    {
        $log = $application->activity_log ?? [];
        
        $log[] = [
            'timestamp' => now()->toIso8601String(),
            'action' => $action,
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'details' => $details
        ];

        $application->update(['activity_log' => $log]);
    }

    /**
     * Generate CSV from applications
     */
    private function generateCSV($applications)
    {
        $csv = "Application Number,Name,Email,Program,Term,Status,Decision,Submitted Date\n";
        
        foreach ($applications as $app) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s\n",
                $app->application_number,
                $app->first_name . ' ' . $app->last_name,
                $app->email,
                $app->program->name ?? 'N/A',
                $app->term->name ?? 'N/A',
                $app->status,
                $app->decision ?? 'Pending',
                $app->submitted_at?->format('Y-m-d') ?? 'Not submitted'
            );
        }

        return $csv;
    }
}