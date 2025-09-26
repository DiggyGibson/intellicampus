<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Basic Information
        'name',
        'email',
        'username',
        'password',
        'email_verified_at',
        
        // User Type and Status
        'user_type',          // student, faculty, staff, admin, parent
        'status',             // active, inactive, suspended, pending
        
        // Personal Information
        'title',              // Mr., Ms., Dr., Prof., etc.
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'nationality',
        'national_id',
        'passport_number',
        
        // Contact Information
        'phone',
        'alternate_phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        
        // Employment Information (for faculty/staff)
        'employee_id',
        'department',
        'designation',
        'office_location',
        'office_phone',
        'date_of_joining',
        'employment_status',   // full-time, part-time, contract, visiting
        'contract_end_date',
        
        // ORGANIZATIONAL SCOPE FIELDS
        'college_id',
        'school_id',
        'department_id',
        'division_id',
        'organizational_role', // dean, associate_dean, director, department_head, faculty, staff
        'secondary_departments', // JSON array of additional department IDs
        'has_administrative_role',
        
        // Academic Information (for faculty)
        'highest_qualification',
        'specialization',
        'research_interests',
        'publications',
        
        // Emergency Contact
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'emergency_contact_email',
        
        // Profile and Settings
        'profile_photo',
        'bio',
        'preferences',         // JSON field for user preferences
        'timezone',
        'language',
        'notification_preferences',  // JSON field
        
        // Security
        'last_login_at',
        'last_login_ip',
        'last_activity_at',
        'password_changed_at',
        'must_change_password',
        'login_attempts',
        'locked_until',
        'two_factor_secret',
        'two_factor_enabled',
        'security_questions',   // JSON field
        
        // System Fields
        'created_by',
        'updated_by',
        'metadata',            // JSON field for additional data
        'notes',               // Admin notes about the user
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'date_of_joining' => 'date',
        'contract_end_date' => 'date',
        'last_login_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'locked_until' => 'datetime',
        'must_change_password' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'has_administrative_role' => 'boolean',
        'preferences' => 'array',
        'notification_preferences' => 'array',
        'security_questions' => 'array',
        'metadata' => 'array',
        'publications' => 'array',
        'research_interests' => 'array',
        'secondary_departments' => 'array',
    ];

    /**
     * Default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'pending',
        'must_change_password' => false,
        'two_factor_enabled' => false,
        'has_administrative_role' => false,
        'login_attempts' => 0,
        'language' => 'en',
        'timezone' => 'UTC',
        'preferences' => '{}',
        'notification_preferences' => '{}',
        'metadata' => '{}',
        'secondary_departments' => '[]',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Set created_by when creating
        static::creating(function ($user) {
            if (auth()->check() && !$user->created_by) {
                $user->created_by = auth()->id();
            }
            
            // Generate username if not provided
            if (empty($user->username)) {
                $user->username = strtolower($user->first_name . '.' . $user->last_name);
                $user->username = preg_replace('/[^a-z0-9.]/', '', $user->username);
                
                // Make username unique
                $count = User::where('username', 'like', $user->username . '%')->count();
                if ($count > 0) {
                    $user->username .= ($count + 1);
                }
            }
        });

        // Set updated_by when updating
        static::updating(function ($user) {
            if (auth()->check()) {
                $user->updated_by = auth()->id();
            }
        });

        // Log user deletion
        static::deleting(function ($user) {
            if (class_exists(UserActivityLog::class)) {
                UserActivityLog::logDeletion($user, "User account deleted: {$user->name}");
            }
        });
    }

    // ========================================
    // ORGANIZATIONAL RELATIONSHIPS
    // ========================================

    /**
     * Get the user's college
     */
    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    /**
     * Get the user's school
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the user's primary department
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the user's division
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get all department affiliations
     */
    public function departmentAffiliations(): HasMany
    {
        return $this->hasMany(UserDepartmentAffiliation::class);
    }

    /**
     * Get active department affiliations
     */
    public function activeDepartmentAffiliations()
    {
        return $this->departmentAffiliations()->active();
    }

    /**
     * Get all departments user is affiliated with (primary + secondary)
     */
    public function getAllDepartments(): Collection
    {
        $departments = collect();
        
        // Add primary department
        if ($this->department_id) {
            $departments->push($this->department);
        }
        
        // Add affiliated departments
        $affiliatedDepartments = $this->activeDepartmentAffiliations()
            ->with('department')
            ->get()
            ->pluck('department');
        
        $departments = $departments->merge($affiliatedDepartments);
        
        // Add from secondary_departments JSON field if exists
        if ($this->secondary_departments && is_array($this->secondary_departments)) {
            $secondaryDepts = Department::whereIn('id', $this->secondary_departments)->get();
            $departments = $departments->merge($secondaryDepts);
        }
        
        return $departments->unique('id');
    }

    /**
     * Get faculty course assignments
     */
    public function courseAssignments(): HasMany
    {
        return $this->hasMany(FacultyCourseAssignment::class, 'faculty_id');
    }

    /**
     * Get active course assignments
     */
    public function activeCourseAssignments()
    {
        return $this->courseAssignments()->active();
    }

    /**
     * Get organizational permissions
     */
    public function organizationalPermissions(): HasMany
    {
        return $this->hasMany(OrganizationalPermission::class);
    }

    /**
     * Get active organizational permissions
     */
    public function activeOrganizationalPermissions()
    {
        return $this->organizationalPermissions()->active();
    }

    // ========================================
    // SCOPE CHECKING METHODS
    // ========================================

    /**
     * Check if user can access a specific department's data
     */
    public function canAccessDepartment(Department $department): bool
    {
        // Super admin can access everything
        if ($this->hasRole(['super-admin', 'admin', 'super-administrator', 'Super Administrator'])) {
            return true;
        }

        // Check if it's user's primary department
        if ($this->department_id === $department->id) {
            return true;
        }

        // Check if user has affiliation with department
        if ($this->departmentAffiliations()->where('department_id', $department->id)->active()->exists()) {
            return true;
        }

        // Check if user is dean of the college
        if ($department->college && $department->college->dean_id === $this->id) {
            return true;
        }

        // Check if user is director of the school
        if ($department->school && $department->school->director_id === $this->id) {
            return true;
        }

        // Check organizational permissions
        return $this->hasOrganizationalPermission('department', $department->id, 'view');
    }

    /**
     * Check if user can manage a department
     */
    public function canManageDepartment(Department $department): bool
    {
        // Admin can manage everything
        if ($this->hasRole(['super-admin', 'admin', 'super-administrator', 'Super Administrator'])) {
            return true;
        }

        // Check if user is department head or deputy
        if ($department->head_id === $this->id || $department->deputy_head_id === $this->id) {
            return true;
        }

        // Check if user is dean of the college
        if ($department->college && $department->college->dean_id === $this->id) {
            return true;
        }

        // Check organizational permissions
        return $this->hasOrganizationalPermission('department', $department->id, 'manage');
    }

    /**
     * Check if user has specific organizational permission
     */
    public function hasOrganizationalPermission(string $scopeType, int $scopeId, string $action): bool
    {
        return $this->activeOrganizationalPermissions()
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->get()
            ->contains(function ($permission) use ($action) {
                return $permission->allows($action);
            });
    }

    /**
     * Get courses user can access based on scope
     */
    public function getAccessibleCourses()
    {
        $query = Course::query();

        if ($this->hasRole(['super-admin', 'admin', 'super-administrator', 'Super Administrator'])) {
            return $query; // Full access
        }

        if ($this->hasRole('dean') && $this->college_id) {
            // Dean can see all courses in their college
            return $query->whereHas('department', function($q) {
                $q->where('college_id', $this->college_id);
            });
        }

        if ($this->hasRole('department-head') && $this->department_id) {
            // Department head can see all courses in their department
            return $query->where('department_id', $this->department_id);
        }

        if ($this->hasRole('faculty')) {
            // Faculty can see:
            // 1. Courses they're assigned to teach
            $courseIds = $this->activeCourseAssignments()->pluck('course_id');
            
            // 2. Courses in their departments
            $departmentIds = $this->getAllDepartments()->pluck('id');
            
            // 3. Courses they coordinate
            return $query->where(function($q) use ($courseIds, $departmentIds) {
                $q->whereIn('id', $courseIds)
                  ->orWhereIn('department_id', $departmentIds)
                  ->orWhere('coordinator_id', $this->id);
            });
        }

        // Default: no access
        return $query->whereRaw('1 = 0');
    }

    /**
     * Get students user can access based on scope
     */
    public function getAccessibleStudents()
    {
        $query = Student::query();

        if ($this->hasRole(['super-admin', 'admin', 'super-administrator', 'Super Administrator', 'registrar'])) {
            return $query; // Full access
        }

        if ($this->hasRole('dean') && $this->college_id) {
            // Dean can see all students in programs under their college
            return $query->whereHas('program.department', function($q) {
                $q->where('college_id', $this->college_id);
            });
        }

        if ($this->hasRole('department-head') && $this->department_id) {
            // Department head can see students in their department's programs
            return $query->whereHas('program', function($q) {
                $q->where('department_id', $this->department_id);
            });
        }

        if ($this->hasRole('faculty')) {
            // Faculty can see students in their sections
            $sectionIds = CourseSection::where('instructor_id', $this->id)->pluck('id');
            
            return $query->whereHas('enrollments', function($q) use ($sectionIds) {
                $q->whereIn('section_id', $sectionIds)
                  ->where('enrollment_status', 'enrolled');
            });
        }

        if ($this->hasRole('advisor')) {
            // Advisor can see their assigned students
            return $query->where('advisor_id', $this->id);
        }

        // Default: no access
        return $query->whereRaw('1 = 0');
    }

    /**
     * Get faculty members user can manage
     */
    public function getManageableFaculty()
    {
        $query = User::where('user_type', 'faculty');

        if ($this->hasRole(['super-admin', 'admin', 'super-administrator', 'Super Administrator', 'hr'])) {
            return $query; // Full access
        }

        if ($this->hasRole('dean') && $this->college_id) {
            // Dean can manage faculty in their college
            return $query->where('college_id', $this->college_id);
        }

        if ($this->hasRole('department-head') && $this->department_id) {
            // Department head can manage faculty in their department
            return $query->where(function($q) {
                $q->where('department_id', $this->department_id)
                  ->orWhereHas('departmentAffiliations', function($aff) {
                      $aff->where('department_id', $this->department_id)
                          ->where('is_active', true);
                  });
            });
        }

        // Default: no management rights
        return $query->whereRaw('1 = 0');
    }

    /**
     * Check if user is in leadership position
     */
    public function isInLeadershipPosition(): bool
    {
        // Check organizational role
        if (in_array($this->organizational_role, ['dean', 'associate_dean', 'director', 'department_head'])) {
            return true;
        }

        // Check if user is a dean
        if (College::where('dean_id', $this->id)->orWhere('associate_dean_id', $this->id)->exists()) {
            return true;
        }

        // Check if user is a school director
        if (School::where('director_id', $this->id)->exists()) {
            return true;
        }

        // Check if user is a department head/deputy/secretary
        if (Department::where('head_id', $this->id)
            ->orWhere('deputy_head_id', $this->id)
            ->orWhere('secretary_id', $this->id)
            ->exists()) {
            return true;
        }

        // Check if user is a division coordinator
        if (Division::where('coordinator_id', $this->id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Get user's organizational scope level
     */
    public function getOrganizationalScope(): array
    {
        $scope = [];

        if ($this->hasRole(['super-admin', 'admin', 'super-administrator', 'Super Administrator'])) {
            $scope[] = ['type' => 'system', 'level' => 'full', 'name' => 'System Administrator'];
        }

        if ($this->college_id) {
            $college = $this->college;
            $scope[] = [
                'type' => 'college', 
                'id' => $this->college_id, 
                'name' => $college ? $college->name : 'Unknown College'
            ];
        }

        if ($this->school_id) {
            $school = $this->school;
            $scope[] = [
                'type' => 'school', 
                'id' => $this->school_id, 
                'name' => $school ? $school->name : 'Unknown School'
            ];
        }

        if ($this->department_id) {
            $department = $this->department;
            $scope[] = [
                'type' => 'department', 
                'id' => $this->department_id, 
                'name' => $department ? $department->name : 'Unknown Department'
            ];
        }

        if ($this->division_id) {
            $division = $this->division;
            $scope[] = [
                'type' => 'division', 
                'id' => $this->division_id, 
                'name' => $division ? $division->name : 'Unknown Division'
            ];
        }

        return $scope;
    }

    // ========================================
    // PERMISSION METHODS - ADDING THE MISSING METHOD HERE
    // ========================================

    /**
     * Get all permissions for this user (through roles and direct)
     * THIS IS THE MISSING METHOD YOUR CONTROLLER NEEDS
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllPermissions()
    {
        $permissions = collect();
        
        // Get permissions through roles
        foreach ($this->roles as $role) {
            if ($role->permissions) {
                $permissions = $permissions->merge($role->permissions);
            }
        }
        
        // Get direct permissions if they exist
        try {
            if ($this->permissions) {
                $permissions = $permissions->merge($this->permissions);
            }
        } catch (\Exception $e) {
            // If direct permissions relationship doesn't exist, continue without them
        }
        
        // Remove duplicates based on permission ID
        return $permissions->unique('id');
    }

    /**
     * Check if user has a specific permission
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        $allPermissions = $this->getAllPermissions();
        
        return $allPermissions->contains('slug', $permission) ||
               $allPermissions->contains('name', $permission);
    }

    /**
     * Sync roles (replace all existing with new ones)
     *
     * @param array $roles Array of role IDs
     * @return void
     */
    public function syncRoles($roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        $this->roles()->sync($roles);
    }

    // ========================================
    // EXISTING METHODS (From Original Model)
    // ========================================

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        $parts = array_filter([
            $this->title,
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ]);
        
        return implode(' ', $parts) ?: $this->name;
    }

    /**
     * Get the user's display name.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return $this->full_name ?: $this->username ?: $this->email;
    }

    /**
     * Get the user's initials for avatar display.
     *
     * @return string
     */
    public function getInitialsAttribute()
    {
        $names = array_filter([
            $this->first_name,
            $this->last_name
        ]);
        
        if (empty($names)) {
            // Fallback to name field
            $names = explode(' ', $this->name);
        }
        
        $initials = '';
        foreach ($names as $name) {
            if (!empty($name)) {
                $initials .= strtoupper(substr($name, 0, 1));
            }
        }
        
        return substr($initials, 0, 2) ?: 'U';
    }

    /**
     * Get the roles assigned to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot('assigned_at', 'assigned_by', 'expires_at', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Get the permissions directly assigned to the user.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
            ->withPivot('granted_at', 'granted_by', 'expires_at')
            ->withTimestamps();
    }

    /**
     * Get the user's activity logs.
     */
    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class);
    }

    /**
     * Get the student record if user is a student.
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Check if user has a specific role.
     *
     * @param string|array|Role $roles
     * @return bool
     */
    public function hasRole($roles)
    {
        // Handle array of roles
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
            return false;
        }
        
        // Handle string role - check both slug and name
        if (is_string($roles)) {
            // Also check for variations with/without hyphens and spaces
            $variations = [
                $roles,
                str_replace('-', ' ', $roles),
                str_replace(' ', '-', $roles),
                strtolower(str_replace(' ', '-', $roles)),
                ucwords(str_replace('-', ' ', $roles))
            ];
            
            return $this->roles()
                ->where(function($query) use ($variations) {
                    foreach ($variations as $variation) {
                        $query->orWhere('slug', $variation)
                              ->orWhere('name', $variation);
                    }
                })
                ->exists();
        }
        
        // Handle Role model instance
        if ($roles instanceof Role) {
            return $this->roles()->where('role_id', $roles->id)->exists();
        }
        
        return false;
    }

    /**
     * Check if user has any of the specified roles.
     *
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole($roles)
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the specified roles.
     *
     * @param array $roles
     * @return bool
     */
    public function hasAllRoles($roles)
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Assign a role to the user.
     *
     * @param Role|string $role
     * @param array $pivotData
     * @return void
     */
    public function assignRole($role, $pivotData = [])
    {
        if (is_string($role)) {
            // Try to find the role by slug or name
            $roleModel = Role::where('slug', $role)
                ->orWhere('name', $role)
                ->first();
            
            if (!$roleModel) {
                // If role doesn't exist, create it
                $roleModel = Role::create([
                    'name' => ucfirst($role),
                    'slug' => strtolower($role),
                    'description' => ucfirst($role) . ' role (auto-created)',
                    'is_active' => true,
                ]);
                
                Log::info('Auto-created missing role', ['role' => $role]);
            }
            
            $role = $roleModel;
        }
        
        if (!$this->hasRole($role)) {
            $defaultPivot = [
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
                'is_primary' => $this->roles()->count() === 0
            ];
            
            $this->roles()->attach($role->id, array_merge($defaultPivot, $pivotData));
            
            if (class_exists(UserActivityLog::class)) {
                UserActivityLog::logRoleChange($this, 'Assigned', [$role->name]);
            }
        }
    }

    /**
     * Remove a role from the user.
     *
     * @param Role|string $role
     * @return void
     */
    public function removeRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->first();
        }
        
        if ($role) {
            $this->roles()->detach($role->id);
            if (class_exists(UserActivityLog::class)) {
                UserActivityLog::logRoleChange($this, 'Removed', [$role->name]);
            }
        }
    }

    /**
     * Get sections taught by this faculty member (if faculty).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|null
     */
    public function sections()
    {
        if ($this->isFaculty()) {
            return $this->hasMany(\App\Models\CourseSection::class, 'instructor_id');
        }
        return null;
    }

    /**
     * Get courses taught by this faculty member (if faculty).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough|null
     */
    public function courses()
    {
        if ($this->isFaculty()) {
            return $this->hasManyThrough(
                \App\Models\Course::class,
                \App\Models\CourseSection::class,
                'instructor_id',
                'id',
                'id',
                'course_id'
            );
        }
        return null;
    }

    /**
     * Check if the user is a super administrator.
     *
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->hasRole(['super-administrator', 'Super Administrator', 'super-admin']);
    }

    /**
     * Check if the user is an administrator.
     *
     * @return bool
     */
    public function isAdmin()
    {
        // Check user_type first
        if ($this->user_type === 'admin') {
            return true;
        }
        
        // Then check roles
        return $this->hasAnyRole(['super-administrator', 'Super Administrator', 'super-admin', 'admin', 'system-administrator', 'academic-administrator']);
    }

    /**
     * Check if user is faculty.
     *
     * @return bool
     */
    public function isFaculty()
    {
        // Check user_type first
        if ($this->user_type === 'faculty') {
            return true;
        }
        
        // Then check roles
        return $this->hasRole('faculty') || $this->hasRole('instructor') || $this->hasRole('professor');
    }

    /**
     * Check if user is a student.
     *
     * @return bool
     */
    public function isStudent()
    {
        // Check user_type first
        if ($this->user_type === 'student') {
            return true;
        }
        
        // Then check role
        return $this->hasRole('student');
    }

    /**
     * Check if user is registrar.
     *
     * @return bool
     */
    public function isRegistrar()
    {
        // Check user_type
        if ($this->user_type === 'staff' && $this->department === 'registrar') {
            return true;
        }
        
        // Check role
        return $this->hasRole('registrar') || $this->hasRole('academic-registrar');
    }

    /**
     * Check if user is staff.
     *
     * @return bool
     */
    public function isStaff()
    {
        return $this->user_type === 'staff' || $this->hasRole('staff');
    }

    /**
     * Check if the user account is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->status === 'active' && !$this->isLocked();
    }

    /**
     * Check if the user account is locked.
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Scope a query to only include active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include users of a specific type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('user_type', $type);
    }

    /**
     * Scope a query to search users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%")
              ->orWhere('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('employee_id', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter users by department
     */
    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where(function($q) use ($departmentId) {
            $q->where('department_id', $departmentId)
              ->orWhereHas('departmentAffiliations', function($aff) use ($departmentId) {
                  $aff->where('department_id', $departmentId)
                      ->where('is_active', true);
              });
        });
    }

    /**
     * Scope to filter users by college
     */
    public function scopeInCollege($query, $collegeId)
    {
        return $query->where('college_id', $collegeId);
    }

    /**
     * Scope to filter users by organizational role
     */
    public function scopeWithOrganizationalRole($query, $role)
    {
        return $query->where('organizational_role', $role);
    }

    /**
     * Get role names as a collection
     * This method is expected by various parts of the system
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoleNames()
    {
        return $this->roles()->pluck('name');
    }

    /**
     * Get role slugs as a collection
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoleSlugs()
    {
        return $this->roles()->pluck('slug');
    }

    /**
     * Get the primary role for display
     *
     * @return \App\Models\Role|null
     */
    public function getPrimaryRole()
    {
        // First check for primary flag in pivot
        $primaryRole = $this->roles()
            ->wherePivot('is_primary', true)
            ->first();
        
        if ($primaryRole) {
            return $primaryRole;
        }
        
        // Otherwise get the highest priority role
        return $this->roles()
            ->orderBy('priority', 'asc')
            ->first();
    }

    /**
     * Get primary role name
     *
     * @return string|null
     */
    public function getPrimaryRoleName()
    {
        $role = $this->getPrimaryRole();
        return $role ? $role->name : null;
    }

    /**
     * Check if user can perform an action
     * Simplified permission check
     *
     * @param string $permission
     * @return bool
     */
    public function can($permission, $arguments = [])
    {
        // Super admin can do anything
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        // Check if user has the permission
        return $this->hasPermission($permission);
    }

    /**
     * Check if user cannot perform an action
     *
     * @param string $permission
     * @return bool
     */
    public function cannot($permission, $arguments = [])
    {
        return !$this->can($permission, $arguments);
    }
}