<?php

// File: app/Models/TimeSlot.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $fillable = [
        'slot_name',
        'start_time',
        'end_time',
        'duration_minutes',
        'slot_type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function getFormattedTimeAttribute()
    {
        return sprintf(
            '%s - %s',
            date('g:i A', strtotime($this->start_time)),
            date('g:i A', strtotime($this->end_time))
        );
    }

    public function scopeRegular($query)
    {
        return $query->where('slot_type', 'regular');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
