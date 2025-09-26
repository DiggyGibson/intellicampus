<?php
// database/seeders/FinancialSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{FeeStructure, StudentAccount, Student, AcademicTerm};
use Illuminate\Support\Facades\DB;

class FinancialSeeder extends Seeder
{
    public function run()
    {
        // Create fee structures
        $fees = [
            ['name' => 'Undergraduate Tuition', 'code' => 'TUITION_UG', 'type' => 'tuition', 'frequency' => 'per_credit', 'amount' => 350, 'academic_level' => 'undergraduate'],
            ['name' => 'Graduate Tuition', 'code' => 'TUITION_GR', 'type' => 'tuition', 'frequency' => 'per_credit', 'amount' => 550, 'academic_level' => 'graduate'],
            ['name' => 'Registration Fee', 'code' => 'REG_FEE', 'type' => 'registration', 'frequency' => 'per_term', 'amount' => 150],
            ['name' => 'Technology Fee', 'code' => 'TECH_FEE', 'type' => 'technology', 'frequency' => 'per_term', 'amount' => 100],
            ['name' => 'Library Fee', 'code' => 'LIB_FEE', 'type' => 'library', 'frequency' => 'per_term', 'amount' => 50],
            ['name' => 'Student Activity Fee', 'code' => 'ACT_FEE', 'type' => 'activity', 'frequency' => 'per_term', 'amount' => 75],
            ['name' => 'Health Services Fee', 'code' => 'HEALTH_FEE', 'type' => 'health', 'frequency' => 'per_term', 'amount' => 200],
            ['name' => 'Lab Fee - Science', 'code' => 'LAB_SCI', 'type' => 'lab', 'frequency' => 'per_term', 'amount' => 150],
            ['name' => 'Lab Fee - Computer', 'code' => 'LAB_COMP', 'type' => 'lab', 'frequency' => 'per_term', 'amount' => 100],
        ];

        foreach ($fees as $fee) {
            FeeStructure::create(array_merge($fee, [
                'is_mandatory' => true,
                'is_active' => true,
                'effective_from' => now()->startOfYear(),
                'description' => "Standard {$fee['name']} for all eligible students"
            ]));
        }

        // Create student accounts for existing students
        $students = Student::all();
        $currentTerm = AcademicTerm::where('is_current', true)->first();

        foreach ($students as $student) {
            // Create account
            $account = StudentAccount::firstOrCreate(
                ['student_id' => $student->id],
                [
                    'balance' => 0,
                    'status' => 'active',
                    'credit_limit' => 500
                ]
            );

            // Generate some charges for testing
            if ($currentTerm && rand(0, 1)) {
                // Get enrollment count
                $enrollmentCount = DB::table('enrollments')
                    ->where('student_id', $student->id)
                    ->where('term_id', $currentTerm->id)
                    ->count();

                if ($enrollmentCount > 0) {
                    // Add tuition
                    $credits = $enrollmentCount * 3; // Assume 3 credits per course
                    $tuitionFee = FeeStructure::where('code', 'TUITION_UG')->first();
                    if ($tuitionFee) {
                        $account->addCharge(
                            $tuitionFee->amount * $credits,
                            "Tuition - {$currentTerm->name}",
                            $currentTerm->id,
                            now()->addDays(30),
                            $tuitionFee->id
                        );
                    }

                    // Add mandatory fees
                    $mandatoryFees = FeeStructure::where('frequency', 'per_term')
                                                 ->where('is_mandatory', true)
                                                 ->get();

                    foreach ($mandatoryFees as $fee) {
                        $account->addCharge(
                            $fee->amount,
                            "{$fee->name} - {$currentTerm->name}",
                            $currentTerm->id,
                            now()->addDays(30),
                            $fee->id
                        );
                    }
                }
            }
        }

        $this->command->info('Financial data seeded successfully!');
    }
}