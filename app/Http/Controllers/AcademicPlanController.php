<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AcademicPlan;
use App\Models\PlanTerm;
use App\Models\PlanCourse;
use App\Models\Course;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Services\DegreeAudit\AcademicPlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class AcademicPlanController extends Controller
{
    protected $planService;

    public function __construct()
    {
        // Service will be injected when created
        // $this->planService = $planService;
        
        $this->middleware('auth');
        $this->middleware('role:student')->only(['myPlan']);
        $this->middleware('role:advisor,admin')->only(['approve']);
    }

    /**
     * Display list of academic plans
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();
        
        // Get plans based on user role
        if ($user->hasRole('student')) {
            $student = Student::where('user_id', $user->id)->first();
            $plans = AcademicPlan::where('student_id', $student->id)
                ->orderBy('is_current', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // Advisors and admins can see all plans
            $plans = AcademicPlan::with(['student', 'primaryProgram'])
                ->when($request->has('student_id'), function ($query) use ($request) {
                    $query->where('student_id', $request->student_id);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $plans
            ]);
        }

        return view('academic-plans.index', compact('plans'));
    }

    /**
     * Display current plan for logged-in student
     */
    public function myPlan(): View|JsonResponse
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return view('academic-plans.error', ['message' => 'Student record not found']);
        }

        $plan = AcademicPlan::with(['planTerms.planCourses.course'])
            ->where('student_id', $student->id)
            ->where('is_current', true)
            ->first();

        if (!$plan) {
            // Create a default plan if none exists
            $plan = $this->createDefaultPlan($student);
        }

        return view('academic-plans.my-plan', compact('student', 'plan'));
    }

    /**
     * Show form to create new academic plan
     */
    public function create(Request $request): View
    {
        $studentId = $request->get('student_id');
        $student = $studentId ? Student::find($studentId) : null;
        
        if (!$student && Auth::user()->hasRole('student')) {
            $student = Student::where('user_id', Auth::id())->first();
        }

        $programs = AcademicProgram::where('is_active', true)->get();
        $currentYear = date('Y');
        $catalogYears = [];
        for ($i = 0; $i < 5; $i++) {
            $year = $currentYear - $i;
            $catalogYears[] = $year . '-' . ($year + 1);
        }

        return view('academic-plans.create', compact('student', 'programs', 'catalogYears'));
    }

    /**
     * Store new academic plan
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'plan_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'plan_type' => 'required|in:four_year,custom,accelerated,part_time,transfer',
            'primary_program_id' => 'required|exists:academic_programs,id',
            'minor_program_id' => 'nullable|exists:academic_programs,id',
            'catalog_year' => 'required|string|max:10',
            'start_date' => 'required|date',
            'expected_graduation_date' => 'required|date|after:start_date',
            'total_terms' => 'required|integer|min:1|max:20'
        ]);

        try {
            DB::beginTransaction();

            // Check authorization
            $student = Student::findOrFail($validated['student_id']);
            if (!$this->canManagePlan($student)) {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
                return back()->with('error', 'Unauthorized to create plan for this student');
            }

            // Create the plan
            $plan = AcademicPlan::create($validated);

            // Create default terms based on plan type
            $this->createDefaultTerms($plan);

            // Set as current if it's the only plan
            if (AcademicPlan::where('student_id', $student->id)->count() === 1) {
                $plan->setAsCurrent();
            }

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Academic plan created successfully',
                    'data' => $plan->load('planTerms')
                ]);
            }

            return redirect()->route('academic-plans.show', $plan)
                ->with('success', 'Academic plan created successfully');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Plan creation error: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to create plan'
                ], 500);
            }
            
            return back()->with('error', 'Failed to create plan')->withInput();
        }
    }

    /**
     * Display specific academic plan
     */
    public function show($id): View|JsonResponse
    {
        $plan = AcademicPlan::with([
            'student',
            'primaryProgram',
            'minorProgram',
            'planTerms.planCourses.course',
            'approvedBy'
        ])->findOrFail($id);

        // Check authorization
        if (!$this->canViewPlan($plan)) {
            abort(403, 'Unauthorized to view this plan');
        }

        // Calculate statistics
        $stats = [
            'total_credits' => $plan->getTotalCredits(),
            'avg_credits_per_term' => $plan->getAverageCreditsPerTerm(),
            'completed_terms' => $plan->getCompletedTermsCount(),
            'total_terms' => $plan->total_terms
        ];

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $plan,
                'stats' => $stats
            ]);
        }

        return view('academic-plans.show', compact('plan', 'stats'));
    }

    /**
     * Show form to edit academic plan
     */
    public function edit($id): View
    {
        $plan = AcademicPlan::findOrFail($id);
        
        if (!$this->canManagePlan($plan->student)) {
            abort(403, 'Unauthorized to edit this plan');
        }

        $programs = AcademicProgram::where('is_active', true)->get();
        $currentYear = date('Y');
        $catalogYears = [];
        for ($i = 0; $i < 5; $i++) {
            $year = $currentYear - $i;
            $catalogYears[] = $year . '-' . ($year + 1);
        }

        return view('academic-plans.edit', compact('plan', 'programs', 'catalogYears'));
    }

    /**
     * Update academic plan
     */
    public function update(Request $request, $id): JsonResponse|RedirectResponse
    {
        $plan = AcademicPlan::findOrFail($id);
        
        if (!$this->canManagePlan($plan->student)) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return back()->with('error', 'Unauthorized to update this plan');
        }

        $validated = $request->validate([
            'plan_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'minor_program_id' => 'nullable|exists:academic_programs,id',
            'expected_graduation_date' => 'required|date|after:start_date',
            'is_current' => 'nullable|boolean'
        ]);

        try {
            $plan->update($validated);

            if ($request->has('is_current') && $validated['is_current']) {
                $plan->setAsCurrent();
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Plan updated successfully',
                    'data' => $plan
                ]);
            }

            return redirect()->route('academic-plans.show', $plan)
                ->with('success', 'Plan updated successfully');

        } catch (\Exception $e) {
            Log::error('Plan update error: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to update plan'
                ], 500);
            }
            
            return back()->with('error', 'Failed to update plan');
        }
    }

    /**
     * Delete academic plan
     */
    public function destroy($id): JsonResponse|RedirectResponse
    {
        $plan = AcademicPlan::findOrFail($id);
        
        if (!$this->canManagePlan($plan->student)) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return back()->with('error', 'Unauthorized to delete this plan');
        }

        try {
            // Don't delete current plan unless it's the only one
            if ($plan->is_current) {
                $otherPlans = AcademicPlan::where('student_id', $plan->student_id)
                    ->where('id', '!=', $plan->id)
                    ->count();
                    
                if ($otherPlans === 0) {
                    throw new \Exception('Cannot delete the only academic plan');
                }
            }

            $plan->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Plan deleted successfully'
                ]);
            }

            return redirect()->route('academic-plans.index')
                ->with('success', 'Plan deleted successfully');

        } catch (\Exception $e) {
            Log::error('Plan deletion error: ' . $e->getMessage());
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Validate academic plan
     */
    public function validatePlan(Request $request, $id): JsonResponse
    {
        $plan = AcademicPlan::findOrFail($id);
        
        if (!$this->canViewPlan($plan)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $isValid = $plan->validate();
            
            return response()->json([
                'success' => true,
                'valid' => $isValid,
                'errors' => $plan->validation_errors,
                'message' => $isValid ? 'Plan is valid' : 'Plan has validation errors'
            ]);

        } catch (\Exception $e) {
            Log::error('Plan validation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to validate plan'
            ], 500);
        }
    }

    /**
     * Approve academic plan (advisor/admin only)
     */
    public function approve(Request $request, $id): JsonResponse
    {
        $plan = AcademicPlan::findOrFail($id);
        
        if (!Auth::user()->hasAnyRole(['advisor', 'admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $plan->approve(Auth::user(), $validated['notes'] ?? null);
            
            return response()->json([
                'success' => true,
                'message' => 'Plan approved successfully',
                'data' => $plan
            ]);

        } catch (\Exception $e) {
            Log::error('Plan approval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to approve plan'
            ], 500);
        }
    }

    /**
     * Add term to plan
     */
    public function addTerm(Request $request, $id): JsonResponse
    {
        $plan = AcademicPlan::findOrFail($id);
        
        if (!$this->canManagePlan($plan->student)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'term_name' => 'required|string|max:255',
            'term_type' => 'required|in:fall,spring,summer,winter,intersession',
            'year' => 'required|integer|min:2020|max:2030',
            'min_credits' => 'nullable|numeric|min:0',
            'max_credits' => 'nullable|numeric|min:0'
        ]);

        try {
            $sequenceNumber = $plan->planTerms()->max('sequence_number') + 1;
            
            $term = PlanTerm::create([
                'plan_id' => $plan->id,
                'sequence_number' => $sequenceNumber,
                'term_name' => $validated['term_name'],
                'term_type' => $validated['term_type'],
                'year' => $validated['year'],
                'min_credits' => $validated['min_credits'] ?? 12,
                'max_credits' => $validated['max_credits'] ?? 18,
                'status' => 'planned'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Term added successfully',
                'data' => $term
            ]);

        } catch (\Exception $e) {
            Log::error('Add term error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to add term'
            ], 500);
        }
    }

    /**
     * Update term in plan
     */
    public function updateTerm(Request $request, $planId, $termId): JsonResponse
    {
        $plan = AcademicPlan::findOrFail($planId);
        $term = PlanTerm::where('plan_id', $planId)->where('id', $termId)->firstOrFail();
        
        if (!$this->canManagePlan($plan->student)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'term_name' => 'nullable|string|max:255',
            'min_credits' => 'nullable|numeric|min:0',
            'max_credits' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        try {
            $term->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Term updated successfully',
                'data' => $term
            ]);

        } catch (\Exception $e) {
            Log::error('Update term error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update term'
            ], 500);
        }
    }

    /**
     * Delete term from plan
     */
    public function deleteTerm($planId, $termId): JsonResponse
    {
        $plan = AcademicPlan::findOrFail($planId);
        $term = PlanTerm::where('plan_id', $planId)->where('id', $termId)->firstOrFail();
        
        if (!$this->canManagePlan($plan->student)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Check if term has courses
            if ($term->planCourses()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete term with courses. Remove courses first.'
                ], 400);
            }

            $term->delete();
            
            // Resequence remaining terms
            $plan->planTerms()->where('sequence_number', '>', $term->sequence_number)
                ->decrement('sequence_number');
            
            return response()->json([
                'success' => true,
                'message' => 'Term deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete term error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete term'
            ], 500);
        }
    }

    /**
     * Add course to term
     */
    public function addCourse(Request $request, $termId): JsonResponse
    {
        $term = PlanTerm::findOrFail($termId);
        $plan = $term->plan;
        
        if (!$this->canManagePlan($plan->student)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'is_required' => 'nullable|boolean',
            'is_backup' => 'nullable|boolean',
            'notes' => 'nullable|string'
        ]);

        try {
            // Check if course already in term
            if ($term->planCourses()->where('course_id', $validated['course_id'])->exists()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Course already in this term'
                ], 400);
            }

            $course = Course::findOrFail($validated['course_id']);
            
            $planCourse = PlanCourse::create([
                'plan_term_id' => $term->id,
                'course_id' => $course->id,
                'credits' => $course->credits,
                'status' => 'planned',
                'is_required' => $validated['is_required'] ?? true,
                'is_backup' => $validated['is_backup'] ?? false,
                'notes' => $validated['notes'] ?? null
            ]);

            // TODO: Check prerequisites and update validation
            
            return response()->json([
                'success' => true,
                'message' => 'Course added successfully',
                'data' => $planCourse->load('course')
            ]);

        } catch (\Exception $e) {
            Log::error('Add course error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to add course'
            ], 500);
        }
    }

    /**
     * Remove course from term
     */
    public function removeCourse($termId, $courseId): JsonResponse
    {
        $term = PlanTerm::findOrFail($termId);
        $plan = $term->plan;
        
        if (!$this->canManagePlan($plan->student)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $planCourse = PlanCourse::where('plan_term_id', $termId)
                ->where('id', $courseId)
                ->firstOrFail();
            
            $planCourse->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Course removed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Remove course error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to remove course'
            ], 500);
        }
    }

    /**
     * Helper: Check if user can view plan
     */
    protected function canViewPlan(AcademicPlan $plan): bool
    {
        $user = Auth::user();
        
        // Student can view their own plans
        if ($plan->student->user_id === $user->id) {
            return true;
        }
        
        // Advisors and admins can view all plans
        if ($user->hasAnyRole(['advisor', 'registrar', 'admin'])) {
            return true;
        }
        
        return false;
    }

    /**
     * Helper: Check if user can manage plan
     */
    protected function canManagePlan(Student $student): bool
    {
        $user = Auth::user();
        
        // Student can manage their own plans
        if ($student->user_id === $user->id) {
            return true;
        }
        
        // Advisors and admins can manage all plans
        if ($user->hasAnyRole(['advisor', 'admin'])) {
            return true;
        }
        
        return false;
    }

    /**
     * Helper: Create default plan for student
     */
    protected function createDefaultPlan(Student $student): AcademicPlan
    {
        $program = $student->program ?? AcademicProgram::first();
        
        $plan = AcademicPlan::create([
            'student_id' => $student->id,
            'plan_name' => 'Default Four-Year Plan',
            'description' => 'Automatically generated four-year plan',
            'plan_type' => 'four_year',
            'primary_program_id' => $program->id,
            'catalog_year' => date('Y') . '-' . (date('Y') + 1),
            'start_date' => now(),
            'expected_graduation_date' => now()->addYears(4),
            'total_terms' => 8,
            'status' => 'draft',
            'is_current' => true
        ]);

        $this->createDefaultTerms($plan);
        
        return $plan;
    }

    /**
     * Helper: Create default terms for a plan
     */
    protected function createDefaultTerms(AcademicPlan $plan): void
    {
        $startYear = $plan->start_date->year;
        $terms = [];
        
        for ($i = 0; $i < $plan->total_terms; $i++) {
            $year = $startYear + intval($i / 2);
            $termType = ($i % 2 === 0) ? 'fall' : 'spring';
            $termName = ucfirst($termType) . ' ' . $year;
            
            $terms[] = [
                'plan_id' => $plan->id,
                'sequence_number' => $i + 1,
                'term_name' => $termName,
                'term_type' => $termType,
                'year' => $year,
                'planned_credits' => 15,
                'min_credits' => 12,
                'max_credits' => 18,
                'status' => 'planned',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        
        PlanTerm::insert($terms);
    }
}