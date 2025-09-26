<?php
// Save as: backend/app/Models/RecommendedCoursePattern.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecommendedCoursePattern extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'program_id',
        'catalog_year',
        'term_number',
        'term_type',
        'term_name',
        'required_courses',
        'recommended_courses',
        'elective_options',
        'recommended_credits',
        'min_credits',
        'max_credits',
        'milestones',
        'notes',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'required_courses' => 'array',
        'recommended_courses' => 'array',
        'elective_options' => 'array',
        'milestones' => 'array',
        'recommended_credits' => 'decimal:1',
        'min_credits' => 'decimal:1',
        'max_credits' => 'decimal:1',
        'is_active' => 'boolean'
    ];

    /**
     * Get the program this pattern belongs to
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    /**
     * Get a formatted term label
     */
    public function getTermLabelAttribute(): string
    {
        return $this->term_name ?? $this->generateTermName();
    }

    /**
     * Generate a term name based on term number
     */
    private function generateTermName(): string
    {
        $year = ceil($this->term_number / 2);
        $yearNames = [
            1 => 'Freshman',
            2 => 'Sophomore',
            3 => 'Junior',
            4 => 'Senior',
            5 => 'Fifth Year',
            6 => 'Sixth Year'
        ];

        $yearName = $yearNames[$year] ?? "Year {$year}";
        $termType = ucfirst($this->term_type);

        return "{$yearName} {$termType}";
    }

    /**
     * Get courses for this term pattern
     */
    public function getCoursesForTerm(): array
    {
        return [
            'required' => $this->getCoursesFromCodes($this->required_courses ?? []),
            'recommended' => $this->getCoursesFromCodes($this->recommended_courses ?? []),
            'electives' => $this->getCoursesFromCodes($this->elective_options ?? [])
        ];
    }

    /**
     * Get course models from course codes
     */
    private function getCoursesFromCodes(array $courseCodes): array
    {
        if (empty($courseCodes)) {
            return [];
        }

        return Course::whereIn('course_code', $courseCodes)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'code' => $course->course_code,
                    'title' => $course->title,
                    'credits' => $course->credits,
                    'description' => $course->description
                ];
            })
            ->toArray();
    }

    /**
     * Apply this pattern to a student's academic plan
     */
    public function applyToPlan(AcademicPlan $plan): PlanTerm
    {
        // Create or update a plan term based on this pattern
        $planTerm = PlanTerm::firstOrNew([
            'plan_id' => $plan->id,
            'sequence_number' => $this->term_number
        ]);

        $planTerm->fill([
            'term_name' => $this->term_name,
            'term_type' => $this->term_type,
            'year' => $plan->start_date->year + floor(($this->term_number - 1) / 2),
            'planned_credits' => $this->recommended_credits,
            'min_credits' => $this->min_credits,
            'max_credits' => $this->max_credits,
            'status' => 'planned'
        ]);

        $planTerm->save();

        // Add required courses to the plan term
        $this->addCoursesToPlanTerm($planTerm, $this->required_courses ?? [], true);
        
        // Add recommended courses
        $this->addCoursesToPlanTerm($planTerm, $this->recommended_courses ?? [], false);

        return $planTerm;
    }

    /**
     * Add courses to a plan term
     */
    private function addCoursesToPlanTerm(PlanTerm $planTerm, array $courseCodes, bool $isRequired): void
    {
        $courses = Course::whereIn('course_code', $courseCodes)->get();

        foreach ($courses as $course) {
            PlanCourse::firstOrCreate([
                'plan_term_id' => $planTerm->id,
                'course_id' => $course->id
            ], [
                'credits' => $course->credits,
                'status' => 'planned',
                'is_required' => $isRequired,
                'priority' => $isRequired ? 1 : 2
            ]);
        }
    }

    /**
     * Check if a student is following this pattern
     */
    public function checkStudentProgress(Student $student, int $termNumber): array
    {
        $result = [
            'on_track' => true,
            'completed_required' => [],
            'missing_required' => [],
            'completed_recommended' => [],
            'missing_recommended' => [],
            'milestones_met' => [],
            'milestones_pending' => []
        ];

        // Check required courses
        foreach ($this->required_courses ?? [] as $courseCode) {
            $completed = $student->enrollments()
                ->whereHas('section.course', function ($query) use ($courseCode) {
                    $query->where('course_code', $courseCode);
                })
                ->where('enrollment_status', 'completed')
                ->exists();

            if ($completed) {
                $result['completed_required'][] = $courseCode;
            } else {
                $result['missing_required'][] = $courseCode;
                $result['on_track'] = false;
            }
        }

        // Check recommended courses
        foreach ($this->recommended_courses ?? [] as $courseCode) {
            $completed = $student->enrollments()
                ->whereHas('section.course', function ($query) use ($courseCode) {
                    $query->where('course_code', $courseCode);
                })
                ->where('enrollment_status', 'completed')
                ->exists();

            if ($completed) {
                $result['completed_recommended'][] = $courseCode;
            } else {
                $result['missing_recommended'][] = $courseCode;
            }
        }

        // Check milestones
        foreach ($this->milestones ?? [] as $milestone) {
            // This would need custom logic based on milestone type
            // For now, just tracking them
            $result['milestones_pending'][] = $milestone;
        }

        return $result;
    }

    /**
     * Get all patterns for a program
     */
    public static function getProgramPattern(int $programId, string $catalogYear)
    {
        return self::where('program_id', $programId)
            ->where('catalog_year', $catalogYear)
            ->where('is_active', true)
            ->orderBy('term_number')
            ->get();
    }

    /**
     * Generate a complete 4-year plan from patterns
     */
    public static function generateFourYearPlan(int $programId, string $catalogYear): array
    {
        $patterns = self::getProgramPattern($programId, $catalogYear);
        
        $plan = [];
        foreach ($patterns as $pattern) {
            $plan["Term {$pattern->term_number}"] = [
                'name' => $pattern->term_name,
                'type' => $pattern->term_type,
                'credits' => $pattern->recommended_credits,
                'required_courses' => $pattern->required_courses,
                'recommended_courses' => $pattern->recommended_courses,
                'electives' => $pattern->elective_options,
                'milestones' => $pattern->milestones
            ];
        }

        return $plan;
    }

    /**
     * Scope for active patterns
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by program
     */
    public function scopeForProgram($query, int $programId)
    {
        return $query->where('program_id', $programId);
    }

    /**
     * Scope by catalog year
     */
    public function scopeForCatalogYear($query, string $catalogYear)
    {
        return $query->where('catalog_year', $catalogYear);
    }
}