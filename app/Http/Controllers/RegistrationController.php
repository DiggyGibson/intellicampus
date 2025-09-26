<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PrerequisiteService;
use App\Services\TimeConflictService;
use App\Services\CreditValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class RegistrationController extends Controller
{
    protected $prerequisiteService;
    protected $conflictService;
    protected $creditService;
    
    public function __construct(
        PrerequisiteService $prerequisiteService,
        TimeConflictService $conflictService,
        CreditValidationService $creditService
    ) {
        $this->prerequisiteService = $prerequisiteService;
        $this->conflictService = $conflictService;
        $this->creditService = $creditService;
    }
    
    /**
     * Display course catalog
     */
    public function catalog(Request $request)
    {
        // Get current term
        $currentTerm = DB::table('academic_terms')
            ->where('is_current', true)
            ->first();
            
        if (!$currentTerm) {
            return view('registration.catalog')->with('error', 'No active registration term');
        }
        
        // Build query for course sections
        $query = DB::table('course_sections as cs')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->leftJoin('users as i', 'cs.instructor_id', '=', 'i.id')
            ->where('cs.term_id', $currentTerm->id)
            ->where('cs.status', 'open')  // Only 'open' status
            ->select(
                'cs.*',
                'c.id as course_id',
                'c.code',
                'c.title',
                'c.description',
                'c.credits',
                'c.department',
                'i.name as instructor_name',
                DB::raw('cs.enrollment_capacity - cs.current_enrollment as available_seats')
            );
        
        // Apply filters
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('c.code', 'like', '%' . $search . '%')
                ->orWhere('c.title', 'like', '%' . $search . '%')
                ->orWhere('c.description', 'like', '%' . $search . '%');
            });
        }
        
        if ($request->has('department') && $request->department) {
            $query->where('c.department', $request->department);
        }
        
        if ($request->has('credits') && $request->credits) {
            $query->where('c.credits', $request->credits);
        }
        
        if ($request->has('day') && $request->day) {
            $query->where('cs.days_of_week', 'like', '%' . $request->day . '%');
        }
        
        $sections = $query->paginate(20);
        
        // Transform sections to add course object
        $sections->getCollection()->transform(function ($section) {
            // Create course object from the joined data
            $section->course = (object) [
                'id' => $section->course_id,
                'code' => $section->code,
                'title' => $section->title,
                'description' => $section->description,
                'credits' => $section->credits,
                'department' => $section->department
            ];
            
            return $section;
        });
        
        // Get departments for filter
        $departments = DB::table('courses')
            ->distinct()
            ->pluck('department')
            ->filter()
            ->sort();
        
        // Initialize these variables to avoid undefined errors
        $cartItems = [];
        $enrolledSections = [];
        
        // Get student's cart items and enrolled sections
        if (Auth::check()) {
            $student = DB::table('students')->where('user_id', Auth::id())->first();
            if ($student) {
                // Get cart items
                $cart = DB::table('registration_carts')
                    ->where('student_id', $student->id)
                    ->where('term_id', $currentTerm->id)
                    ->first();
                    
                if ($cart && $cart->section_ids) {
                    $cartItems = json_decode($cart->section_ids, true) ?: [];
                }
                
                // Get enrolled sections - THIS WAS MISSING!
                $enrolledSections = DB::table('enrollments')
                    ->where('student_id', $student->id)
                    ->where('term_id', $currentTerm->id)
                    ->whereIn('enrollment_status', ['enrolled', 'pending'])
                    ->pluck('section_id')
                    ->toArray();
            }
        }

        // Get all terms for filter
        $terms = DB::table('academic_terms')
            ->orderBy('start_date', 'desc')
            ->get();
        
        // Get instructors for filter
        $instructors = DB::table('users')
            ->where('user_type', 'faculty')
            ->orderBy('name')
            ->get();
        
        // Get credit options
        $creditOptions = DB::table('courses')
            ->distinct()
            ->pluck('credits')
            ->sort();
        
        $selectedTerm = $request->get('term_id', $currentTerm->id);
        
        return view('registration.catalog', compact(
            'sections',
            'departments',
            'currentTerm',
            'cartItems',
            'enrolledSections', 
            'terms',  
            'instructors',
            'creditOptions', 
            'selectedTerm' 
        ));
}
    
    /**
     * Add course to shopping cart with validation
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:course_sections,id'
        ]);
        
        // Get student
        $student = DB::table('students')->where('user_id', Auth::id())->first();
        if (!$student) {
            return back()->with('error', 'Student profile not found');
        }
        
        // Get current term
        $currentTerm = DB::table('academic_terms')->where('is_current', true)->first();
        if (!$currentTerm) {
            return back()->with('error', 'No active registration term');
        }
        
        // Get or create cart
        $cart = DB::table('registration_carts')
            ->where('student_id', $student->id)
            ->where('term_id', $currentTerm->id)
            ->first();
            
        $cartSectionIds = $cart && $cart->section_ids ? json_decode($cart->section_ids, true) : [];
        
        // Check if already in cart
        if (in_array($request->section_id, $cartSectionIds)) {
            return back()->with('warning', 'Course already in cart');
        }
        
        // Get the section to add
        $newSection = DB::table('course_sections as cs')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->where('cs.id', $request->section_id)
            ->select('cs.*', 'c.code as course_code', 'c.title as course_title', 'c.credits')
            ->first();
            
        if (!$newSection) {
            return back()->with('error', 'Section not found');
        }
        
        // Check registration holds
        $holds = DB::table('registration_holds')
            ->where('student_id', $student->id)
            ->where('is_active', true)
            ->get();
            
        if ($holds->count() > 0) {
            $holdTypes = $holds->pluck('hold_type')->implode(', ');
            return back()->with('error', "You have active registration holds: {$holdTypes}. Please contact the Registrar's Office.");
        }
        
        // Get all sections in cart plus the new one for validation
        $allSectionIds = array_merge($cartSectionIds, [$request->section_id]);
        $allSections = DB::table('course_sections as cs')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->whereIn('cs.id', $allSectionIds)
            ->select('cs.*', 'c.code as course_code', 'c.title as course_title', 'c.credits')
            ->get();
        
        // 1. Check Prerequisites
        $prerequisiteIssues = $this->prerequisiteService->checkPrerequisites($student->id, collect([$newSection]));
        if (!empty($prerequisiteIssues)) {
            $messages = array_column($prerequisiteIssues, 'message');
            return back()->with('error', 'Prerequisites not met: ' . implode('; ', $messages));
        }
        
        // 2. Check Time Conflicts
        $conflicts = $this->conflictService->checkTimeConflicts($student->id, collect([$newSection]), $currentTerm->id);
        if (!empty($conflicts)) {
            $conflictMessage = $conflicts[0]['message'] ?? 'Time conflict detected';
            return back()->with('error', $conflictMessage);
        }
        
        // 3. Check Credit Limits
        $creditValidation = $this->creditService->validateCreditLimits($student->id, $allSections, $currentTerm->id);
        if (!$creditValidation['valid']) {
            $issue = $creditValidation['issues'][0] ?? ['message' => 'Credit limit exceeded'];
            return back()->with('error', $issue['message']);
        }
        
        // 4. Check capacity
        if ($newSection->current_enrollment >= $newSection->enrollment_capacity) {
            // Add to waitlist instead
            return $this->addToWaitlist($student->id, $request->section_id, $currentTerm->id);
        }
        
        // Add to cart
        $cartSectionIds[] = $request->section_id;
        
        if ($cart) {
            DB::table('registration_carts')
                ->where('id', $cart->id)
                ->update([
                    'section_ids' => json_encode($cartSectionIds),
                    'total_credits' => $creditValidation['total_credits'],
                    'validated_at' => now(),
                    'updated_at' => now()
                ]);
        } else {
            DB::table('registration_carts')->insert([
                'student_id' => $student->id,
                'term_id' => $currentTerm->id,
                'section_ids' => json_encode($cartSectionIds),
                'total_credits' => $creditValidation['total_credits'],
                'validated_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        // Show any warnings
        $successMessage = "Course added to cart successfully!";
        if (!empty($creditValidation['warnings'])) {
            $warning = $creditValidation['warnings'][0];
            $successMessage .= " Note: " . $warning['message'];
        }
        
        return back()->with('success', $successMessage);
    }
    
    /**
     * View shopping cart with comprehensive validation
     */
    public function viewCart()
    {
        $student = DB::table('students')->where('user_id', Auth::id())->first();
        if (!$student) {
            return redirect()->route('registration.catalog')->with('error', 'Student profile not found');
        }
        
        $currentTerm = DB::table('academic_terms')->where('is_current', true)->first();
        if (!$currentTerm) {
            return redirect()->route('registration.catalog')->with('error', 'No active registration term');
        }
        
        // Get cart
        $cart = DB::table('registration_carts')
            ->where('student_id', $student->id)
            ->where('term_id', $currentTerm->id)
            ->first();
            
        if (!$cart || !$cart->section_ids || $cart->section_ids === '[]') {
            return view('registration.cart', [
                'sections' => collect(),
                'totalCredits' => 0,
                'conflicts' => [],
                'prerequisiteIssues' => [],
                'creditValidation' => ['valid' => true, 'warnings' => [], 'issues' => []],
                'holds' => [],
                'currentTerm' => $currentTerm,
                'student' => $student
            ]);
        }
        
        $sectionIds = json_decode($cart->section_ids, true);
        
        // Get section details
        $sections = DB::table('course_sections as cs')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->leftJoin('users as i', 'cs.instructor_id', '=', 'i.id')
            ->whereIn('cs.id', $sectionIds)
            ->select(
                'cs.*',
                'c.code',
                'c.title',
                'c.credits',
                'c.description',
                'i.name as instructor_name',
                DB::raw('cs.enrollment_capacity - cs.current_enrollment as available_seats')
            )
            ->get();
        
        // Run all validations
        $prerequisiteIssues = $this->prerequisiteService->checkPrerequisites($student->id, $sections);
        $conflicts = $this->conflictService->checkTimeConflicts($student->id, collect(), $currentTerm->id);
        $creditValidation = $this->creditService->validateCreditLimits($student->id, $sections, $currentTerm->id);
        
        // Check for registration holds
        $holds = DB::table('registration_holds')
            ->where('student_id', $student->id)
            ->where('is_active', true)
            ->get();
        
        // Check registration period
        $registrationPeriod = $this->checkRegistrationPeriod($student);
        
        return view('registration.cart', [
            'sections' => $sections,
            'totalCredits' => $creditValidation['total_credits'] ?? 0,
            'conflicts' => $conflicts,
            'prerequisiteIssues' => $prerequisiteIssues,
            'creditValidation' => $creditValidation,
            'holds' => $holds,
            'currentTerm' => $currentTerm,
            'student' => $student,
            'registrationPeriod' => $registrationPeriod,
            'canRegister' => empty($prerequisiteIssues) && empty($conflicts) && 
                           $creditValidation['valid'] && $holds->count() === 0 &&
                           $registrationPeriod['is_open']
        ]);
    }
    
    /**
     * Remove course from cart
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'section_id' => 'required'
        ]);
        
        $student = DB::table('students')->where('user_id', Auth::id())->first();
        if (!$student) {
            return back()->with('error', 'Student profile not found');
        }
        
        $currentTerm = DB::table('academic_terms')->where('is_current', true)->first();
        
        $cart = DB::table('registration_carts')
            ->where('student_id', $student->id)
            ->where('term_id', $currentTerm->id)
            ->first();
            
        if ($cart && $cart->section_ids) {
            $sectionIds = json_decode($cart->section_ids, true);
            $sectionIds = array_diff($sectionIds, [$request->section_id]);
            
            // Recalculate total credits
            $sections = DB::table('course_sections as cs')
                ->join('courses as c', 'cs.course_id', '=', 'c.id')
                ->whereIn('cs.id', $sectionIds)
                ->select('c.credits')
                ->get();
            
            $totalCredits = $sections->sum('credits');
            
            DB::table('registration_carts')
                ->where('id', $cart->id)
                ->update([
                    'section_ids' => json_encode(array_values($sectionIds)),
                    'total_credits' => $totalCredits,
                    'updated_at' => now()
                ]);
                
            return back()->with('success', 'Course removed from cart');
        }
        
        return back()->with('error', 'Cart not found');
    }
    
    /**
     * Submit registration
     */
    public function submitRegistration(Request $request)
    {
        $student = DB::table('students')->where('user_id', Auth::id())->first();
        if (!$student) {
            return back()->with('error', 'Student profile not found');
        }
        
        $currentTerm = DB::table('academic_terms')->where('is_current', true)->first();
        
        // Get cart
        $cart = DB::table('registration_carts')
            ->where('student_id', $student->id)
            ->where('term_id', $currentTerm->id)
            ->first();
            
        if (!$cart || !$cart->section_ids || $cart->section_ids === '[]') {
            return back()->with('error', 'Your cart is empty');
        }
        
        $sectionIds = json_decode($cart->section_ids, true);
        
        // Get sections
        $sections = DB::table('course_sections as cs')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->whereIn('cs.id', $sectionIds)
            ->select('cs.*', 'c.code as course_code', 'c.title as course_title', 'c.credits')
            ->get();
        
        // Final validation
        $prerequisiteIssues = $this->prerequisiteService->checkPrerequisites($student->id, $sections);
        if (!empty($prerequisiteIssues)) {
            return back()->with('error', 'Prerequisites not met. Please review your cart.');
        }
        
        $conflicts = $this->conflictService->checkTimeConflicts($student->id, collect(), $currentTerm->id);
        if (!empty($conflicts)) {
            return back()->with('error', 'Time conflicts detected. Please review your cart.');
        }
        
        $creditValidation = $this->creditService->validateCreditLimits($student->id, $sections, $currentTerm->id);
        if (!$creditValidation['valid']) {
            return back()->with('error', 'Credit limit validation failed. Please review your cart.');
        }
        
        // Check holds
        $holds = DB::table('registration_holds')
            ->where('student_id', $student->id)
            ->where('is_active', true)
            ->count();
            
        if ($holds > 0) {
            return back()->with('error', 'You have registration holds. Please contact the Registrar.');
        }
        
        // Check registration period
        $registrationPeriod = $this->checkRegistrationPeriod($student);
        if (!$registrationPeriod['is_open']) {
            return back()->with('error', $registrationPeriod['message']);
        }
        
        DB::beginTransaction();
        try {
            $registered = [];
            $waitlisted = [];
            
            foreach ($sections as $section) {
                // Check capacity again
                $currentSection = DB::table('course_sections')
                    ->where('id', $section->id)
                    ->lockForUpdate()
                    ->first();
                    
                if ($currentSection->current_enrollment < $currentSection->enrollment_capacity) {
                    // Register for course
                    DB::table('enrollments')->insert([
                        'student_id' => $student->id,
                        'section_id' => $section->id,
                        'term_id' => $currentTerm->id,
                        'enrollment_status' => 'enrolled',
                        'enrollment_date' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    // Update enrollment count
                    DB::table('course_sections')
                        ->where('id', $section->id)
                        ->increment('current_enrollment');
                        
                    $registered[] = $section;
                    
                    // Log registration
                    DB::table('registration_logs')->insert([
                        'student_id' => $student->id,
                        'section_id' => $section->id,
                        'term_id' => $currentTerm->id,
                        'action' => 'enrolled',
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'created_at' => now()
                    ]);
                } else {
                    // Add to waitlist
                    $position = DB::table('waitlists')
                        ->where('section_id', $section->id)
                        ->max('position') + 1;
                        
                    DB::table('waitlists')->insert([
                        'section_id' => $section->id,
                        'student_id' => $student->id,
                        'position' => $position,
                        'added_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    $waitlisted[] = $section;
                    
                    // Log waitlist action
                    DB::table('registration_logs')->insert([
                        'student_id' => $student->id,
                        'section_id' => $section->id,
                        'term_id' => $currentTerm->id,
                        'action' => 'waitlisted',
                        'details' => json_encode(['position' => $position]),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'created_at' => now()
                    ]);
                }
            }
            
            // Clear cart
            DB::table('registration_carts')
                ->where('id', $cart->id)
                ->update([
                    'section_ids' => json_encode([]),
                    'total_credits' => 0,
                    'updated_at' => now()
                ]);
            
            DB::commit();
            
            $message = 'Registration submitted successfully! ';
            if (count($registered) > 0) {
                $message .= count($registered) . ' course(s) registered. ';
            }
            if (count($waitlisted) > 0) {
                $message .= count($waitlisted) . ' course(s) waitlisted.';
            }
            
            return redirect()->route('registration.schedule')->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Registration failed: ' . $e->getMessage());
            return back()->with('error', 'Registration failed. Please try again.');
        }
    }
    
    /**
     * View student schedule
     */
    public function schedule()
    {
        $student = DB::table('students')->where('user_id', Auth::id())->first();
        if (!$student) {
            return redirect()->route('registration.catalog')->with('error', 'Student profile not found');
        }
        
        $currentTerm = DB::table('academic_terms')->where('is_current', true)->first();
        
        // Get enrolled courses with proper joins
        $enrollments = DB::table('enrollments as e')
            ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->leftJoin('users as i', 'cs.instructor_id', '=', 'i.id')
            ->where('e.student_id', $student->id)
            ->where('e.term_id', $currentTerm->id)
            ->whereIn('e.enrollment_status', ['enrolled', 'pending'])
            ->select(
                'e.*',
                'cs.section_number',
                'cs.crn',
                'cs.days_of_week',
                'cs.start_time',
                'cs.end_time',
                'cs.room',
                'cs.building',
                'cs.delivery_mode',
                'c.code as course_code',
                'c.title',
                'c.credits',
                'c.department',
                'i.name as instructor_name'
            )
            ->get();
        
        // Get schedules from section_schedules table if it exists
        foreach ($enrollments as $enrollment) {
            if (Schema::hasTable('section_schedules')) {
                $schedules = DB::table('section_schedules')
                    ->where('section_id', $enrollment->section_id)
                    ->get();
                
                if ($schedules->count() > 0) {
                    $enrollment->schedules = $schedules;
                }
            }
        }
        
        // Get waitlisted courses
        $waitlisted = DB::table('waitlists as w')
            ->join('course_sections as cs', 'w.section_id', '=', 'cs.id')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->where('w.student_id', $student->id)
            ->select(
                'w.*',
                'cs.section_number',
                'c.code as course_code',
                'c.title',
                'c.credits'
            )
            ->get();
        
        // Generate weekly schedule grid - FIXED
        $weeklySchedule = $this->generateWeeklySchedule($enrollments);
        
        // Calculate total credits
        $totalCredits = $enrollments->sum('credits');
        
        return view('registration.schedule', compact(
            'enrollments',
            'waitlisted',
            'weeklySchedule',
            'totalCredits',
            'currentTerm',
            'student'
        ));
    }
        
    /**
     * Drop a course
     */
    public function dropCourse(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id'
        ]);
        
        $student = DB::table('students')->where('user_id', Auth::id())->first();
        if (!$student) {
            return back()->with('error', 'Student profile not found');
        }
        
        // Verify enrollment belongs to student
        $enrollment = DB::table('enrollments')
            ->where('id', $request->enrollment_id)
            ->where('student_id', $student->id)
            ->first();
            
        if (!$enrollment) {
            return back()->with('error', 'Enrollment not found');
        }
        
        // Check drop deadline
        $term = DB::table('academic_terms')->where('id', $enrollment->term_id)->first();
        $dropDeadline = $term ? Carbon::parse($term->drop_deadline) : null;
        
        if ($dropDeadline && now()->after($dropDeadline)) {
            return back()->with('error', 'Drop deadline has passed. Withdrawal may be required.');
        }
        
        DB::beginTransaction();
        try {
            // Update enrollment status
            DB::table('enrollments')
                ->where('id', $request->enrollment_id)
                ->update([
                    'enrollment_status' => 'dropped',
                    'dropped_at' => now(),
                    'updated_at' => now()
                ]);
            
            // Decrease enrollment count
            DB::table('course_sections')
                ->where('id', $enrollment->section_id)
                ->decrement('current_enrollment');
            
            // Process waitlist if applicable
            $this->processWaitlist($enrollment->section_id);
            
            // Log the drop action
            DB::table('registration_logs')->insert([
                'student_id' => $student->id,
                'section_id' => $enrollment->section_id,
                'term_id' => $enrollment->term_id,
                'action' => 'dropped',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
            
            DB::commit();
            
            return back()->with('success', 'Course dropped successfully');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Drop course failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to drop course');
        }
    }
    
    /**
     * Swap sections
     */
    public function swapSection(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
            'new_section_id' => 'required|exists:course_sections,id'
        ]);
        
        $student = DB::table('students')->where('user_id', Auth::id())->first();
        if (!$student) {
            return back()->with('error', 'Student profile not found');
        }
        
        // Get current enrollment
        $currentEnrollment = DB::table('enrollments as e')
            ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
            ->where('e.id', $request->enrollment_id)
            ->where('e.student_id', $student->id)
            ->select('e.*', 'cs.course_id')
            ->first();
            
        if (!$currentEnrollment) {
            return back()->with('error', 'Current enrollment not found');
        }
        
        // Verify new section is same course
        $newSection = DB::table('course_sections as cs')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->where('cs.id', $request->new_section_id)
            ->select('cs.*', 'c.code as course_code', 'c.title as course_title')
            ->first();
            
        if (!$newSection || $newSection->course_id !== $currentEnrollment->course_id) {
            return back()->with('error', 'New section must be for the same course');
        }
        
        // Check capacity
        if ($newSection->current_enrollment >= $newSection->enrollment_capacity) {
            return back()->with('error', 'New section is full');
        }
        
        // Check time conflicts
        $conflicts = $this->conflictService->checkTimeConflicts(
            $student->id, 
            collect([$newSection]), 
            $currentEnrollment->term_id
        );
        
        if (!empty($conflicts)) {
            return back()->with('error', 'New section has time conflicts');
        }
        
        DB::beginTransaction();
        try {
            // Update enrollment
            DB::table('enrollments')
                ->where('id', $request->enrollment_id)
                ->update([
                    'section_id' => $request->new_section_id,
                    'updated_at' => now()
                ]);
            
            // Update enrollment counts
            DB::table('course_sections')
                ->where('id', $currentEnrollment->section_id)
                ->decrement('current_enrollment');
                
            DB::table('course_sections')
                ->where('id', $request->new_section_id)
                ->increment('current_enrollment');
            
            // Process waitlist for old section
            $this->processWaitlist($currentEnrollment->section_id);
            
            // Log the swap
            DB::table('registration_logs')->insert([
                'student_id' => $student->id,
                'section_id' => $request->new_section_id,
                'term_id' => $currentEnrollment->term_id,
                'action' => 'swapped',
                'details' => json_encode([
                    'from_section' => $currentEnrollment->section_id,
                    'to_section' => $request->new_section_id
                ]),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);
            
            DB::commit();
            
            return back()->with('success', 'Section swapped successfully');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Section swap failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to swap sections');
        }
    }

    /**
     * View waitlist
     */
    public function waitlist()
    {
        $student = DB::table('students')->where('user_id', Auth::id())->first();
        if (!$student) {
            return redirect()->route('registration.catalog')->with('error', 'Student profile not found');
        }
        
        $waitlisted = DB::table('waitlists as w')
            ->join('course_sections as cs', 'w.section_id', '=', 'cs.id')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->leftJoin('users as i', 'cs.instructor_id', '=', 'i.id')
            ->where('w.student_id', $student->id)
            ->select(
                'w.*',
                'cs.section_number',
                'cs.days_of_week',
                'cs.start_time',
                'cs.end_time',
                'cs.room',
                'cs.building',
                'c.code as course_code',
                'c.title',
                'c.credits',
                'i.name as instructor_name'
            )
            ->orderBy('w.position')
            ->get();
        
        return view('registration.waitlist', compact('waitlisted', 'student'));
    }
    
    /**
     * View registration holds
     */
    public function viewHolds()
    {
        $student = DB::table('students')->where('user_id', Auth::id())->first();
        if (!$student) {
            return redirect()->route('registration.catalog')->with('error', 'Student profile not found');
        }
        
        $holds = DB::table('registration_holds')
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('registration.holds', compact('holds', 'student'));
    }
    
    /**
     * View registration history
     */
    public function viewHistory()
    {
        $student = DB::table('students')->where('user_id', Auth::id())->first();
        if (!$student) {
            return redirect()->route('registration.catalog')->with('error', 'Student profile not found');
        }
        
        $history = DB::table('registration_logs as rl')
            ->join('course_sections as cs', 'rl.section_id', '=', 'cs.id')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->join('academic_terms as at', 'rl.term_id', '=', 'at.id')
            ->where('rl.student_id', $student->id)
            ->select(
                'rl.*',
                'c.code as course_code',
                'c.title as course_title',
                'cs.section_number',
                'at.name as term_name'
            )
            ->orderBy('rl.created_at', 'desc')
            ->paginate(20);
        
        return view('registration.history', compact('history', 'student'));
    }
    
    /**
     * Print schedule
     */
    public function printSchedule()
    {
        $student = DB::table('students')->where('user_id', Auth::id())->first();
        if (!$student) {
            return redirect()->route('registration.catalog')->with('error', 'Student profile not found');
        }
        
        $currentTerm = DB::table('academic_terms')->where('is_current', true)->first();
        
        $enrollments = DB::table('enrollments as e')
            ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->leftJoin('users as i', 'cs.instructor_id', '=', 'i.id')
            ->where('e.student_id', $student->id)
            ->where('e.term_id', $currentTerm->id)
            ->whereIn('e.enrollment_status', ['enrolled', 'pending'])
            ->select(
                'e.*',
                'cs.*',
                'c.code as course_code',
                'c.title',
                'c.credits',
                'i.name as instructor_name'
            )
            ->get();
        
        $weeklySchedule = $this->generateWeeklySchedule($enrollments);
        $totalCredits = $enrollments->sum('credits');
        
        return view('registration.print-schedule', compact(
            'enrollments',
            'weeklySchedule',
            'totalCredits',
            'currentTerm',
            'student'
        ));
    }
    
    /**
     * Private helper methods
     */
    
    private function checkRegistrationPeriod($student)
    {
        $currentTerm = DB::table('academic_terms')->where('is_current', true)->first();
        
        if (!$currentTerm) {
            return [
                'is_open' => false,
                'message' => 'No active academic term'
            ];
        }
        
        // Check general registration period
        $period = DB::table('registration_periods')
            ->where('term_id', $currentTerm->id)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
            
        if (!$period) {
            return [
                'is_open' => false,
                'message' => 'Registration period is not open'
            ];
        }
        
        // Check student level restrictions
        if ($period->student_levels) {
            $allowedLevels = json_decode($period->student_levels, true);
            if (!in_array($student->academic_level, $allowedLevels)) {
                return [
                    'is_open' => false,
                    'message' => 'Registration is not open for your academic level'
                ];
            }
        }
        
        // Check priority registration
        if ($period->priority_groups) {
            $priorityGroups = json_decode($period->priority_groups, true);
            $studentGroups = $this->getStudentGroups($student->id);
            
            if (!empty($priorityGroups) && !array_intersect($studentGroups, $priorityGroups)) {
                return [
                    'is_open' => false,
                    'message' => 'This is currently a priority registration period'
                ];
            }
        }
        
        return [
            'is_open' => true,
            'period' => $period,
            'message' => 'Registration is open'
        ];
    }
    
    private function getStudentGroups($studentId)
    {
        $groups = [];
        
        $student = DB::table('students')->where('id', $studentId)->first();
        
        if ($student->is_athlete) {
            $groups[] = 'athlete';
        }
        
        if ($student->is_honors) {
            $groups[] = 'honors';
        }
        
        if ($student->has_disability_accommodation) {
            $groups[] = 'disability';
        }
        
        if ($student->academic_level === 'senior' && $student->expected_graduation_date) {
            $expectedGrad = Carbon::parse($student->expected_graduation_date);
            if ($expectedGrad->diffInMonths(now()) <= 6) {
                $groups[] = 'graduating';
            }
        }
        
        return $groups;
    }
    
    private function addToWaitlist($studentId, $sectionId, $termId)
    {
        // Check if already on waitlist
        $existing = DB::table('waitlists')
            ->where('section_id', $sectionId)
            ->where('student_id', $studentId)
            ->first();
            
        if ($existing) {
            return back()->with('warning', 'You are already on the waitlist for this course');
        }
        
        // Get position
        $position = DB::table('waitlists')
            ->where('section_id', $sectionId)
            ->max('position') + 1;
        
        // Add to waitlist
        DB::table('waitlists')->insert([
            'section_id' => $sectionId,
            'student_id' => $studentId,
            'position' => $position,
            'added_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Log action
        DB::table('registration_logs')->insert([
            'student_id' => $studentId,
            'section_id' => $sectionId,
            'term_id' => $termId,
            'action' => 'waitlisted',
            'details' => json_encode(['position' => $position]),
            'created_at' => now()
        ]);
        
        return back()->with('info', "Course is full. You have been added to the waitlist (Position: {$position})");
    }
    
    private function processWaitlist($sectionId)
    {
        // Get section capacity
        $section = DB::table('course_sections')->where('id', $sectionId)->first();
        
        if (!$section || $section->current_enrollment >= $section->enrollment_capacity) {
            return;
        }
        
        // Get first person on waitlist
        $waitlistEntry = DB::table('waitlists')
            ->where('section_id', $sectionId)
            ->orderBy('position')
            ->first();
            
        if (!$waitlistEntry) {
            return;
        }
        
        // Auto-enroll from waitlist
        DB::beginTransaction();
        try {
            // Create enrollment
            DB::table('enrollments')->insert([
                'student_id' => $waitlistEntry->student_id,
                'section_id' => $sectionId,
                'term_id' => $section->term_id,
                'enrollment_status' => 'enrolled',
                'enrollment_date' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Update enrollment count
            DB::table('course_sections')
                ->where('id', $sectionId)
                ->increment('current_enrollment');
            
            // Remove from waitlist
            DB::table('waitlists')
                ->where('id', $waitlistEntry->id)
                ->delete();
            
            // Update positions
            DB::table('waitlists')
                ->where('section_id', $sectionId)
                ->where('position', '>', $waitlistEntry->position)
                ->decrement('position');
            
            // Log auto-enrollment
            DB::table('registration_logs')->insert([
                'student_id' => $waitlistEntry->student_id,
                'section_id' => $sectionId,
                'term_id' => $section->term_id,
                'action' => 'auto_enrolled_from_waitlist',
                'created_at' => now()
            ]);
            
            // TODO: Send notification to student
            
            DB::commit();
            
            // Process next if still space
            if ($section->current_enrollment + 1 < $section->enrollment_capacity) {
                $this->processWaitlist($sectionId);
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Waitlist processing failed: ' . $e->getMessage());
        }
    }
    
    private function generateWeeklySchedule($enrollments)
    {
        $schedule = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $timeSlots = [];
        
        // Generate time slots from 7:00 AM to 9:00 PM (30-minute intervals)
        for ($hour = 7; $hour <= 21; $hour++) {
            $timeSlots[] = sprintf('%02d:00', $hour);
            if ($hour < 21) {
                $timeSlots[] = sprintf('%02d:30', $hour);
            }
        }
        
        // Initialize the schedule grid
        foreach ($timeSlots as $time) {
            $schedule[$time] = [];
            foreach ($days as $day) {
                $schedule[$time][$day] = null;
            }
        }
        
        // Process enrollments
        foreach ($enrollments as $enrollment) {
            // Check if using section_schedules table
            if (isset($enrollment->schedules) && $enrollment->schedules->count() > 0) {
                // Use section_schedules data
                foreach ($enrollment->schedules as $classSchedule) {
                    $day = $classSchedule->day_of_week;
                    
                    if (!in_array($day, $days)) {
                        continue;
                    }
                    
                    $startTime = \Carbon\Carbon::parse($classSchedule->start_time);
                    $endTime = \Carbon\Carbon::parse($classSchedule->end_time);
                    
                    foreach ($timeSlots as $slot) {
                        $slotTime = \Carbon\Carbon::parse($slot);
                        
                        if ($slotTime >= $startTime && $slotTime < $endTime) {
                            $schedule[$slot][$day] = (object) [
                                'course_code' => $enrollment->course_code,
                                'course_title' => $enrollment->title,
                                'section' => $enrollment->section_number,
                                'room' => $classSchedule->room ?? $enrollment->room ?? 'TBA',
                                'instructor' => $enrollment->instructor_name,
                                'start_time' => $classSchedule->start_time,
                                'end_time' => $classSchedule->end_time,
                                'delivery_mode' => $enrollment->delivery_mode
                            ];
                        }
                    }
                }
            } elseif ($enrollment->days_of_week && $enrollment->start_time && $enrollment->end_time) {
                // Fallback to course_sections data
                $daysMap = [
                    'M' => 'Monday',
                    'T' => 'Tuesday', 
                    'W' => 'Wednesday',
                    'R' => 'Thursday',
                    'F' => 'Friday'
                ];
                
                $dayChars = str_split($enrollment->days_of_week);
                foreach ($dayChars as $dayChar) {
                    if (isset($daysMap[$dayChar])) {
                        $day = $daysMap[$dayChar];
                        
                        $startTime = \Carbon\Carbon::parse($enrollment->start_time);
                        $endTime = \Carbon\Carbon::parse($enrollment->end_time);
                        
                        foreach ($timeSlots as $slot) {
                            $slotTime = \Carbon\Carbon::parse($slot);
                            
                            if ($slotTime >= $startTime && $slotTime < $endTime) {
                                $schedule[$slot][$day] = (object) [
                                    'course_code' => $enrollment->course_code,
                                    'course_title' => $enrollment->title,
                                    'section' => $enrollment->section_number,
                                    'room' => $enrollment->room ?? 'TBA',
                                    'instructor' => $enrollment->instructor_name,
                                    'start_time' => $enrollment->start_time,
                                    'end_time' => $enrollment->end_time,
                                    'delivery_mode' => $enrollment->delivery_mode
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        return $schedule;
    }

}