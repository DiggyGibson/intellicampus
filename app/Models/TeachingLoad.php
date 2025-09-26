<?php

// File: app/Models/TeachingLoad.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeachingLoad extends Model
{
    protected $fillable = [
        'faculty_id',
        'term_id',
        'min_credit_hours',
        'max_credit_hours',
        'current_credit_hours',
        'max_courses',
        'current_courses',
        'max_preparations',
        'preferred_times',
        'blocked_times',
        'can_teach_evening',
        'can_teach_weekend',
        'can_teach_online'
    ];

    protected $casts = [
        'preferred_times' => 'array',
        'blocked_times' => 'array',
        'can_teach_evening' => 'boolean',
        'can_teach_weekend' => 'boolean',
        'can_teach_online' => 'boolean'
    ];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    public function isOverloaded()
    {
        return $this->current_credit_hours > $this->max_credit_hours;
    }

    public function isUnderloaded()
    {
        return $this->current_credit_hours < $this->min_credit_hours;
    }

    public function getLoadStatusAttribute()
    {
        if ($this->isOverloaded()) {
            return 'overloaded';
        } elseif ($this->isUnderloaded()) {
            return 'underloaded';
        }
        return 'normal';
    }

    public function getLoadPercentageAttribute()
    {
        if ($this->max_credit_hours == 0) {
            return 0;
        }
        return round(($this->current_credit_hours / $this->max_credit_hours) * 100, 1);
    }
}