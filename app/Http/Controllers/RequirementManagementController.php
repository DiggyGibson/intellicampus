<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RequirementCategory;
use App\Models\DegreeRequirement;
use App\Models\ProgramRequirement;
use App\Models\CourseRequirementMapping;
use App\Models\RequirementSubstitution;
use App\Models\Course;
use App\Models\AcademicProgram;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class RequirementManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:registrar,admin,academic-administrator');
    }

    /**
     * Display requirement management dashboard
     */
    public function index(): View
    {
        $categories = RequirementCategory::withCount('requirements')
            ->orderBy('display_order')
            ->get();
            
        $requirements = DegreeRequirement::with('category')
            ->orderBy('category_id')
            ->orderBy('display_order')
            ->paginate(20);
            
        $programs = AcademicProgram::where('is_active', true)->get();
        
        return view('requirements.index', compact('categories', 'requirements', 'programs'));
    }

    /**
     * Store new requirement category
     */
    public function storeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:requirement_categories',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:university,general_education,major,minor,concentration,elective,other',
            'display_order' => 'nullable|integer|min:0'
        ]);

        try {
            $category = RequirementCategory::create($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ]);

        } catch (\Exception $e) {
            Log::error('Category creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create category'
            ], 500);
        }
    }

    /**
     * Update requirement category
     */
    public function updateCategory(Request $request, $id): JsonResponse
    {
        $category = RequirementCategory::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean'
        ]);

        try {
            $category->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => $category
            ]);

        } catch (\Exception $e) {
            Log::error('Category update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update category'
            ], 500);
        }
    }

    /**
     * Delete requirement category
     */
    public function deleteCategory($id): JsonResponse
    {
        $category = RequirementCategory::findOrFail($id);
        
        try {
            // Check if category has requirements
            if ($category->requirements()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete category with existing requirements'
                ], 400);
            }
            
            $category->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Category deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete category'
            ], 500);
        }
    }

    /**
     * Store new degree requirement
     */
    public function storeRequirement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:requirement_categories,id',
            'code' => 'required|string|max:50|unique:degree_requirements',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requirement_type' => 'required|in:credit_hours,course_count,specific_courses,course_list,gpa,residency,milestone,other',
            'parameters' => 'nullable|array',
            'display_order' => 'nullable|integer|min:0',
            'is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after:effective_from'
        ]);

        try {
            DB::beginTransaction();
            
            $requirement = DegreeRequirement::create($validated);
            
            // If specific courses are defined, create mappings
            if ($validated['requirement_type'] === 'specific_courses' && 
                isset($validated['parameters']['required_courses'])) {
                
                foreach ($validated['parameters']['required_courses'] as $courseCode) {
                    $course = Course::where('course_code', $courseCode)->first();
                    if ($course) {
                        CourseRequirementMapping::create([
                            'course_id' => $course->id,
                            'requirement_id' => $requirement->id,
                            'fulfillment_type' => 'full',
                            'is_active' => true
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Requirement created successfully',
                'data' => $requirement->load('category')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Requirement creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to create requirement'
            ], 500);
        }
    }

    /**
     * Update degree requirement
     */
    public function updateRequirement(Request $request, $id): JsonResponse
    {
        $requirement = DegreeRequirement::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parameters' => 'nullable|array',
            'display_order' => 'nullable|integer|min:0',
            'is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'effective_until' => 'nullable|date'
        ]);

        try {
            $requirement->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Requirement updated successfully',
                'data' => $requirement
            ]);

        } catch (\Exception $e) {
            Log::error('Requirement update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update requirement'
            ], 500);
        }
    }

    /**
     * Delete degree requirement
     */
    public function deleteRequirement($id): JsonResponse
    {
        $requirement = DegreeRequirement::findOrFail($id);
        
        try {
            // Check if requirement is assigned to programs
            if ($requirement->programRequirements()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete requirement assigned to programs'
                ], 400);
            }
            
            // Delete course mappings
            $requirement->courses()->detach();
            
            $requirement->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Requirement deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Requirement deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete requirement'
            ], 500);
        }
    }

    /**
     * Assign requirement to program
     */
    public function assignToProgram(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:academic_programs,id',
            'requirement_id' => 'required|exists:degree_requirements,id',
            'catalog_year' => 'required|string|max:10',
            'credits_required' => 'nullable|numeric|min:0',
            'courses_required' => 'nullable|integer|min:0',
            'applies_to' => 'nullable|in:all,major_only,minor_only,concentration',
            'concentration_code' => 'nullable|string|max:50',
            'program_parameters' => 'nullable|array'
        ]);

        try {
            // Check if already assigned
            $existing = ProgramRequirement::where('program_id', $validated['program_id'])
                ->where('requirement_id', $validated['requirement_id'])
                ->where('catalog_year', $validated['catalog_year'])
                ->first();
                
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'error' => 'Requirement already assigned to this program for this catalog year'
                ], 400);
            }
            
            $programRequirement = ProgramRequirement::create($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Requirement assigned to program successfully',
                'data' => $programRequirement->load(['program', 'requirement'])
            ]);

        } catch (\Exception $e) {
            Log::error('Program assignment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to assign requirement to program'
            ], 500);
        }
    }

    /**
     * Update program requirement
     */
    public function updateProgramRequirement(Request $request, $id): JsonResponse
    {
        $programRequirement = ProgramRequirement::findOrFail($id);
        
        $validated = $request->validate([
            'credits_required' => 'nullable|numeric|min:0',
            'courses_required' => 'nullable|integer|min:0',
            'program_parameters' => 'nullable|array',
            'is_active' => 'nullable|boolean'
        ]);

        try {
            $programRequirement->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Program requirement updated successfully',
                'data' => $programRequirement
            ]);

        } catch (\Exception $e) {
            Log::error('Program requirement update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update program requirement'
            ], 500);
        }
    }

    /**
     * Remove requirement from program
     */
    public function removeProgramRequirement($id): JsonResponse
    {
        $programRequirement = ProgramRequirement::findOrFail($id);
        
        try {
            $programRequirement->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Requirement removed from program successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Program requirement removal error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to remove requirement from program'
            ], 500);
        }
    }

    /**
     * Map course to requirement
     */
    public function mapCourse(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'requirement_id' => 'required|exists:degree_requirements,id',
            'fulfillment_type' => 'required|in:full,partial,elective,choice',
            'credit_value' => 'nullable|numeric|min:0',
            'min_grade' => 'nullable|string|max:5',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after:effective_from'
        ]);

        try {
            // Check if mapping already exists
            $existing = CourseRequirementMapping::where('course_id', $validated['course_id'])
                ->where('requirement_id', $validated['requirement_id'])
                ->first();
                
            if ($existing) {
                // Update existing mapping
                $existing->update($validated);
                $mapping = $existing;
            } else {
                // Create new mapping
                $mapping = CourseRequirementMapping::create($validated);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Course mapped to requirement successfully',
                'data' => $mapping->load(['course', 'requirement'])
            ]);

        } catch (\Exception $e) {
            Log::error('Course mapping error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to map course to requirement'
            ], 500);
        }
    }

    /**
     * Remove course mapping
     */
    public function unmapCourse($id): JsonResponse
    {
        $mapping = CourseRequirementMapping::findOrFail($id);
        
        try {
            $mapping->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Course mapping removed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Course unmapping error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to remove course mapping'
            ], 500);
        }
    }

    /**
     * Request course substitution
     */
    public function requestSubstitution(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'requirement_id' => 'required|exists:degree_requirements,id',
            'original_course_id' => 'nullable|exists:courses,id',
            'substitute_course_id' => 'required|exists:courses,id',
            'reason' => 'required|string|max:500'
        ]);

        try {
            // Check if substitution already exists
            $existing = RequirementSubstitution::where('student_id', $validated['student_id'])
                ->where('requirement_id', $validated['requirement_id'])
                ->where('substitute_course_id', $validated['substitute_course_id'])
                ->whereIn('status', ['pending', 'approved'])
                ->first();
                
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'error' => 'A substitution request already exists for this course'
                ], 400);
            }
            
            $substitution = RequirementSubstitution::create([
                'student_id' => $validated['student_id'],
                'requirement_id' => $validated['requirement_id'],
                'original_course_id' => $validated['original_course_id'],
                'substitute_course_id' => $validated['substitute_course_id'],
                'reason' => $validated['reason'],
                'requested_by' => Auth::id(),
                'status' => 'pending'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Substitution request submitted successfully',
                'data' => $substitution->load(['student', 'requirement', 'originalCourse', 'substituteCourse'])
            ]);

        } catch (\Exception $e) {
            Log::error('Substitution request error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to submit substitution request'
            ], 500);
        }
    }

    /**
     * Approve substitution request
     */
    public function approveSubstitution(Request $request, $id): JsonResponse
    {
        $substitution = RequirementSubstitution::findOrFail($id);
        
        if ($substitution->status !== 'pending') {
            return response()->json([
                'success' => false,
                'error' => 'Only pending substitutions can be approved'
            ], 400);
        }
        
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $substitution->approve(Auth::user(), $validated['notes'] ?? null);
            
            // TODO: Update student's degree progress to reflect substitution
            
            return response()->json([
                'success' => true,
                'message' => 'Substitution approved successfully',
                'data' => $substitution
            ]);

        } catch (\Exception $e) {
            Log::error('Substitution approval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to approve substitution'
            ], 500);
        }
    }

    /**
     * Deny substitution request
     */
    public function denySubstitution(Request $request, $id): JsonResponse
    {
        $substitution = RequirementSubstitution::findOrFail($id);
        
        if ($substitution->status !== 'pending') {
            return response()->json([
                'success' => false,
                'error' => 'Only pending substitutions can be denied'
            ], 400);
        }
        
        $validated = $request->validate([
            'notes' => 'required|string|max:500'
        ]);

        try {
            $substitution->deny(Auth::user(), $validated['notes']);
            
            return response()->json([
                'success' => true,
                'message' => 'Substitution denied',
                'data' => $substitution
            ]);

        } catch (\Exception $e) {
            Log::error('Substitution denial error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to deny substitution'
            ], 500);
        }
    }
}