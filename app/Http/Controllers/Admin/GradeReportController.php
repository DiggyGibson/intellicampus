<?php

// ============================================================
// Save this as: app/Http/Controllers/Admin/GradeReportController.php
// ============================================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeReportController extends Controller
{
    protected $gradeService;

    public function __construct(GradeCalculationService $gradeService)
    {
        $this->gradeService = $gradeService;
    }

    /**
     * GPA Report
     */
    public function gpaReport(Request $request)
    {
        if (!auth()->user()->hasRole(['admin', 'registrar', 'academic-administrator'])) {
            abort(403, 'Unauthorized access');
        }

        $termId = $request->get('term_id');
        $programId = $request->get('program_id');

        $query = Student::with(['enrollments.section.course', 'enrollments.grades']);

        if ($termId) {
            $query->whereHas('enrollments', function($q) use ($termId) {
                $q->whereHas('section', function($sq) use ($termId) {
                    $sq->where('term_id', $termId);
                });
            });
        }

        if ($programId) {
            $query->where('program_id', $programId);
        }

        $students = $query->paginate(50);

        // Calculate GPA for each student
        foreach ($students as $student) {
            $student->term_gpa = $this->gradeService->calculateTermGPA($student->id, $termId);
            $student->cumulative_gpa = $this->gradeService->calculateCumulativeGPA($student->id);
        }

        $terms = DB::table('academic_terms')->orderBy('start_date', 'desc')->get();
        $programs = DB::table('programs')->orderBy('name')->get();

        return view('admin.grades.reports.gpa', compact('students', 'terms', 'programs'));
    }

    /**
     * Dean's List Report
     */
    public function deansList(Request $request)
    {
        if (!auth()->user()->hasRole(['admin', 'registrar', 'dean'])) {
            abort(403, 'Unauthorized access');
        }

        $termId = $request->get('term_id', $this->getCurrentTermId());
        $minGPA = $request->get('min_gpa', 3.5);
        $minCredits = $request->get('min_credits', 12);

        // Get students who qualify for Dean's List
        $students = Student::select('students.*')
            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->where('course_sections.term_id', $termId)
            ->where('enrollments.enrollment_status', 'enrolled')
            ->groupBy('students.id')
            ->havingRaw('SUM(courses.credits) >= ?', [$minCredits])
            ->get();

        // Filter by GPA
        $deansList = [];
        foreach ($students as $student) {
            $termGPA = $this->gradeService->calculateTermGPA($student->id, $termId);
            if ($termGPA >= $minGPA) {
                $student->term_gpa = $termGPA;
                $student->total_credits = DB::table('enrollments')
                    ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
                    ->join('courses', 'course_sections.course_id', '=', 'courses.id')
                    ->where('enrollments.student_id', $student->id)
                    ->where('course_sections.term_id', $termId)
                    ->sum('courses.credits');
                $deansList[] = $student;
            }
        }

        $terms = DB::table('academic_terms')->orderBy('start_date', 'desc')->get();

        return view('admin.grades.reports.deans-list', [
            'students' => collect($deansList),
            'terms' => $terms,
            'currentTermId' => $termId,
            'minGPA' => $minGPA,
            'minCredits' => $minCredits
        ]);
    }

    /**
     * Academic Standing Report
     */
    public function academicStanding(Request $request)
    {
        if (!auth()->user()->hasRole(['admin', 'registrar', 'academic-administrator'])) {
            abort(403, 'Unauthorized access');
        }

        $students = Student::with(['enrollments.grades'])->paginate(50);

        foreach ($students as $student) {
            $gpa = $this->gradeService->calculateCumulativeGPA($student->id);
            $student->cumulative_gpa = $gpa;
            
            // Determine academic standing
            if ($gpa >= 3.5) {
                $student->standing = 'Excellent';
                $student->standing_class = 'success';
            } elseif ($gpa >= 2.5) {
                $student->standing = 'Good Standing';
                $student->standing_class = 'primary';
            } elseif ($gpa >= 2.0) {
                $student->standing = 'Warning';
                $student->standing_class = 'warning';
            } else {
                $student->standing = 'Probation';
                $student->standing_class = 'danger';
            }
        }

        return view('admin.grades.reports.standing', compact('students'));
    }

    /**
     * Grade Distribution Report
     */
    public function gradeDistribution(Request $request)
    {
        if (!auth()->user()->hasRole(['admin', 'registrar', 'department-head'])) {
            abort(403, 'Unauthorized access');
        }

        $termId = $request->get('term_id');
        $courseId = $request->get('course_id');

        $query = Grade::query();

        if ($termId) {
            $query->whereHas('enrollment.section', function($q) use ($termId) {
                $q->where('term_id', $termId);
            });
        }

        if ($courseId) {
            $query->whereHas('enrollment.section', function($q) use ($courseId) {
                $q->where('course_id', $courseId);
            });
        }

        $distribution = $query->select('letter_grade', DB::raw('count(*) as count'))
            ->whereNotNull('letter_grade')
            ->groupBy('letter_grade')
            ->orderBy('letter_grade')
            ->get();

        $terms = DB::table('academic_terms')->orderBy('start_date', 'desc')->get();
        $courses = DB::table('courses')->orderBy('code')->get();

        return view('admin.grades.reports.distribution', compact('distribution', 'terms', 'courses'));
    }

    /**
     * Get current term ID
     */
    private function getCurrentTermId()
    {
        return DB::table('academic_terms')
            ->where('is_current', true)
            ->value('id');
    }
}