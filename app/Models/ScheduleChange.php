<?php

// File: app/Models/ScheduleChange.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleChange extends Model
{
    protected $fillable = [
        'original_schedule_id',
        'change_type',
        'change_date',
        'original_start_time',
        'original_end_time',
        'original_room_id',
        'new_start_time',
        'new_end_time',
        'new_room_id',
        'new_instructor_id',
        'reason',
        'is_permanent',
        'notification_sent',
        'requested_by',
        'approved_by',
        'approved_at',
        'status'
    ];

    protected $casts = [
        'change_date' => 'date',
        'is_permanent' => 'boolean',
        'notification_sent' => 'boolean',
        'approved_at' => 'datetime'
    ];

    public function originalSchedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'original_schedule_id');
    }

    public function originalRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'original_room_id');
    }

    public function newRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'new_room_id');
    }

    public function newInstructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_instructor_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger'
        ][$this->status] ?? 'secondary';
    }
}
