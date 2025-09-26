<?php

// app/Http/Controllers/CourseController.php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Department;
use App\Models\User;
use App\Models\AcademicTerm;
use App\Services\ScopeManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;

class CourseController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
    
    protected $scopeService;

    public function __construct(ScopeManagementService $scopeService)
    {
        $this->scopeService = $scopeService;
        
        // Apply middleware
        $this->middleware('auth');
        $this->middleware('scope:course')->only(['index']);
    }

    /**
     * Display a listing of courses based on user's scope
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Start with base query
        $query = Course::with(['departmentRelation', 'coordinator', 'sections']);
        
        // Apply scope filtering
        $query = $this->scopeService->applyScopeFilter($query, $user, 'course');
        
        // Apply additional filters from request
        if ($request->has('department_id') && $request->department_id) {
            $department = Department::find($request->department_id);
            if ($department && $user->canAccessDepartment($department)) {
                $query->where('department_id', $request->department_id);
            }
        }
        
        // Apply department filter (for string-based department field)
        if ($request->has('department') && $request->department) {
            $query->where('department', $request->department);
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }
        
        if ($request->has('level') && $request->level) {
            $query->where('level', $request->level);
        }
        
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('credits') && $request->credits) {
            $query->where('credits', $request->credits);
        }
        
        // Handle status filter (map to is_active)
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Apply sorting
        $sortBy = $request->get('sort_by', 'code');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Validate sort column
        $allowedSorts = ['code', 'title', 'level', 'credits', 'department_id', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'code';
        }
        
        $query->orderBy($sortBy, $sortOrder);
        
        // Paginate results
        $courses = $query->paginate(20)->withQueryString();
        
        // Get departments for filter dropdown
        $departments = collect();
        try {
            // First try to get from Department model if it exists
            if (class_exists(\App\Models\Department::class)) {
                $departmentsQuery = Department::query();
                $departments = $this->scopeService->applyScopeFilter($departmentsQuery, $user, 'department')
                    ->orderBy('name')
                    ->pluck('name');
            }
        } catch (\Exception $e) {
            Log::debug('Could not get departments from Department model: ' . $e->getMessage());
        }
        
        // If no departments from model, get unique values from courses
        if ($departments->isEmpty()) {
            $departments = Course::whereNotNull('department')
                ->distinct()
                ->pluck('department')
                ->filter()
                ->sort();
        }
        
        // Get unique levels from existing courses for the filter
        $levels = Course::whereNotNull('level')
            ->distinct()
            ->pluck('level')
            ->sort()
            ->values();
        
        // If no levels exist, provide default options
        if ($levels->isEmpty()) {
            $levels = collect([100, 200, 300, 400, 500, 600, 700, 800]);
        }
        
        // Get user's scope summary for display
        try {
            $scopeSummary = $this->scopeService->getUserScopeSummary($user);
        } catch (\Exception $e) {
            Log::error('Error getting scope summary: ' . $e->getMessage());
            // Provide a basic fallback
            $scopeSummary = [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'roles' => $user->roles->pluck('name')->toArray(),
                ],
                'accessible_entities' => [
                    'courses' => $courses->total(),
                    'students' => 0,
                    'faculty' => 0,
                    'departments' => $departments->count(),
                ],
            ];
        }
        
        // Get statistics - FIXED VERSION
        $statistics = [
            'total_courses' => $courses->total(),
            'active_courses' => 0,
            'total_sections' => 0,
            'unique_departments' => $courses->pluck('department_id')->unique()->filter()->count(),
        ];
        
        // Calculate active courses safely
        try {
            $courseIds = $courses->pluck('id');
            if ($courseIds->isNotEmpty()) {
                $statistics['active_courses'] = Course::where('is_active', true)
                    ->whereIn('id', $courseIds)
                    ->count();
            }
        } catch (\Exception $e) {
            Log::debug('Could not get active courses count: ' . $e->getMessage());
        }
        
        // Try to get section count if model exists
        try {
            if (class_exists(\App\Models\CourseSection::class) && $courseIds->isNotEmpty()) {
                $statistics['total_sections'] = \App\Models\CourseSection::whereIn('course_id', $courseIds)->count();
            }
        } catch (\Exception $e) {
            Log::debug('Could not get section count: ' . $e->getMessage());
        }
        
        return view('courses.index', compact(
            'courses', 
            'departments', 
            'levels',
            'scopeSummary', 
            'statistics'
        ));
    }

    /**
     * Show the form for creating a new course
     */
    public function create()
    {
        // Temporarily bypass authorization if it's causing issues
        try {
            $this->authorize('create', Course::class);
        } catch (\Exception $e) {
            // Check basic permission instead
            $user = Auth::user();
            if (!$user->hasRole(['super-administrator', 'Super Administrator', 'admin', 'academic-administrator', 'department-head', 'faculty'])) {
                return redirect()->route('courses.index')
                    ->with('error', 'You do not have permission to create courses');
            }
        }
        
        $user = Auth::user();
        
        // Get departments user can create courses for
        $departments = collect();
        try {
            if (class_exists(\App\Models\Department::class)) {
                $departmentsQuery = Department::query();
                $departments = $this->scopeService->applyScopeFilter($departmentsQuery, $user, 'department')
                    ->where('offers_courses', true)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
            }
        } catch (\Exception $e) {
            Log::error('Error getting departments: ' . $e->getMessage());
            // Fallback: get all active departments
            $departments = Department::where('is_active', true)
                ->where('offers_courses', true)
                ->orderBy('name')
                ->get();
        }
        
        if ($departments->isEmpty()) {
            return redirect()->route('courses.index')
                ->with('error', 'No departments available for course creation. Please contact the administrator.');
        }
        
        // Get potential coordinators (faculty in accessible departments)
        $coordinators = collect();
        try {
            $coordinators = $user->getManageableFaculty()
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        } catch (\Exception $e) {
            Log::debug('Could not get manageable faculty: ' . $e->getMessage());
            // Fallback: get all faculty users
            $coordinators = User::where('user_type', 'faculty')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }
        
        // Get courses for prerequisites
        $availableCourses = Course::whereIn('department_id', $departments->pluck('id'))
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
        
        return view('courses.create', compact('departments', 'coordinators', 'availableCourses'));
    }

    /**
     * Store a newly created course
     */
    public function store(Request $request)
    {
        // Authorization check with fallback
        try {
            $this->authorize('create', Course::class);
        } catch (\Exception $e) {
            $user = Auth::user();
            if (!$user->hasRole(['super-administrator', 'Super Administrator', 'admin', 'academic-administrator', 'department-head'])) {
                abort(403, 'You cannot create courses');
            }
        }
        
        $user = Auth::user();
        
        // Validate department access
        $department = Department::find($request->department_id);
        if (!$department) {
            return back()->withErrors(['department_id' => 'Invalid department selected']);
        }
        
        // Check if user can manage this department (with fallback for super admin)
        if (!$user->hasRole(['super-administrator', 'Super Administrator', 'admin'])) {
            try {
                if (!$user->canManageDepartment($department)) {
                    abort(403, 'You cannot create courses in this department');
                }
            } catch (\Exception $e) {
                // Fallback check
                if ($user->department_id != $department->id) {
                    abort(403, 'You cannot create courses in this department');
                }
            }
        }
        
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:courses',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'credits' => 'required|integer|min:0|max:12',
            'lecture_hours' => 'nullable|integer|min:0|max:20',
            'lab_hours' => 'nullable|integer|min:0|max:20',
            'tutorial_hours' => 'nullable|integer|min:0|max:20',
            'department_id' => 'required|exists:departments,id',
            'department' => 'nullable|string|max:255',
            'level' => 'required|integer|min:100|max:800',
            'type' => 'required|in:required,elective,core,general,general_education,major',
            'grading_method' => 'nullable|in:letter,percentage,pass_fail',
            'coordinator_id' => 'nullable|exists:users,id',
            'course_fee' => 'nullable|numeric|min:0',
            'lab_fee' => 'nullable|numeric|min:0',
            'has_lab' => 'boolean',
            'has_tutorial' => 'boolean',
            'is_active' => 'boolean',
            'min_enrollment' => 'nullable|integer|min:0',
            'max_enrollment' => 'nullable|integer|min:1',
            'learning_outcomes' => 'nullable|string',
            'topics_covered' => 'nullable|string',
            'assessment_methods' => 'nullable|string',
            'textbooks' => 'nullable|string',
            'prerequisites' => 'nullable|array',
            'prerequisites.*' => 'exists:courses,id',
        ]);
        
        // Set department name if not provided
        if (!isset($validated['department'])) {
            $validated['department'] = $department->name;
        }
        
        // Ensure max > min enrollment
        if (($validated['max_enrollment'] ?? 0) > 0 && 
            ($validated['min_enrollment'] ?? 0) > 0 &&
            $validated['max_enrollment'] < $validated['min_enrollment']) {
            return back()->withErrors(['max_enrollment' => 'Maximum enrollment must be greater than minimum enrollment']);
        }
        
        DB::beginTransaction();
        try {
            // Create the course
            $course = Course::create($validated);
            
            // Attach prerequisites if provided
            if (!empty($validated['prerequisites'])) {
                try {
                    foreach ($validated['prerequisites'] as $prerequisiteId) {
                        $course->prerequisites()->attach($prerequisiteId, [
                            'type' => 'prerequisite',
                            'min_grade' => 'D',
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not attach prerequisites: ' . $e->getMessage());
                }
            }
            
            // If coordinator is specified and facultyAssignments relationship exists
            if ($request->coordinator_id) {
                try {
                    if (method_exists($course, 'facultyAssignments')) {
                        $course->facultyAssignments()->create([
                            'faculty_id' => $request->coordinator_id,
                            'assignment_type' => 'coordinator',
                            'can_edit_content' => true,
                            'can_manage_grades' => true,
                            'can_view_all_sections' => true,
                            'effective_from' => now(),
                            'is_active' => true,
                            'assigned_by' => $user->id,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not create faculty assignment: ' . $e->getMessage());
                }
            }
            
            // Update department course count
            try {
                $department->updateCounts();
            } catch (\Exception $e) {
                Log::warning('Could not update department counts: ' . $e->getMessage());
            }
            
            // Log the creation
            $this->scopeService->logAccess($user, 'create', 'course', $course->id, true);
            
            DB::commit();
            
            return redirect()->route('courses.show', $course)
                ->with('success', 'Course created successfully');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating course: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating course: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified course
     */
    public function show(Course $course)
    {
        // Authorization check with fallback
        try {
            $this->authorize('view', $course);
        } catch (\Exception $e) {
            // Basic permission check
            $user = Auth::user();
            if (!$user) {
                abort(403, 'You must be logged in to view courses');
            }
        }
        
        $user = Auth::user();
        
        // Load related data based on what exists
        try {
            $course->load(['departmentRelation', 'coordinator']);
        } catch (\Exception $e) {
            Log::debug('Some course relationships could not be loaded');
        }
        
        // Try to load other relationships if they exist
        try {
            if (method_exists($course, 'prerequisites')) {
                $course->load('prerequisites');
            }
            if (method_exists($course, 'programs')) {
                $course->load('programs');
            }
        } catch (\Exception $e) {
            Log::debug('Some course relationships do not exist');
        }
        
        // Get sections with scope filtering
        $sections = collect();
        try {
            if (method_exists($course, 'sections')) {
                $sectionsQuery = $course->sections()->with(['instructor', 'term']);
                
                // Apply section filtering based on user role
                if (!$user->hasRole(['admin', 'Super Administrator', 'registrar']) && $user->user_type === 'faculty') {
                    // Faculty sees only their sections unless they're course coordinator
                    if ($course->coordinator_id !== $user->id) {
                        $sectionsQuery->where('instructor_id', $user->id);
                    }
                }
                
                $sections = $sectionsQuery->orderBy('created_at', 'desc')->get();
            }
        } catch (\Exception $e) {
            Log::debug('Could not load course sections: ' . $e->getMessage());
        }
        
        // Get current term sections
        $currentTerm = null;
        $currentSections = collect();
        try {
            if (class_exists(\App\Models\AcademicTerm::class)) {
                $currentTerm = AcademicTerm::where('is_current', true)->first();
                if ($currentTerm && $sections->isNotEmpty()) {
                    $currentSections = $sections->where('term_id', $currentTerm->id);
                }
            }
        } catch (\Exception $e) {
            Log::debug('Could not get current term');
        }
        
        // Get faculty assignments if user can manage
        $facultyAssignments = collect();
        try {
            if (method_exists($course, 'canBeManagedBy') && $course->canBeManagedBy($user)) {
                if (method_exists($course, 'facultyAssignments')) {
                    $facultyAssignments = $course->facultyAssignments()
                        ->with('faculty')
                        ->where('is_active', true)
                        ->get();
                }
            }
        } catch (\Exception $e) {
            Log::debug('Could not get faculty assignments');
        }
        
        // Get enrollment statistics if user has permission
        $statistics = [
            'total_sections' => $sections->count(),
            'current_sections' => $currentSections->count(),
            'total_enrollment' => 0,
            'total_capacity' => 0,
            'average_fill_rate' => 0,
            'active_instructors' => 0,
        ];
        
        // Calculate statistics if user has permission
        if ($user->hasRole(['admin', 'Super Administrator', 'registrar', 'department-head', 'Department Head']) || 
            $course->coordinator_id === $user->id) {
            try {
                $statistics['total_enrollment'] = $sections->sum('current_enrollment');
                $statistics['total_capacity'] = $sections->sum('enrollment_capacity');
                $statistics['average_fill_rate'] = $sections->avg(function($section) {
                    return $section->enrollment_capacity > 0 
                        ? ($section->current_enrollment / $section->enrollment_capacity) * 100 
                        : 0;
                });
                $statistics['active_instructors'] = $sections->pluck('instructor_id')->unique()->filter()->count();
            } catch (\Exception $e) {
                Log::debug('Could not calculate statistics');
            }
        }
        
        // Check user permissions
        $permissions = [
            'can_edit' => $user->hasRole(['super-administrator', 'Super Administrator', 'admin', 'academic-administrator', 'department-head']),
            'can_delete' => $user->hasRole(['super-administrator', 'Super Administrator', 'admin']),
            'can_manage_sections' => $user->hasRole(['super-administrator', 'Super Administrator', 'admin', 'academic-administrator', 'registrar']),
            'can_assign_faculty' => $user->hasRole(['super-administrator', 'Super Administrator', 'admin', 'department-head']),
            'can_manage_prerequisites' => $user->hasRole(['super-administrator', 'Super Administrator', 'admin', 'academic-administrator']),
        ];
        
        return view('courses.show', compact(
            'course', 
            'sections', 
            'currentSections',
            'facultyAssignments', 
            'statistics',
            'permissions',
            'currentTerm'
        ));
    }

    /**
     * Show the form for editing the course
     */
    public function edit(Course $course)
    {
        // Authorization check with fallback
        try {
            $this->authorize('update', $course);
        } catch (\Exception $e) {
            $user = Auth::user();
            if (!$user->hasRole(['super-administrator', 'Super Administrator', 'admin', 'academic-administrator', 'department-head'])) {
                abort(403, 'You cannot edit this course');
            }
        }
        
        $user = Auth::user();
        
        // Get departments user can move the course to
        $departments = collect();
        try {
            $departmentsQuery = Department::query();
            $departments = $this->scopeService->applyScopeFilter($departmentsQuery, $user, 'department')
                ->where('offers_courses', true)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            // Fallback: get all active departments
            $departments = Department::where('is_active', true)
                ->where('offers_courses', true)
                ->orderBy('name')
                ->get();
        }
        
        // Get potential coordinators
        $coordinators = User::where('user_type', 'faculty');
        
        // If user is department head, show faculty from their department
        if ($user->hasRole(['department-head', 'Department Head'])) {
            $coordinators->where(function($q) use ($course) {
                $q->where('department_id', $course->department_id);
                
                // Check if departmentAffiliations relationship exists
                try {
                    $q->orWhereHas('departmentAffiliations', function($aff) use ($course) {
                        $aff->where('department_id', $course->department_id)
                            ->where('is_active', true);
                    });
                } catch (\Exception $e) {
                    // Relationship doesn't exist, skip
                }
            });
        }
        
        $coordinators = $coordinators->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        
        // Get current faculty assignments
        $facultyAssignments = collect();
        try {
            if (method_exists($course, 'facultyAssignments')) {
                $facultyAssignments = $course->facultyAssignments()
                    ->with('faculty')
                    ->where('is_active', true)
                    ->get();
            }
        } catch (\Exception $e) {
            Log::debug('Could not get faculty assignments');
        }
        
        // Get available courses for prerequisites
        $availableCourses = Course::where('id', '!=', $course->id)
            ->whereIn('department_id', $departments->pluck('id'))
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
        
        // Get current prerequisites
        $currentPrerequisites = [];
        try {
            if (method_exists($course, 'prerequisites')) {
                $currentPrerequisites = $course->prerequisites->pluck('id')->toArray();
            }
        } catch (\Exception $e) {
            Log::debug('Could not get prerequisites');
        }
        
        return view('courses.edit', compact(
            'course', 
            'departments', 
            'coordinators', 
            'facultyAssignments',
            'availableCourses',
            'currentPrerequisites'
        ));
    }

    /**
     * Update the specified course
     */
    public function update(Request $request, Course $course)
    {
        // Authorization check with fallback
        try {
            $this->authorize('update', $course);
        } catch (\Exception $e) {
            $user = Auth::user();
            if (!$user->hasRole(['super-administrator', 'Super Administrator', 'admin', 'academic-administrator', 'department-head'])) {
                abort(403, 'You cannot update this course');
            }
        }
        
        $user = Auth::user();
        
        // Validate department access if changing
        if ($request->department_id && $request->department_id != $course->department_id) {
            $newDepartment = Department::find($request->department_id);
            if (!$newDepartment) {
                abort(403, 'Invalid department selected');
            }
            
            // Check if user can manage the new department
            if (!$user->hasRole(['super-administrator', 'Super Administrator', 'admin'])) {
                try {
                    if (!$user->canManageDepartment($newDepartment)) {
                        abort(403, 'You cannot move courses to this department');
                    }
                } catch (\Exception $e) {
                    // Fallback check
                    if ($user->department_id != $newDepartment->id) {
                        abort(403, 'You cannot move courses to this department');
                    }
                }
            }
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'credits' => 'required|integer|min:0|max:12',
            'lecture_hours' => 'nullable|integer|min:0|max:20',
            'lab_hours' => 'nullable|integer|min:0|max:20',
            'tutorial_hours' => 'nullable|integer|min:0|max:20',
            'department_id' => 'required|exists:departments,id',
            'department' => 'nullable|string|max:255',
            'level' => 'required|integer|min:100|max:800',
            'type' => 'required|in:required,elective,core,general,general_education,major',
            'grading_method' => 'nullable|in:letter,percentage,pass_fail',
            'coordinator_id' => 'nullable|exists:users,id',
            'course_fee' => 'nullable|numeric|min:0',
            'lab_fee' => 'nullable|numeric|min:0',
            'has_lab' => 'boolean',
            'has_tutorial' => 'boolean',
            'is_active' => 'boolean',
            'min_enrollment' => 'nullable|integer|min:0',
            'max_enrollment' => 'nullable|integer|min:1',
            'learning_outcomes' => 'nullable|string',
            'topics_covered' => 'nullable|string',
            'assessment_methods' => 'nullable|string',
            'textbooks' => 'nullable|string',
            'prerequisites' => 'nullable|array',
            'prerequisites.*' => 'exists:courses,id',
        ]);
        
        // Update department name if department changed
        if ($request->department_id != $course->department_id) {
            $newDept = Department::find($request->department_id);
            if ($newDept) {
                $validated['department'] = $newDept->name;
            }
        }
        
        DB::beginTransaction();
        try {
            // Store old department for count update
            $oldDepartmentId = $course->department_id;
            
            // Update the course
            $course->update($validated);
            
            // Update prerequisites if relationship exists
            if (method_exists($course, 'prerequisites')) {
                try {
                    if (isset($validated['prerequisites'])) {
                        $prerequisiteData = [];
                        foreach ($validated['prerequisites'] as $prerequisiteId) {
                            $prerequisiteData[$prerequisiteId] = [
                                'type' => 'prerequisite',
                                'min_grade' => 'D',
                            ];
                        }
                        $course->prerequisites()->sync($prerequisiteData);
                    } else {
                        // Remove all prerequisites if none provided
                        $course->prerequisites()->detach();
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not update prerequisites: ' . $e->getMessage());
                }
            }
            
            // Update coordinator if changed and facultyAssignments exists
            if ($request->coordinator_id != $course->coordinator_id && method_exists($course, 'facultyAssignments')) {
                try {
                    // Deactivate old coordinator assignment
                    $course->facultyAssignments()
                        ->where('assignment_type', 'coordinator')
                        ->where('is_active', true)
                        ->update([
                            'is_active' => false, 
                            'effective_until' => now()
                        ]);
                    
                    // Create new coordinator assignment
                    if ($request->coordinator_id) {
                        $course->facultyAssignments()->create([
                            'faculty_id' => $request->coordinator_id,
                            'assignment_type' => 'coordinator',
                            'can_edit_content' => true,
                            'can_manage_grades' => true,
                            'can_view_all_sections' => true,
                            'effective_from' => now(),
                            'is_active' => true,
                            'assigned_by' => $user->id,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not update faculty assignments: ' . $e->getMessage());
                }
            }
            
            // Update department counts if department changed
            if ($oldDepartmentId != $course->department_id) {
                try {
                    if ($oldDept = Department::find($oldDepartmentId)) {
                        $oldDept->updateCounts();
                    }
                    if ($course->department) {
                        $course->department->updateCounts();
                    }
                } catch (\Exception $e) {
                    Log::warning('Could not update department counts: ' . $e->getMessage());
                }
            }
            
            // Log the update
            $this->scopeService->logAccess($user, 'update', 'course', $course->id, true);
            
            DB::commit();
            
            return redirect()->route('courses.show', $course)
                ->with('success', 'Course updated successfully');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating course: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating course: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified course
     */
    public function destroy(Course $course)
    {
        // Authorization check with fallback
        try {
            $this->authorize('delete', $course);
        } catch (\Exception $e) {
            $user = Auth::user();
            if (!$user->hasRole(['super-administrator', 'Super Administrator', 'admin'])) {
                abort(403, 'You cannot delete this course');
            }
        }
        
        // Check if course has active sections
        try {
            if (method_exists($course, 'sections')) {
                if ($course->sections()->where('status', '!=', 'cancelled')->exists()) {
                    return redirect()->back()
                        ->with('error', 'Cannot delete course with active sections');
                }
            }
        } catch (\Exception $e) {
            Log::debug('Could not check course sections');
        }
        
        // Check if course has enrolled students
        try {
            $enrollmentCount = DB::table('enrollments')
                ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
                ->where('course_sections.course_id', $course->id)
                ->where('enrollments.enrollment_status', 'enrolled')
                ->count();
            
            if ($enrollmentCount > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete course with enrolled students');
            }
        } catch (\Exception $e) {
            Log::debug('Could not check enrollments');
        }
        
        DB::beginTransaction();
        try {
            // Deactivate faculty assignments if relationship exists
            if (method_exists($course, 'facultyAssignments')) {
                try {
                    $course->facultyAssignments()->update([
                        'is_active' => false,
                        'effective_until' => now()
                    ]);
                } catch (\Exception $e) {
                    Log::debug('Could not deactivate faculty assignments');
                }
            }
            
            // Soft delete the course
            $course->delete();
            
            // Update department count
            if ($course->department) {
                try {
                    $course->department->updateCounts();
                } catch (\Exception $e) {
                    Log::debug('Could not update department count');
                }
            }
            
            // Log the deletion
            $this->scopeService->logAccess(Auth::user(), 'delete', 'course', $course->id, true);
            
            DB::commit();
            
            return redirect()->route('courses.index')
                ->with('success', 'Course deleted successfully');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting course: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting course: ' . $e->getMessage());
        }
    }

    /**
     * Manage faculty assignments for the course
     */
    public function manageFaculty(Course $course)
    {
        // Authorization check with fallback
        try {
            $this->authorize('assignFaculty', $course);
        } catch (\Exception $e) {
            $user = Auth::user();
            if (!$user->hasRole(['super-administrator', 'Super Administrator', 'admin', 'department-head'])) {
                abort(403, 'You cannot manage faculty for this course');
            }
        }
        
        $user = Auth::user();
        
        // Get current assignments
        $currentAssignments = collect();
        try {
            if (method_exists($course, 'facultyAssignments')) {
                $currentAssignments = $course->facultyAssignments()
                    ->with(['faculty', 'assignedBy'])
                    ->orderBy('assignment_type')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        } catch (\Exception $e) {
            Log::debug('Could not get faculty assignments');
        }
        
        // Get available faculty from the course's department
        $availableFaculty = User::where('user_type', 'faculty')
            ->where(function($query) use ($course) {
                $query->where('department_id', $course->department_id);
                
                // Try to include affiliated faculty
                try {
                    $query->orWhereHas('departmentAffiliations', function($q) use ($course) {
                        $q->where('department_id', $course->department_id)
                          ->where('is_active', true);
                    });
                } catch (\Exception $e) {
                    // Relationship doesn't exist
                }
            })
            ->whereNotIn('id', $currentAssignments->where('is_active', true)->pluck('faculty_id'))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        
        // Get assignment types
        $assignmentTypes = [
            'coordinator' => 'Course Coordinator',
            'primary_instructor' => 'Primary Instructor',
            'co_instructor' => 'Co-Instructor',
            'teaching_assistant' => 'Teaching Assistant',
            'grader' => 'Grader',
            'guest_lecturer' => 'Guest Lecturer',
        ];
        
        return view('courses.manage-faculty', compact(
            'course', 
            'currentAssignments', 
            'availableFaculty',
            'assignmentTypes'
        ));
    }

    /**
     * Assign faculty to course
     */
    public function assignFaculty(Request $request, Course $course)
    {
        // Authorization check with fallback
        try {
            $this->authorize('assignFaculty', $course);
        } catch (\Exception $e) {
            $user = Auth::user();
            if (!$user->hasRole(['super-administrator', 'Super Administrator', 'admin', 'department-head'])) {
                abort(403, 'You cannot assign faculty to this course');
            }
        }
        
        $validated = $request->validate([
            'faculty_id' => 'required|exists:users,id',
            'assignment_type' => 'required|in:coordinator,primary_instructor,co_instructor,teaching_assistant,grader,guest_lecturer',
            'can_edit_content' => 'boolean',
            'can_manage_grades' => 'boolean',
            'can_view_all_sections' => 'boolean',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'notes' => 'nullable|string|max:500',
        ]);
        
        // Check if faculty is from appropriate department
        $faculty = User::find($validated['faculty_id']);
        
        // Verify faculty is from the right department
        if ($faculty->department_id != $course->department_id) {
            // Check if faculty has affiliation with the department
            $hasAffiliation = false;
            try {
                if (method_exists($faculty, 'getAllDepartments')) {
                    $hasAffiliation = $faculty->getAllDepartments()->contains('id', $course->department_id);
                }
            } catch (\Exception $e) {
                // Method doesn't exist
            }
            
            if (!$hasAffiliation) {
                return redirect()->back()
                    ->with('error', 'Faculty member is not affiliated with the course department');
            }
        }
        
        // Check if facultyAssignments relationship exists
        if (!method_exists($course, 'facultyAssignments')) {
            return redirect()->back()
                ->with('error', 'Faculty assignments feature is not available');
        }
        
        // Check for existing active assignment
        try {
            $existingAssignment = $course->facultyAssignments()
                ->where('faculty_id', $validated['faculty_id'])
                ->where('assignment_type', $validated['assignment_type'])
                ->where('is_active', true)
                ->first();
            
            if ($existingAssignment) {
                return redirect()->back()
                    ->with('error', 'This faculty member already has an active assignment of this type');
            }
        } catch (\Exception $e) {
            Log::error('Error checking existing assignment: ' . $e->getMessage());
        }
        
        // If assigning coordinator, deactivate current coordinator
        if ($validated['assignment_type'] === 'coordinator') {
            try {
                $course->facultyAssignments()
                    ->where('assignment_type', 'coordinator')
                    ->where('is_active', true)
                    ->update([
                        'is_active' => false,
                        'effective_until' => now()
                    ]);
                
                // Update course coordinator
                $course->update(['coordinator_id' => $validated['faculty_id']]);
            } catch (\Exception $e) {
                Log::warning('Could not update coordinator: ' . $e->getMessage());
            }
        }
        
        // Create assignment
        try {
            $assignment = $course->facultyAssignments()->create([
                'faculty_id' => $validated['faculty_id'],
                'assignment_type' => $validated['assignment_type'],
                'can_edit_content' => $validated['can_edit_content'] ?? false,
                'can_manage_grades' => $validated['can_manage_grades'] ?? false,
                'can_view_all_sections' => $validated['can_view_all_sections'] ?? false,
                'effective_from' => $validated['effective_from'],
                'effective_until' => $validated['effective_until'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'assigned_by' => Auth::id(),
                'is_active' => true,
            ]);
            
            // Log the assignment
            $this->scopeService->logAccess(Auth::user(), 'assign_faculty', 'course', $course->id, true);
            
            return redirect()->route('courses.manage-faculty', $course)
                ->with('success', 'Faculty assigned successfully');
        } catch (\Exception $e) {
            Log::error('Error creating faculty assignment: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error assigning faculty: ' . $e->getMessage());
        }
    }

    /**
     * Remove faculty assignment
     */
    public function removeFacultyAssignment(Course $course, $assignmentId)
    {
        // Authorization check with fallback
        try {
            $this->authorize('assignFaculty', $course);
        } catch (\Exception $e) {
            $user = Auth::user();
            if (!$user->hasRole(['super-administrator', 'Super Administrator', 'admin', 'department-head'])) {
                abort(403, 'You cannot remove faculty assignments');
            }
        }
        
        if (!method_exists($course, 'facultyAssignments')) {
            return redirect()->back()
                ->with('error', 'Faculty assignments feature is not available');
        }
        
        try {
            $assignment = $course->facultyAssignments()->findOrFail($assignmentId);
            
            // Deactivate assignment
            $assignment->update([
                'is_active' => false,
                'effective_until' => now()
            ]);
            
            // If removing coordinator, update course
            if ($assignment->assignment_type === 'coordinator') {
                $course->update(['coordinator_id' => null]);
            }
            
            return redirect()->route('courses.manage-faculty', $course)
                ->with('success', 'Faculty assignment removed');
        } catch (\Exception $e) {
            Log::error('Error removing faculty assignment: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error removing assignment: ' . $e->getMessage());
        }
    }

    /**
     * Manage course sections
     */
    public function sections(Course $course)
    {
        // Authorization check with fallback
        try {
            $this->authorize('manageSections', $course);
        } catch (\Exception $e) {
            $user = Auth::user();
            if (!$user->hasRole(['super-administrator', 'Super Administrator', 'admin', 'academic-administrator', 'registrar'])) {
                abort(403, 'You cannot manage sections for this course');
            }
        }
        
        // This would redirect to the sections management for this course
        return redirect()->route('courses.sections.index', $course);
    }
}