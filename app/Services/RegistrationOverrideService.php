<?php
// app/Services/RegistrationOverrideService.php

namespace App\Services;

use App\Models\RegistrationOverrideRequest;
use App\Models\CreditOverloadPermission;
use App\Models\PrerequisiteWaiver;
use App\Models\SpecialRegistrationFlag;
use App\Models\Student;
use App\Models\CourseSection;
use App\Models\Course;
use App\Models\User;
use App\Models\AcademicTerm;
use App\Notifications\OverrideRequestSubmitted;
use App\Notifications\OverrideRequestDecision;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class RegistrationOverrideService
{
    const CREDIT_OVERLOAD_THRESHOLD = 18;
    const MAX_CREDITS_WITH_OVERRIDE = 24;
    const MIN_GPA_FOR_OVERLOAD = 3.0;
    const OVERRIDE_CODE_LENGTH = 8;
    const OVERRIDE_VALIDITY_DAYS = 7;

    /**
     * Create a credit overload request
     */
    public function requestCreditOverload(
        Student $student, 
        int $requestedCredits, 
        string $justification, 
        array $supportingDocs = []
    ): RegistrationOverrideRequest {
        // Validate request
        $this->validateCreditOverloadRequest($student, $requestedCredits);
        
        // Get current term
        $currentTerm = $this->getCurrentTerm();
        
        // Check for existing request
        $existingRequest = $this->checkExistingRequest(
            $student->id, 
            $currentTerm->id, 
            RegistrationOverrideRequest::TYPE_CREDIT_OVERLOAD
        );
        
        if ($existingRequest) {
            throw new Exception("You already have a {$existingRequest->status} credit overload request for this term.");
        }
        
        // Calculate current credits
        $currentCredits = $this->calculateCurrentCredits($student->id, $currentTerm->id);
        
        // Determine priority
        $priority = $this->calculateRequestPriority($student, 'credit_overload');
        
        // Create request
        $request = DB::transaction(function () use ($student, $currentTerm, $requestedCredits, $currentCredits, $justification, $supportingDocs, $priority) {
            $request = RegistrationOverrideRequest::create([
                'student_id' => $student->id,
                'term_id' => $currentTerm->id,
                'request_type' => RegistrationOverrideRequest::TYPE_CREDIT_OVERLOAD,
                'requested_credits' => $requestedCredits,
                'current_credits' => $currentCredits,
                'student_justification' => $justification,
                'supporting_documents' => $supportingDocs,
                'priority_level' => $priority,
                'is_graduating_senior' => $this->isGraduatingSenior($student),
                'status' => RegistrationOverrideRequest::STATUS_PENDING
            ]);
            
            // Check for auto-approval
            if ($this->checkAutoApproval($request)) {
                $this->autoApprove($request);
            } else {
                // Notify appropriate approver
                $this->notifyApprover($request);
            }
            
            return $request;
        });
        
        // Notify student
        $student->user->notify(new OverrideRequestSubmitted($request));
        
        return $request;
    }

    /**
     * Request prerequisite override
     */
    public function requestPrerequisiteOverride(
        Student $student,
        Course $course,
        string $reason,
        string $justification,
        array $evidence = []
    ): RegistrationOverrideRequest {
        // Get current term
        $currentTerm = $this->getCurrentTerm();
        
        // Validate that student actually lacks prerequisites
        $missingPrereqs = $this->getMissingPrerequisites($student, $course);
        
        if (empty($missingPrereqs)) {
            throw new Exception("You meet all prerequisites for {$course->code}.");
        }
        
        // Check for existing request or waiver
        $existingWaiver = PrerequisiteWaiver::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->where('term_id', $currentTerm->id)
            ->active()
            ->first();
            
        if ($existingWaiver) {
            throw new Exception("You already have an active prerequisite waiver for this course.");
        }
        
        $existingRequest = $this->checkExistingRequest(
            $student->id,
            $currentTerm->id,
            RegistrationOverrideRequest::TYPE_PREREQUISITE,
            $course->id
        );
        
        if ($existingRequest) {
            return $existingRequest;
        }
        
        // Determine priority
        $priority = $this->calculateRequestPriority($student, 'prerequisite');
        
        // Create request
        $request = DB::transaction(function () use ($student, $currentTerm, $course, $reason, $justification, $evidence, $missingPrereqs, $priority) {
            $request = RegistrationOverrideRequest::create([
                'student_id' => $student->id,
                'term_id' => $currentTerm->id,
                'request_type' => RegistrationOverrideRequest::TYPE_PREREQUISITE,
                'course_id' => $course->id,
                'student_justification' => $justification,
                'supporting_documents' => [
                    'reason' => $reason,
                    'missing_prerequisites' => $missingPrereqs,
                    'evidence' => $evidence
                ],
                'priority_level' => $priority,
                'is_graduating_senior' => $this->isGraduatingSenior($student),
                'status' => RegistrationOverrideRequest::STATUS_PENDING
            ]);
            
            // Route to appropriate approver
            $this->notifyApprover($request);
            
            return $request;
        });
        
        // Notify student
        $student->user->notify(new OverrideRequestSubmitted($request));
        
        return $request;
    }

    /**
     * Request capacity override (get into full class)
     */
    public function requestCapacityOverride(
        Student $student,
        CourseSection $section,
        string $justification,
        bool $isRequired = false
    ): RegistrationOverrideRequest {
        // Verify section is actually full
        if ($section->current_enrollment < $section->enrollment_capacity) {
            throw new Exception("Section is not full. Current enrollment: {$section->current_enrollment}/{$section->enrollment_capacity}");
        }
        
        // Get current term
        $currentTerm = $this->getCurrentTerm();
        
        // Check for existing request
        $existingRequest = RegistrationOverrideRequest::where('student_id', $student->id)
            ->where('section_id', $section->id)
            ->where('term_id', $currentTerm->id)
            ->where('request_type', RegistrationOverrideRequest::TYPE_CAPACITY)
            ->whereIn('status', [RegistrationOverrideRequest::STATUS_PENDING, RegistrationOverrideRequest::STATUS_APPROVED])
            ->first();
            
        if ($existingRequest) {
            return $existingRequest;
        }
        
        // Calculate priority (graduating seniors and required courses get higher priority)
        $isGraduating = $this->isGraduatingSenior($student);
        $priority = $this->calculateCapacityOverridePriority($student, $isGraduating, $isRequired);
        
        // Create request
        $request = DB::transaction(function () use ($student, $currentTerm, $section, $justification, $isRequired, $isGraduating, $priority) {
            $request = RegistrationOverrideRequest::create([
                'student_id' => $student->id,
                'term_id' => $currentTerm->id,
                'request_type' => RegistrationOverrideRequest::TYPE_CAPACITY,
                'section_id' => $section->id,
                'course_id' => $section->course_id,
                'student_justification' => $justification,
                'supporting_documents' => [
                    'is_required_course' => $isRequired,
                    'is_graduating' => $isGraduating,
                    'current_enrollment' => $section->current_enrollment,
                    'capacity' => $section->enrollment_capacity,
                    'waitlist_position' => $this->getWaitlistPosition($student->id, $section->id)
                ],
                'priority_level' => $priority,
                'is_graduating_senior' => $isGraduating,
                'status' => RegistrationOverrideRequest::STATUS_PENDING
            ]);
            
            // Auto-approve for graduating seniors if it's a required course
            if ($isGraduating && $isRequired) {
                $this->autoApprove($request, 'Auto-approved: Graduating senior, required course');
            } else {
                $this->notifyApprover($request);
            }
            
            return $request;
        });
        
        // Notify student
        $student->user->notify(new OverrideRequestSubmitted($request));
        
        return $request;
    }

    /**
     * Process override request approval/denial
     */
    public function processOverrideRequest(
        RegistrationOverrideRequest $request,
        User $approver,
        string $decision,
        string $notes = null,
        array $conditions = []
    ): RegistrationOverrideRequest {
        // Validate approver has permission
        if (!$this->canApproveRequest($approver, $request)) {
            throw new Exception("You don't have permission to approve this type of request.");
        }
        
        // Validate request is still pending
        if ($request->status !== RegistrationOverrideRequest::STATUS_PENDING) {
            throw new Exception("This request has already been processed.");
        }
        
        DB::transaction(function () use ($request, $approver, $decision, $notes, $conditions) {
            $request->status = $decision;
            $request->approver_id = $approver->id;
            $request->approver_role = $this->getApproverRole($approver);
            $request->approval_date = now();
            $request->approver_notes = $notes;
            
            if ($decision === RegistrationOverrideRequest::STATUS_APPROVED) {
                // Generate override code
                $request->override_code = $this->generateUniqueOverrideCode();
                $request->override_expires_at = Carbon::now()->addDays(self::OVERRIDE_VALIDITY_DAYS);
                
                if (!empty($conditions)) {
                    $request->conditions = json_encode($conditions);
                }
                
                // Create specific permission records
                $this->createPermissionRecords($request);
            }
            
            $request->save();
            
            // Log the action
            Log::info('Override request processed', [
                'request_id' => $request->id,
                'decision' => $decision,
                'approver_id' => $approver->id,
                'type' => $request->request_type
            ]);
        });
        
        // Notify student of decision
        $request->student->user->notify(new OverrideRequestDecision($request));
        
        return $request;
    }

    /**
     * Use an override code during registration
     */
    public function useOverrideCode(string $code, int $studentId): array {
        $override = RegistrationOverrideRequest::where('override_code', $code)
            ->where('student_id', $studentId)
            ->first();
            
        if (!$override) {
            throw new Exception("Invalid override code.");
        }
        
        if (!$override->isOverrideCodeValid()) {
            if ($override->override_used) {
                throw new Exception("This override code has already been used.");
            }
            if ($override->override_expires_at && $override->override_expires_at->isPast()) {
                throw new Exception("This override code has expired.");
            }
            throw new Exception("This override code is not valid.");
        }
        
        // Mark as used
        $override->markAsUsed();
        
        return [
            'type' => $override->request_type,
            'details' => $override->toArray(),
            'message' => "Override code applied successfully: {$override->type_label}"
        ];
    }

    /**
     * Get pending override requests for an approver
     */
    public function getPendingRequestsForApprover(User $approver) {
        $query = RegistrationOverrideRequest::pending()
            ->with(['student.user', 'course', 'section.course', 'term']);
        
        // Filter based on approver role
        if ($approver->hasRole('advisor')) {
            // Advisors see credit overload requests for their students
            $studentIds = DB::table('advisor_assignments')
                ->where('advisor_id', $approver->id)
                ->pluck('student_id');
                
            $query->where('request_type', RegistrationOverrideRequest::TYPE_CREDIT_OVERLOAD)
                  ->whereIn('student_id', $studentIds);
                  
        } elseif ($approver->hasRole('department-head')) {
            // Department heads see prerequisite requests for their department's courses
            $departmentId = $approver->department_id;
            $courseIds = Course::where('department_id', $departmentId)->pluck('id');
            
            $query->where('request_type', RegistrationOverrideRequest::TYPE_PREREQUISITE)
                  ->whereIn('course_id', $courseIds);
                  
        } elseif ($approver->hasRole(['registrar', 'academic-administrator'])) {
            // Registrars see all types of requests
            // No additional filtering needed
        } else {
            // Other roles don't see any requests
            $query->whereRaw('1 = 0');
        }
        
        return $query->orderBy('priority_level', 'desc')
                    ->orderBy('created_at', 'asc')
                    ->paginate(20);
    }

    /**
     * Validate credit overload request
     */
    private function validateCreditOverloadRequest(Student $student, int $requestedCredits): void {
        if ($requestedCredits <= self::CREDIT_OVERLOAD_THRESHOLD) {
            throw new Exception("Credit overload not needed for {$requestedCredits} credits. Maximum without override is " . self::CREDIT_OVERLOAD_THRESHOLD . ".");
        }
        
        if ($requestedCredits > self::MAX_CREDITS_WITH_OVERRIDE) {
            throw new Exception("Cannot exceed " . self::MAX_CREDITS_WITH_OVERRIDE . " credits even with override.");
        }
        
        // Check GPA requirement (warning, not blocking)
        if ($student->cumulative_gpa < self::MIN_GPA_FOR_OVERLOAD) {
            Log::warning("Student requesting credit overload with GPA below threshold", [
                'student_id' => $student->id,
                'gpa' => $student->cumulative_gpa,
                'threshold' => self::MIN_GPA_FOR_OVERLOAD
            ]);
        }
        
        // Check academic standing
        if ($student->academic_standing === 'probation') {
            throw new Exception("Students on academic probation cannot request credit overload.");
        }
    }

    /**
     * Calculate current registered credits
     */
    private function calculateCurrentCredits(int $studentId, int $termId): int {
        return DB::table('registrations as r')
            ->join('course_sections as cs', 'r.section_id', '=', 'cs.id')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->where('r.student_id', $studentId)
            ->where('r.term_id', $termId)
            ->where('r.registration_status', 'enrolled')
            ->sum('c.credits');
    }

    /**
     * Get missing prerequisites
     */
    private function getMissingPrerequisites(Student $student, Course $course): array {
        $prerequisites = DB::table('course_prerequisites')
            ->where('course_id', $course->id)
            ->where('is_active', true)
            ->get();
            
        $missing = [];
        
        foreach ($prerequisites as $prereq) {
            // Check if student has completed the prerequisite
            $completed = DB::table('registrations as r')
                ->join('course_sections as cs', 'r.section_id', '=', 'cs.id')
                ->where('r.student_id', $student->id)
                ->where('cs.course_id', $prereq->prerequisite_course_id)
                ->where('r.registration_status', 'completed')
                ->where(function ($query) use ($prereq) {
                    if ($prereq->minimum_grade) {
                        // Check if grade meets minimum requirement
                        $query->whereNotNull('r.final_grade')
                              ->whereRaw('r.grade_points >= ?', [$this->gradeToPoints($prereq->minimum_grade)]);
                    }
                })
                ->exists();
                
            if (!$completed) {
                $prereqCourse = Course::find($prereq->prerequisite_course_id);
                $missing[] = [
                    'id' => $prereq->prerequisite_course_id,
                    'code' => $prereqCourse->code ?? 'Unknown',
                    'title' => $prereqCourse->title ?? 'Unknown Course',
                    'minimum_grade' => $prereq->minimum_grade
                ];
            }
        }
        
        return $missing;
    }

    /**
     * Check if student is graduating senior
     */
    private function isGraduatingSenior(Student $student): bool {
        // Check if student is in their final term
        $expectedGraduation = $student->expected_graduation_date;
        
        if (!$expectedGraduation) {
            return false;
        }
        
        $currentTerm = $this->getCurrentTerm();
        
        // Student is graduating if their expected graduation is within the current or next term
        return Carbon::parse($expectedGraduation)->between(
            $currentTerm->start_date,
            $currentTerm->end_date->addMonths(4)
        );
    }

    /**
     * Calculate request priority
     */
    private function calculateRequestPriority(Student $student, string $requestType): int {
        $priority = 5; // Base priority
        
        // Graduating seniors get highest priority
        if ($this->isGraduatingSenior($student)) {
            $priority += 3;
        }
        
        // High GPA students get priority
        if ($student->cumulative_gpa >= 3.5) {
            $priority += 2;
        } elseif ($student->cumulative_gpa >= 3.0) {
            $priority += 1;
        }
        
        // Seniors get priority over other classes
        if ($student->academic_level === 'senior') {
            $priority += 1;
        }
        
        return min($priority, 10); // Cap at 10
    }

    /**
     * Calculate capacity override priority
     */
    private function calculateCapacityOverridePriority(Student $student, bool $isGraduating, bool $isRequired): int {
        $priority = 5;
        
        if ($isGraduating) {
            $priority += 4;
        }
        
        if ($isRequired) {
            $priority += 3;
        }
        
        // Check waitlist position
        $waitlistPosition = $this->getWaitlistPosition($student->id, null);
        if ($waitlistPosition && $waitlistPosition <= 5) {
            $priority += 2;
        }
        
        return min($priority, 10);
    }

    /**
     * Get waitlist position
     */
    private function getWaitlistPosition(int $studentId, ?int $sectionId): ?int {
        if (!$sectionId) {
            return null;
        }
        
        $waitlist = DB::table('waitlists')
            ->where('student_id', $studentId)
            ->where('section_id', $sectionId)
            ->where('status', 'waiting')
            ->first();
            
        return $waitlist ? $waitlist->position : null;
    }

    /**
     * Check if request can be auto-approved
     */
    private function checkAutoApproval(RegistrationOverrideRequest $request): bool {
        // Get auto-approval rules
        $rules = DB::table('override_approval_routes')
            ->where('request_type', $request->request_type)
            ->where('is_active', true)
            ->first();
            
        if (!$rules || !$rules->auto_approve_conditions) {
            return false;
        }
        
        $conditions = json_decode($rules->auto_approve_conditions, true);
        $student = $request->student;
        
        // Check credit overload auto-approval
        if ($request->request_type === RegistrationOverrideRequest::TYPE_CREDIT_OVERLOAD) {
            if (isset($conditions['min_gpa']) && $student->cumulative_gpa >= $conditions['min_gpa']) {
                if (isset($conditions['max_credits']) && $request->requested_credits <= $conditions['max_credits']) {
                    return true;
                }
            }
        }
        
        // Check capacity override auto-approval for graduating seniors
        if ($request->request_type === RegistrationOverrideRequest::TYPE_CAPACITY) {
            if (isset($conditions['graduating_senior']) && $conditions['graduating_senior'] && $request->is_graduating_senior) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Auto-approve a request
     */
    private function autoApprove(RegistrationOverrideRequest $request, string $reason = 'Auto-approved based on criteria'): void {
        $request->status = RegistrationOverrideRequest::STATUS_APPROVED;
        $request->approval_date = now();
        $request->approver_notes = $reason;
        $request->override_code = $this->generateUniqueOverrideCode();
        $request->override_expires_at = Carbon::now()->addDays(self::OVERRIDE_VALIDITY_DAYS);
        $request->save();
        
        // Create permission records
        $this->createPermissionRecords($request);
        
        Log::info('Request auto-approved', [
            'request_id' => $request->id,
            'type' => $request->request_type,
            'student_id' => $request->student_id
        ]);
    }

    /**
     * Create permission records after approval
     */
    private function createPermissionRecords(RegistrationOverrideRequest $request): void {
        switch ($request->request_type) {
            case RegistrationOverrideRequest::TYPE_CREDIT_OVERLOAD:
                CreditOverloadPermission::create([
                    'student_id' => $request->student_id,
                    'term_id' => $request->term_id,
                    'max_credits' => $request->requested_credits,
                    'approved_by' => $request->approver_id ?? 0,
                    'approved_at' => now(),
                    'reason' => $request->student_justification,
                    'conditions' => $request->conditions,
                    'valid_until' => Carbon::now()->endOfMonth()->addMonth(),
                    'is_active' => true
                ]);
                break;
                
            case RegistrationOverrideRequest::TYPE_PREREQUISITE:
                $missingPrereqs = $request->supporting_documents['missing_prerequisites'] ?? [];
                
                foreach ($missingPrereqs as $prereq) {
                    PrerequisiteWaiver::create([
                        'student_id' => $request->student_id,
                        'course_id' => $request->course_id,
                        'waived_prerequisite_id' => $prereq['id'] ?? null,
                        'term_id' => $request->term_id,
                        'reason' => $request->supporting_documents['reason'] ?? 'override',
                        'justification' => $request->student_justification,
                        'supporting_evidence' => $request->supporting_documents['evidence'] ?? [],
                        'approved_by' => $request->approver_id ?? 0,
                        'approved_at' => now(),
                        'expires_at' => Carbon::now()->addYear(),
                        'is_active' => true
                    ]);
                }
                break;
                
            case RegistrationOverrideRequest::TYPE_CAPACITY:
                SpecialRegistrationFlag::create([
                    'student_id' => $request->student_id,
                    'term_id' => $request->term_id,
                    'flag_type' => SpecialRegistrationFlag::FLAG_CAPACITY_OVERRIDE,
                    'flag_value' => [
                        'section_id' => $request->section_id,
                        'override_code' => $request->override_code
                    ],
                    'authorized_by' => $request->approver_id ?? 0,
                    'valid_from' => now(),
                    'valid_until' => Carbon::now()->addDays(self::OVERRIDE_VALIDITY_DAYS),
                    'notes' => 'Capacity override approved',
                    'is_active' => true
                ]);
                break;
        }
    }

    /**
     * Generate unique override code
     */
    private function generateUniqueOverrideCode(): string {
        do {
            $code = strtoupper(Str::random(self::OVERRIDE_CODE_LENGTH));
        } while (RegistrationOverrideRequest::where('override_code', $code)->exists());
        
        return $code;
    }

    /**
     * Check if user can approve request
     */
    private function canApproveRequest(User $user, RegistrationOverrideRequest $request): bool {
        // Super admin can approve anything
        if ($user->hasRole(['super-administrator', 'admin'])) {
            return true;
        }
        
        // Check based on request type
        switch ($request->request_type) {
            case RegistrationOverrideRequest::TYPE_CREDIT_OVERLOAD:
                return $user->hasRole(['advisor', 'registrar', 'academic-administrator']);
                
            case RegistrationOverrideRequest::TYPE_PREREQUISITE:
                if ($user->hasRole(['department-head', 'registrar'])) {
                    // Department head can only approve for their department
                    if ($user->hasRole('department-head') && $request->course) {
                        return $request->course->department_id === $user->department_id;
                    }
                    return true;
                }
                return false;
                
            case RegistrationOverrideRequest::TYPE_CAPACITY:
            case RegistrationOverrideRequest::TYPE_TIME_CONFLICT:
            case RegistrationOverrideRequest::TYPE_LATE_REGISTRATION:
                return $user->hasRole(['registrar', 'academic-administrator']);
                
            default:
                return false;
        }
    }

    /**
     * Get approver role
     */
    private function getApproverRole(User $user): string {
        if ($user->hasRole('registrar')) {
            return 'registrar';
        } elseif ($user->hasRole('advisor')) {
            return 'advisor';
        } elseif ($user->hasRole('department-head')) {
            return 'department_head';
        } elseif ($user->hasRole('academic-administrator')) {
            return 'academic_administrator';
        } else {
            return 'admin';
        }
    }

    /**
     * Notify appropriate approver
     */
    private function notifyApprover(RegistrationOverrideRequest $request): void {
        // Get routing rules
        $route = DB::table('override_approval_routes')
            ->where('request_type', $request->request_type)
            ->where('is_active', true)
            ->first();
            
        if (!$route) {
            Log::error('No approval route found for request type', ['type' => $request->request_type]);
            return;
        }
        
        // Find approvers based on role
        $approvers = User::role($route->approver_role)->get();
        
        // For advisor approval, notify the student's advisor
        if ($route->approver_role === 'advisor') {
            $advisorId = DB::table('advisor_assignments')
                ->where('student_id', $request->student_id)
                ->value('advisor_id');
                
            if ($advisorId) {
                $approvers = User::where('id', $advisorId)->get();
            }
        }
        
        // For department head approval, notify the relevant department head
        if ($route->approver_role === 'department_head' && $request->course) {
            $departmentId = $request->course->department_id;
            $approvers = User::role('department-head')
                ->where('department_id', $departmentId)
                ->get();
        }
        
        // Send notifications
        foreach ($approvers as $approver) {
            $approver->notify(new OverrideRequestSubmitted($request));
        }
    }

    /**
     * Check for existing request
     */
    private function checkExistingRequest(int $studentId, int $termId, string $type, ?int $courseId = null) {
        $query = RegistrationOverrideRequest::where('student_id', $studentId)
            ->where('term_id', $termId)
            ->where('request_type', $type)
            ->whereIn('status', [
                RegistrationOverrideRequest::STATUS_PENDING, 
                RegistrationOverrideRequest::STATUS_APPROVED
            ]);
            
        if ($courseId) {
            $query->where('course_id', $courseId);
        }
        
        return $query->first();
    }

    /**
     * Get current academic term
     */
    private function getCurrentTerm(): AcademicTerm {
        $term = AcademicTerm::where('is_current', true)->first();
        
        if (!$term) {
            throw new Exception("No active academic term found.");
        }
        
        return $term;
    }

    /**
     * Convert letter grade to grade points
     */
    private function gradeToPoints(string $grade): float {
        $gradePoints = [
            'A' => 4.0,
            'A-' => 3.7,
            'B+' => 3.3,
            'B' => 3.0,
            'B-' => 2.7,
            'C+' => 2.3,
            'C' => 2.0,
            'C-' => 1.7,
            'D+' => 1.3,
            'D' => 1.0,
            'F' => 0.0
        ];
        
        return $gradePoints[$grade] ?? 0.0;
    }
}