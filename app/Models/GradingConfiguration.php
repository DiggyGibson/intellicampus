<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingConfiguration extends Model
{
    protected $table = 'grading_configurations';
    
    protected $fillable = [
        'grading_system',
        'max_gpa',
        'passing_gpa',
        'probation_gpa',
        'honors_gpa',
        'high_honors_gpa',
        'use_plus_minus',
        'include_failed_in_gpa'
    ];
    
    protected $casts = [
        'use_plus_minus' => 'boolean',
        'include_failed_in_gpa' => 'boolean',
        'max_gpa' => 'decimal:2',
        'passing_gpa' => 'decimal:2',
        'probation_gpa' => 'decimal:2',
        'honors_gpa' => 'decimal:2',
        'high_honors_gpa' => 'decimal:2'
    ];
}