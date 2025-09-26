<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequirementCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'display_order',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer'
    ];

    /**
     * Category types
     */
    const TYPE_UNIVERSITY = 'university';
    const TYPE_GENERAL_EDUCATION = 'general_education';
    const TYPE_MAJOR = 'major';
    const TYPE_MINOR = 'minor';
    const TYPE_CONCENTRATION = 'concentration';
    const TYPE_ELECTIVE = 'elective';
    const TYPE_OTHER = 'other';

    /**
     * Get all requirements in this category
     */
    public function requirements(): HasMany
    {
        return $this->hasMany(DegreeRequirement::class, 'category_id');
    }

    /**
     * Get active requirements in this category
     */
    public function activeRequirements(): HasMany
    {
        return $this->requirements()->where('is_active', true);
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope ordered by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Check if this is a major requirement category
     */
    public function isMajorCategory(): bool
    {
        return $this->type === self::TYPE_MAJOR;
    }

    /**
     * Check if this is a general education category
     */
    public function isGeneralEducation(): bool
    {
        return $this->type === self::TYPE_GENERAL_EDUCATION;
    }

    /**
     * Check if this is a university requirement category
     */
    public function isUniversityRequirement(): bool
    {
        return $this->type === self::TYPE_UNIVERSITY;
    }

    /**
     * Get the total credits required for all requirements in this category
     */
    public function getTotalCreditsRequired(): float
    {
        return $this->requirements()
            ->where('is_active', true)
            ->where('requirement_type', 'credit_hours')
            ->get()
            ->sum(function ($requirement) {
                return $requirement->parameters['min_credits'] ?? 0;
            });
    }

    /**
     * Get display name with type
     */
    public function getDisplayNameAttribute(): string
    {
        return "[{$this->type}] {$this->name}";
    }
}