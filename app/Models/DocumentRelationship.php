<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRelationship extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'document_id',
        'owner_type',
        'owner_id',
        'relationship_type',
        'purpose',
        'sort_order',
        'access_level',
        'is_primary',
        'is_required',
        'is_verified',
        'valid_from',
        'valid_until'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_primary' => 'boolean',
        'is_required' => 'boolean',
        'is_verified' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'sort_order' => 'integer'
    ];

    /**
     * Get the document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the owning model
     */
    public function owner()
    {
        return $this->morphTo('owner', 'owner_type', 'owner_id');
    }

    /**
     * Scope for primary documents
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope for required documents
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope for verified relationships
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for specific owner type
     */
    public function scopeForOwnerType($query, $type)
    {
        return $query->where('owner_type', $type);
    }

    /**
     * Scope for specific owner
     */
    public function scopeForOwner($query, $type, $id)
    {
        return $query->where('owner_type', $type)
                     ->where('owner_id', $id);
    }

    /**
     * Check if relationship is valid
     */
    public function isValid(): bool
    {
        $now = now();
        
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }
        
        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if relationship is expired
     */
    public function isExpired(): bool
    {
        return $this->valid_until && now()->gt($this->valid_until);
    }

    /**
     * Get the owner model class name
     */
    public function getOwnerModelAttribute(): string
    {
        $mapping = [
            'application' => 'App\Models\AdmissionApplication',
            'student' => 'App\Models\Student',
            'course' => 'App\Models\Course',
            'faculty' => 'App\Models\Faculty',
            'transaction' => 'App\Models\Transaction',
        ];

        return $mapping[$this->owner_type] ?? 'App\Models\\' . ucfirst($this->owner_type);
    }
}