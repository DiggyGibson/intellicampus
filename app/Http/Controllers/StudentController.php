<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Registration;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\Enrollment;
use App\Models\AcademicTerm;
use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\FinancialTransaction;
use App\Models\AcademicProgram;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class StudentController extends Controller
{
    /**
     * Constructor to apply middleware
     */
    public function __construct()
    {
        // Ensure only authenticated students can access these methods
        $this->middleware(['auth', 'verified']);
        
        // Apply student-specific middleware for student portal routes
        $this->middleware(function ($request, $next) {
            if (Auth::check()) {
                $user = Auth::user();
                
                // For student-specific routes, verify they are a student
                if ($request->routeIs('student.*')) {
                    if (!$user->hasRole('student') && !$user->hasRole('super-administrator')) {
                        abort(403, 'Access denied. Student portal is for students only.');
                    }
                }
            }
            return $next($request);
        })->only(['dashboard', 'profile', 'academicOverview', 'mySchedule']);
    }

    // ============================================================
    // STUDENT PORTAL METHODS (From First Controller)
    // ============================================================

    /**
     * Student Portal Dashboard
     * This is the main dashboard for students when they log in
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Get the student record
        $student = Student::where('user_id', $user->id)->first();
        
        // If super-admin viewing, allow without student record
        if (!$student && !$user->hasRole('super-administrator')) {
            // Check if this is a regular student user without a student record
            if ($user->hasRole('student')) {
                // Try to find by email instead
                $student = Student::where('email', $user->email)->first();
                
                if (!$student) {
                    // Create a basic student record if none exists
                    $student = $this->createBasicStudentRecord($user);
                }
            } else {
                return redirect()->route('dashboard')
                    ->with('warning', 'Student profile not found. Please contact the registrar.');
            }
        }

        // Get current term
        $currentTerm = $this->getCurrentTerm();
        
        // Get student's current enrollments
        $currentEnrollments = $student ? $this->getCurrentEnrollments($student, $currentTerm) : collect();
        
        // Get recent grades
        $recentGrades = $student ? $this->getRecentGrades($student) : collect();
        
        // Calculate GPA
        $gpaData = $student ? $this->calculateGPA($student) : $this->getDefaultGPAData();
        
        // Get upcoming assignments/deadlines
        $upcomingDeadlines = $student ? $this->getUpcomingDeadlines($student) : collect();
        
        // Get announcements
        $announcements = $this->getAnnouncements($student);
        
        // Get attendance summary
        $attendanceSummary = $student ? $this->getAttendanceSummary($student, $currentTerm) : $this->getDefaultAttendanceSummary();
        
        // Get financial summary
        $financialSummary = $student ? $this->getFinancialSummary($student) : $this->getDefaultFinancialSummary();
        
        // Get academic progress
        $academicProgress = $student ? $this->getAcademicProgress($student) : $this->getDefaultAcademicProgress();
        
        // Get schedule for today
        $todaySchedule = $student ? $this->getTodaySchedule($student) : collect();
        
        // Get notification count
        $notificationCount = $this->getNotificationCount($user);

        return view('students.dashboard', compact(
            'student',
            'currentTerm',
            'currentEnrollments',
            'recentGrades',
            'gpaData',
            'upcomingDeadlines',
            'announcements',
            'attendanceSummary',
            'financialSummary',
            'academicProgress',
            'todaySchedule',
            'notificationCount'
        ));
    }

    /**
     * Display dashboard statistics (AJAX)
     */
    public function dashboardStats()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return response()->json(['error' => 'Student profile not found'], 404);
        }

        $stats = [
            'current_gpa' => $student->cumulative_gpa ?? 0,
            'credits_earned' => $student->credits_completed ?? 0,
            'courses_enrolled' => Registration::where('student_id', $student->id)
                ->where('status', 'enrolled')
                ->count(),
            'pending_assignments' => 0, // Implement based on LMS integration
            'attendance_rate' => $this->calculateAttendanceRate($student),
            'account_balance' => $this->getAccountBalance($student),
        ];

        return response()->json($stats);
    }

    /**
     * Display dashboard announcements (AJAX)
     */
    public function dashboardAnnouncements()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        $announcements = Announcement::where(function($query) use ($student) {
                $query->where('target_audience', 'all')
                    ->orWhere('target_audience', 'students');
            })
            ->where('is_active', true)
            ->where('publish_date', '<=', now())
            ->where(function($query) {
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now());
            })
            ->orderBy('priority', 'desc')
            ->orderBy('publish_date', 'desc')
            ->limit(5)
            ->get();

        return response()->json($announcements);
    }

    /**
     * Display upcoming events/deadlines (AJAX)
     */
    public function dashboardUpcoming()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        $upcoming = [];
        
        // Add assignment deadlines, exam dates, registration deadlines, etc.
        // This would integrate with various modules
        
        return response()->json($upcoming);
    }

    // ============================================================
    // ADMIN CRUD METHODS (From Second Controller)
    // ============================================================

    /**
     * Display a listing of students (Admin/Registrar view)
     */
    public function index(Request $request)
    {
        // Check permissions
        if (!Auth::user()->hasAnyRole(['admin', 'super-administrator', 'registrar', 'academic-administrator'])) {
            abort(403, 'Unauthorized access');
        }

        $query = Student::query();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('student_id', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('program_name', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('enrollment_status', $request->status);
        }

        // Filter by academic level
        if ($request->has('level') && $request->level != '') {
            $query->where('academic_level', $request->level);
        }

        // Filter by department
        if ($request->has('department') && $request->department != '') {
            $query->where('department', $request->department);
        }

        // Apply scope if user is department-level
        if (Auth::user()->hasRole('department-head')) {
            $query->where('department', Auth::user()->department_id);
        }

        // Pagination with search parameters
        $students = $query->orderBy('created_at', 'desc')
                         ->orderBy('last_name', 'asc')
                         ->paginate(20)
                         ->withQueryString();

        return view('students.index', compact('students'));
    }

    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        // Check permissions
        if (!Auth::user()->hasAnyRole(['admin', 'super-administrator', 'registrar'])) {
            abort(403, 'Unauthorized access');
        }

        $programs = AcademicProgram::where('is_active', true)->get();
        $terms = AcademicTerm::where('is_active', true)->get();
        
        return view('students.create', compact('programs', 'terms'));
    }

    /**
     * Store a newly created student
     */
    public function store(Request $request)
    {
        // Check permissions
        if (!Auth::user()->hasAnyRole(['admin', 'super-administrator', 'registrar'])) {
            abort(403, 'Unauthorized access');
        }

        // Validate the request
        $validated = $request->validate([
            // Personal Information
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'preferred_name' => 'nullable|string|max:100',
            'email' => 'required|email|unique:students,email',
            'phone_number' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other,Male,Female,Other',
            'nationality' => 'required|string|max:100',
            
            // Academic Information
            'program_id' => 'nullable|exists:academic_programs,id',
            'program_name' => 'required|string|max:200',
            'academic_level' => 'required|in:undergraduate,graduate,doctoral,Freshman,Sophomore,Junior,Senior,Graduate,Postgraduate',
            'academic_year' => 'required|string|max:20',
            'enrollment_status' => 'required|in:active,inactive,suspended,graduated,withdrawn,expelled,deferred,probation',
            'enrollment_date' => 'required|date',
            'expected_graduation_date' => 'nullable|date|after:enrollment_date',
            
            // Address
            'street_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state_province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            
            // Emergency Contact
            'emergency_contact_name' => 'required|string|max:200',
            'emergency_contact_relationship' => 'required|string|max:100',
            'emergency_contact_phone' => 'required|string|max:20',
            
            // Documents (optional)
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'id_document' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:5120',
        ]);

        DB::beginTransaction();
        try {
            // Generate unique student ID
            $studentId = $this->generateStudentId();
            
            // Create user account if email doesn't exist
            $user = User::where('email', $validated['email'])->first();
            if (!$user) {
                $user = User::create([
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'email' => $validated['email'],
                    'password' => bcrypt('IntelliCampus' . date('Y')), // Default password
                    'user_type' => 'student',
                ]);
                
                // Assign student role
                $user->assignRole('student');
            }
            
            // Handle file uploads
            $documentPaths = [];
            if ($request->hasFile('photo')) {
                $documentPaths['photo_url'] = $request->file('photo')
                    ->store('students/photos', 'public');
            }
            if ($request->hasFile('id_document')) {
                $documentPaths['id_document_url'] = $request->file('id_document')
                    ->store('students/documents', 'public');
            }
            
            // Create student record
            $studentData = array_merge($validated, [
                'student_id' => $studentId,
                'user_id' => $user->id,
                // Map address fields if needed
                'permanent_address' => $validated['street_address'],
                'permanent_city' => $validated['city'],
                'permanent_state' => $validated['state_province'],
                'permanent_postal_code' => $validated['postal_code'],
                'permanent_country' => $validated['country'],
            ], $documentPaths);
            
            // Remove file fields
            unset($studentData['photo'], $studentData['id_document']);
            
            $student = Student::create($studentData);
            
            // Create enrollment history
            DB::table('student_status_changes')->insert([
                'student_id' => $student->id,
                'previous_status' => null,
                'new_status' => $student->enrollment_status,
                'reason' => 'Initial enrollment',
                'changed_by' => auth()->id(),
                'changed_at' => $student->enrollment_date,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            return redirect()->route('students.show', $student)
                ->with('success', "Student created successfully! Student ID: {$studentId}");
                
        } catch (\Exception $e) {
            DB::rollback();
            
            // Clean up uploaded files
            foreach ($documentPaths as $path) {
                if ($path) {
                    Storage::disk('public')->delete($path);
                }
            }
            
            return back()->withInput()
                ->with('error', 'Error creating student: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified student
     */
    public function show(Student $student)
    {
        // Check if user can view this student
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'super-administrator', 'registrar', 'advisor']) &&
            $student->user_id !== $user->id) {
            abort(403, 'Unauthorized access');
        }

        // Load relationships safely
        try {
            $student->load(['program', 'registrations.section.course', 'grades']);
        } catch (\Exception $e) {
            // If relationships don't exist, continue without them
        }
        
        // Get additional data
        $enrollmentHistory = DB::table('student_status_changes')
            ->where('student_id', $student->id)
            ->orderBy('changed_at', 'desc')
            ->get();
            
        $academicRecord = $this->getAcademicRecord($student);
        $financialSummary = $this->getFinancialSummary($student);
        
        return view('students.show', compact('student', 'enrollmentHistory', 'academicRecord', 'financialSummary'));
    }

    /**
     * Show the form for editing the student
     */
    public function edit(Student $student)
    {
        // Check permissions
        if (!Auth::user()->hasAnyRole(['admin', 'super-administrator', 'registrar'])) {
            abort(403, 'Unauthorized access');
        }

        $programs = AcademicProgram::where('is_active', true)->get();
        
        return view('students.edit', compact('student', 'programs'));
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, Student $student)
    {
        // Check permissions
        if (!Auth::user()->hasAnyRole(['admin', 'super-administrator', 'registrar'])) {
            abort(403, 'Unauthorized access');
        }

        // Validate the request
        $validated = $request->validate([
            // Personal Information
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'preferred_name' => 'nullable|string|max:100',
            'email' => 'required|email|unique:students,email,' . $student->id,
            'phone_number' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other,Male,Female,Other',
            'nationality' => 'required|string|max:100',
            
            // Academic Information
            'program_id' => 'nullable|exists:academic_programs,id',
            'academic_level' => 'required|string',
            'academic_year' => 'required|string|max:20',
            'enrollment_status' => 'required|in:active,inactive,suspended,graduated,withdrawn,expelled,deferred,probation',
            'expected_graduation_date' => 'nullable|date',
            
            // Address
            'street_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state_province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Track status changes
            if ($student->enrollment_status !== $validated['enrollment_status']) {
                DB::table('student_status_changes')->insert([
                    'student_id' => $student->id,
                    'previous_status' => $student->enrollment_status,
                    'new_status' => $validated['enrollment_status'],
                    'reason' => $request->input('status_change_reason', 'Administrative update'),
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Update student
            $student->update($validated);
            
            // Update user if email changed
            if ($student->user && $student->user->email !== $validated['email']) {
                $student->user->update([
                    'email' => $validated['email'],
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('students.show', $student)
                ->with('success', 'Student updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                ->with('error', 'Error updating student: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified student (soft delete)
     */
    public function destroy(Student $student)
    {
        // Check permissions - only super admin can delete
        if (!Auth::user()->hasRole('super-administrator')) {
            abort(403, 'Only super administrators can delete student records');
        }

        try {
            // Soft delete the student
            $student->delete();
            
            return redirect()->route('students.index')
                ->with('success', 'Student record archived successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Error archiving student: ' . $e->getMessage());
        }
    }

    // ===========================================================
    // STUDENT PROFILE METHODS
    // ===========================================================

    /**
     * Show student profile
     */
    public function profile()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        
        return view('student.profile.index', compact('student'));
    }

    /**
     * Edit student profile
     */
    public function editProfile()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        
        return view('student.profile.edit', compact('student'));
    }

    /**
     * Update student profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        
        $validated = $request->validate([
            'phone_number' => 'nullable|string|max:20',
            'preferred_name' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:200',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
        ]);
        
        $student->update($validated);
        
        return redirect()->route('student.profile.index')
            ->with('success', 'Profile updated successfully');
    }

    // ===========================================================
    // ENROLLMENT MANAGEMENT METHODS (From Second Controller)
    // ===========================================================

    /**
     * Display enrollment management page
     */
    public function enrollmentManage(Student $student)
    {
        // Get enrollment history for this student
        $enrollmentHistory = DB::table('student_status_changes')
            ->leftJoin('users', 'student_status_changes.changed_by', '=', 'users.id')
            ->where('student_status_changes.student_id', $student->id)
            ->select(
                'student_status_changes.*',
                'users.name as changed_by_name'
            )
            ->orderBy('student_status_changes.changed_at', 'desc')
            ->limit(10)
            ->get();
        
        // Get available enrollment statuses for the form
        $enrollmentStatuses = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'suspended' => 'Suspended',
            'graduated' => 'Graduated',
            'withdrawn' => 'Withdrawn',
            'expelled' => 'Expelled',
            'deferred' => 'Deferred',
            'probation' => 'Probation'
        ];
        
        return view('students.enrollment.manage', compact('student', 'enrollmentHistory', 'enrollmentStatuses'));
    }

    /**
     * Display full enrollment history
     */
    public function enrollmentHistory(Student $student)
    {
        // Get complete enrollment history with pagination
        $enrollmentHistory = DB::table('student_status_changes')
            ->leftJoin('users', 'student_status_changes.changed_by', '=', 'users.id')
            ->where('student_status_changes.student_id', $student->id)
            ->select(
                'student_status_changes.*',
                'users.name as changed_by_name'
            )
            ->orderBy('student_status_changes.changed_at', 'desc')
            ->paginate(20);
        
        return view('students.enrollment.history', compact('student', 'enrollmentHistory'));
    }

    // ===========================================================
    // HELPER METHODS
    // ===========================================================

    /**
     * Generate unique student ID
     */
    private function generateStudentId()
    {
        $year = date('Y');
        $lastStudent = Student::where('student_id', 'LIKE', "STU{$year}%")
            ->orderBy('student_id', 'desc')
            ->first();
        
        if ($lastStudent) {
            $lastNumber = intval(substr($lastStudent->student_id, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "STU{$year}{$newNumber}";
    }

    /**
     * Create basic student record for existing user
     */
    private function createBasicStudentRecord($user)
    {
        $names = explode(' ', $user->name);
        $firstName = $names[0] ?? 'Unknown';
        $lastName = $names[count($names) - 1] ?? 'Unknown';
        
        return Student::create([
            'student_id' => $this->generateStudentId(),
            'user_id' => $user->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user->email,
            'enrollment_status' => 'active',
            'enrollment_date' => now(),
            'academic_level' => 'Freshman',
            'academic_year' => '1',
            'program_name' => 'Undeclared',
            'department' => 'General Studies',
        ]);
    }

    /**
     * Get current academic term
     */
    private function getCurrentTerm()
    {
        return AcademicTerm::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where('is_active', true)
            ->first();
    }

  
    /**
     * Convert letter grade to grade point
     */
    private function getGradePoint($letterGrade)
    {
        $gradePoints = [
            'A+' => 4.00, 'A' => 4.00, 'A-' => 3.67,
            'B+' => 3.33, 'B' => 3.00, 'B-' => 2.67,
            'C+' => 2.33, 'C' => 2.00, 'C-' => 1.67,
            'D+' => 1.33, 'D' => 1.00, 'D-' => 0.67,
            'F' => 0.00,
        ];
        
        return $gradePoints[$letterGrade] ?? 0.00;
    }

    /**
     * Get upcoming deadlines
     */
    private function getUpcomingDeadlines($student)
    {
        if (!$student) return collect();
        
        // This would integrate with LMS for assignments, exams, etc.
        return collect();
    }

    /**
     * Get announcements for student
     */
    private function getAnnouncements($student)
    {
        $query = Announcement::where('is_active', true)
            ->where('publish_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now());
            });
        
        // Filter by audience
        if ($student) {
            $query->where(function($q) use ($student) {
                $q->where('target_audience', 'all')
                  ->orWhere('target_audience', 'students')
                  ->orWhere(function($q2) use ($student) {
                      $q2->where('target_audience', 'program')
                         ->where('target_id', $student->program_id);
                  });
            });
        }
        
        return $query->orderBy('priority', 'desc')
            ->orderBy('publish_date', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get account balance
     */
    private function getAccountBalance($student)
    {
        if (!$student) return 0;
        
        $summary = $this->getFinancialSummary($student);
        return $summary['balance'];
    }

    /**
     * Get academic progress
     */
    private function getAcademicProgress($student)
    {
        if (!$student || !$student->program) {
            return $this->getDefaultAcademicProgress();
        }
        
        $required = $student->program->total_credits ?? 120;
        $earned = $student->credits_completed ?? 0;
        
        return [
            'credits_required' => $required,
            'credits_earned' => $earned,
            'percentage' => $required > 0 ? round(($earned / $required) * 100, 1) : 0,
        ];
    }


    /**
     * Get notification count
     */
    private function getNotificationCount($user)
    {
        return DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->whereNull('read_at')
            ->count();
    }

    // ===========================================================
    // DEFAULT DATA METHODS
    // ===========================================================

    private function getDefaultGPAData()
    {
        return [
            'current_gpa' => 0.00,
            'cumulative_gpa' => 0.00,
            'credits_earned' => 0,
            'credits_attempted' => 0,
        ];
    }

    private function getDefaultAttendanceSummary()
    {
        return [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'excused' => 0,
            'percentage' => 100,
        ];
    }

    private function getDefaultFinancialSummary()
    {
        return [
            'balance' => 0,
            'total_charges' => 0,
            'total_payments' => 0,
            'pending_aid' => 0,
        ];
    }

    private function getDefaultAcademicProgress()
    {
        return [
            'credits_required' => 120,
            'credits_earned' => 0,
            'percentage' => 0,
        ];
    }


    /**
     * Get recent grades
     */
    private function getRecentGrades($student)
    {
        if (!$student) return collect();
        
        // Fixed: Join through enrollments table since grades don't have student_id
        return Grade::whereHas('enrollment', function($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->whereNotNull('letter_grade') // Changed from final_grade to letter_grade
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->with(['enrollment.section.course'])
            ->get();
    }

    /**
     * Calculate GPA
     */
    private function calculateGPA($student)
    {
        if (!$student) {
            return $this->getDefaultGPAData();
        }
        
        // Fixed: Get grades through enrollments
        $grades = Grade::whereHas('enrollment', function($query) use ($student) {
                $query->where('student_id', $student->id);
            })
            ->whereNotNull('letter_grade')
            ->where('is_final', true) // Only count final grades
            ->with(['enrollment.section.course'])
            ->get();
        
        $totalPoints = 0;
        $totalCredits = 0;
        $earnedCredits = 0;
        
        foreach ($grades as $grade) {
            $gradePoint = $this->getGradePoint($grade->letter_grade); // Changed from final_grade
            $credits = $grade->enrollment->section->course->credits ?? 0;
            
            $totalPoints += ($gradePoint * $credits);
            $totalCredits += $credits;
            
            if ($gradePoint > 0) {
                $earnedCredits += $credits;
            }
        }
        
        return [
            'current_gpa' => $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.00,
            'cumulative_gpa' => $student->cumulative_gpa ?? 0.00,
            'credits_earned' => $earnedCredits,
            'credits_attempted' => $totalCredits,
        ];
    }

    /**
     * Get attendance summary - Fixed for your schema
     */
    private function getAttendanceSummary($student, $term)
    {
        if (!$student || !$term) {
            return $this->getDefaultAttendanceSummary();
        }
        
        // Fixed: Use attendance_records table if it exists
        try {
            $attendance = DB::table('attendance_records')
                ->where('student_id', $student->id)
                ->join('attendance_sessions', 'attendance_records.session_id', '=', 'attendance_sessions.id')
                ->where('attendance_sessions.term_id', $term->id)
                ->selectRaw('attendance_records.status, COUNT(*) as count')
                ->groupBy('attendance_records.status')
                ->pluck('count', 'status')
                ->toArray();
            
            $total = array_sum($attendance);
            $present = ($attendance['present'] ?? 0) + ($attendance['late'] ?? 0);
            
            return [
                'present' => $attendance['present'] ?? 0,
                'absent' => $attendance['absent'] ?? 0,
                'late' => $attendance['late'] ?? 0,
                'excused' => $attendance['excused'] ?? 0,
                'percentage' => $total > 0 ? round(($present / $total) * 100, 1) : 100,
            ];
        } catch (\Exception $e) {
            // If attendance tables don't exist, return defaults
            return $this->getDefaultAttendanceSummary();
        }
    }

    /**
     * Get financial summary - Fixed for your schema
     */
    private function getFinancialSummary($student)
    {
        if (!$student) {
            return $this->getDefaultFinancialSummary();
        }
        
        try {
            // Check if financial_transactions table exists
            $charges = DB::table('financial_transactions')
                ->where('student_id', $student->id)
                ->where('type', 'charge')
                ->sum('amount');
                
            $payments = DB::table('financial_transactions')
                ->where('student_id', $student->id)
                ->where('type', 'payment')
                ->sum('amount');
                
            $pendingAid = DB::table('financial_transactions')
                ->where('student_id', $student->id)
                ->where('type', 'financial_aid')
                ->where('status', 'pending')
                ->sum('amount');
            
            return [
                'balance' => $charges - $payments,
                'total_charges' => $charges,
                'total_payments' => $payments,
                'pending_aid' => $pendingAid,
            ];
        } catch (\Exception $e) {
            // If financial tables don't exist, try alternative tables
            try {
                $charges = DB::table('billing_items')
                    ->where('student_id', $student->id)
                    ->sum('amount');
                    
                $payments = DB::table('payments')
                    ->where('student_id', $student->id)
                    ->where('status', 'completed')
                    ->sum('amount');
                
                return [
                    'balance' => $charges - $payments,
                    'total_charges' => $charges,
                    'total_payments' => $payments,
                    'pending_aid' => 0,
                ];
            } catch (\Exception $e2) {
                return $this->getDefaultFinancialSummary();
            }
        }
    }

    /**
     * Get academic record - Fixed for your schema
     */
    private function getAcademicRecord($student)
    {
        try {
            // Use enrollments table which exists in your schema
            $enrollments = DB::table('enrollments')
                ->where('student_id', $student->id)
                ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
                ->join('courses', 'course_sections.course_id', '=', 'courses.id')
                ->leftJoin('academic_terms', 'enrollments.term_id', '=', 'academic_terms.id')
                ->leftJoin('grades', 'grades.enrollment_id', '=', 'enrollments.id')
                ->select(
                    'enrollments.*',
                    'courses.code as course_code',
                    'courses.title as course_title',
                    'courses.credits',
                    'course_sections.section_number',
                    'academic_terms.name as term_name',
                    'grades.letter_grade',
                    'grades.points_earned'
                )
                ->orderBy('enrollments.created_at', 'desc')
                ->get()
                ->groupBy('term_name');
                
            return $enrollments;
        } catch (\Exception $e) {
            // If there's any error, return empty collection
            return collect();
        }
    }

    /**
     * Calculate attendance rate - Fixed
     */
    private function calculateAttendanceRate($student)
    {
        $summary = $this->getAttendanceSummary($student, $this->getCurrentTerm());
        return $summary['percentage'] ?? 100;
    }

    /**
     * Get today's schedule - Fixed for your schema
     */
    private function getTodaySchedule($student)
    {
        if (!$student) return collect();
        
        $dayOfWeek = strtolower(now()->format('l'));
        $currentTerm = $this->getCurrentTerm();
        
        if (!$currentTerm) return collect();
        
        try {
            return DB::table('enrollments as e')
                ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
                ->leftJoin('section_schedules as ss', 'cs.id', '=', 'ss.section_id')
                ->join('courses as c', 'cs.course_id', '=', 'c.id')
                ->where('e.student_id', $student->id)
                ->where('e.term_id', $currentTerm->id)
                ->where('e.enrollment_status', 'enrolled')
                ->whereRaw("LOWER(ss.day_of_week) = ?", [$dayOfWeek])
                ->select(
                    'c.code',
                    'c.title',
                    'cs.section_number',
                    'ss.start_time',
                    'ss.end_time',
                    'ss.room',
                    'ss.building'
                )
                ->orderBy('ss.start_time')
                ->get();
        } catch (\Exception $e) {
            // If section_schedules doesn't exist, return empty
            return collect();
        }
    }

    /**
     * Get current enrollments - Fixed for your schema
     */
    private function getCurrentEnrollments($student, $term)
    {
        if (!$student || !$term) return collect();
        
        try {
            return DB::table('enrollments as e')
                ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
                ->join('courses as c', 'cs.course_id', '=', 'c.id')
                ->leftJoin('users as instructor', 'cs.instructor_id', '=', 'instructor.id')
                ->where('e.student_id', $student->id)
                ->where('e.term_id', $term->id)
                ->where('e.enrollment_status', 'enrolled')
                ->select(
                    'e.*',
                    'c.code as course_code',
                    'c.title as course_title',
                    'c.credits',
                    'cs.section_number',
                    'instructor.name as instructor_name'
                )
                ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Registrar view of students
     */
    public function registrarIndex(Request $request)
    {
        $query = Student::with(['user', 'program']);
        
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
     * Advanced search for registrar
     */
    public function advancedSearch(Request $request)
    {
        return view('registrar.students.search');
    }
    
    /**
     * View academic record
     */
    public function academicRecord(Student $student)
    {
        $student->load(['enrollments.section.course', 'grades', 'program']);
        
        return view('registrar.students.record', compact('student'));
    }
    
    /**
     * Pending name changes
     */
    public function pendingNameChanges()
    {
        $changes = DB::table('name_change_requests')
            ->where('status', 'pending')
            ->paginate(20);
            
        return view('registrar.students.name-changes', compact('changes'));
    }
}