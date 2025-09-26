<?php

// app/Models/TranscriptPayment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranscriptPayment extends Model
{
    protected $fillable = [
        'transcript_request_id',
        'amount',
        'payment_method',
        'reference_number',
        'transaction_id',
        'status',
        'paid_at',
        'refunded_at',
        'refund_amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function transcriptRequest()
    {
        return $this->belongsTo(TranscriptRequest::class);
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function markAsPaid()
    {
        $this->update([
            'status' => 'completed',
            'paid_at' => now(),
        ]);
        
        // Update the request payment status
        $this->transcriptRequest->update([
            'payment_status' => 'paid',
            'payment_date' => now(),
        ]);
    }
}