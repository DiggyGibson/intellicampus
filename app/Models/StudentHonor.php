<?php

// app/Models/StudentHonor.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentHonor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'term_id',
        'honor_type',
        'honor_name',
        'description',
        'academic_year',
        'awarded_date',
        'awarded_by',
        'gpa_earned',
        'metadata',
    ];

    protected $casts = [
        'awarded_date' => 'date',
        'gpa_earned' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function term()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function getFormattedTypeAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->honor_type));
    }
}