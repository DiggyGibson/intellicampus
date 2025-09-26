<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\CourseSection;
use App\Models\AcademicTerm;
use App\Services\GradeCalculationService;
use App\Services\TranscriptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GradeTemplateExport;
use App\Exports\GradesExport;
use App\Imports\GradesImport;
use Carbon\Carbon;

class GradeController extends Controller
{
    protected $gradeService;
    protected $transcriptService;

    public function __construct(GradeCalculationService $gradeService, TranscriptService $transcriptService)
    {
        $this->gradeService = $gradeService;
        $this->transcriptService = $transcriptService;
    }

    /**
     * Display grading dashboard for faculty
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get current term
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        
        if (!$currentTerm) {
            return view('grades.index', [
                'sections' => collect(),
                'currentTerm' => null,
                'deadlines' => null,
                'error' => 'No active academic term found.'
            ]);
        }
        
        // Get sections - admin/registrar see all, faculty see only theirs
        $sectionsQuery = CourseSection::with(['course', 'term', 'enrollments']);
        
        if (!$user->hasRole(['admin', 'registrar'])) {
            $sectionsQuery->where('instructor_id', $user->id);
        }
        
        $sections = $sectionsQuery->where('term_id', $currentTerm->id)->get();
        
        // Calculate grading statistics for each section
        foreach ($sections as $section) {
            $enrolledCount = $section->enrollments
                ->whereIn('enrollment_status', ['enrolled', 'active'])
                ->count();
                
            $gradesSubmitted = Grade::whereIn('enrollment_id', $section->enrollments->pluck('id'))
                ->where('is_final', true)
                ->count();
                
            $section->stats = [
                'total_enrolled' => $enrolledCount,
                'grades_submitted' => $gradesSubmitted,
                'pending_grades' => max(0, $enrolledCount - $gradesSubmitted),
                'average_grade' => Grade::whereIn('enrollment_id', $section->enrollments->pluck('id'))
                    ->where('is_final', true)
                    ->avg('percentage') ?? 0
            ];
        }
        
        // Get and properly format grade deadlines
        $deadlineRecords = DB::table('grade_deadlines')
            ->where('term_id', $currentTerm->id)
            ->get()
            ->keyBy('deadline_type');
        
        // Create deadline object matching view expectations
        if ($deadlineRecords->isNotEmpty()) {
            $deadlines = (object)[
                'midterm_grade_deadline' => $deadlineRecords->get('midterm')->deadline_date ?? null,
                'final_grade_deadline' => $deadlineRecords->get('final')->deadline_date ?? null,
                'grade_change_deadline' => $deadlineRecords->get('grade_change')->deadline_date ?? 
                                        $deadlineRecords->get('incomplete')->deadline_date ?? null,
                'incomplete_deadline' => $deadlineRecords->get('incomplete')->deadline_date ?? null
            ];
        } else {
            // Fallback: calculate deadlines if none exist in database
            $deadlines = (object)[
                'midterm_grade_deadline' => Carbon::parse($currentTerm->start_date)->addWeeks(8)->format('Y-m-d'),
                'final_grade_deadline' => Carbon::parse($currentTerm->end_date)->addDays(3)->format('Y-m-d'),
                'grade_change_deadline' => Carbon::parse($currentTerm->end_date)->addDays(30)->format('Y-m-d'),
                'incomplete_deadline' => Carbon::parse($currentTerm->end_date)->addDays(60)->format('Y-m-d')
            ];
        }
        
        return view('grades.index', compact('sections', 'currentTerm', 'deadlines'));
    }

    /**
     * Display all sections (admin/registrar view)
     */
    public function sections()
    {
        // Check permission
        if (!auth()->user()->hasRole(['admin', 'registrar', 'academic-administrator'])) {
            abort(403, 'Unauthorized access');
        }
        
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        
        $sections = CourseSection::with(['course', 'instructor', 'enrollments', 'term'])
            ->when($currentTerm, function($query) use ($currentTerm) {
                return $query->where('term_id', $currentTerm->id);
            })
            ->paginate(20);
        
        // Add grade submission status
        foreach ($sections as $section) {
            $totalEnrolled = $section->enrollments->count();
            $gradesSubmitted = Grade::whereIn('enrollment_id', $section->enrollments->pluck('id'))
                ->where('is_final', true)
                ->count();
            
            $section->submission_status = [
                'total' => $totalEnrolled,
                'submitted' => $gradesSubmitted,
                'pending' => $totalEnrolled - $gradesSubmitted,
                'percentage' => $totalEnrolled > 0 ? round(($gradesSubmitted / $totalEnrolled) * 100, 2) : 0
            ];
        }
        
        return view('grades.sections', compact('sections', 'currentTerm'));
    }

    /**
     * Display grade reports dashboard
     */
    public function reports()
    {
        // Check permission
        if (!auth()->user()->hasRole(['admin', 'registrar', 'academic-administrator', 'department-head'])) {
            abort(403, 'Unauthorized access');
        }
        
        // Get summary statistics
        $stats = [
            'total_grades' => Grade::where('is_final', true)->count(),
            'pending_approvals' => DB::table('grade_submissions')->where('status', 'pending')->count(),
            'change_requests' => DB::table('grade_change_requests')->where('status', 'pending')->count(),
            'current_term_grades' => Grade::whereHas('enrollment.section', function($q) {
                $q->whereHas('term', function($tq) {
                    $tq->where('is_current', true);
                });
            })->where('is_final', true)->count()
        ];
        
        // Get grade distribution
        $distribution = Grade::where('is_final', true)
            ->whereNotNull('letter_grade')
            ->select('letter_grade', DB::raw('count(*) as count'))
            ->groupBy('letter_grade')
            ->orderBy('letter_grade')
            ->get();
        
        // Get recent submissions
        $recentSubmissions = DB::table('grade_submissions')
            ->join('course_sections', 'grade_submissions.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->join('users', 'grade_submissions.submitted_by', '=', 'users.id')
            ->select(
                'courses.code',
                'courses.name',
                'users.name as instructor',
                'grade_submissions.submitted_at',
                'grade_submissions.status'
            )
            ->orderBy('grade_submissions.submitted_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('grades.reports', compact('stats', 'distribution', 'recentSubmissions'));
    }

    /**
     * Grade settings page
     */
    public function settings()
    {
        // Check permission
        if (!auth()->user()->hasRole(['admin', 'system-administrator'])) {
            abort(403, 'Unauthorized access');
        }
        
        // Get current settings from database or use defaults
        $settingsData = DB::table('settings')
            ->where('key', 'like', 'grades.%')
            ->pluck('value', 'key')
            ->toArray();
        
        $settings = [
            'allow_late_submission' => $settingsData['grades.allow_late_submission'] ?? false,
            'require_approval' => $settingsData['grades.require_approval'] ?? true,
            'auto_calculate_gpa' => $settingsData['grades.auto_calculate_gpa'] ?? true,
            'grade_change_window_days' => $settingsData['grades.grade_change_window_days'] ?? 30,
            'minimum_passing_grade' => $settingsData['grades.minimum_passing_grade'] ?? 60,
            'deans_list_gpa' => $settingsData['grades.deans_list_gpa'] ?? 3.5,
            'probation_gpa' => $settingsData['grades.probation_gpa'] ?? 2.0,
            'allow_grade_replacement' => $settingsData['grades.allow_grade_replacement'] ?? true,
            'max_repeat_attempts' => $settingsData['grades.max_repeat_attempts'] ?? 3
        ];
        
        // Get grade scales
        $gradeScales = DB::table('grade_scales')->where('is_active', true)->get();
        
        // Get deadline types
        $deadlineTypes = ['midterm', 'final', 'grade_change', 'incomplete'];
        
        return view('grades.settings', compact('settings', 'gradeScales', 'deadlineTypes'));
    }

    /**
     * Save grade settings
     */
    public function saveSettings(Request $request)
    {
        // Check permission
        if (!auth()->user()->hasRole(['admin', 'system-administrator'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validate([
            'allow_late_submission' => 'boolean',
            'require_approval' => 'boolean',
            'auto_calculate_gpa' => 'boolean',
            'grade_change_window_days' => 'integer|min:1|max:365',
            'minimum_passing_grade' => 'numeric|min:0|max:100',
            'deans_list_gpa' => 'numeric|min:0|max:4',
            'probation_gpa' => 'numeric|min:0|max:4',
            'allow_grade_replacement' => 'boolean',
            'max_repeat_attempts' => 'integer|min:1|max:10'
        ]);
        
        // Save settings to database
        foreach ($validated as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'grades.' . $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }
        
        // Clear config cache
        \Artisan::call('config:clear');
        
        return redirect()->route('grades.settings')
            ->with('success', 'Grade settings updated successfully');
    }

    /**
     * Export grades for a section or all sections
     */
    public function exportGrades(Request $request, $sectionId = null)
    {
        // If exporting all sections, check admin permission
        if (!$sectionId && $request->get('all')) {
            if (!auth()->user()->hasRole(['admin', 'registrar'])) {
                abort(403, 'Unauthorized access');
            }
            
            // Export all grades for current term
            $currentTerm = AcademicTerm::where('is_current', true)->first();
            if (!$currentTerm) {
                return back()->with('error', 'No active term found');
            }
            
            $sections = CourseSection::where('term_id', $currentTerm->id)->get();
            $fileName = 'all_grades_' . $currentTerm->code . '_' . date('Y-m-d') . '.xlsx';
            
            return Excel::download(new GradesExport($sections, true), $fileName);
        }
        
        // Export single section
        if ($sectionId) {
            $section = CourseSection::with(['course', 'enrollments.student', 'components'])->findOrFail($sectionId);
            
            // Check permission
            if (!auth()->user()->hasRole(['admin', 'registrar']) && 
                $section->instructor_id != auth()->id()) {
                abort(403, 'Unauthorized access');
            }
            
            // Prepare data for export
            $data = [];
            foreach ($section->enrollments as $enrollment) {
                $row = [
                    'Student ID' => $enrollment->student->student_id,
                    'Name' => $enrollment->student->first_name . ' ' . $enrollment->student->last_name,
                    'Email' => $enrollment->student->email
                ];
                
                // Add component grades
                foreach ($section->components as $component) {
                    $grade = Grade::where('enrollment_id', $enrollment->id)
                        ->where('component_id', $component->id)
                        ->first();
                    
                    $row[$component->name] = $grade ? $grade->points_earned : '';
                }
                
                // Add final grade
                $finalGrade = $this->gradeService->calculateGrade($enrollment->id);
                $row['Final Grade'] = $finalGrade['letter_grade'] ?? '';
                $row['Percentage'] = $finalGrade['percentage'] ?? '';
                
                $data[] = $row;
            }
            
            // Generate Excel file
            $fileName = 'grades_' . $section->course->code . '_' . date('Y-m-d') . '.xlsx';
            
            return Excel::download(new GradesExport([$section], false), $fileName);
        }
        
        return back()->with('error', 'Invalid export request');
    }

    /**
     * Show grade entry form for a section
     */
    public function entry($sectionId)
    {
        $section = CourseSection::with(['course', 'term'])->findOrFail($sectionId);
        
        // Verify instructor access
        if (!$this->hasGradingAccess($section)) {
            abort(403, 'You do not have permission to enter grades for this section.');
        }
        
        // Get or create grade components
        $components = GradeComponent::where('section_id', $sectionId)
            ->orderBy('type')
            ->orderBy('due_date')
            ->get();
        
        // If no components exist, create default ones
        if ($components->isEmpty()) {
            $components = $this->createDefaultComponents($sectionId);
        }
        
        // Get enrolled students with their grades
        $enrollments = Enrollment::with(['student', 'grades.component'])
            ->where('section_id', $sectionId)
            ->whereIn('enrollment_status', ['enrolled', 'completed'])
            ->get();
        
        // Calculate current grades for each student
        foreach ($enrollments as $enrollment) {
            $enrollment->calculated_grade = $this->gradeService->calculateGrade($enrollment->id);
            $enrollment->letter_grade = $this->gradeService->getLetterGrade($enrollment->calculated_grade['percentage']);
        }
        
        return view('grades.entry', compact('section', 'components', 'enrollments'));
    }

    /**
     * Save individual grade entry
     */
    public function saveGrade(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
            'component_id' => 'required|exists:grade_components,id',
            'points_earned' => 'nullable|numeric|min:0',
            'comments' => 'nullable|string|max:500'
        ]);
        
        // Verify access to this enrollment's section
        $enrollment = Enrollment::findOrFail($validated['enrollment_id']);
        $section = CourseSection::findOrFail($enrollment->section_id);
        
        if (!$this->hasGradingAccess($section)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $component = GradeComponent::findOrFail($validated['component_id']);
        
        DB::beginTransaction();
        try {
            $grade = Grade::updateOrCreate(
                [
                    'enrollment_id' => $validated['enrollment_id'],
                    'component_id' => $validated['component_id']
                ],
                [
                    'points_earned' => $validated['points_earned'],
                    'max_points' => $component->max_points,
                    'percentage' => $component->max_points > 0 ? 
                        ($validated['points_earned'] / $component->max_points) * 100 : 0,
                    'comments' => $validated['comments'] ?? null,
                    'graded_by' => Auth::id(),
                    'submitted_at' => now(),
                    'grade_status' => 'draft'
                ]
            );
            
            // Update the letter grade
            $grade->letter_grade = $grade->calculateLetterGrade();
            $grade->save();
            
            // Recalculate overall grade for this enrollment
            $overallGrade = $this->gradeService->calculateGrade($enrollment->id);
            
            // Update enrollment with calculated grade
            $enrollment->update([
                'grade' => $this->gradeService->getLetterGrade($overallGrade['percentage']),
                'grade_points' => $this->gradeService->getGradePoints($overallGrade['letter_grade'])
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Grade saved successfully',
                'data' => [
                    'grade' => $grade,
                    'overall' => $overallGrade
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Grade save error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save grade'], 500);
        }
    }

    /**
     * Bulk grade entry
     */
    public function bulkEntry(Request $request, $sectionId)
    {
        $section = CourseSection::findOrFail($sectionId);
        
        if (!$this->hasGradingAccess($section)) {
            abort(403, 'Unauthorized');
        }
        
        $validated = $request->validate([
            'component_id' => 'required|exists:grade_components,id',
            'grades' => 'required|array',
            'grades.*.enrollment_id' => 'required|exists:enrollments,id',
            'grades.*.points' => 'nullable|numeric|min:0'
        ]);
        
        $component = GradeComponent::findOrFail($validated['component_id']);
        
        DB::beginTransaction();
        try {
            $savedCount = 0;
            
            foreach ($validated['grades'] as $gradeData) {
                if ($gradeData['points'] !== null) {
                    Grade::updateOrCreate(
                        [
                            'enrollment_id' => $gradeData['enrollment_id'],
                            'component_id' => $component->id
                        ],
                        [
                            'points_earned' => $gradeData['points'],
                            'max_points' => $component->max_points,
                            'percentage' => $component->max_points > 0 ? 
                                ($gradeData['points'] / $component->max_points) * 100 : 0,
                            'letter_grade' => $this->gradeService->getLetterGrade(
                                ($gradeData['points'] / $component->max_points) * 100
                            ),
                            'graded_by' => Auth::id(),
                            'submitted_at' => now(),
                            'grade_status' => 'draft'
                        ]
                    );
                    
                    // Recalculate overall grade
                    $this->gradeService->updateEnrollmentGrade($gradeData['enrollment_id']);
                    $savedCount++;
                }
            }
            
            DB::commit();
            
            return back()->with('success', "$savedCount grades saved successfully");
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk grade entry error: ' . $e->getMessage());
            return back()->with('error', 'Failed to save grades. Please try again.');
        }
    }

    /**
     * Download Excel template for grade upload
     */
    public function downloadTemplate($sectionId, $componentId)
    {
        $section = CourseSection::with('course')->findOrFail($sectionId);
        $component = GradeComponent::findOrFail($componentId);
        
        if (!$this->hasGradingAccess($section)) {
            abort(403, 'Unauthorized');
        }
        
        $enrollments = Enrollment::with('student')
            ->where('section_id', $sectionId)
            ->whereIn('enrollment_status', ['enrolled', 'completed'])
            ->orderBy('created_at')
            ->get();
        
        $filename = "{$section->course->code}_{$section->section_code}_{$component->name}_grades.xlsx";
        
        return Excel::download(
            new GradeTemplateExport($enrollments, $section, $component),
            $filename
        );
    }

    /**
     * Upload grades via Excel file
     */
    public function uploadGrades(Request $request, $sectionId)
    {
        $section = CourseSection::findOrFail($sectionId);
        
        if (!$this->hasGradingAccess($section)) {
            abort(403, 'Unauthorized');
        }
        
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'component_id' => 'required|exists:grade_components,id'
        ]);
        
        try {
            $import = new GradesImport($sectionId, $request->component_id, Auth::id());
            Excel::import($import, $request->file('file'));
            
            $results = $import->getResults();
            
            // Recalculate all affected enrollment grades
            foreach ($results['successful'] as $enrollmentId) {
                $this->gradeService->updateEnrollmentGrade($enrollmentId);
            }
            
            return back()->with('success', 
                "Grades uploaded successfully. {$results['success_count']} grades imported, {$results['error_count']} errors.");
            
        } catch (\Exception $e) {
            Log::error('Grade upload error: ' . $e->getMessage());
            return back()->with('error', 'Failed to upload grades. Please check your file format.');
        }
    }

    /**
     * Preview grades before final submission
     */
    public function preview($sectionId)
    {
        $section = CourseSection::with(['course', 'term'])->findOrFail($sectionId);
        
        if (!$this->hasGradingAccess($section)) {
            abort(403, 'Unauthorized');
        }
        
        $enrollments = Enrollment::with(['student', 'grades.component'])
            ->where('section_id', $sectionId)
            ->whereIn('enrollment_status', ['enrolled', 'completed'])
            ->get();
        
        $gradeDistribution = [];
        
        foreach ($enrollments as $enrollment) {
            $calculation = $this->gradeService->calculateGrade($enrollment->id);
            $enrollment->final_percentage = $calculation['percentage'];
            $enrollment->final_letter = $calculation['letter_grade'];
            $enrollment->grade_points = $this->gradeService->getGradePoints($calculation['letter_grade']);
            
            // Build grade distribution
            if (!isset($gradeDistribution[$enrollment->final_letter])) {
                $gradeDistribution[$enrollment->final_letter] = 0;
            }
            $gradeDistribution[$enrollment->final_letter]++;
        }
        
        // Sort grade distribution
        $sortedDistribution = [];
        $gradeOrder = ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'F'];
        foreach ($gradeOrder as $grade) {
            if (isset($gradeDistribution[$grade])) {
                $sortedDistribution[$grade] = $gradeDistribution[$grade];
            }
        }
        
        return view('grades.preview', compact('section', 'enrollments', 'sortedDistribution'));
    }

    /**
     * Submit final grades for a section
     */
    public function submitFinalGrades(Request $request, $sectionId)
    {
        $section = CourseSection::findOrFail($sectionId);
        
        if (!$this->hasGradingAccess($section)) {
            abort(403, 'Unauthorized');
        }
        
        $validated = $request->validate([
            'confirm' => 'required|accepted',
            'grades' => 'required|array',
            'grades.*.enrollment_id' => 'required|exists:enrollments,id',
            'grades.*.final_grade' => 'required|string'
        ]);
        
        DB::beginTransaction();
        try {
            $submittedCount = 0;
            
            foreach ($validated['grades'] as $gradeData) {
                $enrollment = Enrollment::findOrFail($gradeData['enrollment_id']);
                
                // Create or update final grade record
                $finalGrade = Grade::updateOrCreate(
                    [
                        'enrollment_id' => $enrollment->id,
                        'component_id' => null // NULL indicates final grade
                    ],
                    [
                        'letter_grade' => $gradeData['final_grade'],
                        'graded_by' => Auth::id(),
                        'submitted_at' => now(),
                        'is_final' => true,
                        'grade_status' => 'pending_approval'
                    ]
                );
                
                // Update enrollment record
                $enrollment->update([
                    'grade' => $gradeData['final_grade'],
                    'grade_points' => $this->gradeService->getGradePoints($gradeData['final_grade'])
                ]);
                
                // Mark component grades as final
                Grade::where('enrollment_id', $enrollment->id)
                    ->whereNotNull('component_id')
                    ->update([
                        'is_final' => true,
                        'grade_status' => 'posted'
                    ]);
                
                $submittedCount++;
            }
            
            // Record submission in grade submissions log
            DB::table('grade_submissions')->insert([
                'section_id' => $sectionId,
                'submitted_by' => Auth::id(),
                'submitted_at' => now(),
                'total_grades' => $submittedCount,
                'status' => 'pending_approval',
                'term_id' => $section->term_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Update student GPAs
            $this->updateStudentGPAs($validated['grades']);
            
            // Send notifications
            $this->sendGradeNotifications($sectionId);
            
            DB::commit();
            
            return redirect()
                ->route('grades.index')
                ->with('success', "Final grades submitted successfully for {$submittedCount} students.");
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Final grade submission error: ' . $e->getMessage());
            return back()->with('error', 'Failed to submit final grades. Please try again.');
        }
    }

    /**
     * Grade change request form
     */
    public function changeRequest($enrollmentId)
    {
        $enrollment = Enrollment::with(['student', 'section.course'])->findOrFail($enrollmentId);
        $section = $enrollment->section;
        
        if (!$this->hasGradingAccess($section)) {
            abort(403, 'Unauthorized');
        }
        
        $currentGrade = Grade::where('enrollment_id', $enrollmentId)
            ->whereNull('component_id')
            ->where('is_final', true)
            ->first();
        
        return view('grades.change-request', compact('enrollment', 'currentGrade'));
    }

    /**
     * Submit grade change request
     */
    public function submitChangeRequest(Request $request, $enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $section = CourseSection::findOrFail($enrollment->section_id);
        
        if (!$this->hasGradingAccess($section)) {
            abort(403, 'Unauthorized');
        }
        
        $validated = $request->validate([
            'current_grade' => 'required|string',
            'new_grade' => 'required|string|different:current_grade',
            'reason' => 'required|string|min:50|max:1000',
            'supporting_documents' => 'nullable|file|mimes:pdf,doc,docx|max:5120'
        ]);
        
        DB::beginTransaction();
        try {
            // Store supporting document if provided
            $documentPath = null;
            if ($request->hasFile('supporting_documents')) {
                $documentPath = $request->file('supporting_documents')->store('grade-changes', 'private');
            }
            
            // Create grade change request
            $changeRequest = DB::table('grade_change_requests')->insertGetId([
                'enrollment_id' => $enrollmentId,
                'requested_by' => Auth::id(),
                'current_grade' => $validated['current_grade'],
                'requested_grade' => $validated['new_grade'],
                'reason' => $validated['reason'],
                'supporting_document' => $documentPath,
                'status' => 'pending',
                'requested_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Send notification to department head
            $this->notifyDepartmentHead($changeRequest);
            
            DB::commit();
            
            return redirect()
                ->route('grades.index')
                ->with('success', 'Grade change request submitted successfully. Pending department approval.');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Grade change request error: ' . $e->getMessage());
            return back()->with('error', 'Failed to submit grade change request.');
        }
    }

    /**
     * View grade history for a student
     */
    public function history($enrollmentId)
    {
        $enrollment = Enrollment::with(['student', 'section.course'])->findOrFail($enrollmentId);
        
        // Check access (faculty or the student themselves)
        if (!$this->hasGradingAccess($enrollment->section) && 
            Auth::user()->student_id !== $enrollment->student_id) {
            abort(403, 'Unauthorized');
        }
        
        $gradeHistory = DB::table('grade_audit_log')
            ->where('enrollment_id', $enrollmentId)
            ->orderBy('changed_at', 'desc')
            ->get();
        
        return view('grades.history', compact('enrollment', 'gradeHistory'));
    }

    /**
     * Grade statistics for a section
     */
    public function statistics($sectionId)
    {
        $section = CourseSection::with('course')->findOrFail($sectionId);
        
        if (!$this->hasGradingAccess($section)) {
            abort(403, 'Unauthorized');
        }
        
        $stats = $this->gradeService->getSectionStatistics($sectionId);
        
        return view('grades.statistics', compact('section', 'stats'));
    }

    /**
     * Component management page
     */
    public function components($sectionId)
    {
        $section = CourseSection::with('course')->findOrFail($sectionId);
        
        if (!$this->hasGradingAccess($section)) {
            abort(403, 'Unauthorized');
        }
        
        $components = GradeComponent::where('section_id', $sectionId)
            ->orderBy('type')
            ->orderBy('due_date')
            ->get();
        
        $totalWeight = $components->where('is_extra_credit', false)->sum('weight');
        
        return view('grades.components', compact('section', 'components', 'totalWeight'));
    }

    /**
     * Save grade components
     */
    public function saveComponents(Request $request, $sectionId)
    {
        $section = CourseSection::findOrFail($sectionId);
        
        if (!$this->hasGradingAccess($section)) {
            abort(403, 'Unauthorized');
        }
        
        $validated = $request->validate([
            'components' => 'required|array',
            'components.*.name' => 'required|string|max:100',
            'components.*.type' => 'required|in:exam,quiz,assignment,project,participation,attendance,presentation,lab,homework,other',
            'components.*.weight' => 'required|numeric|min:0|max:100',
            'components.*.max_points' => 'required|numeric|min:0',
            'components.*.due_date' => 'nullable|date',
            'components.*.is_extra_credit' => 'boolean'
        ]);
        
        // Validate total weight
        $totalWeight = collect($validated['components'])
            ->where('is_extra_credit', false)
            ->sum('weight');
            
        if ($totalWeight != 100) {
            return back()->with('error', 'Total weight of graded components must equal 100%');
        }
        
        DB::beginTransaction();
        try {
            // Delete existing components
            GradeComponent::where('section_id', $sectionId)->delete();
            
            // Create new components
            foreach ($validated['components'] as $componentData) {
                GradeComponent::create([
                    'section_id' => $sectionId,
                    'name' => $componentData['name'],
                    'type' => $componentData['type'],
                    'weight' => $componentData['weight'],
                    'max_points' => $componentData['max_points'],
                    'due_date' => $componentData['due_date'] ?? null,
                    'is_extra_credit' => $componentData['is_extra_credit'] ?? false,
                    'is_visible' => true
                ]);
            }
            
            DB::commit();
            
            return back()->with('success', 'Grade components saved successfully');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Component save error: ' . $e->getMessage());
            return back()->with('error', 'Failed to save grade components');
        }
    }

    /**
     * Helper: Check if user has grading access to a section
     */
    private function hasGradingAccess($section)
    {
        $user = Auth::user();
        
        // Check if user is the instructor
        if ($section->instructor_id == $user->id) {
            return true;
        }
        
        // Check if user is admin or registrar
        if ($user->hasRole(['admin', 'registrar', 'super-administrator'])) {
            return true;
        }
        
        // Check if user is department head
        if ($user->hasRole(['department-head', 'department_head'])) {
            $course = $section->course;
            // Check if user's department matches course department
            if (isset($user->department_id) && isset($course->department_id)) {
                return $course->department_id == $user->department_id;
            }
        }
        
        // Check if user is a TA for this section
        $isTA = DB::table('teaching_assistants')
            ->where('section_id', $section->id)
            ->where('user_id', $user->id)
            ->exists();
            
        return $isTA;
    }

    /**
     * Helper: Create default grade components
     */
    private function createDefaultComponents($sectionId)
    {
        $defaults = [
            ['name' => 'Assignments', 'type' => 'assignment', 'weight' => 20, 'max_points' => 100],
            ['name' => 'Quizzes', 'type' => 'quiz', 'weight' => 15, 'max_points' => 100],
            ['name' => 'Midterm Exam', 'type' => 'exam', 'weight' => 25, 'max_points' => 100],
            ['name' => 'Final Exam', 'type' => 'exam', 'weight' => 30, 'max_points' => 100],
            ['name' => 'Participation', 'type' => 'participation', 'weight' => 10, 'max_points' => 100]
        ];
        
        $components = collect();
        
        foreach ($defaults as $default) {
            $component = GradeComponent::create([
                'section_id' => $sectionId,
                'name' => $default['name'],
                'type' => $default['type'],
                'weight' => $default['weight'],
                'max_points' => $default['max_points'],
                'is_visible' => true
            ]);
            $components->push($component);
        }
        
        return $components;
    }

    /**
     * Helper: Update student GPAs after grade submission
     */
    private function updateStudentGPAs($grades)
    {
        $studentIds = collect($grades)->map(function ($grade) {
            return Enrollment::find($grade['enrollment_id'])->student_id;
        })->unique();
        
        foreach ($studentIds as $studentId) {
            $this->gradeService->updateStudentGPA($studentId);
        }
    }

    /**
     * Helper: Send grade notifications
     */
    private function sendGradeNotifications($sectionId)
    {
        // Implementation for sending email/SMS notifications
        // This would integrate with your notification service
        Log::info("Grade notifications would be sent for section: {$sectionId}");
    }

    /**
     * Helper: Notify department head of grade change request
     */
    private function notifyDepartmentHead($changeRequestId)
    {
        // Implementation for notifying department head
        // This would integrate with your notification service
        Log::info("Department head would be notified of grade change request: {$changeRequestId}");
    }
}