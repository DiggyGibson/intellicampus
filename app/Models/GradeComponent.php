<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeComponent extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'grade_components';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'section_id',
        'name',
        'description',
        'weight',
        'max_points',
        'type',
        'due_date',
        'is_visible',
        'is_extra_credit',
        'drop_lowest',
        'category'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'weight' => 'decimal:2',
        'max_points' => 'decimal:2',
        'due_date' => 'date',
        'is_visible' => 'boolean',
        'is_extra_credit' => 'boolean',
        'drop_lowest' => 'integer'
    ];

    /**
     * Component types
     */
    const TYPES = [
        'exam' => 'Exam',
        'quiz' => 'Quiz',
        'assignment' => 'Assignment',
        'project' => 'Project',
        'participation' => 'Participation',
        'attendance' => 'Attendance',
        'presentation' => 'Presentation',
        'lab' => 'Lab Work',
        'homework' => 'Homework',
        'other' => 'Other'
    ];

    /**
     * Get the section that owns the component.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    /**
     * Get the grades for this component.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'component_id');
    }

    /**
     * Get the course through section.
     */
    public function course()
    {
        return $this->hasOneThrough(Course::class, CourseSection::class, 'id', 'id', 'section_id', 'course_id');
    }

    /**
     * Scope a query to only include visible components.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Scope a query to only include graded components (not extra credit).
     */
    public function scopeGraded($query)
    {
        return $query->where('is_extra_credit', false);
    }

    /**
     * Scope a query to only include extra credit components.
     */
    public function scopeExtraCredit($query)
    {
        return $query->where('is_extra_credit', true);
    }

    /**
     * Scope a query to get components by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to get components by category.
     */
    public function scopeInCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the average score for this component.
     */
    public function getAverageScore()
    {
        return $this->grades()
            ->whereNotNull('points_earned')
            ->avg('points_earned');
    }

    /**
     * Get the highest score for this component.
     */
    public function getHighestScore()
    {
        return $this->grades()
            ->whereNotNull('points_earned')
            ->max('points_earned');
    }

    /**
     * Get the lowest score for this component.
     */
    public function getLowestScore()
    {
        return $this->grades()
            ->whereNotNull('points_earned')
            ->min('points_earned');
    }

    /**
     * Check if component is past due.
     */
    public function isPastDue()
    {
        return $this->due_date && $this->due_date->isPast();
    }

    /**
     * Check if component is upcoming (due within 7 days).
     */
    public function isUpcoming()
    {
        if (!$this->due_date) return false;
        
        $now = now();
        return $this->due_date->isAfter($now) && 
               $this->due_date->diffInDays($now) <= 7;
    }

    /**
     * Get completion percentage for this component.
     */
    public function getCompletionPercentage()
    {
        $totalStudents = $this->section->enrollments()->count();
        if ($totalStudents == 0) return 0;
        
        $gradedStudents = $this->grades()->whereNotNull('points_earned')->count();
        return round(($gradedStudents / $totalStudents) * 100, 2);
    }

    /**
     * Calculate weighted contribution to final grade.
     */
    public function getWeightedContribution($points_earned)
    {
        if ($this->max_points == 0) return 0;
        
        $percentage = ($points_earned / $this->max_points) * 100;
        return ($percentage * $this->weight) / 100;
    }

    /**
     * Validate that total weights don't exceed 100%.
     */
    public static function validateWeights($sectionId, $excludeId = null)
    {
        $query = static::where('section_id', $sectionId)
            ->where('is_extra_credit', false);
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $totalWeight = $query->sum('weight');
        return $totalWeight <= 100;
    }
}