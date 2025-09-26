<?php

// File: app/Models/CalendarEvent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    protected $fillable = [
        'academic_calendar_id',
        'term_id',
        'event_type',
        'title',
        'description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'all_day',
        'is_holiday',
        'affects_classes',
        'visibility',
        'priority',
        'applicable_to'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'all_day' => 'boolean',
        'is_holiday' => 'boolean',
        'affects_classes' => 'boolean',
        'applicable_to' => 'array',
    ];

    /**
     * Get the calendar
     */
    public function calendar(): BelongsTo
    {
        return $this->belongsTo(AcademicCalendar::class, 'academic_calendar_id');
    }

    /**
     * Get the term
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    /**
     * Scope for public events
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope for events visible to a role
     */
    public function scopeVisibleTo($query, $role)
    {
        $visibilityLevels = ['public'];
        
        switch ($role) {
            case 'student':
                $visibilityLevels[] = 'students';
                break;
            case 'faculty':
                $visibilityLevels[] = 'faculty';
                break;
            case 'staff':
                $visibilityLevels[] = 'staff';
                break;
            case 'admin':
                return $query; // Admin sees all
        }
        
        return $query->whereIn('visibility', $visibilityLevels);
    }

    /**
     * Check if event is currently active
     */
    public function isActive()
    {
        $now = now();
        return $now->between($this->start_date, $this->end_date ?? $this->start_date);
    }

    /**
     * Get event color based on type
     */
    public function getColorAttribute()
    {
        $colors = [
            'holiday' => '#dc3545',        // red
            'deadline' => '#ffc107',       // yellow
            'exam_period' => '#6f42c1',    // purple
            'registration' => '#28a745',   // green
            'orientation' => '#17a2b8',    // cyan
            'graduation' => '#fd7e14',     // orange
            'break' => '#6c757d',          // gray
            'class_start' => '#007bff',    // blue
            'class_end' => '#007bff',      // blue
            'other' => '#343a40',          // dark
        ];
        
        return $colors[$this->event_type] ?? '#343a40';
    }
}
