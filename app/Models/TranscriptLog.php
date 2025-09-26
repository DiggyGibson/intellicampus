<?php

// app/Models/TranscriptLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranscriptLog extends Model
{
    protected $fillable = [
        'student_id',
        'transcript_request_id',
        'action',
        'type',
        'purpose',
        'performed_by',
        'ip_address',
        'user_agent',
        'metadata',
        'performed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'performed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function transcriptRequest()
    {
        return $this->belongsTo(TranscriptRequest::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}