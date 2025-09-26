<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'grades';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'enrollment_id',
        'component_id',
        'points_earned',
        'max_points',
        'percentage',
        'letter_grade',
        'comments',
        'graded_by',
        'submitted_at',
        'is_final',
        'grade_status'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'points_earned' => 'decimal:2',
        'max_points' => 'decimal:2',
        'percentage' => 'decimal:2',
        'submitted_at' => 'datetime',
        'is_final' => 'boolean'
    ];

    /**
     * Get the enrollment that owns the grade.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the grade component.
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(GradeComponent::class, 'component_id');
    }

    /**
     * Get the grader (faculty member who entered the grade).
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Get the student through enrollment.
     */
    public function student()
    {
        return $this->hasOneThrough(Student::class, Enrollment::class, 'id', 'id', 'enrollment_id', 'student_id');
    }

    /**
     * Scope a query to only include final grades.
     */
    public function scopeFinal($query)
    {
        return $query->where('is_final', true);
    }

    /**
     * Scope a query to only include posted grades.
     */
    public function scopePosted($query)
    {
        return $query->where('grade_status', 'posted');
    }

    /**
     * Scope a query to only include pending grades.
     */
    public function scopePending($query)
    {
        return $query->where('grade_status', 'pending');
    }

    /**
     * Calculate the percentage based on points earned and max points.
     */
    public function calculatePercentage()
    {
        if ($this->max_points > 0) {
            $this->percentage = ($this->points_earned / $this->max_points) * 100;
            return $this->percentage;
        }
        return 0;
    }

    /**
     * Convert percentage to letter grade based on grading scale.
     */
    public function calculateLetterGrade()
    {
        $percentage = $this->percentage ?? $this->calculatePercentage();
        
        if ($percentage >= 93) return 'A';
        if ($percentage >= 90) return 'A-';
        if ($percentage >= 87) return 'B+';
        if ($percentage >= 83) return 'B';
        if ($percentage >= 80) return 'B-';
        if ($percentage >= 77) return 'C+';
        if ($percentage >= 73) return 'C';
        if ($percentage >= 70) return 'C-';
        if ($percentage >= 67) return 'D+';
        if ($percentage >= 63) return 'D';
        return 'F';
    }

    /**
     * Get the grade points based on letter grade.
     */
    public function getGradePoints()
    {
        $gradePoints = [
            'A'  => 4.0,
            'A-' => 3.7,
            'B+' => 3.3,
            'B'  => 3.0,
            'B-' => 2.7,
            'C+' => 2.3,
            'C'  => 2.0,
            'C-' => 1.7,
            'D+' => 1.3,
            'D'  => 1.0,
            'F'  => 0.0
        ];
        
        return $gradePoints[$this->letter_grade] ?? 0.0;
    }

    /**
     * Check if grade can be modified.
     */
    public function canBeModified()
    {
        return $this->grade_status !== 'posted' && !$this->is_final;
    }

    /**
     * Post the grade (make it official).
     */
    public function post()
    {
        $this->grade_status = 'posted';
        $this->is_final = true;
        $this->save();
    }
}