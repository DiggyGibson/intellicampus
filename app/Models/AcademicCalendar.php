<?php

// File: app/Models/AcademicCalendar.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicCalendar extends Model
{
    protected $fillable = [
        'name',
        'academic_year',
        'year_start',
        'year_end',
        'is_active',
        'description',
        'metadata'
    ];

    protected $casts = [
        'year_start' => 'date',
        'year_end' => 'date',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get calendar events
     */
    public function events(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    /**
     * Get academic terms
     */
    public function terms(): HasMany
    {
        return $this->hasMany(AcademicTerm::class);
    }

    /**
     * Scope for active calendar
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get holidays
     */
    public function holidays()
    {
        return $this->events()->where('is_holiday', true);
    }

    /**
     * Get important dates
     */
    public function importantDates()
    {
        return $this->events()
            ->whereIn('priority', ['high', 'critical'])
            ->orderBy('start_date');
    }
}
