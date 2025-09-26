<?php
// Save as: backend/app/Services/DegreeAudit/RequirementEvaluatorService.php

namespace App\Services\DegreeAudit;

use App\Models\Student;
use App\Models\DegreeRequirement;
use App\Models\ProgramRequirement;
use App\Models\StudentDegreeProgress;
use App\Models\Enrollment;
use App\Models\Course;
use Illuminate\Support\Collection;

class RequirementEvaluatorService
{
    /**
     * Evaluate a requirement for a student
     */
    public function evaluate(
        Student $student, 
        DegreeRequirement $requirement, 
        ProgramRequirement $programRequirement
    ): array {
        // Base evaluation structure
        $evaluation = [
            'requirement_id' => $requirement->id,
            'requirement_name' => $requirement->name,
            'requirement_type' => $requirement->requirement_type,
            'requirement_description' => $requirement->description,
            'category_name' => $requirement->category->name ?? 'Unknown',
            'is_required' => $requirement->is_required,
            'is_satisfied' => false,
            'progress_percentage' => 0,
            'credits_required' => 0,
            'credits_completed' => 0,
            'credits_in_progress' => 0,
            'credits_remaining' => 0,
            'courses_required' => 0,
            'courses_completed' => 0,
            'courses_in_progress' => 0,
            'courses_remaining' => 0
        ];

        // Check if manually cleared
        $progress = StudentDegreeProgress::where('student_id', $student->id)
            ->where('requirement_id', $requirement->id)
            ->where('program_requirement_id', $programRequirement->id)
            ->first();
            
        if ($progress && $progress->manually_cleared) {
            $evaluation['is_satisfied'] = true;
            $evaluation['progress_percentage'] = 100;
            $evaluation['manually_cleared'] = true;
            $evaluation['cleared_by'] = $progress->clearedBy->name ?? 'Administrator';
            $evaluation['cleared_notes'] = $progress->notes;
            return $evaluation;
        }

        // Evaluate based on requirement type
        switch ($requirement->requirement_type) {
            case DegreeRequirement::TYPE_CREDIT_HOURS:
            case 'credit_hours':
                $evaluation = array_merge($evaluation, $this->evaluateCreditHours($student, $requirement, $programRequirement));
                break;
                
            case DegreeRequirement::TYPE_COURSE_COUNT:
            case 'course_count':
                $evaluation = array_merge($evaluation, $this->evaluateCourseCount($student, $requirement, $programRequirement));
                break;
                
            case DegreeRequirement::TYPE_SPECIFIC_COURSES:
            case 'specific_courses':
                $evaluation = array_merge($evaluation, $this->evaluateSpecificCourses($student, $requirement, $programRequirement));
                break;
                
            case DegreeRequirement::TYPE_COURSE_LIST:
            case 'course_list':
                $evaluation = array_merge($evaluation, $this->evaluateCourseList($student, $requirement, $programRequirement));
                break;
                
            case DegreeRequirement::TYPE_GPA:
            case 'gpa':
                $evaluation = array_merge($evaluation, $this->evaluateGPA($student, $requirement, $programRequirement));
                break;
                
            case DegreeRequirement::TYPE_RESIDENCY:
            case 'residency':
                $evaluation = array_merge($evaluation, $this->evaluateResidency($student, $requirement, $programRequirement));
                break;
                
            case DegreeRequirement::TYPE_MILESTONE:
            case 'milestone':
                $evaluation = array_merge($evaluation, $this->evaluateMilestone($student, $requirement, $programRequirement));
                break;
                
            default:
                // Unknown requirement type
                $evaluation['is_satisfied'] = false;
                $evaluation['progress_percentage'] = 0;
        }

        return $evaluation;
    }

    /**
     * Evaluate credit hour requirement
     */
    protected function evaluateCreditHours(
        Student $student, 
        DegreeRequirement $requirement, 
        ProgramRequirement $programRequirement
    ): array {
        $params = $programRequirement->getEffectiveParameters();
        $minCredits = $params['min_credits'] ?? 0;
        
        // For now, use student's total credits (should be filtered by requirement area)
        $completedCredits = $student->credits_earned ?? 0;
        $inProgressCredits = 0; // Would calculate from current enrollments
        
        $remainingCredits = max(0, $minCredits - $completedCredits);
        $percentage = $minCredits > 0 ? min(100, ($completedCredits / $minCredits) * 100) : 0;
        
        return [
            'credits_required' => $minCredits,
            'credits_completed' => $completedCredits,
            'credits_in_progress' => $inProgressCredits,
            'credits_remaining' => $remainingCredits,
            'is_satisfied' => $completedCredits >= $minCredits,
            'progress_percentage' => $percentage
        ];
    }

    /**
     * Evaluate course count requirement
     */
    protected function evaluateCourseCount(
        Student $student, 
        DegreeRequirement $requirement, 
        ProgramRequirement $programRequirement
    ): array {
        $params = $programRequirement->getEffectiveParameters();
        $minCourses = $params['min_courses'] ?? 0;
        
        // Count completed courses (simplified - should filter by requirement area)
        $completedCourses = Enrollment::where('student_id', $student->id)
            ->where('enrollment_status', 'completed')
            ->count();
            
        $remainingCourses = max(0, $minCourses - $completedCourses);
        $percentage = $minCourses > 0 ? min(100, ($completedCourses / $minCourses) * 100) : 0;
        
        return [
            'courses_required' => $minCourses,
            'courses_completed' => $completedCourses,
            'courses_in_progress' => 0,
            'courses_remaining' => $remainingCourses,
            'is_satisfied' => $completedCourses >= $minCourses,
            'progress_percentage' => $percentage
        ];
    }

    /**
     * Evaluate specific courses requirement
     * FIXED: Using 'code' column instead of 'course_code'
     */
    protected function evaluateSpecificCourses(
        Student $student, 
        DegreeRequirement $requirement, 
        ProgramRequirement $programRequirement
    ): array {
        $params = $programRequirement->getEffectiveParameters();
        $requiredCourses = $params['required_courses'] ?? [];
        $minGrade = $params['min_grade'] ?? 'D';
        
        if (empty($requiredCourses)) {
            return [
                'is_satisfied' => true,
                'progress_percentage' => 100,
                'courses_required' => 0,
                'courses_completed' => 0
            ];
        }
        
        $completedCourses = [];
        $inProgressCourses = [];
        $remainingCourses = [];
        
        foreach ($requiredCourses as $courseCode) {
            // FIXED: Using 'code' column instead of 'course_code'
            $enrollment = Enrollment::where('student_id', $student->id)
                ->whereHas('section.course', function ($query) use ($courseCode) {
                    $query->where('code', $courseCode); // Changed from 'course_code' to 'code'
                })
                ->first();
                
            if ($enrollment) {
                if ($enrollment->enrollment_status === 'completed' && 
                    $this->gradeMetRequirement($enrollment->final_grade, $minGrade)) {
                    $completedCourses[] = $courseCode;
                } elseif (in_array($enrollment->enrollment_status, ['enrolled', 'in_progress'])) {
                    $inProgressCourses[] = $courseCode;
                } else {
                    $remainingCourses[] = $courseCode;
                }
            } else {
                $remainingCourses[] = $courseCode;
            }
        }
        
        $totalRequired = count($requiredCourses);
        $totalCompleted = count($completedCourses);
        $percentage = $totalRequired > 0 ? ($totalCompleted / $totalRequired) * 100 : 0;
        
        return [
            'courses_required' => $totalRequired,
            'courses_completed' => $totalCompleted,
            'courses_in_progress' => count($inProgressCourses),
            'courses_remaining' => count($remainingCourses),
            'required_courses' => $requiredCourses,
            'completed_courses' => $completedCourses,
            'in_progress_courses' => $inProgressCourses,
            'remaining_courses' => $remainingCourses,
            'is_satisfied' => count($remainingCourses) === 0,
            'progress_percentage' => $percentage
        ];
    }

    /**
     * Evaluate course list requirement (choose X from list)
     * FIXED: Using 'code' column instead of 'course_code'
     */
    protected function evaluateCourseList(
        Student $student, 
        DegreeRequirement $requirement, 
        ProgramRequirement $programRequirement
    ): array {
        $params = $programRequirement->getEffectiveParameters();
        $courseOptions = $params['choose_from'] ?? [];
        $minToChoose = $params['min_to_choose'] ?? 1;
        $minGrade = $params['min_grade'] ?? 'D';
        
        if (empty($courseOptions)) {
            return [
                'is_satisfied' => true,
                'progress_percentage' => 100,
                'courses_required' => 0,
                'courses_completed' => 0
            ];
        }
        
        $completedCourses = [];
        $inProgressCourses = [];
        
        foreach ($courseOptions as $courseCode) {
            // FIXED: Using 'code' column instead of 'course_code'
            $enrollment = Enrollment::where('student_id', $student->id)
                ->whereHas('section.course', function ($query) use ($courseCode) {
                    $query->where('code', $courseCode); // Changed from 'course_code' to 'code'
                })
                ->first();
                
            if ($enrollment) {
                if ($enrollment->enrollment_status === 'completed' && 
                    $this->gradeMetRequirement($enrollment->final_grade, $minGrade)) {
                    $completedCourses[] = $courseCode;
                } elseif (in_array($enrollment->enrollment_status, ['enrolled', 'in_progress'])) {
                    $inProgressCourses[] = $courseCode;
                }
            }
        }
        
        $totalCompleted = count($completedCourses);
        $remainingNeeded = max(0, $minToChoose - $totalCompleted);
        $percentage = $minToChoose > 0 ? min(100, ($totalCompleted / $minToChoose) * 100) : 0;
        
        return [
            'courses_required' => $minToChoose,
            'courses_completed' => $totalCompleted,
            'courses_in_progress' => count($inProgressCourses),
            'courses_remaining' => $remainingNeeded,
            'course_options' => $courseOptions,
            'completed_courses' => $completedCourses,
            'in_progress_courses' => $inProgressCourses,
            'is_satisfied' => $totalCompleted >= $minToChoose,
            'progress_percentage' => $percentage
        ];
    }

    /**
     * Evaluate GPA requirement
     */
    protected function evaluateGPA(
        Student $student, 
        DegreeRequirement $requirement, 
        ProgramRequirement $programRequirement
    ): array {
        $params = $programRequirement->getEffectiveParameters();
        $minGPA = $params['min_gpa'] ?? 2.0;
        $gpaType = $params['apply_to'] ?? 'cumulative';
        
        $currentGPA = 0;
        switch ($gpaType) {
            case 'major':
                $currentGPA = $student->major_gpa ?? 0;
                break;
            case 'minor':
                $currentGPA = $student->minor_gpa ?? 0;
                break;
            default:
                $currentGPA = $student->cumulative_gpa ?? 0;
        }
        
        $isSatisfied = $currentGPA >= $minGPA;
        $percentage = $isSatisfied ? 100 : ($currentGPA / $minGPA) * 100;
        
        return [
            'required_gpa' => $minGPA,
            'current_gpa' => $currentGPA,
            'gpa_type' => $gpaType,
            'is_satisfied' => $isSatisfied,
            'progress_percentage' => $percentage
        ];
    }

    /**
     * Evaluate residency requirement
     */
    protected function evaluateResidency(
        Student $student, 
        DegreeRequirement $requirement, 
        ProgramRequirement $programRequirement
    ): array {
        $params = $programRequirement->getEffectiveParameters();
        $minCredits = $params['min_credits'] ?? 30;
        $ofLastCredits = $params['of_last_credits'] ?? 60;
        
        // For now, use total credits earned (should check transfer vs institutional)
        $institutionalCredits = $student->credits_earned ?? 0;
        
        $isSatisfied = $institutionalCredits >= $minCredits;
        $percentage = $minCredits > 0 ? min(100, ($institutionalCredits / $minCredits) * 100) : 0;
        
        return [
            'residency_credits_required' => $minCredits,
            'residency_credits_earned' => $institutionalCredits,
            'of_last_credits' => $ofLastCredits,
            'is_satisfied' => $isSatisfied,
            'progress_percentage' => $percentage
        ];
    }

    /**
     * Evaluate milestone requirement
     */
    protected function evaluateMilestone(
        Student $student, 
        DegreeRequirement $requirement, 
        ProgramRequirement $programRequirement
    ): array {
        $params = $programRequirement->getEffectiveParameters();
        $milestoneName = $params['milestone_name'] ?? 'Unknown Milestone';
        
        // This would check for specific milestones like thesis, comprehensive exam, etc.
        // For now, return not satisfied
        return [
            'milestone_name' => $milestoneName,
            'is_satisfied' => false,
            'progress_percentage' => 0
        ];
    }

    /**
     * Check if a grade meets the minimum requirement
     */
    protected function gradeMetRequirement(?string $grade, string $minGrade): bool
    {
        if (!$grade) {
            return false;
        }
        
        $gradeValues = [
            'A' => 12, 'A-' => 11,
            'B+' => 10, 'B' => 9, 'B-' => 8,
            'C+' => 7, 'C' => 6, 'C-' => 5,
            'D+' => 4, 'D' => 3, 'D-' => 2,
            'F' => 1
        ];
        
        $gradeValue = $gradeValues[$grade] ?? 0;
        $minGradeValue = $gradeValues[$minGrade] ?? 0;
        
        return $gradeValue >= $minGradeValue;
    }
}