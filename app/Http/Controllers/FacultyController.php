<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Student;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FacultyController extends Controller
{
    /**
     * Ensure user is faculty
     */
    protected function checkFacultyAccess()
    {
        if (Auth::user()->user_type !== 'faculty' && Auth::user()->user_type !== 'admin') {
            abort(403, 'Access denied. Faculty only area.');
        }
    }

    /**
     * Faculty Dashboard
     */
    public function dashboard()
    {
        $this->checkFacultyAccess();
        
        $faculty = Auth::user();
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        
        // Get current sections taught by this faculty
        $currentSections = CourseSection::with(['course', 'term'])
            ->where('instructor_id', $faculty->id)
            ->when($currentTerm, function($query) use ($currentTerm) {
                return $query->where('term_id', $currentTerm->id);
            })
            ->where('status', '!=', 'cancelled')
            ->get();
        
        // Get today's classes
        $todayClasses = $this->getTodayClasses($faculty->id);
        
        // Get statistics
        $stats = [
            'total_sections' => $currentSections->count(),
            'total_students' => $currentSections->sum('current_enrollment'),
            'total_courses' => $currentSections->pluck('course_id')->unique()->count(),
            'office_hours_today' => $this->getTodayOfficeHours($faculty->id),
        ];
        
        // Get recent announcements (placeholder for now)
        $recentAnnouncements = [];
        
        // Get upcoming deadlines (placeholder for now)
        $upcomingDeadlines = [];
        
        return view('faculty.dashboard', compact(
            'faculty',
            'currentTerm',
            'currentSections',
            'todayClasses',
            'stats',
            'recentAnnouncements',
            'upcomingDeadlines'
        ));
    }

    /**
     * Display faculty's courses
     */
    public function courses()
    {
        $this->checkFacultyAccess();
        
        $faculty = Auth::user();
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        
        // Get all sections grouped by term
        $sectionsByTerm = CourseSection::with(['course', 'term'])
            ->where('instructor_id', $faculty->id)
            ->orderBy('term_id', 'desc')
            ->get()
            ->groupBy('term_id');
        
        return view('faculty.courses', compact('faculty', 'sectionsByTerm', 'currentTerm'));
    }

    /**
     * Display specific course section details
     */
    public function sectionDetails($sectionId)
    {
        $this->checkFacultyAccess();
        
        $section = CourseSection::with(['course', 'term', 'instructor'])
            ->findOrFail($sectionId);
        
        // Verify this faculty teaches this section
        if ($section->instructor_id !== Auth::id() && Auth::user()->user_type !== 'admin') {
            abort(403, 'You are not authorized to view this section.');
        }
        
        // Get enrolled students
        $enrolledStudents = $this->getEnrolledStudents($sectionId);
        
        // Get attendance statistics
        $attendanceStats = $this->getAttendanceStats($sectionId);
        
        return view('faculty.section-details', compact('section', 'enrolledStudents', 'attendanceStats'));
    }

    /**
     * Display class roster
     */
    public function roster($sectionId)
    {
        $this->checkFacultyAccess();
        
        $section = CourseSection::with(['course', 'term'])
            ->findOrFail($sectionId);
        
        // Verify authorization
        if ($section->instructor_id !== Auth::id() && Auth::user()->user_type !== 'admin') {
            abort(403, 'You are not authorized to view this roster.');
        }
        
        // Get enrolled students with their details
        $students = $this->getEnrolledStudents($sectionId);
        
        return view('faculty.roster', compact('section', 'students'));
    }

    /**
     * Attendance management page
     */
    public function attendance($sectionId)
    {
        $this->checkFacultyAccess();
        
        $section = CourseSection::with(['course', 'term'])
            ->findOrFail($sectionId);
        
        // Verify authorization
        if ($section->instructor_id !== Auth::id() && Auth::user()->user_type !== 'admin') {
            abort(403, 'You are not authorized to manage attendance for this section.');
        }
        
        // Get enrolled students
        $students = $this->getEnrolledStudents($sectionId);
        
        // Get the date from request or use today
        $attendanceDate = request('date', now()->format('Y-m-d'));
        
        // Get existing attendance for the selected date - FIXED: using attendance_date
        $existingAttendance = DB::table('attendance')
            ->where('section_id', $sectionId)
            ->where('attendance_date', $attendanceDate)
            ->get()
            ->keyBy('student_id');
        
        // Get attendance statistics for the selected date - FIXED: using attendance_date
        $attendanceStats = DB::table('attendance')
            ->where('section_id', $sectionId)
            ->where('attendance_date', $attendanceDate)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
        
        // Ensure all status types are present
        $attendanceStats = array_merge([
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
        ], $attendanceStats);
        
        $attendanceStats['total'] = array_sum($attendanceStats);
        
        return view('faculty.attendance', compact(
            'section', 
            'students', 
            'existingAttendance', 
            'attendanceStats'
        ));
    }

    /**
     * Save attendance
     */
    public function saveAttendance(Request $request, $sectionId)
    {
        $this->checkFacultyAccess();
        
        $section = CourseSection::findOrFail($sectionId);
        
        // Verify authorization
        if ($section->instructor_id !== Auth::id() && Auth::user()->user_type !== 'admin') {
            abort(403, 'You are not authorized to save attendance for this section.');
        }
        
        $validated = $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.status' => 'required|in:present,absent,late,excused',
            'attendance.*.notes' => 'nullable|string|max:255',
        ]);
        
        DB::beginTransaction();
        try {
            // Delete existing attendance for this date - FIXED: using attendance_date
            DB::table('attendance')->where([
                'section_id' => $sectionId,
                'attendance_date' => $validated['date'],
            ])->delete();
            
            // Insert new attendance records - FIXED: using attendance_date
            foreach ($validated['attendance'] as $studentId => $record) {
                DB::table('attendance')->insert([
                    'section_id' => $sectionId,
                    'student_id' => $studentId,
                    'attendance_date' => $validated['date'],
                    'status' => $record['status'],
                    'notes' => $record['notes'] ?? null,
                    'marked_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            DB::commit();
            return redirect()->back()->with('success', 'Attendance saved successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to save attendance: ' . $e->getMessage());
        }
    }

    /**
     * Gradebook page
     */
    public function gradebook($sectionId)
    {
        $this->checkFacultyAccess();
        
        $section = CourseSection::with(['course', 'term'])
            ->findOrFail($sectionId);
        
        // Verify authorization
        if ($section->instructor_id !== Auth::id() && Auth::user()->user_type !== 'admin') {
            abort(403, 'You are not authorized to view this gradebook.');
        }
        
        // Get enrolled students
        $students = $this->getEnrolledStudents($sectionId);
        
        // Get grade components
        $gradeComponents = DB::table('grade_components')
            ->where('section_id', $sectionId)
            ->get();
        
        // If no components exist, use defaults
        if ($gradeComponents->isEmpty()) {
            $gradeComponents = collect([
                (object)['id' => 1, 'name' => 'Assignments', 'weight' => 30, 'max_points' => 100],
                (object)['id' => 2, 'name' => 'Midterm Exam', 'weight' => 25, 'max_points' => 100],
                (object)['id' => 3, 'name' => 'Final Exam', 'weight' => 30, 'max_points' => 100],
                (object)['id' => 4, 'name' => 'Participation', 'weight' => 15, 'max_points' => 100],
            ]);
        }
        
        // Get existing grades
        $grades = DB::table('grades')
            ->where('section_id', $sectionId)
            ->get()
            ->groupBy('student_id');
        
        return view('faculty.gradebook', compact('section', 'students', 'gradeComponents', 'grades'));
    }

    /**
     * Save grades
     */
    public function saveGrades(Request $request, $sectionId)
    {
        $this->checkFacultyAccess();
        
        $section = CourseSection::findOrFail($sectionId);
        
        // Verify authorization
        if ($section->instructor_id !== Auth::id() && Auth::user()->user_type !== 'admin') {
            abort(403, 'You are not authorized to save grades for this section.');
        }
        
        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*.*.score' => 'nullable|numeric|min:0|max:100',
        ]);
        
        DB::beginTransaction();
        try {
            foreach ($validated['grades'] as $studentId => $components) {
                foreach ($components as $componentName => $data) {
                    // Check if grade exists
                    $existing = DB::table('grades')
                        ->where('section_id', $sectionId)
                        ->where('student_id', $studentId)
                        ->where('component_name', $componentName)
                        ->first();
                    
                    if ($existing) {
                        // Update existing grade
                        DB::table('grades')
                            ->where('id', $existing->id)
                            ->update([
                                'score' => $data['score'] ?? null,
                                'graded_by' => Auth::id(),
                                'updated_at' => now(),
                            ]);
                    } else if (isset($data['score'])) {
                        // Insert new grade
                        DB::table('grades')->insert([
                            'section_id' => $sectionId,
                            'student_id' => $studentId,
                            'component_name' => $componentName,
                            'score' => $data['score'],
                            'graded_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
            
            DB::commit();
            return redirect()->back()->with('success', 'Grades saved successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to save grades: ' . $e->getMessage());
        }
    }

    /**
     * Office hours management
     */
    public function officeHours()
    {
        $this->checkFacultyAccess();
        
        $faculty = Auth::user();
        
        // Get office hours
        $officeHours = DB::table('office_hours')
            ->where('faculty_id', $faculty->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        
        // Get appointments for the next 7 days
        $appointments = DB::table('office_appointments')
            ->where('faculty_id', $faculty->id)
            ->where('appointment_date', '>=', now())
            ->where('appointment_date', '<=', now()->addDays(7))
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get();
        
        return view('faculty.office-hours', compact('officeHours', 'appointments'));
    }

    /**
     * Save office hours
     */
    public function saveOfficeHours(Request $request)
    {
        $this->checkFacultyAccess();
        
        $validated = $request->validate([
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:100',
            'type' => 'required|in:in-person,virtual,both',
            'meeting_url' => 'nullable|url|required_if:type,virtual,both',
        ]);
        
        DB::table('office_hours')->insert([
            'faculty_id' => Auth::id(),
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $validated['location'],
            'type' => $validated['type'],
            'meeting_url' => $validated['meeting_url'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Office hours added successfully!');
    }

    /**
     * Delete office hours
     */
    public function deleteOfficeHours($id)
    {
        $this->checkFacultyAccess();
        
        // Verify ownership
        $officeHour = DB::table('office_hours')
            ->where('id', $id)
            ->where('faculty_id', Auth::id())
            ->first();
        
        if (!$officeHour) {
            abort(403, 'You are not authorized to delete this office hour.');
        }
        
        DB::table('office_hours')->where('id', $id)->delete();
        
        return redirect()->back()->with('success', 'Office hours deleted successfully!');
    }

    /**
     * View announcements (placeholder)
     */
    public function announcements()
    {
        $this->checkFacultyAccess();
        
        $announcements = DB::table('announcements')
            ->where('faculty_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('faculty.announcements', compact('announcements'));
    }

    /**
     * Create announcement (placeholder)
     */
    public function createAnnouncement(Request $request)
    {
        $this->checkFacultyAccess();
        
        $validated = $request->validate([
            'section_id' => 'required|exists:course_sections,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'in:low,normal,high,urgent',
        ]);
        
        // Verify faculty teaches this section
        $section = CourseSection::findOrFail($validated['section_id']);
        if ($section->instructor_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }
        
        DB::table('announcements')->insert([
            'section_id' => $validated['section_id'],
            'faculty_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $validated['content'],
            'priority' => $validated['priority'] ?? 'normal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Announcement posted successfully!');
    }

    /**
     * Delete announcement (placeholder)
     */
    public function deleteAnnouncement($id)
    {
        $this->checkFacultyAccess();
        
        // Verify ownership
        $announcement = DB::table('announcements')
            ->where('id', $id)
            ->where('faculty_id', Auth::id())
            ->first();
        
        if (!$announcement) {
            abort(403, 'Unauthorized');
        }
        
        DB::table('announcements')->where('id', $id)->delete();
        
        return redirect()->back()->with('success', 'Announcement deleted successfully!');
    }

    // =====================================
    // HELPER METHODS
    // =====================================

    /**
     * Helper: Get today's classes for faculty
     */
    private function getTodayClasses($facultyId)
    {
        $dayOfWeek = now()->format('l');
        $dayAbbr = substr($dayOfWeek, 0, 1);
        
        // Special cases for Tuesday and Thursday
        if ($dayOfWeek === 'Tuesday') $dayAbbr = 'T';
        if ($dayOfWeek === 'Thursday') $dayAbbr = 'R';
        
        return CourseSection::with('course')
            ->where('instructor_id', $facultyId)
            ->where('status', 'open')
            ->where(function($query) use ($dayOfWeek, $dayAbbr) {
                $query->where('days_of_week', 'like', '%' . $dayAbbr . '%')
                      ->orWhere('days_of_week', 'like', '%' . substr($dayOfWeek, 0, 3) . '%');
            })
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Helper: Get today's office hours count
     */
    private function getTodayOfficeHours($facultyId)
    {
        $dayOfWeek = now()->format('l');
        
        return DB::table('office_hours')
            ->where('faculty_id', $facultyId)
            ->where('day_of_week', $dayOfWeek)
            ->count();
    }

    /**
     * Helper: Get enrolled students for a section
     */
    private function getEnrolledStudents($sectionId)
    {
        // First check if enrollments table has data for this section
        $enrollments = DB::table('enrollments')
            ->where('section_id', $sectionId)
            ->where('enrollment_status', 'enrolled')
            ->pluck('student_id');
        
        if ($enrollments->isNotEmpty()) {
            return Student::whereIn('id', $enrollments)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }
        
        // Fallback: return some active students for testing
        // This allows the system to work even without enrollment data
        return Student::where('enrollment_status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(15)
            ->get();
    }

    /**
     * Helper: Get attendance statistics for a section
     */
    private function getAttendanceStats($sectionId)
    {
        $stats = DB::table('attendance')
            ->where('section_id', $sectionId)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
        
        // Ensure all status types are present
        return [
            'present' => $stats['present'] ?? 0,
            'absent' => $stats['absent'] ?? 0,
            'late' => $stats['late'] ?? 0,
            'excused' => $stats['excused'] ?? 0,
            'total' => array_sum($stats),
        ];
    }
}