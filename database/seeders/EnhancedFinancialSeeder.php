<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{
    FeeStructure, StudentAccount, Student, AcademicTerm, 
    BillingItem, Payment, Invoice, FinancialTransaction,
    PaymentPlan, FinancialAid, PaymentAllocation
};
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnhancedFinancialSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Starting Enhanced Financial Seeder...');
        
        // Get or create current term
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        if (!$currentTerm) {
            $this->command->info('Creating Spring 2025 term...');
            $currentTerm = AcademicTerm::create([
                'code' => 'SP2025',
                'name' => 'Spring 2025',
                'type' => 'spring',
                'academic_year' => 2025,
                'start_date' => '2025-01-15',
                'end_date' => '2025-05-15',
                'registration_start' => '2024-12-01',
                'registration_end' => '2025-01-10',
                'add_drop_deadline' => '2025-01-29',
                'withdrawal_deadline' => '2025-04-15',
                'grades_due_date' => '2025-05-22',
                'drop_deadline' => '2025-02-01',
                'is_current' => true,
                'is_active' => true,
                'important_dates' => [
                    'midterm_start' => '2025-03-10',
                    'midterm_end' => '2025-03-14',
                    'spring_break_start' => '2025-03-17',
                    'spring_break_end' => '2025-03-21',
                    'final_exam_start' => '2025-05-05',
                    'final_exam_end' => '2025-05-14'
                ]
            ]);
        }

        // Create previous term for comparison
        $previousTerm = AcademicTerm::where('code', 'FA2024')->first();
        if (!$previousTerm) {
            $this->command->info('Creating Fall 2024 term...');
            $previousTerm = AcademicTerm::create([
                'code' => 'FA2024',
                'name' => 'Fall 2024',
                'type' => 'fall',
                'academic_year' => 2024,
                'start_date' => '2024-08-15',
                'end_date' => '2024-12-15',
                'registration_start' => '2024-07-01',
                'registration_end' => '2024-08-10',
                'add_drop_deadline' => '2024-08-29',
                'withdrawal_deadline' => '2024-11-15',
                'grades_due_date' => '2024-12-22',
                'drop_deadline' => '2024-09-01',
                'is_current' => false,
                'is_active' => true,
                'important_dates' => [
                    'midterm_start' => '2024-10-14',
                    'midterm_end' => '2024-10-18',
                    'thanksgiving_break_start' => '2024-11-25',
                    'thanksgiving_break_end' => '2024-11-29',
                    'final_exam_start' => '2024-12-09',
                    'final_exam_end' => '2024-12-14'
                ]
            ]);
        }

        $this->command->info('Seeding comprehensive financial data...');

        // Create or update fee structures with more variety
        $fees = [
            ['name' => 'Undergraduate Tuition', 'code' => 'TUITION_UG', 'type' => 'tuition', 'frequency' => 'per_credit', 'amount' => 475, 'academic_level' => 'undergraduate'],
            ['name' => 'Graduate Tuition', 'code' => 'TUITION_GR', 'type' => 'tuition', 'frequency' => 'per_credit', 'amount' => 675, 'academic_level' => 'graduate'],
            ['name' => 'Registration Fee', 'code' => 'REG_FEE', 'type' => 'registration', 'frequency' => 'per_term', 'amount' => 150],
            ['name' => 'Technology Fee', 'code' => 'TECH_FEE', 'type' => 'technology', 'frequency' => 'per_term', 'amount' => 125],
            ['name' => 'Library Fee', 'code' => 'LIB_FEE', 'type' => 'library', 'frequency' => 'per_term', 'amount' => 75],
            ['name' => 'Student Activity Fee', 'code' => 'ACT_FEE', 'type' => 'activity', 'frequency' => 'per_term', 'amount' => 100],
            ['name' => 'Health Services Fee', 'code' => 'HEALTH_FEE', 'type' => 'health', 'frequency' => 'per_term', 'amount' => 250],
            ['name' => 'Lab Fee - Science', 'code' => 'LAB_SCI', 'type' => 'lab', 'frequency' => 'per_term', 'amount' => 175],
            ['name' => 'Lab Fee - Computer', 'code' => 'LAB_COMP', 'type' => 'lab', 'frequency' => 'per_term', 'amount' => 100],
            ['name' => 'Parking Permit', 'code' => 'PARKING', 'type' => 'other', 'frequency' => 'per_term', 'amount' => 200],
            ['name' => 'Late Registration Fee', 'code' => 'LATE_REG', 'type' => 'other', 'frequency' => 'once', 'amount' => 50],
            ['name' => 'International Student Fee', 'code' => 'INTL_FEE', 'type' => 'other', 'frequency' => 'per_term', 'amount' => 350],
            ['name' => 'Online Course Fee', 'code' => 'ONLINE_FEE', 'type' => 'other', 'frequency' => 'per_credit', 'amount' => 50],
        ];

        foreach ($fees as $fee) {
            FeeStructure::updateOrCreate(
                ['code' => $fee['code']],
                array_merge($fee, [
                    'is_mandatory' => !in_array($fee['code'], ['PARKING', 'LATE_REG', 'LAB_SCI', 'LAB_COMP', 'INTL_FEE', 'ONLINE_FEE']),
                    'is_active' => true,
                    'effective_from' => now()->startOfYear(),
                    'description' => "Standard {$fee['name']} for eligible students"
                ])
            );
        }
        $this->command->info('Fee structures created/updated successfully.');

        // Process students and create comprehensive financial records
        $students = Student::with('enrollments')->get();
        
        if ($students->isEmpty()) {
            $this->command->warn('No students found. Please run StudentSeeder first.');
            return;
        }

        $processedCount = 0;
        $totalStudents = $students->count();
        $shouldClearData = null;
        
        foreach ($students as $index => $student) {
            // Show progress
            if ($index % 10 == 0) {
                $this->command->info("Processing student " . ($index + 1) . " of {$totalStudents}...");
            }
            
            // Create or get student account
            $account = StudentAccount::firstOrCreate(
                ['student_id' => $student->id],
                [
                    'balance' => 0,
                    'status' => 'active',
                    'credit_limit' => 1000
                ]
            );

            // Clear old test data for this student (ask only once)
            if ($shouldClearData === null && $index == 0) {
                $shouldClearData = $this->command->confirm('Clear existing financial data for students? (recommended for clean test data)', true);
            }

            if ($shouldClearData) {
                // Delete in correct order to respect foreign key constraints
                PaymentAllocation::whereHas('payment', function($q) use ($student) {
                    $q->where('student_id', $student->id);
                })->delete();
                
                BillingItem::where('student_id', $student->id)->delete();
                Payment::where('student_id', $student->id)->delete();
                FinancialTransaction::where('student_id', $student->id)->delete();
            }

            // Generate billing items for current term
            $enrollmentCount = $student->enrollments()
                ->where('term_id', $currentTerm->id)
                ->where('enrollment_status', 'enrolled')
                ->count();

            // If no enrollments in current term, create some for testing (50% of students)
            if ($enrollmentCount == 0 && rand(1, 10) <= 5) {
                $enrollmentCount = rand(3, 6); // Simulate 3-6 course enrollments
            }

            if ($enrollmentCount > 0) {
                $totalCharges = 0;
                $credits = $enrollmentCount * 3; // Assume 3 credits per course
                
                // Add tuition charge
                $tuitionFee = FeeStructure::where('code', 'TUITION_UG')->first();
                if ($tuitionFee) {
                    $tuitionAmount = $tuitionFee->amount * $credits;
                    
                    $billingItem = BillingItem::create([
                        'student_id' => $student->id,
                        'fee_structure_id' => $tuitionFee->id,
                        'term_id' => $currentTerm->id,
                        'type' => 'charge',
                        'description' => "Tuition - {$currentTerm->name} {$currentTerm->academic_year} ({$credits} credits)",
                        'amount' => $tuitionAmount,
                        'balance' => $tuitionAmount,
                        'due_date' => Carbon::parse($currentTerm->start_date)->addDays(30),
                        'status' => 'pending'
                    ]);
                    $totalCharges += $tuitionAmount;

                    // Record as financial transaction
                    FinancialTransaction::create([
                        'student_id' => $student->id,
                        'type' => 'charge',
                        'amount' => $tuitionAmount,
                        'balance_before' => $account->balance,
                        'balance_after' => $account->balance + $tuitionAmount,
                        'description' => $billingItem->description,
                        'reference_type' => 'BillingItem',
                        'reference_id' => $billingItem->id
                    ]);
                }

                // Add mandatory fees
                $mandatoryFees = FeeStructure::where('is_mandatory', true)
                    ->where('frequency', 'per_term')
                    ->get();

                foreach ($mandatoryFees as $fee) {
                    $billingItem = BillingItem::create([
                        'student_id' => $student->id,
                        'fee_structure_id' => $fee->id,
                        'term_id' => $currentTerm->id,
                        'type' => 'charge',  // FIXED: Changed from $fee->type to 'charge'
                        'description' => "{$fee->name} - {$currentTerm->name} {$currentTerm->academic_year}",
                        'amount' => $fee->amount,  // FIXED: This was 'charge' before, now it's the actual amount
                        'balance' => $fee->amount,
                        'due_date' => Carbon::parse($currentTerm->start_date)->addDays(30),
                        'status' => 'pending'
                    ]);
                    $totalCharges += $fee->amount;

                    FinancialTransaction::create([
                        'student_id' => $student->id,
                        'type' => 'charge',
                        'amount' => $fee->amount,
                        'balance_before' => $totalCharges - $fee->amount,
                        'balance_after' => $totalCharges,
                        'description' => $billingItem->description,
                        'reference_type' => 'BillingItem',
                        'reference_id' => $billingItem->id
                    ]);
                }

                // Add optional fees for some students
                if (rand(1, 10) <= 3) { // 30% get parking permit
                    $parkingFee = FeeStructure::where('code', 'PARKING')->first();
                    if ($parkingFee) {
                        BillingItem::create([
                            'student_id' => $student->id,
                            'fee_structure_id' => $parkingFee->id,
                            'term_id' => $currentTerm->id,
                            'type' => 'charge',
                            'description' => "Parking Permit - {$currentTerm->name} {$currentTerm->academic_year}",
                            'amount' => $parkingFee->amount,
                            'balance' => $parkingFee->amount,
                            'due_date' => Carbon::parse($currentTerm->start_date)->addDays(30),
                            'status' => 'pending'
                        ]);
                        $totalCharges += $parkingFee->amount;
                    }
                }

                // Generate some payments for variety (70% of students have made payments)
                if (rand(1, 10) <= 7) {
                    $paymentCount = rand(1, 3);
                    $totalPaid = 0;
                    
                    for ($p = 0; $p < $paymentCount; $p++) {
                        // Vary payment amounts
                        $paymentAmount = $p == 0 
                            ? rand(1000, 3000) // First payment is larger
                            : rand(200, 800);   // Subsequent payments smaller
                        
                        if ($totalPaid + $paymentAmount > $totalCharges) {
                            $paymentAmount = $totalCharges - $totalPaid;
                        }

                        if ($paymentAmount <= 0) break;

                        $paymentDate = Carbon::now()->subDays(rand(1, 60));
                        $paymentMethods = ['card', 'bank_transfer', 'check', 'cash', 'financial_aid'];
                        $selectedMethod = $paymentMethods[array_rand($paymentMethods)];

                        $payment = Payment::create([
                            'student_id' => $student->id,
                            'payment_number' => 'PAY-' . date('YmdHis') . '-' . str_pad($student->id, 4, '0', STR_PAD_LEFT) . '-' . $p,
                            'amount' => $paymentAmount,
                            'payment_method' => $selectedMethod,
                            'payment_date' => $paymentDate,
                            'reference_number' => 'REF-' . strtoupper(uniqid()),
                            'status' => 'completed',
                            'processed_by' => 1,
                            'processed_at' => $paymentDate,
                            'applied_to_account' => true,
                            'notes' => "Payment for {$currentTerm->name} {$currentTerm->academic_year} fees",
                            'ip_address' => '127.0.0.1',
                            'user_agent' => 'Seeder'
                        ]);

                        // Create payment allocation
                        $unpaidItems = BillingItem::where('student_id', $student->id)
                            ->where('status', 'pending')
                            ->orderBy('due_date')
                            ->get();

                        $remainingAmount = $paymentAmount;
                        foreach ($unpaidItems as $item) {
                            if ($remainingAmount <= 0) break;
                            
                            $allocationAmount = min($remainingAmount, $item->amount);
                            
                            PaymentAllocation::create([
                                'payment_id' => $payment->id,
                                'billing_item_id' => $item->id,
                                'amount' => $allocationAmount
                            ]);

                            if ($allocationAmount >= $item->amount) {
                                $item->status = 'paid';
                                $item->balance = 0;
                                $item->save();
                            } else {
                                $item->balance = $item->balance - $allocationAmount;
                                $item->save();
                            }

                            $remainingAmount -= $allocationAmount;
                        }

                        // Record payment transaction
                        FinancialTransaction::create([
                            'student_id' => $student->id,
                            'type' => 'payment',
                            'amount' => $paymentAmount,
                            'balance_before' => $totalCharges - $totalPaid,
                            'balance_after' => $totalCharges - ($totalPaid + $paymentAmount),
                            'description' => "Payment received - {$selectedMethod}",
                            'reference_type' => 'Payment',
                            'reference_id' => $payment->id
                        ]);

                        $totalPaid += $paymentAmount;
                    }

                    // Update account balance
                    $account->balance = $totalCharges - $totalPaid;
                } else {
                    // No payments made - full balance due
                    $account->balance = $totalCharges;
                }

                $account->save();

                // Create financial aid for some students (30%)
                if (rand(1, 10) <= 3) {
                    $aidTypes = ['grant', 'scholarship', 'loan'];
                    $aidNames = [
                        'grant' => ['Federal Pell Grant', 'State Need Grant', 'Institutional Grant'],
                        'scholarship' => ['Merit Scholarship', 'Academic Excellence Award', 'Leadership Scholarship'],
                        'loan' => ['Federal Direct Loan', 'Subsidized Loan', 'Unsubsidized Loan']
                    ];
                    
                    $selectedType = $aidTypes[array_rand($aidTypes)];
                    $selectedName = $aidNames[$selectedType][array_rand($aidNames[$selectedType])];
                    
                    FinancialAid::create([
                        'student_id' => $student->id,
                        'term_id' => $currentTerm->id,
                        'type' => $selectedType,
                        'aid_name' => $selectedName,
                        'amount' => rand(500, 3000),
                        'status' => 'approved',
                        'award_date' => now()->subDays(rand(10, 60)),
                        'disbursement_date' => $currentTerm->start_date
                    ]);
                }

                // Create payment plan for some students with balance (20%)
                if ($account->balance > 1000 && rand(1, 10) <= 2) {
                    $installments = rand(3, 6);
                    PaymentPlan::create([
                        'student_id' => $student->id,
                        'term_id' => $currentTerm->id,
                        'plan_name' => "Payment Plan - {$student->student_id}",
                        'total_amount' => $account->balance,
                        'number_of_installments' => $installments,
                        'installment_amount' => round($account->balance / $installments, 2),
                        'start_date' => now(),
                        'end_date' => now()->addMonths($installments),
                        'status' => 'active',
                        'approved_by' => 1,
                        'approved_at' => now()->subDays(rand(1, 10))
                    ]);
                }

                $processedCount++;
            }

            // Generate some historical data for previous term (for reporting)
            if ($previousTerm && rand(1, 10) <= 8) {
                // Create previous term payment for revenue comparison
                Payment::create([
                    'student_id' => $student->id,
                    'payment_number' => 'PAY-' . $previousTerm->academic_year . '-HIST-' . str_pad($student->id, 4, '0', STR_PAD_LEFT) . '-' . rand(1000, 9999),
                    'amount' => rand(2000, 5000),
                    'payment_method' => ['card', 'bank_transfer', 'check'][rand(0, 2)],
                    'payment_date' => Carbon::parse($previousTerm->start_date)->addDays(rand(15, 45)),
                    'status' => 'completed',
                    'processed_by' => 1,
                    'processed_at' => Carbon::parse($previousTerm->start_date)->addDays(rand(15, 45)),
                    'applied_to_account' => true,
                    'notes' => "Payment for {$previousTerm->name} {$previousTerm->academic_year} fees",
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Historical Seeder',
                    'created_at' => Carbon::parse($previousTerm->start_date)->addDays(rand(15, 45)),
                    'updated_at' => Carbon::parse($previousTerm->start_date)->addDays(rand(15, 45))
                ]);
            }
        }

        // Display summary
        $this->command->info("\n========================================");
        $this->command->info("Financial Data Seeding Complete!");
        $this->command->info("========================================");
        $this->command->info("Students processed: {$processedCount} of {$totalStudents}");
        $this->command->info("Accounts with balance: " . StudentAccount::where('balance', '>', 0)->count());
        $this->command->info("Total payments: " . Payment::count());
        $this->command->info("Total billing items: " . BillingItem::count());
        $this->command->info("Financial aid awards: " . FinancialAid::count());
        $this->command->info("Payment plans created: " . PaymentPlan::count());
        $this->command->info("========================================\n");
    }
}