<?php

// ============================================================
// File: app/Http/Controllers/AttendanceController.php
// ============================================================

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\AttendancePolicy;
use App\Models\AttendanceExcuse;
use App\Models\AttendanceStatistic;
use App\Models\AttendanceAlert;
use App\Models\CourseSection;
use App\Models\Student;
use App\Models\Enrollment;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $attendanceService;
    
    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Faculty: View attendance dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get sections taught by faculty
        $sections = CourseSection::where(function($query) use ($user) {
            $query->where('primary_instructor_id', $user->id)
                  ->orWhere('secondary_instructor_id', $user->id);
        })
        ->whereHas('term', function($query) {
            $query->where('is_current', true);
        })
        ->with(['course', 'term'])
        ->get();
        
        $selectedSection = null;
        $sessions = collect();
        $statistics = null;
        
        if ($request->has('section_id')) {
            $selectedSection = $sections->find($request->section_id);
            
            if ($selectedSection) {
                $sessions = AttendanceSession::where('section_id', $selectedSection->id)
                    ->orderBy('session_date', 'desc')
                    ->orderBy('start_time', 'desc')
                    ->paginate(20);
                
                $statistics = $this->attendanceService->getSectionStatistics($selectedSection->id);
            }
        }
        
        return view('attendance.index', compact('sections', 'selectedSection', 'sessions', 'statistics'));
    }

    /**
     * Faculty: Take attendance for a session
     */
    public function takeAttendance($sectionId, $date = null)
    {
        $section = CourseSection::findOrFail($sectionId);
        
        // Check permission
        $this->authorize('takeAttendance', $section);
        
        $date = $date ? Carbon::parse($date) : now();
        
        // Get or create session
        $session = AttendanceSession::firstOrCreate(
            [
                'section_id' => $section->id,
                'session_date' => $date->format('Y-m-d'),
                'start_time' => $section->schedule->start_time ?? '09:00:00'
            ],
            [
                'end_time' => $section->schedule->end_time ?? '10:00:00',
                'session_type' => 'regular',
                'location' => $section->room
            ]
        );
        
        // Get enrolled students
        $students = Student::whereHas('enrollments', function($query) use ($section) {
            $query->where('section_id', $section->id)
                  ->where('status', 'enrolled');
        })
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();
        
        // Get existing attendance records
        $attendanceRecords = AttendanceRecord::where('session_id', $session->id)
            ->pluck('status', 'student_id');
        
        return view('attendance.take', compact('section', 'session', 'students', 'attendanceRecords'));
    }

    /**
     * Faculty: Save attendance records
     */
    public function saveAttendance(Request $request, $sessionId)
    {
        $session = AttendanceSession::findOrFail($sessionId);
        $section = $session->section;
        
        // Check permission
        $this->authorize('takeAttendance', $section);
        
        $validated = $request->validate([
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent,late,excused,sick,left_early',
            'attendance.*.remarks' => 'nullable|string|max:500',
            'notes' => 'nullable|string'
        ]);
        
        DB::transaction(function() use ($session, $validated) {
            // Update session
            $session->attendance_taken = true;
            $session->marked_by = Auth::id();
            $session->marked_at = now();
            $session->notes = $validated['notes'] ?? null;
            $session->save();
            
            // Save attendance records
            foreach ($validated['attendance'] as $record) {
                AttendanceRecord::updateOrCreate(
                    [
                        'session_id' => $session->id,
                        'student_id' => $record['student_id']
                    ],
                    [
                        'status' => $record['status'],
                        'remarks' => $record['remarks'] ?? null,
                        'minutes_late' => $record['status'] === 'late' ? 15 : 0 // Default 15 mins
                    ]
                );
            }
            
            // Update statistics
            $this->attendanceService->updateStatistics($session->section_id);
            
            // Check for alerts
            $this->attendanceService->checkAndCreateAlerts($session->section_id);
        });
        
        return redirect()->route('attendance.section', $section->id)
            ->with('success', 'Attendance saved successfully');
    }

    /**
     * Faculty: View section attendance report
     */
    public function sectionReport($sectionId)
    {
        $section = CourseSection::findOrFail($sectionId);
        
        // Check permission
        $this->authorize('viewAttendance', $section);
        
        $statistics = AttendanceStatistic::where('section_id', $section->id)
            ->with('student')
            ->orderBy('attendance_percentage', 'asc')
            ->get();
        
        $sessions = AttendanceSession::where('section_id', $section->id)
            ->where('attendance_taken', true)
            ->orderBy('session_date', 'desc')
            ->get();
        
        $policy = $this->attendanceService->getSectionPolicy($section->id);
        
        return view('attendance.section-report', compact('section', 'statistics', 'sessions', 'policy'));
    }

    /**
     * Student: View own attendance
     */
    public function studentAttendance(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        
        // Get current enrollments
        $enrollments = Enrollment::where('student_id', $student->id)
            ->whereHas('section.term', function($query) {
                $query->where('is_current', true);
            })
            ->with(['section.course'])
            ->get();
        
        $attendanceData = [];
        
        foreach ($enrollments as $enrollment) {
            $statistics = AttendanceStatistic::where('student_id', $student->id)
                ->where('section_id', $enrollment->section_id)
                ->first();
            
            $records = AttendanceRecord::whereHas('session', function($query) use ($enrollment) {
                $query->where('section_id', $enrollment->section_id);
            })
            ->where('student_id', $student->id)
            ->with('session')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
            $attendanceData[] = [
                'enrollment' => $enrollment,
                'statistics' => $statistics,
                'recent_records' => $records
            ];
        }
        
        // Get active excuses
        $excuses = AttendanceExcuse::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('attendance.student', compact('attendanceData', 'excuses'));
    }

    /**
     * Student: Submit excuse request
     */
    public function submitExcuse(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'excuse_type' => 'required|in:medical,family_emergency,university_activity,other',
            'reason' => 'required|string|max:1000',
            'supporting_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'apply_to_all_courses' => 'boolean',
            'applicable_sections' => 'nullable|array',
            'applicable_sections.*' => 'exists:course_sections,id'
        ]);
        
        if ($request->hasFile('supporting_document')) {
            $path = $request->file('supporting_document')->store('excuses', 'public');
            $validated['supporting_document'] = $path;
        }
        
        $validated['student_id'] = $student->id;
        $validated['status'] = 'pending';
        
        if (!$validated['apply_to_all_courses'] && isset($validated['applicable_sections'])) {
            $validated['applicable_sections'] = json_encode($validated['applicable_sections']);
        }
        
        AttendanceExcuse::create($validated);
        
        return redirect()->route('attendance.student')
            ->with('success', 'Excuse request submitted successfully');
    }

    /**
     * Faculty: Review excuse requests
     */
    public function reviewExcuses(Request $request)
    {
        $user = Auth::user();
        
        // Get sections taught by faculty
        $sectionIds = CourseSection::where(function($query) use ($user) {
            $query->where('primary_instructor_id', $user->id)
                  ->orWhere('secondary_instructor_id', $user->id);
        })->pluck('id');
        
        // Get excuse requests for students in their sections
        $excuses = AttendanceExcuse::whereHas('student.enrollments', function($query) use ($sectionIds) {
            $query->whereIn('section_id', $sectionIds);
        })
        ->where('status', 'pending')
        ->with(['student'])
        ->orderBy('created_at', 'desc')
        ->paginate(20);
        
        return view('attendance.review-excuses', compact('excuses'));
    }

    /**
     * Faculty: Process excuse request
     */
    public function processExcuse(Request $request, $excuseId)
    {
        $excuse = AttendanceExcuse::findOrFail($excuseId);
        
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'review_notes' => 'nullable|string|max:500'
        ]);
        
        DB::transaction(function() use ($excuse, $validated) {
            $excuse->status = $validated['action'] === 'approve' ? 'approved' : 'rejected';
            $excuse->reviewed_by = Auth::id();
            $excuse->reviewed_at = now();
            $excuse->review_notes = $validated['review_notes'] ?? null;
            $excuse->save();
            
            // If approved, update attendance records
            if ($excuse->status === 'approved') {
                $this->attendanceService->applyExcuse($excuse);
            }
        });
        
        return redirect()->route('attendance.excuses')
            ->with('success', 'Excuse request processed successfully');
    }

    /**
     * Admin: Manage attendance policies
     */
    public function policies()
    {
        $this->authorize('admin.attendance.policies');
        
        $policies = AttendancePolicy::orderBy('is_default', 'desc')
            ->orderBy('policy_name')
            ->paginate(20);
        
        return view('attendance.policies', compact('policies'));
    }

    /**
     * Admin: Create/Edit attendance policy
     */
    public function editPolicy($id = null)
    {
        $this->authorize('admin.attendance.policies');
        
        $policy = $id ? AttendancePolicy::findOrFail($id) : new AttendancePolicy();
        
        return view('attendance.policy-edit', compact('policy'));
    }

    /**
     * Admin: Save attendance policy
     */
    public function savePolicy(Request $request, $id = null)
    {
        $this->authorize('admin.attendance.policies');
        
        $validated = $request->validate([
            'policy_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_absences' => 'nullable|integer|min:0',
            'max_late_arrivals' => 'nullable|integer|min:0',
            'late_threshold_minutes' => 'required|integer|min:1|max:60',
            'attendance_weight' => 'required|numeric|min:0|max:100',
            'auto_fail_on_excess_absence' => 'boolean',
            'auto_fail_threshold' => 'nullable|required_if:auto_fail_on_excess_absence,true|integer|min:1',
            'is_default' => 'boolean'
        ]);
        
        DB::transaction(function() use ($validated, $id) {
            if ($validated['is_default'] ?? false) {
                AttendancePolicy::where('is_default', true)->update(['is_default' => false]);
            }
            
            if ($id) {
                $policy = AttendancePolicy::findOrFail($id);
                $policy->update($validated);
            } else {
                AttendancePolicy::create($validated);
            }
        });
        
        return redirect()->route('attendance.policies')
            ->with('success', 'Attendance policy saved successfully');
    }

    /**
     * View attendance analytics
     */
    public function analytics(Request $request)
    {
        $this->authorize('admin.attendance.analytics');
        
        $termId = $request->get('term_id', AcademicTerm::where('is_current', true)->first()->id);
        
        $data = [
            'overall_rate' => $this->attendanceService->getOverallAttendanceRate($termId),
            'by_department' => $this->attendanceService->getAttendanceByDepartment($termId),
            'by_course_level' => $this->attendanceService->getAttendanceByCourseLevel($termId),
            'trends' => $this->attendanceService->getAttendanceTrends($termId),
            'at_risk_students' => $this->attendanceService->getAtRiskStudents($termId)
        ];
        
        return view('attendance.analytics', $data);
    }
}