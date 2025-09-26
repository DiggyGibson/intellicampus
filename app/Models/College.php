<?php

// app/Models/College.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class College extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description', 'type',
        'dean_id', 'associate_dean_id',
        'email', 'phone', 'website', 'building', 'office',
        'is_active', 'established_date', 'settings', 'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'established_date' => 'date',
        'settings' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the dean of the college
     */
    public function dean(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dean_id');
    }

    /**
     * Get the associate dean
     */
    public function associateDean(): BelongsTo
    {
        return $this->belongsTo(User::class, 'associate_dean_id');
    }

    /**
     * Get schools under this college
     */
    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }

    /**
     * Get departments directly under this college
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get all departments (including through schools)
     */
    public function allDepartments()
    {
        $directDepartments = $this->departments();
        $schoolDepartments = Department::whereIn('school_id', 
            $this->schools()->pluck('id')
        );
        
        return $directDepartments->union($schoolDepartments);
    }

    /**
     * Get all faculty members in this college
     */
    public function facultyMembers()
    {
        return User::where('college_id', $this->id)
                   ->where('user_type', 'faculty');
    }

    /**
     * Get all students in programs under this college
     */
    public function students()
    {
        return Student::whereHas('program.department', function($query) {
            $query->where('college_id', $this->id)
                  ->orWhereIn('school_id', $this->schools()->pluck('id'));
        });
    }

    /**
     * Get all courses offered by this college
     */
    public function courses()
    {
        return Course::whereHas('department', function($query) {
            $query->where('college_id', $this->id)
                  ->orWhereIn('school_id', $this->schools()->pluck('id'));
        });
    }

    /**
     * Scope for active colleges
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a user has administrative rights in this college
     */
    public function hasAdministrator(User $user): bool
    {
        return $this->dean_id === $user->id || 
               $this->associate_dean_id === $user->id;
    }
}
