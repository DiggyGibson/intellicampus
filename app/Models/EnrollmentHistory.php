<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentHistory extends Model
{
    protected $fillable = [
        'student_id',
        'action_type',
        'from_status',
        'to_status',
        'reason',
        'notes',
        'effective_date',
        'end_date',
        'approved_by',
        'approved_at',
        'created_by',
        'supporting_documents',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'supporting_documents' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function getStatusBadgeColorAttribute()
    {
        return match($this->to_status) {
            'active' => 'green',
            'inactive' => 'gray',
            'suspended' => 'red',
            'graduated' => 'blue',
            'withdrawn' => 'orange',
            'leave' => 'yellow',
            default => 'gray'
        };
    }
}