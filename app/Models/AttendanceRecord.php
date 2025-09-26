<?php

// ============================================================
// File: app/Models/AttendanceRecord.php
// ============================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'session_id',
        'student_id',
        'status',
        'check_in_time',
        'check_out_time',
        'minutes_late',
        'remarks',
        'excuse_document',
        'excuse_verified',
        'verified_by',
        'verified_at'
    ];

    protected $casts = [
        'excuse_verified' => 'boolean',
        'verified_at' => 'datetime'
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceSession::class, 'session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getStatusColorAttribute()
    {
        return [
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'excused' => 'info',
            'sick' => 'info',
            'left_early' => 'warning'
        ][$this->status] ?? 'secondary';
    }

    public function getStatusLabelAttribute()
    {
        return [
            'present' => 'Present',
            'absent' => 'Absent',
            'late' => 'Late',
            'excused' => 'Excused',
            'sick' => 'Sick',
            'left_early' => 'Left Early'
        ][$this->status] ?? 'Unknown';
    }
}