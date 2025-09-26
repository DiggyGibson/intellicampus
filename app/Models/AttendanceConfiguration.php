<?php

// File: app/Models/AttendanceConfiguration.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AttendanceConfiguration extends Model
{
    protected $fillable = [
        'track_attendance',
        'max_absences_allowed',
        'attendance_weight_in_grade',
        'attendance_calculation_method',
        'attendance_rules',
        'notify_on_absence',
        'absence_notification_threshold'
    ];

    protected $casts = [
        'track_attendance' => 'boolean',
        'attendance_rules' => 'array',
        'notify_on_absence' => 'boolean',
        'attendance_weight_in_grade' => 'decimal:2',
    ];

    /**
     * Get cached configuration
     */
    public static function getCached()
    {
        return Cache::remember('attendance_configuration', 3600, function () {
            return self::first();
        });
    }

    /**
     * Calculate attendance percentage
     */
    public function calculateAttendancePercentage($present, $total)
    {
        if ($total == 0) return 0;
        
        return round(($present / $total) * 100, 2);
    }

    /**
     * Check if student exceeded absence limit
     */
    public function hasExceededAbsenceLimit($absences)
    {
        if (!$this->max_absences_allowed) {
            return false;
        }
        
        return $absences > $this->max_absences_allowed;
    }

    /**
     * Check if notification should be sent
     */
    public function shouldNotify($absences)
    {
        return $this->notify_on_absence && 
               $absences >= $this->absence_notification_threshold;
    }
}