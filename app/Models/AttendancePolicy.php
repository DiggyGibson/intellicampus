<?php

// ============================================================
// File: app/Models/AttendancePolicy.php
// ============================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendancePolicy extends Model
{
    protected $fillable = [
        'policy_name',
        'description',
        'max_absences',
        'max_late_arrivals',
        'late_threshold_minutes',
        'attendance_weight',
        'auto_fail_on_excess_absence',
        'auto_fail_threshold',
        'grade_penalties',
        'is_default',
        'is_active'
    ];

    protected $casts = [
        'attendance_weight' => 'decimal:2',
        'auto_fail_on_excess_absence' => 'boolean',
        'grade_penalties' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function sections()
    {
        return $this->hasMany(SectionAttendancePolicy::class, 'policy_id');
    }
}