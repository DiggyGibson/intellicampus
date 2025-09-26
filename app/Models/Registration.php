<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'registrations';

    protected $fillable = [
        'student_id',
        'section_id',
        'term_id',
        'registration_date',
        'status',
        'grade',
        'grade_points',
        'credits_attempted',
        'credits_earned',
        'registration_type',
        'midterm_grade',
        'final_grade',
        'grade_submission_date',
        'graded_by',
        'dropped_date',
        'withdrawn_date',
        'completion_status',
        'notes'
    ];

    protected $casts = [
        'registration_date' => 'datetime',
        'grade_submission_date' => 'datetime',
        'dropped_date' => 'datetime',
        'withdrawn_date' => 'datetime',
        'credits_attempted' => 'decimal:2',
        'credits_earned' => 'decimal:2',
        'grade_points' => 'decimal:2'
    ];

    /**
     * Registration statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ENROLLED = 'enrolled';
    const STATUS_WAITLISTED = 'waitlisted';
    const STATUS_DROPPED = 'dropped';
    const STATUS_WITHDRAWN = 'withdrawn';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_INCOMPLETE = 'incomplete';
    const STATUS_AUDIT = 'audit';

    /**
     * Get the student for this registration.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the section for this registration.
     */
    public function section()
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    /**
     * Get the term for this registration.
     */
    public function term()
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    /**
     * Get the faculty member who graded this registration.
     */
    public function gradedBy()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Scope for active enrollments.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ENROLLED);
    }

    /**
     * Scope for current term.
     */
    public function scopeCurrentTerm($query)
    {
        $currentTermId = AcademicTerm::where('is_current', true)->first()?->id;
        return $query->where('term_id', $currentTermId);
    }

    /**
     * Check if registration is active.
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ENROLLED;
    }

    /**
     * Check if registration can be dropped.
     */
    public function canBeDropped()
    {
        return in_array($this->status, [self::STATUS_ENROLLED, self::STATUS_WAITLISTED]);
    }

    /**
     * Calculate grade points for this registration.
     */
    public function calculateGradePoints()
    {
        $gradeValues = [
            'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
            'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
            'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
            'D+' => 1.3, 'D' => 1.0, 'D-' => 0.7,
            'F' => 0.0
        ];

        if ($this->final_grade && isset($gradeValues[$this->final_grade])) {
            $this->grade_points = $gradeValues[$this->final_grade] * $this->credits_attempted;
            $this->save();
        }
    }
}