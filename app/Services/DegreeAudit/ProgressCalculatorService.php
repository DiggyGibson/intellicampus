<?php
// Save as: backend/app/Services/DegreeAudit/ProgressCalculatorService.php

namespace App\Services\DegreeAudit;

use App\Models\Student;
use App\Models\StudentDegreeProgress;
use App\Models\DegreeRequirement;
use App\Models\RequirementCategory;
use App\Models\Enrollment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProgressCalculatorService
{
    /**
     * Calculate overall degree progress for a student
     */
    public function calculateOverallProgress(Student $student): array
    {
        $progress = StudentDegreeProgress::where('student_id', $student->id)
            ->with(['requirement.category', 'programRequirement'])
            ->get();
        
        if ($progress->isEmpty()) {
            return $this->getEmptyProgress();
        }

        $totalRequired = 0;
        $totalCompleted = 0;
        $totalInProgress = 0;
        $totalRemaining = 0;
        
        // Group by categories for detailed breakdown
        $categoryBreakdown = [];
        
        foreach ($progress as $item) {
            // Calculate totals
            $required = $item->credits_completed + $item->credits_remaining;
            $totalRequired += $required;
            $totalCompleted += $item->credits_completed;
            $totalInProgress += $item->credits_in_progress;
            $totalRemaining += $item->credits_remaining;
            
            // Build category breakdown
            $categoryName = $item->requirement->category->name ?? 'Other';
            if (!isset($categoryBreakdown[$categoryName])) {
                $categoryBreakdown[$categoryName] = [
                    'category_id' => $item->requirement->category_id,
                    'category_name' => $categoryName,
                    'requirements' => [],
                    'total_required' => 0,
                    'total_completed' => 0,
                    'total_in_progress' => 0,
                    'total_remaining' => 0,
                    'completion_percentage' => 0,
                    'is_satisfied' => true
                ];
            }
            
            $categoryBreakdown[$categoryName]['requirements'][] = [
                'requirement_id' => $item->requirement_id,
                'requirement_name' => $item->requirement->name,
                'is_satisfied' => $item->is_satisfied,
                'completion_percentage' => $item->completion_percentage,
                'credits_completed' => $item->credits_completed,
                'credits_remaining' => $item->credits_remaining
            ];
            
            $categoryBreakdown[$categoryName]['total_required'] += $required;
            $categoryBreakdown[$categoryName]['total_completed'] += $item->credits_completed;
            $categoryBreakdown[$categoryName]['total_in_progress'] += $item->credits_in_progress;
            $categoryBreakdown[$categoryName]['total_remaining'] += $item->credits_remaining;
            
            if (!$item->is_satisfied && $item->requirement->is_required) {
                $categoryBreakdown[$categoryName]['is_satisfied'] = false;
            }
        }
        
        // Calculate percentages for each category
        foreach ($categoryBreakdown as &$category) {
            if ($category['total_required'] > 0) {
                $category['completion_percentage'] = round(
                    ($category['total_completed'] / $category['total_required']) * 100, 
                    2
                );
            }
        }
        
        // Calculate overall percentage
        $overallPercentage = $totalRequired > 0 
            ? round(($totalCompleted / $totalRequired) * 100, 2) 
            : 0;
        
        return [
            'overall_percentage' => $overallPercentage,
            'credits_required' => $totalRequired,
            'credits_completed' => $totalCompleted,
            'credits_in_progress' => $totalInProgress,
            'credits_remaining' => $totalRemaining,
            'requirements_satisfied' => $progress->where('is_satisfied', true)->count(),
            'total_requirements' => $progress->count(),
            'category_breakdown' => array_values($categoryBreakdown),
            'estimated_completion' => $this->estimateCompletion($student, $totalRemaining)
        ];
    }

    /**
     * Calculate progress for a specific category
     */
    public function calculateCategoryProgress(Student $student, int $categoryId): array
    {
        $progress = StudentDegreeProgress::where('student_id', $student->id)
            ->whereHas('requirement', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->with('requirement')
            ->get();
        
        if ($progress->isEmpty()) {
            return $this->getEmptyProgress();
        }

        $category = RequirementCategory::find($categoryId);
        
        return $this->aggregateProgress($progress, $category);
    }

    /**
     * Calculate progress by requirement type
     */
    public function calculateProgressByType(Student $student, string $requirementType): array
    {
        $progress = StudentDegreeProgress::where('student_id', $student->id)
            ->whereHas('requirement', function ($query) use ($requirementType) {
                $query->where('requirement_type', $requirementType);
            })
            ->with('requirement')
            ->get();
        
        return $this->aggregateProgress($progress);
    }

    /**
     * Calculate detailed requirement progress
     */
    public function calculateRequirementProgress(Student $student, int $requirementId): array
    {
        $progress = StudentDegreeProgress::where('student_id', $student->id)
            ->where('requirement_id', $requirementId)
            ->with(['requirement', 'programRequirement'])
            ->first();
        
        if (!$progress) {
            return [
                'found' => false,
                'message' => 'No progress record found for this requirement'
            ];
        }

        $requirement = $progress->requirement;
        
        // Get courses that apply to this requirement
        $appliedCourses = $this->getAppliedCourses($student, $requirement);
        
        return [
            'requirement_id' => $requirement->id,
            'requirement_name' => $requirement->name,
            'requirement_type' => $requirement->requirement_type,
            'category' => $requirement->category->name,
            'status' => $progress->status,
            'is_satisfied' => $progress->is_satisfied,
            'completion_percentage' => $progress->completion_percentage,
            'credits_completed' => $progress->credits_completed,
            'credits_in_progress' => $progress->credits_in_progress,
            'credits_remaining' => $progress->credits_remaining,
            'courses_completed' => $progress->courses_completed,
            'courses_in_progress' => $progress->courses_in_progress,
            'courses_remaining' => $progress->courses_remaining,
            'applied_courses' => $appliedCourses,
            'manually_cleared' => $progress->manually_cleared,
            'cleared_by' => $progress->cleared_by ? $progress->clearedBy->name : null,
            'cleared_at' => $progress->cleared_at,
            'notes' => $progress->notes,
            'last_calculated' => $progress->last_calculated_at
        ];
    }

    /**
     * Get milestone progress
     */
    public function getMilestoneProgress(Student $student): array
    {
        $milestones = [
            'orientation' => $this->checkOrientation($student),
            'english_proficiency' => $this->checkEnglishProficiency($student),
            'math_placement' => $this->checkMathPlacement($student),
            'writing_requirement' => $this->checkWritingRequirement($student),
            'internship' => $this->checkInternship($student),
            'thesis' => $this->checkThesis($student),
            'comprehensive_exam' => $this->checkComprehensiveExam($student),
            'exit_interview' => $this->checkExitInterview($student)
        ];

        $completed = array_filter($milestones, fn($m) => $m['completed']);
        $percentage = count($milestones) > 0 
            ? round((count($completed) / count($milestones)) * 100, 2)
            : 0;

        return [
            'milestones' => $milestones,
            'completed_count' => count($completed),
            'total_count' => count($milestones),
            'completion_percentage' => $percentage
        ];
    }

    /**
     * Calculate progress toward graduation
     */
    public function calculateGraduationProgress(Student $student): array
    {
        $overallProgress = $this->calculateOverallProgress($student);
        $gpaRequirement = $this->checkGPARequirement($student);
        $residencyRequirement = $this->checkResidencyRequirement($student);
        $milestones = $this->getMilestoneProgress($student);
        
        // Check all requirements
        $requirements = [
            'credits' => $overallProgress['credits_remaining'] == 0,
            'gpa' => $gpaRequirement['met'],
            'residency' => $residencyRequirement['met'],
            'milestones' => $milestones['completion_percentage'] == 100
        ];
        
        $allMet = !in_array(false, $requirements, true);
        
        return [
            'eligible_for_graduation' => $allMet,
            'overall_progress' => $overallProgress['overall_percentage'],
            'requirements_met' => $requirements,
            'details' => [
                'credits' => $overallProgress,
                'gpa' => $gpaRequirement,
                'residency' => $residencyRequirement,
                'milestones' => $milestones
            ],
            'estimated_graduation' => $this->estimateGraduationDate($student, $overallProgress)
        ];
    }

    /**
     * Aggregate progress records
     */
    private function aggregateProgress(Collection $progressRecords, ?RequirementCategory $category = null): array
    {
        $result = [
            'category' => $category ? $category->name : null,
            'category_type' => $category ? $category->type : null,
            'total_requirements' => $progressRecords->count(),
            'satisfied_requirements' => 0,
            'total_credits_required' => 0,
            'total_credits_completed' => 0,
            'total_credits_in_progress' => 0,
            'total_credits_remaining' => 0,
            'percentage' => 0,
            'requirements' => []
        ];

        foreach ($progressRecords as $record) {
            if ($record->is_satisfied) {
                $result['satisfied_requirements']++;
            }
            
            $required = $record->credits_completed + $record->credits_remaining;
            $result['total_credits_required'] += $required;
            $result['total_credits_completed'] += $record->credits_completed;
            $result['total_credits_in_progress'] += $record->credits_in_progress;
            $result['total_credits_remaining'] += $record->credits_remaining;
            
            $result['requirements'][] = [
                'id' => $record->requirement_id,
                'name' => $record->requirement->name,
                'type' => $record->requirement->requirement_type,
                'is_satisfied' => $record->is_satisfied,
                'percentage' => $record->completion_percentage,
                'status' => $record->status
            ];
        }

        if ($result['total_credits_required'] > 0) {
            $result['percentage'] = round(
                ($result['total_credits_completed'] / $result['total_credits_required']) * 100, 
                2
            );
        }

        return $result;
    }

    /**
     * Get courses applied to a requirement
     */
    private function getAppliedCourses(Student $student, DegreeRequirement $requirement): array
    {
        return DB::table('student_course_applications')
            ->join('courses', 'student_course_applications.course_id', '=', 'courses.id')
            ->where('student_course_applications.student_id', $student->id)
            ->where('student_course_applications.requirement_id', $requirement->id)
            ->select(
                'courses.course_code',
                'courses.title',
                'student_course_applications.credits_applied',
                'student_course_applications.grade',
                'student_course_applications.status'
            )
            ->get()
            ->toArray();
    }

    /**
     * Estimate completion date
     */
    private function estimateCompletion(Student $student, float $creditsRemaining): ?string
    {
        if ($creditsRemaining <= 0) {
            return 'Requirements Complete';
        }

        // Average credits per term (default to 15)
        $avgCreditsPerTerm = 15;
        
        // Calculate terms needed
        $termsNeeded = ceil($creditsRemaining / $avgCreditsPerTerm);
        
        // Estimate completion date (assuming 4 months per term)
        $estimatedDate = now()->addMonths($termsNeeded * 4);
        
        return $estimatedDate->format('F Y');
    }

    /**
     * Estimate graduation date
     */
    private function estimateGraduationDate(Student $student, array $overallProgress): ?string
    {
        return $this->estimateCompletion($student, $overallProgress['credits_remaining']);
    }

    /**
     * Check GPA requirement
     */
    private function checkGPARequirement(Student $student): array
    {
        $requiredGPA = 2.0; // This should come from program requirements
        $currentGPA = $student->cumulative_gpa ?? 0;
        
        return [
            'met' => $currentGPA >= $requiredGPA,
            'required' => $requiredGPA,
            'current' => $currentGPA,
            'difference' => round($requiredGPA - $currentGPA, 2)
        ];
    }

    /**
     * Check residency requirement
     */
    private function checkResidencyRequirement(Student $student): array
    {
        $requiredCredits = 30; // Usually 30 credits must be from this institution
        
        $residencyCredits = $student->enrollments()
            ->where('enrollment_status', 'completed')
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->sum('courses.credits');
        
        return [
            'met' => $residencyCredits >= $requiredCredits,
            'required' => $requiredCredits,
            'completed' => $residencyCredits,
            'remaining' => max(0, $requiredCredits - $residencyCredits)
        ];
    }

    /**
     * Milestone check methods (placeholders - implement based on your requirements)
     */
    private function checkOrientation(Student $student): array
    {
        return ['completed' => true, 'date' => $student->created_at->format('Y-m-d')];
    }

    private function checkEnglishProficiency(Student $student): array
    {
        return ['completed' => true, 'score' => 'Pass'];
    }

    private function checkMathPlacement(Student $student): array
    {
        return ['completed' => true, 'level' => 'College Algebra'];
    }

    private function checkWritingRequirement(Student $student): array
    {
        return ['completed' => false, 'status' => 'Pending'];
    }

    private function checkInternship(Student $student): array
    {
        return ['completed' => false, 'status' => 'Not Required'];
    }

    private function checkThesis(Student $student): array
    {
        return ['completed' => false, 'status' => 'Not Required'];
    }

    private function checkComprehensiveExam(Student $student): array
    {
        return ['completed' => false, 'status' => 'Not Required'];
    }

    private function checkExitInterview(Student $student): array
    {
        return ['completed' => false, 'status' => 'Pending'];
    }

    /**
     * Get empty progress structure
     */
    private function getEmptyProgress(): array
    {
        return [
            'overall_percentage' => 0,
            'credits_required' => 0,
            'credits_completed' => 0,
            'credits_in_progress' => 0,
            'credits_remaining' => 0,
            'requirements_satisfied' => 0,
            'total_requirements' => 0,
            'category_breakdown' => [],
            'estimated_completion' => null
        ];
    }
}