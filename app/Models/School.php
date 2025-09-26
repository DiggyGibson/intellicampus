<?php

// app/Models/School.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class School extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description', 'college_id',
        'director_id', 'email', 'phone', 'website', 'location',
        'is_active', 'settings', 'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the college this school belongs to
     */
    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    /**
     * Get the director of the school
     */
    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    /**
     * Get departments under this school
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get all faculty members in this school
     */
    public function facultyMembers()
    {
        return User::where('school_id', $this->id)
                   ->where('user_type', 'faculty');
    }

    /**
     * Scope for active schools
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}