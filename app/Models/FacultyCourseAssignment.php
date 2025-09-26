<?php

// app/Models/FacultyCourseAssignment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacultyCourseAssignment extends Model
{
    protected $fillable = [
        'faculty_id', 'course_id', 'assigned_by', 'assignment_type',
        'can_edit_content', 'can_manage_grades', 'can_view_all_sections',
        'effective_from', 'effective_until', 'is_active', 'notes'
    ];

    protected $casts = [
        'can_edit_content' => 'boolean',
        'can_manage_grades' => 'boolean',
        'can_view_all_sections' => 'boolean',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

    /**
     * Get the faculty member
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    /**
     * Get the course
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get who assigned this
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope for active assignments
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('effective_from', '<=', now())
                     ->where(function($q) {
                         $q->whereNull('effective_until')
                           ->orWhere('effective_until', '>=', now());
                     });
    }

    /**
     * Check if assignment grants specific permission
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = [
            'edit_content' => $this->can_edit_content,
            'manage_grades' => $this->can_manage_grades,
            'view_all_sections' => $this->can_view_all_sections,
        ];

        return $permissions[$permission] ?? false;
    }
}
