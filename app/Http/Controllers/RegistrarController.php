<?php
// ==============================================================
// File: app/Http/Controllers/RegistrarController.php
// ==============================================================

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\CourseSection;
use App\Models\Enrollment;
use App\Models\TranscriptRequest;
use App\Models\Grade;
use App\Models\AcademicTerm;
use Illuminate\Support\Facades\DB;

class RegistrarController extends Controller
{
    /**
     * Registrar dashboard
     */
    public function dashboard()
    {
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        
        $stats = [
            'total_students' => Student::count(),
            'active_enrollments' => Enrollment::where('status', 'enrolled')->count(),
            'pending_transcripts' => TranscriptRequest::where('status', 'pending')->count(),
            'pending_grades' => Grade::where('is_final', false)->count(),
            'current_term' => $currentTerm,
        ];
        
        $recentRequests = TranscriptRequest::with('student')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return view('registrar.dashboard', compact('stats', 'recentRequests'));
    }
    
    /**
     * Student index for registrar
     */
    public function studentIndex()
    {
        $students = Student::with('user', 'program')
            ->paginate(20);
            
        return view('registrar.students.index', compact('students'));
    }
    
    /**
     * Search students
     */
    public function searchStudents(Request $request)
    {
        $query = Student::with('user', 'program');
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $students = $query->paginate(20);
        
        return view('registrar.students.index', compact('students'));
    }
    
    /**
     * View student details
     */
    public function viewStudent(Student $student)
    {
        $student->load('user', 'program', 'enrollments.section.course');
        
        return view('registrar.students.view', compact('student'));
    }
    
    /**
     * Student academic record
     */
    public function academicRecord(Student $student)
    {
        $enrollments = Enrollment::where('student_id', $student->id)
            ->with('section.course', 'grades')
            ->get();
            
        return view('registrar.students.academic-record', compact('student', 'enrollments'));
    }
    
    /**
     * Student enrollment history
     */
    public function enrollmentHistory(Student $student)
    {
        $enrollments = Enrollment::where('student_id', $student->id)
            ->with('section.course', 'section.term')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('registrar.students.enrollment-history', compact('student', 'enrollments'));
    }
    
    /**
     * Student grade history
     */
    public function gradeHistory(Student $student)
    {
        $grades = Grade::whereHas('enrollment', function($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->with('enrollment.section.course')
            ->get();
            
        return view('registrar.students.grade-history', compact('student', 'grades'));
    }
    
    /**
     * View student transcript
     */
    public function viewTranscript(Student $student)
    {
        $transcriptData = $this->generateTranscriptData($student);
        
        return view('registrar.students.transcript', compact('student', 'transcriptData'));
    }
    
    /**
     * Add note to student record
     */
    public function addNote(Request $request, Student $student)
    {
        // Implementation for adding notes
        return back()->with('success', 'Note added successfully');
    }
    
    /**
     * Update student record
     */
    public function updateRecord(Request $request, Student $student)
    {
        $student->update($request->validated());
        
        return back()->with('success', 'Student record updated successfully');
    }
    
    /**
     * Add hold to student account
     */
    public function addHold(Request $request, Student $student)
    {
        // Implementation for adding holds
        return back()->with('success', 'Hold added successfully');
    }
    
    /**
     * Remove hold from student account
     */
    public function removeHold(Student $student, $hold)
    {
        // Implementation for removing holds
        return back()->with('success', 'Hold removed successfully');
    }
    
    /**
     * Reports index
     */
    public function reportsIndex()
    {
        return view('registrar.reports.index');
    }
    
    /**
     * Enrollment report
     */
    public function enrollmentReport()
    {
        $data = [
            'total_enrollments' => Enrollment::count(),
            'by_term' => Enrollment::select('term_id', DB::raw('count(*) as total'))
                ->groupBy('term_id')
                ->with('term')
                ->get(),
        ];
        
        return view('registrar.reports.enrollment', compact('data'));
    }
    
    /**
     * Academic standing report
     */
    public function academicStandingReport()
    {
        return view('registrar.reports.academic-standing');
    }
    
    /**
     * Retention report
     */
    public function retentionReport()
    {
        return view('registrar.reports.retention');
    }
    
    /**
     * Graduation rate report
     */
    public function graduationRateReport()
    {
        return view('registrar.reports.graduation-rate');
    }
    
    /**
     * Custom report
     */
    public function customReport()
    {
        return view('registrar.reports.custom');
    }
    
    /**
     * Generate report
     */
    public function generateReport(Request $request)
    {
        // Implementation for report generation
        return back()->with('success', 'Report generated successfully');
    }
    
    /**
     * Export report
     */
    public function exportReport($report)
    {
        // Implementation for report export
        return response()->download("reports/{$report}.pdf");
    }
    
    /**
     * Helper method to generate transcript data
     */
    private function generateTranscriptData(Student $student)
    {
        return [
            'enrollments' => Enrollment::where('student_id', $student->id)
                ->with('section.course', 'grades')
                ->get(),
            'gpa' => $this->calculateGPA($student),
            'total_credits' => $this->calculateTotalCredits($student),
        ];
    }
    
    /**
     * Calculate GPA
     */
    private function calculateGPA(Student $student)
    {
        // Implementation for GPA calculation
        return 3.5; // Placeholder
    }
    
    /**
     * Calculate total credits
     */
    private function calculateTotalCredits(Student $student)
    {
        // Implementation for credit calculation
        return 120; // Placeholder
    }
    
    /**
     * Quick stats for dashboard widgets
     */
    public function quickStats()
    {
        $stats = [
            'pending_transcripts' => TranscriptRequest::where('status', 'pending')->count(),
            'pending_grades' => Grade::where('is_final', false)->count(),
            'active_holds' => DB::table('student_holds')->where('is_active', true)->count(),
            'graduation_applications' => DB::table('graduation_applications')
                ->where('status', 'pending')->count(),
        ];
        
        return response()->json($stats);
    }
    
    /**
     * Get pending tasks
     */
    public function pendingTasks()
    {
        $tasks = [
            'transcript_requests' => TranscriptRequest::where('status', 'pending')
                ->with('student')->limit(5)->get(),
            'grade_changes' => DB::table('grade_change_requests')
                ->where('status', 'pending')->limit(5)->get(),
            'enrollment_verifications' => DB::table('enrollment_verifications')
                ->where('status', 'pending')->limit(5)->get(),
        ];
        
        return response()->json($tasks);
    }
    
    /**
     * Get registrar alerts
     */
    public function registrarAlerts()
    {
        $alerts = [];
        
        // Check for grade submission deadlines
        $gradeDeadline = DB::table('academic_deadlines')
            ->where('type', 'grade_submission')
            ->where('deadline', '<=', now()->addDays(3))
            ->first();
            
        if ($gradeDeadline) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Grade submission deadline approaching',
                'date' => $gradeDeadline->deadline
            ];
        }
        
        return response()->json($alerts);
    }
    
    /**
     * Academic calendar view
     */
    public function academicCalendar()
    {
        $events = DB::table('academic_calendar')
            ->where('start_date', '>=', now()->startOfMonth())
            ->where('end_date', '<=', now()->endOfMonth()->addMonths(2))
            ->get();
            
        return view('registrar.calendar', compact('events'));
    }

}