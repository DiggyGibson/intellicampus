<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'title',
        'description',
        'credits',
        'lecture_hours',
        'lab_hours',
        'tutorial_hours',
        'level',
        'department',  // String field for backward compatibility
        'department_id',  // Foreign key for proper relationship
        'division_id',
        'coordinator_id',
        'cross_listed_departments',
        'approved_by',
        'approved_at',
        'type',
        'grading_method',
        'learning_outcomes',
        'topics_covered',
        'assessment_methods',
        'textbooks',
        'course_fee',
        'lab_fee',
        'has_lab',
        'has_tutorial',
        'is_active',
        'min_enrollment',
        'max_enrollment',
        'metadata'
    ];

    protected $casts = [
        'credits' => 'integer',
        'lecture_hours' => 'integer',
        'lab_hours' => 'integer',
        'tutorial_hours' => 'integer',
        'level' => 'integer',
        'course_fee' => 'decimal:2',
        'lab_fee' => 'decimal:2',
        'has_lab' => 'boolean',
        'has_tutorial' => 'boolean',
        'is_active' => 'boolean',
        'min_enrollment' => 'integer',
        'max_enrollment' => 'integer',
        'metadata' => 'array',
        'cross_listed_departments' => 'array',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically sync department string with department relationship
        static::creating(function ($course) {
            self::syncDepartmentFields($course);
        });

        static::updating(function ($course) {
            self::syncDepartmentFields($course);
        });

        // Update department statistics after course is created
        static::created(function ($course) {
            if ($course->department_id) {
                $departmentModel = Department::find($course->department_id);
                if ($departmentModel && method_exists($departmentModel, 'updateCounts')) {
                    $departmentModel->updateCounts();
                }
            }
        });

        // Update department statistics after course is deleted
        static::deleted(function ($course) {
            if ($course->department_id) {
                $departmentModel = Department::find($course->department_id);
                if ($departmentModel && method_exists($departmentModel, 'updateCounts')) {
                    $departmentModel->updateCounts();
                }
            }
        });
    }

    /**
     * Sync department string and department_id fields
     */
    private static function syncDepartmentFields($course)
    {
        // If department_id is set but department string is empty, populate it
        if ($course->department_id && !$course->department) {
            $dept = Department::find($course->department_id);
            if ($dept) {
                $course->department = $dept->name;
            }
        }
        
        // If department string is set but department_id is empty, try to find and set it
        if ($course->department && !$course->department_id) {
            $dept = Department::where('name', $course->department)->first();
            if ($dept) {
                $course->department_id = $dept->id;
            }
        }
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get the department this course belongs to (relationship)
     */
    public function departmentRelation(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Get the division this course belongs to
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get the course coordinator
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    /**
     * Get who approved this course
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get faculty assignments for this course
     */
    public function facultyAssignments(): HasMany
    {
        return $this->hasMany(FacultyCourseAssignment::class);
    }

    /**
     * Get active faculty assignments
     */
    public function activeFacultyAssignments()
    {
        return $this->facultyAssignments()->where('is_active', true);
    }

    /**
     * Get assigned faculty members
     */
    public function assignedFaculty()
    {
        return User::whereIn('id', 
            $this->activeFacultyAssignments()->pluck('faculty_id')
        );
    }

    /**
     * Get departments where this course is cross-listed
     */
    public function crossListedDepartments()
    {
        if (empty($this->cross_listed_departments)) {
            return collect();
        }
        
        return Department::whereIn('id', $this->cross_listed_departments)->get();
    }

    /**
     * Get the prerequisites for this course
     */
    public function prerequisites()
    {
        return $this->belongsToMany(Course::class, 'course_prerequisites', 'course_id', 'prerequisite_id')
            ->withPivot('type', 'min_grade', 'notes', 'is_active')
            ->withTimestamps();
    }

    /**
     * Get courses that have this course as prerequisite
     */
    public function dependentCourses()
    {
        return $this->belongsToMany(Course::class, 'course_prerequisites', 'prerequisite_id', 'course_id')
            ->withPivot('type', 'min_grade', 'notes', 'is_active')
            ->withTimestamps();
    }

    /**
     * Get the programs that include this course
     */
    public function programs()
    {
        return $this->belongsToMany(AcademicProgram::class, 'program_courses', 'course_id', 'program_id')
            ->withPivot('requirement_type', 'year_level', 'semester', 'is_mandatory', 'alternative_group')
            ->withTimestamps();
    }

    /**
     * Get the sections for this course
     */
    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class);
    }

    /**
     * Get current term sections
     */
    public function currentSections()
    {
        return $this->hasMany(CourseSection::class)
            ->whereHas('term', function ($query) {
                $query->where('is_current', true);
            });
    }

    /**
     * Get active sections for a specific term
     */
    public function sectionsForTerm($termId)
    {
        return $this->hasMany(CourseSection::class)
            ->where('term_id', $termId)
            ->where('status', '!=', 'cancelled');
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================

    /**
     * Get department attribute (ensure consistency)
     */
    public function getDepartmentAttribute($value)
    {
        // If we have a department_id, always use the relationship name for consistency
        if ($this->department_id && $this->departmentRelation) {
            return $this->departmentRelation->name;
        }
        
        // Otherwise return the stored string value
        return $value;
    }

    /**
     * Get prerequisite courses only (not corequisites)
     */
    public function getPrerequisiteCoursesAttribute()
    {
        return $this->prerequisites()
            ->wherePivot('type', 'prerequisite')
            ->wherePivot('is_active', true)
            ->get();
    }

    /**
     * Get corequisite courses only
     */
    public function getCorequisiteCoursesAttribute()
    {
        return $this->prerequisites()
            ->wherePivot('type', 'corequisite')
            ->wherePivot('is_active', true)
            ->get();
    }

    /**
     * Get the total contact hours
     */
    public function getTotalContactHoursAttribute()
    {
        return $this->lecture_hours + $this->lab_hours + $this->tutorial_hours;
    }

    /**
     * Get the course code with title
     */
    public function getFullTitleAttribute()
    {
        return $this->code . ' - ' . $this->title;
    }

    // ========================================
    // AUTHORIZATION METHODS
    // ========================================

    /**
     * Check if a user can manage this course
     */
    public function canBeManagedBy(User $user): bool
    {
        // Admin can manage all courses
        if ($user->hasRole(['super-admin', 'admin', 'academic-admin'])) {
            return true;
        }

        // Course coordinator can manage
        if ($this->coordinator_id === $user->id) {
            return true;
        }

        // Department head can manage department courses
        if ($this->department_id) {
            $dept = $this->departmentRelation;
            if ($dept && ($dept->head_id === $user->id || $dept->deputy_head_id === $user->id)) {
                return true;
            }
        }

        // Dean can manage college courses
        if ($this->department_id) {
            $dept = $this->departmentRelation;
            if ($dept && $dept->college && $dept->college->dean_id === $user->id) {
                return true;
            }
        }

        // Check faculty assignments with management permissions
        $assignment = $this->facultyAssignments()
            ->where('faculty_id', $user->id)
            ->where('is_active', true)
            ->first();
        
        if ($assignment && in_array($assignment->assignment_type, ['coordinator', 'primary_instructor'])) {
            return true;
        }

        return false;
    }

    /**
     * Check if a user can view this course
     */
    public function canBeViewedBy(User $user): bool
    {
        // If user can manage, they can view
        if ($this->canBeManagedBy($user)) {
            return true;
        }

        // Faculty assigned to course can view
        if ($this->assignedFaculty()->where('users.id', $user->id)->exists()) {
            return true;
        }

        // Faculty teaching sections can view
        if ($this->sections()->where('instructor_id', $user->id)->exists()) {
            return true;
        }

        // Faculty in same department can view
        if ($user->department_id && $user->department_id === $this->department_id) {
            return true;
        }

        // Students enrolled in course sections can view
        if ($user->hasRole('student') && $user->student) {
            return $user->student->enrollments()
                ->whereHas('section', function($q) {
                    $q->where('course_id', $this->id);
                })
                ->exists();
        }

        return false;
    }

    /**
     * Check if course can be deleted by user
     */
    public function canBeDeletedBy(User $user): bool
    {
        // Only admin and department head can delete
        if ($user->hasRole(['super-admin', 'admin'])) {
            return true;
        }

        // Department head can delete if no active sections
        if ($this->department_id) {
            $dept = $this->departmentRelation;
            if ($dept && $dept->head_id === $user->id) {
                return $this->sections()->where('status', '!=', 'cancelled')->count() === 0;
            }
        }

        return false;
    }

    // ========================================
    // BUSINESS LOGIC METHODS
    // ========================================

    /**
     * Check if course has prerequisites
     */
    public function hasPrerequisites(): bool
    {
        return $this->prerequisites()
            ->wherePivot('type', 'prerequisite')
            ->wherePivot('is_active', true)
            ->exists();
    }

    /**
     * Check if course has corequisites
     */
    public function hasCorequisites(): bool
    {
        return $this->prerequisites()
            ->wherePivot('type', 'corequisite')
            ->wherePivot('is_active', true)
            ->exists();
    }

    /**
     * Check if course is approved
     */
    public function isApproved(): bool
    {
        return $this->approved_by !== null && $this->approved_at !== null;
    }

    /**
     * Approve the course
     */
    public function approve(User $approver): void
    {
        $this->update([
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    /**
     * Check if a student meets prerequisites
     */
    public function studentMeetsPrerequisites(Student $student): bool
    {
        if (!$this->hasPrerequisites()) {
            return true;
        }

        $prerequisites = $this->prerequisiteCourses;

        foreach ($prerequisites as $prereq) {
            // Check if student has completed the prerequisite course
            $completed = $student->enrollments()
                ->whereHas('section', function($q) use ($prereq) {
                    $q->where('course_id', $prereq->id);
                })
                ->where('enrollment_status', 'completed')
                ->first();

            if (!$completed) {
                return false;
            }

            // Check if student met minimum grade requirement
            $minGrade = $prereq->pivot->min_grade ?? 'D';
            if ($completed->grade && !$this->gradeMetMinimum($completed->grade, $minGrade)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if grade meets minimum requirement
     */
    private function gradeMetMinimum($grade, $minGrade): bool
    {
        $gradeOrder = [
            'F' => 0, 'D' => 1, 'D+' => 1.3, 'C-' => 1.7, 'C' => 2, 'C+' => 2.3, 
            'B-' => 2.7, 'B' => 3, 'B+' => 3.3, 'A-' => 3.7, 'A' => 4
        ];
        
        $gradeValue = $gradeOrder[$grade] ?? 0;
        $minGradeValue = $gradeOrder[$minGrade] ?? 0;
        
        return $gradeValue >= $minGradeValue;
    }

    /**
     * Get enrollment statistics for the course
     */
    public function getEnrollmentStatistics($termId = null): array
    {
        $query = $this->sections();
        
        if ($termId) {
            $query->where('term_id', $termId);
        }
        
        $sections = $query->get();
        
        return [
            'total_sections' => $sections->count(),
            'total_capacity' => $sections->sum('enrollment_capacity'),
            'total_enrolled' => $sections->sum('current_enrollment'),
            'total_waitlisted' => $sections->sum('current_waitlist'),
            'average_fill_rate' => $sections->count() > 0 ? $sections->avg(function($section) {
                return $section->enrollment_capacity > 0 
                    ? ($section->current_enrollment / $section->enrollment_capacity) * 100 
                    : 0;
            }) : 0,
            'sections_full' => $sections->filter(function($section) {
                return $section->current_enrollment >= $section->enrollment_capacity;
            })->count(),
        ];
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope to filter courses accessible by user
     */
    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->hasRole(['super-admin', 'admin'])) {
            return $query;
        }

        return $query->where(function($q) use ($user) {
            // Courses in user's department
            if ($user->department_id) {
                $q->where('department_id', $user->department_id);
            }

            // Courses user coordinates
            $q->orWhere('coordinator_id', $user->id);

            // Courses user is assigned to teach
            $assignedCourseIds = $this->facultyAssignments()
                ->where('faculty_id', $user->id)
                ->where('is_active', true)
                ->pluck('course_id');
            
            if ($assignedCourseIds->isNotEmpty()) {
                $q->orWhereIn('id', $assignedCourseIds);
            }

            // Courses user teaches sections of
            $sectionCourseIds = CourseSection::where('instructor_id', $user->id)
                ->pluck('course_id')
                ->unique();
            
            if ($sectionCourseIds->isNotEmpty()) {
                $q->orWhereIn('id', $sectionCourseIds);
            }
        });
    }

    /**
     * Scope for active courses
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for courses by department
     */
    public function scopeByDepartment(Builder $query, $department): Builder
    {
        if (is_numeric($department)) {
            return $query->where('department_id', $department);
        }
        return $query->where('department', $department);
    }

    /**
     * Scope for courses by level
     */
    public function scopeByLevel(Builder $query, $level): Builder
    {
        return $query->where('level', $level);
    }

    /**
     * Scope for courses by type
     */
    public function scopeByType(Builder $query, $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for approved courses
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereNotNull('approved_by')
                     ->whereNotNull('approved_at');
    }

    /**
     * Scope for unapproved courses
     */
    public function scopeUnapproved(Builder $query): Builder
    {
        return $query->where(function($q) {
            $q->whereNull('approved_by')
              ->orWhereNull('approved_at');
        });
    }

    /**
     * Scope for courses with available sections in a term
     */
    public function scopeWithAvailableSections(Builder $query, $termId): Builder
    {
        return $query->whereHas('sections', function($q) use ($termId) {
            $q->where('term_id', $termId)
              ->where('status', 'open')
              ->whereColumn('current_enrollment', '<', 'enrollment_capacity');
        });
    }
}