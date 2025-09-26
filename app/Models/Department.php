<?php

// app/Models/Department.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description', 'type',
        'college_id', 'school_id', 'parent_department_id',
        'head_id', 'deputy_head_id', 'secretary_id',
        'email', 'phone', 'fax', 'website', 'building', 'office',
        'faculty_count', 'student_count', 'course_count', 'program_count',
        'annual_budget', 'budget_code',
        'is_active', 'accepts_students', 'offers_courses',
        'established_date', 'settings', 'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'accepts_students' => 'boolean',
        'offers_courses' => 'boolean',
        'established_date' => 'date',
        'annual_budget' => 'decimal:2',
        'settings' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the college this department belongs to
     */
    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    /**
     * Get the school this department belongs to
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get parent department for sub-departments
     */
    public function parentDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_department_id');
    }

    /**
     * Get sub-departments
     */
    public function subDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_department_id');
    }

    /**
     * Get department head
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    /**
     * Get deputy head
     */
    public function deputyHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deputy_head_id');
    }

    /**
     * Get secretary
     */
    public function secretary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secretary_id');
    }

    /**
     * Get divisions under this department
     */
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    /**
     * Get users primarily affiliated with this department
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all affiliations (primary and secondary)
     */
    public function affiliations(): HasMany
    {
        return $this->hasMany(UserDepartmentAffiliation::class);
    }

    /**
     * Get faculty members (primary and affiliated)
     */
    public function facultyMembers()
    {
        return User::where(function($query) {
            $query->where('department_id', $this->id)
                  ->where('user_type', 'faculty');
        })->orWhereHas('departmentAffiliations', function($query) {
            $query->where('department_id', $this->id)
                  ->where('is_active', true);
        });
    }

    /**
     * Get courses offered by this department
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Get academic programs offered by this department
     */
    public function programs(): HasMany
    {
        return $this->hasMany(AcademicProgram::class);
    }

    /**
     * Get students in this department's programs
     */
    public function students()
    {
        return Student::whereHas('program', function($query) {
            $query->where('department_id', $this->id);
        })->orWhere('department', $this->code)
          ->orWhere('department', $this->name);
    }

    /**
     * CRITICAL: Scope to get departments accessible by a user
     * This method is required by ScopeManagementService
     */
    public function scopeAccessibleBy($query, User $user)
    {
        // Super admin sees all departments
        if ($user->hasRole(['super-administrator', 'Super Administrator', 'super-admin', 'admin'])) {
            return $query;
        }
        
        // Dean sees all departments in their college
        if ($user->hasRole(['dean', 'Dean']) && $user->college_id) {
            return $query->where('college_id', $user->college_id);
        }
        
        // School director sees departments in their school
        if (($user->organizational_role === 'director' || $user->hasRole('director')) && $user->school_id) {
            return $query->where('school_id', $user->school_id);
        }
        
        // Department head sees their department and sub-departments
        if ($user->hasRole(['department-head', 'Department Head']) && $user->department_id) {
            return $query->where(function($q) use ($user) {
                $q->where('id', $user->department_id)
                  ->orWhere('parent_department_id', $user->department_id);
            });
        }
        
        // Faculty/staff see departments they're affiliated with
        if ($user->department_id) {
            // Get all department IDs the user is affiliated with
            $departmentIds = collect([$user->department_id]);
            
            // Add affiliated departments if relationship exists
            if (method_exists($user, 'departmentAffiliations')) {
                $affiliatedDeptIds = $user->departmentAffiliations()
                    ->where('is_active', true)
                    ->pluck('department_id');
                $departmentIds = $departmentIds->merge($affiliatedDeptIds);
            }
            
            // Add secondary departments from JSON field
            if ($user->secondary_departments && is_array($user->secondary_departments)) {
                $departmentIds = $departmentIds->merge($user->secondary_departments);
            }
            
            return $query->whereIn('id', $departmentIds->unique());
        }
        
        // Default: no access to any departments
        return $query->whereRaw('1 = 0');
    }

    /**
     * Update department statistics
     */
    public function updateCounts()
    {
        // Safely count related entities with null checks
        $facultyCount = 0;
        $studentCount = 0;
        $courseCount = 0;
        $programCount = 0;

        try {
            // Count faculty
            $facultyCount = User::where('department_id', $this->id)
                ->where('user_type', 'faculty')
                ->count();
            
            // Add affiliated faculty
            if (class_exists(\App\Models\UserDepartmentAffiliation::class)) {
                $facultyCount += UserDepartmentAffiliation::where('department_id', $this->id)
                    ->where('is_active', true)
                    ->distinct('user_id')
                    ->count('user_id');
            }

            // Count students through programs
            if (class_exists(\App\Models\Student::class)) {
                $studentCount = Student::whereHas('program', function($q) {
                    $q->where('department_id', $this->id);
                })->count();
            }

            // Count active courses
            if (class_exists(\App\Models\Course::class)) {
                $courseCount = $this->courses()->where('is_active', true)->count();
            }

            // Count active programs
            if (class_exists(\App\Models\AcademicProgram::class)) {
                $programCount = $this->programs()->where('is_active', true)->count();
            }
        } catch (\Exception $e) {
            \Log::error('Error updating department counts: ' . $e->getMessage());
        }

        $this->update([
            'faculty_count' => $facultyCount,
            'student_count' => $studentCount,
            'course_count' => $courseCount,
            'program_count' => $programCount,
        ]);
    }

    /**
     * Scope for active departments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for academic departments
     */
    public function scopeAcademic($query)
    {
        return $query->where('type', 'academic');
    }

    /**
     * Scope for departments that offer courses
     */
    public function scopeOffersCourses($query)
    {
        return $query->where('offers_courses', true);
    }

    /**
     * Scope for departments that accept students
     */
    public function scopeAcceptsStudents($query)
    {
        return $query->where('accepts_students', true);
    }

    /**
     * Check if user has administrative role in department
     */
    public function hasAdministrator(User $user): bool
    {
        return $this->head_id === $user->id || 
               $this->deputy_head_id === $user->id ||
               $this->secretary_id === $user->id;
    }

    /**
     * Check if a user can manage this department
     */
    public function canBeManagedBy(User $user): bool
    {
        // Super admin can manage all
        if ($user->hasRole(['super-administrator', 'Super Administrator', 'super-admin', 'admin'])) {
            return true;
        }

        // Department administrators can manage
        if ($this->hasAdministrator($user)) {
            return true;
        }

        // Dean can manage departments in their college
        if ($user->hasRole(['dean', 'Dean']) && $this->college_id === $user->college_id) {
            return true;
        }

        // School director can manage departments in their school
        if ($user->organizational_role === 'director' && $this->school_id === $user->school_id) {
            return true;
        }

        return false;
    }

    /**
     * Get the organizational hierarchy path
     */
    public function getHierarchyPath(): array
    {
        $path = [];
        
        // Add parent department path if exists
        if ($this->parentDepartment) {
            $path = $this->parentDepartment->getHierarchyPath();
        }
        
        // Add school/college to path
        if ($this->school_id && $this->school) {
            if ($this->school->college) {
                array_unshift($path, [
                    'type' => 'college', 
                    'id' => $this->school->college_id, 
                    'name' => $this->school->college->name
                ]);
            }
            $path[] = [
                'type' => 'school', 
                'id' => $this->school_id, 
                'name' => $this->school->name
            ];
        } elseif ($this->college_id && $this->college) {
            $path[] = [
                'type' => 'college', 
                'id' => $this->college_id, 
                'name' => $this->college->name
            ];
        }
        
        // Add this department
        $path[] = [
            'type' => 'department', 
            'id' => $this->id, 
            'name' => $this->name
        ];
        
        return $path;
    }

    /**
     * Get a summary of department statistics
     */
    public function getStatsSummary(): array
    {
        return [
            'faculty_count' => $this->faculty_count ?? 0,
            'student_count' => $this->student_count ?? 0,
            'course_count' => $this->course_count ?? 0,
            'program_count' => $this->program_count ?? 0,
            'active_sections' => $this->courses()
                ->join('course_sections', 'courses.id', '=', 'course_sections.course_id')
                ->where('course_sections.status', 'active')
                ->count(),
        ];
    }
}