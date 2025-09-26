<?php
// Save as: backend/app/Services/DegreeAudit/CourseMatcherService.php

namespace App\Services\DegreeAudit;

use App\Models\Student;
use App\Models\Course;
use App\Models\DegreeRequirement;
use App\Models\CourseRequirementMapping;
use App\Models\StudentCourseApplication;
use App\Models\Enrollment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CourseMatcherService
{
    /**
     * Match all student's courses to degree requirements
     */
    public function matchCoursesToRequirements(Student $student): array
    {
        $enrollments = $student->enrollments()
            ->with('section.course')
            ->whereIn('enrollment_status', ['completed', 'enrolled', 'in_progress'])
            ->get();
        
        $matches = [];
        $unmatchedCourses = [];

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->section->course;
            
            // Find all possible requirement mappings for this course
            $mappings = CourseRequirementMapping::where('course_id', $course->id)
                ->where('is_active', true)
                ->with('requirement.category')
                ->get();

            if ($mappings->isEmpty()) {
                // No direct mapping found, try to match by other criteria
                $possibleMatches = $this->findPossibleMatches($course, $student);
                
                if (empty($possibleMatches)) {
                    $unmatchedCourses[] = [
                        'enrollment_id' => $enrollment->id,
                        'course_code' => $course->course_code,
                        'course_title' => $course->title,
                        'credits' => $course->credits,
                        'status' => $enrollment->enrollment_status,
                        'suggestion' => 'May count as free elective'
                    ];
                } else {
                    foreach ($possibleMatches as $match) {
                        $matches[] = array_merge($match, [
                            'enrollment_id' => $enrollment->id,
                            'course_id' => $course->id,
                            'course_code' => $course->course_code,
                            'credits' => $course->credits,
                            'status' => $enrollment->enrollment_status
                        ]);
                    }
                }
            } else {
                // Process direct mappings
                foreach ($mappings as $mapping) {
                    $matches[] = [
                        'enrollment_id' => $enrollment->id,
                        'course_id' => $course->id,
                        'course_code' => $course->course_code,
                        'course_title' => $course->title,
                        'requirement_id' => $mapping->requirement_id,
                        'requirement_name' => $mapping->requirement->name,
                        'category' => $mapping->requirement->category->name,
                        'fulfillment_type' => $mapping->fulfillment_type,
                        'credits_applied' => $mapping->credit_value ?? $course->credits,
                        'status' => $enrollment->enrollment_status,
                        'grade' => $enrollment->final_grade ?? $enrollment->grade,
                        'min_grade_required' => $mapping->min_grade,
                        'meets_min_grade' => $this->meetsMinGrade(
                            $enrollment->final_grade ?? $enrollment->grade,
                            $mapping->min_grade
                        )
                    ];
                }
            }
        }

        return [
            'matched_courses' => $matches,
            'unmatched_courses' => $unmatchedCourses,
            'summary' => $this->generateMatchingSummary($matches, $unmatchedCourses)
        ];
    }

    /**
     * Find the best requirement match for a specific course
     */
    public function findBestRequirementMatch(Course $course, array $requirements): ?DegreeRequirement
    {
        $bestMatch = null;
        $highestPriority = -1;

        foreach ($requirements as $requirement) {
            // Check for direct mapping
            $mapping = CourseRequirementMapping::where('course_id', $course->id)
                ->where('requirement_id', $requirement->id)
                ->where('is_active', true)
                ->first();

            if ($mapping) {
                $priority = $this->calculateMatchPriority($mapping, $requirement);
                if ($priority > $highestPriority) {
                    $highestPriority = $priority;
                    $bestMatch = $requirement;
                }
            } else {
                // Check if course could potentially fulfill this requirement
                if ($this->couldFulfillRequirement($course, $requirement)) {
                    $priority = $this->calculatePotentialPriority($course, $requirement);
                    if ($priority > $highestPriority) {
                        $highestPriority = $priority;
                        $bestMatch = $requirement;
                    }
                }
            }
        }

        return $bestMatch;
    }

    /**
     * Apply course to a requirement
     */
    public function applyCourseToRequirement(
        Student $student,
        Enrollment $enrollment,
        DegreeRequirement $requirement,
        array $options = []
    ): StudentCourseApplication {
        $course = $enrollment->section->course;
        
        // Check if already applied
        $existing = StudentCourseApplication::where('student_id', $student->id)
            ->where('enrollment_id', $enrollment->id)
            ->where('requirement_id', $requirement->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Determine credits to apply
        $mapping = CourseRequirementMapping::where('course_id', $course->id)
            ->where('requirement_id', $requirement->id)
            ->first();
        
        $creditsApplied = $mapping->credit_value ?? $course->credits;

        // Create application
        $application = StudentCourseApplication::create([
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'course_id' => $course->id,
            'requirement_id' => $requirement->id,
            'program_requirement_id' => $options['program_requirement_id'] ?? null,
            'credits_applied' => $creditsApplied,
            'grade' => $enrollment->final_grade ?? $enrollment->grade,
            'status' => $this->determineApplicationStatus($enrollment),
            'is_override' => $options['is_override'] ?? false,
            'override_reason' => $options['override_reason'] ?? null,
            'override_by' => $options['override_by'] ?? null,
            'override_at' => $options['is_override'] ? now() : null
        ]);

        // Update student degree progress
        $this->updateDegreeProgress($student, $requirement);

        return $application;
    }

    /**
     * Optimize course assignments to maximize requirement fulfillment
     */
    public function optimizeCourseAssignments(Student $student): array
    {
        $enrollments = $student->enrollments()
            ->with('section.course')
            ->whereIn('enrollment_status', ['completed', 'enrolled', 'in_progress'])
            ->get();

        $requirements = $this->getStudentRequirements($student);
        $assignments = [];

        // Sort requirements by priority (required before electives)
        $sortedRequirements = $requirements->sortBy(function ($req) {
            return $req->is_required ? 0 : 1;
        });

        // Track which courses have been assigned
        $assignedEnrollments = [];

        foreach ($sortedRequirements as $requirement) {
            // Find best matching courses for this requirement
            $bestMatches = $this->findBestCoursesForRequirement(
                $requirement,
                $enrollments,
                $assignedEnrollments
            );

            foreach ($bestMatches as $enrollmentId) {
                $assignments[] = [
                    'enrollment_id' => $enrollmentId,
                    'requirement_id' => $requirement->id,
                    'requirement_name' => $requirement->name
                ];
                $assignedEnrollments[] = $enrollmentId;
            }
        }

        return [
            'optimized_assignments' => $assignments,
            'unassigned_courses' => $enrollments->whereNotIn('id', $assignedEnrollments)->count(),
            'unfulfilled_requirements' => $this->getUnfulfilledRequirements($assignments, $requirements)
        ];
    }

    /**
     * Suggest courses for unfulfilled requirements
     */
    public function suggestCoursesForRequirements(Student $student): array
    {
        $progress = DB::table('student_degree_progress')
            ->where('student_id', $student->id)
            ->where('is_satisfied', false)
            ->get();

        $suggestions = [];

        foreach ($progress as $item) {
            $requirement = DegreeRequirement::find($item->requirement_id);
            if (!$requirement) continue;

            $suggestedCourses = $this->getSuggestedCourses($requirement, $student);
            
            if (!empty($suggestedCourses)) {
                $suggestions[] = [
                    'requirement_id' => $requirement->id,
                    'requirement_name' => $requirement->name,
                    'credits_remaining' => $item->credits_remaining,
                    'suggested_courses' => $suggestedCourses,
                    'priority' => $requirement->is_required ? 'high' : 'medium'
                ];
            }
        }

        // Sort by priority
        usort($suggestions, function ($a, $b) {
            return $a['priority'] === 'high' && $b['priority'] !== 'high' ? -1 : 1;
        });

        return $suggestions;
    }

    /**
     * Find possible matches for a course without direct mapping
     */
    private function findPossibleMatches(Course $course, Student $student): array
    {
        $matches = [];

        // Check if it could be an elective
        if ($this->couldBeElective($course)) {
            $electiveRequirements = DegreeRequirement::where('requirement_type', 'credit_hours')
                ->whereHas('category', function ($query) {
                    $query->where('type', 'elective');
                })
                ->get();

            foreach ($electiveRequirements as $req) {
                $matches[] = [
                    'requirement_id' => $req->id,
                    'requirement_name' => $req->name,
                    'fulfillment_type' => 'elective',
                    'confidence' => 'medium'
                ];
            }
        }

        // Check department-based matches
        $deptRequirements = $this->findDepartmentBasedMatches($course);
        $matches = array_merge($matches, $deptRequirements);

        return $matches;
    }

    /**
     * Calculate match priority
     */
    private function calculateMatchPriority($mapping, DegreeRequirement $requirement): int
    {
        $priority = 0;

        // Fulfillment type priorities
        $fulfillmentPriorities = [
            'full' => 100,
            'partial' => 50,
            'choice' => 25,
            'elective' => 10
        ];

        $priority += $fulfillmentPriorities[$mapping->fulfillment_type] ?? 0;

        // Add priority for required requirements
        if ($requirement->is_required) {
            $priority += 50;
        }

        // Add priority based on requirement type
        $typePriorities = [
            'specific_courses' => 30,
            'course_list' => 20,
            'credit_hours' => 10
        ];

        $priority += $typePriorities[$requirement->requirement_type] ?? 0;

        return $priority;
    }

    /**
     * Check if course could fulfill requirement
     */
    private function couldFulfillRequirement(Course $course, DegreeRequirement $requirement): bool
    {
        switch ($requirement->requirement_type) {
            case 'specific_courses':
                $requiredCodes = $requirement->parameters['required_courses'] ?? [];
                return in_array($course->course_code, $requiredCodes);
                
            case 'course_list':
                $courseOptions = $requirement->parameters['choose_from'] ?? [];
                return in_array($course->course_code, $courseOptions);
                
            case 'credit_hours':
                // Check if course level/department matches requirement criteria
                return $this->matchesCreditRequirement($course, $requirement);
                
            default:
                return false;
        }
    }

    /**
     * Calculate potential priority for unmapped course
     */
    private function calculatePotentialPriority(Course $course, DegreeRequirement $requirement): int
    {
        $priority = 0;

        // Check if course code appears in requirement parameters
        $params = $requirement->parameters ?? [];
        
        if (isset($params['required_courses']) && in_array($course->course_code, $params['required_courses'])) {
            $priority += 80;
        }
        
        if (isset($params['choose_from']) && in_array($course->course_code, $params['choose_from'])) {
            $priority += 60;
        }

        // Department match
        if ($this->courseDepartmentMatchesRequirement($course, $requirement)) {
            $priority += 20;
        }

        // Level match
        if ($this->courseLevelMatchesRequirement($course, $requirement)) {
            $priority += 10;
        }

        return $priority;
    }

    /**
     * Helper methods
     */
    private function meetsMinGrade(?string $grade, ?string $minGrade): bool
    {
        if (!$minGrade || !$grade) return true;
        
        $gradeValues = [
            'A' => 12, 'A-' => 11,
            'B+' => 10, 'B' => 9, 'B-' => 8,
            'C+' => 7, 'C' => 6, 'C-' => 5,
            'D+' => 4, 'D' => 3, 'D-' => 2,
            'F' => 1
        ];

        return ($gradeValues[$grade] ?? 0) >= ($gradeValues[$minGrade] ?? 0);
    }

    private function determineApplicationStatus(Enrollment $enrollment): string
    {
        return match($enrollment->enrollment_status) {
            'completed' => 'completed',
            'enrolled', 'in_progress' => 'in_progress',
            'dropped', 'withdrawn' => 'withdrawn',
            default => 'planned'
        };
    }

    private function couldBeElective(Course $course): bool
    {
        // Logic to determine if course could be an elective
        return $course->course_level >= 100;
    }

    private function matchesCreditRequirement(Course $course, DegreeRequirement $requirement): bool
    {
        $params = $requirement->parameters ?? [];
        
        // Check course level
        if (isset($params['min_level']) && $course->course_level < $params['min_level']) {
            return false;
        }
        
        // Check department if specified
        if (isset($params['department_id']) && $course->department_id != $params['department_id']) {
            return false;
        }
        
        return true;
    }

    private function courseDepartmentMatchesRequirement(Course $course, DegreeRequirement $requirement): bool
    {
        // Implementation depends on your requirement structure
        return false;
    }

    private function courseLevelMatchesRequirement(Course $course, DegreeRequirement $requirement): bool
    {
        $params = $requirement->parameters ?? [];
        $minLevel = $params['min_level'] ?? 0;
        
        return $course->course_level >= $minLevel;
    }

    private function getStudentRequirements(Student $student): Collection
    {
        // Get requirements based on student's program
        return DegreeRequirement::whereHas('programRequirements', function ($query) use ($student) {
            $query->where('program_id', $student->program_id);
        })->get();
    }

    private function findBestCoursesForRequirement($requirement, $enrollments, $assigned): array
    {
        // Find courses that best match this requirement
        $matches = [];
        
        foreach ($enrollments as $enrollment) {
            if (in_array($enrollment->id, $assigned)) continue;
            
            if ($this->couldFulfillRequirement($enrollment->section->course, $requirement)) {
                $matches[] = $enrollment->id;
            }
        }
        
        return $matches;
    }

    private function getSuggestedCourses(DegreeRequirement $requirement, Student $student): array
    {
        // Get courses that fulfill this requirement
        $courses = Course::whereHas('requirementMappings', function ($query) use ($requirement) {
            $query->where('requirement_id', $requirement->id)
                ->where('is_active', true);
        })->get();

        $suggestions = [];
        foreach ($courses as $course) {
            // Check if student hasn't taken this course
            $taken = $student->enrollments()
                ->whereHas('section', function ($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->exists();

            if (!$taken) {
                $suggestions[] = [
                    'course_code' => $course->_code,
                    'course_title' => $course->title,
                    'credits' => $course->credits
                ];
            }
        }

        return array_slice($suggestions, 0, 5); // Return top 5 suggestions
    }

    private function generateMatchingSummary($matches, $unmatched): array
    {
        $totalCourses = count($matches) + count($unmatched);
        $matchedCredits = array_sum(array_column($matches, 'credits_applied'));
        $unmatchedCredits = array_sum(array_column($unmatched, 'credits'));

        return [
            'total_courses' => $totalCourses,
            'matched_courses' => count($matches),
            'unmatched_courses' => count($unmatched),
            'match_percentage' => $totalCourses > 0 ? round((count($matches) / $totalCourses) * 100, 2) : 0,
            'matched_credits' => $matchedCredits,
            'unmatched_credits' => $unmatchedCredits,
            'total_credits' => $matchedCredits + $unmatchedCredits
        ];
    }

    private function updateDegreeProgress(Student $student, DegreeRequirement $requirement): void
    {
        // This would trigger a recalculation of the student's progress for this requirement
        // Could dispatch a job or call a service method
    }

    private function getUnfulfilledRequirements($assignments, $requirements): array
    {
        $assignedRequirementIds = array_unique(array_column($assignments, 'requirement_id'));
        
        return $requirements->whereNotIn('id', $assignedRequirementIds)
            ->where('is_required', true)
            ->pluck('name')
            ->toArray();
    }

    private function findDepartmentBasedMatches(Course $course): array
    {
        // Find requirements that match based on department
        return [];
    }
}