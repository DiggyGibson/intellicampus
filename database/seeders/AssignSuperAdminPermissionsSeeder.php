<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class AssignSuperAdminPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get super-administrator role
        $superAdminRole = Role::where('slug', 'super-administrator')->first();
        
        if (!$superAdminRole) {
            $this->command->error('Super Administrator role not found!');
            return;
        }
        
        // Get all permissions
        $allPermissions = Permission::all();
        
        if ($allPermissions->isEmpty()) {
            $this->command->info('No permissions found. Creating basic permissions...');
            $this->createBasicPermissions();
            $allPermissions = Permission::all();
        }
        
        // Clear existing permissions for super-admin
        DB::table('permission_role')->where('role_id', $superAdminRole->id)->delete();
        
        // Assign all permissions to super-admin
        foreach ($allPermissions as $permission) {
            DB::table('permission_role')->insert([
                'permission_id' => $permission->id,
                'role_id' => $superAdminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $this->command->info("Assigned {$allPermissions->count()} permissions to Super Administrator role.");
        
        // Also assign to system-administrator
        $sysAdminRole = Role::where('slug', 'system-administrator')->first();
        if ($sysAdminRole) {
            DB::table('permission_role')->where('role_id', $sysAdminRole->id)->delete();
            
            foreach ($allPermissions as $permission) {
                DB::table('permission_role')->insert([
                    'permission_id' => $permission->id,
                    'role_id' => $sysAdminRole->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            $this->command->info("Also assigned permissions to System Administrator role.");
        }
    }
    
    /**
     * Create basic permissions if none exist
     */
    private function createBasicPermissions(): void
    {
        $permissions = [
            // System permissions
            ['name' => 'System Full Access', 'slug' => 'system.full-access', 'description' => 'Full system access'],
            ['name' => 'System Configure', 'slug' => 'system.configure', 'description' => 'Configure system settings'],
            ['name' => 'System Monitor', 'slug' => 'system.monitor', 'description' => 'Monitor system health'],
            ['name' => 'System Backup', 'slug' => 'system.backup', 'description' => 'Manage backups'],
            ['name' => 'System View Audit', 'slug' => 'system.view-audit', 'description' => 'View audit logs'],
            ['name' => 'System Manage Modules', 'slug' => 'system.manage-modules', 'description' => 'Manage system modules'],
            
            // User management
            ['name' => 'Users Manage', 'slug' => 'users.manage', 'description' => 'Manage all users'],
            ['name' => 'Users Create', 'slug' => 'users.create', 'description' => 'Create new users'],
            ['name' => 'Users Edit', 'slug' => 'users.edit', 'description' => 'Edit user details'],
            ['name' => 'Users Delete', 'slug' => 'users.delete', 'description' => 'Delete users'],
            ['name' => 'Users View', 'slug' => 'users.view', 'description' => 'View user details'],
            
            // Role management
            ['name' => 'Roles Manage', 'slug' => 'roles.manage', 'description' => 'Manage roles'],
            ['name' => 'Permissions Manage', 'slug' => 'permissions.manage', 'description' => 'Manage permissions'],
            
            // Academic management
            ['name' => 'Courses Manage', 'slug' => 'courses.manage', 'description' => 'Manage courses'],
            ['name' => 'Programs Manage', 'slug' => 'programs.manage', 'description' => 'Manage academic programs'],
            ['name' => 'Grades Manage', 'slug' => 'grades.manage', 'description' => 'Manage grades'],
            ['name' => 'Grades Approve Changes', 'slug' => 'grades.approve-changes', 'description' => 'Approve grade changes'],
            
            // Student management
            ['name' => 'Students Manage', 'slug' => 'students.manage', 'description' => 'Manage students'],
            ['name' => 'Students View', 'slug' => 'students.view', 'description' => 'View student records'],
            
            // Faculty management
            ['name' => 'Faculty Manage', 'slug' => 'faculty.manage', 'description' => 'Manage faculty'],
            ['name' => 'Faculty View', 'slug' => 'faculty.view', 'description' => 'View faculty details'],
            
            // Department management
            ['name' => 'Department Manage Faculty', 'slug' => 'department.manage-faculty', 'description' => 'Manage department faculty'],
            ['name' => 'Department Manage Curriculum', 'slug' => 'department.manage-curriculum', 'description' => 'Manage curriculum'],
            ['name' => 'Department Manage Schedule', 'slug' => 'department.manage-schedule', 'description' => 'Manage course schedules'],
            
            // Registrar
            ['name' => 'Registrar Manage Records', 'slug' => 'registrar.manage-records', 'description' => 'Manage student records'],
            ['name' => 'Registrar Verify Enrollment', 'slug' => 'registrar.verify-enrollment', 'description' => 'Verify enrollments'],
            ['name' => 'Transcripts Manage', 'slug' => 'transcripts.manage', 'description' => 'Manage transcripts'],
            
            // Financial
            ['name' => 'Financial Manage', 'slug' => 'financial.manage', 'description' => 'Manage financial system'],
            ['name' => 'Financial View Reports', 'slug' => 'financial.view-reports', 'description' => 'View financial reports'],
            
            // Reports
            ['name' => 'Reports View', 'slug' => 'reports.view', 'description' => 'View reports'],
            ['name' => 'Reports Generate', 'slug' => 'reports.generate', 'description' => 'Generate reports'],
            
            // Admissions
            ['name' => 'Admissions Manage', 'slug' => 'admissions.manage', 'description' => 'Manage admissions'],
            ['name' => 'Admissions Apply', 'slug' => 'admissions.apply', 'description' => 'Apply for admission'],
            
            // Advisor
            ['name' => 'Advisor View Students', 'slug' => 'advisor.view-students', 'description' => 'View assigned students'],
            
            // System search
            ['name' => 'System Search', 'slug' => 'system.search', 'description' => 'Use system-wide search'],
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                [
                    'name' => $permission['name'],
                    'description' => $permission['description']
                ]
            );
        }
        
        $this->command->info('Created ' . count($permissions) . ' basic permissions.');
    }
}