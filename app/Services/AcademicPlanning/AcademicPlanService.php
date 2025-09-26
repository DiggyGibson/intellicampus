<?php
// Save as: backend/app/Services/AcademicPlanning/AcademicPlanService.php

namespace App\Services\AcademicPlanning;

use App\Models\Student;
use App\Models\AcademicPlan;
use App\Models\PlanTerm;
use App\Models\PlanCourse;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\AcademicTerm;
use App\Models\RecommendedCoursePattern;
use App\Models\DegreeRequirement;
use App\Models\ProgramRequirement;
use App\Services\DegreeAudit\DegreeAuditService;
use App\Services\AcademicPlanning\PrerequisiteValidatorService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AcademicPlanService
{
    protected $auditService;
    protected $prerequisiteValidator;

    public function __construct(
        DegreeAuditService $auditService,
        PrerequisiteValidatorService $prerequisiteValidator
    ) {
        $this->auditService = $auditService;
        $this->prerequisiteValidator = $prerequisiteValidator;
    }

    /**
     * Create a new academic plan for a student
     */
    public function createPlan(Student $student, array $data): AcademicPlan
    {
        DB::beginTransaction();
        try {
            // Deactivate current plan if exists
            if ($data['make_current'] ?? false) {
                AcademicPlan::where('student_id', $student->id)
                    ->where('is_current', true)
                    ->update(['is_current' => false]);
            }

            // Create the plan
            $plan = AcademicPlan::create([
                'student_id' => $student->id,
                'plan_name' => $data['plan_name'],
                'description' => $data['description'] ?? null,
                'plan_type' => $data['plan_type'] ?? 'four_year',
                'primary_program_id' => $data['primary_program_id'] ?? $student->program_id,
                'minor_program_id' => $data['minor_program_id'] ?? null,
                'catalog_year' => $data['catalog_year'] ?? $this->getCurrentCatalogYear(),
                'start_date' => $data['start_date'] ?? Carbon::now(),
                'expected_graduation_date' => $data['expected_graduation_date'] ?? Carbon::now()->addYears(4),
                'total_terms' => $data['total_terms'] ?? 8,
                'status' => 'draft',
                'is_current' => $data['make_current'] ?? false
            ]);

            // Create terms for the plan
            $this->createPlanTerms($plan, $data['terms'] ?? []);

            // Apply template if requested
            if ($data['apply_template'] ?? false) {
                $this->applyRecommendedPattern($plan);
            }

            // Validate the plan
            $validation = $this->validatePlan($plan);
            $plan->is_valid = $validation['is_valid'];
            $plan->validation_errors = $validation['errors'];
            $plan->last_validated_at = now();
            $plan->save();

            DB::commit();
            return $plan;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create academic plan: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing academic plan
     */
    public function updatePlan(AcademicPlan $plan, array $data): AcademicPlan
    {
        DB::beginTransaction();
        try {
            $plan->update([
                'plan_name' => $data['plan_name'] ?? $plan->plan_name,
                'description' => $data['description'] ?? $plan->description,
                'expected_graduation_date' => $data['expected_graduation_date'] ?? $plan->expected_graduation_date,
                'status' => $data['status'] ?? $plan->status
            ]);

            // Re-validate the plan
            $validation = $this->validatePlan($plan);
            $plan->is_valid = $validation['is_valid'];
            $plan->validation_errors = $validation['errors'];
            $plan->last_validated_at = now();
            $plan->save();

            DB::commit();
            return $plan;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update academic plan: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create terms for an academic plan
     */
    protected function createPlanTerms(AcademicPlan $plan, array $termsData = []): void
    {
        if (empty($termsData)) {
            // Create default terms based on plan type
            $termsData = $this->generateDefaultTerms($plan);
        }

        foreach ($termsData as $index => $termData) {
            PlanTerm::create([
                'plan_id' => $plan->id,
                'term_id' => $termData['term_id'] ?? null,
                'sequence_number' => $index + 1,
                'term_name' => $termData['term_name'] ?? $this->generateTermName($index + 1),
                'term_type' => $termData['term_type'] ?? $this->getTermType($index + 1),
                'year' => $termData['year'] ?? $this->calculateTermYear($plan->start_date, $index + 1),
                'planned_credits' => $termData['planned_credits'] ?? 15,
                'min_credits' => $termData['min_credits'] ?? 12,
                'max_credits' => $termData['max_credits'] ?? 18,
                'status' => $termData['status'] ?? 'planned',
                'notes' => $termData['notes'] ?? null
            ]);
        }
    }

    /**
     * Add a course to a plan term
     */
    public function addCourseToPlan(PlanTerm $term, Course $course, array $data = []): PlanCourse
    {
        // Check if course already exists in term
        $existing = PlanCourse::where('plan_term_id', $term->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            throw new \Exception("Course already exists in this term");
        }

        // Validate prerequisites
        $student = $term->plan->student;
        $prereqValidation = $this->prerequisiteValidator->validateForStudent($student, $course);
        
        // Create plan course
        $planCourse = PlanCourse::create([
            'plan_term_id' => $term->id,
            'course_id' => $course->id,
            'section_id' => $data['section_id'] ?? null,
            'credits' => $course->credits,
            'status' => $data['status'] ?? 'planned',
            'satisfies_requirements' => $data['satisfies_requirements'] ?? $this->findSatisfiedRequirements($student, $course),
            'prerequisites_met' => $prereqValidation['can_register'],
            'corequisites_met' => true, // Will be checked separately
            'validation_warnings' => $prereqValidation['warnings'] ?? null,
            'alternative_courses' => $data['alternative_courses'] ?? null,
            'priority' => $data['priority'] ?? 1,
            'is_required' => $data['is_required'] ?? true,
            'is_backup' => $data['is_backup'] ?? false,
            'notes' => $data['notes'] ?? null
        ]);

        // Update term planned credits
        $this->updateTermCredits($term);

        // Re-validate the plan
        $this->validatePlan($term->plan);

        return $planCourse;
    }

    /**
     * Remove a course from a plan term
     */
    public function removeCourseFromPlan(PlanCourse $planCourse): void
    {
        $term = $planCourse->planTerm;
        $planCourse->delete();
        
        // Update term credits
        $this->updateTermCredits($term);
        
        // Re-validate the plan
        $this->validatePlan($term->plan);
    }

    /**
     * Validate an academic plan
     */
    public function validatePlan(AcademicPlan $plan): array
    {
        $errors = [];
        $warnings = [];
        $isValid = true;

        // Load all plan data
        $plan->load(['terms.courses.course', 'student']);

        // 1. Check prerequisite violations
        foreach ($plan->terms as $term) {
            foreach ($term->courses as $planCourse) {
                if (!$planCourse->prerequisites_met) {
                    $errors[] = "Prerequisites not met for {$planCourse->course->course_code} in {$term->term_name}";
                    $isValid = false;
                }
            }
        }

        // 2. Check credit limits
        foreach ($plan->terms as $term) {
            $totalCredits = $term->courses->sum('credits');
            if ($totalCredits > $term->max_credits) {
                $warnings[] = "{$term->term_name}: {$totalCredits} credits exceeds maximum of {$term->max_credits}";
            }
            if ($totalCredits < $term->min_credits && $term->status !== 'skipped') {
                $warnings[] = "{$term->term_name}: {$totalCredits} credits below minimum of {$term->min_credits}";
            }
        }

        // 3. Check degree requirements coverage
        $requirementsCoverage = $this->checkRequirementsCoverage($plan);
        if (!$requirementsCoverage['all_covered']) {
            foreach ($requirementsCoverage['missing'] as $missing) {
                $errors[] = "Missing requirement: {$missing}";
                $isValid = false;
            }
        }

        // 4. Check course sequencing
        $sequenceIssues = $this->checkCourseSequencing($plan);
        foreach ($sequenceIssues as $issue) {
            $errors[] = $issue;
            $isValid = false;
        }

        // 5. Check graduation timeline
        $totalPlannedCredits = $plan->terms->sum(function ($term) {
            return $term->courses->sum('credits');
        });
        
        $requiredCredits = $plan->primaryProgram->total_credits ?? 120;
        if ($totalPlannedCredits < $requiredCredits) {
            $errors[] = "Plan only covers {$totalPlannedCredits} of {$requiredCredits} required credits";
            $isValid = false;
        }

        return [
            'is_valid' => $isValid,
            'errors' => $errors,
            'warnings' => $warnings,
            'total_credits' => $totalPlannedCredits,
            'required_credits' => $requiredCredits
        ];
    }

    /**
     * Apply recommended course pattern to a plan
     */
    public function applyRecommendedPattern(AcademicPlan $plan): void
    {
        $patterns = RecommendedCoursePattern::where('program_id', $plan->primary_program_id)
            ->where('catalog_year', $plan->catalog_year)
            ->where('is_active', true)
            ->orderBy('term_number')
            ->get();

        foreach ($patterns as $pattern) {
            // Find corresponding plan term
            $planTerm = $plan->terms()->where('sequence_number', $pattern->term_number)->first();
            
            if (!$planTerm) {
                continue;
            }

            // Add required courses
            if ($pattern->required_courses) {
                foreach ($pattern->required_courses as $courseCode) {
                    $course = Course::where('course_code', $courseCode)->first();
                    if ($course) {
                        try {
                            $this->addCourseToPlan($planTerm, $course, ['is_required' => true]);
                        } catch (\Exception $e) {
                            Log::warning("Could not add course {$courseCode} to plan: " . $e->getMessage());
                        }
                    }
                }
            }

            // Add recommended courses
            if ($pattern->recommended_courses) {
                foreach ($pattern->recommended_courses as $courseCode) {
                    $course = Course::where('course_code', $courseCode)->first();
                    if ($course) {
                        try {
                            $this->addCourseToPlan($planTerm, $course, ['is_required' => false]);
                        } catch (\Exception $e) {
                            Log::warning("Could not add recommended course {$courseCode}: " . $e->getMessage());
                        }
                    }
                }
            }
        }
    }

    /**
     * Check if all degree requirements are covered by the plan
     */
    protected function checkRequirementsCoverage(AcademicPlan $plan): array
    {
        $student = $plan->student;
        $programRequirements = ProgramRequirement::where('program_id', $plan->primary_program_id)
            ->where('catalog_year', $plan->catalog_year)
            ->where('is_active', true)
            ->with('requirement')
            ->get();

        $plannedCourses = [];
        foreach ($plan->terms as $term) {
            foreach ($term->courses as $planCourse) {
                $plannedCourses[] = $planCourse->course_id;
            }
        }

        $missingRequirements = [];
        $allCovered = true;

        foreach ($programRequirements as $programReq) {
            $requirement = $programReq->requirement;
            
            // Check if requirement is covered by planned courses
            if ($requirement->requirement_type === 'specific_courses') {
                $requiredCourses = $requirement->parameters['required_courses'] ?? [];
                foreach ($requiredCourses as $courseCode) {
                    $course = Course::where('course_code', $courseCode)->first();
                    if ($course && !in_array($course->id, $plannedCourses)) {
                        $missingRequirements[] = "{$requirement->name}: {$courseCode}";
                        $allCovered = false;
                    }
                }
            }
        }

        return [
            'all_covered' => $allCovered,
            'missing' => $missingRequirements
        ];
    }

    /**
     * Check course sequencing in the plan
     */
    protected function checkCourseSequencing(AcademicPlan $plan): array
    {
        $issues = [];
        $coursesByTerm = [];

        // Build course timeline
        foreach ($plan->terms as $term) {
            $coursesByTerm[$term->sequence_number] = $term->courses->pluck('course_id')->toArray();
        }

        // Check each course's prerequisites
        foreach ($plan->terms as $term) {
            foreach ($term->courses as $planCourse) {
                $course = $planCourse->course;
                
                // Get prerequisites
                $prerequisites = $course->prerequisites ?? [];
                
                foreach ($prerequisites as $prereqCode) {
                    $prereqCourse = Course::where('course_code', $prereqCode)->first();
                    if (!$prereqCourse) continue;
                    
                    // Check if prerequisite is taken before this term
                    $prereqTaken = false;
                    for ($i = 1; $i < $term->sequence_number; $i++) {
                        if (isset($coursesByTerm[$i]) && in_array($prereqCourse->id, $coursesByTerm[$i])) {
                            $prereqTaken = true;
                            break;
                        }
                    }
                    
                    if (!$prereqTaken) {
                        $issues[] = "{$course->course_code} in {$term->term_name} requires {$prereqCode} as prerequisite";
                    }
                }
            }
        }

        return $issues;
    }

    /**
     * Update term planned credits
     */
    protected function updateTermCredits(PlanTerm $term): void
    {
        $totalCredits = $term->courses->sum('credits');
        $term->planned_credits = $totalCredits;
        $term->save();
    }

    /**
     * Find which requirements a course satisfies
     */
    protected function findSatisfiedRequirements(Student $student, Course $course): array
    {
        $satisfiedRequirements = [];
        
        // Get course-requirement mappings
        $mappings = $course->requirementMappings()
            ->with('requirement')
            ->where('is_active', true)
            ->get();
        
        foreach ($mappings as $mapping) {
            $satisfiedRequirements[] = $mapping->requirement->id;
        }
        
        return $satisfiedRequirements;
    }

    /**
     * Generate default terms for a plan
     */
    protected function generateDefaultTerms(AcademicPlan $plan): array
    {
        $terms = [];
        $startDate = Carbon::parse($plan->start_date);
        
        for ($i = 0; $i < $plan->total_terms; $i++) {
            $termType = $this->getTermType($i + 1);
            $year = $this->calculateTermYear($startDate, $i + 1);
            
            $terms[] = [
                'term_name' => $this->generateTermName($i + 1),
                'term_type' => $termType,
                'year' => $year,
                'planned_credits' => 15,
                'min_credits' => 12,
                'max_credits' => 18,
                'status' => 'planned'
            ];
        }
        
        return $terms;
    }

    /**
     * Generate term name based on sequence
     */
    protected function generateTermName(int $sequence): string
    {
        $year = ceil($sequence / 2);
        $semester = ($sequence % 2 === 1) ? 'Fall' : 'Spring';
        
        $yearNames = [
            1 => 'Freshman',
            2 => 'Sophomore',
            3 => 'Junior',
            4 => 'Senior'
        ];
        
        $yearName = $yearNames[$year] ?? "Year {$year}";
        return "{$yearName} {$semester}";
    }

    /**
     * Get term type based on sequence
     */
    protected function getTermType(int $sequence): string
    {
        return ($sequence % 2 === 1) ? 'fall' : 'spring';
    }

    /**
     * Calculate term year based on start date and sequence
     */
    protected function calculateTermYear(Carbon $startDate, int $sequence): int
    {
        $yearOffset = floor(($sequence - 1) / 2);
        return $startDate->year + $yearOffset;
    }

    /**
     * Get current catalog year
     */
    protected function getCurrentCatalogYear(): string
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        
        if ($month < 8) {
            return ($year - 1) . '-' . $year;
        }
        
        return $year . '-' . ($year + 1);
    }
}