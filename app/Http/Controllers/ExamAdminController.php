<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\EntranceExamService;
use App\Services\ExamConductService;
use App\Services\ExamEvaluationService;
use App\Services\ApplicationNotificationService;
use App\Models\EntranceExam;
use App\Models\ExamCenter;
use App\Models\ExamSession;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionPaper;
use App\Models\EntranceExamRegistration;
use App\Models\EntranceExamResult;
use App\Models\ExamAnswerKey;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExamRegistrationsExport;
use App\Exports\ExamResultsExport;
use App\Imports\ExamQuestionsImport;
use Carbon\Carbon;
use Exception;

class ExamAdminController extends Controller
{
    protected $examService;
    protected $conductService;
    protected $evaluationService;
    protected $notificationService;

    /**
     * Items per page for pagination
     */
    private const ITEMS_PER_PAGE = 25;

    /**
     * Exam status types
     */
    private const EXAM_STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
        'registration_open' => 'Registration Open',
        'registration_closed' => 'Registration Closed',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'results_pending' => 'Results Pending',
        'results_published' => 'Results Published',
        'archived' => 'Archived',
    ];

    /**
     * Exam types
     */
    private const EXAM_TYPES = [
        'entrance' => 'Entrance Exam',
        'placement' => 'Placement Test',
        'diagnostic' => 'Diagnostic Test',
        'scholarship' => 'Scholarship Exam',
        'transfer_credit' => 'Transfer Credit Exam',
        'exemption' => 'Exemption Test',
    ];

    /**
     * Delivery modes
     */
    private const DELIVERY_MODES = [
        'paper_based' => 'Paper Based',
        'computer_based' => 'Computer Based (CBT)',
        'online_proctored' => 'Online Proctored',
        'online_unproctored' => 'Online Unproctored',
        'hybrid' => 'Hybrid',
        'take_home' => 'Take Home',
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(
        EntranceExamService $examService,
        ExamConductService $conductService,
        ExamEvaluationService $evaluationService,
        ApplicationNotificationService $notificationService
    ) {
        $this->examService = $examService;
        $this->conductService = $conductService;
        $this->evaluationService = $evaluationService;
        $this->notificationService = $notificationService;
        
        // Middleware for exam administration
        $this->middleware(['auth', 'role:admin,exam_coordinator,registrar']);
    }

    /**
     * Display exams list with filters.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            // Build query with filters
            $query = EntranceExam::with([
                'term',
                'sessions',
                'registrations',
                'questions'
            ]);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('exam_type')) {
                $query->where('exam_type', $request->exam_type);
            }

            if ($request->filled('delivery_mode')) {
                $query->where('delivery_mode', $request->delivery_mode);
            }

            if ($request->filled('term_id')) {
                $query->where('term_id', $request->term_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('exam_name', 'like', "%{$search}%")
                      ->orWhere('exam_code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply date range filter
            if ($request->filled('date_from')) {
                $query->where('exam_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('exam_date', '<=', $request->date_to);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'exam_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Get statistics
            $statistics = $this->getExamStatistics();

            // Paginate results
            $exams = $query->paginate(self::ITEMS_PER_PAGE)
                ->appends($request->all());

            // Get filter options
            $terms = AcademicTerm::orderBy('start_date', 'desc')->pluck('name', 'id');
            $programs = AcademicProgram::where('is_active', true)->pluck('name', 'id');

            return view('exams.admin.index', compact(
                'exams',
                'statistics',
                'terms',
                'programs'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load exams list', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Failed to load exams.');
        }
    }

    /**
     * Show form to create new exam.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            $terms = AcademicTerm::where('is_active', true)
                ->orderBy('start_date', 'desc')
                ->pluck('name', 'id');
            
            $programs = AcademicProgram::where('is_active', true)
                ->orderBy('name')
                ->get();
            
            $centers = ExamCenter::where('is_active', true)
                ->orderBy('center_name')
                ->get();

            return view('exams.admin.create', compact(
                'terms',
                'programs',
                'centers'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load exam creation form', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('exams.admin.index')
                ->with('error', 'Failed to load creation form.');
        }
    }

    /**
     * Store new exam.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'exam_name' => 'required|string|max:200',
            'exam_type' => 'required|in:' . implode(',', array_keys(self::EXAM_TYPES)),
            'delivery_mode' => 'required|in:' . implode(',', array_keys(self::DELIVERY_MODES)),
            'term_id' => 'required|exists:academic_terms,id',
            'applicable_programs' => 'nullable|array',
            'applicable_programs.*' => 'exists:academic_programs,id',
            'applicable_application_types' => 'nullable|array',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:0',
            'duration_minutes' => 'required|integer|min:30',
            'total_questions' => 'required|integer|min:1',
            'sections' => 'nullable|json',
            'general_instructions' => 'nullable|string',
            'exam_rules' => 'nullable|string',
            'negative_marking' => 'boolean',
            'negative_mark_value' => 'nullable|numeric|min:0|max:1',
            'registration_start_date' => 'required|date',
            'registration_end_date' => 'required|date|after:registration_start_date',
            'exam_date' => 'nullable|date|after:registration_end_date',
            'exam_window_start' => 'nullable|date|after:registration_end_date',
            'exam_window_end' => 'nullable|date|after:exam_window_start',
            'result_publish_date' => 'nullable|date|after:exam_date',
            'show_detailed_results' => 'boolean',
            'allow_result_review' => 'boolean',
            'review_period_days' => 'nullable|integer|min:1|max:30',
        ]);

        DB::beginTransaction();

        try {
            // Create exam
            $exam = $this->examService->createExam($validated);

            // Create initial sessions if provided
            if ($request->has('sessions')) {
                foreach ($request->sessions as $sessionData) {
                    $this->examService->createSession($exam->id, $sessionData);
                }
            }

            DB::commit();

            // Clear cache
            Cache::forget('exam_statistics');
            Cache::tags(['exams'])->flush();

            return redirect()->route('exams.admin.show', $exam->id)
                ->with('success', 'Exam created successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create exam', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create exam. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display exam details.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $exam = EntranceExam::with([
                'term',
                'sessions.center',
                'sessions.seatAllocations',
                'registrations.application',
                'questions',
                'questionPapers',
                'results',
                'answerKeys'
            ])->findOrFail($id);

            // Get exam statistics
            $statistics = $this->getExamSpecificStatistics($exam);

            // Get session-wise data
            $sessionData = $this->getSessionWiseData($exam);

            // Get question analysis
            $questionAnalysis = $this->getQuestionAnalysis($exam);

            return view('exams.admin.show', compact(
                'exam',
                'statistics',
                'sessionData',
                'questionAnalysis'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load exam details', [
                'exam_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('exams.admin.index')
                ->with('error', 'Failed to load exam details.');
        }
    }

    /**
     * Show form to edit exam.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        try {
            $exam = EntranceExam::findOrFail($id);
            
            // Check if exam can be edited
            if (in_array($exam->status, ['in_progress', 'completed', 'results_published'])) {
                return redirect()->route('exams.admin.show', $id)
                    ->with('warning', 'This exam cannot be edited in its current status.');
            }

            $terms = AcademicTerm::where('is_active', true)
                ->orderBy('start_date', 'desc')
                ->pluck('name', 'id');
            
            $programs = AcademicProgram::where('is_active', true)
                ->orderBy('name')
                ->get();
            
            $centers = ExamCenter::where('is_active', true)
                ->orderBy('center_name')
                ->get();

            return view('exams.admin.edit', compact(
                'exam',
                'terms',
                'programs',
                'centers'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load exam edit form', [
                'exam_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('exams.admin.index')
                ->with('error', 'Failed to load edit form.');
        }
    }

    /**
     * Update exam.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $exam = EntranceExam::findOrFail($id);

        // Check if exam can be updated
        if (in_array($exam->status, ['in_progress', 'completed', 'results_published'])) {
            return redirect()->route('exams.admin.show', $id)
                ->with('warning', 'This exam cannot be updated in its current status.');
        }

        $validated = $request->validate([
            'exam_name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'general_instructions' => 'nullable|string',
            'exam_rules' => 'nullable|string',
            'total_marks' => 'required|integer|min:1',
            'passing_marks' => 'required|integer|min:0',
            'duration_minutes' => 'required|integer|min:30',
            'registration_end_date' => 'required|date',
            'exam_date' => 'nullable|date|after:registration_end_date',
            'result_publish_date' => 'nullable|date|after:exam_date',
        ]);

        DB::beginTransaction();

        try {
            // Update exam
            $exam->update($validated);

            // Log the update
            Log::info('Exam updated', [
                'exam_id' => $exam->id,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            // Clear cache
            Cache::forget("exam_{$id}");
            Cache::tags(['exams'])->flush();

            return redirect()->route('exams.admin.show', $id)
                ->with('success', 'Exam updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update exam', [
                'exam_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update exam.')
                ->withInput();
        }
    }

    /**
     * Manage exam centers.
     *
     * @param int $examId
     * @return \Illuminate\View\View
     */
    public function manageCenters($examId)
    {
        try {
            $exam = EntranceExam::with('sessions.center')->findOrFail($examId);
            
            $allCenters = ExamCenter::where('is_active', true)
                ->orderBy('center_name')
                ->get();
            
            $assignedCenters = $exam->sessions->pluck('center_id')->unique();

            return view('exams.admin.manage-centers', compact(
                'exam',
                'allCenters',
                'assignedCenters'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load center management', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('exams.admin.show', $examId)
                ->with('error', 'Failed to load center management.');
        }
    }

    /**
     * Manage exam sessions.
     *
     * @param int $examId
     * @return \Illuminate\View\View
     */
    public function manageSessions($examId)
    {
        try {
            $exam = EntranceExam::with([
                'sessions.center',
                'sessions.seatAllocations',
                'sessions.registrations'
            ])->findOrFail($examId);
            
            $centers = ExamCenter::where('is_active', true)
                ->orderBy('center_name')
                ->get();
            
            $proctors = User::role(['faculty', 'staff', 'proctor'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return view('exams.admin.manage-sessions', compact(
                'exam',
                'centers',
                'proctors'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load session management', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('exams.admin.show', $examId)
                ->with('error', 'Failed to load session management.');
        }
    }

    /**
     * View registrations for an exam.
     *
     * @param Request $request
     * @param int $examId
     * @return \Illuminate\View\View
     */
    public function viewRegistrations(Request $request, $examId)
    {
        try {
            $exam = EntranceExam::findOrFail($examId);
            
            $query = EntranceExamRegistration::with([
                'application',
                'session.center',
                'seatAllocation',
                'response',
                'result'
            ])->where('exam_id', $examId);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('registration_status', $request->status);
            }

            if ($request->filled('session_id')) {
                $query->whereHas('seatAllocation', function ($q) use ($request) {
                    $q->where('session_id', $request->session_id);
                });
            }

            if ($request->filled('center_id')) {
                $query->whereHas('seatAllocation', function ($q) use ($request) {
                    $q->where('center_id', $request->center_id);
                });
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('registration_number', 'like', "%{$search}%")
                      ->orWhere('hall_ticket_number', 'like', "%{$search}%")
                      ->orWhere('candidate_name', 'like', "%{$search}%")
                      ->orWhere('candidate_email', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Get registration statistics
            $statistics = $this->getRegistrationStatistics($examId);

            // Paginate results
            $registrations = $query->paginate(self::ITEMS_PER_PAGE)
                ->appends($request->all());

            return view('exams.admin.registrations', compact(
                'exam',
                'registrations',
                'statistics'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load exam registrations', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('exams.admin.show', $examId)
                ->with('error', 'Failed to load registrations.');
        }
    }

    /**
     * Generate reports for an exam.
     *
     * @param Request $request
     * @param int $examId
     * @return mixed
     */
    public function generateReports(Request $request, $examId)
    {
        try {
            $exam = EntranceExam::findOrFail($examId);
            
            $reportType = $request->get('type', 'summary');
            
            switch ($reportType) {
                case 'registrations':
                    return $this->generateRegistrationReport($exam);
                    
                case 'attendance':
                    return $this->generateAttendanceReport($exam);
                    
                case 'results':
                    return $this->generateResultsReport($exam);
                    
                case 'analytics':
                    return $this->generateAnalyticsReport($exam);
                    
                case 'summary':
                default:
                    return $this->generateSummaryReport($exam);
            }

        } catch (Exception $e) {
            Log::error('Failed to generate report', [
                'exam_id' => $examId,
                'report_type' => $reportType ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to generate report.');
        }
    }

    /**
     * Delete exam (soft delete).
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $exam = EntranceExam::findOrFail($id);
            
            // Check if exam can be deleted
            if ($exam->registrations()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete exam with existing registrations.');
            }

            $exam->delete();

            // Clear cache
            Cache::forget("exam_{$id}");
            Cache::tags(['exams'])->flush();

            return redirect()->route('exams.admin.index')
                ->with('success', 'Exam deleted successfully.');

        } catch (Exception $e) {
            Log::error('Failed to delete exam', [
                'exam_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete exam.');
        }
    }

    /**
     * Publish exam for registration.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function publish($id)
    {
        try {
            $exam = EntranceExam::findOrFail($id);
            
            // Validate exam is ready for publication
            if (!$exam->sessions()->count()) {
                return redirect()->back()
                    ->with('error', 'Cannot publish exam without sessions.');
            }

            if (!$exam->questions()->count() && $exam->delivery_mode !== 'paper_based') {
                return redirect()->back()
                    ->with('error', 'Cannot publish exam without questions.');
            }

            $exam->status = 'published';
            $exam->save();

            // Send notifications to eligible candidates
            $this->examService->notifyEligibleCandidates($exam->id);

            return redirect()->route('exams.admin.show', $id)
                ->with('success', 'Exam published successfully.');

        } catch (Exception $e) {
            Log::error('Failed to publish exam', [
                'exam_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to publish exam.');
        }
    }

    /**
     * Open registration for exam.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function openRegistration($id)
    {
        try {
            $exam = EntranceExam::findOrFail($id);
            
            if ($exam->status !== 'published') {
                return redirect()->back()
                    ->with('error', 'Exam must be published before opening registration.');
            }

            $exam->status = 'registration_open';
            $exam->save();

            // Send notification about registration opening
            $this->notificationService->sendBulkNotification(
                'exam_registration_open',
                ['exam' => $exam]
            );

            return redirect()->route('exams.admin.show', $id)
                ->with('success', 'Registration opened successfully.');

        } catch (Exception $e) {
            Log::error('Failed to open registration', [
                'exam_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to open registration.');
        }
    }

    /**
     * Close registration for exam.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function closeRegistration($id)
    {
        try {
            $exam = EntranceExam::findOrFail($id);
            
            $exam->status = 'registration_closed';
            $exam->save();

            // Generate seat allocations
            $this->examService->generateSeatAllocations($exam->id);

            // Send hall tickets
            $this->examService->sendHallTickets($exam->id);

            return redirect()->route('exams.admin.show', $id)
                ->with('success', 'Registration closed and seat allocation completed.');

        } catch (Exception $e) {
            Log::error('Failed to close registration', [
                'exam_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to close registration.');
        }
    }

    /**
     * Private helper methods
     */

    /**
     * Get exam statistics.
     */
    private function getExamStatistics(): array
    {
        return Cache::remember('exam_statistics', 3600, function () {
            return [
                'total_exams' => EntranceExam::count(),
                'upcoming_exams' => EntranceExam::where('exam_date', '>', now())->count(),
                'in_progress' => EntranceExam::where('status', 'in_progress')->count(),
                'total_registrations' => EntranceExamRegistration::count(),
                'results_pending' => EntranceExam::where('status', 'results_pending')->count(),
            ];
        });
    }

    /**
     * Get exam-specific statistics.
     */
    private function getExamSpecificStatistics(EntranceExam $exam): array
    {
        return [
            'total_registrations' => $exam->registrations()->count(),
            'confirmed_registrations' => $exam->registrations()
                ->where('registration_status', 'confirmed')->count(),
            'sessions_count' => $exam->sessions()->count(),
            'centers_count' => $exam->sessions()->distinct('center_id')->count(),
            'questions_count' => $exam->questions()->count(),
            'hall_tickets_generated' => $exam->registrations()
                ->whereNotNull('hall_ticket_number')->count(),
            'attendance_marked' => $exam->registrations()
                ->whereHas('seatAllocation', function ($q) {
                    $q->where('attendance_marked', true);
                })->count(),
            'results_published' => $exam->results()
                ->where('is_published', true)->count(),
        ];
    }

    /**
     * Get session-wise data.
     */
    private function getSessionWiseData(EntranceExam $exam): Collection
    {
        return $exam->sessions->map(function ($session) {
            return [
                'session' => $session,
                'registrations' => $session->seatAllocations()->count(),
                'attendance' => $session->seatAllocations()
                    ->where('attendance_marked', true)->count(),
                'capacity_utilization' => $session->capacity > 0
                    ? round(($session->registered_count / $session->capacity) * 100, 2)
                    : 0,
            ];
        });
    }

    /**
     * Get question analysis.
     */
    private function getQuestionAnalysis(EntranceExam $exam): array
    {
        if (!$exam->questions()->count()) {
            return [];
        }

        $questions = $exam->questions;
        
        return [
            'total_questions' => $questions->count(),
            'by_difficulty' => $questions->groupBy('difficulty_level')
                ->map->count(),
            'by_subject' => $questions->groupBy('subject')
                ->map->count(),
            'by_type' => $questions->groupBy('question_type')
                ->map->count(),
            'total_marks' => $questions->sum('marks'),
        ];
    }

    /**
     * Get registration statistics.
     */
    private function getRegistrationStatistics(int $examId): array
    {
        $registrations = EntranceExamRegistration::where('exam_id', $examId);
        
        return [
            'total' => $registrations->count(),
            'confirmed' => $registrations->where('registration_status', 'confirmed')->count(),
            'pending' => $registrations->where('registration_status', 'pending')->count(),
            'cancelled' => $registrations->where('registration_status', 'cancelled')->count(),
            'fee_paid' => $registrations->where('fee_paid', true)->count(),
            'hall_tickets_generated' => $registrations->whereNotNull('hall_ticket_number')->count(),
        ];
    }

    /**
     * Generate registration report.
     */
    private function generateRegistrationReport(EntranceExam $exam)
    {
        $filename = "exam_registrations_{$exam->exam_code}_" . date('Y-m-d') . '.xlsx';
        
        return Excel::download(
            new ExamRegistrationsExport($exam),
            $filename
        );
    }

    /**
     * Generate attendance report.
     */
    private function generateAttendanceReport(EntranceExam $exam)
    {
        $sessions = $exam->sessions()->with([
            'seatAllocations.registration',
            'center'
        ])->get();

        $data = [];
        foreach ($sessions as $session) {
            foreach ($session->seatAllocations as $allocation) {
                $data[] = [
                    'Session' => $session->session_code,
                    'Center' => $session->center->center_name,
                    'Registration Number' => $allocation->registration->registration_number,
                    'Hall Ticket' => $allocation->registration->hall_ticket_number,
                    'Seat Number' => $allocation->seat_number,
                    'Attendance' => $allocation->attendance_marked ? 'Present' : 'Absent',
                    'Check-in Time' => $allocation->check_in_time,
                ];
            }
        }

        return Excel::download(
            new \App\Exports\GenericExport($data),
            "attendance_{$exam->exam_code}_" . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Generate results report.
     */
    private function generateResultsReport(EntranceExam $exam)
    {
        return Excel::download(
            new ExamResultsExport($exam),
            "results_{$exam->exam_code}_" . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Generate analytics report.
     */
    private function generateAnalyticsReport(EntranceExam $exam)
    {
        $analytics = [
            'exam_details' => $exam->toArray(),
            'statistics' => $this->getExamSpecificStatistics($exam),
            'session_data' => $this->getSessionWiseData($exam),
            'question_analysis' => $this->getQuestionAnalysis($exam),
            'performance_metrics' => $this->evaluationService->getPerformanceMetrics($exam->id),
        ];

        $pdf = \PDF::loadView('exams.reports.analytics', compact('analytics', 'exam'));
        
        return $pdf->download("analytics_{$exam->exam_code}_" . date('Y-m-d') . '.pdf');
    }

    /**
     * Generate summary report.
     */
    private function generateSummaryReport(EntranceExam $exam)
    {
        $summary = [
            'exam' => $exam,
            'statistics' => $this->getExamSpecificStatistics($exam),
            'sessions' => $this->getSessionWiseData($exam),
            'top_performers' => $exam->results()
                ->where('is_published', true)
                ->orderBy('overall_rank')
                ->limit(10)
                ->with('registration')
                ->get(),
        ];

        $pdf = \PDF::loadView('exams.reports.summary', compact('summary'));
        
        return $pdf->download("summary_{$exam->exam_code}_" . date('Y-m-d') . '.pdf');
    }
}