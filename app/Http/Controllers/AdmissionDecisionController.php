<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AdmissionDecisionService;
use App\Services\ApplicationService;
use App\Services\ApplicationNotificationService;
use App\Services\EnrollmentConfirmationService;
use App\Services\InterviewSchedulingService;
use App\Services\WaitlistManagementService;
use App\Models\AdmissionApplication;
use App\Models\ApplicationReview;
use App\Models\AdmissionWaitlist;
use App\Models\ApplicationNote;
use App\Models\ApplicationStatusHistory;
use App\Models\EnrollmentConfirmation;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdmissionDecisionsExport;
use Carbon\Carbon;
use Exception;

class AdmissionDecisionController extends Controller
{
    protected $decisionService;
    protected $applicationService;
    protected $notificationService;
    protected $enrollmentService;
    protected $interviewService;
    protected $waitlistService;

    /**
     * Decision types
     */
    private const DECISION_TYPES = [
        'admit' => 'Admit',
        'conditional_admit' => 'Conditional Admit',
        'waitlist' => 'Waitlist',
        'deny' => 'Deny',
        'defer' => 'Defer',
    ];

    /**
     * Decision filters
     */
    private const DECISION_FILTERS = [
        'pending' => 'Pending Decision',
        'reviewed' => 'Review Complete',
        'committee' => 'In Committee',
        'decided' => 'Decision Made',
        'released' => 'Decision Released',
    ];

    /**
     * Bulk action limits
     */
    private const BULK_ACTION_LIMIT = 100;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        AdmissionDecisionService $decisionService,
        ApplicationService $applicationService,
        ApplicationNotificationService $notificationService,
        EnrollmentConfirmationService $enrollmentService,
        InterviewSchedulingService $interviewService,
        WaitlistManagementService $waitlistService
    ) {
        $this->decisionService = $decisionService;
        $this->applicationService = $applicationService;
        $this->notificationService = $notificationService;
        $this->enrollmentService = $enrollmentService;
        $this->interviewService = $interviewService;
        $this->waitlistService = $waitlistService;
        
        // Middleware for decision-making authority
        $this->middleware(['auth', 'role:admin,admissions_director,registrar,dean']);
    }

    /**
     * Display pending decisions dashboard.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function pendingDecisions(Request $request)
    {
        try {
            // Build query for applications pending decision
            $query = AdmissionApplication::with([
                'program',
                'term',
                'reviews',
                'documents',
                'interviews',
                'waitlist'
            ])
            ->whereIn('status', ['committee_review', 'decision_pending', 'under_review'])
            ->whereNull('decision');

            // Apply filters
            if ($request->has('program_id')) {
                $query->where('program_id', $request->program_id);
            }

            if ($request->has('term_id')) {
                $query->where('term_id', $request->term_id);
            }

            if ($request->has('review_complete')) {
                $query->whereHas('reviews', function ($q) {
                    $q->where('status', 'completed')
                      ->groupBy('application_id')
                      ->havingRaw('COUNT(*) >= 2'); // At least 2 complete reviews
                });
            }

            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }

            // Sort by priority and submission date
            $query->orderByRaw("
                CASE 
                    WHEN status = 'decision_pending' THEN 1
                    WHEN status = 'committee_review' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('submitted_at', 'asc');

            // Get statistics
            $statistics = $this->getDecisionStatistics($request);

            // Paginate results
            $applications = $query->paginate(25)->appends($request->all());

            // Get filter options
            $programs = AcademicProgram::where('is_active', true)->pluck('name', 'id');
            $terms = AcademicTerm::orderBy('start_date', 'desc')->pluck('name', 'id');

            // Get committee members
            $committeeMembers = User::role(['admissions_officer', 'faculty', 'dean'])
                ->where('is_active', true)
                ->pluck('name', 'id');

            return view('admissions.decisions.pending', compact(
                'applications',
                'statistics',
                'programs',
                'terms',
                'committeeMembers'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load pending decisions', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('admin.dashboard')
                ->with('error', 'Failed to load pending decisions.');
        }
    }

    /**
     * Show decision form for an application.
     *
     * @param int $applicationId
     * @return \Illuminate\View\View
     */
    public function makeDecision($applicationId)
    {
        try {
            $application = AdmissionApplication::with([
                'program',
                'term',
                'reviews.reviewer',
                'documents',
                'interviews.interviewer',
                'checklistItems',
                'notes',
                'statusHistory'
            ])->findOrFail($applicationId);

            // Check if user can make decision
            if (!$this->canMakeDecision($application)) {
                return redirect()->route('admissions.decisions.pending')
                    ->with('error', 'You do not have permission to make decisions for this application.');
            }

            // Calculate review summary
            $reviewSummary = $this->calculateReviewSummary($application);

            // Get decision options based on application status
            $decisionOptions = $this->getDecisionOptions($application);

            // Get waitlist information if applicable
            $waitlistInfo = null;
            if ($application->program) {
                $waitlistInfo = $this->waitlistService->getWaitlistInfo(
                    $application->program_id,
                    $application->term_id
                );
            }

            // Get conditional admission options
            $conditionalOptions = $this->getConditionalAdmissionOptions();

            // Get previous decisions for similar applications (for consistency)
            $similarDecisions = $this->getSimilarApplicationDecisions($application);

            return view('admissions.decisions.make', compact(
                'application',
                'reviewSummary',
                'decisionOptions',
                'waitlistInfo',
                'conditionalOptions',
                'similarDecisions'
            ));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admissions.decisions.pending')
                ->with('error', 'Application not found.');
        } catch (Exception $e) {
            Log::error('Failed to load decision form', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admissions.decisions.pending')
                ->with('error', 'Failed to load decision form.');
        }
    }

    /**
     * Process admission decision.
     *
     * @param Request $request
     * @param int $applicationId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeDecision(Request $request, $applicationId)
    {
        $validated = $request->validate([
            'decision' => 'required|in:' . implode(',', array_keys(self::DECISION_TYPES)),
            'decision_reason' => 'required|string|max:2000',
            'admission_conditions' => 'nullable|string|max:2000',
            'waitlist_rank' => 'nullable|integer|min:1',
            'notification_method' => 'required|in:email,letter,both',
            'release_immediately' => 'boolean',
            'internal_notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);

            // Check permission
            if (!$this->canMakeDecision($application)) {
                throw new Exception('Unauthorized to make decision for this application.');
            }

            // Make the decision
            $decision = $this->decisionService->makeDecision(
                $applicationId,
                $validated['decision'],
                $validated['decision_reason']
            );

            // Handle conditional admission
            if ($validated['decision'] === 'conditional_admit' && !empty($validated['admission_conditions'])) {
                $this->decisionService->conditionalAdmission($applicationId, $validated['admission_conditions']);
            }

            // Handle waitlist
            if ($validated['decision'] === 'waitlist' && !empty($validated['waitlist_rank'])) {
                $this->waitlistService->addToWaitlist($applicationId, $validated['waitlist_rank']);
            }

            // Add internal notes
            if (!empty($validated['internal_notes'])) {
                ApplicationNote::create([
                    'application_id' => $applicationId,
                    'note' => $validated['internal_notes'],
                    'type' => 'decision',
                    'created_by' => Auth::id(),
                ]);
            }

            // Generate decision letter
            $letterPath = $this->decisionService->generateDecisionLetter($applicationId);

            // Send notification if requested
            if ($validated['release_immediately']) {
                $this->notificationService->sendDecisionNotification(
                    $applicationId,
                    $validated['notification_method']
                );
                
                $application->decision_released_at = now();
                $application->save();
            }

            // Create enrollment confirmation record if admitted
            if (in_array($validated['decision'], ['admit', 'conditional_admit'])) {
                $this->enrollmentService->createEnrollmentRecord($applicationId);
            }

            DB::commit();

            // Clear cache
            Cache::forget("application_{$applicationId}");
            Cache::forget("decision_stats_{$application->term_id}_{$application->program_id}");

            return redirect()->route('admissions.decisions.view', $applicationId)
                ->with('success', 'Decision recorded successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to process decision', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to process decision. Please try again.')
                ->withInput();
        }
    }

    /**
     * Process bulk decisions.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDecisions(Request $request)
    {
        $validated = $request->validate([
            'application_ids' => 'required|array|max:' . self::BULK_ACTION_LIMIT,
            'application_ids.*' => 'integer|exists:admission_applications,id',
            'decision' => 'required|in:' . implode(',', array_keys(self::DECISION_TYPES)),
            'decision_reason' => 'required|string|max:1000',
            'release_immediately' => 'boolean',
        ]);

        DB::beginTransaction();

        try {
            $results = $this->decisionService->bulkDecisions([
                'application_ids' => $validated['application_ids'],
                'decision' => $validated['decision'],
                'reason' => $validated['decision_reason'],
            ]);

            // Send notifications if requested
            if ($validated['release_immediately']) {
                foreach ($validated['application_ids'] as $appId) {
                    $this->notificationService->sendDecisionNotification($appId, 'email');
                }
            }

            DB::commit();

            // Clear relevant caches
            foreach ($validated['application_ids'] as $appId) {
                Cache::forget("application_{$appId}");
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully processed {$results['success']} decisions.",
                'results' => $results,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Bulk decision processing failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk decisions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate decision letters.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function generateLetters(Request $request)
    {
        $validated = $request->validate([
            'application_ids' => 'required|array',
            'application_ids.*' => 'integer|exists:admission_applications,id',
            'letter_type' => 'required|in:decision,enrollment,all',
        ]);

        try {
            $letters = [];

            foreach ($validated['application_ids'] as $appId) {
                $application = AdmissionApplication::find($appId);
                
                if (!$application->decision) {
                    continue;
                }

                // Generate appropriate letter
                $letterPath = $this->decisionService->generateDecisionLetter($appId);
                
                if ($letterPath) {
                    $letters[] = [
                        'application_id' => $appId,
                        'path' => $letterPath,
                        'filename' => basename($letterPath),
                    ];
                }
            }

            if (count($letters) === 1) {
                // Single letter - download directly
                return response()->download(storage_path('app/' . $letters[0]['path']));
            } else {
                // Multiple letters - create zip
                $zipPath = $this->createLettersZip($letters);
                return response()->download($zipPath)->deleteFileAfterSend();
            }

        } catch (Exception $e) {
            Log::error('Failed to generate letters', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to generate letters.');
        }
    }

    /**
     * Manage waitlist.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function manageWaitlist(Request $request)
    {
        try {
            $query = AdmissionWaitlist::with(['application.program', 'application.term'])
                ->where('status', 'active');

            // Apply filters
            if ($request->has('program_id')) {
                $query->whereHas('application', function ($q) use ($request) {
                    $q->where('program_id', $request->program_id);
                });
            }

            if ($request->has('term_id')) {
                $query->where('term_id', $request->term_id);
            }

            // Sort by rank
            $query->orderBy('rank');

            $waitlistEntries = $query->paginate(50)->appends($request->all());

            // Get waitlist statistics
            $statistics = $this->waitlistService->getWaitlistStatistics(
                $request->program_id,
                $request->term_id
            );

            // Get filter options
            $programs = AcademicProgram::where('is_active', true)->pluck('name', 'id');
            $terms = AcademicTerm::orderBy('start_date', 'desc')->pluck('name', 'id');

            return view('admissions.decisions.waitlist', compact(
                'waitlistEntries',
                'statistics',
                'programs',
                'terms'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load waitlist', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('admissions.decisions.pending')
                ->with('error', 'Failed to load waitlist.');
        }
    }

    /**
     * Review admission appeal.
     *
     * @param int $applicationId
     * @return \Illuminate\View\View
     */
    public function appealReview($applicationId)
    {
        try {
            $application = AdmissionApplication::with([
                'program',
                'term',
                'reviews',
                'documents',
                'notes',
                'statusHistory'
            ])->findOrFail($applicationId);

            // Check if appeal exists
            $appeal = $application->appeal; // Assuming appeal relationship exists
            
            if (!$appeal) {
                return redirect()->route('admissions.decisions.view', $applicationId)
                    ->with('error', 'No appeal found for this application.');
            }

            // Get original decision information
            $originalDecision = [
                'decision' => $application->decision,
                'date' => $application->decision_date,
                'reason' => $application->decision_reason,
                'made_by' => $application->decisionMaker,
            ];

            // Get appeal review options
            $appealOptions = [
                'uphold' => 'Uphold Original Decision',
                'overturn' => 'Overturn Decision',
                'modify' => 'Modify Decision',
                'defer' => 'Defer for Further Review',
            ];

            return view('admissions.decisions.appeal', compact(
                'application',
                'appeal',
                'originalDecision',
                'appealOptions'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load appeal review', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admissions.decisions.pending')
                ->with('error', 'Failed to load appeal review.');
        }
    }

    /**
     * View decision details.
     *
     * @param int $applicationId
     * @return \Illuminate\View\View
     */
    public function viewDecision($applicationId)
    {
        try {
            $application = AdmissionApplication::with([
                'program',
                'term',
                'decisionMaker',
                'enrollmentConfirmation',
                'waitlist',
                'communications'
            ])->findOrFail($applicationId);

            if (!$application->decision) {
                return redirect()->route('admissions.decisions.make', $applicationId)
                    ->with('info', 'No decision has been made for this application yet.');
            }

            // Get decision history
            $decisionHistory = ApplicationStatusHistory::where('application_id', $applicationId)
                ->whereIn('to_status', ['admitted', 'denied', 'waitlisted', 'deferred'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Get enrollment status if admitted
            $enrollmentStatus = null;
            if ($application->enrollmentConfirmation) {
                $enrollmentStatus = [
                    'confirmed' => $application->enrollment_confirmed,
                    'deposit_paid' => $application->enrollment_deposit_paid,
                    'deadline' => $application->enrollment_deadline,
                ];
            }

            return view('admissions.decisions.view', compact(
                'application',
                'decisionHistory',
                'enrollmentStatus'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load decision details', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admissions.decisions.pending')
                ->with('error', 'Failed to load decision details.');
        }
    }

    /**
     * Helper Methods
     */

    /**
     * Check if user can make decision for application.
     */
    private function canMakeDecision(AdmissionApplication $application): bool
    {
        $user = Auth::user();
        
        // Admin and admissions director can make any decision
        if ($user->hasRole(['admin', 'admissions_director', 'registrar'])) {
            return true;
        }

        // Dean can make decisions for their programs
        if ($user->hasRole('dean')) {
            // Check if user is dean of the program's college
            return $application->program && 
                   $application->program->department &&
                   $application->program->department->college_id === $user->college_id;
        }

        // Department head for department programs
        if ($user->hasRole('department_head')) {
            return $application->program &&
                   $application->program->department_id === $user->department_id;
        }

        return false;
    }

    /**
     * Calculate review summary.
     */
    private function calculateReviewSummary(AdmissionApplication $application): array
    {
        $reviews = $application->reviews->where('status', 'completed');
        
        if ($reviews->isEmpty()) {
            return [
                'total_reviews' => 0,
                'average_rating' => 0,
                'recommendations' => [],
            ];
        }

        $totalRating = 0;
        $recommendations = [];

        foreach ($reviews as $review) {
            $totalRating += $review->overall_rating ?? 0;
            
            if ($review->recommendation) {
                $recommendations[$review->recommendation] = 
                    ($recommendations[$review->recommendation] ?? 0) + 1;
            }
        }

        return [
            'total_reviews' => $reviews->count(),
            'average_rating' => round($totalRating / $reviews->count(), 2),
            'recommendations' => $recommendations,
            'unanimous' => count($recommendations) === 1,
        ];
    }

    /**
     * Get decision options based on application.
     */
    private function getDecisionOptions(AdmissionApplication $application): array
    {
        $options = self::DECISION_TYPES;

        // Remove defer option if already deferred once
        if ($application->statusHistory()->where('to_status', 'deferred')->exists()) {
            unset($options['defer']);
        }

        // Add conditional admit only for certain programs
        if ($application->program && !$application->program->allows_conditional_admission) {
            unset($options['conditional_admit']);
        }

        return $options;
    }

    /**
     * Get conditional admission options.
     */
    private function getConditionalAdmissionOptions(): array
    {
        return [
            'english_proficiency' => 'English Proficiency Requirement',
            'final_transcript' => 'Final Official Transcript Required',
            'minimum_gpa' => 'Maintain Minimum GPA',
            'prerequisite_courses' => 'Complete Prerequisite Courses',
            'test_scores' => 'Submit Test Scores',
            'other' => 'Other (Specify)',
        ];
    }

    /**
     * Get similar application decisions.
     */
    private function getSimilarApplicationDecisions(AdmissionApplication $application): array
    {
        return AdmissionApplication::where('program_id', $application->program_id)
            ->where('term_id', $application->term_id)
            ->whereNotNull('decision')
            ->where('id', '!=', $application->id)
            ->whereBetween('previous_gpa', [
                max(0, $application->previous_gpa - 0.3),
                min(4.0, $application->previous_gpa + 0.3)
            ])
            ->select('decision', DB::raw('COUNT(*) as count'))
            ->groupBy('decision')
            ->get()
            ->pluck('count', 'decision')
            ->toArray();
    }

    /**
     * Get decision statistics.
     */
    private function getDecisionStatistics(Request $request): array
    {
        $query = AdmissionApplication::query();

        if ($request->has('term_id')) {
            $query->where('term_id', $request->term_id);
        }

        if ($request->has('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        return [
            'total_pending' => (clone $query)->whereNull('decision')->count(),
            'ready_for_decision' => (clone $query)
                ->whereNull('decision')
                ->whereHas('reviews', function ($q) {
                    $q->where('status', 'completed');
                })
                ->count(),
            'in_committee' => (clone $query)->where('status', 'committee_review')->count(),
            'decisions_today' => (clone $query)
                ->whereDate('decision_date', today())
                ->count(),
            'admitted' => (clone $query)->where('decision', 'admit')->count(),
            'denied' => (clone $query)->where('decision', 'deny')->count(),
            'waitlisted' => (clone $query)->where('decision', 'waitlist')->count(),
        ];
    }

    /**
     * Create zip file of letters.
     */
    private function createLettersZip(array $letters): string
    {
        $zip = new \ZipArchive();
        $zipPath = storage_path('app/temp/letters_' . time() . '.zip');
        
        if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
            foreach ($letters as $letter) {
                $zip->addFile(
                    storage_path('app/' . $letter['path']),
                    $letter['filename']
                );
            }
            $zip->close();
        }

        return $zipPath;
    }
}