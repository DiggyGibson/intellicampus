<?php
// ============================================
// app/Http/Controllers/RegistrationIntegrationController.php
// Integration with existing registration system
// ============================================

namespace App\Http\Controllers;

use App\Models\RegistrationOverrideRequest;
use App\Models\Student;
use App\Models\CourseSection;
use App\Services\RegistrationOverrideService;
use App\Services\CreditValidationService;
use App\Services\PrerequisiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RegistrationIntegrationController extends Controller
{
    protected $overrideService;
    protected $creditValidationService;
    protected $prerequisiteService;
    
    public function __construct(
        RegistrationOverrideService $overrideService,
        CreditValidationService $creditValidationService,
        PrerequisiteService $prerequisiteService
    ) {
        $this->overrideService = $overrideService;
        $this->creditValidationService = $creditValidationService;
        $this->prerequisiteService = $prerequisiteService;
    }
    
    /**
     * Enhanced registration validation with override support
     */
    public function validateRegistration(Request $request)
    {
        $validated = $request->validate([
            'section_ids' => 'required|array',
            'section_ids.*' => 'exists:course_sections,id',
            'override_codes' => 'nullable|array',
            'override_codes.*' => 'string|size:8'
        ]);
        
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        
        $sections = CourseSection::with('course')->whereIn('id', $validated['section_ids'])->get();
        $overrideCodes = $validated['override_codes'] ?? [];
        
        $validationResult = [
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'overrides_available' => [],
            'overrides_used' => []
        ];
        
        // 1. Check credit limits with override support
        $creditValidation = $this->validateCreditsWithOverride($student, $sections, $overrideCodes);
        $validationResult = array_merge_recursive($validationResult, $creditValidation);
        
        // 2. Check prerequisites with override support
        foreach ($sections as $section) {
            $prereqValidation = $this->validatePrerequisitesWithOverride($student, $section, $overrideCodes);
            $validationResult = array_merge_recursive($validationResult, $prereqValidation);
        }
        
        // 3. Check capacity with override support
        foreach ($sections as $section) {
            $capacityValidation = $this->validateCapacityWithOverride($student, $section, $overrideCodes);
            $validationResult = array_merge_recursive($validationResult, $capacityValidation);
        }
        
        // 4. Check time conflicts (usually no override possible)
        $conflictValidation = $this->validateTimeConflicts($sections);
        if (!$conflictValidation['valid']) {
            // Check for special time conflict override
            if ($this->hasTimeConflictOverride($student, $overrideCodes)) {
                $validationResult['warnings'][] = 'Time conflict allowed with special permission';
                $validationResult['overrides_used'][] = 'time_conflict_override';
            } else {
                $validationResult['valid'] = false;
                $validationResult['errors'][] = $conflictValidation['error'];
            }
        }
        
        // 5. Suggest available overrides if validation failed
        if (!$validationResult['valid']) {
            $validationResult['overrides_available'] = $this->suggestOverrides($student, $validationResult['errors']);
        }
        
        return response()->json($validationResult);
    }
    
    /**
     * Process registration with override codes
     */
    public function registerWithOverrides(Request $request)
    {
        $validated = $request->validate([
            'section_ids' => 'required|array',
            'section_ids.*' => 'exists:course_sections,id',
            'override_codes' => 'nullable|array',
            'override_codes.*' => 'string|size:8'
        ]);
        
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->firstOrFail();
        
        DB::beginTransaction();
        
        try {
            $results = [
                'registered' => [],
                'failed' => [],
                'overrides_used' => []
            ];
            
            // Process each override code
            foreach ($validated['override_codes'] ?? [] as $code) {
                try {
                    $override = $this->overrideService->useOverrideCode($code, $student->id);
                    $results['overrides_used'][] = $override;
                } catch (\Exception $e) {
                    // Invalid code, continue
                }
            }
            
            // Register for each section
            foreach ($validated['section_ids'] as $sectionId) {
                try {
                    $section = CourseSection::findOrFail($sectionId);
                    
                    // Create registration record
                    $registration = DB::table('registrations')->insert([
                        'student_id' => $student->id,
                        'section_id' => $sectionId,
                        'term_id' => $section->term_id,
                        'registration_status' => 'enrolled',
                        'registration_date' => now(),
                        'registered_with_override' => !empty($results['overrides_used']),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    // Update enrollment count
                    $section->increment('current_enrollment');
                    
                    $results['registered'][] = [
                        'section_id' => $sectionId,
                        'course' => $section->course->code,
                        'message' => 'Successfully registered'
                    ];
                    
                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'section_id' => $sectionId,
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Validate credits with override support
     */
    private function validateCreditsWithOverride($student, $sections, $overrideCodes)
    {
        $totalCredits = $sections->sum('course.credits');
        $currentCredits = $this->creditValidationService->calculateCurrentCredits($student->id);
        $combinedCredits = $totalCredits + $currentCredits;
        
        // Check for credit overload permission
        $overloadPermission = DB::table('credit_overload_permissions')
            ->where('student_id', $student->id)
            ->where('term_id', $this->getCurrentTermId())
            ->where('is_active', true)
            ->first();
            
        $maxAllowed = $overloadPermission ? $overloadPermission->max_credits : 18;
        
        if ($combinedCredits > $maxAllowed) {
            // Try to use override code
            foreach ($overrideCodes as $code) {
                $override = RegistrationOverrideRequest::where('override_code', $code)
                    ->where('student_id', $student->id)
                    ->where('request_type', 'credit_overload')
                    ->where('status', 'approved')
                    ->where('override_used', false)
                    ->first();
                    
                if ($override && $override->requested_credits >= $combinedCredits) {
                    return [
                        'valid' => true,
                        'overrides_used' => ['Credit overload override applied']
                    ];
                }
            }
            
            return [
                'valid' => false,
                'errors' => ["Total credits ($combinedCredits) exceeds maximum allowed ($maxAllowed)"]
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Suggest available overrides
     */
    private function suggestOverrides($student, $errors)
    {
        $suggestions = [];
        
        foreach ($errors as $error) {
            if (str_contains($error, 'credits')) {
                $suggestions[] = [
                    'type' => 'credit_overload',
                    'message' => 'You may request a credit overload override',
                    'url' => route('student.override.create')
                ];
            } elseif (str_contains($error, 'prerequisite')) {
                $suggestions[] = [
                    'type' => 'prerequisite',
                    'message' => 'You may request a prerequisite waiver',
                    'url' => route('student.override.create')
                ];
            } elseif (str_contains($error, 'full') || str_contains($error, 'capacity')) {
                $suggestions[] = [
                    'type' => 'capacity',
                    'message' => 'You may request a capacity override',
                    'url' => route('student.override.create')
                ];
            }
        }
        
        return $suggestions;
    }
    
    private function getCurrentTermId()
    {
        return DB::table('academic_terms')
            ->where('is_current', true)
            ->value('id');
    }
}