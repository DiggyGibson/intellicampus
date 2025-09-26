<?php
// File: C:\IntelliCampus\app\Http\Controllers\PermissionManagementController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PermissionManagementController extends Controller
{
    /**
     * Module configuration - defines all modules and their standard permissions
     * This is the central registry for all module permissions in the system
     */
    protected $moduleConfigurations = [
        'users' => [
            'name' => 'User Management',
            'icon' => 'fas fa-users',
            'color' => 'blue',
            'permissions' => [
                'view' => ['display' => 'View Users', 'description' => 'Can view user list and details'],
                'create' => ['display' => 'Create Users', 'description' => 'Can create new users'],
                'edit' => ['display' => 'Edit Users', 'description' => 'Can edit user information'],
                'delete' => ['display' => 'Delete Users', 'description' => 'Can delete users'],
                'manage_roles' => ['display' => 'Manage User Roles', 'description' => 'Can assign and remove roles'],
                'manage_permissions' => ['display' => 'Manage User Permissions', 'description' => 'Can assign direct permissions'],
                'import' => ['display' => 'Import Users', 'description' => 'Can bulk import users'],
                'export' => ['display' => 'Export Users', 'description' => 'Can export user data'],
                'impersonate' => ['display' => 'Impersonate Users', 'description' => 'Can login as another user'],
                'view_activity' => ['display' => 'View User Activity', 'description' => 'Can view user activity logs'],
            ]
        ],
        'students' => [
            'name' => 'Student Management',
            'icon' => 'fas fa-user-graduate',
            'color' => 'purple',
            'permissions' => [
                'view' => ['display' => 'View Students', 'description' => 'Can view student records'],
                'view_own' => ['display' => 'View Own Record', 'description' => 'Can view own student record'],
                'create' => ['display' => 'Create Students', 'description' => 'Can create student records'],
                'edit' => ['display' => 'Edit Students', 'description' => 'Can edit student information'],
                'delete' => ['display' => 'Delete Students', 'description' => 'Can delete student records'],
                'view_grades' => ['display' => 'View Student Grades', 'description' => 'Can view student grades'],
                'update_grades' => ['display' => 'Update Student Grades', 'description' => 'Can modify student grades'],
                'view_attendance' => ['display' => 'View Attendance', 'description' => 'Can view attendance records'],
                'update_attendance' => ['display' => 'Update Attendance', 'description' => 'Can modify attendance'],
                'manage_enrollment' => ['display' => 'Manage Enrollment', 'description' => 'Can manage student enrollment'],
                'import' => ['display' => 'Import Students', 'description' => 'Can bulk import students'],
                'export' => ['display' => 'Export Students', 'description' => 'Can export student data'],
            ]
        ],
        'courses' => [
            'name' => 'Course Management',
            'icon' => 'fas fa-book',
            'color' => 'green',
            'permissions' => [
                'view' => ['display' => 'View Courses', 'description' => 'Can view course catalog'],
                'create' => ['display' => 'Create Courses', 'description' => 'Can create new courses'],
                'edit' => ['display' => 'Edit Courses', 'description' => 'Can edit course information'],
                'delete' => ['display' => 'Delete Courses', 'description' => 'Can delete courses'],
                'manage_sections' => ['display' => 'Manage Sections', 'description' => 'Can manage course sections'],
                'manage_prerequisites' => ['display' => 'Manage Prerequisites', 'description' => 'Can set prerequisites'],
                'assign_faculty' => ['display' => 'Assign Faculty', 'description' => 'Can assign instructors'],
                'view_enrollments' => ['display' => 'View Enrollments', 'description' => 'Can view course enrollments'],
                'manage_syllabus' => ['display' => 'Manage Syllabus', 'description' => 'Can upload and manage syllabus'],
                'schedule_classes' => ['display' => 'Schedule Classes', 'description' => 'Can schedule class times'],
            ]
        ],
        'faculty' => [
            'name' => 'Faculty Management',
            'icon' => 'fas fa-chalkboard-teacher',
            'color' => 'yellow',
            'permissions' => [
                'view' => ['display' => 'View Faculty', 'description' => 'Can view faculty list'],
                'create' => ['display' => 'Create Faculty', 'description' => 'Can create faculty records'],
                'edit' => ['display' => 'Edit Faculty', 'description' => 'Can edit faculty information'],
                'delete' => ['display' => 'Delete Faculty', 'description' => 'Can delete faculty records'],
                'view_courses' => ['display' => 'View Assigned Courses', 'description' => 'Can view assigned courses'],
                'manage_roster' => ['display' => 'Manage Class Roster', 'description' => 'Can manage class rosters'],
                'take_attendance' => ['display' => 'Take Attendance', 'description' => 'Can record attendance'],
                'manage_gradebook' => ['display' => 'Manage Gradebook', 'description' => 'Can manage gradebook'],
                'view_schedule' => ['display' => 'View Faculty Schedule', 'description' => 'Can view teaching schedule'],
                'manage_office_hours' => ['display' => 'Manage Office Hours', 'description' => 'Can set office hours'],
            ]
        ],
        'finance' => [
            'name' => 'Financial Management',
            'icon' => 'fas fa-dollar-sign',
            'color' => 'green',
            'permissions' => [
                'view' => ['display' => 'View Finance', 'description' => 'Can view financial information'],
                'view_own_account' => ['display' => 'View Own Account', 'description' => 'Can view own financial account'],
                'view_all_accounts' => ['display' => 'View All Accounts', 'description' => 'Can view all financial accounts'],
                'make_payment' => ['display' => 'Make Payment', 'description' => 'Can make payments'],
                'process_payments' => ['display' => 'Process Payments', 'description' => 'Can process and approve payments'],
                'manage_fees' => ['display' => 'Manage Fee Structure', 'description' => 'Can manage fees'],
                'generate_invoices' => ['display' => 'Generate Invoices', 'description' => 'Can generate invoices'],
                'issue_refunds' => ['display' => 'Issue Refunds', 'description' => 'Can process refunds'],
                'waive_fees' => ['display' => 'Waive Fees', 'description' => 'Can waive or adjust fees'],
                'manage_scholarships' => ['display' => 'Manage Scholarships', 'description' => 'Can manage scholarships'],
                'view_reports' => ['display' => 'View Financial Reports', 'description' => 'Can view reports'],
                'export_data' => ['display' => 'Export Financial Data', 'description' => 'Can export data'],
                'reconcile' => ['display' => 'Reconcile Accounts', 'description' => 'Can reconcile accounts'],
                'audit' => ['display' => 'Audit Finances', 'description' => 'Can perform audits'],
                'manage_holds' => ['display' => 'Manage Financial Holds', 'description' => 'Can manage holds'],
            ]
        ],
        'registration' => [
            'name' => 'Registration System',
            'icon' => 'fas fa-clipboard-list',
            'color' => 'indigo',
            'permissions' => [
                'view' => ['display' => 'View Registration', 'description' => 'Can view registration information'],
                'view_own' => ['display' => 'View Own Registration', 'description' => 'Can view own registrations'],
                'register' => ['display' => 'Register for Courses', 'description' => 'Can register for courses'],
                'drop' => ['display' => 'Drop Courses', 'description' => 'Can drop courses'],
                'view_all' => ['display' => 'View All Registrations', 'description' => 'Can view all registrations'],
                'override' => ['display' => 'Override Registration', 'description' => 'Can override restrictions'],
                'manage_holds' => ['display' => 'Manage Registration Holds', 'description' => 'Can manage holds'],
                'manage_waitlist' => ['display' => 'Manage Waitlist', 'description' => 'Can manage waitlists'],
                'approve_special' => ['display' => 'Approve Special Registration', 'description' => 'Can approve special cases'],
            ]
        ],
        'grades' => [
            'name' => 'Grade Management',
            'icon' => 'fas fa-graduation-cap',
            'color' => 'red',
            'permissions' => [
                'view_own' => ['display' => 'View Own Grades', 'description' => 'Can view own grades'],
                'view_all' => ['display' => 'View All Grades', 'description' => 'Can view all student grades'],
                'enter' => ['display' => 'Enter Grades', 'description' => 'Can enter grades'],
                'edit' => ['display' => 'Edit Grades', 'description' => 'Can edit existing grades'],
                'approve' => ['display' => 'Approve Grades', 'description' => 'Can approve grade submissions'],
                'export' => ['display' => 'Export Grades', 'description' => 'Can export grade data'],
                'calculate_gpa' => ['display' => 'Calculate GPA', 'description' => 'Can calculate GPAs'],
                'generate_transcripts' => ['display' => 'Generate Transcripts', 'description' => 'Can generate transcripts'],
                'manage_scales' => ['display' => 'Manage Grade Scales', 'description' => 'Can manage grading scales'],
            ]
        ],
        'admissions' => [
            'name' => 'Admissions Management',
            'icon' => 'fas fa-university',
            'color' => 'teal',
            'permissions' => [
                'view' => ['display' => 'View Applications', 'description' => 'Can view admission applications'],
                'create' => ['display' => 'Create Application', 'description' => 'Can create applications'],
                'edit' => ['display' => 'Edit Applications', 'description' => 'Can edit applications'],
                'delete' => ['display' => 'Delete Applications', 'description' => 'Can delete applications'],
                'review' => ['display' => 'Review Applications', 'description' => 'Can review applications'],
                'approve' => ['display' => 'Approve Applications', 'description' => 'Can approve/reject applications'],
                'manage_requirements' => ['display' => 'Manage Requirements', 'description' => 'Can manage admission requirements'],
                'view_statistics' => ['display' => 'View Admission Statistics', 'description' => 'Can view admission stats'],
                'communicate' => ['display' => 'Communicate with Applicants', 'description' => 'Can send communications'],
            ]
        ],
        'library' => [
            'name' => 'Library Management',
            'icon' => 'fas fa-book-open',
            'color' => 'brown',
            'permissions' => [
                'view' => ['display' => 'View Library Resources', 'description' => 'Can view library catalog'],
                'borrow' => ['display' => 'Borrow Items', 'description' => 'Can borrow library items'],
                'manage_catalog' => ['display' => 'Manage Catalog', 'description' => 'Can manage library catalog'],
                'manage_fines' => ['display' => 'Manage Fines', 'description' => 'Can manage library fines'],
                'reserve_items' => ['display' => 'Reserve Items', 'description' => 'Can reserve library items'],
                'manage_acquisitions' => ['display' => 'Manage Acquisitions', 'description' => 'Can manage new acquisitions'],
            ]
        ],
        'housing' => [
            'name' => 'Housing Management',
            'icon' => 'fas fa-home',
            'color' => 'orange',
            'permissions' => [
                'view' => ['display' => 'View Housing', 'description' => 'Can view housing information'],
                'apply' => ['display' => 'Apply for Housing', 'description' => 'Can apply for housing'],
                'manage_applications' => ['display' => 'Manage Applications', 'description' => 'Can manage housing applications'],
                'assign_rooms' => ['display' => 'Assign Rooms', 'description' => 'Can assign room allocations'],
                'manage_facilities' => ['display' => 'Manage Facilities', 'description' => 'Can manage housing facilities'],
                'handle_maintenance' => ['display' => 'Handle Maintenance', 'description' => 'Can handle maintenance requests'],
            ]
        ],
        'examinations' => [
            'name' => 'Examination Management',
            'icon' => 'fas fa-file-alt',
            'color' => 'pink',
            'permissions' => [
                'view' => ['display' => 'View Examinations', 'description' => 'Can view exam schedules'],
                'create' => ['display' => 'Create Examinations', 'description' => 'Can create exams'],
                'edit' => ['display' => 'Edit Examinations', 'description' => 'Can edit exam details'],
                'schedule' => ['display' => 'Schedule Exams', 'description' => 'Can schedule examinations'],
                'manage_venues' => ['display' => 'Manage Exam Venues', 'description' => 'Can manage exam venues'],
                'assign_invigilators' => ['display' => 'Assign Invigilators', 'description' => 'Can assign invigilators'],
                'process_results' => ['display' => 'Process Results', 'description' => 'Can process exam results'],
            ]
        ],
        'reports' => [
            'name' => 'Reports & Analytics',
            'icon' => 'fas fa-chart-bar',
            'color' => 'cyan',
            'permissions' => [
                'view_academic' => ['display' => 'View Academic Reports', 'description' => 'Can view academic reports'],
                'view_financial' => ['display' => 'View Financial Reports', 'description' => 'Can view financial reports'],
                'view_administrative' => ['display' => 'View Admin Reports', 'description' => 'Can view admin reports'],
                'generate_custom' => ['display' => 'Generate Custom Reports', 'description' => 'Can create custom reports'],
                'export' => ['display' => 'Export Reports', 'description' => 'Can export report data'],
                'schedule_reports' => ['display' => 'Schedule Reports', 'description' => 'Can schedule automated reports'],
            ]
        ],
        'settings' => [
            'name' => 'System Settings',
            'icon' => 'fas fa-cogs',
            'color' => 'gray',
            'permissions' => [
                'view' => ['display' => 'View Settings', 'description' => 'Can view system settings'],
                'update' => ['display' => 'Update Settings', 'description' => 'Can modify settings'],
                'manage_system' => ['display' => 'Manage System', 'description' => 'Can manage system configuration'],
                'manage_security' => ['display' => 'Manage Security', 'description' => 'Can manage security settings'],
                'manage_backup' => ['display' => 'Manage Backups', 'description' => 'Can manage system backups'],
                'manage_integration' => ['display' => 'Manage Integrations', 'description' => 'Can manage external integrations'],
            ]
        ],
        'communication' => [
            'name' => 'Communication System',
            'icon' => 'fas fa-envelope',
            'color' => 'blue',
            'permissions' => [
                'send_email' => ['display' => 'Send Emails', 'description' => 'Can send email communications'],
                'send_sms' => ['display' => 'Send SMS', 'description' => 'Can send SMS messages'],
                'send_broadcast' => ['display' => 'Send Broadcasts', 'description' => 'Can send broadcast messages'],
                'manage_templates' => ['display' => 'Manage Templates', 'description' => 'Can manage message templates'],
                'view_logs' => ['display' => 'View Communication Logs', 'description' => 'Can view communication history'],
            ]
        ],
    ];

    /**
     * Display the permission management dashboard
     */
    public function index()
    {
        $stats = $this->getSystemStats();
        $permissionsByModule = $this->getPermissionsByModule();
        $roles = $this->getRolesWithStats();
        $moduleConfig = $this->moduleConfigurations;
        $missingPermissions = $this->detectMissingPermissions();
        $orphanedPermissions = $this->detectOrphanedPermissions();
        
        return view('admin.permissions.index', compact(
            'stats', 
            'permissionsByModule', 
            'roles', 
            'moduleConfig',
            'missingPermissions',
            'orphanedPermissions'
        ));
    }

    /**
     * Display the permission matrix view
     */
    public function matrix()
    {
        $roles = Role::with(['permissions', 'users'])->orderBy('priority')->get();
        $permissions = Permission::orderBy('module')->orderBy('slug')->get();
        
        return view('admin.permissions.matrix', compact('roles', 'permissions'));
    }

    /**
     * Bulk update permissions from matrix view
     */
    public function bulkUpdate(Request $request)
    {
        $changes = $request->input('changes', []);
        $report = [
            'granted' => 0,
            'revoked' => 0,
            'errors' => []
        ];
        
        DB::beginTransaction();
        try {
            foreach ($changes as $change) {
                $role = Role::find($change['role_id']);
                $permission = Permission::find($change['permission_id']);
                
                if (!$role || !$permission) {
                    $report['errors'][] = "Invalid role or permission ID";
                    continue;
                }
                
                if ($change['action'] === 'grant') {
                    if (!$role->permissions->contains($permission)) {
                        $role->permissions()->attach($permission->id, [
                            'granted_at' => now(),
                            'granted_by' => auth()->id()
                        ]);
                        $report['granted']++;
                    }
                } else {
                    $role->permissions()->detach($permission->id);
                    $report['revoked']++;
                }
            }
            
            Cache::forget('permission_stats');
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Updated permissions: {$report['granted']} granted, {$report['revoked']} revoked",
                'report' => $report
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk permission update failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system statistics
     */
    private function getSystemStats()
    {
        return Cache::remember('permission_stats', 60, function() {
            $modules = Permission::select('module')
                ->distinct()
                ->pluck('module')
                ->filter()
                ->values();

            return [
                'total_permissions' => Permission::count(),
                'total_roles' => Role::count(),
                'total_users' => User::count(),
                'modules' => $modules,
                'modules_count' => $modules->count(),
                'unassigned_permissions' => Permission::doesntHave('roles')->count(),
                'system_permissions' => Permission::where('is_system', true)->count(),
                'custom_permissions' => Permission::where('is_system', false)->count(),
            ];
        });
    }

    /**
     * Get permissions grouped by module
     */
    private function getPermissionsByModule()
    {
        return Permission::all()
            ->groupBy('module')
            ->map(function ($permissions, $module) {
                return [
                    'module' => $module,
                    'config' => $this->moduleConfigurations[$module] ?? null,
                    'permissions' => $permissions,
                    'count' => $permissions->count()
                ];
            });
    }

    /**
     * Get roles with statistics
     */
    private function getRolesWithStats()
    {
        return Role::withCount(['permissions', 'users'])
            ->orderBy('priority')
            ->get()
            ->map(function ($role) {
                $role->permission_coverage = $this->calculatePermissionCoverage($role);
                return $role;
            });
    }

    /**
     * Calculate permission coverage percentage for a role
     */
    private function calculatePermissionCoverage($role)
    {
        $totalSystemPermissions = Permission::where('is_system', true)->count();
        if ($totalSystemPermissions === 0) return 0;
        
        $rolePermissions = $role->permissions()->where('is_system', true)->count();
        return round(($rolePermissions / $totalSystemPermissions) * 100, 1);
    }

    /**
     * Detect missing permissions based on module configuration
     */
    private function detectMissingPermissions()
    {
        $missing = [];
        
        foreach ($this->moduleConfigurations as $module => $config) {
            foreach ($config['permissions'] as $action => $details) {
                $permissionSlug = "{$module}.{$action}";
                if (!Permission::where('slug', $permissionSlug)->exists()) {
                    $missing[$module][] = [
                        'slug' => $permissionSlug,
                        'display_name' => $details['display'],
                        'description' => $details['description']
                    ];
                }
            }
        }
        
        return $missing;
    }

    /**
     * Detect orphaned permissions (not in configuration)
     */
    private function detectOrphanedPermissions()
    {
        $configured = [];
        foreach ($this->moduleConfigurations as $module => $config) {
            foreach ($config['permissions'] as $action => $details) {
                $configured[] = "{$module}.{$action}";
            }
        }
        
        return Permission::whereNotIn('slug', $configured)
            ->where('is_system', true)
            ->get();
    }

    /**
     * Install all missing permissions for a module
     */
    public function installModulePermissions(Request $request, $module)
    {
        if (!isset($this->moduleConfigurations[$module])) {
            return response()->json(['error' => 'Module not found'], 404);
        }

        DB::beginTransaction();
        try {
            $config = $this->moduleConfigurations[$module];
            $created = 0;
            $updated = 0;
            
            foreach ($config['permissions'] as $action => $details) {
                $permissionSlug = "{$module}.{$action}";
                
                $permission = Permission::firstOrNew(['slug' => $permissionSlug]);
                $isNew = !$permission->exists;
                
                $permission->name = $details['display'];
                $permission->display_name = $details['display'];
                $permission->description = $details['description'];
                $permission->module = $module;
                $permission->is_system = true;
                $permission->save();
                
                $isNew ? $created++ : $updated++;
            }
            
            // Auto-assign to appropriate roles
            $this->autoAssignModulePermissions($module);
            
            // Clear cache
            Cache::forget('permission_stats');
            
            DB::commit();
            
            Log::info("Module permissions installed", [
                'module' => $module,
                'created' => $created,
                'updated' => $updated,
                'user' => auth()->id()
            ]);
            
            return redirect()->back()->with('success', 
                "Module '{$config['name']}' permissions installed: {$created} created, {$updated} updated.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to install module permissions", [
                'module' => $module,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Failed to install module permissions: ' . $e->getMessage());
        }
    }

    /**
     * Auto-assign module permissions to appropriate roles
     */
    private function autoAssignModulePermissions($module)
    {
        // Get all permissions for this module
        $modulePermissions = Permission::where('module', $module)->get();
        
        // Super Admin gets everything
        $superAdmin = Role::where('slug', 'super-administrator')->first();
        if ($superAdmin) {
            $superAdmin->permissions()->syncWithoutDetaching($modulePermissions->pluck('id'));
        }
        
        // Module-specific role assignments
        $roleAssignments = $this->getModuleRoleAssignments($module);
        
        foreach ($roleAssignments as $roleSlug => $permissionPatterns) {
            $role = Role::where('slug', $roleSlug)->first();
            if (!$role) continue;
            
            $permissions = $modulePermissions->filter(function ($permission) use ($permissionPatterns) {
                foreach ($permissionPatterns as $pattern) {
                    if (str_contains($permission->slug, $pattern)) {
                        return true;
                    }
                }
                return false;
            });
            
            $role->permissions()->syncWithoutDetaching($permissions->pluck('id'));
        }
    }

    /**
     * Define which roles should get which permissions for each module
     */
    private function getModuleRoleAssignments($module)
    {
        $assignments = [
            'finance' => [
                'financial-administrator' => ['finance.'],
                'bursar' => ['finance.'],
                'student' => ['finance.view_own', 'finance.make_payment'],
                'parent-guardian' => ['finance.view_own', 'finance.make_payment'],
            ],
            'students' => [
                'registrar' => ['students.'],
                'academic-administrator' => ['students.'],
                'faculty' => ['students.view', 'students.view_grades', 'students.view_attendance'],
                'student' => ['students.view_own'],
            ],
            'courses' => [
                'academic-administrator' => ['courses.'],
                'department-head' => ['courses.'],
                'faculty' => ['courses.view', 'courses.view_enrollments'],
                'student' => ['courses.view'],
            ],
            'grades' => [
                'registrar' => ['grades.'],
                'faculty' => ['grades.enter', 'grades.edit', 'grades.view_all'],
                'student' => ['grades.view_own'],
            ],
            'registration' => [
                'registrar' => ['registration.'],
                'student' => ['registration.view_own', 'registration.register', 'registration.drop'],
            ],
            // Add more module-specific assignments as needed
        ];
        
        return $assignments[$module] ?? [];
    }

    /**
     * Sync all system permissions
     */
    public function syncAllPermissions()
    {
        DB::beginTransaction();
        try {
            $report = [
                'created' => 0,
                'updated' => 0,
                'assigned' => 0
            ];
            
            foreach ($this->moduleConfigurations as $module => $config) {
                foreach ($config['permissions'] as $action => $details) {
                    $permissionSlug = "{$module}.{$action}";
                    
                    $permission = Permission::firstOrNew(['slug' => $permissionSlug]);
                    $isNew = !$permission->exists;
                    
                    $permission->name = $details['display'];
                    $permission->display_name = $details['display'];
                    $permission->description = $details['description'];
                    $permission->module = $module;
                    $permission->is_system = true;
                    $permission->save();
                    
                    $isNew ? $report['created']++ : $report['updated']++;
                }
                
                // Auto-assign to roles
                $this->autoAssignModulePermissions($module);
                $report['assigned']++;
            }
            
            // Clear cache
            Cache::forget('permission_stats');
            
            DB::commit();
            
            Log::info("System permissions synced", $report);
            
            return redirect()->back()->with('success', 
                "Permissions synced: {$report['created']} created, {$report['updated']} updated, {$report['assigned']} modules assigned to roles.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to sync permissions", ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to sync permissions: ' . $e->getMessage());
        }
    }

    /**
     * Create custom permission
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|unique:permissions|regex:/^[a-z]+\.[a-z_]+$/',
            'name' => 'required|string|max:100',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'module' => 'required|string|max:50'
        ]);
        
        try {
            $validated['is_system'] = false; // Custom permissions are not system
            Permission::create($validated);
            
            Cache::forget('permission_stats');
            
            return redirect()->back()->with('success', 'Permission created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create permission: ' . $e->getMessage());
        }
    }

    /**
     * Update permission details
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255'
        ]);
        
        try {
            $permission->update($validated);
            Cache::forget('permission_stats');
            
            return response()->json(['success' => true, 'message' => 'Permission updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete non-system permission
     */
    public function destroy(Permission $permission)
    {
        if ($permission->is_system) {
            return response()->json(['success' => false, 'message' => 'Cannot delete system permission'], 403);
        }
        
        try {
            $permission->delete();
            Cache::forget('permission_stats');
            
            return response()->json(['success' => true, 'message' => 'Permission deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Toggle permission for role
     */
    public function toggleRolePermission(Request $request, Role $role, Permission $permission)
    {
        try {
            if ($role->permissions->contains($permission)) {
                $role->permissions()->detach($permission);
                $action = 'revoked';
            } else {
                $role->permissions()->attach($permission, [
                    'granted_at' => now(),
                    'granted_by' => auth()->id()
                ]);
                $action = 'granted';
            }
            
            Cache::forget('permission_stats');
            
            return response()->json([
                'success' => true,
                'message' => "Permission {$action} successfully",
                'action' => $action
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Bulk update role permissions
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);
        
        try {
            $role->permissions()->sync($validated['permissions'] ?? []);
            Cache::forget('permission_stats');
            
            return redirect()->back()->with('success', "Permissions updated for role: {$role->name}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update role permissions: ' . $e->getMessage());
        }
    }

    /**
     * Export permissions configuration
     */
    public function export()
    {
        $data = [
            'metadata' => [
                'exported_at' => now()->toIso8601String(),
                'system' => config('app.name'),
                'version' => '1.0',
                'total_permissions' => Permission::count(),
                'total_roles' => Role::count()
            ],
            'modules' => $this->moduleConfigurations,
            'permissions' => Permission::with('roles')->get()->map(function ($permission) {
                return [
                    'slug' => $permission->slug,
                    'name' => $permission->name,
                    'display_name' => $permission->display_name,
                    'description' => $permission->description,
                    'module' => $permission->module,
                    'is_system' => $permission->is_system,
                    'roles' => $permission->roles->pluck('slug')
                ];
            }),
            'roles' => Role::with('permissions')->get()->map(function ($role) {
                return [
                    'slug' => $role->slug,
                    'name' => $role->name,
                    'description' => $role->description,
                    'priority' => $role->priority,
                    'is_system' => $role->is_system,
                    'permissions' => $role->permissions->pluck('slug')
                ];
            })
        ];
        
        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="intellicampus-permissions-' . date('Y-m-d') . '.json"'
        ]);
    }

    /**
     * Import permissions configuration
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:json|max:2048'
        ]);
        
        try {
            $content = file_get_contents($request->file('file')->getRealPath());
            $data = json_decode($content, true);
            
            if (!isset($data['permissions']) || !isset($data['roles'])) {
                throw new \Exception('Invalid import file format');
            }
            
            DB::beginTransaction();
            
            $report = [
                'permissions_created' => 0,
                'permissions_updated' => 0,
                'roles_created' => 0,
                'roles_updated' => 0
            ];
            
            // Import permissions
            foreach ($data['permissions'] as $permData) {
                $permission = Permission::firstOrNew(['slug' => $permData['slug']]);
                $isNew = !$permission->exists;
                
                $permission->fill([
                    'name' => $permData['name'],
                    'display_name' => $permData['display_name'],
                    'description' => $permData['description'],
                    'module' => $permData['module'],
                    'is_system' => $permData['is_system'] ?? false
                ])->save();
                
                $isNew ? $report['permissions_created']++ : $report['permissions_updated']++;
            }
            
            // Import roles
            foreach ($data['roles'] as $roleData) {
                $role = Role::firstOrNew(['slug' => $roleData['slug']]);
                $isNew = !$role->exists;
                
                $role->fill([
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                    'priority' => $roleData['priority'] ?? 99,
                    'is_system' => $roleData['is_system'] ?? false
                ])->save();
                
                // Sync permissions
                $permissions = Permission::whereIn('slug', $roleData['permissions'])->pluck('id');
                $role->permissions()->sync($permissions);
                
                $isNew ? $report['roles_created']++ : $report['roles_updated']++;
            }
            
            Cache::forget('permission_stats');
            DB::commit();
            
            return redirect()->back()->with('success', 
                "Import successful: {$report['permissions_created']} permissions created, " .
                "{$report['permissions_updated']} updated, {$report['roles_created']} roles created, " .
                "{$report['roles_updated']} updated.");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Health check for permission system
     */
    public function healthCheck()
    {
        $issues = [];
        
        // Check for roles without permissions
        $rolesWithoutPermissions = Role::doesntHave('permissions')->get();
        if ($rolesWithoutPermissions->count() > 0) {
            $issues[] = [
                'type' => 'warning',
                'message' => "{$rolesWithoutPermissions->count()} role(s) have no permissions assigned",
                'details' => $rolesWithoutPermissions->pluck('name')
            ];
        }
        
        // Check for orphaned permissions
        $orphaned = $this->detectOrphanedPermissions();
        if ($orphaned->count() > 0) {
            $issues[] = [
                'type' => 'info',
                'message' => "{$orphaned->count()} permission(s) not in system configuration",
                'details' => $orphaned->pluck('slug')
            ];
        }
        
        // Check for missing permissions
        $missing = $this->detectMissingPermissions();
        if (count($missing) > 0) {
            $totalMissing = array_sum(array_map('count', $missing));
            $issues[] = [
                'type' => 'warning',
                'message' => "{$totalMissing} permission(s) missing from database",
                'details' => array_keys($missing)
            ];
        }
        
        // Check super admin
        $superAdmin = Role::where('slug', 'super-administrator')->first();
        if (!$superAdmin) {
            $issues[] = [
                'type' => 'error',
                'message' => 'Super Administrator role not found',
                'details' => []
            ];
        } else {
            $totalPermissions = Permission::count();
            $superAdminPermissions = $superAdmin->permissions->count();
            if ($superAdminPermissions < $totalPermissions) {
                $issues[] = [
                    'type' => 'warning',
                    'message' => "Super Admin missing " . ($totalPermissions - $superAdminPermissions) . " permissions",
                    'details' => []
                ];
            }
        }
        
        return response()->json([
            'healthy' => count(array_filter($issues, fn($i) => $i['type'] === 'error')) === 0,
            'issues' => $issues,
            'stats' => $this->getSystemStats()
        ]);
    }
}