<?php

// ============================================================
// File: app/Models/AttendanceSession.php
// ============================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceSession extends Model
{
    protected $fillable = [
        'section_id',
        'session_date',
        'start_time',
        'end_time',
        'session_type',
        'location',
        'notes',
        'is_cancelled',
        'cancellation_reason',
        'attendance_taken',
        'marked_by',
        'marked_at'
    ];

    protected $casts = [
        'session_date' => 'date',
        'is_cancelled' => 'boolean',
        'attendance_taken' => 'boolean',
        'marked_at' => 'datetime'
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'session_id');
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function getPresentCountAttribute()
    {
        return $this->records()->where('status', 'present')->count();
    }

    public function getAbsentCountAttribute()
    {
        return $this->records()->where('status', 'absent')->count();
    }

    public function getLateCountAttribute()
    {
        return $this->records()->where('status', 'late')->count();
    }
}
