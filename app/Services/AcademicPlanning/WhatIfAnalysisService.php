<?php
// Save as: backend/app/Services/AcademicPlanning/WhatIfAnalysisService.php

namespace App\Services\AcademicPlanning;

use App\Models\Student;
use App\Models\WhatIfScenario;
use App\Models\AcademicProgram;
use App\Models\ProgramRequirement;
use App\Models\DegreeAuditReport;
use App\Models\Course;
use App\Services\DegreeAudit\DegreeAuditService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WhatIfAnalysisService
{
    protected $auditService;

    public function __construct(DegreeAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Run what-if analysis for a student
     */
    public function analyzeScenario(Student $student, array $scenario): array
    {
        try {
            // Get current state
            $currentAudit = $this->getCurrentAudit($student);
            
            // Build scenario analysis based on type
            $scenarioResult = match($scenario['scenario_type']) {
                'change_major' => $this->analyzeMajorChange($student, $scenario),
                'add_minor' => $this->analyzeMinorAddition($student, $scenario),
                'add_double_major' => $this->analyzeDoubleMajor($student, $scenario),
                'change_catalog' => $this->analyzeCatalogChange($student, $scenario),
                'transfer_credits' => $this->analyzeTransferCredits($student, $scenario),
                'course_substitution' => $this->analyzeCourseSubstitution($student, $scenario),
                default => throw new \Exception("Invalid scenario type")
            };

            // Compare with current state
            $comparison = $this->compareResults($currentAudit, $scenarioResult);

            // Calculate feasibility
            $feasibility = $this->assessFeasibility($student, $scenarioResult, $comparison);

            return [
                'success' => true,
                'scenario_type' => $scenario['scenario_type'],
                'current_state' => $this->formatAuditSummary($currentAudit),
                'scenario_result' => $this->formatAuditSummary($scenarioResult),
                'comparison' => $comparison,
                'feasibility' => $feasibility,
                'recommendations' => $this->generateRecommendations($comparison, $feasibility)
            ];

        } catch (\Exception $e) {
            Log::error('What-if analysis failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Analyze major change scenario
     */
    protected function analyzeMajorChange(Student $student, array $scenario): DegreeAuditReport
    {
        if (!isset($scenario['new_program_id'])) {
            throw new \Exception("New program ID is required for major change");
        }

        $newProgram = AcademicProgram::findOrFail($scenario['new_program_id']);
        
        // Create a temporary student model with new program
        $tempStudent = clone $student;
        $tempStudent->program_id = $newProgram->id;
        $tempStudent->program = $newProgram;
        
        // Run audit with new program
        $options = [
            'catalog_year' => $scenario['catalog_year'] ?? $this->getCurrentCatalogYear(),
            'program_id' => $newProgram->id
        ];
        
        return $this->auditService->runAudit($tempStudent, $options);
    }

    /**
     * Analyze adding a minor
     */
    protected function analyzeMinorAddition(Student $student, array $scenario): DegreeAuditReport
    {
        if (!isset($scenario['add_minor_id'])) {
            throw new \Exception("Minor program ID is required");
        }

        $minorProgram = AcademicProgram::findOrFail($scenario['add_minor_id']);
        
        // Get current audit
        $currentAudit = $this->getCurrentAudit($student);
        
        // Get minor requirements
        $minorRequirements = $this->getMinorRequirements($minorProgram, $scenario['catalog_year'] ?? $this->getCurrentCatalogYear());
        
        // Create modified audit with minor requirements added
        $modifiedAudit = clone $currentAudit;
        
        // Add minor credits to total required
        $minorCredits = $minorProgram->minor_credits ?? 18;
        $modifiedAudit->total_credits_required += $minorCredits;
        $modifiedAudit->total_credits_remaining += $minorCredits;
        
        // Check which minor requirements are already satisfied by current courses
        $satisfiedMinorCredits = $this->calculateSatisfiedMinorCredits($student, $minorProgram);
        $modifiedAudit->total_credits_remaining -= $satisfiedMinorCredits;
        
        // Recalculate completion percentage
        if ($modifiedAudit->total_credits_required > 0) {
            $modifiedAudit->overall_completion_percentage = 
                ($modifiedAudit->total_credits_completed / $modifiedAudit->total_credits_required) * 100;
        }
        
        // Recalculate terms to completion
        $creditsPerTerm = 15;
        $modifiedAudit->terms_to_completion = ceil($modifiedAudit->total_credits_remaining / $creditsPerTerm);
        $modifiedAudit->expected_graduation_date = Carbon::now()->addMonths($modifiedAudit->terms_to_completion * 4);
        
        return $modifiedAudit;
    }

    /**
     * Analyze double major scenario
     */
    protected function analyzeDoubleMajor(Student $student, array $scenario): DegreeAuditReport
    {
        if (!isset($scenario['add_second_major_id'])) {
            throw new \Exception("Second major program ID is required");
        }

        $secondMajor = AcademicProgram::findOrFail($scenario['add_second_major_id']);
        
        // Get current audit
        $currentAudit = $this->getCurrentAudit($student);
        
        // Get second major requirements
        $secondMajorRequirements = ProgramRequirement::where('program_id', $secondMajor->id)
            ->where('catalog_year', $scenario['catalog_year'] ?? $this->getCurrentCatalogYear())
            ->where('is_active', true)
            ->with('requirement')
            ->get();
        
        // Calculate additional requirements
        $additionalCredits = $this->calculateAdditionalCreditsForDoubleMajor($student, $secondMajor, $secondMajorRequirements);
        
        // Create modified audit
        $modifiedAudit = clone $currentAudit;
        $modifiedAudit->total_credits_required += $additionalCredits;
        $modifiedAudit->total_credits_remaining += $additionalCredits;
        
        // Recalculate metrics
        $this->recalculateAuditMetrics($modifiedAudit);
        
        return $modifiedAudit;
    }

    /**
     * Analyze catalog year change
     */
    protected function analyzeCatalogChange(Student $student, array $scenario): DegreeAuditReport
    {
        if (!isset($scenario['new_catalog_year'])) {
            throw new \Exception("New catalog year is required");
        }

        // Run audit with new catalog year
        $options = [
            'catalog_year' => $scenario['new_catalog_year'],
            'force_refresh' => true
        ];
        
        return $this->auditService->runAudit($student, $options);
    }

    /**
     * Analyze transfer credits scenario
     */
    protected function analyzeTransferCredits(Student $student, array $scenario): DegreeAuditReport
    {
        $transferCourses = $scenario['transfer_courses'] ?? [];
        $transferCredits = $scenario['transfer_credits'] ?? 0;
        
        // Get current audit
        $currentAudit = $this->getCurrentAudit($student);
        
        // Create modified audit
        $modifiedAudit = clone $currentAudit;
        
        // Apply transfer credits
        if ($transferCredits > 0) {
            $modifiedAudit->total_credits_completed += $transferCredits;
            $modifiedAudit->total_credits_remaining = max(0, $modifiedAudit->total_credits_remaining - $transferCredits);
            
            // Check if specific requirements are satisfied by transfer courses
            foreach ($transferCourses as $transferCourse) {
                // This would need more detailed implementation based on your transfer credit evaluation rules
                $this->applyTransferCourseToRequirements($modifiedAudit, $transferCourse);
            }
        }
        
        // Recalculate metrics
        $this->recalculateAuditMetrics($modifiedAudit);
        
        return $modifiedAudit;
    }

    /**
     * Analyze course substitution scenario
     */
    protected function analyzeCourseSubstitution(Student $student, array $scenario): DegreeAuditReport
    {
        $originalCourseId = $scenario['original_course_id'] ?? null;
        $substituteCourseId = $scenario['substitute_course_id'] ?? null;
        
        if (!$originalCourseId || !$substituteCourseId) {
            throw new \Exception("Both original and substitute course IDs are required");
        }

        $originalCourse = Course::findOrFail($originalCourseId);
        $substituteCourse = Course::findOrFail($substituteCourseId);
        
        // Get current audit
        $currentAudit = $this->getCurrentAudit($student);
        
        // Create modified audit
        $modifiedAudit = clone $currentAudit;
        
        // Check if the substitution affects any requirements
        $affectedRequirements = $this->findAffectedRequirements($originalCourse, $student);
        
        foreach ($affectedRequirements as $requirement) {
            // Update requirement progress with substitution
            $this->applySubstitutionToRequirement($modifiedAudit, $requirement, $originalCourse, $substituteCourse);
        }
        
        // Recalculate metrics
        $this->recalculateAuditMetrics($modifiedAudit);
        
        return $modifiedAudit;
    }

    /**
     * Save what-if scenario
     */
    public function saveScenario(Student $student, array $scenarioData, array $analysisResults): WhatIfScenario
    {
        return WhatIfScenario::create([
            'student_id' => $student->id,
            'scenario_name' => $scenarioData['scenario_name'],
            'description' => $scenarioData['description'] ?? null,
            'scenario_type' => $scenarioData['scenario_type'],
            'new_program_id' => $scenarioData['new_program_id'] ?? null,
            'add_minor_id' => $scenarioData['add_minor_id'] ?? null,
            'add_second_major_id' => $scenarioData['add_second_major_id'] ?? null,
            'new_catalog_year' => $scenarioData['new_catalog_year'] ?? null,
            'transfer_courses' => $scenarioData['transfer_courses'] ?? null,
            'transfer_credits' => $scenarioData['transfer_credits'] ?? null,
            'analysis_results' => $analysisResults,
            'current_credits_required' => $analysisResults['current_state']['credits_required'] ?? null,
            'scenario_credits_required' => $analysisResults['scenario_result']['credits_required'] ?? null,
            'credit_difference' => $analysisResults['comparison']['credit_difference'] ?? null,
            'current_terms_remaining' => $analysisResults['current_state']['terms_remaining'] ?? null,
            'scenario_terms_remaining' => $analysisResults['scenario_result']['terms_remaining'] ?? null,
            'is_feasible' => $analysisResults['feasibility']['is_feasible'] ?? false,
            'feasibility_issues' => $analysisResults['feasibility']['issues'] ?? null,
            'is_saved' => true
        ]);
    }

    /**
     * Apply saved scenario to student
     */
    public function applyScenario(WhatIfScenario $scenario): bool
    {
        if ($scenario->is_applied) {
            throw new \Exception("Scenario has already been applied");
        }

        DB::beginTransaction();
        try {
            $student = $scenario->student;
            
            switch ($scenario->scenario_type) {
                case 'change_major':
                    if ($scenario->new_program_id) {
                        $student->program_id = $scenario->new_program_id;
                        $student->save();
                    }
                    break;
                    
                case 'add_minor':
                    if ($scenario->add_minor_id) {
                        $student->minor_program_id = $scenario->add_minor_id;
                        $student->save();
                    }
                    break;
                    
                case 'change_catalog':
                    if ($scenario->new_catalog_year) {
                        $student->catalog_year = $scenario->new_catalog_year;
                        $student->save();
                    }
                    break;
                    
                // Other scenario types would need more complex implementation
            }
            
            $scenario->is_applied = true;
            $scenario->applied_at = now();
            $scenario->save();
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to apply scenario: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Compare audit results
     */
    protected function compareResults(DegreeAuditReport $current, DegreeAuditReport $scenario): array
    {
        return [
            'credit_difference' => $scenario->total_credits_remaining - $current->total_credits_remaining,
            'term_difference' => ($scenario->terms_to_completion ?? 0) - ($current->terms_to_completion ?? 0),
            'completion_difference' => $scenario->overall_completion_percentage - $current->overall_completion_percentage,
            'new_requirements' => $this->identifyNewRequirements($current, $scenario),
            'removed_requirements' => $this->identifyRemovedRequirements($current, $scenario),
            'cost_impact' => $this->calculateCostImpact($current, $scenario)
        ];
    }

    /**
     * Assess feasibility of scenario
     */
    protected function assessFeasibility(Student $student, DegreeAuditReport $scenarioAudit, array $comparison): array
    {
        $issues = [];
        $isFeasible = true;
        
        // Check if additional time exceeds maximum allowed
        $maxYears = 6;
        $totalTerms = $scenarioAudit->terms_to_completion ?? 0;
        if ($totalTerms > ($maxYears * 2)) {
            $issues[] = "Scenario would exceed maximum time to degree ({$maxYears} years)";
            $isFeasible = false;
        }
        
        // Check if student has already completed conflicting requirements
        if ($comparison['credit_difference'] > 30) {
            $issues[] = "Scenario requires more than 30 additional credits";
        }
        
        // Check financial implications
        if (($comparison['cost_impact'] ?? 0) > 10000) {
            $issues[] = "Scenario would cost more than $10,000 in additional tuition";
        }
        
        // Check if required courses are available
        foreach ($comparison['new_requirements'] as $reqId) {
            // This would check course availability
            // Simplified for now
        }
        
        return [
            'is_feasible' => $isFeasible,
            'issues' => $issues,
            'estimated_cost' => $comparison['cost_impact'] ?? 0,
            'additional_terms' => $comparison['term_difference'] ?? 0,
            'additional_credits' => max(0, $comparison['credit_difference'] ?? 0)
        ];
    }

    /**
     * Generate recommendations based on analysis
     */
    protected function generateRecommendations(array $comparison, array $feasibility): array
    {
        $recommendations = [];
        
        if ($comparison['credit_difference'] > 0) {
            $recommendations[] = [
                'type' => 'credits',
                'message' => "Consider summer courses to reduce time to graduation",
                'priority' => 'medium'
            ];
        }
        
        if ($comparison['term_difference'] > 2) {
            $recommendations[] = [
                'type' => 'timeline',
                'message' => "This change would add {$comparison['term_difference']} terms to your graduation timeline",
                'priority' => 'high'
            ];
        }
        
        if (!$feasibility['is_feasible']) {
            $recommendations[] = [
                'type' => 'feasibility',
                'message' => "This scenario may not be feasible due to: " . implode(', ', $feasibility['issues']),
                'priority' => 'critical'
            ];
        }
        
        if (($feasibility['estimated_cost'] ?? 0) > 5000) {
            $recommendations[] = [
                'type' => 'financial',
                'message' => "Consider financial aid options for additional costs",
                'priority' => 'high'
            ];
        }
        
        return $recommendations;
    }

    /**
     * Helper methods
     */
    
    protected function getCurrentAudit(Student $student): DegreeAuditReport
    {
        // Get most recent audit or generate new one
        $recentAudit = DegreeAuditReport::where('student_id', $student->id)
            ->where('report_type', 'unofficial')
            ->where('generated_at', '>=', now()->subHours(24))
            ->orderBy('generated_at', 'desc')
            ->first();
            
        if ($recentAudit) {
            return $recentAudit;
        }
        
        return $this->auditService->runAudit($student);
    }
    
    protected function formatAuditSummary(DegreeAuditReport $audit): array
    {
        return [
            'credits_required' => $audit->total_credits_required,
            'credits_completed' => $audit->total_credits_completed,
            'credits_remaining' => $audit->total_credits_remaining,
            'completion_percentage' => $audit->overall_completion_percentage,
            'terms_remaining' => $audit->terms_to_completion,
            'expected_graduation' => $audit->expected_graduation_date?->format('Y-m-d'),
            'gpa' => $audit->cumulative_gpa,
            'graduation_eligible' => $audit->graduation_eligible
        ];
    }
    
    protected function recalculateAuditMetrics(DegreeAuditReport &$audit): void
    {
        // Recalculate completion percentage
        if ($audit->total_credits_required > 0) {
            $audit->overall_completion_percentage = min(100,
                ($audit->total_credits_completed / $audit->total_credits_required) * 100
            );
        }
        
        // Recalculate terms to completion
        $creditsPerTerm = 15;
        $audit->terms_to_completion = ceil($audit->total_credits_remaining / $creditsPerTerm);
        
        // Recalculate expected graduation date
        $audit->expected_graduation_date = Carbon::now()->addMonths($audit->terms_to_completion * 4);
        
        // Check graduation eligibility
        $audit->graduation_eligible = ($audit->total_credits_remaining <= 0) && 
                                      ($audit->cumulative_gpa >= 2.0);
    }
    
    protected function getMinorRequirements(AcademicProgram $minorProgram, string $catalogYear): Collection
    {
        return ProgramRequirement::where('program_id', $minorProgram->id)
            ->where('catalog_year', $catalogYear)
            ->where('applies_to', 'minor_only')
            ->where('is_active', true)
            ->with('requirement')
            ->get();
    }
    
    protected function calculateSatisfiedMinorCredits(Student $student, AcademicProgram $minorProgram): float
    {
        // Get completed courses that could count toward minor
        $completedCourses = $student->enrollments()
            ->where('enrollment_status', 'completed')
            ->whereHas('section.course', function ($query) use ($minorProgram) {
                $query->where('department_id', $minorProgram->department_id);
            })
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->sum('courses.credits');
            
        return min($completedCourses, $minorProgram->minor_credits ?? 18);
    }
    
    protected function calculateAdditionalCreditsForDoubleMajor(
        Student $student, 
        AcademicProgram $secondMajor,
        Collection $secondMajorRequirements
    ): float {
        $additionalCredits = 0;
        
        // Get courses already completed
        $completedCourseIds = $student->enrollments()
            ->where('enrollment_status', 'completed')
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->pluck('course_sections.course_id')
            ->toArray();
        
        foreach ($secondMajorRequirements as $programReq) {
            $requirement = $programReq->requirement;
            
            // Check if any courses for this requirement are already completed
            $applicableCourses = $requirement->courses()->pluck('courses.id')->toArray();
            $overlap = array_intersect($completedCourseIds, $applicableCourses);
            
            if (count($overlap) > 0) {
                // Some courses already satisfy this requirement
                $overlapCredits = Course::whereIn('id', $overlap)->sum('credits');
                $requiredCredits = $programReq->credits_required ?? 0;
                $additionalCredits += max(0, $requiredCredits - $overlapCredits);
            } else {
                // No overlap, all credits are additional
                $additionalCredits += $programReq->credits_required ?? 0;
            }
        }
        
        return $additionalCredits;
    }
    
    protected function applyTransferCourseToRequirements(DegreeAuditReport &$audit, array $transferCourse): void
    {
        // This would need detailed implementation based on transfer evaluation rules
        // For now, applying credits to general electives
        $credits = $transferCourse['credits'] ?? 3;
        
        // Find elective requirement in audit and apply credits
        if (isset($audit->remaining_requirements)) {
            foreach ($audit->remaining_requirements as &$req) {
                if ($req['requirement_type'] === 'elective' && $req['credits_remaining'] > 0) {
                    $applied = min($credits, $req['credits_remaining']);
                    $req['credits_remaining'] -= $applied;
                    $req['credits_completed'] += $applied;
                    break;
                }
            }
        }
    }
    
    protected function findAffectedRequirements(Course $course, Student $student): array
    {
        // Find requirements that this course could satisfy
        $requirements = [];
        
        $mappings = $course->requirementMappings()
            ->with('requirement')
            ->where('is_active', true)
            ->get();
            
        foreach ($mappings as $mapping) {
            $requirements[] = $mapping->requirement;
        }
        
        return $requirements;
    }
    
    protected function applySubstitutionToRequirement(
        DegreeAuditReport &$audit,
        $requirement,
        Course $originalCourse,
        Course $substituteCourse
    ): void {
        // Update requirement progress with substitution
        // This would need detailed implementation based on your substitution rules
        
        $creditDifference = $substituteCourse->credits - $originalCourse->credits;
        
        if ($creditDifference != 0 && isset($audit->remaining_requirements)) {
            foreach ($audit->remaining_requirements as &$req) {
                if ($req['requirement_id'] === $requirement->id) {
                    $req['credits_remaining'] = max(0, $req['credits_remaining'] - $creditDifference);
                    break;
                }
            }
        }
    }
    
    protected function identifyNewRequirements(DegreeAuditReport $current, DegreeAuditReport $scenario): array
    {
        $currentReqs = collect($current->requirements_summary ?? [])->pluck('requirement_id')->toArray();
        $scenarioReqs = collect($scenario->requirements_summary ?? [])->pluck('requirement_id')->toArray();
        
        return array_diff($scenarioReqs, $currentReqs);
    }
    
    protected function identifyRemovedRequirements(DegreeAuditReport $current, DegreeAuditReport $scenario): array
    {
        $currentReqs = collect($current->requirements_summary ?? [])->pluck('requirement_id')->toArray();
        $scenarioReqs = collect($scenario->requirements_summary ?? [])->pluck('requirement_id')->toArray();
        
        return array_diff($currentReqs, $scenarioReqs);
    }
    
    protected function calculateCostImpact(DegreeAuditReport $current, DegreeAuditReport $scenario): float
    {
        // Calculate additional cost based on credit difference
        $creditDifference = max(0, $scenario->total_credits_remaining - $current->total_credits_remaining);
        $costPerCredit = 500; // This should come from configuration
        
        return $creditDifference * $costPerCredit;
    }
    
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