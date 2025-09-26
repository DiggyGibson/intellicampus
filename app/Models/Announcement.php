<?php

// ===================================================================
// File: app/Models/Announcement.php
// ===================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_site_id',
        'posted_by',
        'title',
        'content',
        'priority',
        'send_email',
        'send_sms',
        'is_visible',
        'display_from',
        'display_until',
        'view_count'
    ];

    protected $casts = [
        'send_email' => 'boolean',
        'send_sms' => 'boolean',
        'is_visible' => 'boolean',
        'display_from' => 'datetime',
        'display_until' => 'datetime',
        'view_count' => 'integer'
    ];

    public function courseSite(): BelongsTo
    {
        return $this->belongsTo(CourseSite::class);
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function isCurrentlyVisible(): bool
    {
        if (!$this->is_visible) {
            return false;
        }
        
        $now = now();
        
        if ($this->display_from && $now->lt($this->display_from)) {
            return false;
        }
        
        if ($this->display_until && $now->gt($this->display_until)) {
            return false;
        }
        
        return true;
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function scopeVisible($query)
    {
        $now = now();
        return $query->where('is_visible', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('display_from')
                  ->orWhere('display_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('display_until')
                  ->orWhere('display_until', '>=', $now);
            });
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }
}