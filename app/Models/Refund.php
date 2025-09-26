<?php

// ========================================
// app/Models/Refund.php
// ========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'refund_number',
        'payment_id',
        'student_id',
        'amount',
        'reason',
        'detailed_reason',
        'type',
        'status',
        'refund_method',
        'gateway_refund_id',
        'gateway_response',
        'requested_date',
        'approved_date',
        'processed_date',
        'requested_by',
        'approved_by',
        'processed_by',
        'rejection_reason'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'requested_date' => 'date',
        'approved_date' => 'date',
        'processed_date' => 'date'
    ];

    // Relationships
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function canBeProcessed()
    {
        return in_array($this->status, ['pending', 'approved']);
    }

    public function approve($userId)
    {
        $this->status = 'approved';
        $this->approved_by = $userId;
        $this->approved_date = now();
        $this->save();
    }

    public function reject($userId, $reason)
    {
        $this->status = 'rejected';
        $this->approved_by = $userId;
        $this->rejection_reason = $reason;
        $this->approved_date = now();
        $this->save();
    }

    public function markAsProcessed($userId, $gatewayRefundId = null)
    {
        $this->status = 'completed';
        $this->processed_by = $userId;
        $this->processed_date = now();
        if ($gatewayRefundId) {
            $this->gateway_refund_id = $gatewayRefundId;
        }
        $this->save();
    }
}
