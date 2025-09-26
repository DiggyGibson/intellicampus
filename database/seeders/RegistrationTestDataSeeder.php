<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class RegistrationTestDataSeeder extends Seeder
{
    public function run()
    {
        echo "Starting Registration Test Data Seeder...\n";
        echo "====================================\n\n";
        
        // Get or create current term
        $currentTerm = $this->ensureCurrentTerm();
        echo "✓ Using academic term: {$currentTerm->name} (ID: {$currentTerm->id})\n\n";
        
        // Create registration periods
        $this->createRegistrationPeriods($currentTerm->id);
        
        // Create course prerequisites
        $this->createPrerequisites();
        
        // Update student statuses
        $this->updateStudentStatuses();
        
        // Create registration holds
        $this->createRegistrationHolds();
        
        echo "\n====================================\n";
        echo "✅ Registration test data seeded successfully!\n";
        echo "====================================\n";
    }
    
    private function ensureCurrentTerm()
    {
        $currentTerm = DB::table('academic_terms')->where('is_current', true)->first();
        
        if (!$currentTerm) {
            echo "Creating current academic term...\n";
            
            // Check which columns exist in academic_terms
            $columns = Schema::hasTable('academic_terms') ? Schema::getColumnListing('academic_terms') : [];
            
            $data = [
                'name' => 'Spring 2025',
                'code' => 'SP2025',
                'start_date' => '2025-01-15',
                'end_date' => '2025-05-15',
                'is_current' => true,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Add optional columns if they exist
            if (in_array('drop_deadline', $columns)) {
                $data['drop_deadline'] = '2025-02-01';
            }
            if (in_array('withdrawal_deadline', $columns)) {
                $data['withdrawal_deadline'] = '2025-04-01';
            }
            
            $termId = DB::table('academic_terms')->insertGetId($data);
            $currentTerm = DB::table('academic_terms')->find($termId);
        }
        
        return $currentTerm;
    }
    
    private function createRegistrationPeriods($termId)
    {
        echo "Setting up registration periods...\n";
        
        if (!Schema::hasTable('registration_periods')) {
            echo "  ⚠ registration_periods table doesn't exist. Skipping...\n";
            return;
        }
        
        $columns = Schema::getColumnListing('registration_periods');
        
        if (!in_array('period_type', $columns)) {
            echo "  ⚠ registration_periods doesn't have required columns. Skipping...\n";
            return;
        }
        
        $periods = [
            [
                'name' => 'Priority Registration',
                'type' => 'priority',
                'start' => Carbon::now()->subDays(7),
                'end' => Carbon::now()->subDays(5),
                'description' => 'Priority registration for seniors, athletes, and honors students'
            ],
            [
                'name' => 'Regular Registration',
                'type' => 'regular',
                'start' => Carbon::now()->subDays(4),
                'end' => Carbon::now()->addDays(30),
                'description' => 'Regular registration period for all students'
            ],
            [
                'name' => 'Late Registration',
                'type' => 'late',
                'start' => Carbon::now()->addDays(31),
                'end' => Carbon::now()->addDays(45),
                'description' => 'Late registration with additional fees'
            ]
        ];
        
        foreach ($periods as $period) {
            $exists = DB::table('registration_periods')
                ->where('term_id', $termId)
                ->where('period_type', $period['type'])
                ->exists();
            
            if (!$exists) {
                $data = [
                    'term_id' => $termId,
                    'period_type' => $period['type'],
                    'start_date' => $period['start']->format('Y-m-d'),
                    'end_date' => $period['end']->format('Y-m-d'),
                    'description' => $period['description'],
                    'is_active' => $period['type'] === 'regular',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                // Add optional columns if they exist
                if (in_array('student_levels', $columns)) {
                    if ($period['type'] === 'priority') {
                        $data['student_levels'] = json_encode(['senior', 'graduate']);
                    } else {
                        $data['student_levels'] = json_encode(['senior', 'junior', 'sophomore', 'freshman']);
                    }
                }
                
                if (in_array('priority_groups', $columns) && $period['type'] === 'priority') {
                    $data['priority_groups'] = json_encode(['athlete', 'honors', 'disability']);
                }
                
                DB::table('registration_periods')->insert($data);
                echo "  ✓ Created {$period['type']} registration period\n";
            } else {
                echo "  - {$period['type']} registration period already exists\n";
            }
        }
    }
    
    private function createPrerequisites()
    {
        echo "\nSetting up course prerequisites...\n";
        
        if (!Schema::hasTable('course_prerequisites')) {
            echo "  ⚠ course_prerequisites table doesn't exist. Skipping...\n";
            return;
        }
        
        // Get actual columns in the table
        $columns = Schema::getColumnListing('course_prerequisites');
        echo "  Available columns: " . implode(', ', $columns) . "\n";
        
        // Map columns to what exists
        $prereqColumn = in_array('prerequisite_course_id', $columns) ? 'prerequisite_course_id' : 
                       (in_array('prerequisite_id', $columns) ? 'prerequisite_id' : null);
        $typeColumn = in_array('requirement_type', $columns) ? 'requirement_type' : 
                     (in_array('type', $columns) ? 'type' : null);
        
        if (!$prereqColumn || !$typeColumn) {
            echo "  ⚠ Required columns missing. Skipping prerequisites...\n";
            return;
        }
        
        // Get courses
        $courses = DB::table('courses')->get();
        
        if ($courses->count() < 6) {
            echo "  ⚠ Not enough courses to create meaningful prerequisites.\n";
            return;
        }
        
        // Define relationships
        $prerequisites = [
            ['course' => 'CS201', 'prerequisite' => 'CS101', 'type' => 'prerequisite'],
            ['course' => 'CS301', 'prerequisite' => 'CS201', 'type' => 'prerequisite'],
            ['course' => 'MATH201', 'prerequisite' => 'MATH101', 'type' => 'prerequisite'],
            ['course' => 'PHYS201', 'prerequisite' => 'PHYS101', 'type' => 'prerequisite'],
            ['course' => 'ENG201', 'prerequisite' => 'ENG101', 'type' => 'prerequisite'],
        ];
        
        $created = 0;
        foreach ($prerequisites as $prereq) {
            $course = $courses->where('code', $prereq['course'])->first();
            $prerequisiteCourse = $courses->where('code', $prereq['prerequisite'])->first();
            
            if ($course && $prerequisiteCourse) {
                // Check if already exists
                $exists = DB::table('course_prerequisites')
                    ->where('course_id', $course->id)
                    ->where($prereqColumn, $prerequisiteCourse->id)
                    ->exists();
                
                if (!$exists) {
                    $data = [
                        'course_id' => $course->id,
                        $prereqColumn => $prerequisiteCourse->id,
                        $typeColumn => $prereq['type'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    // Add optional columns ONLY if they exist
                    if (in_array('minimum_grade', $columns)) {
                        $data['minimum_grade'] = 'C';
                    } elseif (in_array('min_grade', $columns)) {
                        $data['min_grade'] = 'C';
                    }
                    
                    if (in_array('notes', $columns)) {
                        $data['notes'] = "Must complete {$prereq['prerequisite']} before taking {$prereq['course']}";
                    }
                    
                    // Add is_active if the column exists
                    if (in_array('is_active', $columns)) {
                        $data['is_active'] = true;
                    }
                    
                    try {
                        DB::table('course_prerequisites')->insert($data);
                        $created++;
                        echo "  ✓ Created: {$prereq['prerequisite']} → {$prereq['course']}\n";
                    } catch (\Exception $e) {
                        echo "  ⚠ Failed to create prerequisite: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
        
        if ($created > 0) {
            echo "  ✓ Created {$created} prerequisite relationships\n";
        }
    }
    
    private function updateStudentStatuses()
    {
        echo "\nUpdating student profiles...\n";
        
        if (!Schema::hasTable('students')) {
            echo "  ⚠ students table doesn't exist. Skipping...\n";
            return;
        }
        
        $columns = Schema::getColumnListing('students');
        $students = DB::table('students')->limit(20)->get();
        
        if ($students->isEmpty()) {
            echo "  ⚠ No students found.\n";
            return;
        }
        
        $updated = 0;
        foreach ($students as $index => $student) {
            $updates = [];
            
            // Only set fields that exist
            if (in_array('cumulative_gpa', $columns) && !$student->cumulative_gpa) {
                $gpa = round(2.5 + (rand(0, 15) / 10), 2); // 2.5 to 4.0
                $updates['cumulative_gpa'] = $gpa;
                
                if (in_array('academic_standing', $columns)) {
                    $updates['academic_standing'] = $gpa >= 2.0 ? 'good' : 'probation';
                }
            }
            
            if (in_array('is_athlete', $columns) && $index % 5 == 0) {
                $updates['is_athlete'] = true;
            }
            
            if (in_array('is_honors', $columns) && $index % 4 == 0) {
                $updates['is_honors'] = true;
            }
            
            if (in_array('expected_graduation_date', $columns) && !$student->expected_graduation_date) {
                $updates['expected_graduation_date'] = Carbon::now()->addMonths(rand(12, 36))->format('Y-m-d');
            }
            
            if (!empty($updates)) {
                $updates['updated_at'] = now();
                DB::table('students')->where('id', $student->id)->update($updates);
                $updated++;
            }
        }
        
        echo "  ✓ Updated {$updated} student profiles\n";
    }
    
    private function createRegistrationHolds()
    {
        echo "\nCreating sample registration holds...\n";
        
        if (!Schema::hasTable('registration_holds')) {
            echo "  ⚠ registration_holds table doesn't exist. Skipping...\n";
            return;
        }
        
        $columns = Schema::getColumnListing('registration_holds');
        $students = DB::table('students')->limit(3)->get();
        
        if ($students->isEmpty()) {
            echo "  ⚠ No students found.\n";
            return;
        }
        
        // Get first admin or any user for placed_by
        $adminUser = DB::table('users')
            ->where('user_type', 'admin')
            ->orWhere('email', 'admin@intellicampus.edu')
            ->first();
        
        if (!$adminUser) {
            $adminUser = DB::table('users')->first();
        }
        
        if (!$adminUser) {
            echo "  ⚠ No users found to place holds. Skipping...\n";
            return;
        }
        
        $holds = [
            [
                'type' => 'financial', 
                'reason' => 'Outstanding tuition balance', 
                'description' => 'Student has an outstanding balance of $1,500 for the current semester',
                'dept' => 'Bursar Office',
                'instructions' => 'Visit the Bursar Office to make payment arrangements or clear the balance.'
            ],
            [
                'type' => 'academic', 
                'reason' => 'Missing official transcripts', 
                'description' => 'Official transcripts from previous institution have not been received',
                'dept' => 'Registrar Office',
                'instructions' => 'Submit official transcripts to the Registrar Office.'
            ],
            [
                'type' => 'immunization', 
                'reason' => 'Incomplete immunization records', 
                'description' => 'COVID-19 vaccination documentation required for campus access',
                'dept' => 'Health Services',
                'instructions' => 'Submit vaccination records to Health Services or schedule an appointment.'
            ]
        ];
        
        $created = 0;
        foreach ($students as $index => $student) {
            if ($index >= count($holds)) break;
            
            // Check if hold already exists
            $exists = DB::table('registration_holds')
                ->where('student_id', $student->id)
                ->where('hold_type', $holds[$index]['type'])
                ->where('is_active', true)
                ->exists();
            
            if (!$exists) {
                $data = [
                    'student_id' => $student->id,
                    'hold_type' => $holds[$index]['type'],
                    'reason' => $holds[$index]['reason'],
                    'description' => $holds[$index]['description'],
                    'placed_by' => $adminUser->id,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                // Add placed_date if column exists
                if (in_array('placed_date', $columns)) {
                    $data['placed_date'] = now();
                }
                
                // Add resolved fields for inactive holds (for testing variety)
                if ($index == 2) { // Make the third hold resolved for variety
                    $data['is_active'] = false;
                    if (in_array('resolved_date', $columns)) {
                        $data['resolved_date'] = now();
                    }
                    if (in_array('resolved_by', $columns)) {
                        $data['resolved_by'] = $adminUser->id;
                    }
                    if (in_array('cleared_at', $columns)) {
                        $data['cleared_at'] = now();
                    }
                }
                
                // Add department if column exists
                if (in_array('placed_by_department', $columns)) {
                    $data['placed_by_department'] = $holds[$index]['dept'];
                }
                
                // Add resolution instructions if column exists
                if (in_array('resolution_instructions', $columns)) {
                    $data['resolution_instructions'] = $holds[$index]['instructions'];
                }
                
                try {
                    DB::table('registration_holds')->insert($data);
                    $created++;
                    $status = $data['is_active'] ? 'active' : 'resolved';
                    echo "  ✓ Created {$status} {$holds[$index]['type']} hold for student #{$student->id}\n";
                } catch (\Exception $e) {
                    $errorMsg = $e->getMessage();
                    if (strpos($errorMsg, 'null value in column') !== false) {
                        preg_match('/column "([^"]+)"/', $errorMsg, $matches);
                        $missingColumn = $matches[1] ?? 'unknown';
                        echo "  ⚠ Failed: Missing required value for column '{$missingColumn}'\n";
                        
                        // Debug: Show what columns are NOT NULL
                        if ($created == 0) { // Only show once
                            $required = DB::select("
                                SELECT column_name 
                                FROM information_schema.columns 
                                WHERE table_name = 'registration_holds' 
                                AND is_nullable = 'NO'
                                AND column_default IS NULL
                                AND column_name NOT IN ('id', 'created_at', 'updated_at')
                            ");
                            echo "     Required columns: ";
                            foreach ($required as $col) {
                                echo $col->column_name . " ";
                            }
                            echo "\n";
                        }
                    } else {
                        echo "  ⚠ Failed: " . substr($errorMsg, 0, 80) . "\n";
                    }
                }
            } else {
                echo "  - {$holds[$index]['type']} hold already exists for student #{$student->id}\n";
            }
        }
        
        if ($created > 0) {
            echo "  ✓ Created {$created} registration holds\n";
        }
    }
}