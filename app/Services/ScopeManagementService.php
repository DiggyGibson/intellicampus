<?php

// app/Services/ScopeManagementService.php
namespace App\Services;

use App\Models\User;
use App\Models\Department;
use App\Models\College;
use App\Models\School;
use App\Models\Course;
use App\Models\Student;
use App\Models\CourseSection;
use App\Models\OrganizationalPermission;
use App\Models\ScopeAuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScopeManagementService
{
    /**
     * Apply scope filtering to a query based on user's access level
     */
    public function applyScopeFilter(Builder $query, User $user, string $entityType): Builder
    {
        // Super admins bypass all scope filtering
        if ($user->hasRole(['super-administrator', 'Super Administrator', 'super-admin', 'admin'])) {
            return $query;
        }

        // Apply entity-specific scope filtering
        try {
            switch ($entityType) {
                case 'course':
                    return $this->applyCourseScope($query, $user);
                case 'student':
                    return $this->applyStudentScope($query, $user);
                case 'faculty':
                    return $this->applyFacultyScope($query, $user);
                case 'section':
                    return $this->applySectionScope($query, $user);
                case 'department':
                    return $this->applyDepartmentScope($query, $user);
                default:
                    Log::warning("Unknown entity type for scope filtering: {$entityType}");
                    // Return empty result set for unknown entity types
                    return $query->whereRaw('1 = 0');
            }
        } catch (\Exception $e) {
            Log::error('Error applying scope filter: ' . $e->getMessage());
            // On error, return restricted query for safety
            return $query->whereRaw('1 = 0');
        }
    }

    /**
     * Apply course scope filtering
     */
    protected function applyCourseScope(Builder $query, User $user): Builder
    {
        return $query->where(function($q) use ($user) {
            // Dean sees all courses in their college
            if ($user->hasRole(['dean', 'Dean']) && $user->college_id) {
                $q->orWhereHas('department', function($deptQuery) use ($user) {
                    $deptQuery->where('college_id', $user->college_id);
                });
            }

            // School director sees all courses in their school
            if (($user->organizational_role === 'director' || $user->hasRole('director')) && $user->school_id) {
                $q->orWhereHas('department', function($deptQuery) use ($user) {
                    $deptQuery->where('school_id', $user->school_id);
                });
            }

            // Department head sees all courses in their department
            if ($user->hasRole(['department-head', 'Department Head']) && $user->department_id) {
                $q->orWhere('department_id', $user->department_id);
                
                // Include cross-listed courses if field exists
                if (\Schema::hasColumn('courses', 'cross_listed_departments')) {
                    $q->orWhereJsonContains('cross_listed_departments', $user->department_id);
                }
            }

            // Faculty sees courses they're assigned to or teach
            if ($user->hasRole(['faculty', 'Faculty']) || $user->user_type === 'faculty') {
                // Courses in faculty's departments
                $departmentIds = $user->getAllDepartments()->pluck('id');
                if ($departmentIds->isNotEmpty()) {
                    $q->orWhereIn('department_id', $departmentIds);
                }

                // Courses faculty is assigned to (if relationship exists)
                if (method_exists($user, 'activeCourseAssignments')) {
                    $assignedCourseIds = $user->activeCourseAssignments()->pluck('course_id');
                    if ($assignedCourseIds->isNotEmpty()) {
                        $q->orWhereIn('id', $assignedCourseIds);
                    }
                }

                // Courses faculty teaches sections of
                if (class_exists(\App\Models\CourseSection::class)) {
                    $sectionCourseIds = CourseSection::where('instructor_id', $user->id)
                        ->pluck('course_id')
                        ->unique();
                    if ($sectionCourseIds->isNotEmpty()) {
                        $q->orWhereIn('id', $sectionCourseIds);
                    }
                }

                // Courses where faculty is coordinator
                $q->orWhere('coordinator_id', $user->id);
            }

            // Students see courses they're enrolled in
            if ($user->hasRole(['student', 'Student']) || $user->user_type === 'student') {
                if ($user->student) {
                    $enrolledCourseIds = DB::table('enrollments')
                        ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
                        ->where('enrollments.student_id', $user->student->id)
                        ->pluck('course_sections.course_id')
                        ->unique();
                    
                    if ($enrolledCourseIds->isNotEmpty()) {
                        $q->orWhereIn('id', $enrolledCourseIds);
                    }
                }
            }
        });
    }

    /**
     * Apply student scope filtering
     */
    protected function applyStudentScope(Builder $query, User $user): Builder
    {
        // Registrar sees all students
        if ($user->hasRole(['registrar', 'Registrar'])) {
            return $query;
        }

        return $query->where(function($q) use ($user) {
            // Dean sees students in programs under their college
            if ($user->hasRole(['dean', 'Dean']) && $user->college_id) {
                // Check if program relationship exists
                if (method_exists(\App\Models\Student::class, 'program')) {
                    $q->orWhereHas('program.department', function($deptQuery) use ($user) {
                        $deptQuery->where('college_id', $user->college_id);
                    });
                }
            }

            // Department head sees students in their department's programs
            if ($user->hasRole(['department-head', 'Department Head']) && $user->department_id) {
                if (method_exists(\App\Models\Student::class, 'program')) {
                    $q->orWhereHas('program', function($progQuery) use ($user) {
                        $progQuery->where('department_id', $user->department_id);
                    });
                }
                // Also check direct department field
                $q->orWhere('department', $user->department_id);
            }

            // Faculty sees students in their sections
            if ($user->hasRole(['faculty', 'Faculty']) || $user->user_type === 'faculty') {
                if (class_exists(\App\Models\CourseSection::class)) {
                    $sectionIds = CourseSection::where('instructor_id', $user->id)->pluck('id');
                    if ($sectionIds->isNotEmpty()) {
                        $q->orWhereHas('enrollments', function($enrollQuery) use ($sectionIds) {
                            $enrollQuery->whereIn('section_id', $sectionIds)
                                       ->where('enrollment_status', 'enrolled');
                        });
                    }
                }
            }

            // Advisor sees their assigned students
            if ($user->hasRole(['advisor', 'Advisor'])) {
                $q->orWhere('advisor_id', $user->id);
            }

            // Students only see themselves
            if (($user->hasRole(['student', 'Student']) || $user->user_type === 'student') && $user->student) {
                $q->orWhere('id', $user->student->id);
            }
        });
    }

    /**
     * Apply faculty scope filtering
     */
    protected function applyFacultyScope(Builder $query, User $user): Builder
    {
        // HR and Admin see all faculty
        if ($user->hasRole(['hr', 'human-resources', 'HR', 'Human Resources'])) {
            return $query;
        }

        return $query->where(function($q) use ($user) {
            // Dean sees all faculty in their college
            if ($user->hasRole(['dean', 'Dean']) && $user->college_id) {
                $q->orWhere('college_id', $user->college_id);
            }

            // School director sees faculty in their school
            if (($user->organizational_role === 'director' || $user->hasRole('director')) && $user->school_id) {
                $q->orWhere('school_id', $user->school_id);
            }

            // Department head sees faculty in their department
            if ($user->hasRole(['department-head', 'Department Head']) && $user->department_id) {
                $q->orWhere('department_id', $user->department_id);
                
                // Include affiliated faculty if relationship exists
                if (method_exists(\App\Models\User::class, 'departmentAffiliations')) {
                    $q->orWhereHas('departmentAffiliations', function($affQuery) use ($user) {
                        $affQuery->where('department_id', $user->department_id)
                                 ->where('is_active', true);
                    });
                }
            }

            // Faculty can see other faculty in their department
            if (($user->hasRole(['faculty', 'Faculty']) || $user->user_type === 'faculty') && $user->department_id) {
                $q->orWhere('department_id', $user->department_id);
            }
        });
    }

    /**
     * Apply section scope filtering
     */
    protected function applySectionScope(Builder $query, User $user): Builder
    {
        // Registrar sees all sections
        if ($user->hasRole(['registrar', 'Registrar'])) {
            return $query;
        }

        return $query->where(function($q) use ($user) {
            // Apply course-based filtering first
            if (class_exists(\App\Models\Course::class)) {
                $accessibleCourseIds = Course::query()
                    ->where(function($courseQuery) use ($user) {
                        $this->applyCourseScope($courseQuery, $user);
                    })
                    ->pluck('id');
                    
                if ($accessibleCourseIds->isNotEmpty()) {
                    $q->whereIn('course_id', $accessibleCourseIds);
                }
            }

            // Faculty sees sections they teach
            if ($user->hasRole(['faculty', 'Faculty']) || $user->user_type === 'faculty') {
                $q->orWhere('instructor_id', $user->id);
            }
        });
    }

    /**
     * Apply department scope filtering
     */
    protected function applyDepartmentScope(Builder $query, User $user): Builder
    {
        // Use the scopeAccessibleBy method from Department model
        return $query->accessibleBy($user);
    }

    /**
     * Check if user can perform action on entity
     */
    public function canPerformAction(User $user, string $action, string $entityType, $entity): bool
    {
        // Cache the result for performance
        $cacheKey = "scope_permission:{$user->id}:{$action}:{$entityType}:{$entity->id}";
        
        return Cache::remember($cacheKey, 300, function() use ($user, $action, $entityType, $entity) {
            // Super admin can do everything
            if ($user->hasRole(['super-administrator', 'Super Administrator', 'super-admin', 'admin'])) {
                $this->logAccess($user, $action, $entityType, $entity->id, true);
                return true;
            }

            // Check entity-specific permissions
            $allowed = $this->checkEntityPermission($user, $action, $entityType, $entity);
            
            // Log the access attempt
            $this->logAccess($user, $action, $entityType, $entity->id, $allowed);
            
            return $allowed;
        });
    }

    /**
     * Check entity-specific permissions
     */
    protected function checkEntityPermission(User $user, string $action, string $entityType, $entity): bool
    {
        try {
            switch ($entityType) {
                case 'course':
                    return $this->checkCoursePermission($user, $action, $entity);
                case 'student':
                    return $this->checkStudentPermission($user, $action, $entity);
                case 'faculty':
                    return $this->checkFacultyPermission($user, $action, $entity);
                case 'section':
                    return $this->checkSectionPermission($user, $action, $entity);
                case 'department':
                    return $this->checkDepartmentPermission($user, $action, $entity);
                default:
                    return false;
            }
        } catch (\Exception $e) {
            Log::error("Error checking permission: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check course permissions
     */
    protected function checkCoursePermission(User $user, string $action, $course): bool
    {
        switch ($action) {
            case 'view':
                // Check if course has canBeViewedBy method
                if (method_exists($course, 'canBeViewedBy')) {
                    return $course->canBeViewedBy($user);
                }
                // Fallback: check if user can access department
                return $user->canAccessDepartment($course->department);
                
            case 'edit':
            case 'update':
                if (method_exists($course, 'canBeManagedBy')) {
                    return $course->canBeManagedBy($user);
                }
                // Fallback: check if user can manage department
                return $user->canManageDepartment($course->department);
                
            case 'delete':
                // Only admin and department head can delete
                if ($user->hasRole(['admin', 'super-administrator'])) {
                    return true;
                }
                if ($user->hasRole(['department-head', 'Department Head']) && 
                    $course->department_id === $user->department_id) {
                    return true;
                }
                return false;
                
            default:
                return false;
        }
    }

    /**
     * Check student permissions
     */
    protected function checkStudentPermission(User $user, string $action, $student): bool
    {
        switch ($action) {
            case 'view':
                // Check if student is in user's accessible students
                $accessibleStudents = $user->getAccessibleStudents();
                return $accessibleStudents->where('id', $student->id)->exists();
                
            case 'edit':
            case 'update':
                // Registrar can edit all students
                if ($user->hasRole(['registrar', 'Registrar'])) {
                    return true;
                }
                // Advisor can edit their students
                if ($user->hasRole(['advisor', 'Advisor']) && $student->advisor_id === $user->id) {
                    return true;
                }
                return false;
                
            default:
                return false;
        }
    }

    /**
     * Check faculty permissions
     */
    protected function checkFacultyPermission(User $user, string $action, $faculty): bool
    {
        if ($faculty->user_type !== 'faculty') {
            return false;
        }

        switch ($action) {
            case 'view':
                // Can view faculty in same department or college
                if ($user->department_id === $faculty->department_id) {
                    return true;
                }
                if ($user->college_id && $user->college_id === $faculty->college_id) {
                    return true;
                }
                return false;
                
            case 'edit':
            case 'update':
                // HR can edit all faculty
                if ($user->hasRole(['hr', 'human-resources', 'HR'])) {
                    return true;
                }
                // Department head can edit faculty in their department
                if ($user->hasRole(['department-head', 'Department Head']) && 
                    $faculty->department_id === $user->department_id) {
                    return true;
                }
                return false;
                
            default:
                return false;
        }
    }

    /**
     * Check section permissions
     */
    protected function checkSectionPermission(User $user, string $action, $section): bool
    {
        switch ($action) {
            case 'view':
                // Can view if can view the course
                return $this->checkCoursePermission($user, 'view', $section->course);
                
            case 'edit':
            case 'update':
                // Instructor can edit their own section
                if ($section->instructor_id === $user->id) {
                    return true;
                }
                // Check if user can manage the course
                return $this->checkCoursePermission($user, 'edit', $section->course);
                
            default:
                return false;
        }
    }

    /**
     * Check department permissions
     */
    protected function checkDepartmentPermission(User $user, string $action, $department): bool
    {
        switch ($action) {
            case 'view':
                return $user->canAccessDepartment($department);
                
            case 'edit':
            case 'update':
            case 'manage':
                return $user->canManageDepartment($department);
                
            default:
                return false;
        }
    }

    /**
     * Log access attempt - simplified version
     */
    public function logAccess(User $user, string $action, string $entityType, $entityId, bool $allowed, string $reason = null): void
    {
        try {
            // Only log if ScopeAuditLog model exists
            if (!class_exists(\App\Models\ScopeAuditLog::class)) {
                return;
            }

            // Get user's current scope
            $scope = $user->getOrganizationalScope();
            $primaryScope = $scope[0] ?? null;

            ScopeAuditLog::create([
                'user_id' => $user->id,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'scope_type' => $primaryScope['type'] ?? 'unknown',
                'scope_id' => $primaryScope['id'] ?? 0,
                'was_allowed' => $allowed,
                'denial_reason' => !$allowed ? ($reason ?? 'Insufficient permissions') : null,
                'context' => [
                    'user_roles' => $user->roles->pluck('slug')->toArray(),
                    'user_scope' => $scope,
                    'timestamp' => now()->toIso8601String(),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'occurred_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail if logging doesn't work
            Log::warning('Could not log scope access: ' . $e->getMessage());
        }
    }

    /**
     * Get user's scope summary - simplified version
     */
    public function getUserScopeSummary(User $user): array
    {
        $summary = [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'roles' => $user->roles->pluck('name')->toArray(),
            ],
            'organizational_scope' => [],
            'leadership_positions' => [],
            'accessible_entities' => [
                'courses' => 0,
                'students' => 0,
                'faculty' => 0,
                'departments' => 0,
            ],
            'permissions_summary' => [],
        ];

        try {
            // Get organizational scope
            $summary['organizational_scope'] = $user->getOrganizationalScope();
            
            // Get leadership positions
            $summary['leadership_positions'] = $this->getUserLeadershipPositions($user);
            
            // Count accessible entities - with error handling
            try {
                $summary['accessible_entities']['courses'] = $user->getAccessibleCourses()->count();
            } catch (\Exception $e) {
                Log::debug('Could not count accessible courses: ' . $e->getMessage());
            }
            
            try {
                $summary['accessible_entities']['students'] = $user->getAccessibleStudents()->count();
            } catch (\Exception $e) {
                Log::debug('Could not count accessible students: ' . $e->getMessage());
            }
            
            try {
                $summary['accessible_entities']['faculty'] = $user->getManageableFaculty()->count();
            } catch (\Exception $e) {
                Log::debug('Could not count manageable faculty: ' . $e->getMessage());
            }
            
            try {
                if (class_exists(\App\Models\Department::class)) {
                    $summary['accessible_entities']['departments'] = Department::accessibleBy($user)->count();
                }
            } catch (\Exception $e) {
                Log::debug('Could not count accessible departments: ' . $e->getMessage());
            }
            
            // Get permissions summary
            $summary['permissions_summary'] = $this->getPermissionsSummary($user);
            
        } catch (\Exception $e) {
            Log::error('Error getting user scope summary: ' . $e->getMessage());
        }
        
        return $summary;
    }

    /**
     * Get user's leadership positions
     */
    protected function getUserLeadershipPositions(User $user): array
    {
        $positions = [];

        try {
            // Check college leadership
            if (class_exists(\App\Models\College::class)) {
                $colleges = College::where('dean_id', $user->id)
                    ->orWhere('associate_dean_id', $user->id)
                    ->get();
                
                foreach ($colleges as $college) {
                    if ($college->dean_id === $user->id) {
                        $positions[] = ['role' => 'Dean', 'entity' => 'College', 'name' => $college->name];
                    }
                    if (isset($college->associate_dean_id) && $college->associate_dean_id === $user->id) {
                        $positions[] = ['role' => 'Associate Dean', 'entity' => 'College', 'name' => $college->name];
                    }
                }
            }

            // Check school leadership
            if (class_exists(\App\Models\School::class)) {
                $schools = School::where('director_id', $user->id)->get();
                foreach ($schools as $school) {
                    $positions[] = ['role' => 'Director', 'entity' => 'School', 'name' => $school->name];
                }
            }

            // Check department leadership
            if (class_exists(\App\Models\Department::class)) {
                $departments = Department::where('head_id', $user->id)
                    ->orWhere('deputy_head_id', $user->id)
                    ->orWhere('secretary_id', $user->id)
                    ->get();
                
                foreach ($departments as $department) {
                    if ($department->head_id === $user->id) {
                        $positions[] = ['role' => 'Head', 'entity' => 'Department', 'name' => $department->name];
                    }
                    if ($department->deputy_head_id === $user->id) {
                        $positions[] = ['role' => 'Deputy Head', 'entity' => 'Department', 'name' => $department->name];
                    }
                    if ($department->secretary_id === $user->id) {
                        $positions[] = ['role' => 'Secretary', 'entity' => 'Department', 'name' => $department->name];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::debug('Error getting leadership positions: ' . $e->getMessage());
        }

        return $positions;
    }

    /**
     * Get permissions summary for user
     */
    protected function getPermissionsSummary(User $user): array
    {
        $summary = [
            'role_based' => [],
            'organizational' => [],
            'custom' => [],
        ];

        try {
            // Role-based permissions
            foreach ($user->roles as $role) {
                if ($role->permissions) {
                    $summary['role_based'][$role->name] = $role->permissions->pluck('slug')->toArray();
                }
            }

            // Organizational permissions (if relationship exists)
            if (method_exists($user, 'activeOrganizationalPermissions')) {
                $orgPerms = $user->activeOrganizationalPermissions()->get();
                foreach ($orgPerms as $perm) {
                    $summary['organizational'][] = [
                        'scope' => "{$perm->scope_type}:{$perm->scope_id}",
                        'permission' => $perm->permission_key,
                        'level' => $perm->access_level ?? 'default',
                    ];
                }
            }

            // Custom direct permissions (if relationship exists)
            if ($user->permissions) {
                foreach ($user->permissions as $perm) {
                    $summary['custom'][] = $perm->slug;
                }
            }
        } catch (\Exception $e) {
            Log::debug('Error getting permissions summary: ' . $e->getMessage());
        }

        return $summary;
    }

    /**
     * Clear scope cache for user
     */
    public function clearUserScopeCache(User $user): void
    {
        try {
            // Use Laravel's cache tags if available
            Cache::tags(['user-scope', "user-{$user->id}"])->flush();
            
            // Fallback to pattern-based clearing
            $pattern = "scope_permission:{$user->id}:*";
            
            // This works if using Redis
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                $keys = Cache::getRedis()->keys($pattern);
                foreach ($keys as $key) {
                    Cache::forget($key);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not clear user scope cache: ' . $e->getMessage());
        }
    }
}