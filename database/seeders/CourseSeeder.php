<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicProgram;
use App\Models\Course;
use App\Models\AcademicTerm;
use App\Models\CourseSection;
use App\Models\Department;
use App\Models\CourseSite;
use App\Models\Role; 
use App\Models\User;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Create departments
        $departments = [
            'Computer Science',
            'Business Administration',
            'Information Technology',
            'Nursing',
            'English',
            'Mathematics',
            'Physics',
            'Chemistry'
        ];

        foreach ($departments as $deptName) {
            $code = strtoupper(substr(str_replace(' ', '', $deptName), 0, 4));
            
            Department::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $deptName,
                    'description' => "Department of {$deptName}",
                    'is_active' => true
                ]
            );
        }

        // Create Academic Programs
        $programs = [
            [
                'code' => 'BSCS',
                'name' => 'Bachelor of Science in Computer Science',
                'level' => 'bachelor',
                'department' => 'Computer Science',
                'faculty' => 'Engineering and Technology',
                'duration_years' => 4,
                'total_credits' => 120,
            ],
            [
                'code' => 'BBA',
                'name' => 'Bachelor of Business Administration',
                'level' => 'bachelor',
                'department' => 'Business Administration',
                'faculty' => 'Business School',
                'duration_years' => 4,
                'total_credits' => 120,
            ],
            [
                'code' => 'BSIT',
                'name' => 'Bachelor of Science in Information Technology',
                'level' => 'bachelor',
                'department' => 'Information Technology',
                'faculty' => 'Engineering and Technology',
                'duration_years' => 4,
                'total_credits' => 120,
            ],
            [
                'code' => 'BSN',
                'name' => 'Bachelor of Science in Nursing',
                'level' => 'bachelor',
                'department' => 'Nursing',
                'faculty' => 'Health Sciences',
                'duration_years' => 4,
                'total_credits' => 135,
            ],
        ];

        foreach ($programs as $program) {
            AcademicProgram::updateOrCreate(
                ['code' => $program['code']],
                $program
            );
        }

        // Create Courses
        $courses = [
            // Computer Science Courses
            ['code' => 'CS101', 'title' => 'Introduction to Computer Science', 'department' => 'Computer Science', 'level' => 100, 'credits' => 3],
            ['code' => 'CS102', 'title' => 'Programming Fundamentals', 'department' => 'Computer Science', 'level' => 100, 'credits' => 4, 'has_lab' => true],
            ['code' => 'CS201', 'title' => 'Data Structures', 'department' => 'Computer Science', 'level' => 200, 'credits' => 3],
            ['code' => 'CS202', 'title' => 'Algorithms', 'department' => 'Computer Science', 'level' => 200, 'credits' => 3],
            ['code' => 'CS301', 'title' => 'Database Systems', 'department' => 'Computer Science', 'level' => 300, 'credits' => 3],
            ['code' => 'CS302', 'title' => 'Software Engineering', 'department' => 'Computer Science', 'level' => 300, 'credits' => 3],
            ['code' => 'CS303', 'title' => 'Web Development', 'department' => 'Computer Science', 'level' => 300, 'credits' => 3, 'has_lab' => true],
            ['code' => 'CS401', 'title' => 'Artificial Intelligence', 'department' => 'Computer Science', 'level' => 400, 'credits' => 3],
            
            // Business Courses
            ['code' => 'BUS101', 'title' => 'Introduction to Business', 'department' => 'Business Administration', 'level' => 100, 'credits' => 3],
            ['code' => 'BUS102', 'title' => 'Principles of Management', 'department' => 'Business Administration', 'level' => 100, 'credits' => 3],
            ['code' => 'ACC101', 'title' => 'Financial Accounting', 'department' => 'Business Administration', 'level' => 100, 'credits' => 3],
            ['code' => 'MKT201', 'title' => 'Marketing Principles', 'department' => 'Business Administration', 'level' => 200, 'credits' => 3],
            ['code' => 'FIN201', 'title' => 'Corporate Finance', 'department' => 'Business Administration', 'level' => 200, 'credits' => 3],
            ['code' => 'BUS301', 'title' => 'Business Ethics', 'department' => 'Business Administration', 'level' => 300, 'credits' => 3],
            
            // General Education
            ['code' => 'ENG101', 'title' => 'English Composition I', 'department' => 'English', 'level' => 100, 'credits' => 3],
            ['code' => 'ENG102', 'title' => 'English Composition II', 'department' => 'English', 'level' => 100, 'credits' => 3],
            ['code' => 'MATH101', 'title' => 'Calculus I', 'department' => 'Mathematics', 'level' => 100, 'credits' => 4],
            ['code' => 'MATH102', 'title' => 'Calculus II', 'department' => 'Mathematics', 'level' => 100, 'credits' => 4],
            ['code' => 'PHY101', 'title' => 'Physics I', 'department' => 'Physics', 'level' => 100, 'credits' => 4, 'has_lab' => true],
            ['code' => 'CHEM101', 'title' => 'Chemistry I', 'department' => 'Chemistry', 'level' => 100, 'credits' => 4, 'has_lab' => true],
        ];

        foreach ($courses as $courseData) {
            $dept = Department::where('name', $courseData['department'])->first();
            
            // Set both department (string) and department_id (foreign key)
            if ($dept) {
                $courseData['department_id'] = $dept->id;
            }
            // Keep department as string for backward compatibility
            
            $courseData['description'] = "This course covers the fundamental concepts of {$courseData['title']}.";
            $courseData['type'] = 'core';
            $courseData['grading_method'] = 'letter';
            $courseData['is_active'] = true;
            $courseData['has_lab'] = $courseData['has_lab'] ?? false;
            
            Course::updateOrCreate(
                ['code' => $courseData['code']],
                $courseData
            );
        }

        // Create Academic Terms
        $currentYear = date('Y');
        $terms = [
            [
                'code' => "{$currentYear}-FALL",
                'name' => "Fall {$currentYear}",
                'type' => 'fall',
                'academic_year' => $currentYear,
                'start_date' => "{$currentYear}-09-01",
                'end_date' => "{$currentYear}-12-15",
                'registration_start' => "{$currentYear}-08-01",
                'registration_end' => "{$currentYear}-08-31",
                'is_current' => true,
                'is_active' => true,
            ],
            [
                'code' => ($currentYear + 1) . "-SPRING",
                'name' => "Spring " . ($currentYear + 1),
                'type' => 'spring',
                'academic_year' => $currentYear + 1,
                'start_date' => ($currentYear + 1) . "-01-15",
                'end_date' => ($currentYear + 1) . "-05-15",
                'registration_start' => "{$currentYear}-12-01",
                'registration_end' => "{$currentYear}-12-31",
                'is_current' => false,
                'is_active' => true,
            ],
            [
                'code' => "{$currentYear}-SUMMER",
                'name' => "Summer {$currentYear}",
                'type' => 'summer',
                'academic_year' => $currentYear,
                'start_date' => ($currentYear + 1) . "-06-01",
                'end_date' => ($currentYear + 1) . "-08-15",
                'registration_start' => ($currentYear + 1) . "-05-01",
                'registration_end' => ($currentYear + 1) . "-05-31",
                'is_current' => false,
                'is_active' => true,
            ],
        ];

        foreach ($terms as $term) {
            $term['add_drop_deadline'] = date('Y-m-d', strtotime($term['start_date'] . ' +2 weeks'));
            $term['withdrawal_deadline'] = date('Y-m-d', strtotime($term['start_date'] . ' +10 weeks'));
            $term['grades_due_date'] = date('Y-m-d', strtotime($term['end_date'] . ' +1 week'));
            
            AcademicTerm::updateOrCreate(
                ['code' => $term['code']],
                $term
            );
        }

        // Create faculty if needed
        $instructors = User::where('user_type', 'faculty')->get();
        if ($instructors->isEmpty()) {
            $facultyMembers = [
                ['name' => 'Dr. John Smith', 'email' => 'john.smith@intellicampus.edu'],
                ['name' => 'Dr. Jane Doe', 'email' => 'jane.doe@intellicampus.edu'],
                ['name' => 'Prof. Robert Johnson', 'email' => 'robert.johnson@intellicampus.edu'],
                ['name' => 'Dr. Emily Brown', 'email' => 'emily.brown@intellicampus.edu'],
            ];

            foreach ($facultyMembers as $faculty) {
                $user = User::updateOrCreate(
                    ['email' => $faculty['email']],
                    [
                        'name' => $faculty['name'],
                        'username' => str_replace(' ', '.', strtolower($faculty['name'])),
                        'password' => bcrypt('Faculty123!'),
                        'user_type' => 'faculty',
                        'status' => 'active',
                        'email_verified_at' => now(),
                    ]
                );
                
                // Assign faculty role if method exists
                $facultyRole = Role::where('slug', 'faculty')->first();
                if ($facultyRole && method_exists($user, 'assignRole')) {
                    $user->assignRole($facultyRole);
                }
            }
            
            $instructors = User::where('user_type', 'faculty')->get();
        }

        // Create Course Sections
        $currentTerm = AcademicTerm::where('is_current', true)->first();

        if (!$currentTerm) {
            $this->command->error('No current term found! Please ensure at least one term is marked as current.');
            return;
        }

        $courses = Course::all();

        // Get existing CRNs to avoid duplicates
        $existingCrns = CourseSection::pluck('crn')->toArray();
        $crnCounter = 10000;

        foreach ($courses as $index => $course) {
            // Generate unique CRN
            do {
                $crn = str_pad($crnCounter++, 5, '0', STR_PAD_LEFT);
            } while (in_array($crn, $existingCrns));
            
            $existingCrns[] = $crn; // Add to list to avoid duplicates in this run
            
            $existingSection = CourseSection::where('course_id', $course->id)
                ->where('term_id', $currentTerm->id)
                ->where('section_number', '01')
                ->first();
            
            if (!$existingSection) {
                $section = CourseSection::create([
                    'crn' => $crn,
                    'course_id' => $course->id,
                    'term_id' => $currentTerm->id,
                    'section_number' => '01',
                    'instructor_id' => $instructors->isNotEmpty() ? $instructors->random()->id : null,
                    'delivery_mode' => collect(['traditional', 'hybrid', 'online_sync'])->random(),
                    'enrollment_capacity' => rand(20, 40),
                    'current_enrollment' => 0,
                    'waitlist_capacity' => 5,
                    'status' => 'open',
                    'days_of_week' => collect(['MWF', 'TTh', 'MW', 'TR'])->random(),
                    'start_time' => collect(['08:00:00', '09:00:00', '10:00:00', '11:00:00', '13:00:00'])->random(),
                    'end_time' => '09:50:00',
                    'room' => 'Room ' . (101 + $index),
                    'building' => collect(['Main Building', 'Science Hall', 'Engineering Building'])->random(),
                ]);
                
                // Create LMS Course Site if model exists
                if (class_exists('App\Models\CourseSite')) {
                    try {
                        CourseSite::create([
                            'section_id' => $section->id,
                            'site_code' => $course->code . '-' . $section->section_number . '-' . $currentTerm->code,
                            'site_name' => $course->title . ' (' . $currentTerm->name . ')',
                            'description' => $course->description,
                            'is_active' => true,
                            'is_published' => false,
                            'settings' => []
                        ]);
                    } catch (\Exception $e) {
                        $this->command->warn('Could not create course site for section ' . $section->crn . ': ' . $e->getMessage());
                    }
                }
            }
        }

        // Output results
        $this->command->info('Course data seeded successfully!');
        $this->command->info('Created/Updated: ' . Department::count() . ' departments');
        $this->command->info('Created/Updated: ' . AcademicProgram::count() . ' programs');
        $this->command->info('Created/Updated: ' . Course::count() . ' courses');
        $this->command->info('Created/Updated: ' . AcademicTerm::count() . ' terms');
        $this->command->info('Created/Updated: ' . CourseSection::count() . ' sections');
        
        if (class_exists('App\Models\CourseSite')) {
            $this->command->info('Created/Updated: ' . CourseSite::count() . ' LMS course sites');
        }
    }
}