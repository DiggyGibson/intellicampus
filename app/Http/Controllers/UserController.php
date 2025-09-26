<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $search = $request->get('search');
        $userType = $request->get('user_type');
        $status = $request->get('status');
        $role = $request->get('role');
        $department = $request->get('department');

        // Build query with filters
        $query = User::with(['roles']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($userType) {
            $query->where('user_type', $userType);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($role) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('roles.id', $role);
            });
        }

        if ($department) {
            $query->where('department', $department);
        }

        // Get users with pagination
        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get data for filters
        $roles = Role::orderBy('priority')->get();
        $departments = User::whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->sort();

        // Log activity
        UserActivityLog::log('view', 'Viewed user list');

        return view('users.index', compact('users', 'roles', 'departments'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::where('is_active', true)->orderBy('priority')->get();
        $departments = [
            'Administration',
            'Academic Affairs',
            'Computer Science',
            'Engineering',
            'Mathematics',
            'Physics',
            'Chemistry',
            'Biology',
            'Finance Department',
            'Student Affairs',
            'Library',
            'IT Department',
            'Human Resources',
            'Registrar Office',
            'Quality Assurance'
        ];
        
        return view('users.create', compact('roles', 'departments'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        // Validation
        $validated = $request->validate([
            // Basic Information
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'nullable|string|unique:users,username',
            'password' => 'required|string|min:8|confirmed',
            
            // User Type and Status
            'user_type' => 'required|in:student,faculty,staff,admin,parent',
            'status' => 'required|in:active,inactive,suspended,pending',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            
            // Personal Information
            'title' => 'nullable|string|max:20',
            'gender' => 'nullable|in:Male,Female,Other',
            'date_of_birth' => 'nullable|date|before:today',
            'nationality' => 'nullable|string|max:100',
            'national_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            
            // Contact Information
            'phone' => 'nullable|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            
            // Employment Information
            'employee_id' => 'nullable|string|unique:users,employee_id',
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'date_of_joining' => 'nullable|date',
            'employment_status' => 'nullable|in:full-time,part-time,contract,visiting',
            
            // Profile Photo
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            
            // Settings
            'must_change_password' => 'boolean',
            'send_welcome_email' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Prepare user data
            $userData = $validated;
            unset($userData['roles'], $userData['password_confirmation'], $userData['send_welcome_email'], $userData['profile_photo']);
            
            // Set name field (full name)
            $userData['name'] = trim($userData['first_name'] . ' ' . ($userData['middle_name'] ?? '') . ' ' . $userData['last_name']);
            
            // Hash password
            $userData['password'] = Hash::make($validated['password']);
            
            // Set email verified if creating active user
            if ($userData['status'] === 'active') {
                $userData['email_verified_at'] = now();
            }
            
            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('profile-photos', 'public');
                $userData['profile_photo'] = $path;
            }
            
            // Create user
            $user = User::create($userData);
            
            // Assign roles
            foreach ($validated['roles'] as $roleId) {
                $role = Role::find($roleId);
                if ($role) {
                    $user->assignRole($role);
                }
            }
            
            // Log activity
            UserActivityLog::logCreation($user, "Created new user: {$user->name}");
            
            // TODO: Send welcome email if requested
            // if ($request->boolean('send_welcome_email')) {
            //     // Send email
            // }
            
            DB::commit();
            
            return redirect()->route('users.show', $user)
                ->with('success', 'User created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded photo if exists
            if (isset($userData['profile_photo'])) {
                Storage::disk('public')->delete($userData['profile_photo']);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['roles.permissions', 'student', 'activityLogs' => function ($query) {
            $query->latest()->limit(10);
        }]);
        
        // Get all permissions (direct and through roles)
        $allPermissions = $user->getAllPermissions();
        
        // Group permissions by module
        $permissionsByModule = $allPermissions->groupBy('module');
        
        // Log activity
        UserActivityLog::log('view', "Viewed user profile: {$user->name}", $user);
        
        return view('users.show', compact('user', 'permissionsByModule'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = Role::where('is_active', true)->orderBy('priority')->get();
        $userRoles = $user->roles->pluck('id')->toArray();
        
        $departments = [
            'Administration',
            'Academic Affairs',
            'Computer Science',
            'Engineering',
            'Mathematics',
            'Physics',
            'Chemistry',
            'Biology',
            'Finance Department',
            'Student Affairs',
            'Library',
            'IT Department',
            'Human Resources',
            'Registrar Office',
            'Quality Assurance'
        ];
        
        return view('users.edit', compact('user', 'roles', 'userRoles', 'departments'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        // Validation
        $validated = $request->validate([
            // Basic Information
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'username' => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            
            // User Type and Status
            'user_type' => 'required|in:student,faculty,staff,admin,parent',
            'status' => 'required|in:active,inactive,suspended,pending',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
            
            // Personal Information
            'title' => 'nullable|string|max:20',
            'gender' => 'nullable|in:Male,Female,Other',
            'date_of_birth' => 'nullable|date|before:today',
            'nationality' => 'nullable|string|max:100',
            'national_id' => 'nullable|string|max:50',
            'passport_number' => 'nullable|string|max:50',
            
            // Contact Information
            'phone' => 'nullable|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            
            // Employment Information
            'employee_id' => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'department' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:100',
            'date_of_joining' => 'nullable|date',
            'employment_status' => 'nullable|in:full-time,part-time,contract,visiting',
            
            // Profile Photo
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'remove_photo' => 'boolean',
            
            // Password (optional)
            'password' => 'nullable|string|min:8|confirmed',
            'must_change_password' => 'boolean',
        ]);

        try {
            DB::beginTransaction();
            
            // Track changes for activity log
            $changes = [];
            $original = $user->toArray();
            
            // Prepare user data
            $userData = $validated;
            unset($userData['roles'], $userData['password'], $userData['password_confirmation'], $userData['remove_photo'], $userData['profile_photo']);
            
            // Set name field (full name)
            $userData['name'] = trim($userData['first_name'] . ' ' . ($userData['middle_name'] ?? '') . ' ' . $userData['last_name']);
            
            // Handle password update
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
                $userData['password_changed_at'] = now();
                $changes['password'] = 'Changed';
            }
            
            // Handle profile photo
            if ($request->boolean('remove_photo') && $user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
                $userData['profile_photo'] = null;
                $changes['profile_photo'] = 'Removed';
            } elseif ($request->hasFile('profile_photo')) {
                // Delete old photo if exists
                if ($user->profile_photo) {
                    Storage::disk('public')->delete($user->profile_photo);
                }
                
                $path = $request->file('profile_photo')->store('profile-photos', 'public');
                $userData['profile_photo'] = $path;
                $changes['profile_photo'] = 'Updated';
            }
            
            // Update user
            $user->update($userData);
            
            // Track field changes
            foreach ($userData as $key => $value) {
                if (isset($original[$key]) && $original[$key] != $value) {
                    $changes[$key] = [
                        'old' => $original[$key],
                        'new' => $value
                    ];
                }
            }
            
            // Sync roles
            $oldRoles = $user->roles->pluck('name')->toArray();
            $user->syncRoles($validated['roles']);
            $newRoles = $user->roles->pluck('name')->toArray();
            
            if ($oldRoles != $newRoles) {
                $changes['roles'] = [
                    'old' => $oldRoles,
                    'new' => $newRoles
                ];
            }
            
            // Log activity
            if (!empty($changes)) {
                UserActivityLog::logUpdate($user, $changes, "Updated user: {$user->name}");
            }
            
            DB::commit();
            
            return redirect()->route('users.show', $user)
                ->with('success', 'User updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting super admin
        if ($user->isSuperAdmin()) {
            return redirect()->back()
                ->with('error', 'Cannot delete super administrator account!');
        }
        
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account!');
        }
        
        try {
            DB::beginTransaction();
            
            // Delete profile photo if exists
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            
            // Log activity before deletion
            UserActivityLog::logDeletion($user, "Deleted user: {$user->name}");
            
            // Soft delete the user
            $user->delete();
            
            DB::commit();
            
            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
            'must_change_password' => 'boolean',
        ]);
        
        try {
            $user->update([
                'password' => Hash::make($validated['password']),
                'password_changed_at' => now(),
                'must_change_password' => $request->boolean('must_change_password'),
            ]);
            
            // Log activity
            UserActivityLog::logPasswordReset($user, 'completed');
            
            return redirect()->route('users.show', $user)
                ->with('success', 'Password reset successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to reset password: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user status (activate/suspend).
     */

    public function toggleStatus(User $user)
    {
        // Prevent users from deactivating themselves
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'You cannot change your own status.');
        }
        
        // Prevent deactivating super admins unless you are one
        if ($user->hasRole('super-administrator') && !auth()->user()->hasRole('super-administrator')) {
            return redirect()->back()->with('error', 'Only super administrators can modify other super administrator accounts.');
        }
        
        // Toggle the status
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->status = $newStatus;
        $user->save();
        
        $message = $newStatus === 'active' 
            ? "User {$user->name} has been activated." 
            : "User {$user->name} has been deactivated.";
        
        return redirect()->back()->with('success', $message);
    }

    /**
     * Manage user roles.
     */
    public function manageRoles(User $user)
    {
        $allRoles = Role::where('is_active', true)->orderBy('priority')->get();
        $userRoles = $user->roles;
        
        return view('users.manage-roles', compact('user', 'allRoles', 'userRoles'));
    }

    /**
     * Sync user roles - ADDED METHOD
     * This is the missing method that the manage-roles form needs
     */
    public function syncRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Get old roles for logging
            $oldRoles = $user->roles->pluck('name')->toArray();
            
            // Sync the roles (replaces all existing roles)
            $user->roles()->sync($validated['roles'] ?? []);
            
            // Get new roles for logging
            $newRoles = $user->roles->pluck('name')->toArray();
            
            // Log activity if roles changed
            if ($oldRoles != $newRoles) {
                UserActivityLog::log(
                    'roles_updated',
                    "Updated roles for {$user->name}: " . implode(', ', $newRoles),
                    $user
                );
            }
            
            DB::commit();
            
            return redirect()
                ->route('users.manage-roles', $user)
                ->with('success', 'Roles have been successfully updated for ' . $user->name);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to update roles: ' . $e->getMessage());
        }
    }

    /**
     * Update user roles - DEPRECATED - Use syncRoles instead
     * Keeping for backward compatibility
     */
    public function updateRoles(Request $request, User $user)
    {
        return $this->syncRoles($request, $user);
    }

    /**
     * Bulk action handler for multiple users
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'action' => 'required|in:activate,suspend,delete'
        ]);
        
        try {
            DB::beginTransaction();
            
            $users = User::whereIn('id', $validated['user_ids'])->get();
            $count = 0;
            
            foreach ($users as $user) {
                // Skip self and super admin
                if ($user->id === auth()->id() || $user->isSuperAdmin()) {
                    continue;
                }
                
                switch ($validated['action']) {
                    case 'activate':
                        $user->update(['status' => 'active']);
                        $count++;
                        break;
                        
                    case 'suspend':
                        $user->update(['status' => 'suspended']);
                        $count++;
                        break;
                        
                    case 'delete':
                        if ($user->profile_photo) {
                            Storage::disk('public')->delete($user->profile_photo);
                        }
                        $user->delete();
                        $count++;
                        break;
                }
            }
            
            // Log bulk action
            UserActivityLog::log(
                'bulk_action',
                "Performed bulk {$validated['action']} on {$count} users"
            );
            
            DB::commit();
            
            $message = match($validated['action']) {
                'activate' => "{$count} user(s) activated successfully.",
                'suspend' => "{$count} user(s) suspended successfully.",
                'delete' => "{$count} user(s) deleted successfully.",
            };
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to perform bulk action: ' . $e->getMessage());
        }
    }

    /**
     * Import users from CSV/Excel
     */
    public function import(Request $request)
    {
        // Implementation for CSV import
        return redirect()->back()->with('info', 'Import feature coming soon');
    }

    /**
     * Export users to CSV/Excel
     */
    public function export(Request $request)
    {
        // Implementation for export
        return redirect()->back()->with('info', 'Export feature coming soon');
    }

    public function getAccessibleStudents()
    {
        $query = Student::query();

        // Super admin, admin, and registrar see all students
        if ($this->hasRole(['super-administrator', 'Super Administrator', 'super-admin', 'admin', 'registrar', 'Registrar'])) {
            return $query; // Full access
        }

        // Dean can see all students in programs under their college
        if ($this->hasRole(['dean', 'Dean']) && $this->college_id) {
            // First check if the program relationship exists on Student model
            try {
                // Test if relationship exists by checking if method exists
                if (method_exists(Student::class, 'program')) {
                    // Create a test instance to verify the relationship works
                    $testStudent = new Student();
                    $relation = $testStudent->program();
                    
                    // If we get here, the relationship exists and works
                    return $query->whereHas('program', function($programQuery) {
                        // Now check if department relationship exists on program
                        $programQuery->whereHas('department', function($deptQuery) {
                            $deptQuery->where('college_id', $this->college_id);
                        });
                    });
                }
            } catch (\Exception $e) {
                // If relationship doesn't work, fall back to department field
                Log::debug('Student program relationship not working, using department field');
            }
            
            // Fallback: use department field directly if it exists
            if (\Schema::hasColumn('students', 'department')) {
                // Get all departments in the dean's college
                $departmentIds = Department::where('college_id', $this->college_id)->pluck('id');
                return $query->whereIn('department', $departmentIds);
            }
        }

        // Department head can see students in their department's programs
        if ($this->hasRole(['department-head', 'Department Head']) && $this->department_id) {
            try {
                // Try using program relationship
                if (method_exists(Student::class, 'program')) {
                    $query->whereHas('program', function($programQuery) {
                        $programQuery->where('department_id', $this->department_id);
                    });
                }
            } catch (\Exception $e) {
                Log::debug('Could not use program relationship for department head');
            }
            
            // Also check direct department field
            if (\Schema::hasColumn('students', 'department')) {
                $dept = Department::find($this->department_id);
                if ($dept) {
                    $query->orWhere('department', $dept->code)
                        ->orWhere('department', $dept->name)
                        ->orWhere('department', $this->department_id);
                }
            }
            
            return $query;
        }

        // Faculty can see students in their sections
        if ($this->hasRole(['faculty', 'Faculty']) || $this->user_type === 'faculty') {
            try {
                // Get sections taught by this faculty
                $sectionIds = \App\Models\CourseSection::where('instructor_id', $this->id)->pluck('id');
                
                if ($sectionIds->isNotEmpty()) {
                    return $query->whereHas('enrollments', function($enrollQuery) use ($sectionIds) {
                        $enrollQuery->whereIn('section_id', $sectionIds)
                                ->where('enrollment_status', 'enrolled');
                    });
                }
            } catch (\Exception $e) {
                Log::debug('Could not get students for faculty: ' . $e->getMessage());
            }
        }

        // Advisor can see their assigned students
        if ($this->hasRole(['advisor', 'Advisor'])) {
            if (\Schema::hasColumn('students', 'advisor_id')) {
                return $query->where('advisor_id', $this->id);
            }
            // Fallback: check advisor_name field if it exists
            if (\Schema::hasColumn('students', 'advisor_name')) {
                return $query->where('advisor_name', $this->name);
            }
        }

        // Students only see themselves
        if (($this->hasRole(['student', 'Student']) || $this->user_type === 'student') && $this->student) {
            return $query->where('id', $this->student->id);
        }

        // Default: no access to any students
        return $query->whereRaw('1 = 0');
    }

    public function getAccessibleCourses()
    {
        $query = Course::query();

        // Super admin and admin see all courses
        if ($this->hasRole(['super-administrator', 'Super Administrator', 'super-admin', 'admin'])) {
            return $query; // Full access
        }

        // Dean can see all courses in their college
        if ($this->hasRole(['dean', 'Dean']) && $this->college_id) {
            return $query->whereHas('department', function($deptQuery) {
                $deptQuery->where('college_id', $this->college_id);
            });
        }

        // Department head can see all courses in their department
        if ($this->hasRole(['department-head', 'Department Head']) && $this->department_id) {
            return $query->where('department_id', $this->department_id);
        }

        // Faculty can see courses they teach or are assigned to
        if ($this->hasRole(['faculty', 'Faculty']) || $this->user_type === 'faculty') {
            $query->where(function($q) {
                // Courses in faculty's departments
                $departmentIds = $this->getAllDepartments()->pluck('id');
                if ($departmentIds->isNotEmpty()) {
                    $q->whereIn('department_id', $departmentIds);
                }
                
                // Courses faculty is coordinator for
                $q->orWhere('coordinator_id', $this->id);
                
                // Courses faculty teaches sections of
                try {
                    if (class_exists(\App\Models\CourseSection::class)) {
                        $sectionCourseIds = \App\Models\CourseSection::where('instructor_id', $this->id)
                            ->pluck('course_id')
                            ->unique();
                        if ($sectionCourseIds->isNotEmpty()) {
                            $q->orWhereIn('id', $sectionCourseIds);
                        }
                    }
                } catch (\Exception $e) {
                    Log::debug('Could not get courses from sections: ' . $e->getMessage());
                }
                
                // Courses from faculty assignments if relationship exists
                try {
                    if (method_exists($this, 'activeCourseAssignments')) {
                        $assignedCourseIds = $this->activeCourseAssignments()->pluck('course_id');
                        if ($assignedCourseIds->isNotEmpty()) {
                            $q->orWhereIn('id', $assignedCourseIds);
                        }
                    }
                } catch (\Exception $e) {
                    Log::debug('Could not get assigned courses: ' . $e->getMessage());
                }
            });
            
            return $query;
        }

        // Students see courses they're enrolled in
        if (($this->hasRole(['student', 'Student']) || $this->user_type === 'student') && $this->student) {
            try {
                $enrolledCourseIds = DB::table('enrollments')
                    ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
                    ->where('enrollments.student_id', $this->student->id)
                    ->pluck('course_sections.course_id')
                    ->unique();
                
                if ($enrolledCourseIds->isNotEmpty()) {
                    return $query->whereIn('id', $enrolledCourseIds);
                }
            } catch (\Exception $e) {
                Log::debug('Could not get enrolled courses: ' . $e->getMessage());
            }
        }

        // Default: no access
        return $query->whereRaw('1 = 0');
    }

    /**
     * Get faculty members user can manage
     * FIXED VERSION - Handles missing relationships properly
     */
    public function getManageableFaculty()
    {
        $query = User::where('user_type', 'faculty');

        // Super admin, admin, and HR can manage all faculty
        if ($this->hasRole(['super-administrator', 'Super Administrator', 'super-admin', 'admin', 'hr', 'HR', 'human-resources'])) {
            return $query; // Full access
        }

        // Dean can manage faculty in their college
        if ($this->hasRole(['dean', 'Dean']) && $this->college_id) {
            return $query->where('college_id', $this->college_id);
        }

        // Department head can manage faculty in their department
        if ($this->hasRole(['department-head', 'Department Head']) && $this->department_id) {
            $query->where(function($q) {
                $q->where('department_id', $this->department_id);
                
                // Include affiliated faculty if relationship exists
                try {
                    if (method_exists(User::class, 'departmentAffiliations')) {
                        $q->orWhereHas('departmentAffiliations', function($affQuery) {
                            $affQuery->where('department_id', $this->department_id)
                                    ->where('is_active', true);
                        });
                    }
                } catch (\Exception $e) {
                    Log::debug('Could not check department affiliations: ' . $e->getMessage());
                }
            });
            
            return $query;
        }

        // Default: no management rights
        return $query->whereRaw('1 = 0');
    }

}