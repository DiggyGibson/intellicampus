<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GradeComponent;
use App\Models\Grade;
use App\Models\CourseSection;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;

class GradeApiController extends Controller
{
    protected $gradeService;

    public function __construct(GradeCalculationService $gradeService)
    {
        $this->gradeService = $gradeService;
    }

    /**
     * Get grade components for a section
     */
    public function getComponents($sectionId)
    {
        $components = GradeComponent::where('section_id', $sectionId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return response()->json($components);
    }

    /**
     * Get grades for a component
     */
    public function getComponentGrades($componentId)
    {
        $grades = Grade::where('component_id', $componentId)
            ->with('enrollment.student')
            ->get();

        return response()->json($grades);
    }

    /**
     * Calculate GPA
     */
    public function calculateGPA(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'term_id' => 'nullable|exists:academic_terms,id'
        ]);

        if ($validated['term_id']) {
            $gpa = $this->gradeService->calculateTermGPA(
                $validated['student_id'], 
                $validated['term_id']
            );
        } else {
            $gpa = $this->gradeService->calculateCumulativeGPA($validated['student_id']);
        }

        return response()->json(['gpa' => $gpa]);
    }

    /**
     * Get grade statistics for a section
     */
    public function getStatistics($sectionId)
    {
        $section = CourseSection::with(['enrollments.grades'])->find($sectionId);
        
        if (!$section) {
            return response()->json(['error' => 'Section not found'], 404);
        }

        $stats = $this->gradeService->calculateSectionStatistics($sectionId);

        return response()->json($stats);
    }

    /**
     * Validate grade entry
     */
    public function validateGrade(Request $request)
    {
        $validated = $request->validate([
            'points_earned' => 'required|numeric|min:0',
            'max_points' => 'required|numeric|min:0',
            'component_id' => 'required|exists:grade_components,id'
        ]);

        $percentage = ($validated['points_earned'] / $validated['max_points']) * 100;
        
        // Get letter grade based on scale
        $component = GradeComponent::find($validated['component_id']);
        $letterGrade = $this->gradeService->calculateLetterGrade($percentage, $component->section_id);

        return response()->json([
            'valid' => true,
            'percentage' => round($percentage, 2),
            'letter_grade' => $letterGrade
        ]);
    }
}