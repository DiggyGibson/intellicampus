<?php
// Save as: backend/app/Models/CourseSequencingRule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseSequencingRule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'course_id',
        'rule_type',
        'related_course_id',
        'course_group',
        'min_grade',
        'min_courses_from_group',
        'sequence_order',
        'sequence_name',
        'is_strict',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'course_group' => 'array',
        'is_strict' => 'boolean',
        'is_active' => 'boolean'
    ];

    /**
     * Rule types
     */
    const TYPE_PREREQUISITE = 'prerequisite';
    const TYPE_COREQUISITE = 'corequisite';
    const TYPE_RECOMMENDED = 'recommended';
    const TYPE_PROHIBITED = 'prohibited';
    const TYPE_SEQUENCE = 'sequence';

    /**
     * Get the course this rule applies to
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the related course (for prerequisite/corequisite)
     */
    public function relatedCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'related_course_id');
    }

    /**
     * Check if a student meets this sequencing rule
     */
    public function checkForStudent(Student $student): array
    {
        $result = [
            'rule_type' => $this->rule_type,
            'is_met' => false,
            'message' => '',
            'details' => []
        ];

        switch ($this->rule_type) {
            case self::TYPE_PREREQUISITE:
                $result = $this->checkPrerequisite($student);
                break;
                
            case self::TYPE_COREQUISITE:
                $result = $this->checkCorequisite($student);
                break;
                
            case self::TYPE_RECOMMENDED:
                $result = $this->checkRecommended($student);
                break;
                
            case self::TYPE_PROHIBITED:
                $result = $this->checkProhibited($student);
                break;
                
            case self::TYPE_SEQUENCE:
                $result = $this->checkSequence($student);
                break;
        }

        return $result;
    }

    /**
     * Check prerequisite rule
     */
    private function checkPrerequisite(Student $student): array
    {
        $met = false;
        $message = '';

        if ($this->related_course_id) {
            // Check single course prerequisite
            $enrollment = $student->enrollments()
                ->where('section_id', function ($query) {
                    $query->select('id')
                        ->from('course_sections')
                        ->where('course_id', $this->related_course_id);
                })
                ->where('enrollment_status', 'completed')
                ->first();

            if ($enrollment) {
                // Check minimum grade if specified
                if ($this->min_grade) {
                    $met = $this->compareGrades($enrollment->final_grade, $this->min_grade) >= 0;
                    $message = $met 
                        ? "Prerequisite met with grade {$enrollment->final_grade}"
                        : "Prerequisite course completed but grade {$enrollment->final_grade} is below minimum {$this->min_grade}";
                } else {
                    $met = true;
                    $message = "Prerequisite course completed";
                }
            } else {
                $message = "Missing prerequisite: " . ($this->relatedCourse->course_code ?? 'Unknown course');
            }
        } elseif ($this->course_group) {
            // Check group prerequisite
            $completedCourses = $student->enrollments()
                ->whereHas('section.course', function ($query) {
                    $query->whereIn('course_code', $this->course_group);
                })
                ->where('enrollment_status', 'completed')
                ->count();

            $required = $this->min_courses_from_group ?? 1;
            $met = $completedCourses >= $required;
            $message = $met 
                ? "Completed {$completedCourses} of {$required} required prerequisite courses"
                : "Need to complete {$required} courses from prerequisite group, completed {$completedCourses}";
        }

        return [
            'rule_type' => 'prerequisite',
            'is_met' => $met,
            'message' => $message,
            'is_strict' => $this->is_strict
        ];
    }

    /**
     * Check corequisite rule
     */
    private function checkCorequisite(Student $student): array
    {
        // Check if student is currently enrolled in the corequisite course
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        
        $isEnrolled = $student->enrollments()
            ->where('term_id', $currentTerm->id ?? 0)
            ->whereHas('section.course', function ($query) {
                $query->where('id', $this->related_course_id);
            })
            ->whereIn('enrollment_status', ['enrolled', 'in_progress'])
            ->exists();

        // Also check if already completed
        $isCompleted = $student->enrollments()
            ->whereHas('section.course', function ($query) {
                $query->where('id', $this->related_course_id);
            })
            ->where('enrollment_status', 'completed')
            ->exists();

        $met = $isEnrolled || $isCompleted;

        return [
            'rule_type' => 'corequisite',
            'is_met' => $met,
            'message' => $met 
                ? 'Corequisite requirement met'
                : 'Must be taken concurrently with ' . ($this->relatedCourse->course_code ?? 'required course'),
            'is_strict' => $this->is_strict
        ];
    }

    /**
     * Check recommended rule
     */
    private function checkRecommended(Student $student): array
    {
        // Recommended courses are not strict requirements
        $completed = $student->enrollments()
            ->whereHas('section.course', function ($query) {
                $query->where('id', $this->related_course_id);
            })
            ->where('enrollment_status', 'completed')
            ->exists();

        return [
            'rule_type' => 'recommended',
            'is_met' => true, // Always "met" since it's just recommended
            'message' => $completed 
                ? 'Recommended prerequisite completed'
                : 'It is recommended to complete ' . ($this->relatedCourse->course_code ?? 'recommended course') . ' first',
            'is_strict' => false
        ];
    }

    /**
     * Check prohibited rule
     */
    private function checkProhibited(Student $student): array
    {
        // Check if student has taken or is taking the prohibited course
        $hasTaken = $student->enrollments()
            ->whereHas('section.course', function ($query) {
                $query->where('id', $this->related_course_id);
            })
            ->whereIn('enrollment_status', ['enrolled', 'in_progress', 'completed'])
            ->exists();

        return [
            'rule_type' => 'prohibited',
            'is_met' => !$hasTaken, // Met if NOT taken
            'message' => $hasTaken 
                ? 'Cannot take this course - already completed ' . ($this->relatedCourse->course_code ?? 'prohibited course')
                : 'No conflicts found',
            'is_strict' => true
        ];
    }

    /**
     * Check sequence rule
     */
    private function checkSequence(Student $student): array
    {
        // Check if previous courses in sequence are completed
        $previousInSequence = self::where('sequence_name', $this->sequence_name)
            ->where('sequence_order', '<', $this->sequence_order)
            ->pluck('course_id');

        $completedCount = $student->enrollments()
            ->whereHas('section.course', function ($query) use ($previousInSequence) {
                $query->whereIn('id', $previousInSequence);
            })
            ->where('enrollment_status', 'completed')
            ->count();

        $met = $completedCount === $previousInSequence->count();

        return [
            'rule_type' => 'sequence',
            'is_met' => $met,
            'message' => $met 
                ? 'Sequence requirements met'
                : "Must complete earlier courses in {$this->sequence_name} sequence",
            'is_strict' => $this->is_strict
        ];
    }

    /**
     * Compare grades
     */
    private function compareGrades(string $grade1, string $grade2): int
    {
        $gradeOrder = [
            'A' => 12, 'A-' => 11,
            'B+' => 10, 'B' => 9, 'B-' => 8,
            'C+' => 7, 'C' => 6, 'C-' => 5,
            'D+' => 4, 'D' => 3, 'D-' => 2,
            'F' => 1
        ];

        $value1 = $gradeOrder[$grade1] ?? 0;
        $value2 = $gradeOrder[$grade2] ?? 0;

        return $value1 - $value2;
    }

    /**
     * Scope for active rules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for strict rules
     */
    public function scopeStrict($query)
    {
        return $query->where('is_strict', true);
    }

    /**
     * Scope by rule type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('rule_type', $type);
    }
}