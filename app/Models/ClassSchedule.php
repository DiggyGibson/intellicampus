<?php
// File: app/Models/ClassSchedule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassSchedule extends Model
{
    protected $fillable = [
        'section_id',
        'room_id',
        'instructor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'schedule_type',
        'effective_from',
        'effective_until',
        'is_recurring',
        'recurrence_pattern',
        'is_online',
        'online_link',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_recurring' => 'boolean',
        'is_online' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function conflicts(): HasMany
    {
        return $this->hasMany(ScheduleConflict::class, 'schedule_1_id')
            ->orWhere('schedule_2_id', $this->id);
    }

    public function changes(): HasMany
    {
        return $this->hasMany(ScheduleChange::class, 'original_schedule_id');
    }

    public function getDayNumberAttribute()
    {
        $days = [
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7
        ];
        
        return $days[$this->day_of_week] ?? 0;
    }

    public function getFormattedTimeAttribute()
    {
        return sprintf(
            '%s - %s',
            date('g:i A', strtotime($this->start_time)),
            date('g:i A', strtotime($this->end_time))
        );
    }

    public function getLocationAttribute()
    {
        if ($this->is_online) {
            return 'Online';
        }
        
        return $this->room ? $this->room->room_code : 'TBA';
    }
}