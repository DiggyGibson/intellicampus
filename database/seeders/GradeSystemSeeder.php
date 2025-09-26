<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class GradeSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Seeding Grade System data...');
        
        // 1. Seed Grade Scales
        $this->seedGradeScales();
        
        // 2. Seed Grade Deadlines for Current Term
        $this->seedGradeDeadlines();
        
        // 3. Seed Sample Grade Components for Existing Sections
        $this->seedGradeComponents();
        
        // 4. Seed Sample Grades (optional - for testing)
        if ($this->command->confirm('Do you want to seed sample grades for testing?', false)) {
            $this->seedSampleGrades();
        }
        
        $this->command->info('Grade System seeding completed!');
    }
    
    /**
     * Seed grade scales
     */
    private function seedGradeScales()
    {
        $this->command->info('Seeding grade scales...');
        
        // Check if table exists
        if (!Schema::hasTable('grade_scales')) {
            $this->command->warn('grade_scales table does not exist. Creating it now...');
            
            // Create the table
            Schema::create('grade_scales', function ($table) {
                $table->id();
                $table->string('name', 100);
                $table->json('scale_values');
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
        
        // Standard Letter Grade Scale
        DB::table('grade_scales')->updateOrInsert(
            ['name' => 'Standard Letter Grade Scale'],
            [
                'scale_values' => json_encode([
                    'A'  => ['min' => 93, 'max' => 100, 'points' => 4.0],
                    'A-' => ['min' => 90, 'max' => 92.99, 'points' => 3.7],
                    'B+' => ['min' => 87, 'max' => 89.99, 'points' => 3.3],
                    'B'  => ['min' => 83, 'max' => 86.99, 'points' => 3.0],
                    'B-' => ['min' => 80, 'max' => 82.99, 'points' => 2.7],
                    'C+' => ['min' => 77, 'max' => 79.99, 'points' => 2.3],
                    'C'  => ['min' => 73, 'max' => 76.99, 'points' => 2.0],
                    'C-' => ['min' => 70, 'max' => 72.99, 'points' => 1.7],
                    'D+' => ['min' => 67, 'max' => 69.99, 'points' => 1.3],
                    'D'  => ['min' => 63, 'max' => 66.99, 'points' => 1.0],
                    'F'  => ['min' => 0, 'max' => 62.99, 'points' => 0.0],
                    'W'  => ['points' => 0.0, 'special' => true, 'description' => 'Withdrawal'],
                    'I'  => ['points' => 0.0, 'special' => true, 'description' => 'Incomplete'],
                    'P'  => ['points' => 0.0, 'special' => true, 'description' => 'Pass'],
                    'NP' => ['points' => 0.0, 'special' => true, 'description' => 'No Pass'],
                    'AU' => ['points' => 0.0, 'special' => true, 'description' => 'Audit'],
                    'IP' => ['points' => 0.0, 'special' => true, 'description' => 'In Progress']
                ]),
                'is_active' => true,
                'description' => 'Standard grading scale used for all undergraduate courses',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        
        // Pass/Fail Scale
        DB::table('grade_scales')->updateOrInsert(
            ['name' => 'Pass/Fail Scale'],
            [
                'scale_values' => json_encode([
                    'P'  => ['min' => 70, 'max' => 100, 'points' => 0.0],
                    'NP' => ['min' => 0, 'max' => 69.99, 'points' => 0.0]
                ]),
                'is_active' => true,
                'description' => 'Pass/Fail grading for specific courses',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        
        // Graduate Scale (stricter requirements)
        DB::table('grade_scales')->updateOrInsert(
            ['name' => 'Graduate Scale'],
            [
                'scale_values' => json_encode([
                    'A'  => ['min' => 95, 'max' => 100, 'points' => 4.0],
                    'A-' => ['min' => 92, 'max' => 94.99, 'points' => 3.7],
                    'B+' => ['min' => 89, 'max' => 91.99, 'points' => 3.3],
                    'B'  => ['min' => 85, 'max' => 88.99, 'points' => 3.0],
                    'B-' => ['min' => 82, 'max' => 84.99, 'points' => 2.7],
                    'C+' => ['min' => 79, 'max' => 81.99, 'points' => 2.3],
                    'C'  => ['min' => 75, 'max' => 78.99, 'points' => 2.0],
                    'F'  => ['min' => 0, 'max' => 74.99, 'points' => 0.0]
                ]),
                'is_active' => true,
                'description' => 'Grading scale for graduate programs',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        
        $this->command->info('Grade scales seeded successfully.');
    }
    
    /**
     * Seed grade deadlines - UPDATED WITH ALL 4 DEADLINE TYPES
     */
    private function seedGradeDeadlines()
    {
        $this->command->info('Seeding grade deadlines...');
        
        // Check if table exists
        if (!Schema::hasTable('grade_deadlines')) {
            $this->command->warn('grade_deadlines table does not exist. Creating it now...');
            
            Schema::create('grade_deadlines', function ($table) {
                $table->id();
                $table->foreignId('term_id')->constrained('academic_terms');
                $table->enum('deadline_type', ['midterm', 'final', 'incomplete', 'grade_change']); // Added grade_change
                $table->date('deadline_date');
                $table->time('deadline_time');
                $table->text('description')->nullable();
                $table->boolean('send_reminders')->default(true);
                $table->integer('reminder_days_before')->default(3);
                $table->timestamps();
                $table->unique(['term_id', 'deadline_type']);
            });
        }
        
        // Get current academic term
        $currentTerm = DB::table('academic_terms')
            ->where('is_current', true)
            ->first();
        
        if (!$currentTerm) {
            $this->command->warn('No current academic term found. Creating Fall 2025...');
            
            $termId = DB::table('academic_terms')->insertGetId([
                'name' => 'Fall 2025',
                'code' => 'F25',
                'academic_year' => '2025-2026',
                'start_date' => '2025-08-26',
                'end_date' => '2025-12-20',
                'registration_start' => '2025-08-01',
                'registration_end' => '2025-09-02',
                'add_drop_end' => '2025-09-09',
                'withdrawal_end' => '2025-11-15',
                'is_current' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $currentTerm = DB::table('academic_terms')->find($termId);
        }
        
        // Create ALL FOUR grade deadlines
        $deadlines = [
            [
                'term_id' => $currentTerm->id,
                'deadline_type' => 'midterm',
                'deadline_date' => Carbon::parse($currentTerm->start_date)->addWeeks(8)->toDateString(),
                'deadline_time' => '23:59:59',
                'description' => 'Midterm grade submission deadline',
                'send_reminders' => true,
                'reminder_days_before' => 3,
            ],
            [
                'term_id' => $currentTerm->id,
                'deadline_type' => 'final',
                'deadline_date' => Carbon::parse($currentTerm->end_date)->addDays(3)->toDateString(),
                'deadline_time' => '23:59:59',
                'description' => 'Final grade submission deadline',
                'send_reminders' => true,
                'reminder_days_before' => 5,
            ],
            [
                'term_id' => $currentTerm->id,
                'deadline_type' => 'grade_change', // NEW DEADLINE TYPE
                'deadline_date' => Carbon::parse($currentTerm->end_date)->addDays(30)->toDateString(),
                'deadline_time' => '23:59:59',
                'description' => 'Grade change request deadline',
                'send_reminders' => true,
                'reminder_days_before' => 7,
            ],
            [
                'term_id' => $currentTerm->id,
                'deadline_type' => 'incomplete',
                'deadline_date' => Carbon::parse($currentTerm->end_date)->addDays(60)->toDateString(),
                'deadline_time' => '23:59:59',
                'description' => 'Incomplete grade resolution deadline',
                'send_reminders' => true,
                'reminder_days_before' => 7,
            ]
        ];
        
        foreach ($deadlines as $deadline) {
            try {
                DB::table('grade_deadlines')->updateOrInsert(
                    [
                        'term_id' => $deadline['term_id'],
                        'deadline_type' => $deadline['deadline_type']
                    ],
                    array_merge($deadline, [
                        'created_at' => now(),
                        'updated_at' => now()
                    ])
                );
            } catch (\Exception $e) {
                $this->command->warn("Could not insert {$deadline['deadline_type']} deadline: " . $e->getMessage());
            }
        }
        
        $this->command->info('Grade deadlines seeded successfully.');
    }
    
    // ... Rest of the methods remain the same ...
    
    /**
     * Seed grade components for existing sections
     */
    private function seedGradeComponents()
    {
        $this->command->info('Seeding grade components...');
        
        // Get a few course sections to add components to
        $sections = DB::table('course_sections')
            ->join('academic_terms', 'course_sections.term_id', '=', 'academic_terms.id')
            ->where('academic_terms.is_current', true)
            ->select('course_sections.*')
            ->limit(5)
            ->get();
        
        if ($sections->isEmpty()) {
            $this->command->warn('No course sections found for current term. Skipping grade components.');
            return;
        }
        
        // Default component templates based on course type
        $templates = [
            'standard' => [
                ['name' => 'Assignments', 'type' => 'assignment', 'weight' => 20, 'max_points' => 100],
                ['name' => 'Quizzes', 'type' => 'quiz', 'weight' => 15, 'max_points' => 100],
                ['name' => 'Midterm Exam', 'type' => 'exam', 'weight' => 25, 'max_points' => 100],
                ['name' => 'Final Exam', 'type' => 'exam', 'weight' => 30, 'max_points' => 100],
                ['name' => 'Class Participation', 'type' => 'participation', 'weight' => 10, 'max_points' => 100]
            ],
            'lab' => [
                ['name' => 'Lab Reports', 'type' => 'assignment', 'weight' => 30, 'max_points' => 100],
                ['name' => 'Lab Quizzes', 'type' => 'quiz', 'weight' => 10, 'max_points' => 100],
                ['name' => 'Midterm Exam', 'type' => 'exam', 'weight' => 20, 'max_points' => 100],
                ['name' => 'Final Exam', 'type' => 'exam', 'weight' => 25, 'max_points' => 100],
                ['name' => 'Lab Performance', 'type' => 'lab', 'weight' => 15, 'max_points' => 100]
            ],
            'project' => [
                ['name' => 'Project Proposal', 'type' => 'project', 'weight' => 10, 'max_points' => 100],
                ['name' => 'Progress Reports', 'type' => 'assignment', 'weight' => 15, 'max_points' => 100],
                ['name' => 'Final Project', 'type' => 'project', 'weight' => 40, 'max_points' => 100],
                ['name' => 'Project Presentation', 'type' => 'presentation', 'weight' => 20, 'max_points' => 100],
                ['name' => 'Peer Review', 'type' => 'participation', 'weight' => 15, 'max_points' => 100]
            ]
        ];
        
        foreach ($sections as $index => $section) {
            // Check if components already exist for this section
            $existingComponents = DB::table('grade_components')
                ->where('section_id', $section->id)
                ->count();
            
            if ($existingComponents > 0) {
                $this->command->info("Section ID {$section->id} already has components. Skipping.");
                continue;
            }
            
            // Choose template based on section or rotate through templates
            $templateKey = array_keys($templates)[$index % count($templates)];
            $template = $templates[$templateKey];
            
            foreach ($template as $componentData) {
                DB::table('grade_components')->insert([
                    'section_id' => $section->id,
                    'name' => $componentData['name'],
                    'type' => $componentData['type'],
                    'weight' => $componentData['weight'],
                    'max_points' => $componentData['max_points'],
                    'due_date' => $this->calculateDueDate($section, $componentData['name']),
                    'is_visible' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            $this->command->info("Added grade components for section ID: {$section->id}");
        }
        
        $this->command->info('Grade components seeded successfully.');
    }
    
    /**
     * Seed sample grades for testing
     */
    private function seedSampleGrades()
    {
        $this->command->info('Seeding sample grades...');
        
        // Check what column name is used in enrollments table
        $enrollmentColumns = Schema::getColumnListing('enrollments');
        $statusColumn = in_array('enrollment_status', $enrollmentColumns) ? 'enrollment_status' : 'status';
        
        // Get enrollments for current term
        $query = DB::table('enrollments as e')
            ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
            ->join('academic_terms as at', 'cs.term_id', '=', 'at.id')
            ->where('at.is_current', true)
            ->select('e.*', 'cs.instructor_id')
            ->limit(20);
        
        // Add status filter if column exists
        if (in_array($statusColumn, $enrollmentColumns)) {
            $query->where('e.' . $statusColumn, 'enrolled');
        }
        
        $enrollments = $query->get();
        
        if ($enrollments->isEmpty()) {
            $this->command->warn('No enrollments found for current term. Creating test enrollments...');
            $this->createTestEnrollments();
            
            // Try again
            $enrollments = DB::table('enrollments as e')
                ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
                ->join('academic_terms as at', 'cs.term_id', '=', 'at.id')
                ->where('at.is_current', true)
                ->select('e.*', 'cs.instructor_id')
                ->limit(20)
                ->get();
        }
        
        $gradesInserted = 0;
        
        foreach ($enrollments as $enrollment) {
            // Get components for this section
            $components = DB::table('grade_components')
                ->where('section_id', $enrollment->section_id)
                ->get();
            
            if ($components->isEmpty()) {
                continue;
            }
            
            foreach ($components as $component) {
                // Check if grade already exists
                $existingGrade = DB::table('grades')
                    ->where('enrollment_id', $enrollment->id)
                    ->where('component_id', $component->id)
                    ->first();
                
                if ($existingGrade) {
                    continue;
                }
                
                // Generate random grade (60-100% range for variety)
                $percentage = rand(60, 100);
                $pointsEarned = ($percentage / 100) * $component->max_points;
                
                // Get grader (instructor or default user)
                $graderId = $enrollment->instructor_id ?? 
                           DB::table('users')->where('email', 'faculty.test@intellicampus.edu')->value('id') ?? 
                           DB::table('users')->first()->id ?? 
                           1;
                
                // Insert grade
                try {
                    DB::table('grades')->insert([
                        'enrollment_id' => $enrollment->id,
                        'component_id' => $component->id,
                        'points_earned' => round($pointsEarned, 2),
                        'max_points' => $component->max_points,
                        'percentage' => $percentage,
                        'letter_grade' => $this->percentageToLetter($percentage),
                        'graded_by' => $graderId,
                        'submitted_at' => now()->subDays(rand(1, 30)),
                        'is_final' => false,
                        'grade_status' => 'draft',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    $gradesInserted++;
                } catch (\Exception $e) {
                    $this->command->warn("Could not insert grade: " . $e->getMessage());
                }
            }
        }
        
        $this->command->info("Sample grades seeded successfully! Inserted {$gradesInserted} grades.");
    }
    
    /**
     * Create test enrollments if none exist
     */
    private function createTestEnrollments()
    {
        $currentTerm = DB::table('academic_terms')->where('is_current', true)->first();
        if (!$currentTerm) {
            $this->command->error('No current term found. Cannot create test enrollments.');
            return;
        }
        
        // Get some students and sections
        $students = DB::table('students')->limit(5)->get();
        $sections = DB::table('course_sections')
            ->where('term_id', $currentTerm->id)
            ->limit(3)
            ->get();
        
        if ($students->isEmpty() || $sections->isEmpty()) {
            $this->command->warn('Not enough students or sections to create test enrollments.');
            return;
        }
        
        // Check what column name to use
        $enrollmentColumns = Schema::getColumnListing('enrollments');
        $statusColumn = in_array('enrollment_status', $enrollmentColumns) ? 'enrollment_status' : 'status';
        
        foreach ($students as $student) {
            foreach ($sections as $section) {
                try {
                    $data = [
                        'student_id' => $student->id,
                        'section_id' => $section->id,
                        'term_id' => $currentTerm->id,
                        'enrolled_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    // Add status column if it exists
                    if (in_array($statusColumn, $enrollmentColumns)) {
                        $data[$statusColumn] = 'enrolled';
                    }
                    
                    DB::table('enrollments')->insert($data);
                } catch (\Exception $e) {
                    // Skip duplicates
                }
            }
        }
        
        $this->command->info('Test enrollments created.');
    }
    
    /**
     * Calculate due date based on component name
     */
    private function calculateDueDate($section, $componentName)
    {
        $term = DB::table('academic_terms')->find($section->term_id);
        if (!$term) {
            return null;
        }
        
        $startDate = Carbon::parse($term->start_date);
        $endDate = Carbon::parse($term->end_date);
        
        // Set due dates based on component name
        if (stripos($componentName, 'midterm') !== false) {
            return $startDate->addWeeks(8);
        } elseif (stripos($componentName, 'final') !== false) {
            return $endDate->subDays(7);
        } elseif (stripos($componentName, 'quiz') !== false) {
            return $startDate->addWeeks(rand(2, 6));
        } elseif (stripos($componentName, 'assignment') !== false) {
            return $startDate->addWeeks(rand(3, 10));
        } elseif (stripos($componentName, 'project') !== false) {
            return $endDate->subDays(14);
        } else {
            return null;
        }
    }
    
    /**
     * Convert percentage to letter grade
     */
    private function percentageToLetter($percentage)
    {
        if ($percentage >= 93) return 'A';
        if ($percentage >= 90) return 'A-';
        if ($percentage >= 87) return 'B+';
        if ($percentage >= 83) return 'B';
        if ($percentage >= 80) return 'B-';
        if ($percentage >= 77) return 'C+';
        if ($percentage >= 73) return 'C';
        if ($percentage >= 70) return 'C-';
        if ($percentage >= 67) return 'D+';
        if ($percentage >= 63) return 'D';
        return 'F';
    }
    
    /**
     * Convert letter grade to grade points
     */
    private function letterToPoints($letter)
    {
        $points = [
            'A'  => 4.0, 'A-' => 3.7,
            'B+' => 3.3, 'B'  => 3.0, 'B-' => 2.7,
            'C+' => 2.3, 'C'  => 2.0, 'C-' => 1.7,
            'D+' => 1.3, 'D'  => 1.0,
            'F'  => 0.0
        ];
        
        return $points[$letter] ?? 0.0;
    }
}