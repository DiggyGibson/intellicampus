<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'hash',
        'original_name',
        'display_name',
        'mime_type',
        'size',
        'path',
        'disk',
        'context',
        'category',
        'subcategory',
        'tags',
        'metadata',
        'status',
        'verification_status',
        'verified_by',
        'verified_at',
        'verification_notes',
        'rejection_reason',
        'access_level',
        'requires_authentication',
        'access_rules',
        'is_processed',
        'has_thumbnail',
        'is_searchable',
        'virus_scanned',
        'virus_scanned_at',
        'retention_until',
        'expires_at',
        'is_sensitive',
        'compliance_tags',
        'download_count',
        'view_count',
        'last_accessed_at',
        'uploaded_by',
        'uploaded_ip',
        'updated_by'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tags' => 'array',
        'metadata' => 'array',
        'access_rules' => 'array',
        'verified_at' => 'datetime',
        'virus_scanned_at' => 'datetime',
        'retention_until' => 'date',
        'expires_at' => 'date',
        'last_accessed_at' => 'datetime',
        'is_processed' => 'boolean',
        'has_thumbnail' => 'boolean',
        'is_searchable' => 'boolean',
        'virus_scanned' => 'boolean',
        'is_sensitive' => 'boolean',
        'requires_authentication' => 'boolean',
        'size' => 'integer',
        'download_count' => 'integer',
        'view_count' => 'integer'
    ];

    /**
     * Get the relationships for this document
     */
    public function relationships(): HasMany
    {
        return $this->hasMany(DocumentRelationship::class);
    }

    /**
     * Get the versions for this document
     */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    /**
     * Get the access logs for this document
     */
    public function accessLogs(): HasMany
    {
        return $this->hasMany(DocumentAccessLog::class);
    }

    /**
     * Get the processing queue items for this document
     */
    public function processingQueue(): HasMany
    {
        return $this->hasMany(DocumentProcessingQueue::class);
    }

    /**
     * Get the user who uploaded this document
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the user who verified this document
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the user who last updated this document
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope for verified documents
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    /**
     * Scope for pending verification
     */
    public function scopePendingVerification($query)
    {
        return $query->where('verification_status', 'pending');
    }

    /**
     * Scope for active documents
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for documents by context
     */
    public function scopeForContext($query, $context)
    {
        return $query->where('context', $context);
    }

    /**
     * Check if document is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if document is verified
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Get human-readable file size
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the full storage path
     */
    public function getFullPathAttribute(): string
    {
        return storage_path('app/' . $this->path);
    }

    /**
     * Get public URL if accessible
     */
    public function getPublicUrlAttribute(): ?string
    {
        if ($this->access_level === 'public') {
            return route('documents.view', $this->uuid);
        }
        
        return null;
    }
}