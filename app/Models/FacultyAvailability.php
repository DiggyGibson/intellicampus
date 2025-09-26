<?php

// File: app/Models/FacultyAvailability.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacultyAvailability extends Model
{
    protected $table = 'faculty_availability';

    protected $fillable = [
        'faculty_id',
        'term_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
        'preference_level',
        'notes'
    ];

    protected $casts = [
        'is_available' => 'boolean'
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    public function getFormattedTimeAttribute()
    {
        return sprintf(
            '%s - %s',
            date('g:i A', strtotime($this->start_time)),
            date('g:i A', strtotime($this->end_time))
        );
    }

    public function getPreferenceColorAttribute()
    {
        return [
            'preferred' => 'success',
            'neutral' => 'secondary',
            'avoid' => 'warning'
        ][$this->preference_level] ?? 'info';
    }
}