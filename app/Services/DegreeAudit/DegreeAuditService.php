<?php
// Save as: backend/app/Services/DegreeAudit/DegreeAuditService.php

namespace App\Services\DegreeAudit;

use App\Models\Student;
use App\Models\DegreeRequirement;
use App\Models\ProgramRequirement;
use App\Models\StudentDegreeProgress;
use App\Models\DegreeAuditReport;
use App\Models\StudentCourseApplication;
use App\Models\RequirementCategory;
use App\Models\Enrollment;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DegreeAuditService
{
    protected $requirementEvaluator;
    protected $progressCalculator;
    protected $gpaCalculator;
    protected $courseMatcher;
    
    public function __construct(
        RequirementEvaluatorService $requirementEvaluator = null,
        ProgressCalculatorService $progressCalculator = null,
        GPACalculatorService $gpaCalculator = null,
        CourseMatcherService $courseMatcher = null
    ) {
        $this->requirementEvaluator = $requirementEvaluator ?: new RequirementEvaluatorService();
        $this->progressCalculator = $progressCalculator ?: new ProgressCalculatorService();
        $this->gpaCalculator = $gpaCalculator ?: new GPACalculatorService();
        $this->courseMatcher = $courseMatcher ?: new CourseMatcherService();
    }

    /**
     * Run a complete degree audit for a student
     */
    public function runAudit(Student $student, array $options = []): DegreeAuditReport
    {
        try {
            DB::beginTransaction();

            // Get student's program
            $program = $this->getStudentProgram($student);
            if (!$program) {
                throw new \Exception("Student has no program assigned");
            }
            
            $catalogYear = $options['catalog_year'] ?? $this->getCurrentCatalogYear();
            $termId = $options['term_id'] ?? $this->getCurrentTermId();

            // Initialize audit report
            $report = $this->initializeAuditReport($student, $program, $termId, $catalogYear);

            // Get all program requirements
            $programRequirements = $this->getProgramRequirements($program->id, $catalogYear);

            // If no requirements found, create a basic report
            if ($programRequirements->isEmpty()) {
                $report->requirements_summary = [];
                $report->completed_requirements = [];
                $report->in_progress_requirements = [];
                $report->remaining_requirements = [];
                $report->recommendations = ['No requirements found for this program'];
                $report->save();
                DB::commit();
                return $report;
            }

            // Process requirements by category
            $requirementsByCategory = $this->groupRequirementsByCategory($programRequirements);
            $overallProgress = [];

            foreach ($requirementsByCategory as $categoryId => $requirements) {
                $categoryProgress = $this->processCategoryRequirements(
                    $student,
                    $requirements,
                    $categoryId
                );
                $overallProgress[$categoryId] = $categoryProgress;
            }

            // Calculate overall statistics
            $overallStats = $this->calculateOverallStatistics($student, $overallProgress);

            // Update the audit report
            $report = $this->updateAuditReport($report, $overallProgress, $overallStats);

            // Check graduation eligibility
            $graduationStatus = $this->checkGraduationEligibility($student, $overallStats);
            $report->graduation_eligible = $graduationStatus['eligible'];
            $report->expected_graduation_date = $graduationStatus['expected_date'];
            $report->terms_to_completion = $graduationStatus['terms_remaining'];

            // Generate recommendations
            $recommendations = $this->generateRecommendations($student, $overallProgress);
            $report->recommendations = $recommendations;

            // Save the report
            $report->save();

            // Update student degree progress records
            $this->updateStudentProgress($student, $overallProgress);

            DB::commit();
            return $report;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Degree audit failed: ' . $e->getMessage(), [
                'student_id' => $student->id,
                'error' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get student's program
     */
    protected function getStudentProgram(Student $student)
    {
        // Try relationship first
        if ($student->program) {
            return $student->program;
        }
        
        // Try by program_id
        if ($student->program_id) {
            return AcademicProgram::find($student->program_id);
        }
        
        return null;
    }

    /**
     * Initialize a new audit report
     */
    protected function initializeAuditReport(
        Student $student,
        AcademicProgram $program,
        int $termId,
        string $catalogYear
    ): DegreeAuditReport {
        return new DegreeAuditReport([
            'student_id' => $student->id,
            'program_id' => $program->id,
            'term_id' => $termId,
            'report_type' => 'unofficial',
            'catalog_year' => $catalogYear,
            'cumulative_gpa' => $student->cumulative_gpa ?? 0,
            'major_gpa' => $this->calculateMajorGPA($student, $program),
            'minor_gpa' => 0,
            'total_credits_required' => 120, // Default
            'total_credits_completed' => $student->credits_earned ?? 0,
            'total_credits_in_progress' => 0,
            'total_credits_remaining' => 120 - ($student->credits_earned ?? 0),
            'overall_completion_percentage' => 0,
            'generated_by' => auth()->id() ?? 1,
            'generated_at' => now()
        ]);
    }

    /**
     * Get all program requirements
     */
    protected function getProgramRequirements(int $programId, string $catalogYear)
    {
        $requirements = ProgramRequirement::with(['requirement.category', 'requirement.courses'])
            ->where('program_id', $programId)
            ->where('catalog_year', $catalogYear)
            ->where('is_active', true)
            ->get();

        // If no requirements found for this catalog year, try without catalog year
        if ($requirements->isEmpty()) {
            $requirements = ProgramRequirement::with(['requirement.category', 'requirement.courses'])
                ->where('program_id', $programId)
                ->where('is_active', true)
                ->get();
        }

        return $requirements;
    }

    /**
     * Group requirements by category
     */
    protected function groupRequirementsByCategory($programRequirements)
    {
        return $programRequirements->groupBy(function ($item) {
            return $item->requirement->category_id;
        });
    }

    /**
     * Process all requirements in a category
     */
    protected function processCategoryRequirements(
        Student $student,
        $requirements,
        int $categoryId
    ): array {
        $category = RequirementCategory::find($categoryId);
        $categoryProgress = [
            'category_id' => $categoryId,
            'category_name' => $category->name ?? 'Unknown',
            'category_type' => $category->type ?? 'other',
            'requirements' => [],
            'total_required' => 0,
            'total_completed' => 0,
            'total_in_progress' => 0,
            'total_remaining' => 0,
            'is_satisfied' => false,
            'completion_percentage' => 0
        ];

        foreach ($requirements as $programRequirement) {
            $requirement = $programRequirement->requirement;
            
            // Evaluate this requirement for the student
            $evaluation = $this->evaluateRequirement($student, $requirement, $programRequirement);
            
            // Track progress
            $categoryProgress['requirements'][] = $evaluation;
            
            // Update category totals based on requirement type
            if ($requirement->requirement_type === 'credit_hours') {
                $categoryProgress['total_required'] += $evaluation['credits_required'] ?? 0;
                $categoryProgress['total_completed'] += $evaluation['credits_completed'] ?? 0;
                $categoryProgress['total_in_progress'] += $evaluation['credits_in_progress'] ?? 0;
                $categoryProgress['total_remaining'] += $evaluation['credits_remaining'] ?? 0;
            } elseif ($requirement->requirement_type === 'course_count') {
                $categoryProgress['total_required'] += $evaluation['courses_required'] ?? 0;
                $categoryProgress['total_completed'] += $evaluation['courses_completed'] ?? 0;
                $categoryProgress['total_in_progress'] += $evaluation['courses_in_progress'] ?? 0;
                $categoryProgress['total_remaining'] += $evaluation['courses_remaining'] ?? 0;
            }
        }

        // Calculate category completion percentage
        if ($categoryProgress['total_required'] > 0) {
            $categoryProgress['completion_percentage'] = min(100, 
                ($categoryProgress['total_completed'] / $categoryProgress['total_required']) * 100
            );
        }

        // Check if category is satisfied
        $categoryProgress['is_satisfied'] = $this->isCategorySatisfied($categoryProgress);

        return $categoryProgress;
    }

    /**
     * Evaluate a single requirement
     */
    protected function evaluateRequirement(
        Student $student, 
        DegreeRequirement $requirement, 
        ProgramRequirement $programRequirement
    ): array {
        // Use the requirement evaluator service if available
        if ($this->requirementEvaluator) {
            return $this->requirementEvaluator->evaluate($student, $requirement, $programRequirement);
        }
        
        // Fallback to basic evaluation
        $parameters = $requirement->parameters;
        if (is_string($parameters)) {
            $parameters = json_decode($parameters, true);
        }
        
        $evaluation = [
            'requirement_id' => $requirement->id,
            'requirement_name' => $requirement->name,
            'requirement_type' => $requirement->requirement_type,
            'is_required' => $requirement->is_required,
            'is_satisfied' => false,
            'progress_percentage' => 0,
            'credits_required' => 0,
            'credits_completed' => 0,
            'credits_in_progress' => 0,
            'credits_remaining' => 0
        ];

        // Basic evaluation based on type
        switch ($requirement->requirement_type) {
            case 'credit_hours':
                $minCredits = $parameters['min_credits'] ?? 0;
                $completed = min($minCredits, $student->credits_earned ?? 0);
                $evaluation['credits_required'] = $minCredits;
                $evaluation['credits_completed'] = $completed;
                $evaluation['credits_remaining'] = max(0, $minCredits - $completed);
                $evaluation['progress_percentage'] = $minCredits > 0 ? ($completed / $minCredits) * 100 : 0;
                $evaluation['is_satisfied'] = $completed >= $minCredits;
                break;
                
            case 'gpa':
                $minGPA = $parameters['min_gpa'] ?? 2.0;
                $currentGPA = $student->cumulative_gpa ?? 0;
                $evaluation['is_satisfied'] = $currentGPA >= $minGPA;
                $evaluation['progress_percentage'] = $evaluation['is_satisfied'] ? 100 : ($currentGPA / $minGPA) * 100;
                break;
                
            default:
                // For other types, default to not satisfied
                $evaluation['is_satisfied'] = false;
                $evaluation['progress_percentage'] = 0;
        }

        return $evaluation;
    }

    /**
     * Check if a category is satisfied
     */
    protected function isCategorySatisfied(array $categoryProgress): bool
    {
        // All requirements in the category must be satisfied
        foreach ($categoryProgress['requirements'] as $requirement) {
            if (!$requirement['is_satisfied'] && $requirement['is_required']) {
                return false;
            }
        }
        return true;
    }

    /**
     * Calculate overall statistics
     */
    protected function calculateOverallStatistics(Student $student, array $overallProgress): array
    {
        $stats = [
            'total_credits_required' => 120, // Default program requirement
            'total_credits_completed' => 0,
            'total_credits_in_progress' => 0,
            'total_credits_remaining' => 0,
            'overall_completion_percentage' => 0,
            'categories_satisfied' => 0,
            'total_categories' => count($overallProgress),
            'gpa_requirements_met' => true,
            'residency_requirements_met' => false,
            'requirement_details' => []
        ];

        // Get ACTUAL credits from student's enrollments (not from requirements)
        $completedCredits = $student->enrollments()
            ->where('enrollment_status', 'completed')
            ->whereNotNull('final_grade')
            ->whereNotIn('final_grade', ['F', 'W', 'WF', 'I'])
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->sum('courses.credits');

        $inProgressCredits = $student->enrollments()
            ->whereIn('enrollment_status', ['enrolled', 'in_progress', 'active'])
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->sum('courses.credits');

        // Get the total credit requirement from the University Requirements category
        $totalCreditReq = 120; // Default
        foreach ($overallProgress as $category) {
            if ($category['category_type'] === 'university') {
                foreach ($category['requirements'] as $req) {
                    if ($req['requirement_name'] === 'Total Credit Hours' && isset($req['credits_required'])) {
                        $totalCreditReq = $req['credits_required'];
                        break 2;
                    }
                }
            }
        }

        // Check satisfaction of each category (but don't sum credits from all categories)
        foreach ($overallProgress as $category) {
            if ($category['is_satisfied']) {
                $stats['categories_satisfied']++;
            }
            
            // Store requirement details for debugging
            $stats['requirement_details'][$category['category_name']] = [
                'satisfied' => $category['is_satisfied'],
                'requirements_count' => count($category['requirements'])
            ];
        }

        // Set the correct values
        $stats['total_credits_required'] = $totalCreditReq;
        $stats['total_credits_completed'] = $completedCredits;
        $stats['total_credits_in_progress'] = $inProgressCredits;
        $stats['total_credits_remaining'] = max(0, $totalCreditReq - $completedCredits);
        
        // Calculate overall completion percentage based on actual credits
        if ($stats['total_credits_required'] > 0) {
            $stats['overall_completion_percentage'] = min(100,
                ($stats['total_credits_completed'] / $stats['total_credits_required']) * 100
            );
        }
        
        // Check residency requirements
        $residencyCredits = $this->calculateResidencyCredits($student);
        $stats['residency_requirements_met'] = $residencyCredits >= 30;
        $stats['residency_credits'] = $residencyCredits;

        // Check GPA requirements
        $minGPA = 2.0;
        $stats['gpa_requirements_met'] = ($student->cumulative_gpa ?? 0) >= $minGPA;

        return $stats;
    }

    /**
     * Update the audit report
     */
    protected function updateAuditReport(
        DegreeAuditReport $report,
        array $overallProgress,
        array $overallStats
    ): DegreeAuditReport {
        $report->total_credits_required = $overallStats['total_credits_required'] ?: 120;
        $report->total_credits_completed = $overallStats['total_credits_completed'];
        $report->total_credits_in_progress = $overallStats['total_credits_in_progress'];
        $report->total_credits_remaining = $overallStats['total_credits_remaining'];
        $report->overall_completion_percentage = $overallStats['overall_completion_percentage'];
        
        // Organize requirements by status
        $completedReqs = [];
        $inProgressReqs = [];
        $remainingReqs = [];
        
        foreach ($overallProgress as $category) {
            foreach ($category['requirements'] as $req) {
                if ($req['is_satisfied']) {
                    $completedReqs[] = $req;
                } elseif ($req['progress_percentage'] > 0) {
                    $inProgressReqs[] = $req;
                } else {
                    $remainingReqs[] = $req;
                }
            }
        }
        
        $report->requirements_summary = $overallProgress;
        $report->completed_requirements = $completedReqs;
        $report->in_progress_requirements = $inProgressReqs;
        $report->remaining_requirements = $remainingReqs;
        
        return $report;
    }

    /**
     * Check graduation eligibility
     */
    protected function checkGraduationEligibility(Student $student, array $overallStats): array
    {
        $eligible = true;
        $reasons = [];
        
        // Check credit requirements
        if ($overallStats['total_credits_remaining'] > 0) {
            $eligible = false;
            $reasons[] = "Need {$overallStats['total_credits_remaining']} more credits";
        }
        
        // Check GPA requirements
        $minGPA = 2.0;
        if (($student->cumulative_gpa ?? 0) < $minGPA) {
            $eligible = false;
            $reasons[] = "GPA below minimum ({$student->cumulative_gpa} < {$minGPA})";
        }
        
        // Check residency requirements
        if (!$overallStats['residency_requirements_met']) {
            $eligible = false;
            $reasons[] = "Residency requirement not met";
        }
        
        // Calculate expected graduation date
        $creditsPerTerm = 15;
        $termsRemaining = $overallStats['total_credits_remaining'] > 0 
            ? ceil($overallStats['total_credits_remaining'] / $creditsPerTerm) 
            : 0;
        $expectedDate = Carbon::now()->addMonths($termsRemaining * 4);
        
        return [
            'eligible' => $eligible,
            'reasons' => $reasons,
            'terms_remaining' => $termsRemaining,
            'expected_date' => $expectedDate
        ];
    }

    /**
     * Generate recommendations
     */
    protected function generateRecommendations(Student $student, array $overallProgress): array
    {
        $recommendations = [];
        
        foreach ($overallProgress as $category) {
            if (!$category['is_satisfied']) {
                foreach ($category['requirements'] as $req) {
                    if (!$req['is_satisfied'] && $req['is_required']) {
                        if (isset($req['credits_remaining']) && $req['credits_remaining'] > 0) {
                            $recommendations[] = [
                                'type' => 'credits',
                                'priority' => 'medium',
                                'message' => "Need {$req['credits_remaining']} credits in {$req['requirement_name']}",
                                'requirement' => $req['requirement_name']
                            ];
                        }
                    }
                }
            }
        }
        
        // Add GPA recommendation if needed
        if (($student->cumulative_gpa ?? 0) < 2.0) {
            $recommendations[] = [
                'type' => 'gpa',
                'priority' => 'high',
                'message' => 'Improve GPA to meet minimum requirement (2.0)',
                'current_gpa' => $student->cumulative_gpa
            ];
        }
        
        return array_slice($recommendations, 0, 5);
    }

    /**
     * Update student degree progress records
     */
    protected function updateStudentProgress(Student $student, array $overallProgress): void
    {
        foreach ($overallProgress as $category) {
            foreach ($category['requirements'] as $req) {
                // Find the program requirement
                $programRequirement = ProgramRequirement::where('requirement_id', $req['requirement_id'])
                    ->where('program_id', $student->program_id)
                    ->first();
                    
                if ($programRequirement) {
                    StudentDegreeProgress::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'requirement_id' => $req['requirement_id'],
                            'program_requirement_id' => $programRequirement->id
                        ],
                        [
                            'credits_completed' => $req['credits_completed'] ?? 0,
                            'credits_in_progress' => $req['credits_in_progress'] ?? 0,
                            'credits_remaining' => $req['credits_remaining'] ?? 0,
                            'status' => $this->determineProgressStatus($req),
                            'completion_percentage' => $req['progress_percentage'] ?? 0,
                            'is_satisfied' => $req['is_satisfied'] ?? false,
                            'last_calculated_at' => now()
                        ]
                    );
                }
            }
        }
    }

    /**
     * Determine progress status
     */
    protected function determineProgressStatus(array $evaluation): string
    {
        if ($evaluation['is_satisfied']) {
            return 'completed';
        } elseif ($evaluation['progress_percentage'] > 0) {
            return 'in_progress';
        }
        return 'not_started';
    }

    /**
     * Calculate major GPA
     */
    protected function calculateMajorGPA(Student $student, AcademicProgram $program): float
    {
        // Simplified calculation
        return floatval($student->major_gpa ?? $student->cumulative_gpa ?? 0);
    }

    /**
     * Calculate residency credits
     */
    protected function calculateResidencyCredits(Student $student): float
    {
        // For now, return total credits earned (should check transfer credits)
        return floatval($student->credits_earned ?? 0);
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

    /**
     * Get current term ID
     */
    protected function getCurrentTermId(): int
    {
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        return $currentTerm ? $currentTerm->id : 1;
    }
}