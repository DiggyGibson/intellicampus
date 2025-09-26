<?php
// File: C:\IntelliCampus\app\Http\Controllers\GraduationController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\GraduationApplication;
use App\Models\DegreeAuditReport;
use App\Models\AcademicTerm;
use App\Services\DegreeAudit\DegreeAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class GraduationController extends Controller
{
    protected $auditService;

    public function __construct(DegreeAuditService $auditService)
    {
        $this->auditService = $auditService;
        
        $this->middleware('auth');
        $this->middleware('role:registrar,admin')->only(['applications', 'reviewApplication', 'approve', 'deny', 'updateClearance']);
    }

    /**
     * Check graduation eligibility for current student
     * URL: /graduation/check
     */
    public function checkEligibility(Request $request)
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
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Student record not found'], 404);
            }
            return view('degree-audit.graduation.check', [
                'error' => 'Student record not found',
                'student' => null,
                'eligibility' => null
            ]);
        }

        try {
            // Run fresh audit to check eligibility
            $audit = $this->auditService->runAudit($student, ['force_refresh' => true]);
            
            $eligibility = (object)[
                'is_eligible' => $audit->graduation_eligible ?? false,
                'total_credits_completed' => $audit->total_credits_completed ?? 0,
                'total_credits_required' => $audit->total_credits_required ?? 120,
                'gpa' => $audit->cumulative_gpa ?? 0,
                'expected_graduation_date' => $audit->expected_graduation_date,
                'pending_requirements' => $audit->remaining_requirements ?? [],
                'holds' => $this->checkStudentHolds($student),
                'completion_percentage' => $audit->overall_completion_percentage ?? 0
            ];

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $eligibility
                ]);
            }

            return view('degree-audit.graduation.check', compact('student', 'eligibility', 'audit'));

        } catch (\Exception $e) {
            Log::error('Eligibility check error: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to check eligibility: ' . $e->getMessage()
                ], 500);
            }
            
            return view('degree-audit.graduation.check', [
                'error' => 'Failed to check graduation eligibility',
                'student' => $student,
                'eligibility' => null
            ]);
        }
    }

    /**
     * Display graduation application form
     * URL: /graduation/apply (GET)
     */
    public function showApplicationForm(Request $request): View
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            return view('degree-audit.graduation.error', ['message' => 'Student record not found']);
        }

        // Check for existing application
        $existingApplication = GraduationApplication::where('student_id', $student->id)
            ->whereIn('status', ['draft', 'submitted', 'under_review', 'approved'])
            ->first();
            
        if ($existingApplication) {
            return redirect()->route('graduation.status')
                ->with('info', 'You already have a graduation application in progress');
        }

        // Get upcoming graduation terms
        $graduationTerms = AcademicTerm::where('start_date', '>=', now())
            ->orderBy('start_date')
            ->limit(4)
            ->get();

        // Run audit to get current status
        $audit = $this->auditService->runAudit($student);
        
        return view('degree-audit.graduation.application', compact('student', 'graduationTerms', 'audit'));
    }

    /**
     * Submit graduation application
     * URL: /graduation/apply (POST)
     */
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'term_id' => 'required|exists:academic_terms,id',
            'expected_graduation_date' => 'required|date|after:today',
            'diploma_name' => 'nullable|string|max:255',
            'confirm_requirements' => 'required|accepted',
            'confirm_information' => 'required|accepted'
        ]);

        try {
            DB::beginTransaction();

            $student = Student::findOrFail($validated['student_id']);
            
            // Check authorization
            if ($student->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['advisor', 'admin'])) {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
                return back()->with('error', 'Unauthorized');
            }

            // Check for existing application
            $existing = GraduationApplication::where('student_id', $student->id)
                ->whereIn('status', ['submitted', 'under_review', 'approved'])
                ->first();
                
            if ($existing) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'An application is already in progress'
                    ], 400);
                }
                return back()->with('error', 'You already have an application in progress');
            }

            // Run audit to get current status
            $audit = $this->auditService->runAudit($student);
            
            // Create application
            $application = GraduationApplication::create([
                'student_id' => $student->id,
                'program_id' => $student->program_id,
                'term_id' => $validated['term_id'],
                'expected_graduation_date' => $validated['expected_graduation_date'],
                'degree_type' => $student->program->degree_type ?? 'BS',
                'diploma_name' => $validated['diploma_name'] ?? $student->first_name . ' ' . $student->last_name,
                'requirements_met' => $audit->graduation_eligible ?? false,
                'final_gpa' => $audit->cumulative_gpa ?? 0,
                'total_credits_earned' => $audit->total_credits_completed ?? 0,
                'pending_requirements' => $audit->remaining_requirements ?? [],
                'has_holds' => count($this->checkStudentHolds($student)) > 0,
                'holds_list' => $this->checkStudentHolds($student),
                'status' => 'submitted',
                'submitted_at' => now()
            ]);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Graduation application submitted successfully',
                    'data' => $application
                ]);
            }

            return redirect()->route('graduation.status')
                ->with('success', 'Your graduation application has been submitted successfully');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Graduation application error: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to submit application'
                ], 500);
            }
            
            return back()->with('error', 'Failed to submit graduation application')->withInput();
        }
    }

    /**
     * Check application status for current student
     * URL: /graduation/status
     */
    public function status(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        
        if (!$student) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Student record not found'], 404);
            }
            return view('degree-audit.graduation.status', [
                'error' => 'Student record not found',
                'student' => null,
                'application' => null
            ]);
        }

        $application = GraduationApplication::where('student_id', $student->id)
            ->with(['term', 'program', 'reviewedBy'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$application) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No graduation application found'
                ]);
            }
            return redirect()->route('graduation.check')
                ->with('info', 'You have not submitted a graduation application yet');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $application
            ]);
        }

        return view('degree-audit.graduation.status', compact('student', 'application'));
    }

    /**
     * Clear student for graduation (admin only)
     * URL: /graduation/clear (POST)
     */
    public function clearForGraduation(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id'
        ]);

        try {
            $student = Student::findOrFail($validated['student_id']);
            
            // Run fresh audit
            $audit = $this->auditService->runAudit($student, ['force_refresh' => true]);
            
            if (!$audit->graduation_eligible) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student does not meet graduation requirements'
                ], 400);
            }
            
            // Update student record
            $student->update([
                'graduation_status' => 'cleared',
                'graduation_date' => $request->graduation_date ?? now()->addMonths(1)
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Student cleared for graduation'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Clear for graduation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear student for graduation'
            ], 500);
        }
    }

    // Keep all other methods as they are...
    
    /**
     * Helper: Check student holds
     */
    protected function checkStudentHolds(Student $student): array
    {
        $holds = [];
        
        // Check financial holds
        if ($student->studentAccount && $student->studentAccount->balance > 0) {
            $holds[] = [
                'type' => 'financial',
                'description' => 'Outstanding balance: $' . number_format($student->studentAccount->balance, 2)
            ];
        }
        
        // Check registration holds
        if (method_exists($student, 'registrationHolds')) {
            $activeHolds = $student->registrationHolds()->where('is_active', true)->get();
            foreach ($activeHolds as $hold) {
                $holds[] = [
                    'type' => 'registration',
                    'description' => $hold->reason
                ];
            }
        }
        
        return $holds;
    }
}