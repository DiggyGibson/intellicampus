<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AcademicTerm;
use App\Models\CourseSection;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GradeReportsExport;
use Carbon\Carbon;

class GradeReportController extends Controller
{
    protected $gradeService;

    public function __construct(GradeCalculationService $gradeService)
    {
        $this->gradeService = $gradeService;
    }

    /**
     * GPA Report - Shows GPA distribution and statistics
     */
    public function gpaReport(Request $request)
    {
        // Check admin permission
        if (!auth()->user()->hasRole(['admin', 'registrar', 'academic_admin'])) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'term_id' => 'nullable|exists:academic_terms,id',
            'program_id' => 'nullable|exists:academic_programs,id',
            'academic_level' => 'nullable|in:freshman,sophomore,junior,senior,graduate',
            'gpa_min' => 'nullable|numeric|min:0|max:4',
            'gpa_max' => 'nullable|numeric|min:0|max:4'
        ]);

        // Get current term if not specified
        $termId = $validated['term_id'] ?? AcademicTerm::where('is_current', true)->value('id');

        // Build query
        $query = DB::table('students as s')
            ->leftJoin('enrollments as e', function ($join) use ($termId) {
                $join->on('s.id', '=', 'e.student_id')
                    ->where('e.term_id', '=', $termId)
                    ->where('e.enrollment_status', '=', 'completed');
            })
            ->select(
                's.id',
                's.student_id',
                's.first_name',
                's.last_name',
                's.program_name',
                's.academic_level',
                's.cumulative_gpa',
                's.semester_gpa',
                's.major_gpa',
                's.total_credits_earned',
                's.total_credits_attempted',
                DB::raw('COUNT(DISTINCT e.id) as courses_completed'),
                DB::raw('AVG(e.grade_points) as term_gpa')
            )
            ->where('s.enrollment_status', 'active')
            ->groupBy('s.id');

        // Apply filters
        if (!empty($validated['program_id'])) {
            $query->where('s.program_id', $validated['program_id']);
        }
        if (!empty($validated['academic_level'])) {
            $query->where('s.academic_level', $validated['academic_level']);
        }
        if (!empty($validated['gpa_min'])) {
            $query->where('s.cumulative_gpa', '>=', $validated['gpa_min']);
        }
        if (!empty($validated['gpa_max'])) {
            $query->where('s.cumulative_gpa', '<=', $validated['gpa_max']);
        }

        $students = $query->get();

        // Calculate GPA distribution
        $distribution = $this->calculateGPADistribution($students);
        
        // Calculate statistics
        $statistics = $this->calculateGPAStatistics($students);

        // Get terms for filter
        $terms = AcademicTerm::orderBy('start_date', 'desc')->limit(10)->get();
        
        // Get programs for filter
        $programs = DB::table('academic_programs')->orderBy('name')->get();

        return view('admin.grades.reports.gpa', compact(
            'students', 
            'distribution', 
            'statistics', 
            'terms', 
            'programs',
            'termId'
        ));
    }

    /**
     * Dean's List Report
     */
    public function deansList(Request $request)
    {
        // Check admin permission
        if (!auth()->user()->hasRole(['admin', 'registrar', 'academic_admin', 'dean'])) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'term_id' => 'nullable|exists:academic_terms,id',
            'gpa_threshold' => 'nullable|numeric|min:3.0|max:4.0',
            'min_credits' => 'nullable|integer|min:12|max:21'
        ]);

        $termId = $validated['term_id'] ?? AcademicTerm::where('is_current', true)->value('id');
        $gpaThreshold = $validated['gpa_threshold'] ?? 3.5;
        $minCredits = $validated['min_credits'] ?? 12;

        // Get Dean's List students
        $deansListStudents = DB::table('students as s')
            ->join('enrollments as e', 's.id', '=', 'e.student_id')
            ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->where('e.term_id', $termId)
            ->where('e.enrollment_status', 'completed')
            ->groupBy('s.id', 's.student_id', 's.first_name', 's.last_name', 
                     's.email', 's.program_name', 's.academic_level')
            ->havingRaw('SUM(c.credits) >= ?', [$minCredits])
            ->havingRaw('AVG(e.grade_points) >= ?', [$gpaThreshold])
            ->select(
                's.id',
                's.student_id',
                's.first_name',
                's.last_name',
                's.email',
                's.program_name',
                's.academic_level',
                DB::raw('COUNT(DISTINCT e.id) as courses_taken'),
                DB::raw('SUM(c.credits) as total_credits'),
                DB::raw('AVG(e.grade_points) as term_gpa'),
                DB::raw('SUM(e.grade_points * c.credits) / SUM(c.credits) as weighted_gpa')
            )
            ->orderBy('weighted_gpa', 'desc')
            ->get();

        // Check if already recorded
        foreach ($deansListStudents as $student) {
            $student->already_recorded = DB::table('deans_list')
                ->where('student_id', $student->id)
                ->where('term_id', $termId)
                ->exists();
        }

        // Get term info
        $term = AcademicTerm::find($termId);
        $terms = AcademicTerm::orderBy('start_date', 'desc')->limit(10)->get();

        // Statistics
        $stats = [
            'total_eligible' => $deansListStudents->count(),
            'by_level' => $deansListStudents->groupBy('academic_level')->map->count(),
            'by_program' => $deansListStudents->groupBy('program_name')->map->count(),
            'average_gpa' => $deansListStudents->avg('weighted_gpa'),
            'highest_gpa' => $deansListStudents->max('weighted_gpa')
        ];

        return view('admin.grades.reports.deans-list', compact(
            'deansListStudents',
            'term',
            'terms',
            'stats',
            'gpaThreshold',
            'minCredits'
        ));
    }

    /**
     * Academic Standing Report
     */
    public function academicStanding(Request $request)
    {
        // Check admin permission
        if (!auth()->user()->hasRole(['admin', 'registrar', 'academic_admin'])) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'term_id' => 'nullable|exists:academic_terms,id',
            'standing_filter' => 'nullable|in:good,warning,probation,suspension'
        ]);

        $termId = $validated['term_id'] ?? AcademicTerm::where('is_current', true)->value('id');

        // Get all active students with their academic standing
        $query = DB::table('students as s')
            ->leftJoin('enrollments as e', function ($join) use ($termId) {
                $join->on('s.id', '=', 'e.student_id')
                    ->where('e.term_id', '=', $termId);
            })
            ->select(
                's.id',
                's.student_id',
                's.first_name',
                's.last_name',
                's.email',
                's.program_name',
                's.academic_level',
                's.cumulative_gpa',
                's.semester_gpa',
                's.total_credits_earned',
                's.total_credits_attempted',
                's.academic_standing',
                DB::raw('COUNT(DISTINCT e.id) as current_enrollments'),
                DB::raw('AVG(e.grade_points) as current_term_gpa')
            )
            ->where('s.enrollment_status', 'active')
            ->groupBy('s.id');

        if (!empty($validated['standing_filter'])) {
            $query->where('s.academic_standing', $validated['standing_filter']);
        }

        $students = $query->get();

        // Calculate academic standing for each student
        foreach ($students as $student) {
            $student->calculated_standing = $this->calculateAcademicStanding(
                $student->cumulative_gpa,
                $student->total_credits_attempted
            );
            
            // Check if standing needs update
            $student->needs_update = ($student->academic_standing != $student->calculated_standing);
        }

        // Group by standing
        $standingGroups = [
            'good' => $students->where('calculated_standing', 'good'),
            'warning' => $students->where('calculated_standing', 'warning'),
            'probation' => $students->where('calculated_standing', 'probation'),
            'suspension' => $students->where('calculated_standing', 'suspension')
        ];

        // Statistics
        $stats = [
            'total_students' => $students->count(),
            'good_standing' => $standingGroups['good']->count(),
            'warning' => $standingGroups['warning']->count(),
            'probation' => $standingGroups['probation']->count(),
            'suspension' => $standingGroups['suspension']->count(),
            'needs_update' => $students->where('needs_update', true)->count()
        ];

        $terms = AcademicTerm::orderBy('start_date', 'desc')->limit(10)->get();

        return view('admin.grades.reports.academic-standing', compact(
            'students',
            'standingGroups',
            'stats',
            'terms',
            'termId'
        ));
    }

    /**
     * Grade Distribution Report
     */
    public function gradeDistribution(Request $request)
    {
        // Check admin permission
        if (!auth()->user()->hasRole(['admin', 'registrar', 'academic_admin', 'department_head'])) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'term_id' => 'nullable|exists:academic_terms,id',
            'course_id' => 'nullable|exists:courses,id',
            'instructor_id' => 'nullable|exists:users,id',
            'department' => 'nullable|string'
        ]);

        $termId = $validated['term_id'] ?? AcademicTerm::where('is_current', true)->value('id');

        // Build query for grade distribution
        $query = DB::table('grades as g')
            ->join('enrollments as e', 'g.enrollment_id', '=', 'e.id')
            ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->where('cs.term_id', $termId)
            ->where('g.is_final', true)
            ->whereNotNull('g.letter_grade');

        // Apply filters
        if (!empty($validated['course_id'])) {
            $query->where('c.id', $validated['course_id']);
        }
        if (!empty($validated['instructor_id'])) {
            $query->where('cs.instructor_id', $validated['instructor_id']);
        }
        if (!empty($validated['department'])) {
            $query->where('c.department', $validated['department']);
        }

        // Get grade distribution
        $gradeDistribution = $query
            ->select('g.letter_grade', DB::raw('COUNT(*) as count'))
            ->groupBy('g.letter_grade')
            ->orderByRaw("
                CASE g.letter_grade
                    WHEN 'A' THEN 1
                    WHEN 'A-' THEN 2
                    WHEN 'B+' THEN 3
                    WHEN 'B' THEN 4
                    WHEN 'B-' THEN 5
                    WHEN 'C+' THEN 6
                    WHEN 'C' THEN 7
                    WHEN 'C-' THEN 8
                    WHEN 'D+' THEN 9
                    WHEN 'D' THEN 10
                    WHEN 'F' THEN 11
                    ELSE 12
                END
            ")
            ->get();

        // Calculate percentages
        $totalGrades = $gradeDistribution->sum('count');
        foreach ($gradeDistribution as $grade) {
            $grade->percentage = $totalGrades > 0 
                ? round(($grade->count / $totalGrades) * 100, 2) 
                : 0;
        }

        // Get distribution by course
        $courseDistribution = DB::table('grades as g')
            ->join('enrollments as e', 'g.enrollment_id', '=', 'e.id')
            ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->where('cs.term_id', $termId)
            ->where('g.is_final', true)
            ->whereNotNull('g.letter_grade')
            ->select(
                'c.code',
                'c.title',
                'g.letter_grade',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('c.id', 'c.code', 'c.title', 'g.letter_grade')
            ->get()
            ->groupBy('code');

        // Get filters data
        $terms = AcademicTerm::orderBy('start_date', 'desc')->limit(10)->get();
        $courses = DB::table('courses')->orderBy('code')->get();
        $instructors = DB::table('users')
            ->where('user_type', 'faculty')
            ->orderBy('name')
            ->get();
        $departments = DB::table('courses')
            ->distinct()
            ->pluck('department')
            ->filter()
            ->sort();

        // Calculate statistics
        $stats = $this->calculateDistributionStatistics($gradeDistribution);

        return view('admin.grades.reports.distribution', compact(
            'gradeDistribution',
            'courseDistribution',
            'stats',
            'terms',
            'courses',
            'instructors',
            'departments',
            'termId',
            'totalGrades'
        ));
    }

    /**
     * Export grade report to Excel
     */
    public function export(Request $request, $type)
    {
        // Check admin permission
        if (!auth()->user()->hasRole(['admin', 'registrar', 'academic_admin'])) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'term_id' => 'nullable|exists:academic_terms,id',
            'format' => 'required|in:xlsx,csv,pdf'
        ]);

        $termId = $validated['term_id'] ?? AcademicTerm::where('is_current', true)->value('id');
        $term = AcademicTerm::find($termId);

        $filename = "{$type}_report_{$term->code}_{$validated['format']}";

        switch ($type) {
            case 'gpa':
                $data = $this->getGPAReportData($termId);
                break;
            case 'deans-list':
                $data = $this->getDeansListData($termId);
                break;
            case 'academic-standing':
                $data = $this->getAcademicStandingData($termId);
                break;
            case 'distribution':
                $data = $this->getGradeDistributionData($termId);
                break;
            default:
                abort(404, 'Invalid report type');
        }

        return Excel::download(new GradeReportsExport($data, $type), $filename);
    }

    /**
     * Update academic standings in bulk
     */
    public function updateStandings(Request $request)
    {
        // Check admin permission
        if (!auth()->user()->hasRole(['admin', 'registrar'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        DB::beginTransaction();
        try {
            $updated = 0;
            
            foreach ($validated['student_ids'] as $studentId) {
                $student = Student::find($studentId);
                $newStanding = $this->calculateAcademicStanding(
                    $student->cumulative_gpa,
                    $student->total_credits_attempted
                );

                if ($student->academic_standing != $newStanding) {
                    $student->academic_standing = $newStanding;
                    $student->save();
                    $updated++;

                    // Log the change
                    DB::table('academic_standing_changes')->insert([
                        'student_id' => $studentId,
                        'old_standing' => $student->academic_standing,
                        'new_standing' => $newStanding,
                        'gpa' => $student->cumulative_gpa,
                        'credits' => $student->total_credits_attempted,
                        'changed_by' => auth()->id(),
                        'created_at' => now()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Updated academic standing for {$updated} students"
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Academic standing update error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return response()->json(['error' => 'Failed to update standings'], 500);
        }
    }

    /**
     * Record Dean's List students
     */
    public function recordDeansList(Request $request)
    {
        // Check admin permission
        if (!auth()->user()->hasRole(['admin', 'registrar', 'dean'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'term_id' => 'required|exists:academic_terms,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        DB::beginTransaction();
        try {
            $recorded = 0;
            
            foreach ($validated['student_ids'] as $studentId) {
                // Check if already recorded
                $exists = DB::table('deans_list')
                    ->where('student_id', $studentId)
                    ->where('term_id', $validated['term_id'])
                    ->exists();

                if (!$exists) {
                    // Get student's GPA for this term
                    $termGPA = DB::table('enrollments as e')
                        ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
                        ->where('e.student_id', $studentId)
                        ->where('cs.term_id', $validated['term_id'])
                        ->where('e.enrollment_status', 'completed')
                        ->avg('e.grade_points');

                    DB::table('deans_list')->insert([
                        'student_id' => $studentId,
                        'term_id' => $validated['term_id'],
                        'gpa' => $termGPA,
                        'recorded_by' => auth()->id(),
                        'created_at' => now()
                    ]);
                    
                    $recorded++;
                }
            }

            DB::commit();

            return back()->with('success', "Recorded {$recorded} students to Dean's List");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Deans list recording error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'Failed to record Dean\'s List');
        }
    }

    /**
     * Helper: Calculate GPA distribution
     */
    private function calculateGPADistribution($students)
    {
        $ranges = [
            '4.0' => 0,
            '3.5-3.99' => 0,
            '3.0-3.49' => 0,
            '2.5-2.99' => 0,
            '2.0-2.49' => 0,
            '1.5-1.99' => 0,
            '1.0-1.49' => 0,
            'Below 1.0' => 0
        ];

        foreach ($students as $student) {
            $gpa = $student->cumulative_gpa;
            
            if ($gpa == 4.0) {
                $ranges['4.0']++;
            } elseif ($gpa >= 3.5) {
                $ranges['3.5-3.99']++;
            } elseif ($gpa >= 3.0) {
                $ranges['3.0-3.49']++;
            } elseif ($gpa >= 2.5) {
                $ranges['2.5-2.99']++;
            } elseif ($gpa >= 2.0) {
                $ranges['2.0-2.49']++;
            } elseif ($gpa >= 1.5) {
                $ranges['1.5-1.99']++;
            } elseif ($gpa >= 1.0) {
                $ranges['1.0-1.49']++;
            } else {
                $ranges['Below 1.0']++;
            }
        }

        return $ranges;
    }

    /**
     * Helper: Calculate GPA statistics
     */
    private function calculateGPAStatistics($students)
    {
        $gpas = $students->pluck('cumulative_gpa')->filter()->sort();
        
        return [
            'mean' => $gpas->avg(),
            'median' => $gpas->median(),
            'mode' => $gpas->mode()->first(),
            'min' => $gpas->min(),
            'max' => $gpas->max(),
            'std_dev' => $this->standardDeviation($gpas->toArray()),
            'count' => $gpas->count()
        ];
    }

    /**
     * Helper: Calculate standard deviation
     */
    private function standardDeviation($values)
    {
        $count = count($values);
        if ($count < 2) return 0;
        
        $mean = array_sum($values) / $count;
        $variance = 0;
        
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        
        return sqrt($variance / ($count - 1));
    }

    /**
     * Helper: Calculate academic standing
     */
    private function calculateAcademicStanding($gpa, $creditsAttempted)
    {
        // Good Standing: GPA >= 2.0
        // Academic Warning: GPA 1.7 - 1.99
        // Academic Probation: GPA 1.0 - 1.69
        // Academic Suspension: GPA < 1.0 or on probation for 2 consecutive terms
        
        if ($gpa >= 2.0) {
            return 'good';
        } elseif ($gpa >= 1.7) {
            return 'warning';
        } elseif ($gpa >= 1.0) {
            return 'probation';
        } else {
            return 'suspension';
        }
    }

    /**
     * Helper: Calculate distribution statistics
     */
    private function calculateDistributionStatistics($distribution)
    {
        $total = $distribution->sum('count');
        
        // Calculate grade points for average
        $gradePoints = [
            'A' => 4.0, 'A-' => 3.7, 'B+' => 3.3, 'B' => 3.0,
            'B-' => 2.7, 'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
            'D+' => 1.3, 'D' => 1.0, 'F' => 0.0
        ];
        
        $totalPoints = 0;
        $passingCount = 0;
        
        foreach ($distribution as $grade) {
            $points = $gradePoints[$grade->letter_grade] ?? 0;
            $totalPoints += $points * $grade->count;
            
            if ($grade->letter_grade != 'F') {
                $passingCount += $grade->count;
            }
        }
        
        return [
            'average_grade_points' => $total > 0 ? round($totalPoints / $total, 2) : 0,
            'pass_rate' => $total > 0 ? round(($passingCount / $total) * 100, 2) : 0,
            'fail_rate' => $total > 0 ? round((($total - $passingCount) / $total) * 100, 2) : 0,
            'total_grades' => $total
        ];
    }

    // Data retrieval methods for exports would go here...
}