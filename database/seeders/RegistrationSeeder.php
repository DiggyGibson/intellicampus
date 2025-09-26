<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get current term
        $currentTerm = DB::table('academic_terms')
            ->where('is_current', true)
            ->first();
            
        if (!$currentTerm) {
            $this->command->info('No current term found. Skipping registration seeder.');
            return;
        }

        // Create Registration Periods
        $this->command->info('Creating registration periods...');
        
        // Check if registration periods already exist for this term
        $existingPeriods = DB::table('registration_periods')
            ->where('term_id', $currentTerm->id)
            ->count();
            
        if ($existingPeriods == 0) {
            DB::table('registration_periods')->insert([
                [
                    'term_id' => $currentTerm->id,
                    'name' => 'Senior Registration',
                    'description' => 'Early registration for seniors (90+ credits)',
                    'start_date' => Carbon::now()->subDays(14),
                    'end_date' => Carbon::now()->subDays(10),
                    'student_level' => 'senior',
                    'min_credits' => 90,
                    'max_credits' => 18,
                    'is_active' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'term_id' => $currentTerm->id,
                    'name' => 'Junior Registration',
                    'description' => 'Early registration for juniors (60-89 credits)',
                    'start_date' => Carbon::now()->subDays(10),
                    'end_date' => Carbon::now()->subDays(7),
                    'student_level' => 'junior',
                    'min_credits' => 60,
                    'max_credits' => 18,
                    'is_active' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'term_id' => $currentTerm->id,
                    'name' => 'General Registration',
                    'description' => 'Open registration for all students',
                    'start_date' => Carbon::now()->subDays(7),
                    'end_date' => Carbon::now()->addDays(7),
                    'student_level' => 'all',
                    'min_credits' => 0,
                    'max_credits' => 21,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'term_id' => $currentTerm->id,
                    'name' => 'Late Registration',
                    'description' => 'Late registration period with additional fees',
                    'start_date' => Carbon::now()->addDays(8),
                    'end_date' => Carbon::now()->addDays(14),
                    'student_level' => 'all',
                    'min_credits' => 0,
                    'max_credits' => 18,
                    'is_active' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // Create course prerequisites
        $this->command->info('Setting up course prerequisites...');
        
        $courses = DB::table('courses')->get();
        if ($courses->isNotEmpty()) {
            // CS201 requires CS101
            $cs101 = $courses->where('course_code', 'CS101')->first();
            $cs201 = $courses->where('course_code', 'CS201')->first();
            
            if ($cs101 && $cs201) {
                $exists = DB::table('course_prerequisites')
                    ->where('course_id', $cs201->id)
                    ->where('prerequisite_course_id', $cs101->id)
                    ->exists();
                    
                if (!$exists) {
                    DB::table('course_prerequisites')->insert([
                        'course_id' => $cs201->id,
                        'prerequisite_course_id' => $cs101->id,
                        'minimum_grade' => 2.0,
                        'requirement_type' => 'required',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Advanced courses require introductory courses
            $intro = $courses->where('course_code', 'MATH101')->first();
            $advanced = $courses->where('course_code', 'MATH201')->first();
            
            if ($intro && $advanced) {
                $exists = DB::table('course_prerequisites')
                    ->where('course_id', $advanced->id)
                    ->where('prerequisite_course_id', $intro->id)
                    ->exists();
                    
                if (!$exists) {
                    DB::table('course_prerequisites')->insert([
                        'course_id' => $advanced->id,
                        'prerequisite_course_id' => $intro->id,
                        'minimum_grade' => 2.0,
                        'requirement_type' => 'required',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Create sample enrollments for some students
        $this->command->info('Creating sample enrollments...');
        
        $students = DB::table('students')
            ->where('enrollment_status', 'active')
            ->limit(30)
            ->get();
            
        $sections = DB::table('course_sections')
            ->where('term_id', $currentTerm->id)
            ->where('status', 'open')
            ->get();

        if ($students->isNotEmpty() && $sections->isNotEmpty()) {
            foreach ($students as $index => $student) {
                // Each student enrolls in 3-5 courses
                $numCourses = rand(3, 5);
                $selectedSections = $sections->random(min($numCourses, $sections->count()));
                
                foreach ($selectedSections as $section) {
                    // Check if not already enrolled
                    $exists = DB::table('enrollments')
                        ->where('student_id', $student->id)
                        ->where('section_id', $section->id)
                        ->exists();
                    
                    if (!$exists) {
                        // Get course credits
                        $course = DB::table('courses')->find($section->course_id);
                        
                        // FIXED: Added term_id field
                        DB::table('enrollments')->insert([
                            'student_id' => $student->id,
                            'section_id' => $section->id,
                            'term_id' => $currentTerm->id,  // FIXED: Added this line
                            'enrollment_status' => 'enrolled',
                            'enrollment_date' => Carbon::now()->subDays(rand(1, 7)),
                            'registration_date' => Carbon::now()->subDays(rand(1, 7)),
                            'grade_option' => 'graded',
                            'credits_attempted' => $course->credits ?? 3,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        // Update section enrollment count
                        DB::table('course_sections')
                            ->where('id', $section->id)
                            ->increment('current_enrollment');
                        
                        // Add to registration log
                        DB::table('registration_logs')->insert([
                            'student_id' => $student->id,
                            'section_id' => $section->id,
                            'action' => 'enrolled',
                            'details' => 'Seeded enrollment',
                            'ip_address' => '127.0.0.1',
                            'user_agent' => 'Database Seeder',
                            'created_at' => Carbon::now()->subDays(rand(1, 7)),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // Add some students to waitlists
        $this->command->info('Creating sample waitlists...');
        
        $fullSections = DB::table('course_sections')
            ->whereRaw('current_enrollment >= enrollment_capacity')
            ->limit(5)
            ->get();
            
        if ($fullSections->isNotEmpty() && $students->count() > 20) {
            $waitlistStudents = $students->skip(20)->take(10);
            
            foreach ($fullSections as $section) {
                $position = 1;
                foreach ($waitlistStudents->random(min(3, $waitlistStudents->count())) as $student) {
                    $exists = DB::table('waitlists')
                        ->where('student_id', $student->id)
                        ->where('section_id', $section->id)
                        ->exists();
                    
                    if (!$exists) {
                        DB::table('waitlists')->insert([
                            'section_id' => $section->id,
                            'student_id' => $student->id,
                            'position' => $position++,
                            'added_date' => now(),
                            'expiry_date' => Carbon::now()->addDays(7),
                            'status' => 'waiting',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        // Add sample registration holds
        $this->command->info('Creating sample registration holds...');
        
        // Get a user ID to use as placed_by
        $adminUser = DB::table('users')->where('user_type', 'admin')->first();
        $placedBy = $adminUser ? $adminUser->id : 1;
        
        if ($students->count() > 25) {
            // Check if holds already exist
            $student1 = $students->get(25);
            $student2 = $students->get(26);
            
            if ($student1) {
                $existingHold = DB::table('registration_holds')
                    ->where('student_id', $student1->id)
                    ->where('hold_type', 'financial')
                    ->exists();
                    
                if (!$existingHold) {
                    DB::table('registration_holds')->insert([
                        'student_id' => $student1->id,
                        'hold_type' => 'financial',
                        'reason' => 'Outstanding Balance',
                        'description' => 'Student has an outstanding balance of $1,500 from previous semester',
                        'placed_date' => Carbon::now()->subDays(30),
                        'resolved_date' => null,
                        'placed_by' => $placedBy,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            if ($student2) {
                $existingHold = DB::table('registration_holds')
                    ->where('student_id', $student2->id)
                    ->where('hold_type', 'academic')
                    ->exists();
                    
                if (!$existingHold) {
                    DB::table('registration_holds')->insert([
                        'student_id' => $student2->id,
                        'hold_type' => 'academic',
                        'reason' => 'Academic Probation',
                        'description' => 'Student must meet with academic advisor before registration',
                        'placed_date' => Carbon::now()->subDays(14),
                        'resolved_date' => null,
                        'placed_by' => $placedBy,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Registration seeder completed successfully!');
        $this->command->info('- Created registration periods');
        $this->command->info('- Added course prerequisites');
        $this->command->info('- Enrolled students in courses');
        $this->command->info('- Created sample waitlists');
        $this->command->info('- Added sample registration holds');
    }
}