<?php

namespace App\Services;

use App\Models\Student;
use App\Models\CourseSection;
use App\Models\Registration;
use App\Models\RegistrationCart;
use App\Models\Enrollment;
use App\Models\RegistrationOverrideRequest;
use App\Models\AcademicTerm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RegistrationWorkflowService
{
    /**
     * Complete registration workflow from cart to enrollment
     */
    public function processRegistration(Student $student, AcademicTerm $term)
    {
        DB::beginTransaction();
        
        try {
            // Step 1: Get the validated cart
            $cart = RegistrationCart::where('student_id', $student->id)
                ->where('term_id', $term->id)
                ->where('status', 'validated')
                ->first();
            
            if (!$cart || empty($cart->section_ids)) {
                throw new \Exception('No validated cart found');
            }
            
            // Step 2: Create registration records
            $registrations = [];
            foreach ($cart->section_ids as $sectionId) {
                $registration = $this->createRegistration($student, $sectionId, $term);
                $registrations[] = $registration;
            }
            
            // Step 3: Validate all registrations
            $allValid = true;
            $errors = [];
            
            foreach ($registrations as $registration) {
                if (!$this->validateRegistration($registration)) {
                    $allValid = false;
                    $errors = array_merge($errors, $registration->validation_errors ?? []);
                }
            }
            
            if (!$allValid) {
                DB::rollback();
                return [
                    'success' => false,
                    'errors' => $errors
                ];
            }
            
            // Step 4: Check payment requirements
            $requiresPayment = $this->checkPaymentRequired($registrations);
            
            if ($requiresPayment) {
                // Mark registrations as pending payment
                foreach ($registrations as $registration) {
                    $registration->status = Registration::STATUS_PENDING;
                    $registration->save();
                }
                
                // Update cart status
                $cart->status = RegistrationCart::STATUS_SUBMITTED;
                $cart->save();
                
                DB::commit();
                
                return [
                    'success' => true,
                    'requires_payment' => true,
                    'registration_ids' => collect($registrations)->pluck('id')->toArray()
                ];
            }
            
            // Step 5: If no payment required, convert to enrollments
            $enrollments = [];
            foreach ($registrations as $registration) {
                $enrollment = $this->convertToEnrollment($registration);
                $enrollments[] = $enrollment;
            }
            
            // Step 6: Clear the cart
            $cart->status = RegistrationCart::STATUS_PROCESSED;
            $cart->save();
            
            DB::commit();
            
            return [
                'success' => true,
                'enrollments' => $enrollments
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Registration failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create a registration record
     */
    private function createRegistration(Student $student, $sectionId, AcademicTerm $term)
    {
        $section = CourseSection::findOrFail($sectionId);
        
        // Check for existing registration
        $existing = Registration::where('student_id', $student->id)
            ->where('section_id', $sectionId)
            ->where('term_id', $term->id)
            ->whereNull('deleted_at')
            ->first();
        
        if ($existing) {
            return $existing;
        }
        
        return Registration::create([
            'student_id' => $student->id,
            'section_id' => $sectionId,
            'term_id' => $term->id,
            'registration_date' => now(),
            'status' => Registration::STATUS_PENDING,
            'registration_type' => 'regular',
            'credits_attempted' => $section->course->credits
        ]);
    }
    
    /**
     * Validate a registration with override support
     */
    private function validateRegistration(Registration $registration)
    {
        $errors = [];
        $student = $registration->student;
        $section = $registration->section;
        
        // Check for overrides
        $overrides = $this->getStudentOverrides($student->id, $registration->term_id);
        
        // 1. Check prerequisites
        if (!$this->checkPrerequisites($registration, $overrides)) {
            $errors[] = 'Prerequisites not met for ' . $section->course->code;
        }
        
        // 2. Check time conflicts
        if (!$this->checkTimeConflicts($registration, $overrides)) {
            $errors[] = 'Time conflict detected for ' . $section->course->code;
        }
        
        // 3. Check capacity
        if (!$this->checkCapacity($registration, $overrides)) {
            $errors[] = 'Section is full for ' . $section->course->code;
        }
        
        // 4. Check credit limits
        if (!$this->checkCreditLimits($registration, $overrides)) {
            $errors[] = 'Credit limit exceeded';
        }
        
        // 5. Check holds
        if (!$this->checkHolds($registration)) {
            $errors[] = 'Registration hold prevents enrollment';
        }
        
        if (empty($errors)) {
            $registration->status = Registration::STATUS_VALIDATED;
            $registration->validated_at = now();
            $registration->validation_errors = null;
        } else {
            $registration->status = Registration::STATUS_FAILED;
            $registration->validation_errors = $errors;
        }
        
        $registration->save();
        
        return empty($errors);
    }
    
    /**
     * Get all active overrides for a student
     */
    private function getStudentOverrides($studentId, $termId)
    {
        return [
            'override_requests' => RegistrationOverrideRequest::where('student_id', $studentId)
                ->where('term_id', $termId)
                ->where('status', 'approved')
                ->where('override_used', false)
                ->where(function($q) {
                    $q->whereNull('override_expires_at')
                      ->orWhere('override_expires_at', '>', now());
                })
                ->get(),
            
            'credit_overload' => DB::table('credit_overload_permissions')
                ->where('student_id', $studentId)
                ->where('term_id', $termId)
                ->where('is_active', true)
                ->first(),
            
            'prerequisite_waivers' => DB::table('prerequisite_waivers')
                ->where('student_id', $studentId)
                ->where('term_id', $termId)
                ->where('is_active', true)
                ->get(),
            
            'special_flags' => DB::table('special_registration_flags')
                ->where('student_id', $studentId)
                ->where('term_id', $termId)
                ->where('is_active', true)
                ->get()
        ];
    }
    
    /**
     * Check prerequisites with override support
     */
    private function checkPrerequisites(Registration $registration, $overrides)
    {
        $course = $registration->section->course;
        
        // Check for prerequisite override
        foreach ($overrides['override_requests'] as $override) {
            if ($override->request_type === 'prerequisite' && 
                $override->course_id === $course->id) {
                // Mark override as used
                $override->override_used = true;
                $override->save();
                
                $registration->override_used = true;
                $registration->override_code = $override->override_code;
                $registration->override_type = 'prerequisite';
                
                return true;
            }
        }
        
        // Check prerequisite waivers
        foreach ($overrides['prerequisite_waivers'] as $waiver) {
            if ($waiver->course_id === $course->id) {
                return true;
            }
        }
        
        // Standard prerequisite check
        $prerequisites = $course->prerequisites;
        
        if ($prerequisites->isEmpty()) {
            return true;
        }
        
        $completedPrereqs = Enrollment::where('student_id', $registration->student_id)
            ->whereIn('status', ['completed', 'passed'])
            ->whereHas('section.course', function ($query) use ($prerequisites) {
                $query->whereIn('id', $prerequisites->pluck('prerequisite_course_id'));
            })
            ->count();
        
        return $completedPrereqs >= $prerequisites->count();
    }
    
    /**
     * Check time conflicts with override support
     */
    private function checkTimeConflicts(Registration $registration, $overrides)
    {
        // Check for time conflict override
        foreach ($overrides['special_flags'] as $flag) {
            if ($flag->flag_type === 'time_conflict_allowed') {
                return true;
            }
        }
        
        // Standard time conflict check
        $otherRegistrations = Registration::where('student_id', $registration->student_id)
            ->where('term_id', $registration->term_id)
            ->where('id', '!=', $registration->id)
            ->whereIn('status', [
                Registration::STATUS_VALIDATED,
                Registration::STATUS_ENROLLED
            ])
            ->with('section.schedules')
            ->get();
        
        // Simplified check - you'd implement full schedule conflict detection
        return true; // For now, assume no conflicts
    }
    
    /**
     * Check capacity with override support
     */
    private function checkCapacity(Registration $registration, $overrides)
    {
        $section = $registration->section;
        
        // Check for capacity override
        foreach ($overrides['override_requests'] as $override) {
            if ($override->request_type === 'capacity' && 
                $override->section_id === $section->id) {
                // Mark override as used
                $override->override_used = true;
                $override->save();
                
                $registration->override_used = true;
                $registration->override_code = $override->override_code;
                $registration->override_type = 'capacity';
                
                return true;
            }
        }
        
        // Standard capacity check
        return $section->current_enrollment < $section->enrollment_capacity;
    }
    
    /**
     * Check credit limits with override support
     */
    private function checkCreditLimits(Registration $registration, $overrides)
    {
        // Check for credit overload permission
        if ($overrides['credit_overload']) {
            $maxCredits = $overrides['credit_overload']->max_credits;
        } else {
            $maxCredits = 18; // Default max
        }
        
        // Calculate total credits
        $currentCredits = Registration::where('student_id', $registration->student_id)
            ->where('term_id', $registration->term_id)
            ->whereIn('status', [
                Registration::STATUS_VALIDATED,
                Registration::STATUS_ENROLLED
            ])
            ->join('course_sections', 'registrations.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->sum('courses.credits');
        
        return $currentCredits <= $maxCredits;
    }
    
    /**
     * Check for registration holds
     */
    private function checkHolds(Registration $registration)
    {
        // Check if student has any active holds
        $hasHolds = DB::table('registration_holds')
            ->where('student_id', $registration->student_id)
            ->where('is_active', true)
            ->where('prevents_registration', true)
            ->exists();
        
        return !$hasHolds;
    }
    
    /**
     * Check if payment is required
     */
    private function checkPaymentRequired($registrations)
    {
        // Logic to determine if payment is needed
        // For now, assume payment is always required
        return true;
    }
    
    /**
     * Convert registration to enrollment after payment
     */
    public function convertToEnrollment(Registration $registration)
    {
        // Check registration is ready
        if (!in_array($registration->status, [Registration::STATUS_VALIDATED, Registration::STATUS_CONFIRMED])) {
            throw new \Exception('Registration not ready for enrollment');
        }
        
        // Create enrollment
        $enrollment = Enrollment::create([
            'student_id' => $registration->student_id,
            'section_id' => $registration->section_id,
            'term_id' => $registration->term_id,
            'enrollment_date' => now(),
            'status' => 'enrolled',
            'grading_option' => $registration->grading_option ?? 'graded'
        ]);
        
        // Update registration
        $registration->status = Registration::STATUS_ENROLLED;
        $registration->enrollment_id = $enrollment->id;
        $registration->save();
        
        // Update section enrollment count
        $registration->section->increment('current_enrollment');
        
        return $enrollment;
    }
    
    /**
     * Process payment and complete registration
     */
    public function completeRegistrationAfterPayment(array $registrationIds)
    {
        DB::beginTransaction();
        
        try {
            $enrollments = [];
            
            foreach ($registrationIds as $id) {
                $registration = Registration::findOrFail($id);
                
                // Update payment status
                $registration->status = Registration::STATUS_CONFIRMED;
                $registration->confirmed_at = now();
                $registration->save();
                
                // Convert to enrollment
                $enrollment = $this->convertToEnrollment($registration);
                $enrollments[] = $enrollment;
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'enrollments' => $enrollments
            ];
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}