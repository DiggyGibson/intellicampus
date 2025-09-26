<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AcademicProgramsSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Starting Academic Programs Seeder...');
        
        // 1. Create Program Types
        $this->seedProgramTypes();
        
        // 2. Create Degrees
        $this->seedDegrees();
        
        // 3. Create Programs
        $this->seedPrograms();
        
        // 4. Create Entrance Exams (optional)
        $this->seedEntranceExams();
        
        $this->command->info('✅ Academic Programs seeded successfully!');
    }
    
    private function seedProgramTypes()
    {
        $this->command->info('Creating program types...');
        
        $types = [
            ['name' => 'Certificate', 'code' => 'CERT', 'level' => 0],
            ['name' => 'Diploma', 'code' => 'DIP', 'level' => 1],
            ['name' => 'Undergraduate', 'code' => 'UG', 'level' => 2],
            ['name' => 'Graduate', 'code' => 'GR', 'level' => 3],
            ['name' => 'Doctoral', 'code' => 'PHD', 'level' => 4],
            ['name' => 'Professional', 'code' => 'PROF', 'level' => 3],
        ];
        
        foreach ($types as $type) {
            DB::table('program_types')->updateOrInsert(
                ['code' => $type['code']],
                array_merge($type, [
                    'is_active' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ])
            );
        }
        
        $this->command->info('✓ Program types created');
    }
    
    private function seedDegrees()
    {
        $this->command->info('Creating degrees...');
        
        $degrees = [
            // Undergraduate
            ['name' => 'Bachelor of Science', 'abbreviation' => 'BS', 'level' => 'undergraduate', 'order' => 1],
            ['name' => 'Bachelor of Arts', 'abbreviation' => 'BA', 'level' => 'undergraduate', 'order' => 2],
            ['name' => 'Bachelor of Business Administration', 'abbreviation' => 'BBA', 'level' => 'undergraduate', 'order' => 3],
            ['name' => 'Bachelor of Engineering', 'abbreviation' => 'BE', 'level' => 'undergraduate', 'order' => 4],
            ['name' => 'Bachelor of Technology', 'abbreviation' => 'BTech', 'level' => 'undergraduate', 'order' => 5],
            
            // Graduate
            ['name' => 'Master of Science', 'abbreviation' => 'MS', 'level' => 'graduate', 'order' => 10],
            ['name' => 'Master of Arts', 'abbreviation' => 'MA', 'level' => 'graduate', 'order' => 11],
            ['name' => 'Master of Business Administration', 'abbreviation' => 'MBA', 'level' => 'graduate', 'order' => 12],
            ['name' => 'Master of Engineering', 'abbreviation' => 'MEng', 'level' => 'graduate', 'order' => 13],
            ['name' => 'Master of Technology', 'abbreviation' => 'MTech', 'level' => 'graduate', 'order' => 14],
            
            // Doctoral
            ['name' => 'Doctor of Philosophy', 'abbreviation' => 'PhD', 'level' => 'doctoral', 'order' => 20],
            ['name' => 'Doctor of Science', 'abbreviation' => 'DSc', 'level' => 'doctoral', 'order' => 21],
            ['name' => 'Doctor of Business Administration', 'abbreviation' => 'DBA', 'level' => 'doctoral', 'order' => 22],
        ];
        
        foreach ($degrees as $degree) {
            DB::table('degrees')->updateOrInsert(
                ['abbreviation' => $degree['abbreviation']],
                array_merge($degree, [
                    'is_active' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ])
            );
        }
        
        $this->command->info('✓ Degrees created');
    }
    
    private function seedPrograms()
    {
        $this->command->info('Creating academic programs...');
        
        // Get required IDs
        $departments = DB::table('departments')->pluck('id', 'code')->toArray();
        if (empty($departments)) {
            // Get any department if codes don't exist
            $departments = DB::table('departments')->pluck('id')->toArray();
            if (empty($departments)) {
                $this->command->warn('No departments found! Creating a default department...');
                $deptId = DB::table('departments')->insertGetId([
                    'code' => 'GEN',
                    'name' => 'General Studies',
                    'type' => 'academic',
                    'is_active' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                $departments = ['GEN' => $deptId];
            }
        }
        
        $programTypes = DB::table('program_types')->pluck('id', 'code')->toArray();
        $degrees = DB::table('degrees')->pluck('id', 'abbreviation')->toArray();
        
        // Use first available department if specific ones don't exist
        $csId = $departments['CS'] ?? $departments['COMP'] ?? reset($departments);
        $busId = $departments['BUS'] ?? $departments['BBA'] ?? reset($departments);
        $engId = $departments['ENG'] ?? $departments['EE'] ?? reset($departments);
        
        $programs = [
            // Computer Science Programs
            [
                'code' => 'BSCS',
                'name' => 'Bachelor of Science in Computer Science',
                'level' => 'undergraduate',  // REQUIRED FIELD
                'department_id' => $csId,
                'program_type_id' => $programTypes['UG'] ?? 1,
                'degree_id' => $degrees['BS'] ?? null,
                'duration_years' => 4,
                'credits_required' => 120,
                'total_credits' => 120,  // Also add this field
                'min_gpa' => 2.5,
                'description' => 'Comprehensive undergraduate program in computer science covering programming, algorithms, data structures, and software engineering.',
                'requirements' => json_encode([
                    'min_gpa' => 3.0,
                    'tests' => ['SAT', 'ACT'],
                    'documents' => ['High School Transcript', 'Two Recommendation Letters', 'Personal Statement'],
                    'prerequisites' => ['Algebra II', 'Pre-Calculus']
                ]),
                'learning_outcomes' => json_encode([
                    'Design and implement software solutions',
                    'Analyze computational problems',
                    'Work effectively in development teams',
                    'Apply theoretical concepts to practical problems'
                ]),
                'career_prospects' => json_encode([
                    'Software Developer',
                    'Systems Analyst',
                    'Data Scientist',
                    'IT Consultant'
                ]),
                'delivery_mode' => 'on-campus',
                'is_active' => true,
                'admission_open' => true,
                'max_enrollment' => 150,
                'current_enrollment' => 0,
                'application_fee' => 50.00
            ],
            
            // MBA Program
            [
                'code' => 'MBA',
                'name' => 'Master of Business Administration',
                'level' => 'graduate',  // REQUIRED FIELD
                'department_id' => $busId,
                'program_type_id' => $programTypes['GR'] ?? 2,
                'degree_id' => $degrees['MBA'] ?? null,
                'duration_years' => 2,
                'credits_required' => 60,
                'total_credits' => 60,
                'min_gpa' => 3.0,
                'description' => 'Professional graduate degree for business leaders and entrepreneurs.',
                'requirements' => json_encode([
                    'min_gpa' => 3.0,
                    'tests' => ['GMAT', 'GRE'],
                    'work_experience' => '2+ years preferred',
                    'documents' => ['Transcripts', 'Resume', 'Essays', 'Three Recommendation Letters']
                ]),
                'learning_outcomes' => json_encode([
                    'Strategic business planning',
                    'Financial analysis and management',
                    'Leadership and team management',
                    'Global business perspective'
                ]),
                'career_prospects' => json_encode([
                    'Business Manager',
                    'Consultant',
                    'Entrepreneur',
                    'Executive Leadership'
                ]),
                'delivery_mode' => 'hybrid',
                'is_active' => true,
                'admission_open' => true,
                'max_enrollment' => 80,
                'current_enrollment' => 0,
                'application_fee' => 75.00
            ],
            
            // MS Computer Science
            [
                'code' => 'MSCS',
                'name' => 'Master of Science in Computer Science',
                'level' => 'graduate',  // REQUIRED FIELD
                'department_id' => $csId,
                'program_type_id' => $programTypes['GR'] ?? 2,
                'degree_id' => $degrees['MS'] ?? null,
                'duration_years' => 2,
                'credits_required' => 36,
                'total_credits' => 36,
                'min_gpa' => 3.0,
                'description' => 'Advanced graduate program in computer science with research focus.',
                'requirements' => json_encode([
                    'min_gpa' => 3.5,
                    'tests' => ['GRE'],
                    'documents' => ['Transcripts', 'Statement of Purpose', 'Three Recommendation Letters', 'Resume']
                ]),
                'learning_outcomes' => json_encode([
                    'Advanced algorithm design',
                    'Research methodology',
                    'Systems architecture',
                    'Machine learning applications'
                ]),
                'career_prospects' => json_encode([
                    'Research Scientist',
                    'Senior Software Engineer',
                    'Technical Lead',
                    'PhD Studies'
                ]),
                'delivery_mode' => 'on-campus',
                'is_active' => true,
                'admission_open' => true,
                'max_enrollment' => 40,
                'current_enrollment' => 0,
                'application_fee' => 60.00
            ],
            
            // PhD Computer Science
            [
                'code' => 'PHDCS',
                'name' => 'Doctor of Philosophy in Computer Science',
                'level' => 'doctorate',  // REQUIRED FIELD
                'department_id' => $csId,
                'program_type_id' => $programTypes['PHD'] ?? 3,
                'degree_id' => $degrees['PhD'] ?? null,
                'duration_years' => 5,
                'credits_required' => 72,
                'total_credits' => 72,
                'min_gpa' => 3.5,
                'description' => 'Research doctoral program in advanced computer science.',
                'requirements' => json_encode([
                    'min_gpa' => 3.7,
                    'tests' => ['GRE'],
                    'documents' => ['Transcripts', 'Research Proposal', 'Three Recommendation Letters', 'Publications (if any)']
                ]),
                'learning_outcomes' => json_encode([
                    'Independent research',
                    'Academic publication',
                    'Teaching experience',
                    'Domain expertise'
                ]),
                'career_prospects' => json_encode([
                    'University Professor',
                    'Research Director',
                    'Chief Technology Officer',
                    'Principal Scientist'
                ]),
                'delivery_mode' => 'on-campus',
                'is_active' => true,
                'admission_open' => true,
                'max_enrollment' => 15,
                'current_enrollment' => 0,
                'application_fee' => 100.00
            ],
            
            // BBA Program
            [
                'code' => 'BBA',
                'name' => 'Bachelor of Business Administration',
                'level' => 'undergraduate',  // REQUIRED FIELD
                'department_id' => $busId,
                'program_type_id' => $programTypes['UG'] ?? 1,
                'degree_id' => $degrees['BBA'] ?? null,
                'duration_years' => 4,
                'credits_required' => 120,
                'total_credits' => 120,
                'min_gpa' => 2.5,
                'description' => 'Comprehensive business education covering all aspects of business management.',
                'requirements' => json_encode([
                    'min_gpa' => 2.8,
                    'tests' => ['SAT', 'ACT'],
                    'documents' => ['High School Transcript', 'Two Recommendation Letters', 'Essay']
                ]),
                'learning_outcomes' => json_encode([
                    'Business fundamentals',
                    'Financial literacy',
                    'Marketing strategies',
                    'Management principles'
                ]),
                'career_prospects' => json_encode([
                    'Business Analyst',
                    'Marketing Manager',
                    'Financial Advisor',
                    'Operations Manager'
                ]),
                'delivery_mode' => 'on-campus',
                'is_active' => true,
                'admission_open' => true,
                'max_enrollment' => 200,
                'current_enrollment' => 0,
                'application_fee' => 50.00
            ],
            
            // Engineering Program
            [
                'code' => 'BSEE',
                'name' => 'Bachelor of Science in Electrical Engineering',
                'level' => 'undergraduate',  // REQUIRED FIELD
                'department_id' => $engId,
                'program_type_id' => $programTypes['UG'] ?? 1,
                'degree_id' => $degrees['BS'] ?? null,
                'duration_years' => 4,
                'credits_required' => 128,
                'total_credits' => 128,
                'min_gpa' => 3.0,
                'description' => 'Engineering program focusing on electrical systems and electronics.',
                'requirements' => json_encode([
                    'min_gpa' => 3.2,
                    'tests' => ['SAT Math', 'ACT'],
                    'documents' => ['High School Transcript', 'Two Recommendation Letters', 'Statement of Interest']
                ]),
                'learning_outcomes' => json_encode([
                    'Circuit design and analysis',
                    'Power systems engineering',
                    'Digital signal processing',
                    'Control systems'
                ]),
                'career_prospects' => json_encode([
                    'Electrical Engineer',
                    'Systems Engineer',
                    'Power Engineer',
                    'Electronics Designer'
                ]),
                'delivery_mode' => 'on-campus',
                'is_active' => true,
                'admission_open' => true,
                'max_enrollment' => 100,
                'current_enrollment' => 0,
                'application_fee' => 50.00
            ]
        ];
        
        foreach ($programs as $program) {
            try {
                DB::table('programs')->updateOrInsert(
                    ['code' => $program['code']],
                    array_merge($program, [
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ])
                );
                $this->command->info("✓ Created program: {$program['code']} - {$program['name']}");
            } catch (\Exception $e) {
                $this->command->error("Failed to create program {$program['code']}: " . $e->getMessage());
            }
        }
        
        $programCount = DB::table('programs')->count();
        $this->command->info("✓ Total programs in database: {$programCount}");
    }
    
    private function seedEntranceExams()
    {
        if (!Schema::hasTable('entrance_exams')) {
            $this->command->info('⚠ Entrance exams table not found, skipping...');
            return;
        }
        
        $this->command->info('Creating entrance exam schedules...');
        
        $programs = DB::table('programs')->pluck('id', 'code')->toArray();
        
        $exams = [
            [
                'program_id' => $programs['MSCS'] ?? null,
                'exam_name' => 'CS Graduate Entrance Exam',
                'exam_date' => Carbon::now()->addMonths(2),
                'registration_deadline' => Carbon::now()->addMonths(1),
                'exam_fee' => 150.00,
                'duration_minutes' => 180,
                'total_marks' => 200,
                'passing_marks' => 120,
                'is_online' => true,
                'is_active' => true
            ],
            [
                'program_id' => $programs['MBA'] ?? null,
                'exam_name' => 'MBA Admission Test',
                'exam_date' => Carbon::now()->addMonths(2)->addDays(7),
                'registration_deadline' => Carbon::now()->addMonths(1)->addDays(7),
                'exam_fee' => 200.00,
                'duration_minutes' => 240,
                'total_marks' => 300,
                'passing_marks' => 180,
                'is_online' => true,
                'is_active' => true
            ],
            [
                'program_id' => $programs['PHDCS'] ?? null,
                'exam_name' => 'PhD Comprehensive Exam',
                'exam_date' => Carbon::now()->addMonths(3),
                'registration_deadline' => Carbon::now()->addMonths(2),
                'exam_fee' => 250.00,
                'duration_minutes' => 360,
                'total_marks' => 400,
                'passing_marks' => 280,
                'is_online' => false,
                'is_active' => true
            ]
        ];
        
        foreach ($exams as $exam) {
            if ($exam['program_id']) {
                try {
                    DB::table('entrance_exams')->updateOrInsert(
                        ['program_id' => $exam['program_id'], 'exam_name' => $exam['exam_name']],
                        array_merge($exam, [
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ])
                    );
                    $this->command->info("✓ Created exam: {$exam['exam_name']}");
                } catch (\Exception $e) {
                    $this->command->warn("Could not create exam {$exam['exam_name']}: " . $e->getMessage());
                }
            }
        }
        
        $examCount = DB::table('entrance_exams')->count();
        $this->command->info("✓ Total entrance exams in database: {$examCount}");
    }
}