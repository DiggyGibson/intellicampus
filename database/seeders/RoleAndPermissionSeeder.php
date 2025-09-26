<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks for seeding
        DB::statement('SET session_replication_role = replica');

        // Clear existing data
        DB::table('permission_role')->truncate();
        DB::table('permission_user')->truncate();
        DB::table('role_user')->truncate();
        Permission::query()->forceDelete();
        Role::query()->forceDelete();

        // Create all permissions first
        $this->createPermissions();

        // Create all roles
        $this->createRoles();

        // Assign permissions to roles
        $this->assignPermissionsToRoles();

        // Create default super admin user
        $this->createSuperAdminUser();

        // Re-enable foreign key checks
        DB::statement('SET session_replication_role = DEFAULT');

        $this->command->info('Roles and permissions seeded successfully!');
    }

    /**
     * Create all system permissions
     */
    private function createPermissions(): void
    {
        $modules = [
            'dashboard' => [
                'view' => 'View dashboard and analytics',
                'manage' => 'Manage dashboard widgets and settings',
            ],
            'users' => [
                'view' => 'View user list and details',
                'create' => 'Create new users',
                'update' => 'Update user information',
                'delete' => 'Delete users',
                'manage' => 'Full user management access',
                'assign_roles' => 'Assign roles to users',
                'manage_permissions' => 'Manage user permissions',
                'import' => 'Import users from files',
                'export' => 'Export user data',
            ],
            'students' => [
                'view' => 'View student records',
                'create' => 'Create new student records',
                'update' => 'Update student information',
                'delete' => 'Delete student records',
                'manage' => 'Full student management access',
                'view_grades' => 'View student grades',
                'update_grades' => 'Update student grades',
                'view_attendance' => 'View student attendance',
                'update_attendance' => 'Update student attendance',
                'view_financial' => 'View student financial information',
                'manage_enrollment' => 'Manage student enrollment',
                'import' => 'Import student data',
                'export' => 'Export student data',
            ],
            'faculty' => [
                'view' => 'View faculty records',
                'create' => 'Create new faculty records',
                'update' => 'Update faculty information',
                'delete' => 'Delete faculty records',
                'manage' => 'Full faculty management access',
                'assign_courses' => 'Assign courses to faculty',
                'view_schedule' => 'View faculty schedules',
                'manage_schedule' => 'Manage faculty schedules',
                'view_evaluations' => 'View faculty evaluations',
                'manage_evaluations' => 'Manage faculty evaluations',
            ],
            'courses' => [
                'view' => 'View course catalog',
                'create' => 'Create new courses',
                'update' => 'Update course information',
                'delete' => 'Delete courses',
                'manage' => 'Full course management access',
                'assign_faculty' => 'Assign faculty to courses',
                'manage_prerequisites' => 'Manage course prerequisites',
                'manage_sections' => 'Manage course sections',
                'view_enrollments' => 'View course enrollments',
                'manage_enrollments' => 'Manage course enrollments',
            ],
            'enrollment' => [
                'view' => 'View enrollment records',
                'create' => 'Create new enrollments',
                'update' => 'Update enrollment information',
                'delete' => 'Delete enrollments',
                'manage' => 'Full enrollment management access',
                'approve' => 'Approve enrollment requests',
                'register_students' => 'Register students for courses',
                'drop_students' => 'Drop students from courses',
                'manage_waitlist' => 'Manage course waitlists',
                'override_prerequisites' => 'Override course prerequisites',
            ],
            'grades' => [
                'view' => 'View grades',
                'create' => 'Enter new grades',
                'update' => 'Update existing grades',
                'delete' => 'Delete grades',
                'manage' => 'Full grade management access',
                'approve' => 'Approve final grades',
                'generate_transcripts' => 'Generate transcripts',
                'calculate_gpa' => 'Calculate GPA',
                'manage_grading_scales' => 'Manage grading scales',
            ],
            'attendance' => [
                'view' => 'View attendance records',
                'create' => 'Mark attendance',
                'update' => 'Update attendance records',
                'delete' => 'Delete attendance records',
                'manage' => 'Full attendance management access',
                'generate_reports' => 'Generate attendance reports',
                'manage_policies' => 'Manage attendance policies',
            ],
            'finance' => [
                'view' => 'View financial records',
                'create' => 'Create financial transactions',
                'update' => 'Update financial records',
                'delete' => 'Delete financial records',
                'manage' => 'Full financial management access',
                'manage_fees' => 'Manage fee structure',
                'process_payments' => 'Process payments',
                'generate_invoices' => 'Generate invoices',
                'manage_scholarships' => 'Manage scholarships',
                'view_reports' => 'View financial reports',
                'generate_reports' => 'Generate financial reports',
                'manage_refunds' => 'Process refunds',
            ],
            'library' => [
                'view' => 'View library resources',
                'create' => 'Add new library resources',
                'update' => 'Update library resources',
                'delete' => 'Delete library resources',
                'manage' => 'Full library management access',
                'issue_books' => 'Issue books to users',
                'return_books' => 'Process book returns',
                'manage_fines' => 'Manage library fines',
                'manage_reservations' => 'Manage book reservations',
            ],
            'hostel' => [
                'view' => 'View hostel information',
                'create' => 'Create hostel records',
                'update' => 'Update hostel information',
                'delete' => 'Delete hostel records',
                'manage' => 'Full hostel management access',
                'allocate_rooms' => 'Allocate rooms to students',
                'manage_facilities' => 'Manage hostel facilities',
                'manage_complaints' => 'Handle hostel complaints',
                'manage_fees' => 'Manage hostel fees',
            ],
            'transport' => [
                'view' => 'View transport information',
                'create' => 'Create transport records',
                'update' => 'Update transport information',
                'delete' => 'Delete transport records',
                'manage' => 'Full transport management access',
                'manage_routes' => 'Manage transport routes',
                'manage_vehicles' => 'Manage vehicles',
                'assign_drivers' => 'Assign drivers to routes',
                'manage_fees' => 'Manage transport fees',
            ],
            'examinations' => [
                'view' => 'View examination schedules',
                'create' => 'Create examination schedules',
                'update' => 'Update examination information',
                'delete' => 'Delete examination records',
                'manage' => 'Full examination management access',
                'schedule_exams' => 'Schedule examinations',
                'assign_invigilators' => 'Assign exam invigilators',
                'manage_venues' => 'Manage examination venues',
                'generate_halltickets' => 'Generate hall tickets',
                'manage_results' => 'Manage examination results',
            ],
            'reports' => [
                'view_academic' => 'View academic reports',
                'view_financial' => 'View financial reports',
                'view_administrative' => 'View administrative reports',
                'generate_custom' => 'Generate custom reports',
                'export' => 'Export reports',
                'schedule' => 'Schedule automated reports',
            ],
            'settings' => [
                'view' => 'View system settings',
                'update' => 'Update system settings',
                'manage_academic' => 'Manage academic settings',
                'manage_system' => 'Manage system configuration',
                'manage_security' => 'Manage security settings',
                'manage_backup' => 'Manage system backups',
                'manage_integration' => 'Manage third-party integrations',
            ],
            'audit' => [
                'view_logs' => 'View audit logs',
                'export_logs' => 'Export audit logs',
                'manage_retention' => 'Manage log retention policies',
            ],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action => $description) {
                Permission::create([
                    'name' => ucfirst(str_replace('_', ' ', $action)) . ' ' . ucfirst($module),
                    'slug' => "{$module}.{$action}",
                    'module' => $module,
                    'description' => $description,
                    'is_system' => true,
                ]);
            }
        }

        $this->command->info('Created ' . Permission::count() . ' permissions');
    }

    /**
     * Create all system roles as defined in FRS
     */
    private function createRoles(): void
    {
        $roles = [
            [
                'name' => 'Super Administrator',
                'slug' => 'super-administrator',
                'description' => 'Full system access with all permissions. Can manage system configuration, users, and all modules.',
                'is_system' => true,
                'priority' => 1,
            ],
            [
                'name' => 'System Administrator',
                'slug' => 'system-administrator',
                'description' => 'Technical administration including system settings, backups, and integrations.',
                'is_system' => true,
                'priority' => 2,
            ],
            [
                'name' => 'Academic Administrator',
                'slug' => 'academic-administrator',
                'description' => 'Manages academic operations including courses, faculty, and student records.',
                'is_system' => true,
                'priority' => 3,
            ],
            [
                'name' => 'Financial Administrator',
                'slug' => 'financial-administrator',
                'description' => 'Manages all financial operations including fees, payments, and financial reports.',
                'is_system' => true,
                'priority' => 4,
            ],
            [
                'name' => 'Registrar',
                'slug' => 'registrar',
                'description' => 'Manages student registration, enrollment, and academic records.',
                'is_system' => true,
                'priority' => 5,
            ],
            [
                'name' => 'Dean',
                'slug' => 'dean',
                'description' => 'Academic oversight with access to faculty and student performance data.',
                'is_system' => true,
                'priority' => 6,
            ],
            [
                'name' => 'Department Head',
                'slug' => 'department-head',
                'description' => 'Manages department-specific courses, faculty, and students.',
                'is_system' => true,
                'priority' => 7,
            ],
            [
                'name' => 'Faculty',
                'slug' => 'faculty',
                'description' => 'Teaching staff with access to courses, grades, and attendance.',
                'is_system' => true,
                'priority' => 8,
            ],
            [
                'name' => 'Advisor',
                'slug' => 'advisor',
                'description' => 'Student advisor with access to advisee records and academic planning.',
                'is_system' => true,
                'priority' => 9,
            ],
            [
                'name' => 'Staff',
                'slug' => 'staff',
                'description' => 'Administrative staff with limited access to specific modules.',
                'is_system' => true,
                'priority' => 10,
            ],
            [
                'name' => 'Student',
                'slug' => 'student',
                'description' => 'Students with access to their own records and services.',
                'is_system' => true,
                'priority' => 11,
            ],
            [
                'name' => 'Parent/Guardian',
                'slug' => 'parent-guardian',
                'description' => 'Limited view access to linked student records.',
                'is_system' => true,
                'priority' => 12,
            ],
            [
                'name' => 'Auditor',
                'slug' => 'auditor',
                'description' => 'Read-only access to all modules for audit purposes.',
                'is_system' => true,
                'priority' => 13,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::create($roleData);
        }

        $this->command->info('Created ' . Role::count() . ' roles');
    }

    /**
     * Assign permissions to roles based on FRS requirements
     */
    private function assignPermissionsToRoles(): void
    {
        // Super Administrator - Gets all permissions
        $superAdmin = Role::where('slug', 'super-administrator')->first();
        $superAdmin->permissions()->sync(Permission::all()->pluck('id'));

        // System Administrator
        $systemAdmin = Role::where('slug', 'system-administrator')->first();
        $systemAdmin->syncPermissions([
            'dashboard.view', 'dashboard.manage',
            'users.view', 'users.create', 'users.update', 'users.delete', 'users.manage',
            'users.assign_roles', 'users.manage_permissions', 'users.import', 'users.export',
            'settings.view', 'settings.update', 'settings.manage_system', 'settings.manage_security',
            'settings.manage_backup', 'settings.manage_integration',
            'audit.view_logs', 'audit.export_logs', 'audit.manage_retention',
            'reports.view_administrative', 'reports.generate_custom', 'reports.export',
        ]);

        // Academic Administrator
        $academicAdmin = Role::where('slug', 'academic-administrator')->first();
        $academicAdmin->syncPermissions([
            'dashboard.view',
            'students.view', 'students.create', 'students.update', 'students.delete', 'students.manage',
            'students.view_grades', 'students.update_grades', 'students.view_attendance', 'students.update_attendance',
            'students.manage_enrollment', 'students.import', 'students.export',
            'faculty.view', 'faculty.create', 'faculty.update', 'faculty.delete', 'faculty.manage',
            'faculty.assign_courses', 'faculty.view_schedule', 'faculty.manage_schedule',
            'courses.view', 'courses.create', 'courses.update', 'courses.delete', 'courses.manage',
            'courses.assign_faculty', 'courses.manage_prerequisites', 'courses.manage_sections',
            'enrollment.view', 'enrollment.create', 'enrollment.update', 'enrollment.delete', 'enrollment.manage',
            'enrollment.approve', 'enrollment.register_students', 'enrollment.drop_students',
            'grades.view', 'grades.manage', 'grades.approve', 'grades.generate_transcripts',
            'attendance.view', 'attendance.manage', 'attendance.generate_reports',
            'examinations.view', 'examinations.create', 'examinations.update', 'examinations.manage',
            'reports.view_academic', 'reports.generate_custom', 'reports.export',
            'settings.view', 'settings.manage_academic',
        ]);

        // Financial Administrator
        $financialAdmin = Role::where('slug', 'financial-administrator')->first();
        $financialAdmin->syncPermissions([
            'dashboard.view',
            'students.view', 'students.view_financial',
            'finance.view', 'finance.create', 'finance.update', 'finance.delete', 'finance.manage',
            'finance.manage_fees', 'finance.process_payments', 'finance.generate_invoices',
            'finance.manage_scholarships', 'finance.view_reports', 'finance.generate_reports', 'finance.manage_refunds',
            'hostel.view', 'hostel.manage_fees',
            'transport.view', 'transport.manage_fees',
            'library.view', 'library.manage_fines',
            'reports.view_financial', 'reports.generate_custom', 'reports.export',
        ]);

        // Registrar
        $registrar = Role::where('slug', 'registrar')->first();
        $registrar->syncPermissions([
            'dashboard.view',
            'students.view', 'students.create', 'students.update', 'students.manage_enrollment',
            'students.import', 'students.export',
            'courses.view', 'courses.view_enrollments', 'courses.manage_enrollments',
            'enrollment.view', 'enrollment.create', 'enrollment.update', 'enrollment.delete', 'enrollment.manage',
            'enrollment.approve', 'enrollment.register_students', 'enrollment.drop_students',
            'enrollment.manage_waitlist', 'enrollment.override_prerequisites',
            'grades.view', 'grades.generate_transcripts', 'grades.calculate_gpa',
            'reports.view_academic', 'reports.generate_custom', 'reports.export',
        ]);

        // Dean
        $dean = Role::where('slug', 'dean')->first();
        $dean->syncPermissions([
            'dashboard.view',
            'students.view', 'students.view_grades', 'students.view_attendance',
            'faculty.view', 'faculty.view_schedule', 'faculty.view_evaluations',
            'courses.view', 'courses.view_enrollments',
            'enrollment.view',
            'grades.view', 'grades.approve',
            'attendance.view', 'attendance.generate_reports',
            'examinations.view',
            'reports.view_academic', 'reports.generate_custom', 'reports.export',
        ]);

        // Department Head
        $deptHead = Role::where('slug', 'department-head')->first();
        $deptHead->syncPermissions([
            'dashboard.view',
            'students.view', 'students.view_grades', 'students.view_attendance',
            'faculty.view', 'faculty.assign_courses', 'faculty.view_schedule', 'faculty.manage_schedule',
            'courses.view', 'courses.create', 'courses.update', 'courses.assign_faculty',
            'courses.manage_prerequisites', 'courses.manage_sections', 'courses.view_enrollments',
            'enrollment.view',
            'grades.view',
            'attendance.view', 'attendance.generate_reports',
            'examinations.view', 'examinations.schedule_exams',
            'reports.view_academic', 'reports.export',
        ]);

        // Faculty
        $faculty = Role::where('slug', 'faculty')->first();
        $faculty->syncPermissions([
            'dashboard.view',
            'students.view', 'students.view_grades', 'students.update_grades',
            'students.view_attendance', 'students.update_attendance',
            'courses.view', 'courses.view_enrollments',
            'grades.view', 'grades.create', 'grades.update',
            'attendance.view', 'attendance.create', 'attendance.update',
            'examinations.view',
            'library.view',
        ]);

        // Advisor
        $advisor = Role::where('slug', 'advisor')->first();
        $advisor->syncPermissions([
            'dashboard.view',
            'students.view', 'students.view_grades', 'students.view_attendance',
            'courses.view',
            'enrollment.view',
            'grades.view',
            'attendance.view',
        ]);

        // Staff
        $staff = Role::where('slug', 'staff')->first();
        $staff->syncPermissions([
            'dashboard.view',
            'students.view',
            'courses.view',
            'library.view', 'library.issue_books', 'library.return_books',
            'hostel.view',
            'transport.view',
        ]);

        // Student
        $student = Role::where('slug', 'student')->first();
        $student->syncPermissions([
            'dashboard.view',
            'courses.view',
            'enrollment.view',
            'grades.view',
            'attendance.view',
            'finance.view',
            'library.view',
            'hostel.view',
            'transport.view',
            'examinations.view',
        ]);

        // Parent/Guardian
        $parent = Role::where('slug', 'parent-guardian')->first();
        $parent->syncPermissions([
            'dashboard.view',
            'students.view',
            'grades.view',
            'attendance.view',
            'finance.view',
        ]);

        // Auditor - Read-only access to everything
        $auditor = Role::where('slug', 'auditor')->first();
        $viewPermissions = Permission::where('slug', 'like', '%.view%')
            ->orWhere('slug', 'like', '%.view_%')
            ->pluck('slug')
            ->toArray();
        $auditor->syncPermissions($viewPermissions);

        $this->command->info('Permissions assigned to all roles');
    }

    /**
     * Create a default super admin user
     */
    private function createSuperAdminUser(): void
    {
        // Check if super admin already exists
        $existingAdmin = User::where('email', 'admin@intellicampus.edu')->first();
        
        if (!$existingAdmin) {
            $superAdmin = User::create([
                'name' => 'Super Administrator',
                'email' => 'admin@intellicampus.edu',
                'username' => 'superadmin',
                'password' => Hash::make('Admin@123456'),
                'user_type' => 'admin',
                'status' => 'active',
                'first_name' => 'Super',
                'last_name' => 'Administrator',
                'email_verified_at' => now(),
                'must_change_password' => true, // Force password change on first login
            ]);

            // Assign super administrator role
            $superAdminRole = Role::where('slug', 'super-administrator')->first();
            $superAdmin->assignRole($superAdminRole);

            $this->command->info('Super Admin User created:');
            $this->command->info('Email: admin@intellicampus.edu');
            $this->command->info('Username: superadmin');
            $this->command->info('Password: Admin@123456');
            $this->command->warn('Please change this password after first login!');
        } else {
            $this->command->info('Super Admin user already exists');
        }
    }
}