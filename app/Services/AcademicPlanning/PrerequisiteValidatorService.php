<?php
// Save as: backend/app/Services/AcademicPlanning/PrerequisiteValidatorService.php

namespace App\Services\AcademicPlanning;

use App\Models\Student;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\CourseSequencingRule;
use App\Models\Enrollment;
use App\Models\AcademicTerm;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PrerequisiteValidatorService
{
    /**
     * Validate prerequisites for a student registering for a course
     */
    public function validateForStudent(Student $student, Course $course): array
    {
        $rules = CourseSequencingRule::where('course_id', $course->id)
            ->where('is_active', true)
            ->get();

        $results = [
            'can_register' => true,
            'errors' => [],
            'warnings' => [],
            'overrideable' => [],
            'details' => []
        ];

        if ($rules->isEmpty()) {
            return $results;
        }

        // Group rules by type for organized checking
        $rulesByType = $rules->groupBy('rule_type');

        // Check prerequisites
        if (isset($rulesByType['prerequisite'])) {
            $prereqResults = $this->checkPrerequisites($student, $rulesByType['prerequisite']);
            $results = $this->mergeResults($results, $prereqResults);
        }

        // Check corequisites
        if (isset($rulesByType['corequisite'])) {
            $coreqResults = $this->checkCorequisites($student, $rulesByType['corequisite']);
            $results = $this->mergeResults($results, $coreqResults);
        }

        // Check prohibited courses
        if (isset($rulesByType['prohibited'])) {
            $prohibitedResults = $this->checkProhibited($student, $rulesByType['prohibited']);
            $results = $this->mergeResults($results, $prohibitedResults);
        }

        // Check recommended courses (warnings only)
        if (isset($rulesByType['recommended'])) {
            $recommendedResults = $this->checkRecommended($student, $rulesByType['recommended']);
            $results = $this->mergeResults($results, $recommendedResults);
        }

        // Check course sequences
        if (isset($rulesByType['sequence'])) {
            $sequenceResults = $this->checkSequences($student, $rulesByType['sequence']);
            $results = $this->mergeResults($results, $sequenceResults);
        }

        return $results;
    }

    /**
     * Validate prerequisites for a section
     */
    public function validateForSection(Student $student, CourseSection $section): array
    {
        return $this->validateForStudent($student, $section->course);
    }

    /**
     * Batch validate prerequisites for multiple courses
     */
    public function validateMultiple(Student $student, array $courseIds): array
    {
        $results = [];
        
        foreach ($courseIds as $courseId) {
            $course = Course::find($courseId);
            if ($course) {
                $results[$courseId] = $this->validateForStudent($student, $course);
            }
        }

        return $results;
    }

    /**
     * Check if a specific prerequisite is met
     */
    public function checkSpecificPrerequisite(Student $student, Course $prerequisiteCourse, ?string $minGrade = null): bool
    {
        $enrollment = $student->enrollments()
            ->whereHas('section.course', function ($query) use ($prerequisiteCourse) {
                $query->where('id', $prerequisiteCourse->id);
            })
            ->where('enrollment_status', 'completed')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$enrollment) {
            return false;
        }

        if ($minGrade) {
            return $this->meetsMinimumGrade($enrollment->final_grade ?? $enrollment->grade, $minGrade);
        }

        return true;
    }

    /**
     * Get prerequisite chain for a course
     */
    public function getPrerequisiteChain(Course $course, int $maxDepth = 5): array
    {
        return $this->buildPrerequisiteTree($course, 0, $maxDepth);
    }

    /**
     * Check if student can waive prerequisites
     */
    public function checkWaiverEligibility(Student $student, Course $course): array
    {
        $eligibility = [
            'can_request_waiver' => false,
            'reasons' => [],
            'supporting_evidence' => []
        ];

        // Check if student has equivalent experience
        if ($this->hasEquivalentExperience($student, $course)) {
            $eligibility['can_request_waiver'] = true;
            $eligibility['reasons'][] = 'Equivalent experience or coursework';
        }

        // Check if student has high GPA
        if ($student->cumulative_gpa >= 3.5) {
            $eligibility['can_request_waiver'] = true;
            $eligibility['reasons'][] = 'High academic standing (GPA >= 3.5)';
        }

        // Check if it's the student's last term
        if ($this->isLastTerm($student)) {
            $eligibility['can_request_waiver'] = true;
            $eligibility['reasons'][] = 'Final term before graduation';
        }

        // Check for transfer credits
        if ($this->hasRelevantTransferCredits($student, $course)) {
            $eligibility['can_request_waiver'] = true;
            $eligibility['reasons'][] = 'Relevant transfer credits';
            $eligibility['supporting_evidence'][] = 'Transfer transcript required';
        }

        return $eligibility;
    }

    /**
     * Private helper methods
     */
    private function checkPrerequisites(Student $student, Collection $rules): array
    {
        $results = [
            'can_register' => true,
            'errors' => [],
            'warnings' => [],
            'overrideable' => [],
            'details' => []
        ];

        foreach ($rules as $rule) {
            if ($rule->related_course_id) {
                // Single course prerequisite
                $prereqMet = $this->checkSinglePrerequisite($student, $rule);
                
                if (!$prereqMet['met']) {
                    if ($rule->is_strict) {
                        $results['can_register'] = false;
                        $results['errors'][] = $prereqMet['message'];
                    } else {
                        $results['overrideable'][] = $prereqMet['message'];
                    }
                }
                
                $results['details'][] = $prereqMet;
                
            } elseif ($rule->course_group) {
                // Group prerequisite (one of several courses)
                $groupMet = $this->checkGroupPrerequisite($student, $rule);
                
                if (!$groupMet['met']) {
                    if ($rule->is_strict) {
                        $results['can_register'] = false;
                        $results['errors'][] = $groupMet['message'];
                    } else {
                        $results['overrideable'][] = $groupMet['message'];
                    }
                }
                
                $results['details'][] = $groupMet;
            }
        }

        return $results;
    }

    private function checkSinglePrerequisite(Student $student, CourseSequencingRule $rule): array
    {
        $prerequisiteCourse = Course::find($rule->related_course_id);
        if (!$prerequisiteCourse) {
            return [
                'met' => false,
                'type' => 'prerequisite',
                'course' => 'Unknown',
                'message' => 'Prerequisite course not found'
            ];
        }

        $enrollment = $student->enrollments()
            ->whereHas('section.course', function ($query) use ($rule) {
                $query->where('id', $rule->related_course_id);
            })
            ->whereIn('enrollment_status', ['completed'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$enrollment) {
            // Check if currently enrolled (might be taking concurrently)
            $currentlyEnrolled = $student->enrollments()
                ->whereHas('section.course', function ($query) use ($rule) {
                    $query->where('id', $rule->related_course_id);
                })
                ->whereIn('enrollment_status', ['enrolled', 'in_progress'])
                ->exists();

            if ($currentlyEnrolled) {
                return [
                    'met' => false,
                    'type' => 'prerequisite',
                    'course' => $prerequisiteCourse->course_code,
                    'message' => "Currently taking prerequisite {$prerequisiteCourse->course_code} - must complete first"
                ];
            }

            return [
                'met' => false,
                'type' => 'prerequisite',
                'course' => $prerequisiteCourse->course_code,
                'message' => "Missing prerequisite: {$prerequisiteCourse->course_code} - {$prerequisiteCourse->title}"
            ];
        }

        // Check minimum grade if specified
        if ($rule->min_grade) {
            $gradeMet = $this->meetsMinimumGrade(
                $enrollment->final_grade ?? $enrollment->grade,
                $rule->min_grade
            );

            if (!$gradeMet) {
                return [
                    'met' => false,
                    'type' => 'prerequisite',
                    'course' => $prerequisiteCourse->course_code,
                    'grade' => $enrollment->final_grade ?? $enrollment->grade,
                    'min_grade' => $rule->min_grade,
                    'message' => "Prerequisite {$prerequisiteCourse->course_code} completed with grade {$enrollment->final_grade}, minimum {$rule->min_grade} required"
                ];
            }
        }

        return [
            'met' => true,
            'type' => 'prerequisite',
            'course' => $prerequisiteCourse->course_code,
            'grade' => $enrollment->final_grade ?? $enrollment->grade,
            'message' => "Prerequisite {$prerequisiteCourse->course_code} satisfied"
        ];
    }

    private function checkGroupPrerequisite(Student $student, CourseSequencingRule $rule): array
    {
        $courseCodes = $rule->course_group;
        $minRequired = $rule->min_courses_from_group ?? 1;

        $completedCourses = $student->enrollments()
            ->whereHas('section.course', function ($query) use ($courseCodes) {
                $query->whereIn('course_code', $courseCodes);
            })
            ->where('enrollment_status', 'completed')
            ->with('section.course')
            ->get();

        $completedCount = 0;
        $completedList = [];

        foreach ($completedCourses as $enrollment) {
            // Check minimum grade if required
            if ($rule->min_grade) {
                if ($this->meetsMinimumGrade($enrollment->final_grade ?? $enrollment->grade, $rule->min_grade)) {
                    $completedCount++;
                    $completedList[] = $enrollment->section->course->course_code;
                }
            } else {
                $completedCount++;
                $completedList[] = $enrollment->section->course->course_code;
            }
        }

        if ($completedCount >= $minRequired) {
            return [
                'met' => true,
                'type' => 'prerequisite_group',
                'completed' => $completedList,
                'message' => "Completed {$completedCount} of {$minRequired} required from group"
            ];
        }

        $remaining = $minRequired - $completedCount;
        $courseList = implode(', ', $courseCodes);

        return [
            'met' => false,
            'type' => 'prerequisite_group',
            'completed' => $completedList,
            'required' => $minRequired,
            'course_group' => $courseCodes,
            'message' => "Need {$remaining} more course(s) from: {$courseList}"
        ];
    }

    private function checkCorequisites(Student $student, Collection $rules): array
    {
        $results = [
            'can_register' => true,
            'errors' => [],
            'warnings' => [],
            'overrideable' => [],
            'details' => []
        ];

        $currentTerm = AcademicTerm::where('is_current', true)->first();
        if (!$currentTerm) {
            return $results;
        }

        foreach ($rules as $rule) {
            if ($rule->related_course_id) {
                $corequisiteCourse = Course::find($rule->related_course_id);
                if (!$corequisiteCourse) {
                    continue;
                }

                // Check if already completed
                $completed = $student->enrollments()
                    ->whereHas('section.course', function ($query) use ($rule) {
                        $query->where('id', $rule->related_course_id);
                    })
                    ->where('enrollment_status', 'completed')
                    ->exists();

                if ($completed) {
                    $results['details'][] = [
                        'met' => true,
                        'type' => 'corequisite',
                        'course' => $corequisiteCourse->course_code,
                        'message' => "Corequisite {$corequisiteCourse->course_code} already completed"
                    ];
                    continue;
                }

                // Check if currently enrolled in same term
                $enrolledConcurrently = $student->enrollments()
                    ->where('term_id', $currentTerm->id)
                    ->whereHas('section.course', function ($query) use ($rule) {
                        $query->where('id', $rule->related_course_id);
                    })
                    ->whereIn('enrollment_status', ['enrolled', 'in_progress'])
                    ->exists();

                if (!$enrolledConcurrently) {
                    $message = "Must take {$corequisiteCourse->course_code} concurrently";
                    
                    if ($rule->is_strict) {
                        $results['can_register'] = false;
                        $results['errors'][] = $message;
                    } else {
                        $results['warnings'][] = $message;
                    }

                    $results['details'][] = [
                        'met' => false,
                        'type' => 'corequisite',
                        'course' => $corequisiteCourse->course_code,
                        'message' => $message
                    ];
                } else {
                    $results['details'][] = [
                        'met' => true,
                        'type' => 'corequisite',
                        'course' => $corequisiteCourse->course_code,
                        'message' => "Corequisite {$corequisiteCourse->course_code} satisfied"
                    ];
                }
            }
        }

        return $results;
    }

    private function checkProhibited(Student $student, Collection $rules): array
    {
        $results = [
            'can_register' => true,
            'errors' => [],
            'warnings' => [],
            'overrideable' => [],
            'details' => []
        ];

        foreach ($rules as $rule) {
            if ($rule->related_course_id) {
                $prohibitedCourse = Course::find($rule->related_course_id);
                if (!$prohibitedCourse) {
                    continue;
                }

                $hasTaken = $student->enrollments()
                    ->whereHas('section.course', function ($query) use ($rule) {
                        $query->where('id', $rule->related_course_id);
                    })
                    ->whereIn('enrollment_status', ['completed', 'enrolled', 'in_progress'])
                    ->exists();

                if ($hasTaken) {
                    $message = "Cannot take - already completed or enrolled in {$prohibitedCourse->course_code}";
                    $results['can_register'] = false;
                    $results['errors'][] = $message;
                    
                    $results['details'][] = [
                        'met' => false,
                        'type' => 'prohibited',
                        'course' => $prohibitedCourse->course_code,
                        'message' => $message
                    ];
                } else {
                    $results['details'][] = [
                        'met' => true,
                        'type' => 'prohibited',
                        'course' => $prohibitedCourse->course_code,
                        'message' => "No conflict with {$prohibitedCourse->course_code}"
                    ];
                }
            }
        }

        return $results;
    }

    private function checkRecommended(Student $student, Collection $rules): array
    {
        $results = [
            'can_register' => true,
            'errors' => [],
            'warnings' => [],
            'overrideable' => [],
            'details' => []
        ];

        foreach ($rules as $rule) {
            if ($rule->related_course_id) {
                $recommendedCourse = Course::find($rule->related_course_id);
                if (!$recommendedCourse) {
                    continue;
                }

                $hasTaken = $student->enrollments()
                    ->whereHas('section.course', function ($query) use ($rule) {
                        $query->where('id', $rule->related_course_id);
                    })
                    ->where('enrollment_status', 'completed')
                    ->exists();

                if (!$hasTaken) {
                    $message = "Recommended to complete {$recommendedCourse->course_code} first";
                    $results['warnings'][] = $message;
                    
                    $results['details'][] = [
                        'met' => false,
                        'type' => 'recommended',
                        'course' => $recommendedCourse->course_code,
                        'message' => $message
                    ];
                } else {
                    $results['details'][] = [
                        'met' => true,
                        'type' => 'recommended',
                        'course' => $recommendedCourse->course_code,
                        'message' => "Recommended course {$recommendedCourse->course_code} completed"
                    ];
                }
            }
        }

        return $results;
    }

    private function checkSequences(Student $student, Collection $rules): array
    {
        $results = [
            'can_register' => true,
            'errors' => [],
            'warnings' => [],
            'overrideable' => [],
            'details' => []
        ];

        foreach ($rules as $rule) {
            if (!$rule->sequence_name || !$rule->sequence_order) {
                continue;
            }

            // Get all courses in this sequence before the current one
            $previousCourses = CourseSequencingRule::where('sequence_name', $rule->sequence_name)
                ->where('sequence_order', '<', $rule->sequence_order)
                ->pluck('course_id');

            $completedPrevious = $student->enrollments()
                ->whereHas('section.course', function ($query) use ($previousCourses) {
                    $query->whereIn('id', $previousCourses);
                })
                ->where('enrollment_status', 'completed')
                ->count();

            if ($completedPrevious < $previousCourses->count()) {
                $message = "Must complete earlier courses in {$rule->sequence_name} sequence";
                
                if ($rule->is_strict) {
                    $results['can_register'] = false;
                    $results['errors'][] = $message;
                } else {
                    $results['overrideable'][] = $message;
                }

                $results['details'][] = [
                    'met' => false,
                    'type' => 'sequence',
                    'sequence' => $rule->sequence_name,
                    'message' => $message
                ];
            } else {
                $results['details'][] = [
                    'met' => true,
                    'type' => 'sequence',
                    'sequence' => $rule->sequence_name,
                    'message' => "Sequence requirement satisfied"
                ];
            }
        }

        return $results;
    }

    private function meetsMinimumGrade(?string $grade, string $minGrade): bool
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

        return ($gradeValues[$grade] ?? 0) >= ($gradeValues[$minGrade] ?? 0);
    }

    private function mergeResults(array $current, array $new): array
    {
        $current['can_register'] = $current['can_register'] && $new['can_register'];
        $current['errors'] = array_merge($current['errors'], $new['errors']);
        $current['warnings'] = array_merge($current['warnings'], $new['warnings']);
        $current['overrideable'] = array_merge($current['overrideable'], $new['overrideable']);
        $current['details'] = array_merge($current['details'], $new['details']);
        
        return $current;
    }

    private function buildPrerequisiteTree(Course $course, int $currentDepth, int $maxDepth): array
    {
        if ($currentDepth >= $maxDepth) {
            return [];
        }

        $tree = [
            'course_id' => $course->id,
            'course_code' => $course->course_code,
            'course_title' => $course->title,
            'prerequisites' => []
        ];

        $rules = CourseSequencingRule::where('course_id', $course->id)
            ->where('rule_type', 'prerequisite')
            ->where('is_active', true)
            ->get();

        foreach ($rules as $rule) {
            if ($rule->related_course_id) {
                $prereqCourse = Course::find($rule->related_course_id);
                if ($prereqCourse) {
                    $tree['prerequisites'][] = $this->buildPrerequisiteTree(
                        $prereqCourse,
                        $currentDepth + 1,
                        $maxDepth
                    );
                }
            }
        }

        return $tree;
    }

    private function hasEquivalentExperience(Student $student, Course $course): bool
    {
        // Implementation would check for relevant experience
        return false;
    }

    private function isLastTerm(Student $student): bool
    {
        // Check if student is in their final term
        $creditsRemaining = 120 - ($student->credits_earned ?? 0);
        return $creditsRemaining <= 18; // One term's worth of credits
    }

    private function hasRelevantTransferCredits(Student $student, Course $course): bool
    {
        // Check for relevant transfer credits
        return false;
    }
}