<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ApplicationService;
use App\Services\ApplicationReviewService;
use App\Services\AdmissionDecisionService;
use App\Services\DocumentVerificationService;
use App\Services\ApplicationNotificationService;
use App\Services\AdmissionsAnalyticsService;
use App\Services\EnrollmentConfirmationService;
use App\Services\EntranceExamService;
use App\Models\AdmissionApplication;
use App\Models\ApplicationDocument;
use App\Models\ApplicationReview;
use App\Models\ApplicationNote;
use App\Models\ApplicationStatusHistory;
use App\Models\ApplicationCommunication;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ApplicationsExport;
use Carbon\Carbon;
use Exception;

class AdminApplicationController extends Controller
{
    protected $applicationService;
    protected $reviewService;
    protected $decisionService;
    protected $documentService;
    protected $notificationService;
    protected $analyticsService;
    protected $enrollmentService;
    protected $examService;

    /**
     * Items per page for pagination
     */
    private const ITEMS_PER_PAGE = 25;

    /**
     * Application status filters
     */
    private const STATUS_FILTERS = [
        'all' => 'All Applications',
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'documents_pending' => 'Documents Pending',
        'committee_review' => 'Committee Review',
        'interview_scheduled' => 'Interview Scheduled',
        'decision_pending' => 'Decision Pending',
        'admitted' => 'Admitted',
        'waitlisted' => 'Waitlisted',
        'denied' => 'Denied',
        'withdrawn' => 'Withdrawn',
    ];

    /**
     * Bulk action types
     */
    private const BULK_ACTIONS = [
        'update_status' => 'Update Status',
        'assign_reviewer' => 'Assign Reviewer',
        'send_notification' => 'Send Notification',
        'export' => 'Export Selected',
        'move_to_committee' => 'Move to Committee Review',
        'request_documents' => 'Request Documents',
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(
        ApplicationService $applicationService,
        ApplicationReviewService $reviewService,
        AdmissionDecisionService $decisionService,
        DocumentVerificationService $documentService,
        ApplicationNotificationService $notificationService,
        AdmissionsAnalyticsService $analyticsService,
        EnrollmentConfirmationService $enrollmentService = null,
        EntranceExamService $examService = null
    ) {
        $this->applicationService = $applicationService;
        $this->reviewService = $reviewService;
        $this->decisionService = $decisionService;
        $this->documentService = $documentService;
        $this->notificationService = $notificationService;
        $this->analyticsService = $analyticsService;
        $this->enrollmentService = $enrollmentService ?: app(EnrollmentConfirmationService::class);
        $this->examService = $examService ?: app(EntranceExamService::class); 
        
        // Middleware for admin access
        $this->middleware(['auth', 'role:admin,admissions_officer,registrar']);
    }

    /**
     * Display applications list with filters.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            // Build query with filters
            $query = AdmissionApplication::with(['program', 'term', 'reviews', 'documents']);

            // Apply filters
            if ($request->has('status') && $request->status != 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('program_id') && $request->program_id) {
                $query->where('program_id', $request->program_id);
            }

            if ($request->has('term_id') && $request->term_id) {
                $query->where('term_id', $request->term_id);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('application_number', 'like', '%'.$search.'%')
                    ->orWhere('first_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
                });
            }

            // Apply sorting
            $sortField = $request->get('sort', 'created_at');
            $sortOrder = $request->get('order', 'desc');
            $query->orderBy($sortField, $sortOrder);

            // Paginate results
            $applications = $query->paginate(25)->withQueryString();

            // Get filter options
            $programs = AcademicProgram::where('is_active', true)->pluck('name', 'id');
            $terms = AcademicTerm::orderBy('start_date', 'desc')->pluck('name', 'id');
            $currentTerm = AcademicTerm::where('is_current', true)->first();

            // Status options
            $statuses = [
                'all' => 'All Applications',
                'draft' => 'Draft',
                'submitted' => 'Submitted',
                'under_review' => 'Under Review',
                'decision_pending' => 'Decision Pending',
                'admitted' => 'Admitted',
                'denied' => 'Denied',
                'waitlisted' => 'Waitlisted'
            ];

            // Statistics for quick view
            $stats = [
                'submitted' => AdmissionApplication::where('status', 'submitted')->count(),
                'under_review' => AdmissionApplication::where('status', 'under_review')->count(),
                'admitted' => AdmissionApplication::where('decision', 'admit')->count(),
                'denied' => AdmissionApplication::where('decision', 'deny')->count(),
                'waitlisted' => AdmissionApplication::where('decision', 'waitlist')->count(),
            ];

            return view('admissions.admin.applications-list', compact(
                'applications',
                'programs',
                'terms',
                'statuses',
                'currentTerm',
                'stats'
            ));
        } catch (\Exception $e) {
            Log::error('Applications list failed: ' . $e->getMessage());
            
            // IMPORTANT: Redirect to the correct dashboard route
            return redirect()->route('admin.admissions.dashboard')
                ->with('error', 'Unable to load applications list');
        }
    }

    /**
     * Submit review for an application
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitReview(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $application = AdmissionApplication::findOrFail($id);
            
            // Find the reviewer's pending review
            $review = ApplicationReview::where('application_id', $id)
                ->where('reviewer_id', Auth::id())
                ->where('status', 'pending')
                ->firstOrFail();
            
            $validated = $request->validate([
                'academic_rating' => 'required|integer|between:1,5',
                'extracurricular_rating' => 'required|integer|between:1,5',
                'essay_rating' => 'required|integer|between:1,5',
                'recommendation_rating' => 'required|integer|between:1,5',
                'interview_rating' => 'nullable|integer|between:1,5',
                'overall_rating' => 'required|integer|between:1,5',
                'strengths' => 'required|string|max:2000',
                'weaknesses' => 'required|string|max:2000',
                'comments' => 'required|string|max:5000',
                'recommendation' => 'required|in:strong_admit,admit,waitlist,deny,defer',
                'red_flags' => 'nullable|array',
                'red_flags.*' => 'string'
            ]);
            
            // Update review
            $review->update([
                'academic_rating' => $validated['academic_rating'],
                'extracurricular_rating' => $validated['extracurricular_rating'],
                'essay_rating' => $validated['essay_rating'],
                'recommendation_rating' => $validated['recommendation_rating'],
                'interview_rating' => $validated['interview_rating'],
                'overall_rating' => $validated['overall_rating'],
                'strengths' => $validated['strengths'],
                'weaknesses' => $validated['weaknesses'],
                'comments' => $validated['comments'],
                'recommendation' => $validated['recommendation'],
                'red_flags' => json_encode($validated['red_flags'] ?? []),
                'status' => 'completed',
                'completed_at' => now()
            ]);
            
            // Check if all required reviews are complete
            $this->checkReviewCompletion($application);
            
            // Add note about review completion
            ApplicationNote::create([
                'application_id' => $application->id,
                'note' => sprintf(
                    'Review completed by %s - Recommendation: %s',
                    Auth::user()->name,
                    str_replace('_', ' ', $validated['recommendation'])
                ),
                'type' => 'review',
                'created_by' => Auth::id()
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.admissions.applications.show', $id)
                ->with('success', 'Review submitted successfully.');
                
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit review', [
                'application_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to submit review.')
                ->withInput();
        }
    }

    /**
     * Verify document
     * 
     * @param Request $request
     * @param int $documentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyDocument(Request $request, $documentId)
    {
        try {
            $document = ApplicationDocument::findOrFail($documentId);
            
            $document->is_verified = true;
            $document->verified_by = Auth::id();
            $document->verified_at = now();
            $document->status = 'verified';
            $document->save();
            
            // Add note
            ApplicationNote::create([
                'application_id' => $document->application_id,
                'note' => sprintf('Document "%s" verified', $document->document_name),
                'type' => 'document',
                'created_by' => Auth::id()
            ]);
            
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            Log::error('Failed to verify document', [
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * Reject document
     * 
     * @param Request $request
     * @param int $documentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectDocument(Request $request, $documentId)
    {
        try {
            $document = ApplicationDocument::findOrFail($documentId);
            
            $validated = $request->validate([
                'reason' => 'required|string|max:500'
            ]);
            
            $document->status = 'rejected';
            $document->rejection_reason = $validated['reason'];
            $document->rejected_by = Auth::id();
            $document->rejected_at = now();
            $document->is_verified = false;
            $document->save();
            
            // Add note
            ApplicationNote::create([
                'application_id' => $document->application_id,
                'note' => sprintf('Document "%s" rejected: %s', $document->document_name, $validated['reason']),
                'type' => 'document',
                'created_by' => Auth::id()
            ]);
            
            // Notify applicant
            if ($this->notificationService) {
                $this->notificationService->sendDocumentRejection(
                    $document->application_id, 
                    $document->id, 
                    $validated['reason']
                );
            }
            
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            Log::error('Failed to reject document', [
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /**
     * View document
     * 
     * @param int $documentId
     * @return mixed
     */
    public function viewDocument($documentId)
    {
        try {
            $document = ApplicationDocument::findOrFail($documentId);
            
            // Check permission
            $this->authorize('view', $document);
            
            $path = storage_path('app/' . $document->file_path);
            
            if (!file_exists($path)) {
                abort(404, 'Document file not found');
            }
            
            return response()->file($path);
        } catch (Exception $e) {
            Log::error('Failed to view document', [
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);
            abort(404, 'Document not found');
        }
    }

    /**
     * Schedule interview for application
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function scheduleInterview(Request $request, $id)
    {
        try {
            $application = AdmissionApplication::findOrFail($id);
            
            $validated = $request->validate([
                'interview_date' => 'required|date|after:today',
                'interview_time' => 'required|date_format:H:i',
                'interview_type' => 'required|in:in_person,phone,video',
                'interviewer_id' => 'required|exists:users,id',
                'location' => 'required_if:interview_type,in_person|nullable|string|max:255',
                'meeting_link' => 'required_if:interview_type,video|nullable|url',
                'duration_minutes' => 'required|integer|min:15|max:120',
                'instructions' => 'nullable|string|max:1000'
            ]);
            
            // Create interview record
            DB::table('admission_interviews')->insert([
                'application_id' => $application->id,
                'interview_date' => $validated['interview_date'],
                'interview_time' => $validated['interview_time'],
                'interview_type' => $validated['interview_type'],
                'interviewer_id' => $validated['interviewer_id'],
                'location' => $validated['location'] ?? null,
                'meeting_link' => $validated['meeting_link'] ?? null,
                'duration_minutes' => $validated['duration_minutes'],
                'instructions' => $validated['instructions'] ?? null,
                'status' => 'scheduled',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Update application status
            $application->status = 'interview_scheduled';
            $application->save();
            
            // Send notifications
            if ($this->notificationService) {
                $this->notificationService->sendInterviewInvitation($application->id);
            }
            
            return redirect()->route('admin.admissions.applications.show', $id)
                ->with('success', 'Interview scheduled successfully.');
                
        } catch (Exception $e) {
            Log::error('Failed to schedule interview', [
                'application_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to schedule interview.')
                ->withInput();
        }
    }

    /**
     * Show application details.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $application = AdmissionApplication::with([
                'program',
                'term',
                'documents',
                'reviews.reviewer',
                'notes.createdBy'
            ])->findOrFail($id);

            // Calculate WASSCE average if scores exist
            $wassceAverage = null;
            $examRequired = false;
            
            if ($application->test_scores && isset($application->test_scores['wassce'])) {
                $wassceScores = $application->test_scores['wassce'];
                $gradePoints = [
                    'A1' => 1, 'B2' => 2, 'B3' => 3,
                    'C4' => 4, 'C5' => 5, 'C6' => 6,
                    'D7' => 7, 'E8' => 8, 'F9' => 9
                ];
                
                $totalPoints = 0;
                $subjectCount = 0;
                
                foreach ($wassceScores as $subject => $grade) {
                    if (isset($gradePoints[$grade])) {
                        $totalPoints += $gradePoints[$grade];
                        $subjectCount++;
                    }
                }
                
                if ($subjectCount > 0) {
                    $wassceAverage = round($totalPoints / $subjectCount, 2);
                    $examRequired = $wassceAverage > 6; // Entrance exam required if average > C6
                }
            }

            // Get available reviewers
            $availableReviewers = User::whereHas('roles', function($q) {
                $q->whereIn('name', ['admissions-officer', 'faculty', 'admin']);
            })->pluck('name', 'id');

            // Get review statistics
            $reviewStats = [
                'total_reviews' => $application->reviews->count(),
                'completed_reviews' => $application->reviews->where('status', 'completed')->count(),
                'average_rating' => $application->reviews->where('status', 'completed')->avg('overall_rating') ?? 0,
            ];

            // Decision options
            $decisionOptions = [
                'admit' => 'Admit',
                'conditional_admit' => 'Conditional Admit',
                'waitlist' => 'Waitlist',
                'deny' => 'Deny',
                'defer' => 'Defer to Next Term'
            ];

            // Check permissions
            $canReview = Auth::user()->can('review-applications') || 
                        Auth::user()->hasRole(['admin', 'admissions-officer']);
            $canMakeDecision = Auth::user()->can('make-admission-decisions') || 
                            Auth::user()->hasRole(['admin', 'admissions-director']);
            $canAssignReviewer = Auth::user()->can('assign-reviewers') || 
                                Auth::user()->hasRole(['admin', 'admissions-officer']);

            // Enrollment status if admitted
            $enrollmentStatus = null;
            if ($application->decision === 'admit') {
                $enrollmentStatus = DB::table('enrollment_confirmations')
                    ->where('application_id', $id)
                    ->first();
            }

            // Exam registration if required
            $examRegistration = null;
            if ($examRequired) {
                $examRegistration = DB::table('entrance_exam_registrations')
                    ->where('application_id', $id)
                    ->first();
            }

            return view('admissions.admin.application-detail', compact(
                'application',
                'wassceAverage',
                'examRequired',
                'availableReviewers',
                'reviewStats',
                'decisionOptions',
                'canReview',
                'canMakeDecision',
                'canAssignReviewer',
                'enrollmentStatus',
                'examRegistration'
            ));
        } catch (\Exception $e) {
            Log::error('Application detail failed: ' . $e->getMessage());
            
            // IMPORTANT: Redirect to the applications list, not the general dashboard
            return redirect()->route('admin.admissions.applications.index')
                ->with('error', 'Application not found');
        }
    }

    /**
     * Make admission decision.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function makeDecision(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($id);

            $validated = $request->validate([
                'decision' => 'required|in:admit,conditional_admit,waitlist,deny,defer',
                'decision_reason' => 'required|string|max:1000',
                'conditions' => 'required_if:decision,conditional_admit|nullable|string|max:1000',
                'waitlist_rank' => 'required_if:decision,waitlist|nullable|integer|min:1',
                'notify_applicant' => 'boolean',
                'generate_letter' => 'boolean',
            ]);

            // Update application with decision
            $application->decision = $validated['decision'];
            $application->decision_date = now();
            $application->decision_by = Auth::id();
            $application->decision_reason = $validated['decision_reason'];
            
            if ($validated['decision'] === 'conditional_admit') {
                $application->admission_conditions = $validated['conditions'];
            }
            
            if ($validated['decision'] === 'waitlist') {
                $application->waitlist_rank = $validated['waitlist_rank'];
            }
            
            // Update status based on decision
            $application->status = match($validated['decision']) {
                'admit', 'conditional_admit' => 'admitted',
                'deny' => 'denied',
                'waitlist' => 'waitlisted',
                'defer' => 'deferred',
                default => $application->status
            };
            
            $application->save();

            // Log the decision
            DB::table('admission_decision_logs')->insert([
                'application_id' => $id,
                'decision' => $validated['decision'],
                'decision_reason' => $validated['decision_reason'],
                'decided_by' => Auth::id(),
                'created_at' => now()
            ]);

            DB::commit();

            // Stay on the same application page
            return redirect()->route('admin.admissions.applications.show', $id)
                ->with('success', 'Decision recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to make decision', [
                'application_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to record decision.')
                ->withInput();
        }
    }


    /**
     * Assign reviewer to application.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignReviewer(Request $request, $id)
    {
        try {
            $application = AdmissionApplication::findOrFail($id);

            $validated = $request->validate([
                'reviewer_id' => 'required|exists:users,id',
                'review_stage' => 'required|in:initial_review,academic_review,department_review,committee_review,final_review',
                'deadline' => 'nullable|date|after:today',
                'instructions' => 'nullable|string|max:500',
            ]);

            // Assign reviewer
            $review = $this->reviewService->assignReviewer(
                $application->id,
                $validated['reviewer_id'],
                $validated['review_stage']
            );

            // Set deadline if provided
            if (!empty($validated['deadline'])) {
                $review->deadline = $validated['deadline'];
                $review->save();
            }

            // Add instructions as note
            if (!empty($validated['instructions'])) {
                ApplicationNote::create([
                    'application_id' => $application->id,
                    'note' => 'Review Instructions: ' . $validated['instructions'],
                    'type' => 'review_assignment',
                    'created_by' => Auth::id(),
                ]);
            }

            // Send notification to reviewer
            $this->notificationService->sendReviewAssignment($review->id);

            return redirect()->back()
                ->with('success', 'Reviewer assigned successfully.');

        } catch (Exception $e) {
            Log::error('Failed to assign reviewer', [
                'application_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to assign reviewer.');
        }
    }

    /**
     * Bulk actions on applications.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkAction(Request $request)
    {
        try {
            $validated = $request->validate([
                'action' => 'required|in:' . implode(',', array_keys(self::BULK_ACTIONS)),
                'application_ids' => 'required|array|min:1',
                'application_ids.*' => 'exists:admission_applications,id',
            ]);

            $count = 0;

            switch ($validated['action']) {
                case 'update_status':
                    $request->validate(['new_status' => 'required|string']);
                    foreach ($validated['application_ids'] as $id) {
                        $this->updateApplicationStatus($id, $request->new_status);
                        $count++;
                    }
                    $message = "{$count} application(s) status updated.";
                    break;

                case 'assign_reviewer':
                    $request->validate([
                        'reviewer_id' => 'required|exists:users,id',
                        'review_stage' => 'required|string',
                    ]);
                    $count = $this->reviewService->bulkAssignReviews(
                        $validated['application_ids'],
                        [$request->reviewer_id],
                        $request->review_stage
                    );
                    $message = "{$count} application(s) assigned to reviewer.";
                    break;

                case 'send_notification':
                    $request->validate([
                        'notification_type' => 'required|string',
                        'message' => 'required|string',
                    ]);
                    foreach ($validated['application_ids'] as $id) {
                        $this->notificationService->sendCustomNotification($id, $request->message);
                        $count++;
                    }
                    $message = "{$count} notification(s) sent.";
                    break;

                case 'export':
                    return $this->exportApplications($validated['application_ids']);

                case 'move_to_committee':
                    foreach ($validated['application_ids'] as $id) {
                        $app = AdmissionApplication::find($id);
                        if ($app && $app->status === 'under_review') {
                            $app->status = 'committee_review';
                            $app->save();
                            $count++;
                        }
                    }
                    $message = "{$count} application(s) moved to committee review.";
                    break;

                case 'request_documents':
                    $request->validate(['document_types' => 'required|array']);
                    foreach ($validated['application_ids'] as $id) {
                        $this->documentService->requestAdditionalDocuments(
                            $id,
                            $request->document_types
                        );
                        $count++;
                    }
                    $message = "Document requests sent to {$count} application(s).";
                    break;

                default:
                    throw new Exception("Unknown bulk action: {$validated['action']}");
            }

            return redirect()->back()
                ->with('success', $message);

        } catch (Exception $e) {
            Log::error('Bulk action failed', [
                'action' => $request->action,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Bulk action failed: ' . $e->getMessage());
        }
    }

    /**
     * Export applications.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        try {
            $format = $request->get('format', 'excel');
            $query = AdmissionApplication::with(['program', 'term']);
            
            // Apply filters
            $this->applyFilters($query, $request);

            // Get applications
            $applications = $query->get();

            if ($format === 'pdf') {
                // Handle PDF export
                // You'll need to implement PDF generation
                return response()->download('applications.pdf');
            }
            
            // Default to Excel
            $export = new ApplicationsExport($applications);
            $filename = 'applications_' . now()->format('Y-m-d_His') . '.xlsx';

            return Excel::download($export, $filename);

        } catch (Exception $e) {
            Log::error('Export failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Export failed.');
        }
    }

    /**
     * Dashboard view
     */
    public function dashboard()
    {
        try {
            // Get statistics for dashboard
            $statistics = [
                'total_applications' => AdmissionApplication::count(),
                'pending_review' => AdmissionApplication::where('status', 'submitted')->count(),
                'under_review' => AdmissionApplication::where('status', 'under_review')->count(),
                'decisions_pending' => AdmissionApplication::where('status', 'decision_pending')->count(),
                'admitted' => AdmissionApplication::where('decision', 'admit')->count(),
                'denied' => AdmissionApplication::where('decision', 'deny')->count(),
                'waitlisted' => AdmissionApplication::where('decision', 'waitlist')->count(),
                'today_submissions' => AdmissionApplication::whereDate('created_at', today())->count(),
            ];

            // Get recent applications
            $recentApplications = AdmissionApplication::with(['program', 'term'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Get pending actions
            $pendingActions = [
                'documents' => ApplicationDocument::where('is_verified', false)->count(),
                'reviews' => ApplicationReview::whereNull('completed_at')->count(),
                'decisions' => AdmissionApplication::where('status', 'decision_pending')->count(),
                'interviews' => 0,
            ];

            // Get current term
            $currentTerm = AcademicTerm::where('is_current', true)->first();

            // Additional stats for the dashboard
            $stats = [
                'total_applications' => $statistics['total_applications'],
                'pending_review' => $statistics['pending_review'],
                'under_review' => $statistics['under_review'],
                'admitted' => $statistics['admitted'],
                'denied' => $statistics['denied'],
                'waitlisted' => $statistics['waitlisted'],
                'documents_pending' => $pendingActions['documents'],
                'today' => $statistics['today_submissions'],
                'this_week' => AdmissionApplication::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'conversion_rate' => $statistics['total_applications'] > 0 ? 
                    round(($statistics['admitted'] / $statistics['total_applications']) * 100, 1) : 0,
                'current_term' => $currentTerm,
            ];

            return view('admissions.admin.dashboard', compact(
                'statistics',
                'recentApplications',
                'pendingActions',
                'currentTerm',
                'stats'
            ));
        } catch (\Exception $e) {
            Log::error('Dashboard loading failed: ' . $e->getMessage());
            
            // Redirect to main admin dashboard if admissions dashboard fails
            return redirect()->route('admin.dashboard')
                ->with('error', 'Unable to load admissions dashboard');
        }
    }

    /**
     * Overview page (alias for dashboard with more details)
     */
    public function overview()
    {
        return $this->dashboard();
    }

    /**
     * Statistics page
     */
    public function statistics()
    {
        $stats = $this->analyticsService->getDetailedStatistics();
        return view('admissions.admin.statistics', compact('stats'));
    }

    /**
     * Admissions calendar
     */
    public function admissionsCalendar()
    {
        $events = $this->applicationService->getCalendarEvents();
        return view('admissions.admin.calendar', compact('events'));
    }

    /**
     * Pending applications
     */
    public function pending()
    {
        $applications = AdmissionApplication::with(['program', 'term'])
            ->where('status', 'submitted')
            ->orderBy('submitted_at', 'asc')
            ->paginate(self::ITEMS_PER_PAGE);

        return view('admissions.admin.applications-list', compact('applications'));
    }

    /**
     * Applications under review
     */
    public function underReview()
    {
        $applications = AdmissionApplication::with(['program', 'term', 'reviews'])
            ->where('status', 'under_review')
            ->orderBy('updated_at', 'desc')
            ->paginate(self::ITEMS_PER_PAGE);

        return view('admissions.admin.applications-list', compact('applications'));
    }

    /**
     * Incomplete applications
     */
    public function incomplete()
    {
        $applications = AdmissionApplication::with(['program', 'term'])
            ->whereIn('status', ['draft', 'documents_pending'])
            ->orderBy('updated_at', 'desc')
            ->paginate(self::ITEMS_PER_PAGE);

        return view('admissions.admin.applications-list', compact('applications'));
    }

    /**
     * Edit application
     */
    public function edit($id)
    {
        $application = AdmissionApplication::findOrFail($id);
        $programs = AcademicProgram::where('is_active', true)->get();
        $terms = AcademicTerm::orderBy('start_date', 'desc')->get();

        return view('admissions.admin.edit', compact('application', 'programs', 'terms'));
    }

    /**
     * Update application
     */
    public function update(Request $request, $id)
    {
        $application = AdmissionApplication::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'notes' => 'sometimes|string',
            // Add other fields as needed
        ]);

        $application->update($validated);

        return redirect()->route('admin.admissions.applications.show', $id)
            ->with('success', 'Application updated successfully');
    }

    /**
     * Delete application
     */
    public function destroy($id)
    {
        $application = AdmissionApplication::findOrFail($id);
        
        if ($application->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft applications can be deleted');
        }

        $application->delete();

        return redirect()->route('admin.admissions.applications.index')
            ->with('success', 'Application deleted successfully');
    }

    /**
     * Flag application
     */
    public function flagApplication(Request $request, $id)
    {
        // Implementation handled by your service
        return response()->json(['success' => true]);
    }

    /**
     * Send to review
     */
    public function sendToReview(Request $request, $id)
    {
        $validated = $request->validate([
            'reviewer_id' => 'required|exists:users,id',
            'review_type' => 'required|string',
        ]);

        $this->reviewService->assignReviewer($id, $validated['reviewer_id'], $validated['review_type']);

        return response()->json(['success' => true, 'message' => 'Sent to review']);
    }

    /**
     * Export all applications
     */
    public function exportAll(Request $request)
    {
        $query = AdmissionApplication::with(['program', 'term']);
        $this->applyFilters($query, $request);
        
        return $this->export($request);
    }

    /**
     * Export selected applications
     */
    public function exportSelected(Request $request)
    {
        $validated = $request->validate([
            'application_ids' => 'required|array',
            'application_ids.*' => 'exists:admission_applications,id'
        ]);

        return $this->exportApplications($validated['application_ids']);
    }

    // Settings & Configuration Methods (stubs for now)
    public function settings() 
    { 
        return view('admissions.admin.settings.index'); 
    }

    public function updateGeneralSettings(Request $request) 
    { 
        return redirect()->back()->with('success', 'Settings updated'); 
    }

    public function admissionCycles() 
    { 
        $cycles = AcademicTerm::orderBy('start_date', 'desc')->paginate(20);
        return view('admissions.admin.settings.cycles', compact('cycles')); 
    }


    /**
     * Generate various reports.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function generateReports(Request $request)
    {
        try {
            $reportType = $request->get('type', 'summary');
            $termId = $request->get('term_id', $this->getCurrentTermId());
            $programId = $request->get('program_id');

            $data = [];

            switch ($reportType) {
                case 'summary':
                    $data = $this->analyticsService->getApplicationStatistics([
                        'term_id' => $termId,
                        'program_id' => $programId,
                    ]);
                    break;

                case 'conversion':
                    $data = $this->analyticsService->calculateConversionRates($termId, $programId);
                    break;

                case 'demographics':
                    $data = $this->analyticsService->generateDemographicReport($termId, $programId);
                    break;

                case 'trends':
                    $data = $this->analyticsService->analyzeApplicationTrends([
                        'start_date' => $request->get('start_date'),
                        'end_date' => $request->get('end_date'),
                    ]);
                    break;

                case 'program_comparison':
                    $data = $this->analyticsService->compareProgramStatistics($termId);
                    break;

                default:
                    throw new Exception("Unknown report type: {$reportType}");
            }

            // Get available terms and programs for filters
            $terms = AcademicTerm::orderBy('start_date', 'desc')->pluck('name', 'id');
            $programs = AcademicProgram::where('is_active', true)->pluck('name', 'id');

            return view('admissions.admin.reports', compact(
                'data',
                'reportType',
                'terms',
                'programs',
                'termId',
                'programId'
            ));

        } catch (Exception $e) {
            Log::error('Report generation failed', [
                'type' => $request->type,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to generate report.');
        }
    }

    /**
     * Send communication to applicant.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendCommunication(Request $request, $id)
    {
        try {
            $application = AdmissionApplication::findOrFail($id);

            $validated = $request->validate([
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
                'communication_type' => 'required|in:email,sms,portal_message',
                'template' => 'nullable|string',
            ]);

            // Send communication
            $communication = $this->notificationService->sendCustomCommunication(
                $application->id,
                $validated['subject'],
                $validated['message'],
                $validated['communication_type']
            );

            return redirect()->back()
                ->with('success', 'Communication sent successfully.');

        } catch (Exception $e) {
            Log::error('Failed to send communication', [
                'application_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to send communication.');
        }
    }

    /**
     * Show audit log/history for an application
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function auditLog($id)
    {
        try {
            $application = AdmissionApplication::findOrFail($id);
            
            // Get status history
            $statusHistory = DB::table('application_status_history')
                ->where('application_id', $id)
                ->join('users', 'application_status_history.changed_by', '=', 'users.id')
                ->select('application_status_history.*', 'users.name as changed_by_name')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Get notes
            $notes = ApplicationNote::where('application_id', $id)
                ->with('createdBy')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Get document activity
            $documentActivity = ApplicationDocument::where('application_id', $id)
                ->with(['verifiedBy', 'uploadedBy'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Get review activity
            $reviewActivity = ApplicationReview::where('application_id', $id)
                ->with('reviewer')
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Combine all activities into a timeline
            $timeline = collect();
            
            // Add status changes
            foreach ($statusHistory as $history) {
                $timeline->push([
                    'date' => $history->created_at,
                    'type' => 'status_change',
                    'icon' => 'fas fa-exchange-alt',
                    'color' => 'info',
                    'title' => 'Status Changed',
                    'description' => "From {$history->from_status} to {$history->to_status}",
                    'user' => $history->changed_by_name,
                    'notes' => $history->notes
                ]);
            }
            
            // Add notes
            foreach ($notes as $note) {
                $timeline->push([
                    'date' => $note->created_at,
                    'type' => 'note',
                    'icon' => 'fas fa-sticky-note',
                    'color' => 'warning',
                    'title' => ucfirst($note->type) . ' Note Added',
                    'description' => $note->note,
                    'user' => $note->createdBy->name ?? 'System'
                ]);
            }
            
            // Add document uploads/verifications
            foreach ($documentActivity as $doc) {
                $timeline->push([
                    'date' => $doc->created_at,
                    'type' => 'document',
                    'icon' => 'fas fa-file-upload',
                    'color' => 'primary',
                    'title' => 'Document ' . ($doc->is_verified ? 'Verified' : 'Uploaded'),
                    'description' => $doc->document_name . ' (' . $doc->document_type . ')',
                    'user' => $doc->is_verified ? 
                        ($doc->verifiedBy->name ?? 'System') : 
                        ($doc->uploadedBy->name ?? 'Applicant')
                ]);
            }
            
            // Add reviews
            foreach ($reviewActivity as $review) {
                if ($review->completed_at) {
                    $timeline->push([
                        'date' => $review->completed_at,
                        'type' => 'review',
                        'icon' => 'fas fa-clipboard-check',
                        'color' => 'success',
                        'title' => 'Review Completed',
                        'description' => ucfirst($review->review_stage) . ' - Rating: ' . $review->overall_rating . '/5',
                        'user' => $review->reviewer->name ?? 'Unknown'
                    ]);
                }
            }
            
            // Sort timeline by date descending
            $timeline = $timeline->sortByDesc('date')->values();
            
            return view('admissions.admin.audit-log', compact('application', 'timeline'));
            
        } catch (\Exception $e) {
            Log::error('Failed to load audit log', [
                'application_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('admin.admissions.applications.show', $id)
                ->with('error', 'Unable to load audit log');
        }
    }

    /**
     * Update application status via AJAX
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        
        try {
            $application = AdmissionApplication::findOrFail($id);
            
            $validated = $request->validate([
                'status' => 'required|in:submitted,under_review,decision_pending,admitted,denied,waitlisted,withdrawn',
                'notes' => 'nullable|string|max:500'
            ]);
            
            $oldStatus = $application->status;
            
            // Update status
            $application->status = $validated['status'];
            $application->save();
            
            // Log status change
            DB::table('application_status_history')->insert([
                'application_id' => $id,
                'from_status' => $oldStatus,
                'to_status' => $validated['status'],
                'changed_by' => Auth::id(),
                'notes' => $validated['notes'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Add note if provided
            if (!empty($validated['notes'])) {
                ApplicationNote::create([
                    'application_id' => $id,
                    'note' => 'Status changed: ' . $validated['notes'],
                    'type' => 'status_change',
                    'created_by' => Auth::id()
                ]);
            }
            
            DB::commit();
            
            // Return JSON if AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status updated successfully',
                    'new_status' => $validated['status']
                ]);
            }
            
            return redirect()->back()->with('success', 'Status updated successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update status', [
                'application_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update status'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update status');
        }
    }

    /**
     * Add note to application (handles both regular and AJAX requests)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function addNote(Request $request, $id)
    {
        try {
            $application = AdmissionApplication::findOrFail($id);
            
            $validated = $request->validate([
                'note' => 'required|string|max:1000',
                'type' => 'nullable|in:general,review,decision,document,communication',
                'is_private' => 'boolean'
            ]);
            
            $note = ApplicationNote::create([
                'application_id' => $id,
                'note' => $validated['note'],
                'type' => $validated['type'] ?? 'general',
                'is_private' => $request->boolean('is_private'),
                'created_by' => Auth::id()
            ]);
            
            // If AJAX request, return JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Note added successfully',
                    'note' => [
                        'id' => $note->id,
                        'note' => $note->note,
                        'type' => $note->type,
                        'created_at' => $note->created_at->format('M d, Y H:i'),
                        'created_by' => Auth::user()->name
                    ]
                ]);
            }
            
            return redirect()->back()->with('success', 'Note added successfully');
            
        } catch (\Exception $e) {
            Log::error('Failed to add note', [
                'application_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add note'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to add note');
        }
    }

    /**
     * Helper Methods
     */

    /**
     * Apply filters to query.
     */
    private function applyFilters($query, Request $request)
    {
        // Status filter
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Program filter
        if ($request->has('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Term filter
        if ($request->has('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        // Application type filter
        if ($request->has('application_type')) {
            $query->where('application_type', $request->application_type);
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Review status filter
        if ($request->has('review_status')) {
            switch ($request->review_status) {
                case 'pending_review':
                    $query->whereDoesntHave('reviews');
                    break;
                case 'in_review':
                    $query->whereHas('reviews', function ($q) {
                        $q->where('status', 'in_progress');
                    });
                    break;
                case 'reviewed':
                    $query->whereHas('reviews', function ($q) {
                        $q->where('status', 'completed');
                    });
                    break;
            }
        }

        // Decision filter
        if ($request->has('decision')) {
            $query->where('decision', $request->decision);
        }
    }

    /**
     * Check if entrance exam is required
     * 
     * @param AdmissionApplication $application
     * @return bool
     */
    private function checkEntranceExamRequired($application): bool
    {
        // Check if program requires entrance exam
        $program = $application->program;
        
        if (!$program || !isset($program->entrance_exam_required) || !$program->entrance_exam_required) {
            return false;
        }
        
        // Get test scores safely
        $testScores = $application->test_scores ?? [];
        
        // For freshman (local students) - Check WASSCE
        if ($application->application_type === 'freshman') {
            if (isset($testScores['WASSCE'])) {
                // Check if WASSCE grades meet minimum requirements
                $wassce = $testScores['WASSCE'];
                
                // Convert grades to points for evaluation
                $gradePoints = [
                    'A1' => 1, 'B2' => 2, 'B3' => 3, 'C4' => 4, 
                    'C5' => 5, 'C6' => 6, 'D7' => 7, 'E8' => 8, 'F9' => 9
                ];
                
                // Check core subjects
                $coreSubjects = ['english', 'mathematics', 'science', 'social'];
                $totalPoints = 0;
                $subjectCount = 0;
                
                foreach ($coreSubjects as $subject) {
                    if (isset($wassce[$subject]) && isset($gradePoints[$wassce[$subject]])) {
                        $totalPoints += $gradePoints[$wassce[$subject]];
                        $subjectCount++;
                    }
                }
                
                // If average grade is C6 or better (6 points or less), no entrance exam required
                if ($subjectCount >= 2) { // At least 2 core subjects
                    $averagePoints = $totalPoints / $subjectCount;
                    if ($averagePoints <= 6) { // C6 or better average
                        return false; // No entrance exam needed
                    }
                }
            }
            
            return true; // Need entrance exam if no WASSCE or poor grades
        }
        
        // For international students - Check SAT/ACT
        if ($application->application_type === 'international') {
            // Check minimum scores for international tests
            $minimumScores = [
                'SAT' => 1200,
                'ACT' => 26
            ];
            
            foreach ($minimumScores as $test => $minScore) {
                if (isset($testScores[$test])) {
                    $score = $testScores[$test]['total'] ?? $testScores[$test]['composite'] ?? 0;
                    if ($score >= $minScore) {
                        return false; // Has qualifying score
                    }
                }
            }
            
            return true; // No qualifying scores found
        }
        
        // For graduate students - Check GRE
        if ($application->application_type === 'graduate') {
            if (isset($testScores['GRE']) && isset($testScores['GRE']['total'])) {
                if ($testScores['GRE']['total'] >= 300) {
                    return false; // Has qualifying GRE score
                }
            }
            return true; // Need entrance exam
        }
        
        // For transfer students - Usually no entrance exam required
        if ($application->application_type === 'transfer') {
            // Check if they have good college GPA
            if ($application->previous_gpa >= 3.0) {
                return false; // Good GPA, no exam needed
            }
            return true; // Poor GPA, need exam
        }
        
        return false; // Default: no exam required
    }

    /**
     * Get decision options for application
     * 
     * @param AdmissionApplication $application
     * @return array
     */
    private function getDecisionOptions($application): array
    {
        $options = [
            'admitted' => 'Admit',
            'conditional_admit' => 'Conditional Admit',
            'waitlisted' => 'Waitlist',
            'denied' => 'Deny',
            'deferred' => 'Defer to Next Term'
        ];
        
        // Filter options based on application status
        if ($application->decision && $application->decision !== 'deferred') {
            // If decision already made, only allow certain changes
            if ($application->decision === 'waitlisted') {
                return [
                    'admitted' => 'Admit from Waitlist',
                    'denied' => 'Deny from Waitlist'
                ];
            }
            return []; // No changes allowed
        }
        
        return $options;
    }

    /**
     * Get communication templates
     * 
     * @param AdmissionApplication $application
     * @return array
     */
    private function getCommunicationTemplates($application): array
    {
        $templates = [
            'missing_documents' => 'Missing Documents Reminder',
            'interview_invitation' => 'Interview Invitation',
            'additional_information' => 'Request for Additional Information',
            'application_received' => 'Application Received Confirmation',
            'status_update' => 'Application Status Update',
            'decision_notification' => 'Decision Notification',
            'enrollment_reminder' => 'Enrollment Deadline Reminder',
            'exam_registration' => 'Entrance Exam Registration Required'
        ];
        
        // Add WASSCE-specific template if needed
        if ($application->application_type === 'freshman') {
            $templates['wassce_results'] = 'WASSCE Results Request';
        }
        
        return $templates;
    }

    /**
     * Check if all reviews are complete
     * 
     * @param AdmissionApplication $application
     * @return void
     */
    private function checkReviewCompletion($application)
    {
        $pendingReviews = ApplicationReview::where('application_id', $application->id)
            ->where('status', 'pending')
            ->count();
        
        if ($pendingReviews === 0) {
            $completedReviews = ApplicationReview::where('application_id', $application->id)
                ->where('status', 'completed')
                ->count();
            
            if ($completedReviews > 0) {
                $application->status = 'review_complete';
                $application->save();
                
                // Notify decision makers
                if ($this->notificationService) {
                    $this->notificationService->notifyReviewComplete($application->id);
                }
            }
        }
    }

    /**
     * Create enrollment confirmation record
     * 
     * @param AdmissionApplication $application
     * @param array $validated
     * @return EnrollmentConfirmation
     */
    private function createEnrollmentRecord($application, $validated)
    {
        if (!$this->enrollmentService) {
            Log::warning('EnrollmentService not available');
            return null;
        }
        
        return EnrollmentConfirmation::create([
            'application_id' => $application->id,
            'enrollment_deadline' => now()->addDays(30),
            'deposit_deadline' => now()->addDays(30),
            'deposit_amount' => 500.00,
            'merit_scholarship_amount' => $validated['merit_scholarship'] ?? 0,
            'need_based_aid_amount' => $validated['need_based_aid'] ?? 0,
            'orientation_date' => $application->term ? $application->term->start_date->subWeeks(2) : now()->addMonths(2),
            'classes_start_date' => $application->term ? $application->term->start_date : now()->addMonths(2),
            'status' => 'pending'
        ]);
    }

    /**
     * Create entrance exam requirement
     * 
     * @param AdmissionApplication $application
     * @return void
     */
    private function createEntranceExamRequirement($application)
    {
        if (!$this->examService) {
            Log::warning('ExamService not available');
            return;
        }
        
        // Find or create entrance exam for this term
        $exam = $this->examService->getOrCreateTermExam($application->term_id);
        
        if ($exam) {
            // Register applicant for exam
            $this->examService->registerCandidate($exam->id, [
                'application_id' => $application->id,
                'candidate_name' => $application->first_name . ' ' . $application->last_name,
                'candidate_email' => $application->email,
                'candidate_phone' => $application->phone,
                'exam_language' => 'English',
                'requires_accommodation' => false
            ]);
            
            // Add note
            ApplicationNote::create([
                'application_id' => $application->id,
                'note' => 'Entrance exam registration required',
                'type' => 'exam',
                'created_by' => Auth::id()
            ]);
        }
    }

    /**
     * Generate decision letter PDF
     * 
     * @param AdmissionApplication $application
     * @return string
     */
    private function generateDecisionLetter($application)
    {
        try {
            $data = [
                'application' => $application,
                'program' => $application->program,
                'term' => $application->term,
                'decision_date' => now(),
                'academic_year' => $application->term ? $application->term->academic_year : '2025-2026',
                'university_name' => config('app.name', 'IntelliCampus University'),
                'registrar_name' => 'Dr. Jane Smith',
                'registrar_title' => 'Director of Admissions'
            ];
            
            // Check if view exists, if not use a simple template
            $viewName = 'admissions.letters.decision-letter';
            if (!view()->exists($viewName)) {
                // Create a simple HTML template
                $html = view('admissions.letters.simple-decision-letter', $data)->render();
            } else {
                $html = view($viewName, $data)->render();
            }
            
            $pdf = PDF::loadHTML($html);
            
            $filename = 'decision_letter_' . $application->application_number . '.pdf';
            $path = 'admission_letters/' . $application->id . '/' . $filename;
            
            \Storage::put($path, $pdf->output());
            
            return $path;
        } catch (Exception $e) {
            Log::error('Failed to generate decision letter', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
        ]);
        
        // Return a placeholder path
        return 'admission_letters/placeholder.pdf';
    }
}

/**
 * Get available reviewers
 * 
 * @return \Illuminate\Support\Collection
 */
private function getAvailableReviewers()
{
    return User::whereHas('roles', function($q) {
        $q->whereIn('name', ['admissions-officer', 'admissions-reviewer', 'faculty', 'admin']);
    })
    ->where('is_active', true)
    ->orderBy('name')
    ->get();
}

    /**
     * Get application statistics.
     */
    private function getApplicationStatistics(Request $request): array
    {
        $cacheKey = 'admin_app_stats_' . md5($request->fullUrl());
        
        return Cache::remember($cacheKey, 300, function () use ($request) {
            $baseQuery = AdmissionApplication::query();
            $this->applyFilters($baseQuery, $request);
            
            return [
                'total' => (clone $baseQuery)->count(),
                'submitted' => (clone $baseQuery)->where('status', 'submitted')->count(),
                'under_review' => (clone $baseQuery)->where('status', 'under_review')->count(),
                'admitted' => (clone $baseQuery)->where('decision', 'admit')->count(),
                'denied' => (clone $baseQuery)->where('decision', 'deny')->count(),
                'pending_decision' => (clone $baseQuery)->whereNull('decision')->where('status', '!=', 'draft')->count(),
                'today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
                'this_week' => (clone $baseQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            ];
        });
    }

    /**
     * Get application timeline.
     */
    private function getApplicationTimeline($application): array
    {
        $timeline = [];

        // Add status history
        foreach ($application->statusHistory as $history) {
            $timeline[] = [
                'date' => $history->created_at,
                'type' => 'status_change',
                'title' => 'Status Changed',
                'description' => "From {$history->from_status} to {$history->to_status}",
                'user' => $history->changedBy->name ?? 'System',
            ];
        }

        // Add reviews
        foreach ($application->reviews as $review) {
            if ($review->completed_at) {
                $timeline[] = [
                    'date' => $review->completed_at,
                    'type' => 'review',
                    'title' => 'Review Completed',
                    'description' => "{$review->review_stage} by {$review->reviewer->name}",
                    'user' => $review->reviewer->name,
                ];
            }
        }

        // Add communications
        foreach ($application->communications->take(5) as $comm) {
            $timeline[] = [
                'date' => $comm->created_at,
                'type' => 'communication',
                'title' => 'Communication Sent',
                'description' => $comm->subject,
                'user' => $comm->sender->name ?? 'System',
            ];
        }

        // Sort by date
        usort($timeline, function ($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return array_slice($timeline, 0, 20); // Limit to 20 most recent
    }

    /**
     * Get available actions for application.
     */
    private function getAvailableActions($application): array
    {
        $actions = [];

        switch ($application->status) {
            case 'submitted':
                $actions[] = ['action' => 'assign_reviewer', 'label' => 'Assign Reviewer'];
                $actions[] = ['action' => 'request_documents', 'label' => 'Request Documents'];
                $actions[] = ['action' => 'update_status', 'label' => 'Update Status'];
                break;

            case 'under_review':
                $actions[] = ['action' => 'view_reviews', 'label' => 'View Reviews'];
                $actions[] = ['action' => 'move_to_committee', 'label' => 'Move to Committee'];
                $actions[] = ['action' => 'make_decision', 'label' => 'Make Decision'];
                break;

            case 'committee_review':
                $actions[] = ['action' => 'schedule_interview', 'label' => 'Schedule Interview'];
                $actions[] = ['action' => 'make_decision', 'label' => 'Make Decision'];
                break;

            case 'decision_pending':
                $actions[] = ['action' => 'make_decision', 'label' => 'Make Decision'];
                break;

            case 'admitted':
                $actions[] = ['action' => 'send_offer', 'label' => 'Send Offer Letter'];
                $actions[] = ['action' => 'view_enrollment', 'label' => 'View Enrollment Status'];
                break;
        }

        // Common actions
        $actions[] = ['action' => 'add_note', 'label' => 'Add Note'];
        $actions[] = ['action' => 'send_communication', 'label' => 'Send Message'];
        $actions[] = ['action' => 'view_documents', 'label' => 'View Documents'];

        return $actions;
    }

    /**
     * Get similar applications.
     */
    private function getSimilarApplications($application): \Illuminate\Support\Collection
    {
        return AdmissionApplication::where('id', '!=', $application->id)
            ->where(function ($query) use ($application) {
                $query->where('program_id', $application->program_id)
                    ->orWhere('email', $application->email)
                    ->orWhere(function ($q) use ($application) {
                        $q->where('first_name', $application->first_name)
                          ->where('last_name', $application->last_name);
                    });
            })
            ->limit(5)
            ->get();
    }

    /**
     * Mark application as viewed.
     */
    private function markAsViewed($application)
    {
        try {
            ApplicationNote::create([
                'application_id' => $application->id,
                'note' => 'Application viewed',
                'type' => 'view',
                'is_private' => true,
                'created_by' => Auth::id(),
            ]);
        } catch (Exception $e) {
            // Silent fail for view tracking
            Log::debug('Failed to track view', ['application_id' => $application->id]);
        }
    }

    /**
     * Update application status (helper for bulk actions).
     */
    private function updateApplicationStatus($applicationId, $newStatus)
    {
        $application = AdmissionApplication::find($applicationId);
        if ($application) {
            $oldStatus = $application->status;
            $application->status = $newStatus;
            $application->save();

            ApplicationStatusHistory::create([
                'application_id' => $applicationId,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_by' => Auth::id(),
                'notes' => 'Bulk status update',
            ]);
        }
    }

    /**
     * Export selected applications.
     */
    private function exportApplications(array $applicationIds)
    {
        $applications = AdmissionApplication::whereIn('id', $applicationIds)
            ->with(['program', 'term'])
            ->get();

        $export = new ApplicationsExport($applications);
        $filename = 'selected_applications_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download($export, $filename);
    }

    /**
     * Get current term ID.
     */
    private function getCurrentTermId()
    {
        return AcademicTerm::where('is_current', true)->first()->id ?? null;
    }
}