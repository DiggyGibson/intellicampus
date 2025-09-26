<?php

// File: app/Models/Room.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'building_id',
        'room_code',
        'room_name',
        'room_type',
        'capacity',
        'exam_capacity',
        'equipment',
        'software',
        'is_accessible',
        'has_ac',
        'has_projector',
        'has_computers',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'equipment' => 'array',
        'software' => 'array',
        'is_accessible' => 'boolean',
        'has_ac' => 'boolean',
        'has_projector' => 'boolean',
        'has_computers' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function availability(): HasMany
    {
        return $this->hasMany(RoomAvailability::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(RoomBooking::class);
    }

    public function getFullCodeAttribute()
    {
        return $this->room_code;
    }

    public function getDisplayNameAttribute()
    {
        return "{$this->room_code} - {$this->room_name}";
    }

    public function isAvailable($day, $startTime, $endTime)
    {
        // Check if room has any schedules at this time
        return !$this->schedules()
            ->where('day_of_week', $day)
            ->where('is_active', true)
            ->where(function($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            })
            ->exists();
    }
}