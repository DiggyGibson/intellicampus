<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'crn', 'course_id', 'term_id', 'section_number', 'instructor_id',
        'delivery_mode', 'enrollment_capacity', 'current_enrollment',
        'waitlist_capacity', 'current_waitlist', 'status',
        'days_of_week', 'start_time', 'end_time', 'room', 'building', 'campus',
        'online_meeting_url', 'online_meeting_password', 'auto_record',
        'section_notes', 'instructor_notes', 'additional_fee',
        'start_date', 'end_date', 'metadata'
    ];

    protected $casts = [
        'enrollment_capacity' => 'integer',
        'current_enrollment' => 'integer',
        'waitlist_capacity' => 'integer',
        'current_waitlist' => 'integer',
        'auto_record' => 'boolean',
        'additional_fee' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'metadata' => 'array',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function term()
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function schedules()
    {
        return $this->hasMany(SectionSchedule::class, 'section_id');
    }

    public function getAvailableSeatsAttribute()
    {
        return max(0, $this->enrollment_capacity - $this->current_enrollment);
    }

    public function isFull()
    {
        return $this->current_enrollment >= $this->enrollment_capacity;
    }

    public function hasWaitlist()
    {
        return $this->waitlist_capacity > 0;
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function enrollments()
    {
        return $this->hasMany(\App\Models\Enrollment::class, 'section_id');
    }
}