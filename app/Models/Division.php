<?php

// app/Models/Division.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Division extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'description', 'department_id',
        'coordinator_id', 'is_active', 'settings', 'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the department this division belongs to
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the coordinator
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    /**
     * Get users in this division
     */
    public function users()
    {
        return User::where('division_id', $this->id);
    }

    /**
     * Get courses managed by this division
     */
    public function courses()
    {
        return Course::where('division_id', $this->id);
    }
}