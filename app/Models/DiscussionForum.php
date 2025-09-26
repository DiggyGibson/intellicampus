<?php

// ===================================================================
// File: app/Models/DiscussionForum.php
// ===================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionForum extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_site_id',
        'title',
        'description',
        'type',
        'is_active',
        'allow_student_posts',
        'require_approval',
        'allow_anonymous',
        'display_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_student_posts' => 'boolean',
        'require_approval' => 'boolean',
        'allow_anonymous' => 'boolean',
        'display_order' => 'integer'
    ];

    public function courseSite(): BelongsTo
    {
        return $this->belongsTo(CourseSite::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(DiscussionPost::class, 'forum_id');
    }

    public function threads(): HasMany
    {
        return $this->posts()->whereNull('parent_id');
    }

    public function getStatistics(): array
    {
        return [
            'total_posts' => $this->posts()->count(),
            'total_threads' => $this->threads()->count(),
            'total_participants' => $this->posts()->distinct('user_id')->count('user_id'),
            'unanswered_threads' => $this->threads()->where('reply_count', 0)->count(),
            'posts_today' => $this->posts()->whereDate('created_at', today())->count()
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}