<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\WhatIfScenario;
use App\Models\AcademicProgram;
use App\Services\DegreeAudit\DegreeAuditService;
use App\Services\DegreeAudit\WhatIfAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class WhatIfAnalysisController extends Controller
{
    protected $auditService;
    protected $whatIfService;

    public function __construct(DegreeAuditService $auditService)
    {
        $this->auditService = $auditService;
        // WhatIfAnalysisService will be created and injected
        
        $this->middleware('auth');
    }

    /**
     * Display what-if analysis interface
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = Auth::user();
        
        // Get student record
        if ($user->hasRole('student')) {
            $student = Student::where('user_id', $user->id)->first();
        } else {
            $studentId = $request->get('student_id');
            $student = $studentId ? Student::find($studentId) : null;
        }

        if (!$student) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Student record not found'], 404);
            }
            return view('what-if.error', ['message' => 'Please select a student']);
        }

        // Get saved scenarios
        $scenarios = WhatIfScenario::where('student_id', $student->id)
            ->where('is_saved', true)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get available programs for scenario testing
        $programs = AcademicProgram::where('is_active', true)->get();
        
        // Get catalog years
        $currentYear = date('Y');
        $catalogYears = [];
        for ($i = 0; $i < 5; $i++) {
            $year = $currentYear - $i;
            $catalogYears[] = $year . '-' . ($year + 1);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'student' => $student,
                'scenarios' => $scenarios,
                'programs' => $programs,
                'catalog_years' => $catalogYears
            ]);
        }

        return view('degree-audit.student.what-if', compact('student', 'scenarios', 'programs', 'catalogYears'));
    }

    /**
     * Analyze a what-if scenario
     */
    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'scenario_type' => 'required|in:change_major,add_minor,add_double_major,change_catalog,transfer_credits,course_substitution',
            'scenario_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'new_program_id' => 'nullable|exists:academic_programs,id',
            'add_minor_id' => 'nullable|exists:academic_programs,id',
            'add_second_major_id' => 'nullable|exists:academic_programs,id',
            'new_catalog_year' => 'nullable|string|max:10',
            'transfer_courses' => 'nullable|array',
            'transfer_credits' => 'nullable|numeric|min:0',
            'save_scenario' => 'nullable|boolean'
        ]);

        try {
            $student = Student::findOrFail($validated['student_id']);
            
            // Check authorization
            if (!$this->canAnalyzeForStudent($student)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Run current audit for comparison
            $currentAudit = $this->auditService->runAudit($student);
            
            // Create scenario parameters
            $scenarioParams = $this->buildScenarioParameters($validated);
            
            // Run scenario audit
            $scenarioAudit = $this->auditService->runAudit($student, $scenarioParams);
            
            // Calculate differences
            $analysis = $this->compareAudits($currentAudit, $scenarioAudit);
            
            // Create scenario record
            $scenario = WhatIfScenario::create([
                'student_id' => $student->id,
                'scenario_name' => $validated['scenario_name'],
                'description' => $validated['description'] ?? null,
                'scenario_type' => $validated['scenario_type'],
                'new_program_id' => $validated['new_program_id'] ?? null,
                'add_minor_id' => $validated['add_minor_id'] ?? null,
                'add_second_major_id' => $validated['add_second_major_id'] ?? null,
                'new_catalog_year' => $validated['new_catalog_year'] ?? null,
                'transfer_courses' => $validated['transfer_courses'] ?? null,
                'transfer_credits' => $validated['transfer_credits'] ?? null,
                'analysis_results' => $analysis,
                'current_credits_required' => $currentAudit->total_credits_required,
                'scenario_credits_required' => $scenarioAudit->total_credits_required,
                'credit_difference' => $scenarioAudit->total_credits_required - $currentAudit->total_credits_required,
                'current_terms_remaining' => $currentAudit->terms_to_completion,
                'scenario_terms_remaining' => $scenarioAudit->terms_to_completion,
                'is_feasible' => $this->checkFeasibility($analysis),
                'feasibility_issues' => $this->identifyFeasibilityIssues($analysis),
                'is_saved' => $validated['save_scenario'] ?? false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'What-if analysis completed',
                'scenario' => $scenario,
                'current_audit' => $this->formatAuditSummary($currentAudit),
                'scenario_audit' => $this->formatAuditSummary($scenarioAudit),
                'analysis' => $analysis
            ]);

        } catch (\Exception $e) {
            Log::error('What-if analysis error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to perform what-if analysis',
                'message' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get saved scenarios for a student
     */
    public function scenarios(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Get student
        if ($user->hasRole('student')) {
            $student = Student::where('user_id', $user->id)->first();
        } else {
            $studentId = $request->get('student_id');
            $student = $studentId ? Student::find($studentId) : null;
        }

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        $scenarios = WhatIfScenario::where('student_id', $student->id)
            ->where('is_saved', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $scenarios
        ]);
    }

    /**
     * Save a what-if scenario
     */
    public function saveScenario(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'scenario_id' => 'required|exists:what_if_scenarios,id'
        ]);

        try {
            $scenario = WhatIfScenario::findOrFail($validated['scenario_id']);
            
            // Check authorization
            if (!$this->canManageScenario($scenario)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $scenario->saveScenario();

            return response()->json([
                'success' => true,
                'message' => 'Scenario saved successfully',
                'data' => $scenario
            ]);

        } catch (\Exception $e) {
            Log::error('Save scenario error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to save scenario'
            ], 500);
        }
    }

    /**
     * Delete a saved scenario
     */
    public function deleteScenario($id): JsonResponse
    {
        try {
            $scenario = WhatIfScenario::findOrFail($id);
            
            // Check authorization
            if (!$this->canManageScenario($scenario)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Don't delete applied scenarios
            if ($scenario->is_applied) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete an applied scenario'
                ], 400);
            }

            $scenario->delete();

            return response()->json([
                'success' => true,
                'message' => 'Scenario deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Delete scenario error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete scenario'
            ], 500);
        }
    }

    /**
     * Apply a what-if scenario (actually change student's program/catalog)
     */
    public function applyScenario(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'scenario_id' => 'required|exists:what_if_scenarios,id',
            'confirm' => 'required|boolean|accepted'
        ]);

        try {
            DB::beginTransaction();

            $scenario = WhatIfScenario::findOrFail($validated['scenario_id']);
            
            // Check authorization (only advisors/admins can apply)
            if (!Auth::user()->hasAnyRole(['advisor', 'admin'])) {
                return response()->json(['error' => 'Only advisors can apply scenarios'], 403);
            }

            // Check if scenario is feasible
            if (!$scenario->is_feasible) {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot apply an infeasible scenario'
                ], 400);
            }

            // Apply the scenario changes
            $student = $scenario->student;
            
            switch ($scenario->scenario_type) {
                case 'change_major':
                    if ($scenario->new_program_id) {
                        $student->program_id = $scenario->new_program_id;
                    }
                    break;
                    
                case 'add_minor':
                    if ($scenario->add_minor_id) {
                        $student->minor_program_id = $scenario->add_minor_id;
                    }
                    break;
                    
                case 'change_catalog':
                    if ($scenario->new_catalog_year) {
                        $student->catalog_year = $scenario->new_catalog_year;
                    }
                    break;
                    
                // Add other scenario types as needed
            }
            
            $student->save();
            
            // Mark scenario as applied
            $scenario->apply();
            
            // Re-run audit with new parameters
            $this->auditService->runAudit($student);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Scenario applied successfully',
                'data' => $scenario
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Apply scenario error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to apply scenario'
            ], 500);
        }
    }

    /**
     * Helper: Build scenario parameters for audit
     */
    protected function buildScenarioParameters(array $validated): array
    {
        $params = ['what_if' => true];
        
        if (isset($validated['new_program_id'])) {
            $params['program_id'] = $validated['new_program_id'];
        }
        
        if (isset($validated['new_catalog_year'])) {
            $params['catalog_year'] = $validated['new_catalog_year'];
        }
        
        if (isset($validated['add_minor_id'])) {
            $params['minor_id'] = $validated['add_minor_id'];
        }
        
        if (isset($validated['add_second_major_id'])) {
            $params['second_major_id'] = $validated['add_second_major_id'];
        }
        
        if (isset($validated['transfer_credits'])) {
            $params['additional_credits'] = $validated['transfer_credits'];
        }
        
        return $params;
    }

    /**
     * Helper: Compare two audits
     */
    protected function compareAudits($currentAudit, $scenarioAudit): array
    {
        return [
            'credits' => [
                'current_required' => $currentAudit->total_credits_required,
                'scenario_required' => $scenarioAudit->total_credits_required,
                'difference' => $scenarioAudit->total_credits_required - $currentAudit->total_credits_required
            ],
            'completion' => [
                'current_percentage' => $currentAudit->overall_completion_percentage,
                'scenario_percentage' => $scenarioAudit->overall_completion_percentage,
                'difference' => $scenarioAudit->overall_completion_percentage - $currentAudit->overall_completion_percentage
            ],
            'graduation' => [
                'current_terms' => $currentAudit->terms_to_completion,
                'scenario_terms' => $scenarioAudit->terms_to_completion,
                'additional_terms' => max(0, $scenarioAudit->terms_to_completion - $currentAudit->terms_to_completion)
            ],
            'new_requirements' => $this->identifyNewRequirements($currentAudit, $scenarioAudit),
            'removed_requirements' => $this->identifyRemovedRequirements($currentAudit, $scenarioAudit)
        ];
    }

    /**
     * Helper: Check if scenario is feasible
     */
    protected function checkFeasibility(array $analysis): bool
    {
        // Scenario is feasible if it doesn't add too many terms or credits
        $additionalTerms = $analysis['graduation']['additional_terms'] ?? 0;
        $additionalCredits = $analysis['credits']['difference'] ?? 0;
        
        // These thresholds can be configured
        return $additionalTerms <= 4 && $additionalCredits <= 30;
    }

    /**
     * Helper: Identify feasibility issues
     */
    protected function identifyFeasibilityIssues(array $analysis): array
    {
        $issues = [];
        
        $additionalTerms = $analysis['graduation']['additional_terms'] ?? 0;
        if ($additionalTerms > 4) {
            $issues[] = "Requires {$additionalTerms} additional terms";
        }
        
        $additionalCredits = $analysis['credits']['difference'] ?? 0;
        if ($additionalCredits > 30) {
            $issues[] = "Requires {$additionalCredits} additional credits";
        }
        
        if (count($analysis['new_requirements'] ?? []) > 10) {
            $issues[] = "Adds many new requirements";
        }
        
        return $issues;
    }

    /**
     * Helper: Identify new requirements in scenario
     */
    protected function identifyNewRequirements($currentAudit, $scenarioAudit): array
    {
        $currentReqs = collect($currentAudit->requirements_summary ?? [])->pluck('requirement_id')->toArray();
        $scenarioReqs = collect($scenarioAudit->requirements_summary ?? [])->pluck('requirement_id')->toArray();
        
        return array_diff($scenarioReqs, $currentReqs);
    }

    /**
     * Helper: Identify removed requirements in scenario
     */
    protected function identifyRemovedRequirements($currentAudit, $scenarioAudit): array
    {
        $currentReqs = collect($currentAudit->requirements_summary ?? [])->pluck('requirement_id')->toArray();
        $scenarioReqs = collect($scenarioAudit->requirements_summary ?? [])->pluck('requirement_id')->toArray();
        
        return array_diff($currentReqs, $scenarioReqs);
    }

    /**
     * Helper: Format audit summary for response
     */
    protected function formatAuditSummary($audit): array
    {
        return [
            'total_credits_required' => $audit->total_credits_required,
            'total_credits_completed' => $audit->total_credits_completed,
            'total_credits_remaining' => $audit->total_credits_remaining,
            'overall_completion_percentage' => $audit->overall_completion_percentage,
            'graduation_eligible' => $audit->graduation_eligible,
            'terms_to_completion' => $audit->terms_to_completion,
            'expected_graduation_date' => $audit->expected_graduation_date?->format('Y-m-d')
        ];
    }

    /**
     * Helper: Check if user can analyze for student
     */
    protected function canAnalyzeForStudent(Student $student): bool
    {
        $user = Auth::user();
        
        // Student can analyze their own scenarios
        if ($student->user_id === $user->id) {
            return true;
        }
        
        // Advisors and admins can analyze for any student
        if ($user->hasAnyRole(['advisor', 'registrar', 'admin'])) {
            return true;
        }
        
        return false;
    }

    /**
     * Helper: Check if user can manage scenario
     */
    protected function canManageScenario(WhatIfScenario $scenario): bool
    {
        $user = Auth::user();
        
        // Student can manage their own scenarios
        if ($scenario->student->user_id === $user->id) {
            return true;
        }
        
        // Advisors and admins can manage all scenarios
        if ($user->hasAnyRole(['advisor', 'admin'])) {
            return true;
        }
        
        return false;
    }
}