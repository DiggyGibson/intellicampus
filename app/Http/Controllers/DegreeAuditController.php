<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\DegreeAuditReport;
use App\Models\DegreeRequirement;
use App\Models\ProgramRequirement;
use App\Models\StudentDegreeProgress;
use App\Models\AcademicProgram;
use App\Services\DegreeAudit\DegreeAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class DegreeAuditController extends Controller
{
    protected $auditService;

    public function __construct(DegreeAuditService $auditService)
    {
        $this->auditService = $auditService;
        
        // Apply middleware
        $this->middleware('auth');
        $this->middleware('role:student')->only(['myAudit', 'myProgress']);
        $this->middleware('role:advisor,registrar,admin')->only(['studentAudit', 'clearRequirement']);
    }

    /**
     * Display the degree audit dashboard
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();
        $student = $this->getStudentRecord($request);
        
        if (!$student) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Student record not found'], 404);
            }
            return view('degree-audit.error', ['message' => 'Student record not found']);
        }

        // Check permissions
        if (!$this->canViewAudit($student)) {
            abort(403, 'Unauthorized to view this audit');
        }

        // Get or generate audit
        $auditReport = $this->getOrGenerateAudit($student);
        
        // Get student progress
        $progress = StudentDegreeProgress::with(['requirement.category', 'programRequirement'])
            ->where('student_id', $student->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'student' => $student,
                'audit' => $this->formatAuditResponse($auditReport, $student),
                'progress' => $progress
            ]);
        }

        return view('degree-audit.index', compact('student', 'auditReport', 'progress'));
    }

    /**
     * Display degree audit for current student
     */
    public function myAudit(): View
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return view('degree-audit.error', ['message' => 'Student record not found']);
        }

        $auditReport = $this->getOrGenerateAudit($student);
        $progress = StudentDegreeProgress::with(['requirement.category', 'programRequirement'])
            ->where('student_id', $student->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('degree-audit.my-audit', compact('student', 'auditReport', 'progress'));
    }

    /**
     * Display degree audit for a specific student (advisor/admin view)
     */
    public function studentAudit($studentId): View
    {
        $student = Student::findOrFail($studentId);
        
        // Check permissions
        if (!$this->canViewAudit($student)) {
            abort(403, 'Unauthorized to view this audit');
        }

        $auditReport = $this->getOrGenerateAudit($student);
        $progress = StudentDegreeProgress::with(['requirement.category', 'programRequirement'])
            ->where('student_id', $student->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('degree-audit.student-audit', compact('student', 'auditReport', 'progress'));
    }

    /**
     * Run a new degree audit
     */
    public function runAudit(Request $request, $studentId = null): JsonResponse
    {
        try {
            // Get student
            $student = $studentId 
                ? Student::findOrFail($studentId)
                : Student::where('user_id', Auth::id())->firstOrFail();

            // Check permissions
            if (!$this->canRunAudit($student)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Validate request
            $validated = $request->validate([
                'catalog_year' => 'nullable|string|max:10',
                'program_id' => 'nullable|exists:academic_programs,id',
                'force_refresh' => 'nullable|boolean',
                'include_what_if' => 'nullable|boolean'
            ]);

            // Check for recent audit unless force refresh
            if (!($validated['force_refresh'] ?? false)) {
                $recentAudit = DegreeAuditReport::where('student_id', $student->id)
                    ->where('report_type', 'unofficial')
                    ->where('generated_at', '>=', now()->subHours(24))
                    ->orderBy('generated_at', 'desc')
                    ->first();

                if ($recentAudit) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Using cached audit report',
                        'data' => $this->formatAuditResponse($recentAudit, $student),
                        'cached' => true
                    ]);
                }
            }

            // Run the audit
            $auditReport = $this->auditService->runAudit($student, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Degree audit completed successfully',
                'data' => $this->formatAuditResponse($auditReport, $student),
                'cached' => false
            ]);

        } catch (\Exception $e) {
            Log::error('Degree audit error: ' . $e->getMessage(), [
                'student_id' => $studentId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to run degree audit',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    /**
     * Get degree progress for current student
     */
    public function myProgress(): JsonResponse
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return response()->json(['error' => 'Student record not found'], 404);
        }

        return $this->getProgressData($student);
    }

    /**
     * Get degree progress for a specific student
     */
    public function progress(Request $request, $studentId = null): JsonResponse
    {
        try {
            $student = $studentId 
                ? Student::findOrFail($studentId)
                : Student::where('user_id', Auth::id())->firstOrFail();

            if (!$this->canViewAudit($student)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            return $this->getProgressData($student);

        } catch (\Exception $e) {
            Log::error('Progress retrieval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve progress'
            ], 500);
        }
    }

    /**
     * Get audit history
     */
    public function history(Request $request, $studentId = null): JsonResponse|View
    {
        try {
            $student = $studentId 
                ? Student::findOrFail($studentId)
                : Student::where('user_id', Auth::id())->firstOrFail();

            if (!$this->canViewAudit($student)) {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
                abort(403);
            }

            $history = DegreeAuditReport::with('generatedBy')
                ->where('student_id', $student->id)
                ->orderBy('generated_at', 'desc')
                ->paginate(10);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $history->items(),
                    'pagination' => [
                        'total' => $history->total(),
                        'per_page' => $history->perPage(),
                        'current_page' => $history->currentPage(),
                        'last_page' => $history->lastPage()
                    ]
                ]);
            }

            return view('degree-audit.history', compact('student', 'history'));

        } catch (\Exception $e) {
            Log::error('History retrieval error: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to retrieve audit history'
                ], 500);
            }
            
            return back()->with('error', 'Failed to retrieve audit history');
        }
    }

    /**
     * Get requirements for a program
     */
    public function requirements(Request $request, $programId): JsonResponse|View
    {
        try {
            $validated = $request->validate([
                'catalog_year' => 'required|string|max:10'
            ]);

            $program = AcademicProgram::findOrFail($programId);

            $requirements = ProgramRequirement::with(['requirement.category', 'requirement.courses'])
                ->where('program_id', $programId)
                ->where('catalog_year', $validated['catalog_year'])
                ->where('is_active', true)
                ->get()
                ->groupBy(function ($item) {
                    return $item->requirement->category->name;
                });

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'program' => $program,
                    'catalog_year' => $validated['catalog_year'],
                    'requirements' => $requirements
                ]);
            }

            return view('degree-audit.requirements', compact('program', 'requirements'));

        } catch (\Exception $e) {
            Log::error('Requirements retrieval error: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to retrieve requirements'
                ], 500);
            }
            
            return back()->with('error', 'Failed to retrieve requirements');
        }
    }

    /**
     * Download audit report as PDF
     */
    public function download($reportId)
    {
        try {
            $report = DegreeAuditReport::findOrFail($reportId);
            $student = Student::findOrFail($report->student_id);

            if (!$this->canViewAudit($student)) {
                abort(403, 'Unauthorized');
            }

            // TODO: Implement PDF generation using DomPDF or similar
            // For now, return a simple view that can be printed
            return view('degree-audit.print', compact('report', 'student'));

        } catch (\Exception $e) {
            Log::error('Download error: ' . $e->getMessage());
            return back()->with('error', 'Failed to download report');
        }
    }

    /**
     * Clear/override a requirement for a student
     */
    public function clearRequirement(Request $request, $studentId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'requirement_id' => 'required|exists:degree_requirements,id',
                'notes' => 'required|string|max:500',
                'clear' => 'required|boolean'
            ]);

            $student = Student::findOrFail($studentId);
            
            // Check permissions (only advisors and admins)
            if (!Auth::user()->hasAnyRole(['advisor', 'registrar', 'admin'])) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $progress = StudentDegreeProgress::where('student_id', $studentId)
                ->where('requirement_id', $validated['requirement_id'])
                ->firstOrFail();

            if ($validated['clear']) {
                $progress->markAsCleared(Auth::user(), $validated['notes']);
                $message = 'Requirement cleared successfully';
            } else {
                $progress->manually_cleared = false;
                $progress->cleared_by = null;
                $progress->cleared_at = null;
                $progress->save();
                $message = 'Requirement clearance removed';
            }

            // Re-run audit to update overall progress
            $this->auditService->runAudit($student, ['partial' => true]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'progress' => $progress->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Clear requirement error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear requirement'
            ], 500);
        }
    }

    /**
     * Helper: Get student record
     */
    protected function getStudentRecord(Request $request): ?Student
    {
        $user = Auth::user();
        
        // If user is a student, get their record
        if ($user->hasRole('student')) {
            return Student::where('user_id', $user->id)->first();
        }
        
        // If user is advisor/admin, get requested student
        if ($request->has('student_id')) {
            return Student::find($request->student_id);
        }
        
        return null;
    }

    /**
     * Helper: Check if user can view audit
     */
    protected function canViewAudit(Student $student): bool
    {
        $user = Auth::user();
        
        // Student can view their own audit
        if ($student->user_id === $user->id) {
            return true;
        }
        
        // Advisors, registrars, and admins can view
        if ($user->hasAnyRole(['advisor', 'registrar', 'admin', 'academic-administrator'])) {
            return true;
        }
        
        return false;
    }

    /**
     * Helper: Check if user can run audit
     */
    protected function canRunAudit(Student $student): bool
    {
        return $this->canViewAudit($student);
    }

    /**
     * Helper: Get or generate audit
     */
    protected function getOrGenerateAudit(Student $student): DegreeAuditReport
    {
        // Check for recent audit (within last 24 hours)
        $recentAudit = DegreeAuditReport::where('student_id', $student->id)
            ->where('report_type', 'unofficial')
            ->where('generated_at', '>=', now()->subHours(24))
            ->orderBy('generated_at', 'desc')
            ->first();

        if ($recentAudit) {
            return $recentAudit;
        }

        // Generate new audit
        return $this->auditService->runAudit($student);
    }

    /**
     * Helper: Get progress data for a student
     */
    protected function getProgressData(Student $student): JsonResponse
    {
        $progress = StudentDegreeProgress::with(['requirement.category', 'programRequirement'])
            ->where('student_id', $student->id)
            ->get();

        $progressByCategory = $progress->groupBy(function ($item) {
            return $item->requirement->category->name;
        })->map(function ($items, $categoryName) {
            $totalRequired = $items->sum(function ($item) {
                return $item->credits_completed + $item->credits_remaining;
            });
            $totalCompleted = $items->sum('credits_completed');
            
            return [
                'category' => $categoryName,
                'requirements' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'requirement_name' => $item->requirement->name,
                        'status' => $item->status,
                        'credits_completed' => $item->credits_completed,
                        'credits_in_progress' => $item->credits_in_progress,
                        'credits_remaining' => $item->credits_remaining,
                        'completion_percentage' => $item->completion_percentage,
                        'is_satisfied' => $item->is_satisfied,
                        'manually_cleared' => $item->manually_cleared
                    ];
                }),
                'total_completed' => $totalCompleted,
                'total_required' => $totalRequired,
                'category_percentage' => $totalRequired > 0 
                    ? round(($totalCompleted / $totalRequired) * 100, 2) 
                    : 0
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'student_id' => $student->id,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'program' => $student->program->name ?? 'Not Set',
                'progress' => $progressByCategory,
                'summary' => [
                    'total_categories' => $progressByCategory->count(),
                    'completed_categories' => $progressByCategory->filter(function ($cat) {
                        return $cat['category_percentage'] >= 100;
                    })->count(),
                    'overall_percentage' => $this->calculateOverallPercentage($progress)
                ],
                'last_updated' => $progress->max('updated_at')
            ]
        ]);
    }

    /**
     * Helper: Format audit response
     */
    protected function formatAuditResponse(DegreeAuditReport $report, Student $student): array
    {
        return [
            'report_id' => $report->id,
            'student' => [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'student_id' => $student->student_id,
                'program' => $student->program->name ?? 'Not Set',
                'catalog_year' => $report->catalog_year,
                'cumulative_gpa' => $report->cumulative_gpa,
                'major_gpa' => $report->major_gpa
            ],
            'progress' => [
                'overall_percentage' => $report->overall_completion_percentage,
                'credits_completed' => $report->total_credits_completed,
                'credits_in_progress' => $report->total_credits_in_progress,
                'credits_remaining' => $report->total_credits_remaining,
                'total_required' => $report->total_credits_required
            ],
            'graduation' => [
                'eligible' => $report->graduation_eligible,
                'expected_date' => $report->expected_graduation_date?->format('F Y'),
                'terms_remaining' => $report->terms_to_completion
            ],
            'requirements' => $report->requirements_summary ?? [],
            'completed' => $report->completed_requirements ?? [],
            'in_progress' => $report->in_progress_requirements ?? [],
            'remaining' => $report->remaining_requirements ?? [],
            'recommendations' => $report->recommendations ?? [],
            'generated_at' => $report->generated_at->format('Y-m-d H:i:s'),
            'report_type' => $report->report_type
        ];
    }

    /**
     * Helper: Calculate overall percentage
     */
    protected function calculateOverallPercentage($progress): float
    {
        $totalRequired = $progress->sum(function ($item) {
            return $item->credits_completed + $item->credits_remaining;
        });
        
        $totalCompleted = $progress->sum('credits_completed');
        
        return $totalRequired > 0 
            ? round(($totalCompleted / $totalRequired) * 100, 2)
            : 0;
    }

    /**
     * Display student dashboard view
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return redirect()->route('home')->with('error', 'Student record not found');
        }

        // Get or generate audit
        $auditReport = $this->getOrGenerateAudit($student);
        
        // Calculate additional stats for the view
        $completedCount = count($auditReport->completed_requirements ?? []);
        $inProgressCount = count($auditReport->in_progress_requirements ?? []);
        $remainingCount = count($auditReport->remaining_requirements ?? []);
        
        $requirements = $auditReport->requirements_summary ?? [];
        $totalCategories = count($requirements);
        $categoriesSatisfied = collect($requirements)->where('is_satisfied', true)->count();

        return view('degree-audit.student.dashboard', compact(
            'student',
            'auditReport',
            'completedCount',
            'inProgressCount', 
            'remainingCount',
            'totalCategories',
            'categoriesSatisfied'
        ));
    }

    /**
     * Display detailed audit report
     */
    public function detailedReport(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return redirect()->route('home')->with('error', 'Student record not found');
        }

        $auditReport = $this->getOrGenerateAudit($student);
        
        // Get all enrollments for transcript-like view
        $enrollments = $student->enrollments()
            ->with(['section.course', 'section.term'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Group enrollments by term
        $enrollmentsByTerm = $enrollments->groupBy(function($enrollment) {
            return $enrollment->section->term->name ?? 'Unknown Term';
        });

        return view('degree-audit.student.detailed-report', compact(
            'student',
            'auditReport',
            'enrollmentsByTerm'
        ));
    }

    /**
     * Display what-if analysis tool
     */
    public function whatIfAnalysis(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return redirect()->route('home')->with('error', 'Student record not found');
        }

        // Get available programs for scenarios
        $programs = AcademicProgram::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Get saved scenarios
        $scenarios = WhatIfScenario::where('student_id', $student->id)
            ->where('is_saved', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('degree-audit.student.what-if', compact(
            'student',
            'programs',
            'scenarios'
        ));
    }

    /**
     * Advisor: View list of advisees
     */
    public function advisorDashboard(Request $request)
    {
        $user = Auth::user();
        
        // Get students assigned to this advisor
        $students = Student::where('advisor_id', $user->id)
            ->orWhere('advisor_name', $user->name)
            ->with(['program'])
            ->paginate(20);
        
        // Get progress for each student
        $studentProgress = [];
        foreach ($students as $student) {
            $audit = DegreeAuditReport::where('student_id', $student->id)
                ->orderBy('generated_at', 'desc')
                ->first();
            
            $studentProgress[$student->id] = [
                'completion' => $audit->overall_completion_percentage ?? 0,
                'gpa' => $audit->cumulative_gpa ?? 0,
                'graduation_eligible' => $audit->graduation_eligible ?? false
            ];
        }

        return view('degree-audit.advisor.student-list', compact(
            'students',
            'studentProgress'
        ));
    }

    public function requirementsTracker(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return redirect()->route('home')->with('error', 'Student record not found');
        }

        $auditReport = $this->getOrGenerateAudit($student);
        
        // Calculate counts
        $completedCount = 0;
        $inProgressCount = 0;
        $remainingCount = 0;
        $plannedCount = 0;
        
        if (isset($auditReport->requirements_summary)) {
            foreach ($auditReport->requirements_summary as $category) {
                foreach ($category['requirements'] ?? [] as $req) {
                    if ($req['is_satisfied'] ?? false) {
                        $completedCount++;
                    } elseif (($req['progress_percentage'] ?? 0) > 0) {
                        $inProgressCount++;
                    } else {
                        $remainingCount++;
                    }
                }
            }
        }
        
        $availableCourses = \App\Models\Course::where('is_active', true)->get();
        $recommendedCourses = [];
        
        return view('degree-audit.student.requirements-tracker', compact(
            'student', 
            'auditReport',
            'completedCount',
            'inProgressCount',
            'remainingCount',
            'plannedCount',
            'availableCourses',
            'recommendedCourses'
        ));
    }
}