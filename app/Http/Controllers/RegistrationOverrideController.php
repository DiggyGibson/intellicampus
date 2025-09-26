<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RegistrationOverrideRequest;
use App\Models\Student;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\AcademicTerm;
use App\Models\Registration;
use App\Models\RegistrationCart;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\RegistrationOverrideService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class RegistrationOverrideController extends Controller
{
    protected $overrideService;
    
    public function __construct(RegistrationOverrideService $overrideService = null)
    {
        // Handle case where service might not exist yet
        $this->overrideService = $overrideService ?: new \stdClass();
    }
    
    /**
     * Display student's override requests
     */
    public function myRequests(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        // Handle case where student record doesn't exist
        if (!$student) {
            return redirect()->route('dashboard')
                ->with('error', 'Student record not found. Please contact the registrar.');
        }
        
        // Get current term
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        
        // Get all requests with safe relationship loading
        $requests = RegistrationOverrideRequest::where('student_id', $student->id)
            ->with([
                'course' => function($q) { $q->withDefault(); },
                'section' => function($q) { $q->with('course')->withDefault(); },
                'term' => function($q) { $q->withDefault(); },
                'approver' => function($q) { $q->withDefault(); }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Get active override codes - safer query
        $activeOverrides = RegistrationOverrideRequest::where('student_id', $student->id)
            ->where('status', 'approved')
            ->where('override_used', false)
            ->where(function($query) {
                $query->whereNull('override_expires_at')
                      ->orWhere('override_expires_at', '>', now());
            })
            ->get();
        
        // Add type labels and badge classes for display
        $requests->getCollection()->transform(function ($request) {
            $request->type_label = $this->getTypeLabel($request->request_type);
            $request->type_badge_class = $this->getTypeBadgeClass($request->request_type);
            return $request;
        });
        
        $activeOverrides->transform(function ($override) {
            $override->type_label = $this->getTypeLabel($override->request_type);
            $override->override_expires_at = $override->override_expires_at ? Carbon::parse($override->override_expires_at) : null;
            return $override;
        });
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'requests' => $requests,
                'activeOverrides' => $activeOverrides
            ]);
        }
        
        return view('student.override-requests', compact('requests', 'activeOverrides', 'currentTerm'));
    }
    
    /**
     * Show the form for creating a new override request
     */
    public function showCreateForm()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return redirect()->route('dashboard')
                ->with('error', 'Student record not found.');
        }
        
        // Get current term
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        
        if (!$currentTerm) {
            return redirect()->route('student.override.requests')
                ->with('error', 'No active academic term found.');
        }
        
        // Calculate current credits from multiple sources
        $currentCredits = $this->calculateCurrentCredits($student->id, $currentTerm->id);
        
        // Get available courses for prerequisite override
        $courses = Course::where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'title']);
        
        // Get full sections for capacity override
        $sections = CourseSection::with('course')
            ->where('term_id', $currentTerm->id)
            ->whereRaw('COALESCE(current_enrollment, 0) >= COALESCE(enrollment_capacity, 30)')
            ->orderBy('section_number')
            ->get();
        
        // Get student's recent requests
        $myRequests = RegistrationOverrideRequest::where('student_id', $student->id)
            ->with(['course', 'section.course'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('student.override-request-form', compact(
            'student',
            'currentCredits',
            'courses',
            'sections',
            'myRequests',
            'currentTerm'
        ));
    }
    
    /**
     * Show details of a specific override request
     */
    public function show($id)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }
        
        $request = RegistrationOverrideRequest::where('id', $id)
            ->where('student_id', $student->id)
            ->with(['course', 'section.course', 'term', 'approver'])
            ->first();
        
        if (!$request) {
            return response()->json(['error' => 'Request not found'], 404);
        }
        
        $request->type_label = $this->getTypeLabel($request->request_type);
        
        return response()->json([
            'success' => true,
            'request' => $request
        ]);
    }
    
    /**
     * Create a new override request
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'type' => [
                'required',
                Rule::in([
                    'credit_overload',
                    'prerequisite',
                    'capacity',
                    'time_conflict',
                    'late_registration'
                ])
            ],
            'justification' => 'required|string|min:50|max:2000',
            'supporting_documents' => 'nullable|array',
            
            // For credit overload
            'requested_credits' => 'required_if:type,credit_overload|integer|min:19|max:24',
            
            // For prerequisite override
            'course_id' => 'required_if:type,prerequisite|exists:courses,id',
            'reason' => 'required_if:type,prerequisite|string',
            'evidence' => 'nullable|string',
            
            // For capacity override
            'section_id' => 'required_if:type,capacity|exists:course_sections,id',
            'is_required' => 'nullable|boolean',
        ]);
        
        // Get authenticated student
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return redirect()->back()
                ->with('error', 'Student record not found')
                ->withInput();
        }
        
        // Get current term
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        
        if (!$currentTerm) {
            return redirect()->back()
                ->with('error', 'No active academic term')
                ->withInput();
        }
        
        try {
            // If we have the service, use it; otherwise create manually
            if (method_exists($this->overrideService, 'createRequest')) {
                $overrideRequest = $this->createOverrideWithService($student, $validated, $currentTerm);
            } else {
                $overrideRequest = $this->createOverrideManually($student, $validated, $currentTerm);
            }
            
            // Check if it was auto-approved
            if ($overrideRequest->status === 'approved') {
                return redirect()->route('student.override.requests')
                    ->with('success', 'Your override request has been auto-approved! Override code: ' . $overrideRequest->override_code);
            }
            
            return redirect()->route('student.override.requests')
                ->with('success', 'Override request submitted successfully. You will be notified once a decision is made.');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating override request: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Use an override code during registration
     */
    public function useOverride(Request $request)
    {
        $validated = $request->validate([
            'override_code' => 'required|string|size:8'
        ]);
        
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student record not found'
            ], 404);
        }
        
        // Find the override request
        $overrideRequest = RegistrationOverrideRequest::where('override_code', $validated['override_code'])
            ->where('student_id', $student->id)
            ->where('status', 'approved')
            ->where('override_used', false)
            ->where(function($query) {
                $query->whereNull('override_expires_at')
                      ->orWhere('override_expires_at', '>', now());
            })
            ->first();
        
        if (!$overrideRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired override code'
            ], 400);
        }
        
        // Mark as used
        $overrideRequest->override_used = true;
        $overrideRequest->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Override code applied successfully',
            'type' => $overrideRequest->request_type,
            'details' => [
                'course_id' => $overrideRequest->course_id,
                'section_id' => $overrideRequest->section_id,
                'requested_credits' => $overrideRequest->requested_credits
            ]
        ]);
    }
    
    /**
     * Check what overrides a student has available
     */
    public function checkOverrides(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student record not found'
            ], 404);
        }
        
        // Get current term
        $currentTerm = DB::table('academic_terms')
            ->where('is_current', true)
            ->first();
            
        if (!$currentTerm) {
            return response()->json([
                'success' => false,
                'message' => 'No active academic term'
            ], 400);
        }
        
        // Check for active credit overload permission
        $creditOverload = DB::table('credit_overload_permissions')
            ->where('student_id', $student->id)
            ->where('term_id', $currentTerm->id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('valid_until')
                      ->orWhere('valid_until', '>=', now());
            })
            ->first();
            
        // Check for prerequisite waivers
        $prerequisiteWaivers = DB::table('prerequisite_waivers as pw')
            ->join('courses as c', 'pw.course_id', '=', 'c.id')
            ->where('pw.student_id', $student->id)
            ->where('pw.term_id', $currentTerm->id)
            ->where('pw.is_active', true)
            ->where(function ($query) {
                $query->whereNull('pw.expires_at')
                      ->orWhere('pw.expires_at', '>=', now());
            })
            ->select('pw.*', 'c.code as course_code', 'c.title as course_title')
            ->get();
            
        // Check for special registration flags
        $specialFlags = DB::table('special_registration_flags')
            ->where('student_id', $student->id)
            ->where('term_id', $currentTerm->id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('valid_from')
                      ->orWhere('valid_from', '<=', now());
                })->where(function ($q) {
                    $q->whereNull('valid_until')
                      ->orWhere('valid_until', '>=', now());
                });
            })
            ->get();
            
        // Check for unused override codes
        $overrideCodes = RegistrationOverrideRequest::where('student_id', $student->id)
            ->where('status', 'approved')
            ->where('override_used', false)
            ->where(function($query) {
                $query->whereNull('override_expires_at')
                      ->orWhere('override_expires_at', '>', now());
            })
            ->select('id', 'request_type', 'override_code', 'override_expires_at')
            ->get();
            
        return response()->json([
            'success' => true,
            'overrides' => [
                'credit_overload' => $creditOverload,
                'prerequisite_waivers' => $prerequisiteWaivers,
                'special_flags' => $specialFlags,
                'unused_codes' => $overrideCodes
            ]
        ]);
    }
    
    /**
     * Calculate current credits from all sources
     */
    private function calculateCurrentCredits($studentId, $termId)
    {
        // Credits from enrollments
        $enrollmentCredits = DB::table('enrollments as e')
            ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->where('e.student_id', $studentId)
            ->where('e.term_id', $termId)
            ->whereIn('e.status', ['enrolled', 'active'])
            ->sum('c.credits');
        
        // Credits from pending registrations
        $registrationCredits = DB::table('registrations as r')
            ->join('course_sections as cs', 'r.section_id', '=', 'cs.id')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->where('r.student_id', $studentId)
            ->where('r.term_id', $termId)
            ->whereIn('r.status', ['pending', 'enrolled', 'waitlisted'])
            ->whereNull('r.deleted_at')
            ->sum('c.credits');
        
        // Credits from registration cart
        $cartCredits = DB::table('registration_carts as rc')
            ->where('rc.student_id', $studentId)
            ->where('rc.term_id', $termId)
            ->whereIn('rc.status', ['active', 'validated'])
            ->value('total_credits');
        
        // Return the maximum to avoid double counting
        return max(
            ($enrollmentCredits ?: 0),
            ($registrationCredits ?: 0),
            ($cartCredits ?: 0)
        );
    }
    
    /**
     * Create override request manually (without service)
     */
    private function createOverrideManually($student, $validated, $currentTerm)
    {
        $data = [
            'student_id' => $student->id,
            'term_id' => $currentTerm->id,
            'request_type' => $validated['type'],
            'status' => 'pending',
            'student_justification' => $validated['justification'],
            'supporting_documents' => $validated['supporting_documents'] ?? null,
            'priority_level' => 'normal',
            'is_graduating_senior' => false,
        ];
        
        // Add type-specific data
        switch ($validated['type']) {
            case 'credit_overload':
                $data['requested_credits'] = $validated['requested_credits'];
                $data['current_credits'] = $this->calculateCurrentCredits($student->id, $currentTerm->id);
                break;
                
            case 'prerequisite':
                $data['course_id'] = $validated['course_id'];
                break;
                
            case 'capacity':
                $data['section_id'] = $validated['section_id'];
                break;
        }
        
        // Check for auto-approval conditions
        if ($this->checkAutoApproval($student, $validated['type'])) {
            $data['status'] = 'approved';
            $data['override_code'] = strtoupper(substr(md5(uniqid()), 0, 8));
            $data['override_expires_at'] = Carbon::now()->addDays(7);
            $data['approval_date'] = now();
            $data['approver_notes'] = 'Auto-approved based on academic standing';
        }
        
        return RegistrationOverrideRequest::create($data);
    }
    
    /**
     * Create override request with service
     */
    private function createOverrideWithService($student, $validated, $currentTerm)
    {
        switch ($validated['type']) {
            case 'credit_overload':
                return $this->overrideService->requestCreditOverload(
                    $student,
                    $validated['requested_credits'],
                    $validated['justification'],
                    $validated['supporting_documents'] ?? []
                );
                
            case 'prerequisite':
                $course = Course::findOrFail($validated['course_id']);
                $evidence = !empty($validated['evidence']) ? [$validated['evidence']] : [];
                
                return $this->overrideService->requestPrerequisiteOverride(
                    $student,
                    $course,
                    $validated['reason'],
                    $validated['justification'],
                    $evidence
                );
                
            case 'capacity':
                $section = CourseSection::findOrFail($validated['section_id']);
                return $this->overrideService->requestCapacityOverride(
                    $student,
                    $section,
                    $validated['justification'],
                    $validated['is_required'] ?? false
                );
                
            default:
                throw new \Exception('Invalid override type');
        }
    }
    
    /**
     * Check if request qualifies for auto-approval
     */
    private function checkAutoApproval($student, $type)
    {
        // Auto-approve for high-achieving students
        if ($type === 'credit_overload') {
            // Check GPA - assuming field exists
            $gpa = $student->cumulative_gpa ?? 0;
            if ($gpa >= 3.5) {
                return true;
            }
        }
        
        // Add other auto-approval conditions as needed
        
        return false;
    }
    
    /**
     * Get type label for display
     */
    private function getTypeLabel($type)
    {
        return match($type) {
            'credit_overload' => 'Credit Overload',
            'prerequisite' => 'Prerequisite Override',
            'capacity' => 'Capacity Override',
            'time_conflict' => 'Time Conflict',
            'late_registration' => 'Late Registration',
            default => ucfirst(str_replace('_', ' ', $type))
        };
    }
    
    /**
     * Get badge class for type
     */
    private function getTypeBadgeClass($type)
    {
        return match($type) {
            'credit_overload' => 'primary',
            'prerequisite' => 'warning',
            'capacity' => 'info',
            'time_conflict' => 'danger',
            'late_registration' => 'secondary',
            default => 'light'
        };
    }
}