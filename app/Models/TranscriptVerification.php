<?php

// app/Models/TranscriptVerification.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranscriptVerification extends Model
{
    protected $fillable = [
        'student_id',
        'transcript_request_id',
        'verification_code',
        'type',
        'file_path',
        'generated_at',
        'expires_at',
        'generated_by',
        'verification_count',
        'last_verified_at',
        'metadata',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_verified_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function transcriptRequest()
    {
        return $this->belongsTo(TranscriptRequest::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function isValid()
    {
        return $this->expires_at->isFuture();
    }
}