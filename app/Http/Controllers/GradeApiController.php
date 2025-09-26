<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\Enrollment;
use App\Models\CourseSection;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GradeApiController extends Controller
{
    protected $gradeService;

    public function __construct(GradeCalculationService $gradeService)
    {
        $this->gradeService = $gradeService;
        //$this->middleware('auth');
    }

    /**
     * Get grade components for a section
     */
    public function getComponents($sectionId)
    {
        try {
            $section = CourseSection::findOrFail($sectionId);
            
            // Check access permission
            if (!$this->hasAccess($section)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $components = GradeComponent::where('section_id', $sectionId)
                ->orderBy('type')
                ->orderBy('due_date')
                ->get()
                ->map(function ($component) {
                    return [
                        'id' => $component->id,
                        'name' => $component->name,
                        'type' => $component->type,
                        'weight' => $component->weight,
                        'max_points' => $component->max_points,
                        'due_date' => $component->due_date ? $component->due_date->format('Y-m-d') : null,
                        'is_extra_credit' => $component->is_extra_credit,
                        'completion_rate' => $component->getCompletionPercentage()
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $components
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching components', [
                'section_id' => $sectionId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to fetch components'], 500);
        }
    }

    /**
     * Get grades for a specific component
     */
    public function getComponentGrades($componentId)
    {
        try {
            $component = GradeComponent::findOrFail($componentId);
            $section = CourseSection::findOrFail($component->section_id);
            
            // Check access permission
            if (!$this->hasAccess($section)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $grades = Grade::where('component_id', $componentId)
                ->with(['enrollment.student'])
                ->get()
                ->map(function ($grade) {
                    return [
                        'enrollment_id' => $grade->enrollment_id,
                        'student_id' => $grade->enrollment->student->student_id,
                        'student_name' => $grade->enrollment->student->first_name . ' ' . 
                                        $grade->enrollment->student->last_name,
                        'points_earned' => $grade->points_earned,
                        'percentage' => $grade->percentage,
                        'letter_grade' => $grade->letter_grade,
                        'comments' => $grade->comments,
                        'submitted_at' => $grade->submitted_at ? $grade->submitted_at->format('Y-m-d H:i') : null
                    ];
                });

            return response()->json([
                'success' => true,
                'grades' => $grades,
                'component' => [
                    'name' => $component->name,
                    'max_points' => $component->max_points,
                    'weight' => $component->weight
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching component grades', [
                'component_id' => $componentId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to fetch grades'], 500);
        }
    }

    /**
     * Save or update a single grade via AJAX
     */
    public function saveGrade(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'enrollment_id' => 'required|exists:enrollments,id',
            'component_id' => 'required|exists:grade_components,id',
            'points_earned' => 'nullable|numeric|min:0',
            'comments' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $enrollment = Enrollment::findOrFail($request->enrollment_id);
            $section = CourseSection::findOrFail($enrollment->section_id);
            
            // Check access permission
            if (!$this->hasAccess($section)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $component = GradeComponent::findOrFail($request->component_id);
            
            // Validate points against max points
            if ($request->points_earned > $component->max_points) {
                return response()->json([
                    'success' => false,
                    'error' => 'Points cannot exceed maximum points for this component'
                ], 422);
            }

            DB::beginTransaction();

            // Calculate percentage
            $percentage = $component->max_points > 0 
                ? ($request->points_earned / $component->max_points) * 100 
                : 0;

            // Calculate letter grade
            $letterGrade = $this->gradeService->getLetterGrade($percentage);

            // Save or update grade
            $grade = Grade::updateOrCreate(
                [
                    'enrollment_id' => $request->enrollment_id,
                    'component_id' => $request->component_id
                ],
                [
                    'points_earned' => $request->points_earned,
                    'max_points' => $component->max_points,
                    'percentage' => $percentage,
                    'letter_grade' => $letterGrade,
                    'comments' => $request->comments,
                    'graded_by' => Auth::id(),
                    'submitted_at' => now(),
                    'grade_status' => 'draft',
                    'is_final' => false
                ]
            );

            // Recalculate overall grade
            $overallGrade = $this->gradeService->calculateGrade($enrollment->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Grade saved successfully',
                'data' => [
                    'grade_id' => $grade->id,
                    'percentage' => round($percentage, 2),
                    'letter_grade' => $letterGrade,
                    'overall_percentage' => $overallGrade['percentage'],
                    'overall_letter' => $overallGrade['letter_grade']
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error saving grade', [
                'enrollment_id' => $request->enrollment_id,
                'component_id' => $request->component_id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to save grade'], 500);
        }
    }

    /**
     * Calculate GPA for given courses
     */
    public function calculateGPA(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'grades' => 'required|array',
            'grades.*.letter_grade' => 'required|string',
            'grades.*.credits' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $totalQualityPoints = 0;
            $totalCredits = 0;
            $courses = [];

            foreach ($request->grades as $grade) {
                $gradePoints = $this->gradeService->getGradePoints($grade['letter_grade']);
                $qualityPoints = $gradePoints * $grade['credits'];
                
                $totalQualityPoints += $qualityPoints;
                $totalCredits += $grade['credits'];
                
                $courses[] = [
                    'letter_grade' => $grade['letter_grade'],
                    'credits' => $grade['credits'],
                    'grade_points' => $gradePoints,
                    'quality_points' => round($qualityPoints, 2)
                ];
            }

            $gpa = $totalCredits > 0 
                ? round($totalQualityPoints / $totalCredits, 2) 
                : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'gpa' => $gpa,
                    'total_credits' => $totalCredits,
                    'total_quality_points' => round($totalQualityPoints, 2),
                    'courses' => $courses
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error calculating GPA', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to calculate GPA'], 500);
        }
    }

    /**
     * Get grade statistics for a section
     */
    public function getStatistics($sectionId)
    {
        try {
            $section = CourseSection::findOrFail($sectionId);
            
            // Check access permission
            if (!$this->hasAccess($section)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $stats = $this->gradeService->getSectionStatistics($sectionId);

            // Get grade distribution
            $grades = Grade::whereIn('enrollment_id', function($query) use ($sectionId) {
                $query->select('id')
                    ->from('enrollments')
                    ->where('section_id', $sectionId)
                    ->where('enrollment_status', 'enrolled');
            })
            ->whereNull('component_id') // Final grades only
            ->where('is_final', true)
            ->select('letter_grade', DB::raw('COUNT(*) as count'))
            ->groupBy('letter_grade')
            ->get();

            $distribution = [];
            $gradeOrder = ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'F'];
            
            foreach ($gradeOrder as $grade) {
                $gradeData = $grades->firstWhere('letter_grade', $grade);
                $distribution[] = [
                    'grade' => $grade,
                    'count' => $gradeData ? $gradeData->count : 0
                ];
            }

            // Get component completion rates
            $components = GradeComponent::where('section_id', $sectionId)
                ->get()
                ->map(function ($component) {
                    return [
                        'name' => $component->name,
                        'type' => $component->type,
                        'weight' => $component->weight,
                        'completion_rate' => $component->getCompletionPercentage(),
                        'average_score' => $component->getAverageScore(),
                        'highest_score' => $component->getHighestScore(),
                        'lowest_score' => $component->getLowestScore()
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'overall' => $stats,
                    'distribution' => $distribution,
                    'components' => $components
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching statistics', [
                'section_id' => $sectionId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to fetch statistics'], 500);
        }
    }

    /**
     * Validate grade entry before saving
     */
    public function validateGrade(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'enrollment_id' => 'required|exists:enrollments,id',
            'component_id' => 'required|exists:grade_components,id',
            'points_earned' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'errors' => $validator->errors()
            ]);
        }

        try {
            $component = GradeComponent::find($request->component_id);
            
            // Check if points exceed maximum
            if ($request->points_earned > $component->max_points) {
                return response()->json([
                    'valid' => false,
                    'message' => "Points cannot exceed {$component->max_points}"
                ]);
            }

            // Check if grade already exists and is final
            $existingGrade = Grade::where('enrollment_id', $request->enrollment_id)
                ->where('component_id', $request->component_id)
                ->where('is_final', true)
                ->first();

            if ($existingGrade) {
                return response()->json([
                    'valid' => false,
                    'message' => 'This grade has been finalized and cannot be changed',
                    'requires_approval' => true
                ]);
            }

            // Calculate what the grade would be
            $percentage = $component->max_points > 0 
                ? ($request->points_earned / $component->max_points) * 100 
                : 0;
            $letterGrade = $this->gradeService->getLetterGrade($percentage);

            return response()->json([
                'valid' => true,
                'preview' => [
                    'percentage' => round($percentage, 2),
                    'letter_grade' => $letterGrade,
                    'status' => $percentage >= 60 ? 'passing' : 'failing'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error validating grade', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'valid' => false,
                'message' => 'Validation error occurred'
            ], 500);
        }
    }

    /**
     * Get real-time grade updates for a section (for live updates)
     */
    public function getLiveUpdates($sectionId)
    {
        try {
            $section = CourseSection::findOrFail($sectionId);
            
            // Check access permission
            if (!$this->hasAccess($section)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Get recent grade updates (last 5 minutes)
            $recentGrades = Grade::whereIn('enrollment_id', function($query) use ($sectionId) {
                $query->select('id')
                    ->from('enrollments')
                    ->where('section_id', $sectionId);
            })
            ->where('updated_at', '>', now()->subMinutes(5))
            ->with(['enrollment.student', 'component'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($grade) {
                return [
                    'student_name' => $grade->enrollment->student->first_name . ' ' . 
                                    $grade->enrollment->student->last_name,
                    'component' => $grade->component->name,
                    'grade' => $grade->letter_grade,
                    'updated_at' => $grade->updated_at->diffForHumans()
                ];
            });

            return response()->json([
                'success' => true,
                'updates' => $recentGrades,
                'timestamp' => now()->toIso8601String()
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching live updates', [
                'section_id' => $sectionId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to fetch updates'], 500);
        }
    }

    /**
     * Batch save grades (for bulk operations)
     */
    public function batchSave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'component_id' => 'required|exists:grade_components,id',
            'grades' => 'required|array',
            'grades.*.enrollment_id' => 'required|exists:enrollments,id',
            'grades.*.points_earned' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $component = GradeComponent::findOrFail($request->component_id);
            $section = CourseSection::findOrFail($component->section_id);
            
            // Check access permission
            if (!$this->hasAccess($section)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            DB::beginTransaction();
            
            $saved = 0;
            $errors = [];

            foreach ($request->grades as $gradeData) {
                try {
                    if ($gradeData['points_earned'] !== null) {
                        // Validate points
                        if ($gradeData['points_earned'] > $component->max_points) {
                            $errors[] = "Enrollment {$gradeData['enrollment_id']}: Points exceed maximum";
                            continue;
                        }

                        $percentage = $component->max_points > 0 
                            ? ($gradeData['points_earned'] / $component->max_points) * 100 
                            : 0;

                        Grade::updateOrCreate(
                            [
                                'enrollment_id' => $gradeData['enrollment_id'],
                                'component_id' => $component->id
                            ],
                            [
                                'points_earned' => $gradeData['points_earned'],
                                'max_points' => $component->max_points,
                                'percentage' => $percentage,
                                'letter_grade' => $this->gradeService->getLetterGrade($percentage),
                                'graded_by' => Auth::id(),
                                'submitted_at' => now(),
                                'grade_status' => 'draft'
                            ]
                        );
                        
                        $saved++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Enrollment {$gradeData['enrollment_id']}: " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$saved} grades saved successfully",
                'saved_count' => $saved,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in batch save', [
                'component_id' => $request->component_id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to save grades'], 500);
        }
    }

    /**
     * Check if user has access to grade this section
     */
    private function hasAccess($section)
    {
        $user = Auth::user();
        
        // Instructor of the section
        if ($section->instructor_id == $user->id) {
            return true;
        }
        
        // Admin or registrar
        if ($user->hasRole(['admin', 'registrar'])) {
            return true;
        }
        
        // Teaching assistant
        $isTA = DB::table('teaching_assistants')
            ->where('section_id', $section->id)
            ->where('user_id', $user->id)
            ->exists();
            
        return $isTA;
    }
}