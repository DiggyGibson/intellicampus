<?php
// File: app/Models/CourseSite.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class CourseSite extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'site_code',
        'site_name',
        'description',
        'welcome_message',
        'is_active',
        'is_published',
        'settings',
        'published_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'settings' => 'array',
        'published_at' => 'datetime'
    ];

    protected $attributes = [
        'is_active' => true,
        'is_published' => false,
        'settings' => '[]'
    ];

    /**
     * Boot method to auto-generate site code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->site_code)) {
                $model->site_code = $model->generateSiteCode();
            }
            
            if (empty($model->site_name)) {
                $model->site_name = $model->section->course->name . ' - ' . $model->section->name;
            }
        });
    }

    /**
     * Generate unique site code
     */
    protected function generateSiteCode()
    {
        $section = $this->section;
        $course = $section->course;
        
        // Format: COURSE_CODE-SECTION-TERM-YEAR
        $code = $course->code . '-' . $section->section_number . '-' . 
                $section->term->code . '-' . $section->term->year;
        
        // Ensure uniqueness
        $baseCode = $code;
        $counter = 1;
        while (self::where('site_code', $code)->exists()) {
            $code = $baseCode . '-' . $counter;
            $counter++;
        }
        
        return $code;
    }

    /**
     * Get the course section
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    /**
     * Get the course (through section)
     */
    public function course()
    {
        return $this->hasOneThrough(Course::class, CourseSection::class, 'id', 'id', 'section_id', 'course_id');
    }

    /**
     * Get content items
     */
    public function contentItems(): HasMany
    {
        return $this->hasMany(ContentItem::class);
    }

    /**
     * Get content folders
     */
    public function contentFolders(): HasMany
    {
        return $this->hasMany(ContentFolder::class);
    }

    /**
     * Get assignments
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get quizzes
     */
    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    /**
     * Get discussion forums
     */
    public function discussionForums(): HasMany
    {
        return $this->hasMany(DiscussionForum::class);
    }

    /**
     * Get announcements
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    /**
     * Get gradebook items
     */
    public function gradebookItems(): HasMany
    {
        return $this->hasMany(GradebookItem::class);
    }

    /**
     * Get student progress records
     */
    public function studentProgress(): HasMany
    {
        return $this->hasMany(StudentProgress::class);
    }

    /**
     * Get enrolled students (through section)
     */
    public function enrolledStudents()
    {
        return $this->section->enrollments()->with('student');
    }

    /**
     * Check if a user is enrolled
     */
    public function isUserEnrolled($userId): bool
    {
        return $this->section->enrollments()
            ->whereHas('student', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->exists();
    }

    /**
     * Check if a user is the instructor
     */
    public function isUserInstructor($userId): bool
    {
        return $this->section->primary_instructor_id === $userId ||
               $this->section->secondary_instructor_id === $userId;
    }

    /**
     * Check if user has access to the site
     */
    public function userHasAccess($userId): bool
    {
        // Check if user is enrolled or is instructor
        return $this->isUserEnrolled($userId) || 
               $this->isUserInstructor($userId) ||
               User::find($userId)->hasRole(['admin', 'academic-administrator']);
    }

    /**
     * Publish the course site
     */
    public function publish(): bool
    {
        $this->is_published = true;
        $this->published_at = now();
        return $this->save();
    }

    /**
     * Unpublish the course site
     */
    public function unpublish(): bool
    {
        $this->is_published = false;
        $this->published_at = null;
        return $this->save();
    }

    /**
     * Get site statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_content' => $this->contentItems()->count(),
            'total_assignments' => $this->assignments()->count(),
            'total_quizzes' => $this->quizzes()->count(),
            'total_discussions' => $this->discussionForums()->count(),
            'total_announcements' => $this->announcements()->count(),
            'enrolled_students' => $this->section->enrollments()->count(),
            'submitted_assignments' => AssignmentSubmission::whereIn('assignment_id', 
                $this->assignments()->pluck('id'))->count(),
            'average_grade' => $this->calculateAverageGrade()
        ];
    }

    /**
     * Calculate average grade for the course
     */
    protected function calculateAverageGrade()
    {
        return GradebookEntry::whereIn('gradebook_item_id', 
            $this->gradebookItems()->pluck('id'))
            ->avg('percentage') ?? 0;
    }

    /**
     * Get recent activity
     */
    public function getRecentActivity($limit = 10)
    {
        // This would aggregate recent activities from various sources
        // For now, return recent announcements
        return $this->announcements()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clone site structure for new term
     */
    public function cloneForSection($newSectionId): CourseSite
    {
        $newSite = $this->replicate();
        $newSite->section_id = $newSectionId;
        $newSite->site_code = null; // Will be auto-generated
        $newSite->is_published = false;
        $newSite->published_at = null;
        $newSite->save();

        // Clone content structure (folders)
        foreach ($this->contentFolders()->whereNull('parent_id')->get() as $folder) {
            $this->cloneFolder($folder, $newSite->id);
        }

        // Clone other structures as needed
        // You can add cloning for assignments, quizzes, etc.

        return $newSite;
    }

    /**
     * Helper to clone folder structure
     */
    protected function cloneFolder($folder, $newSiteId, $newParentId = null)
    {
        $newFolder = $folder->replicate();
        $newFolder->course_site_id = $newSiteId;
        $newFolder->parent_id = $newParentId;
        $newFolder->save();

        // Recursively clone subfolders
        foreach ($folder->children as $child) {
            $this->cloneFolder($child, $newSiteId, $newFolder->id);
        }
    }

    /**
     * Scope for active sites
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for published sites
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for sites user has access to
     */
    public function scopeAccessibleBy($query, $userId)
    {
        return $query->whereHas('section', function ($q) use ($userId) {
            $q->where('primary_instructor_id', $userId)
              ->orWhere('secondary_instructor_id', $userId)
              ->orWhereHas('enrollments', function ($eq) use ($userId) {
                  $eq->whereHas('student', function ($sq) use ($userId) {
                      $sq->where('user_id', $userId);
                  });
              });
        });
    }
}