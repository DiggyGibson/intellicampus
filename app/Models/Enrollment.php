<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'enrollments';

    protected $fillable = [
        'student_id',
        'section_id',
        'term_id',
        'enrollment_status',  // Using your actual column name
        'enrollment_date',
        'drop_date',
        'grade',
        'grade_points',
        'attendance_mode',
        'registration_date',
        'grade_option',
        'credits_attempted',
        'credits_earned',
        'dropped_at',
        'final_grade'
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'drop_date' => 'date',
        'registration_date' => 'date',
        'dropped_at' => 'datetime',
        'grade_points' => 'float',
        'credits_attempted' => 'float',
        'credits_earned' => 'float'
    ];

    // Status constants
    const STATUS_ENROLLED = 'enrolled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_WITHDRAWN = 'withdrawn';
    const STATUS_DROPPED = 'dropped';
    const STATUS_FAILED = 'failed';
    const STATUS_INCOMPLETE = 'incomplete';
    const STATUS_ACTIVE = 'active';

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function section()
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    public function term()
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('enrollment_status', [self::STATUS_ENROLLED, self::STATUS_ACTIVE]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('enrollment_status', self::STATUS_COMPLETED);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    public function scopeCurrentTerm($query)
    {
        $currentTermId = AcademicTerm::where('is_current', true)->value('id');
        return $query->where('term_id', $currentTermId);
    }
    
    // Helper method to get/set status using the actual column name
    public function getStatusAttribute()
    {
        return $this->enrollment_status;
    }
    
    public function setStatusAttribute($value)
    {
        $this->enrollment_status = $value;
    }
}