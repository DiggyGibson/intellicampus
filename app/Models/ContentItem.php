<?php
// ===================================================================
// File: app/Models/ContentItem.php
// ===================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ContentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_site_id',
        'folder_id',
        'title',
        'description',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'content_text',
        'external_url',
        'display_order',
        'is_visible',
        'available_from',
        'available_until',
        'download_count',
        'view_count'
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
        'download_count' => 'integer',
        'view_count' => 'integer'
    ];

    public function courseSite(): BelongsTo
    {
        return $this->belongsTo(CourseSite::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(ContentFolder::class, 'folder_id');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(ContentAccessLog::class);
    }

    public function isAvailable(): bool
    {
        $now = now();
        
        if (!$this->is_visible) {
            return false;
        }
        
        if ($this->available_from && $now->lt($this->available_from)) {
            return false;
        }
        
        if ($this->available_until && $now->gt($this->available_until)) {
            return false;
        }
        
        return true;
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    public function getFileUrl(): ?string
    {
        if ($this->type === 'link') {
            return $this->external_url;
        }
        
        if ($this->file_path) {
            return Storage::url($this->file_path);
        }
        
        return null;
    }

    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeAvailable($query)
    {
        $now = now();
        return $query->where('is_visible', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('available_from')
                  ->orWhere('available_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('available_until')
                  ->orWhere('available_until', '>=', $now);
            });
    }
}