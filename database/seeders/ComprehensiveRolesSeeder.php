<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Schema;

class ComprehensiveRolesSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            // System Roles
            [
                'name' => 'Super Administrator',
                'slug' => 'super-administrator',
                'description' => 'Full system access with all permissions',
                'is_system' => true,
                'priority' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'System Administrator',
                'slug' => 'system-administrator',
                'description' => 'System administration access',
                'is_system' => true,
                'priority' => 2,
                'is_active' => true,
            ],
            
            // Academic Administration
            [
                'name' => 'Academic Administrator',
                'slug' => 'academic-administrator',
                'description' => 'Academic affairs administration',
                'is_system' => false,
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Registrar',
                'slug' => 'registrar',
                'description' => 'Academic records and registration management',
                'is_system' => false,
                'priority' => 11,
                'is_active' => true,
            ],
            [
                'name' => 'Dean',
                'slug' => 'dean',
                'description' => 'College/School dean',
                'is_system' => false,
                'priority' => 12,
                'is_active' => true,
            ],
            [
                'name' => 'Department Head',
                'slug' => 'department-head',
                'description' => 'Department leadership',
                'is_system' => false,
                'priority' => 13,
                'is_active' => true,
            ],
            
            // Faculty & Staff
            [
                'name' => 'Faculty',
                'slug' => 'faculty',
                'description' => 'Teaching faculty member',
                'is_system' => false,
                'priority' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Advisor',
                'slug' => 'advisor',
                'description' => 'Academic advisor',
                'is_system' => false,
                'priority' => 21,
                'is_active' => true,
            ],
            [
                'name' => 'Staff',
                'slug' => 'staff',
                'description' => 'Administrative staff member',
                'is_system' => false,
                'priority' => 25,
                'is_active' => true,
            ],
            
            // Admissions Roles
            [
                'name' => 'Admissions Director',
                'slug' => 'admissions-director',
                'description' => 'Head of admissions department',
                'is_system' => false,
                'priority' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Admissions Officer',
                'slug' => 'admissions-officer',
                'description' => 'Admissions processing and review',
                'is_system' => false,
                'priority' => 31,
                'is_active' => true,
            ],
            [
                'name' => 'Applicant',
                'slug' => 'applicant',
                'description' => 'Admission applicant - can submit and track applications',
                'is_system' => false,
                'priority' => 40,
                'is_active' => true,
            ],
            
            // Student Roles
            [
                'name' => 'Student',
                'slug' => 'student',
                'description' => 'Enrolled student',
                'is_system' => false,
                'priority' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Alumni',
                'slug' => 'alumni',
                'description' => 'Graduated student',
                'is_system' => false,
                'priority' => 51,
                'is_active' => true,
            ],
            
            // External Roles
            [
                'name' => 'Parent/Guardian',
                'slug' => 'parent-guardian',
                'description' => 'Parent or guardian of student',
                'is_system' => false,
                'priority' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'Guest',
                'slug' => 'guest',
                'description' => 'Guest user with limited access',
                'is_system' => false,
                'priority' => 70,
                'is_active' => true,
            ],
            
            // Other Roles
            [
                'name' => 'Financial Administrator',
                'slug' => 'financial-administrator',
                'description' => 'Financial affairs management',
                'is_system' => false,
                'priority' => 80,
                'is_active' => true,
            ],
            [
                'name' => 'Auditor',
                'slug' => 'auditor',
                'description' => 'System and records auditing',
                'is_system' => false,
                'priority' => 90,
                'is_active' => true,
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
            
            $this->command->info("Ensured role: {$role->name}");
        }

        // Set up basic permissions for applicant role
        $this->setupApplicantPermissions();
        
        $this->command->info('All roles have been ensured with proper configuration.');
    }
    
    private function setupApplicantPermissions()
    {
        $applicantRole = Role::where('slug', 'applicant')->first();
        
        if (!$applicantRole) {
            return;
        }
        
        // Define applicant permissions
        $applicantPermissions = [
            'application.create',
            'application.view.own',
            'application.edit.own',
            'application.submit.own',
            'application.withdraw.own',
            'application.track.own',
            'document.upload.own',
            'document.view.own',
            'payment.make.own',
            'exam.register',
            'exam.view.own',
        ];
        
        foreach ($applicantPermissions as $permissionSlug) {
            // Build permission data based on what columns exist
            $permissionData = [
                'name' => ucwords(str_replace('.', ' ', $permissionSlug)),
                'description' => 'Applicant permission: ' . $permissionSlug,
                'module' => explode('.', $permissionSlug)[0],
            ];
            
            // Only add is_active if column exists
            if (Schema::hasColumn('permissions', 'is_active')) {
                $permissionData['is_active'] = true;
            }
            
            $permission = Permission::firstOrCreate(
                ['slug' => $permissionSlug],
                $permissionData
            );
            
            // Attach permission to role if not already attached
            if (!$applicantRole->permissions()->where('permission_id', $permission->id)->exists()) {
                $applicantRole->permissions()->attach($permission->id);
            }
        }
        
        $this->command->info('Applicant permissions configured.');
    }
}