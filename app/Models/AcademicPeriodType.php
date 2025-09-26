<?php

// File: app/Models/AcademicPeriodType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicPeriodType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'periods_per_year',
        'weeks_per_period',
        'instruction_weeks',
        'exam_weeks',
        'has_breaks',
        'break_configuration',
        'is_active'
    ];

    protected $casts = [
        'has_breaks' => 'boolean',
        'break_configuration' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Scope for active period types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}