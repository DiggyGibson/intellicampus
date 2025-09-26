<?php

// database/seeders/OrganizationalStructureSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\College;
use App\Models\School;
use App\Models\Department;
use App\Models\Division;
use App\Models\User;
use App\Models\Course;
use App\Models\UserDepartmentAffiliation;
use App\Models\FacultyCourseAssignment;
use App\Models\OrganizationalPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OrganizationalStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        
        try {
            // Create Colleges
            $this->createColleges();
            
            // Create Schools
            $this->createSchools();
            
            // Create Departments
            $this->createDepartments();
            
            // Create Divisions
            $this->createDivisions();
            
            // Create Leadership Users
            $this->createLeadershipUsers();
            
            // Create Faculty Members
            $this->createFacultyMembers();
            
            // Create Department Affiliations
            $this->createDepartmentAffiliations();
            
            // Assign Courses to Departments
            $this->assignCoursesToDepartments();
            
            // Create Organizational Permissions
            $this->createOrganizationalPermissions();
            
            DB::commit();
            
            $this->command->info('Organizational structure seeded successfully!');
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('Error seeding organizational structure: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create colleges
     */
    private function createColleges()
    {
        $colleges = [
            [
                'code' => 'CAS',
                'name' => 'College of Arts and Sciences',
                'description' => 'Liberal arts and sciences education',
                'type' => 'academic',
                'email' => 'cas@university.edu',
                'phone' => '555-0100',
                'building' => 'Liberal Arts Building',
                'is_active' => true,
            ],
            [
                'code' => 'COE',
                'name' => 'College of Engineering',
                'description' => 'Engineering and technology programs',
                'type' => 'academic',
                'email' => 'engineering@university.edu',
                'phone' => '555-0200',
                'building' => 'Engineering Complex',
                'is_active' => true,
            ],
            [
                'code' => 'COB',
                'name' => 'College of Business',
                'description' => 'Business and management education',
                'type' => 'professional',
                'email' => 'business@university.edu',
                'phone' => '555-0300',
                'building' => 'Business Tower',
                'is_active' => true,
            ],
            [
                'code' => 'COM',
                'name' => 'College of Medicine',
                'description' => 'Medical and health sciences',
                'type' => 'professional',
                'email' => 'medicine@university.edu',
                'phone' => '555-0400',
                'building' => 'Medical Center',
                'is_active' => true,
            ],
        ];

        foreach ($colleges as $collegeData) {
            College::create($collegeData);
        }
        
        $this->command->info('Created ' . count($colleges) . ' colleges');
    }

    /**
     * Create schools
     */
    private function createSchools()
    {
        $engineeringCollege = College::where('code', 'COE')->first();
        $medicalCollege = College::where('code', 'COM')->first();
        
        $schools = [
            [
                'code' => 'SCS',
                'name' => 'School of Computer Science',
                'description' => 'Computer science and information technology',
                'college_id' => $engineeringCollege->id,
                'email' => 'cs@university.edu',
                'phone' => '555-0210',
                'location' => 'Technology Building',
                'is_active' => true,
            ],
            [
                'code' => 'SNS',
                'name' => 'School of Nursing',
                'description' => 'Nursing and healthcare programs',
                'college_id' => $medicalCollege->id,
                'email' => 'nursing@university.edu',
                'phone' => '555-0410',
                'location' => 'Health Sciences Building',
                'is_active' => true,
            ],
        ];

        foreach ($schools as $schoolData) {
            School::create($schoolData);
        }
        
        $this->command->info('Created ' . count($schools) . ' schools');
    }

    /**
     * Create departments
     */
    private function createDepartments()
    {
        $casCollege = College::where('code', 'CAS')->first();
        $coeCollege = College::where('code', 'COE')->first();
        $cobCollege = College::where('code', 'COB')->first();
        $csSchool = School::where('code', 'SCS')->first();
        
        $departments = [
            // Arts & Sciences Departments
            [
                'code' => 'MATH',
                'name' => 'Department of Mathematics',
                'description' => 'Mathematics and statistics programs',
                'type' => 'academic',
                'college_id' => $casCollege->id,
                'email' => 'math@university.edu',
                'phone' => '555-0110',
                'building' => 'Mathematics Building',
                'office' => 'Room 200',
                'is_active' => true,
                'accepts_students' => true,
                'offers_courses' => true,
            ],
            [
                'code' => 'PHYS',
                'name' => 'Department of Physics',
                'description' => 'Physics and astronomy programs',
                'type' => 'academic',
                'college_id' => $casCollege->id,
                'email' => 'physics@university.edu',
                'phone' => '555-0120',
                'building' => 'Science Building',
                'office' => 'Room 300',
                'is_active' => true,
                'accepts_students' => true,
                'offers_courses' => true,
            ],
            [
                'code' => 'ENGL',
                'name' => 'Department of English',
                'description' => 'English literature and writing programs',
                'type' => 'academic',
                'college_id' => $casCollege->id,
                'email' => 'english@university.edu',
                'phone' => '555-0130',
                'building' => 'Liberal Arts Building',
                'office' => 'Room 400',
                'is_active' => true,
                'accepts_students' => true,
                'offers_courses' => true,
            ],
            
            // Engineering Departments
            [
                'code' => 'ECE',
                'name' => 'Department of Electrical and Computer Engineering',
                'description' => 'Electrical and computer engineering programs',
                'type' => 'academic',
                'college_id' => $coeCollege->id,
                'email' => 'ece@university.edu',
                'phone' => '555-0220',
                'building' => 'Engineering Building A',
                'office' => 'Room 100',
                'is_active' => true,
                'accepts_students' => true,
                'offers_courses' => true,
            ],
            [
                'code' => 'MECH',
                'name' => 'Department of Mechanical Engineering',
                'description' => 'Mechanical engineering programs',
                'type' => 'academic',
                'college_id' => $coeCollege->id,
                'email' => 'mech@university.edu',
                'phone' => '555-0230',
                'building' => 'Engineering Building B',
                'office' => 'Room 200',
                'is_active' => true,
                'accepts_students' => true,
                'offers_courses' => true,
            ],
            
            // Computer Science Department (under School)
            [
                'code' => 'CS',
                'name' => 'Department of Computer Science',
                'description' => 'Computer science and software engineering',
                'type' => 'academic',
                'school_id' => $csSchool->id,
                'email' => 'cs.dept@university.edu',
                'phone' => '555-0211',
                'building' => 'Technology Building',
                'office' => 'Room 500',
                'is_active' => true,
                'accepts_students' => true,
                'offers_courses' => true,
            ],
            
            // Business Departments
            [
                'code' => 'ACCT',
                'name' => 'Department of Accounting',
                'description' => 'Accounting and finance programs',
                'type' => 'academic',
                'college_id' => $cobCollege->id,
                'email' => 'accounting@university.edu',
                'phone' => '555-0310',
                'building' => 'Business Tower',
                'office' => 'Floor 10',
                'is_active' => true,
                'accepts_students' => true,
                'offers_courses' => true,
            ],
            [
                'code' => 'MGMT',
                'name' => 'Department of Management',
                'description' => 'Management and organizational behavior',
                'type' => 'academic',
                'college_id' => $cobCollege->id,
                'email' => 'management@university.edu',
                'phone' => '555-0320',
                'building' => 'Business Tower',
                'office' => 'Floor 12',
                'is_active' => true,
                'accepts_students' => true,
                'offers_courses' => true,
            ],
        ];

        foreach ($departments as $deptData) {
            Department::create($deptData);
        }
        
        $this->command->info('Created ' . count($departments) . ' departments');
    }

    /**
     * Create divisions within departments
     */
    private function createDivisions()
    {
        $csDept = Department::where('code', 'CS')->first();
        $mathDept = Department::where('code', 'MATH')->first();
        
        $divisions = [
            [
                'code' => 'AI',
                'name' => 'Artificial Intelligence Division',
                'description' => 'AI and machine learning research',
                'department_id' => $csDept->id,
                'is_active' => true,
            ],
            [
                'code' => 'SE',
                'name' => 'Software Engineering Division',
                'description' => 'Software development and engineering',
                'department_id' => $csDept->id,
                'is_active' => true,
            ],
            [
                'code' => 'STAT',
                'name' => 'Statistics Division',
                'description' => 'Applied and theoretical statistics',
                'department_id' => $mathDept->id,
                'is_active' => true,
            ],
        ];

        foreach ($divisions as $divData) {
            Division::create($divData);
        }
        
        $this->command->info('Created ' . count($divisions) . ' divisions');
    }

    /**
     * Create leadership users
     */
    private function createLeadershipUsers()
    {
        $colleges = College::all();
        $schools = School::all();
        $departments = Department::all();
        
        // Create Deans
        foreach ($colleges as $college) {
            $dean = User::create([
                'name' => 'Dean ' . $college->code,
                'email' => 'dean.' . strtolower($college->code) . '@university.edu',
                'username' => 'dean_' . strtolower($college->code),
                'password' => Hash::make('password'),
                'user_type' => 'faculty',
                'college_id' => $college->id,
                'organizational_role' => 'dean',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            
            $dean->assignRole('dean');
            $college->update(['dean_id' => $dean->id]);
            
            // Create Associate Dean
            $assocDean = User::create([
                'name' => 'Associate Dean ' . $college->code,
                'email' => 'assoc.dean.' . strtolower($college->code) . '@university.edu',
                'username' => 'assoc_dean_' . strtolower($college->code),
                'password' => Hash::make('password'),
                'user_type' => 'faculty',
                'college_id' => $college->id,
                'organizational_role' => 'associate_dean',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            
            $assocDean->assignRole('dean');
            $college->update(['associate_dean_id' => $assocDean->id]);
        }
        
        // Create School Directors
        foreach ($schools as $school) {
            $director = User::create([
                'name' => 'Director ' . $school->code,
                'email' => 'director.' . strtolower($school->code) . '@university.edu',
                'username' => 'director_' . strtolower($school->code),
                'password' => Hash::make('password'),
                'user_type' => 'faculty',
                'college_id' => $school->college_id,
                'school_id' => $school->id,
                'organizational_role' => 'director',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            
            $director->assignRole('department-head');
            $school->update(['director_id' => $director->id]);
        }
        
        // Create Department Heads
        foreach ($departments as $dept) {
            $head = User::create([
                'name' => 'Head ' . $dept->code,
                'email' => 'head.' . strtolower($dept->code) . '@university.edu',
                'username' => 'head_' . strtolower($dept->code),
                'password' => Hash::make('password'),
                'user_type' => 'faculty',
                'college_id' => $dept->college_id,
                'school_id' => $dept->school_id,
                'department_id' => $dept->id,
                'organizational_role' => 'department_head',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            
            $head->assignRole('department-head');
            $dept->update(['head_id' => $head->id]);
        }
        
        $this->command->info('Created leadership users');
    }

    /**
     * Create faculty members
     */
    private function createFacultyMembers()
    {
        $departments = Department::all();
        
        foreach ($departments as $dept) {
            // Create 3-5 faculty members per department
            $facultyCount = rand(3, 5);
            
            for ($i = 1; $i <= $facultyCount; $i++) {
                $faculty = User::create([
                    'name' => 'Prof. ' . $dept->code . ' ' . $i,
                    'email' => strtolower($dept->code) . '.faculty' . $i . '@university.edu',
                    'username' => strtolower($dept->code) . '_faculty' . $i,
                    'password' => Hash::make('password'),
                    'user_type' => 'faculty',
                    'college_id' => $dept->college_id,
                    'school_id' => $dept->school_id,
                    'department_id' => $dept->id,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
                
                $faculty->assignRole('faculty');
                
                // Create primary affiliation
                UserDepartmentAffiliation::create([
                    'user_id' => $faculty->id,
                    'department_id' => $dept->id,
                    'affiliation_type' => 'primary',
                    'role' => 'faculty',
                    'appointment_percentage' => 100,
                    'start_date' => now()->subYears(rand(1, 10)),
                    'is_active' => true,
                    'position_title' => 'Assistant Professor',
                ]);
            }
        }
        
        $this->command->info('Created faculty members');
    }

    /**
     * Create cross-department affiliations
     */
    private function createDepartmentAffiliations()
    {
        // Create some cross-department affiliations
        $mathDept = Department::where('code', 'MATH')->first();
        $csDept = Department::where('code', 'CS')->first();
        $physicsDept = Department::where('code', 'PHYS')->first();
        
        // Math faculty teaching in CS
        $mathFaculty = User::where('department_id', $mathDept->id)->first();
        if ($mathFaculty && $csDept) {
            UserDepartmentAffiliation::create([
                'user_id' => $mathFaculty->id,
                'department_id' => $csDept->id,
                'affiliation_type' => 'cross_appointment',
                'role' => 'faculty',
                'appointment_percentage' => 20,
                'start_date' => now()->subMonths(6),
                'is_active' => true,
                'position_title' => 'Adjunct Faculty',
            ]);
            
            // Update user's secondary departments
            $mathFaculty->update([
                'secondary_departments' => [$csDept->id],
            ]);
        }
        
        // Physics faculty in Math
        $physicsFaculty = User::where('department_id', $physicsDept->id)->first();
        if ($physicsFaculty && $mathDept) {
            UserDepartmentAffiliation::create([
                'user_id' => $physicsFaculty->id,
                'department_id' => $mathDept->id,
                'affiliation_type' => 'secondary',
                'role' => 'faculty',
                'appointment_percentage' => 30,
                'start_date' => now()->subMonths(3),
                'is_active' => true,
                'position_title' => 'Visiting Faculty',
            ]);
        }
        
        $this->command->info('Created department affiliations');
    }

    /**
     * Assign existing courses to departments
     */
    private function assignCoursesToDepartments()
    {
        $departments = Department::all()->keyBy('code');
        
        // Map course prefixes to departments
        $courseMapping = [
            'CS' => 'CS',
            'MATH' => 'MATH',
            'PHYS' => 'PHYS',
            'ENGL' => 'ENGL',
            'EE' => 'ECE',
            'ME' => 'MECH',
            'ACCT' => 'ACCT',
            'MGMT' => 'MGMT',
        ];
        
        foreach ($courseMapping as $prefix => $deptCode) {
            if (isset($departments[$deptCode])) {
                Course::where('code', 'LIKE', $prefix . '%')
                    ->update(['department_id' => $departments[$deptCode]->id]);
            }
        }
        
        // Assign coordinators to some courses
        $courses = Course::whereNotNull('department_id')->get();
        
        foreach ($courses as $course) {
            // Randomly assign a faculty member from the department as coordinator
            $faculty = User::where('department_id', $course->department_id)
                ->where('user_type', 'faculty')
                ->inRandomOrder()
                ->first();
            
            if ($faculty) {
                $course->update(['coordinator_id' => $faculty->id]);
                
                // Create faculty course assignment
                FacultyCourseAssignment::create([
                    'faculty_id' => $faculty->id,
                    'course_id' => $course->id,
                    'assignment_type' => 'coordinator',
                    'can_edit_content' => true,
                    'can_manage_grades' => true,
                    'can_view_all_sections' => true,
                    'effective_from' => now()->subMonths(6),
                    'is_active' => true,
                ]);
            }
        }
        
        $this->command->info('Assigned courses to departments');
    }

    /**
     * Create organizational permissions
     */
    private function createOrganizationalPermissions()
    {
        // Give deans additional permissions for their college
        $deans = User::where('organizational_role', 'dean')->get();
        
        foreach ($deans as $dean) {
            if ($dean->college_id) {
                OrganizationalPermission::create([
                    'user_id' => $dean->id,
                    'scope_type' => 'college',
                    'scope_id' => $dean->college_id,
                    'permission_key' => 'college.manage',
                    'access_level' => 'manage',
                    'granted_at' => now(),
                    'is_active' => true,
                ]);
            }
        }
        
        // Give department heads management permissions
        $deptHeads = User::where('organizational_role', 'department_head')->get();
        
        foreach ($deptHeads as $head) {
            if ($head->department_id) {
                OrganizationalPermission::create([
                    'user_id' => $head->id,
                    'scope_type' => 'department',
                    'scope_id' => $head->department_id,
                    'permission_key' => 'department.manage',
                    'access_level' => 'manage',
                    'granted_at' => now(),
                    'is_active' => true,
                ]);
            }
        }
        
        $this->command->info('Created organizational permissions');
    }
}