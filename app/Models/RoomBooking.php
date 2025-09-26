<?php

// File: app/Models/RoomBooking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomBooking extends Model
{
    protected $fillable = [
        'room_id',
        'booking_type',
        'event_name',
        'description',
        'booking_date',
        'start_time',
        'end_time',
        'expected_attendees',
        'requirements',
        'booked_by',
        'approved_by',
        'approved_at',
        'status',
        'cancellation_reason'
    ];

    protected $casts = [
        'booking_date' => 'date',
        'requirements' => 'array',
        'approved_at' => 'datetime'
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function bookedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'booked_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getFormattedTimeAttribute()
    {
        return sprintf(
            '%s - %s',
            date('g:i A', strtotime($this->start_time)),
            date('g:i A', strtotime($this->end_time))
        );
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary'
        ][$this->status] ?? 'info';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('booking_date', '>=', now())
            ->where('status', 'approved');
    }
}