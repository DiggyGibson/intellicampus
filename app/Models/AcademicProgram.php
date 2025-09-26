<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicProgram extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'level',
        'department',
        'faculty',
        'duration_years',
        'total_credits',
        'core_credits',
        'major_credits',
        'elective_credits',
        'min_gpa',
        'graduation_gpa',
        'description',
        'learning_outcomes',
        'career_prospects',
        'admission_requirements',
        'is_active',
        'accreditation_status',
        'accreditation_date',
        'next_review_date',
        'metadata'
    ];

    protected $casts = [
        'duration_years' => 'integer',
        'total_credits' => 'integer',
        'core_credits' => 'integer',
        'major_credits' => 'integer',
        'elective_credits' => 'integer',
        'min_gpa' => 'decimal:2',
        'graduation_gpa' => 'decimal:2',
        'is_active' => 'boolean',
        'accreditation_date' => 'date',
        'next_review_date' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Get the courses in this program.
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'program_courses')
            ->withPivot('requirement_type', 'year_level', 'semester', 'is_mandatory', 'alternative_group')
            ->withTimestamps();
    }

    /**
     * Get core courses.
     */
    public function coreCourses()
    {
        return $this->courses()->wherePivot('requirement_type', 'core');
    }

    /**
     * Get major courses.
     */
    public function majorCourses()
    {
        return $this->courses()->wherePivot('requirement_type', 'major');
    }

    /**
     * Get elective courses.
     */
    public function electiveCourses()
    {
        return $this->courses()->wherePivot('requirement_type', 'elective');
    }

    /**
     * Get courses for a specific year and semester.
     */
    public function coursesForYearSemester($year, $semester)
    {
        return $this->courses()
            ->wherePivot('year_level', $year)
            ->wherePivot('semester', $semester)
            ->get();
    }

    /**
     * Check if program is accredited.
     */
    public function isAccredited()
    {
        return !empty($this->accreditation_status) && 
               $this->accreditation_date && 
               $this->next_review_date && 
               $this->next_review_date->isFuture();
    }

    /**
     * Get students enrolled in this program.
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'program_id');
    }

    /**
     * Calculate completion percentage for a student.
     */
    public function calculateCompletionPercentage($completedCredits)
    {
        if ($this->total_credits == 0) {
            return 0;
        }
        return min(100, round(($completedCredits / $this->total_credits) * 100, 2));
    }

    /**
     * Scope for active programs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for programs by level.
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope for programs by department.
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Get the department relationship
     * Note: This uses the string 'department' field, not a foreign key
     */
    public function department()
    {
        // Since we use a string field, return the matching department
        return Department::where('code', $this->department)
            ->orWhere('name', $this->department)
            ->first();
    }

    /**
     * Get department as a relationship-like query
     * This is for compatibility with queries expecting a relationship
     */
    public function getDepartmentAttribute()
    {
        if ($this->attributes['department'] ?? null) {
            return Department::where('code', $this->attributes['department'])
                ->orWhere('name', $this->attributes['department'])
                ->first();
        }
        return null;
    }

}