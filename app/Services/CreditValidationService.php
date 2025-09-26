<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class CreditValidationService
{
    // Standard credit limits
    const MIN_FULL_TIME_CREDITS = 12;
    const MAX_STANDARD_CREDITS = 18;
    const MAX_OVERLOAD_CREDITS = 21;
    const MIN_PART_TIME_CREDITS = 1;
    
    /**
     * Validate credit limits for registration
     *
     * @param int $studentId
     * @param Collection $newSections
     * @param int|null $termId
     * @return array Validation result with details
     */
    public function validateCreditLimits(int $studentId, Collection $newSections, ?int $termId = null): array
    {
        // Get current term if not provided
        if (!$termId) {
            $currentTerm = DB::table('academic_terms')->where('is_current', true)->first();
            $termId = $currentTerm ? $currentTerm->id : null;
        }
        
        if (!$termId) {
            return [
                'valid' => false,
                'message' => 'No active academic term found',
                'details' => []
            ];
        }
        
        // Get student information
        $student = $this->getStudentInfo($studentId);
        
        // Calculate current enrolled credits
        $currentCredits = $this->getCurrentCredits($studentId, $termId);
        
        // Calculate new section credits
        $newCredits = $this->calculateNewCredits($newSections);
        
        // Total credits after registration
        $totalCredits = $currentCredits + $newCredits;
        
        // Get student's credit limits based on their status
        $limits = $this->getStudentCreditLimits($student);
        
        // Check for overload permission
        $hasOverloadPermission = $this->hasOverloadPermission($studentId, $termId);
        
        // Validate against limits
        $validation = [
            'valid' => true,
            'current_credits' => $currentCredits,
            'new_credits' => $newCredits,
            'total_credits' => $totalCredits,
            'min_credits' => $limits['min'],
            'max_credits' => $limits['max'],
            'student_status' => $student->enrollment_status ?? 'full_time',
            'academic_level' => $student->academic_level ?? 'undergraduate',
            'has_overload_permission' => $hasOverloadPermission,
            'issues' => [],
            'warnings' => []
        ];
        
        // Check maximum credits
        if ($totalCredits > $limits['max']) {
            if ($hasOverloadPermission && $totalCredits <= self::MAX_OVERLOAD_CREDITS) {
                $validation['warnings'][] = [
                    'type' => 'overload_approved',
                    'message' => "You are registering for {$totalCredits} credits with approved overload permission"
                ];
            } else {
                $validation['valid'] = false;
                $validation['issues'][] = [
                    'type' => 'exceeds_maximum',
                    'message' => "Total credits ({$totalCredits}) exceeds maximum allowed ({$limits['max']})",
                    'suggestion' => $hasOverloadPermission ? 
                        "Even with overload permission, you cannot exceed " . self::MAX_OVERLOAD_CREDITS . " credits" :
                        "You need overload permission to register for more than {$limits['max']} credits"
                ];
            }
        }
        
        // Check minimum credits for full-time students
        if ($student->enrollment_status === 'full_time' && $totalCredits < $limits['min'] && $totalCredits > 0) {
            $validation['warnings'][] = [
                'type' => 'below_minimum',
                'message' => "Total credits ({$totalCredits}) is below full-time minimum ({$limits['min']})",
                'suggestion' => "You need at least {$limits['min']} credits to maintain full-time status"
            ];
        }
        
        // Check for repeated courses
        $repeatedCourses = $this->checkRepeatedCourses($studentId, $newSections);
        if (!empty($repeatedCourses)) {
            foreach ($repeatedCourses as $course) {
                $validation['warnings'][] = [
                    'type' => 'repeated_course',
                    'course' => $course['code'],
                    'message' => "You are repeating {$course['code']} - {$course['title']}",
                    'previous_grade' => $course['previous_grade'],
                    'note' => $this->getRepeatPolicy($course['previous_grade'])
                ];
            }
        }
        
        // Check GPA-based credit restrictions
        $gpaRestriction = $this->checkGPARestrictions($student, $totalCredits);
        if ($gpaRestriction) {
            if ($gpaRestriction['blocks_registration']) {
                $validation['valid'] = false;
                $validation['issues'][] = $gpaRestriction;
            } else {
                $validation['warnings'][] = $gpaRestriction;
            }
        }
        
        // Check for special program requirements
        $programRequirements = $this->checkProgramRequirements($student, $totalCredits);
        if (!empty($programRequirements)) {
            foreach ($programRequirements as $req) {
                if ($req['blocks_registration']) {
                    $validation['valid'] = false;
                    $validation['issues'][] = $req;
                } else {
                    $validation['warnings'][] = $req;
                }
            }
        }
        
        return $validation;
    }
    
    /**
     * Get student information
     */
    private function getStudentInfo(int $studentId)
    {
        return DB::table('students')
            ->where('id', $studentId)
            ->first();
    }
    
    /**
     * Calculate current enrolled credits for the term
     */
    private function getCurrentCredits(int $studentId, int $termId): int
    {
        return DB::table('enrollments as e')
            ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->where('e.student_id', $studentId)
            ->where('e.term_id', $termId)
            ->whereIn('e.enrollment_status', ['enrolled', 'pending'])
            ->sum('c.credits');
    }
    
    /**
     * Calculate total credits from new sections
     */
    private function calculateNewCredits(Collection $sections): int
    {
        $totalCredits = 0;
        
        foreach ($sections as $section) {
            if (isset($section->credits)) {
                $totalCredits += $section->credits;
            } elseif (isset($section->course) && isset($section->course->credits)) {
                $totalCredits += $section->course->credits;
            } else {
                // Fetch credits if not loaded
                $course = DB::table('courses')
                    ->where('id', $section->course_id)
                    ->first();
                if ($course) {
                    $totalCredits += $course->credits;
                }
            }
        }
        
        return $totalCredits;
    }
    
    /**
     * Get credit limits based on student status
     */
    private function getStudentCreditLimits($student): array
    {
        // Default limits
        $limits = [
            'min' => self::MIN_FULL_TIME_CREDITS,
            'max' => self::MAX_STANDARD_CREDITS
        ];
        
        // Adjust based on student status
        if ($student) {
            // Part-time students
            if ($student->enrollment_status === 'part_time') {
                $limits['min'] = self::MIN_PART_TIME_CREDITS;
                $limits['max'] = 11; // Part-time maximum
            }
            
            // Academic probation
            if ($student->academic_standing === 'probation') {
                $limits['max'] = 15; // Reduced maximum for probation
            }
            
            // Graduate students
            if ($student->academic_level === 'graduate') {
                $limits['min'] = 9; // Graduate full-time minimum
                $limits['max'] = 15; // Graduate maximum
            }
        }
        
        return $limits;
    }
    
    /**
     * Check if student has overload permission
     */
    private function hasOverloadPermission(int $studentId, int $termId): bool
    {
        return DB::table('registration_overrides')
            ->where('student_id', $studentId)
            ->where('term_id', $termId)
            ->where('override_type', 'credit_overload')
            ->where('is_approved', true)
            ->where('expires_at', '>', now())
            ->exists();
    }
    
    /**
     * Check for repeated courses
     */
    private function checkRepeatedCourses(int $studentId, Collection $newSections): array
    {
        $repeated = [];
        
        foreach ($newSections as $section) {
            $courseId = $section->course_id;
            
            // Check if student has taken this course before
            $previousEnrollment = DB::table('enrollments as e')
                ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
                ->join('courses as c', 'cs.course_id', '=', 'c.id')
                ->where('e.student_id', $studentId)
                ->where('cs.course_id', $courseId)
                ->where('e.enrollment_status', 'completed')
                ->select('c.code', 'c.title', 'e.final_grade')
                ->first();
                
            if ($previousEnrollment) {
                $repeated[] = [
                    'code' => $previousEnrollment->code,
                    'title' => $previousEnrollment->title,
                    'previous_grade' => $previousEnrollment->final_grade
                ];
            }
        }
        
        return $repeated;
    }
    
    /**
     * Get repeat policy message based on previous grade
     */
    private function getRepeatPolicy(string $previousGrade): string
    {
        if (in_array($previousGrade, ['F', 'W', 'I'])) {
            return "Repeating due to unsatisfactory grade. New grade will replace the old one in GPA calculation.";
        } elseif (in_array($previousGrade, ['D', 'C'])) {
            return "Grade improvement attempt. Both grades will appear on transcript, higher grade used for GPA.";
        } else {
            return "Course repeat may require special permission. Please check with your advisor.";
        }
    }
    
    /**
     * Check GPA-based credit restrictions
     */
    private function checkGPARestrictions($student, int $totalCredits): ?array
    {
        if (!$student || !isset($student->cumulative_gpa)) {
            return null;
        }
        
        $gpa = (float) $student->cumulative_gpa;
        
        // Academic probation restrictions
        if ($gpa < 2.0 && $totalCredits > 15) {
            return [
                'type' => 'gpa_restriction',
                'message' => "Students on academic probation (GPA < 2.0) cannot register for more than 15 credits",
                'current_gpa' => $gpa,
                'blocks_registration' => true
            ];
        }
        
        // Warning for low GPA with high credit load
        if ($gpa < 2.5 && $totalCredits > 16) {
            return [
                'type' => 'gpa_warning',
                'message' => "With a GPA of {$gpa}, consider reducing your course load for better academic performance",
                'current_gpa' => $gpa,
                'blocks_registration' => false
            ];
        }
        
        // Dean's list students can take more credits
        if ($gpa >= 3.5 && $totalCredits > self::MAX_STANDARD_CREDITS) {
            return [
                'type' => 'high_achiever',
                'message' => "As a high-achieving student (GPA {$gpa}), you may be eligible for credit overload",
                'current_gpa' => $gpa,
                'blocks_registration' => false
            ];
        }
        
        return null;
    }
    
    /**
     * Check program-specific requirements
     */
    private function checkProgramRequirements($student, int $totalCredits): array
    {
        $requirements = [];
        
        if (!$student || !$student->program_id) {
            return $requirements;
        }
        
        // Get program requirements
        $program = DB::table('academic_programs')
            ->where('id', $student->program_id)
            ->first();
            
        if (!$program) {
            return $requirements;
        }
        
        // Check if program has specific credit requirements
        if ($program->min_credits_per_term && $totalCredits < $program->min_credits_per_term) {
            $requirements[] = [
                'type' => 'program_minimum',
                'message' => "Your program ({$program->name}) requires at least {$program->min_credits_per_term} credits per term",
                'blocks_registration' => false
            ];
        }
        
        if ($program->max_credits_per_term && $totalCredits > $program->max_credits_per_term) {
            $requirements[] = [
                'type' => 'program_maximum',
                'message' => "Your program ({$program->name}) limits registration to {$program->max_credits_per_term} credits per term",
                'blocks_registration' => true
            ];
        }
        
        // Engineering programs often have stricter requirements
        if (str_contains(strtolower($program->name), 'engineering') && $totalCredits > 19) {
            $requirements[] = [
                'type' => 'program_restriction',
                'message' => "Engineering students are limited to 19 credits per term due to course intensity",
                'blocks_registration' => true
            ];
        }
        
        return $requirements;
    }
}