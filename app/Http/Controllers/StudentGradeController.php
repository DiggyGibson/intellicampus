<?php
// Save as: app/Http/Controllers/StudentGradeController.php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\AcademicTerm;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentGradeController extends Controller
{
    protected $gradeService;

    public function __construct(GradeCalculationService $gradeService)
    {
        $this->gradeService = $gradeService;
    }

    /**
     * Display student grades dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return view('students.grades.index', [
                'error' => 'Student record not found.'
            ]);
        }

        // Get current term
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        
        // Get all enrollments with grades
        $enrollments = Enrollment::with(['section.course', 'grades'])
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate GPA
        $currentGPA = $this->gradeService->calculateTermGPA($student->id, $currentTerm->id ?? null);
        $cumulativeGPA = $this->gradeService->calculateCumulativeGPA($student->id);

        return view('students.grades.index', compact(
            'student',
            'enrollments',
            'currentTerm',
            'currentGPA',
            'cumulativeGPA'
        ));
    }

    /**
     * Display current semester grades
     */
    public function currentGrades()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return redirect()->route('student.grades')
                ->with('error', 'Student record not found.');
        }

        $currentTerm = AcademicTerm::where('is_current', true)->first();
        
        if (!$currentTerm) {
            return view('students.grades.current', [
                'error' => 'No active term found.'
            ]);
        }

        $enrollments = Enrollment::with(['section.course', 'grades.component'])
            ->where('student_id', $student->id)
            ->whereHas('section', function($query) use ($currentTerm) {
                $query->where('term_id', $currentTerm->id);
            })
            ->get();

        // Calculate grades for each enrollment
        foreach ($enrollments as $enrollment) {
            $enrollment->calculated_grade = $this->gradeService->calculateGrade($enrollment->id);
        }

        return view('students.grades.current', compact(
            'student',
            'enrollments',
            'currentTerm'
        ));
    }

    /**
     * Display grade history
     */
    public function gradeHistory()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return redirect()->route('student.grades')
                ->with('error', 'Student record not found.');
        }

        // Get all terms with grades
        $terms = AcademicTerm::whereHas('sections.enrollments', function($query) use ($student) {
            $query->where('student_id', $student->id);
        })
        ->orderBy('start_date', 'desc')
        ->get();

        $gradeHistory = [];
        
        foreach ($terms as $term) {
            $termData = [
                'term' => $term,
                'enrollments' => Enrollment::with(['section.course', 'grades'])
                    ->where('student_id', $student->id)
                    ->whereHas('section', function($query) use ($term) {
                        $query->where('term_id', $term->id);
                    })
                    ->get(),
                'term_gpa' => $this->gradeService->calculateTermGPA($student->id, $term->id)
            ];
            
            $gradeHistory[] = $termData;
        }

        return view('students.grades.history', compact(
            'student',
            'gradeHistory'
        ));
    }

    /**
     * Display grades for a specific term
     */
    public function termGrades($termId)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return redirect()->route('student.grades')
                ->with('error', 'Student record not found.');
        }

        $term = AcademicTerm::findOrFail($termId);
        
        $enrollments = Enrollment::with(['section.course', 'grades.component'])
            ->where('student_id', $student->id)
            ->whereHas('section', function($query) use ($termId) {
                $query->where('term_id', $termId);
            })
            ->get();

        // Calculate grades
        foreach ($enrollments as $enrollment) {
            $enrollment->calculated_grade = $this->gradeService->calculateGrade($enrollment->id);
        }

        $termGPA = $this->gradeService->calculateTermGPA($student->id, $termId);

        return view('students.grades.term', compact(
            'student',
            'term',
            'enrollments',
            'termGPA'
        ));
    }

    /**
     * Display GPA details
     */
    public function gpaDetails()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return redirect()->route('student.grades')
                ->with('error', 'Student record not found.');
        }

        // Calculate various GPAs
        $cumulativeGPA = $this->gradeService->calculateCumulativeGPA($student->id);
        $majorGPA = $this->gradeService->calculateMajorGPA($student->id);
        
        // Get term-by-term GPA
        $termGPAs = [];
        $terms = AcademicTerm::orderBy('start_date', 'desc')->limit(8)->get();
        
        foreach ($terms as $term) {
            $termGPA = $this->gradeService->calculateTermGPA($student->id, $term->id);
            if ($termGPA > 0) {
                $termGPAs[] = [
                    'term' => $term,
                    'gpa' => $termGPA
                ];
            }
        }

        // Get credit summary
        $creditSummary = DB::table('enrollments')
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->where('enrollments.student_id', $student->id)
            ->select(
                DB::raw('SUM(CASE WHEN enrollments.grade IS NOT NULL THEN courses.credits ELSE 0 END) as earned_credits'),
                DB::raw('SUM(courses.credits) as attempted_credits')
            )
            ->first();

        return view('students.grades.gpa', compact(
            'student',
            'cumulativeGPA',
            'majorGPA',
            'termGPAs',
            'creditSummary'
        ));
    }

    /**
     * GPA Calculator tool
     */
    public function gpaCalculator()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return redirect()->route('student.grades')
                ->with('error', 'Student record not found.');
        }

        $currentGPA = $this->gradeService->calculateCumulativeGPA($student->id);
        
        return view('students.grades.gpa-calculator', compact('student', 'currentGPA'));
    }

    /**
     * Degree audit
     */
    public function degreeAudit()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return redirect()->route('student.grades')
                ->with('error', 'Student record not found.');
        }

        // This would integrate with degree requirements
        // For now, return a placeholder
        return view('students.grades.degree-audit', compact('student'));
    }
}