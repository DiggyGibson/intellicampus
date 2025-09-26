<?php
// Save as: backend/app/Models/DegreeRequirement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DegreeRequirement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'category_id',
        'code',
        'name',
        'description',
        'requirement_type',
        'parameters',
        'display_order',
        'is_required',
        'is_active',
        'effective_from',
        'effective_until'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'parameters' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date'
    ];

    /**
     * Requirement types
     */
    const TYPE_CREDIT_HOURS = 'credit_hours';
    const TYPE_COURSE_COUNT = 'course_count';
    const TYPE_SPECIFIC_COURSES = 'specific_courses';
    const TYPE_COURSE_LIST = 'course_list';
    const TYPE_GPA = 'gpa';
    const TYPE_RESIDENCY = 'residency';
    const TYPE_MILESTONE = 'milestone';
    const TYPE_OTHER = 'other';

    /**
     * Get the category this requirement belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(RequirementCategory::class, 'category_id');
    }

    /**
     * Get the program requirements that use this degree requirement
     */
    public function programRequirements(): HasMany
    {
        return $this->hasMany(ProgramRequirement::class, 'requirement_id');
    }

    /**
     * Get the courses that can fulfill this requirement
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_requirement_mappings', 'requirement_id', 'course_id')
            ->withPivot('fulfillment_type', 'credit_value', 'min_grade', 'effective_from', 'effective_until', 'is_active')
            ->withTimestamps();
    }

    /**
     * Get active courses that fulfill this requirement
     */
    public function activeCourses()
    {
        return $this->courses()
            ->wherePivot('is_active', true)
            ->where(function ($query) {
                $query->whereNull('course_requirement_mappings.effective_from')
                    ->orWhere('course_requirement_mappings.effective_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('course_requirement_mappings.effective_until')
                    ->orWhere('course_requirement_mappings.effective_until', '>=', now());
            });
    }

    /**
     * Get student progress records for this requirement
     */
    public function studentProgress(): HasMany
    {
        return $this->hasMany(StudentDegreeProgress::class, 'requirement_id');
    }

    /**
     * Get student course applications for this requirement
     */
    public function studentCourseApplications(): HasMany
    {
        return $this->hasMany(StudentCourseApplication::class, 'requirement_id');
    }

    /**
     * Get substitutions for this requirement
     */
    public function substitutions(): HasMany
    {
        return $this->hasMany(RequirementSubstitution::class, 'requirement_id');
    }

    /**
     * Check if this requirement is currently effective
     */
    public function isEffective(): bool
    {
        $now = now();
        
        if ($this->effective_from && $this->effective_from > $now) {
            return false;
        }
        
        if ($this->effective_until && $this->effective_until < $now) {
            return false;
        }
        
        return $this->is_active;
    }

    /**
     * Get the minimum credits required from parameters
     */
    public function getMinCredits(): ?float
    {
        return $this->parameters['min_credits'] ?? null;
    }

    /**
     * Get the minimum courses required from parameters
     */
    public function getMinCourses(): ?int
    {
        return $this->parameters['min_courses'] ?? null;
    }

    /**
     * Get the minimum GPA required from parameters
     */
    public function getMinGPA(): ?float
    {
        return $this->parameters['min_gpa'] ?? null;
    }

    /**
     * Get the minimum grade required from parameters
     */
    public function getMinGrade(): ?string
    {
        return $this->parameters['min_grade'] ?? null;
    }

    /**
     * Get required course codes from parameters
     */
    public function getRequiredCourses(): array
    {
        return $this->parameters['required_courses'] ?? [];
    }

    /**
     * Get course options to choose from
     */
    public function getCourseOptions(): array
    {
        return $this->parameters['choose_from'] ?? [];
    }

    /**
     * Get minimum number of courses to choose from options
     */
    public function getMinToChoose(): ?int
    {
        return $this->parameters['min_to_choose'] ?? null;
    }

    /**
     * Check if pass/fail grades are allowed
     */
    public function allowsPassFail(): bool
    {
        return $this->parameters['allow_pass_fail'] ?? false;
    }

    /**
     * Evaluate if a student meets this requirement
     * This is a simplified version - the full logic would be in the service
     */
    public function evaluateForStudent(Student $student): array
    {
        $result = [
            'requirement_id' => $this->id,
            'requirement_name' => $this->name,
            'is_satisfied' => false,
            'progress_percentage' => 0,
            'details' => []
        ];

        switch ($this->requirement_type) {
            case self::TYPE_CREDIT_HOURS:
                $result = $this->evaluateCreditHours($student);
                break;
                
            case self::TYPE_COURSE_COUNT:
                $result = $this->evaluateCourseCount($student);
                break;
                
            case self::TYPE_SPECIFIC_COURSES:
                $result = $this->evaluateSpecificCourses($student);
                break;
                
            case self::TYPE_GPA:
                $result = $this->evaluateGPA($student);
                break;
                
            // Add other evaluation methods as needed
        }

        return $result;
    }

    /**
     * Evaluate credit hour requirement
     */
    private function evaluateCreditHours(Student $student): array
    {
        $minCredits = $this->getMinCredits();
        if (!$minCredits) {
            return ['is_satisfied' => true, 'progress_percentage' => 100];
        }

        // Get completed credits that apply to this requirement
        $completedCredits = $student->courseApplications()
            ->where('requirement_id', $this->id)
            ->where('status', 'completed')
            ->sum('credits_applied');

        $percentage = min(100, ($completedCredits / $minCredits) * 100);

        return [
            'is_satisfied' => $completedCredits >= $minCredits,
            'progress_percentage' => $percentage,
            'completed_credits' => $completedCredits,
            'required_credits' => $minCredits,
            'remaining_credits' => max(0, $minCredits - $completedCredits)
        ];
    }

    /**
     * Evaluate course count requirement
     */
    private function evaluateCourseCount(Student $student): array
    {
        $minCourses = $this->getMinCourses();
        if (!$minCourses) {
            return ['is_satisfied' => true, 'progress_percentage' => 100];
        }

        // Get completed courses that apply to this requirement
        $completedCourses = $student->courseApplications()
            ->where('requirement_id', $this->id)
            ->where('status', 'completed')
            ->count();

        $percentage = min(100, ($completedCourses / $minCourses) * 100);

        return [
            'is_satisfied' => $completedCourses >= $minCourses,
            'progress_percentage' => $percentage,
            'completed_courses' => $completedCourses,
            'required_courses' => $minCourses,
            'remaining_courses' => max(0, $minCourses - $completedCourses)
        ];
    }

    /**
     * Evaluate specific courses requirement
     */
    private function evaluateSpecificCourses(Student $student): array
    {
        $requiredCourses = $this->getRequiredCourses();
        if (empty($requiredCourses)) {
            return ['is_satisfied' => true, 'progress_percentage' => 100];
        }

        // Get completed required courses
        $completedCourses = $student->enrollments()
            ->whereHas('section.course', function ($query) use ($requiredCourses) {
                $query->whereIn('course_code', $requiredCourses);
            })
            ->where('enrollment_status', 'completed')
            ->with('section.course')
            ->get()
            ->pluck('section.course.course_code')
            ->unique()
            ->values()
            ->toArray();

        $remainingCourses = array_diff($requiredCourses, $completedCourses);
        $percentage = (count($completedCourses) / count($requiredCourses)) * 100;

        return [
            'is_satisfied' => empty($remainingCourses),
            'progress_percentage' => $percentage,
            'required_courses' => $requiredCourses,
            'completed_courses' => $completedCourses,
            'remaining_courses' => array_values($remainingCourses)
        ];
    }

    /**
     * Evaluate GPA requirement
     */
    private function evaluateGPA(Student $student): array
    {
        $minGPA = $this->getMinGPA();
        if (!$minGPA) {
            return ['is_satisfied' => true, 'progress_percentage' => 100];
        }

        // This would typically calculate GPA for courses in this requirement
        $currentGPA = $student->current_gpa ?? 0;

        return [
            'is_satisfied' => $currentGPA >= $minGPA,
            'progress_percentage' => $currentGPA >= $minGPA ? 100 : ($currentGPA / $minGPA) * 100,
            'current_gpa' => $currentGPA,
            'required_gpa' => $minGPA,
            'gpa_difference' => round($minGPA - $currentGPA, 2)
        ];
    }

    /**
     * Scope for active requirements
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            });
    }

    /**
     * Scope for required (non-elective) requirements
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope by category type
     */
    public function scopeByCategoryType($query, $type)
    {
        return $query->whereHas('category', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }
}