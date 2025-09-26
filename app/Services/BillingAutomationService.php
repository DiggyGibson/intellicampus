<?php
// app/Services/BillingAutomationService.php

namespace App\Services;

use App\Models\{
    Student, StudentAccount, BillingItem, Invoice, 
    FeeStructure, AcademicTerm, Registration, 
    FinancialTransaction, PaymentPlan, Enrollment
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BillingAutomationService
{
    /**
     * Generate billing for a term
     */
    public function generateTermBilling($termId, $generateFor = 'all', $programId = null, $studentId = null)
    {
        $term = AcademicTerm::findOrFail($termId);
        $results = ['processed' => 0, 'errors' => 0, 'error_messages' => []];
        
        // Get students to bill based on criteria
        $students = $this->getStudentsToBill($termId, $generateFor, $programId, $studentId);
        
        foreach ($students as $student) {
            try {
                DB::transaction(function () use ($student, $term, &$results) {
                    $this->generateStudentBilling($student, $term);
                    $results['processed']++;
                });
            } catch (\Exception $e) {
                $results['errors']++;
                $results['error_messages'][] = "Student {$student->student_id}: {$e->getMessage()}";
                Log::error('Billing generation failed', [
                    'student_id' => $student->id,
                    'term_id' => $term->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Send billing notifications
        if ($results['processed'] > 0) {
            $this->sendBillingNotifications($term);
        }
        
        return $results;
    }
    
    /**
     * Generate billing for individual student
     */
    public function generateStudentBilling($student, $term)
    {
        // Get or create student account
        $account = StudentAccount::firstOrCreate(
            ['student_id' => $student->id],
            [
                'account_number' => $this->generateAccountNumber($student),
                'balance' => 0,
                'status' => 'active'
            ]
        );
        
        // Get student's registrations for the term
        $registrations = Registration::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->where('registration_status', 'enrolled')
            ->get();
        
        // Alternative: Use enrollments if registrations don't exist
        if ($registrations->isEmpty()) {
            $registrations = Enrollment::where('student_id', $student->id)
                ->where('term_id', $term->id)
                ->where('enrollment_status', 'enrolled')
                ->get();
        }
        
        if ($registrations->isEmpty()) {
            return; // No enrollments, no charges
        }
        
        // Calculate total credits
        $totalCredits = $this->calculateTotalCredits($registrations);
        
        // Get fee structure for student's program
        $feeStructure = $this->getFeeStructure($student, $term);
        
        // Generate charges
        $charges = [];
        
        // 1. Tuition charges
        $tuitionCharge = $this->calculateTuition($student, $totalCredits, $feeStructure);
        if ($tuitionCharge > 0) {
            $charges[] = $this->createBillingItem($student, $term, 'tuition', 
                'Tuition - ' . $totalCredits . ' credits', $tuitionCharge);
        }
        
        // 2. Mandatory fees
        $fees = $this->calculateMandatoryFees($student, $feeStructure, $term);
        foreach ($fees as $fee) {
            $charges[] = $this->createBillingItem($student, $term, 'fee', 
                $fee['description'], $fee['amount']);
        }
        
        // 3. Course-specific fees
        foreach ($registrations as $registration) {
            $courseFees = $this->getCourseSpecificFees($registration);
            foreach ($courseFees as $fee) {
                $charges[] = $this->createBillingItem($student, $term, 'course_fee', 
                    $fee['description'], $fee['amount'], $registration->id);
            }
        }
        
        // 4. Housing charges (if applicable)
        $housingCharges = $this->getHousingCharges($student, $term);
        if ($housingCharges > 0) {
            $charges[] = $this->createBillingItem($student, $term, 'housing', 
                'Housing - ' . $term->name, $housingCharges);
        }
        
        // 5. Meal plan charges (if applicable)
        $mealPlanCharges = $this->getMealPlanCharges($student, $term);
        if ($mealPlanCharges > 0) {
            $charges[] = $this->createBillingItem($student, $term, 'meal_plan', 
                'Meal Plan - ' . $term->name, $mealPlanCharges);
        }
        
        // Generate invoice
        $invoice = $this->generateInvoice($student, $term, $charges);
        
        // Update account balance
        $this->updateAccountBalance($account);
        
        // Check for automatic payment plan
        $this->checkAutoPaymentPlan($student, $account, $invoice);
        
        // Apply financial aid if available
        $this->applyFinancialAid($student, $term, $account);
        
        return $invoice;
    }
    
    /**
     * Calculate total credits from registrations
     */
    private function calculateTotalCredits($registrations)
    {
        $totalCredits = 0;
        
        foreach ($registrations as $registration) {
            // Check if it's a Registration or Enrollment model
            if (isset($registration->section)) {
                $totalCredits += $registration->section->course->credits ?? 0;
            } elseif (isset($registration->course)) {
                $totalCredits += $registration->course->credits ?? 0;
            }
        }
        
        return $totalCredits;
    }
    
    /**
     * Calculate tuition based on credits and fee structure
     */
    private function calculateTuition($student, $totalCredits, $feeStructure)
    {
        if (!$feeStructure) {
            // Use default tuition rate if no fee structure
            $defaultRate = config('billing.default_tuition_per_credit', 500);
            return $defaultRate * $totalCredits;
        }
        
        // Check for flat rate tuition
        if ($feeStructure->flat_tuition_amount && 
            $totalCredits >= $feeStructure->flat_tuition_min_credits &&
            $totalCredits <= $feeStructure->flat_tuition_max_credits) {
            return $feeStructure->flat_tuition_amount;
        }
        
        // Per credit calculation
        $baseRate = $feeStructure->tuition_per_credit ?? 
                   $feeStructure->per_credit_amount ?? 
                   config('billing.default_tuition_per_credit', 500);
        
        // Apply residency multiplier
        $multiplier = 1.0;
        if ($student->residency_status === 'out_state') {
            $multiplier = $feeStructure->out_state_multiplier ?? 1.5;
        } elseif ($student->residency_status === 'international') {
            $multiplier = $feeStructure->international_multiplier ?? 2.0;
        }
        
        return $baseRate * $totalCredits * $multiplier;
    }
    
    /**
     * Calculate mandatory fees
     */
    private function calculateMandatoryFees($student, $feeStructure, $term)
    {
        $fees = [];
        
        // Get mandatory fees from fee structure
        if ($feeStructure) {
            if ($feeStructure->registration_fee > 0) {
                $fees[] = [
                    'description' => 'Registration Fee',
                    'amount' => $feeStructure->registration_fee
                ];
            }
            
            if ($feeStructure->technology_fee > 0) {
                $fees[] = [
                    'description' => 'Technology Fee',
                    'amount' => $feeStructure->technology_fee
                ];
            }
            
            if (($feeStructure->activity_fee ?? 0) > 0) {
                $fees[] = [
                    'description' => 'Student Activity Fee',
                    'amount' => $feeStructure->activity_fee
                ];
            }
            
            if (($feeStructure->health_fee ?? 0) > 0) {
                $fees[] = [
                    'description' => 'Health Services Fee',
                    'amount' => $feeStructure->health_fee
                ];
            }
        }
        
        // Also check for standalone mandatory fees
        $mandatoryFees = FeeStructure::where('is_mandatory', true)
            ->where('is_active', true)
            ->where(function($query) use ($student) {
                $query->whereNull('program_id')
                      ->orWhere('program_id', $student->program_id);
            })
            ->where(function($query) use ($term) {
                $query->whereNull('term_id')
                      ->orWhere('term_id', $term->id);
            })
            ->get();
        
        foreach ($mandatoryFees as $fee) {
            // Skip if already added from main fee structure
            $exists = collect($fees)->contains('description', $fee->name);
            if (!$exists) {
                $fees[] = [
                    'description' => $fee->name,
                    'amount' => $fee->amount
                ];
            }
        }
        
        return $fees;
    }
    
    /**
     * Create billing item
     */
    private function createBillingItem($student, $term, $type, $description, $amount, $referenceId = null)
    {
        // Check if billing item already exists
        $exists = BillingItem::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->where('description', $description)
            ->first();
        
        if ($exists) {
            return $exists;
        }
        
        return BillingItem::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'type' => 'charge',
            'description' => $description,
            'amount' => $amount,
            'balance' => $amount,
            'due_date' => $term->fee_deadline ?? Carbon::now()->addDays(30),
            'status' => 'billed',
            'reference_type' => $type,
            'reference_id' => $referenceId
        ]);
    }
    
    /**
     * Generate invoice for charges
     */
    private function generateInvoice($student, $term, $charges)
    {
        if (empty($charges)) {
            return null;
        }
        
        $totalAmount = collect($charges)->sum('amount');
        
        $invoice = Invoice::create([
            'invoice_number' => $this->generateInvoiceNumber(),
            'student_id' => $student->id,
            'term_id' => $term->id,
            'invoice_date' => now(),
            'due_date' => $term->fee_deadline ?? Carbon::now()->addDays(30),
            'total_amount' => $totalAmount,
            'balance' => $totalAmount,
            'status' => 'sent',
            'line_items' => json_encode(collect($charges)->map(function ($charge) {
                return [
                    'id' => $charge->id,
                    'description' => $charge->description,
                    'amount' => $charge->amount
                ];
            }))
        ]);
        
        return $invoice;
    }
    
    /**
     * Update student account balance
     */
    private function updateAccountBalance($account)
    {
        $totalCharges = BillingItem::where('student_id', $account->student_id)
            ->where('type', 'charge')
            ->where('status', '!=', 'cancelled')
            ->sum('balance');
        
        $totalCredits = BillingItem::where('student_id', $account->student_id)
            ->where('type', 'credit')
            ->where('status', '!=', 'cancelled')
            ->sum('amount');
        
        $account->balance = $totalCharges - abs($totalCredits);
        $account->save();
        
        // Check for financial hold
        $this->checkFinancialHold($account);
    }
    
    /**
     * Check and apply financial hold if necessary
     */
    private function checkFinancialHold($account)
    {
        $holdThreshold = config('billing.financial_hold_threshold', 500);
        
        // Check for overdue items
        $overdueAmount = BillingItem::where('student_id', $account->student_id)
            ->where('due_date', '<', now())
            ->where('status', '!=', 'paid')
            ->sum('balance');
        
        if ($overdueAmount > $holdThreshold) {
            $account->has_financial_hold = true;
            $account->hold_amount = $overdueAmount;
            $account->hold_reason = 'Overdue balance: $' . number_format($overdueAmount, 2);
            $account->hold_placed_date = now();
            $account->save();
            
            // Also create registration hold
            DB::table('registration_holds')->updateOrInsert(
                [
                    'student_id' => $account->student_id,
                    'hold_type' => 'financial'
                ],
                [
                    'reason' => $account->hold_reason,
                    'placed_date' => now(),
                    'placed_by' => 1, // System user
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        } elseif ($account->has_financial_hold && $overdueAmount <= 0) {
            // Remove hold if balance is clear
            $account->has_financial_hold = false;
            $account->hold_amount = null;
            $account->hold_reason = null;
            $account->save();
            
            DB::table('registration_holds')
                ->where('student_id', $account->student_id)
                ->where('hold_type', 'financial')
                ->delete();
        }
    }
    
    /**
     * Apply financial aid to account
     */
    private function applyFinancialAid($student, $term, $account)
    {
        $aids = \App\Models\FinancialAidAward::where('student_id', $student->id)
            ->where('term_id', $term->id)
            ->where('status', 'accepted')
            ->get();
        
        foreach ($aids as $aid) {
            if ($aid->disbursed_amount < $aid->amount) {
                $disbursementAmount = $aid->amount - $aid->disbursed_amount;
                
                // Create credit billing item
                BillingItem::create([
                    'student_id' => $student->id,
                    'term_id' => $term->id,
                    'type' => 'credit',
                    'description' => 'Financial Aid: ' . ($aid->aidType->name ?? 'Grant'),
                    'amount' => -$disbursementAmount,
                    'balance' => 0,
                    'status' => 'applied',
                    'reference_type' => 'financial_aid',
                    'reference_id' => $aid->id
                ]);
                
                // Update aid disbursement
                $aid->disbursed_amount += $disbursementAmount;
                $aid->last_disbursement_date = now();
                $aid->save();
                
                // Update account
                $account->total_aid = ($account->total_aid ?? 0) + $disbursementAmount;
                $account->balance -= $disbursementAmount;
                $account->save();
            }
        }
    }
    
    /**
     * Process late fees
     */
    public function processLateFees()
    {
        $lateFeeAmount = config('billing.late_fee_amount', 50);
        $gracePeriodDays = config('billing.late_fee_grace_days', 10);
        
        $overdueItems = BillingItem::where('due_date', '<', Carbon::now()->subDays($gracePeriodDays))
            ->where('status', '!=', 'paid')
            ->where('type', 'charge')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('billing_items as bi2')
                    ->whereRaw('bi2.reference_id = billing_items.id')
                    ->where('bi2.reference_type', 'late_fee');
            })
            ->get();
        
        foreach ($overdueItems as $item) {
            // Create late fee
            BillingItem::create([
                'student_id' => $item->student_id,
                'term_id' => $item->term_id,
                'type' => 'charge',
                'description' => 'Late Fee for ' . $item->description,
                'amount' => $lateFeeAmount,
                'balance' => $lateFeeAmount,
                'due_date' => now(),
                'status' => 'billed',
                'reference_type' => 'late_fee',
                'reference_id' => $item->id
            ]);
            
            // Update account balance
            $account = StudentAccount::where('student_id', $item->student_id)->first();
            if ($account) {
                $account->balance += $lateFeeAmount;
                $account->save();
                
                // Log transaction
                FinancialTransaction::create([
                    'student_id' => $item->student_id,
                    'account_id' => $account->id,
                    'transaction_type' => 'late_fee',
                    'amount' => $lateFeeAmount,
                    'description' => 'Late fee assessed',
                    'reference_type' => 'billing_item',
                    'reference_id' => $item->id
                ]);
            }
        }
        
        return count($overdueItems);
    }
    
    /**
     * Helper methods
     */
    private function generateAccountNumber($student)
    {
        $year = date('Y');
        $sequence = str_pad($student->id, 6, '0', STR_PAD_LEFT);
        return "ACC{$year}{$sequence}";
    }
    
    private function generateInvoiceNumber()
    {
        $date = date('Ymd');
        $sequence = Invoice::whereDate('created_at', today())->count() + 1;
        $random = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        return "INV{$date}{$random}";
    }
    
    private function getStudentsToBill($termId, $generateFor, $programId, $studentId)
    {
        $query = Student::query();
        
        if ($generateFor === 'student' && $studentId) {
            $query->where('id', $studentId);
        } elseif ($generateFor === 'program' && $programId) {
            $query->where('program_id', $programId);
        }
        
        // Only active students with enrollments
        $query->where('enrollment_status', 'active')
            ->where(function($q) use ($termId) {
                // Check both registrations and enrollments tables
                $q->whereHas('registrations', function ($q2) use ($termId) {
                    $q2->where('term_id', $termId)
                       ->where('registration_status', 'enrolled');
                })->orWhereHas('enrollments', function ($q2) use ($termId) {
                    $q2->where('term_id', $termId)
                       ->where('enrollment_status', 'enrolled');
                });
            });
        
        return $query->get();
    }
    
    private function getFeeStructure($student, $term)
    {
        return FeeStructure::where(function($query) use ($student) {
                $query->where('program_id', $student->program_id)
                      ->orWhereNull('program_id');
            })
            ->where(function($query) use ($term) {
                $query->where('term_id', $term->id)
                      ->orWhereNull('term_id');
            })
            ->where('is_active', true)
            ->orderBy('program_id', 'desc') // Prioritize program-specific
            ->orderBy('term_id', 'desc')     // Then term-specific
            ->first();
    }
    
    private function getCourseSpecificFees($registration)
    {
        $fees = [];
        
        // Get course from registration or enrollment
        $course = null;
        if (isset($registration->section->course)) {
            $course = $registration->section->course;
        } elseif (isset($registration->course)) {
            $course = $registration->course;
        }
        
        if ($course) {
            // Check for lab fee
            if (($course->lab_fee ?? 0) > 0) {
                $fees[] = [
                    'description' => "Lab Fee - {$course->course_code}",
                    'amount' => $course->lab_fee
                ];
            }
            
            // Check for material fee
            if (($course->material_fee ?? 0) > 0) {
                $fees[] = [
                    'description' => "Material Fee - {$course->course_code}",
                    'amount' => $course->material_fee
                ];
            }
        }
        
        return $fees;
    }
    
    private function getHousingCharges($student, $term)
    {
        // Check for housing assignment
        // This would integrate with housing module when built
        // For now, return 0 or check a basic housing_assignments table if exists
        
        if (DB::getSchemaBuilder()->hasTable('housing_assignments')) {
            $assignment = DB::table('housing_assignments')
                ->where('student_id', $student->id)
                ->where('term_id', $term->id)
                ->where('status', 'active')
                ->first();
            
            if ($assignment) {
                return $assignment->rate ?? 0;
            }
        }
        
        return 0;
    }
    
    private function getMealPlanCharges($student, $term)
    {
        // Check for meal plan enrollment
        // This would integrate with meal plan module when built
        
        if (DB::getSchemaBuilder()->hasTable('meal_plan_enrollments')) {
            $enrollment = DB::table('meal_plan_enrollments')
                ->where('student_id', $student->id)
                ->where('term_id', $term->id)
                ->where('status', 'active')
                ->first();
            
            if ($enrollment) {
                return $enrollment->cost ?? 0;
            }
        }
        
        return 0;
    }
    
    private function checkAutoPaymentPlan($student, $account, $invoice)
    {
        // Check if student qualifies for automatic payment plan
        $threshold = config('billing.payment_plan_threshold', 1000);
        
        if ($invoice && $invoice->total_amount > $threshold && !$account->has_financial_hold) {
            // Could auto-create payment plan offer or notification
            Log::info('Student eligible for payment plan', [
                'student_id' => $student->id,
                'amount' => $invoice->total_amount
            ]);
        }
    }
    
    private function sendBillingNotifications($term)
    {
        // Queue billing notification emails
        // This would integrate with notification system
        Log::info('Billing notifications would be sent for term: ' . $term->name);
        
        // You can implement email notifications here when ready
        // Example:
        // Notification::send($students, new BillingGenerated($term));
    }
}