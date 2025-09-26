<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends Model
{
    use HasFactory;

    /**
     * Disable timestamps as we only need created_at
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'document_id',
        'version_number',
        'hash',
        'path',
        'size',
        'change_type',
        'change_summary',
        'changes',
        'metadata',
        'is_major_version',
        'created_by',
        'created_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'changes' => 'array',
        'metadata' => 'array',
        'is_major_version' => 'boolean',
        'size' => 'integer',
        'version_number' => 'integer',
        'created_at' => 'datetime'
    ];

    /**
     * Set created_at on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    /**
     * Get the document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user who created this version
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for major versions only
     */
    public function scopeMajor($query)
    {
        return $query->where('is_major_version', true);
    }

    /**
     * Scope for minor versions only
     */
    public function scopeMinor($query)
    {
        return $query->where('is_major_version', false);
    }

    /**
     * Scope for specific change type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('change_type', $type);
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
     * Get version label (e.g., "v1.0", "v2.1")
     */
    public function getVersionLabelAttribute(): string
    {
        return 'v' . $this->version_number;
    }

    /**
     * Check if this is the latest version
     */
    public function isLatest(): bool
    {
        $latestVersion = self::where('document_id', $this->document_id)
            ->max('version_number');
        
        return $this->version_number === $latestVersion;
    }

    /**
     * Get the previous version
     */
    public function previousVersion()
    {
        return self::where('document_id', $this->document_id)
            ->where('version_number', '<', $this->version_number)
            ->orderBy('version_number', 'desc')
            ->first();
    }

    /**
     * Get the next version
     */
    public function nextVersion()
    {
        return self::where('document_id', $this->document_id)
            ->where('version_number', '>', $this->version_number)
            ->orderBy('version_number', 'asc')
            ->first();
    }
}