<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks for seeding
        DB::statement('SET session_replication_role = replica;');
        
        $this->command->info('Starting database seeding...');
        
        // Clear existing data in correct order (respecting foreign keys)
        $this->command->info('Clearing existing data...');
        
        // Clear user-related tables first (pivot tables)
        DB::table('role_user')->truncate();
        DB::table('permission_role')->truncate();
        DB::table('permission_user')->truncate();
        DB::table('user_activity_logs')->truncate();
        
        // Clear main tables
        DB::table('students')->truncate();
        // Add other tables here as we build them
        // DB::table('courses')->truncate();
        // DB::table('enrollments')->truncate();
        
        // Seed roles and permissions first (required for users)
        $this->command->info('Setting up roles and permissions...');
        $this->call([
            RoleAndPermissionSeeder::class,
        ]);
        
        // Create default users with roles
        $this->command->info('Creating default users with roles...');
        $this->createDefaultUsers();
        
        // Call all module seeders
        $this->command->info('Seeding modules...');
        $this->call([
            StudentSeeder::class,
            // Add other seeders here as we build them
            // CourseSeeder::class,
            // FacultySeeder::class,
        ]);
        
        // Re-enable foreign key checks
        DB::statement('SET session_replication_role = DEFAULT;');
        
        $this->command->info('Database seeding completed successfully!');
        $this->displayLoginCredentials();
    }
    
    /**
     * Create default system users with appropriate roles
     */
    private function createDefaultUsers(): void
    {
        // The super admin is already created by RoleAndPermissionSeeder
        // Let's create additional test users with different roles
        
        // Create System Administrator
        $systemAdmin = User::updateOrCreate(
            ['email' => 'sysadmin@intellicampus.edu'],
            [
                'name' => 'System Administrator',
                'username' => 'sysadmin',
                'password' => Hash::make('sysadmin123'),
                'user_type' => 'admin',
                'status' => 'active',
                'first_name' => 'System',
                'last_name' => 'Admin',
                'email_verified_at' => now(),
                'department' => 'IT Department',
                'employee_id' => 'EMP001',
            ]
        );
        $systemAdmin->assignRole('system-administrator');
        
        // Create Academic Administrator
        $academicAdmin = User::updateOrCreate(
            ['email' => 'academic@intellicampus.edu'],
            [
                'name' => 'Academic Administrator',
                'username' => 'academic.admin',
                'password' => Hash::make('academic123'),
                'user_type' => 'admin',
                'status' => 'active',
                'first_name' => 'Academic',
                'last_name' => 'Admin',
                'email_verified_at' => now(),
                'department' => 'Academic Affairs',
                'employee_id' => 'EMP002',
            ]
        );
        $academicAdmin->assignRole('academic-administrator');
        
        // Create Financial Administrator
        $financialAdmin = User::updateOrCreate(
            ['email' => 'finance@intellicampus.edu'],
            [
                'name' => 'Financial Administrator',
                'username' => 'finance.admin',
                'password' => Hash::make('finance123'),
                'user_type' => 'admin',
                'status' => 'active',
                'first_name' => 'Finance',
                'last_name' => 'Admin',
                'email_verified_at' => now(),
                'department' => 'Finance Department',
                'employee_id' => 'EMP003',
            ]
        );
        $financialAdmin->assignRole('financial-administrator');
        
        // Create Registrar
        $registrar = User::updateOrCreate(
            ['email' => 'registrar@intellicampus.edu'],
            [
                'name' => 'University Registrar',
                'username' => 'registrar',
                'password' => Hash::make('registrar123'),
                'user_type' => 'staff',
                'status' => 'active',
                'first_name' => 'University',
                'last_name' => 'Registrar',
                'email_verified_at' => now(),
                'department' => 'Registrar Office',
                'employee_id' => 'EMP004',
            ]
        );
        $registrar->assignRole('registrar');
        
        // Create Dean
        $dean = User::updateOrCreate(
            ['email' => 'dean@intellicampus.edu'],
            [
                'name' => 'Dr. Robert Johnson',
                'username' => 'dean.johnson',
                'password' => Hash::make('dean123'),
                'user_type' => 'faculty',
                'status' => 'active',
                'title' => 'Dr.',
                'first_name' => 'Robert',
                'last_name' => 'Johnson',
                'email_verified_at' => now(),
                'department' => 'Engineering',
                'employee_id' => 'FAC001',
                'designation' => 'Dean of Engineering',
                'highest_qualification' => 'Ph.D. in Computer Science',
            ]
        );
        $dean->assignRole('dean');
        
        // Create Department Head
        $deptHead = User::updateOrCreate(
            ['email' => 'depthead@intellicampus.edu'],
            [
                'name' => 'Dr. Sarah Williams',
                'username' => 'sarah.williams',
                'password' => Hash::make('depthead123'),
                'user_type' => 'faculty',
                'status' => 'active',
                'title' => 'Dr.',
                'first_name' => 'Sarah',
                'last_name' => 'Williams',
                'email_verified_at' => now(),
                'department' => 'Computer Science',
                'employee_id' => 'FAC002',
                'designation' => 'Head of Computer Science',
                'highest_qualification' => 'Ph.D. in Computer Science',
            ]
        );
        $deptHead->assignRole('department-head');
        
        // Create Faculty Members
        $faculty1 = User::updateOrCreate(
            ['email' => 'faculty1@intellicampus.edu'],
            [
                'name' => 'Dr. James Smith',
                'username' => 'james.smith',
                'password' => Hash::make('faculty123'),
                'user_type' => 'faculty',
                'status' => 'active',
                'title' => 'Dr.',
                'first_name' => 'James',
                'last_name' => 'Smith',
                'email_verified_at' => now(),
                'department' => 'Computer Science',
                'employee_id' => 'FAC003',
                'designation' => 'Associate Professor',
                'highest_qualification' => 'Ph.D. in Software Engineering',
                'specialization' => 'Artificial Intelligence',
            ]
        );
        $faculty1->assignRole('faculty');
        
        $faculty2 = User::updateOrCreate(
            ['email' => 'faculty2@intellicampus.edu'],
            [
                'name' => 'Dr. Emily Brown',
                'username' => 'emily.brown',
                'password' => Hash::make('faculty123'),
                'user_type' => 'faculty',
                'status' => 'active',
                'title' => 'Dr.',
                'first_name' => 'Emily',
                'last_name' => 'Brown',
                'email_verified_at' => now(),
                'department' => 'Mathematics',
                'employee_id' => 'FAC004',
                'designation' => 'Assistant Professor',
                'highest_qualification' => 'Ph.D. in Applied Mathematics',
                'specialization' => 'Statistical Analysis',
            ]
        );
        $faculty2->assignRole('faculty');
        
        // Create Advisor
        $advisor = User::updateOrCreate(
            ['email' => 'advisor@intellicampus.edu'],
            [
                'name' => 'Ms. Jennifer Davis',
                'username' => 'jennifer.davis',
                'password' => Hash::make('advisor123'),
                'user_type' => 'staff',
                'status' => 'active',
                'title' => 'Ms.',
                'first_name' => 'Jennifer',
                'last_name' => 'Davis',
                'email_verified_at' => now(),
                'department' => 'Student Affairs',
                'employee_id' => 'STF001',
                'designation' => 'Academic Advisor',
            ]
        );
        $advisor->assignRole('advisor');
        
        // Create Staff Members
        $staff1 = User::updateOrCreate(
            ['email' => 'staff1@intellicampus.edu'],
            [
                'name' => 'Mr. Michael Wilson',
                'username' => 'michael.wilson',
                'password' => Hash::make('staff123'),
                'user_type' => 'staff',
                'status' => 'active',
                'title' => 'Mr.',
                'first_name' => 'Michael',
                'last_name' => 'Wilson',
                'email_verified_at' => now(),
                'department' => 'Library',
                'employee_id' => 'STF002',
                'designation' => 'Library Assistant',
            ]
        );
        $staff1->assignRole('staff');
        
        $staff2 = User::updateOrCreate(
            ['email' => 'staff2@intellicampus.edu'],
            [
                'name' => 'Ms. Lisa Anderson',
                'username' => 'lisa.anderson',
                'password' => Hash::make('staff123'),
                'user_type' => 'staff',
                'status' => 'active',
                'title' => 'Ms.',
                'first_name' => 'Lisa',
                'last_name' => 'Anderson',
                'email_verified_at' => now(),
                'department' => 'Administration',
                'employee_id' => 'STF003',
                'designation' => 'Administrative Assistant',
            ]
        );
        $staff2->assignRole('staff');
        
        // Create Student Users
        $student1 = User::updateOrCreate(
            ['email' => 'student1@intellicampus.edu'],
            [
                'name' => 'John Doe',
                'username' => 'john.doe',
                'password' => Hash::make('student123'),
                'user_type' => 'student',
                'status' => 'active',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email_verified_at' => now(),
                'date_of_birth' => '2003-05-15',
                'gender' => 'Male',
            ]
        );
        $student1->assignRole('student');
        
        $student2 = User::updateOrCreate(
            ['email' => 'student2@intellicampus.edu'],
            [
                'name' => 'Jane Smith',
                'username' => 'jane.smith',
                'password' => Hash::make('student123'),
                'user_type' => 'student',
                'status' => 'active',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email_verified_at' => now(),
                'date_of_birth' => '2004-08-22',
                'gender' => 'Female',
            ]
        );
        $student2->assignRole('student');
        
        // Create Parent/Guardian User
        $parent = User::updateOrCreate(
            ['email' => 'parent@intellicampus.edu'],
            [
                'name' => 'Mr. Robert Doe',
                'username' => 'robert.doe',
                'password' => Hash::make('parent123'),
                'user_type' => 'parent',
                'status' => 'active',
                'title' => 'Mr.',
                'first_name' => 'Robert',
                'last_name' => 'Doe',
                'email_verified_at' => now(),
            ]
        );
        $parent->assignRole('parent-guardian');
        
        // Create Auditor User
        $auditor = User::updateOrCreate(
            ['email' => 'auditor@intellicampus.edu'],
            [
                'name' => 'Ms. Patricia Moore',
                'username' => 'patricia.moore',
                'password' => Hash::make('auditor123'),
                'user_type' => 'staff',
                'status' => 'active',
                'title' => 'Ms.',
                'first_name' => 'Patricia',
                'last_name' => 'Moore',
                'email_verified_at' => now(),
                'department' => 'Quality Assurance',
                'employee_id' => 'AUD001',
                'designation' => 'System Auditor',
            ]
        );
        $auditor->assignRole('auditor');
        
        $this->command->info('Created 16 default users with assigned roles');
    }
    
    /**
     * Display login credentials for testing
     */
    private function displayLoginCredentials(): void
    {
        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('   LOGIN CREDENTIALS FOR TESTING');
        $this->command->info('========================================');
        
        $credentials = [
            ['Role', 'Email', 'Password'],
            ['----', '-----', '--------'],
            ['Super Admin', 'admin@intellicampus.edu', 'Admin@123456'],
            ['System Admin', 'sysadmin@intellicampus.edu', 'sysadmin123'],
            ['Academic Admin', 'academic@intellicampus.edu', 'academic123'],
            ['Financial Admin', 'finance@intellicampus.edu', 'finance123'],
            ['Registrar', 'registrar@intellicampus.edu', 'registrar123'],
            ['Dean', 'dean@intellicampus.edu', 'dean123'],
            ['Dept Head', 'depthead@intellicampus.edu', 'depthead123'],
            ['Faculty', 'faculty1@intellicampus.edu', 'faculty123'],
            ['Advisor', 'advisor@intellicampus.edu', 'advisor123'],
            ['Staff', 'staff1@intellicampus.edu', 'staff123'],
            ['Student', 'student1@intellicampus.edu', 'student123'],
            ['Parent', 'parent@intellicampus.edu', 'parent123'],
            ['Auditor', 'auditor@intellicampus.edu', 'auditor123'],
        ];
        
        foreach ($credentials as $row) {
            $this->command->info(sprintf('%-15s | %-30s | %s', ...$row));
        }
        
        $this->command->info('========================================');
        $this->command->warn('⚠️  Please change these passwords in production!');
        $this->command->newLine();
    }
}