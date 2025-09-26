<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PrerequisiteService
{
    private $prerequisiteColumn;
    private $typeColumn;
    private $gradeColumn;
    
    public function __construct()
    {
        // Detect which column names the database is using
        if (Schema::hasTable('course_prerequisites')) {
            $columns = Schema::getColumnListing('course_prerequisites');
            $this->prerequisiteColumn = in_array('prerequisite_course_id', $columns) ? 'prerequisite_course_id' : 'prerequisite_id';
            $this->typeColumn = in_array('requirement_type', $columns) ? 'requirement_type' : 'type';
            $this->gradeColumn = in_array('minimum_grade', $columns) ? 'minimum_grade' : 'min_grade';
        } else {
            // Default column names
            $this->prerequisiteColumn = 'prerequisite_id';
            $this->typeColumn = 'type';
            $this->gradeColumn = 'min_grade';
        }
    }
    
    /**
     * Check if a student meets all prerequisites for the given sections
     *
     * @param int $studentId
     * @param Collection $sections
     * @return array Array of prerequisite issues
     */
    public function checkPrerequisites(int $studentId, Collection $sections): array
    {
        $issues = [];
        
        foreach ($sections as $section) {
            // Get all prerequisites for this course
            $prerequisites = $this->getCoursePrerequisites($section->course_id);
            
            foreach ($prerequisites as $prereq) {
                // Get the prerequisite course ID (handle different column names)
                $prerequisiteCourseId = $prereq->{$this->prerequisiteColumn};
                
                // Check if student has completed the prerequisite
                $completed = $this->hasCompletedCourse($studentId, $prerequisiteCourseId);
                
                // Check if currently enrolled (for corequisites)
                $currentlyEnrolled = $this->isCurrentlyEnrolled($studentId, $prerequisiteCourseId);
                
                // Get the requirement type (handle different column names)
                $requirementType = $prereq->{$this->typeColumn};
                
                // Get course code safely (extract before using in conditions)
                $courseCode = $this->getCourseCode($section);
                $courseTitle = $this->getCourseTitle($section);
                
                // Apply validation based on requirement type
                if ($requirementType === 'prerequisite' && !$completed) {
                    $issues[] = [
                        'course' => $courseCode,
                        'course_title' => $courseTitle,
                        'prerequisite' => $prereq->prerequisite_code,
                        'prerequisite_title' => $prereq->prerequisite_title,
                        'type' => 'prerequisite',
                        'message' => "You must complete {$prereq->prerequisite_code} before taking {$courseCode}"
                    ];
                } elseif ($requirementType === 'corequisite' && !$completed && !$currentlyEnrolled) {
                    $issues[] = [
                        'course' => $courseCode,
                        'course_title' => $courseTitle,
                        'prerequisite' => $prereq->prerequisite_code,
                        'prerequisite_title' => $prereq->prerequisite_title,
                        'type' => 'corequisite',
                        'message' => "You must complete or be enrolled in {$prereq->prerequisite_code} to take {$courseCode}"
                    ];
                }
                
                // Check minimum grade requirement if specified
                if ($completed && property_exists($prereq, $this->gradeColumn) && $prereq->{$this->gradeColumn}) {
                    $grade = $this->getStudentGrade($studentId, $prerequisiteCourseId);
                    if (!$this->meetsMinimumGrade($grade, $prereq->{$this->gradeColumn})) {
                        $issues[] = [
                            'course' => $courseCode,
                            'course_title' => $courseTitle,
                            'prerequisite' => $prereq->prerequisite_code,
                            'prerequisite_title' => $prereq->prerequisite_title,
                            'type' => 'grade_requirement',
                            'message' => "You need a minimum grade of {$prereq->{$this->gradeColumn}} in {$prereq->prerequisite_code} (You earned: {$grade})"
                        ];
                    }
                }
            }
        }
        
        return $issues;
    }
    
    /**
     * Get course code from section object safely
     */
    private function getCourseCode($section): string
    {
        if (isset($section->course) && isset($section->course->code)) {
            return $section->course->code;
        }
        if (isset($section->course_code)) {
            return $section->course_code;
        }
        if (isset($section->code)) {
            return $section->code;
        }
        return 'Unknown';
    }
    
    /**
     * Get course title from section object safely
     */
    private function getCourseTitle($section): string
    {
        if (isset($section->course) && isset($section->course->title)) {
            return $section->course->title;
        }
        if (isset($section->title)) {
            return $section->title;
        }
        return 'Unknown Course';
    }
    
    /**
     * Get all prerequisites for a course
     */
    private function getCoursePrerequisites(int $courseId): Collection
    {
        $query = DB::table('course_prerequisites as cp')
            ->join('courses as c', 'cp.' . $this->prerequisiteColumn, '=', 'c.id')
            ->where('cp.course_id', $courseId);
        
        // Only filter by is_active if the column exists
        if (Schema::hasColumn('course_prerequisites', 'is_active')) {
            $query->where('cp.is_active', true);
        }
        
        // Check for effective dates if columns exist
        if (Schema::hasColumn('course_prerequisites', 'effective_from')) {
            $query->where(function($q) {
                $q->whereNull('cp.effective_from')
                  ->orWhere('cp.effective_from', '<=', now());
            });
        }
        
        if (Schema::hasColumn('course_prerequisites', 'effective_until')) {
            $query->where(function($q) {
                $q->whereNull('cp.effective_until')
                  ->orWhere('cp.effective_until', '>=', now());
            });
        }
        
        $query->select(
            'cp.*',
            'c.code as prerequisite_code',
            'c.title as prerequisite_title'
        );
        
        // Add the dynamic column names to the select
        $query->addSelect([
            'cp.' . $this->prerequisiteColumn,
            'cp.' . $this->typeColumn,
        ]);
        
        if (Schema::hasColumn('course_prerequisites', $this->gradeColumn)) {
            $query->addSelect('cp.' . $this->gradeColumn);
        }
        
        return $query->get();
    }
    
    /**
     * Check if student has completed a specific course
     */
    private function hasCompletedCourse(int $studentId, int $courseId): bool
    {
        return DB::table('enrollments as e')
            ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
            ->where('e.student_id', $studentId)
            ->where('cs.course_id', $courseId)
            ->whereIn('e.final_grade', ['A', 'B+', 'B', 'C+', 'C', 'D', 'P'])
            ->where('e.enrollment_status', 'completed')
            ->exists();
    }
    
    /**
     * Check if student is currently enrolled in a course
     */
    private function isCurrentlyEnrolled(int $studentId, int $courseId): bool
    {
        // Get current term
        $currentTerm = DB::table('academic_terms')
            ->where('is_current', true)
            ->first();
            
        if (!$currentTerm) {
            return false;
        }
        
        return DB::table('enrollments as e')
            ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
            ->where('e.student_id', $studentId)
            ->where('cs.course_id', $courseId)
            ->where('e.term_id', $currentTerm->id)
            ->whereIn('e.enrollment_status', ['enrolled', 'pending'])
            ->exists();
    }
    
    /**
     * Get student's grade for a specific course
     */
    private function getStudentGrade(int $studentId, int $courseId): ?string
    {
        $enrollment = DB::table('enrollments as e')
            ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
            ->where('e.student_id', $studentId)
            ->where('cs.course_id', $courseId)
            ->where('e.enrollment_status', 'completed')
            ->orderBy('e.created_at', 'desc')
            ->first();
            
        return $enrollment ? $enrollment->final_grade : null;
    }
    
    /**
     * Check if a grade meets the minimum requirement
     */
    private function meetsMinimumGrade(?string $actualGrade, string $minimumGrade): bool
    {
        $gradeValues = [
            'A' => 4.0,
            'B+' => 3.5,
            'B' => 3.0,
            'C+' => 2.5,
            'C' => 2.0,
            'D' => 1.0,
            'F' => 0.0
        ];
        
        if (!$actualGrade || !isset($gradeValues[$actualGrade]) || !isset($gradeValues[$minimumGrade])) {
            return false;
        }
        
        return $gradeValues[$actualGrade] >= $gradeValues[$minimumGrade];
    }
    
    /**
     * Check if student has prerequisite override
     */
    public function hasPrerequisiteOverride(int $studentId, int $courseId): bool
    {
        if (!Schema::hasTable('prerequisite_overrides')) {
            return false;
        }
        
        $currentTerm = DB::table('academic_terms')
            ->where('is_current', true)
            ->first();
            
        if (!$currentTerm) {
            return false;
        }
        
        return DB::table('prerequisite_overrides')
            ->where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->where('term_id', $currentTerm->id)
            ->where('is_approved', true)
            ->where('expires_at', '>', now())
            ->exists();
    }
}