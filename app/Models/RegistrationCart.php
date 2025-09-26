<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'term_id',
        'section_ids',
        'total_credits',
        'validated_at',
        'has_time_conflicts',
        'has_prerequisite_issues',
        'validation_messages',
        'status'
    ];

    protected $casts = [
        'section_ids' => 'array',
        'total_credits' => 'integer',
        'validated_at' => 'datetime',
        'has_time_conflicts' => 'boolean',
        'has_prerequisite_issues' => 'boolean',
        'validation_messages' => 'array'
    ];

    /**
     * Cart status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_VALIDATED = 'validated';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_PROCESSED = 'processed';
    const STATUS_EXPIRED = 'expired';

    /**
     * Get the student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the term
     */
    public function term(): BelongsTo
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    /**
     * Get the sections in the cart
     */
    public function sections()
    {
        if (empty($this->section_ids)) {
            return collect();
        }
        
        return CourseSection::whereIn('id', $this->section_ids)->get();
    }

    /**
     * Add a section to the cart
     */
    public function addSection($sectionId): bool
    {
        $sections = $this->section_ids ?? [];
        
        if (!in_array($sectionId, $sections)) {
            $sections[] = $sectionId;
            $this->section_ids = $sections;
            $this->recalculateCredits();
            return $this->save();
        }
        
        return false;
    }

    /**
     * Remove a section from the cart
     */
    public function removeSection($sectionId): bool
    {
        $sections = $this->section_ids ?? [];
        $key = array_search($sectionId, $sections);
        
        if ($key !== false) {
            unset($sections[$key]);
            $this->section_ids = array_values($sections);
            $this->recalculateCredits();
            return $this->save();
        }
        
        return false;
    }

    /**
     * Clear the cart
     */
    public function clear(): bool
    {
        $this->section_ids = [];
        $this->total_credits = 0;
        $this->validated_at = null;
        $this->has_time_conflicts = false;
        $this->has_prerequisite_issues = false;
        $this->validation_messages = null;
        $this->status = self::STATUS_ACTIVE;
        
        return $this->save();
    }

    /**
     * Recalculate total credits
     */
    public function recalculateCredits(): void
    {
        $this->total_credits = CourseSection::whereIn('id', $this->section_ids ?? [])
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->sum('courses.credits');
    }

    /**
     * Validate the cart
     */
    public function validate(): bool
    {
        $messages = [];
        $hasIssues = false;
        
        // Check time conflicts
        if ($this->checkTimeConflicts()) {
            $this->has_time_conflicts = true;
            $messages[] = 'Time conflicts detected between selected courses';
            $hasIssues = true;
        } else {
            $this->has_time_conflicts = false;
        }
        
        // Check prerequisites
        if ($this->checkPrerequisites()) {
            $this->has_prerequisite_issues = true;
            $messages[] = 'Prerequisites not met for one or more courses';
            $hasIssues = true;
        } else {
            $this->has_prerequisite_issues = false;
        }
        
        // Check credit limits
        $maxCredits = RegistrationConfiguration::getCached()->max_credits ?? 18;
        if ($this->total_credits > $maxCredits) {
            $messages[] = "Total credits ({$this->total_credits}) exceeds maximum allowed ({$maxCredits})";
            $hasIssues = true;
        }
        
        $this->validation_messages = $messages;
        
        if (!$hasIssues) {
            $this->validated_at = now();
            $this->status = self::STATUS_VALIDATED;
        }
        
        $this->save();
        
        return !$hasIssues;
    }

    /**
     * Check for time conflicts
     */
    private function checkTimeConflicts(): bool
    {
        $sections = $this->sections();
        
        // Simple check - would need more complex logic based on your schedule structure
        // This is a placeholder implementation
        return false;
    }

    /**
     * Check prerequisites
     */
    private function checkPrerequisites(): bool
    {
        $sections = $this->sections();
        
        foreach ($sections as $section) {
            $prerequisites = $section->course->prerequisites;
            
            if ($prerequisites->isNotEmpty()) {
                // Check if student has completed prerequisites
                $completedCourses = Enrollment::where('student_id', $this->student_id)
                    ->where('status', 'completed')
                    ->whereHas('section.course', function ($query) use ($prerequisites) {
                        $query->whereIn('id', $prerequisites->pluck('prerequisite_course_id'));
                    })
                    ->count();
                
                if ($completedCourses < $prerequisites->count()) {
                    return true; // Has prerequisite issues
                }
            }
        }
        
        return false;
    }

    /**
     * Convert cart to registrations
     */
    public function submitForRegistration(): bool
    {
        if ($this->status !== self::STATUS_VALIDATED) {
            return false;
        }
        
        foreach ($this->section_ids as $sectionId) {
            Registration::create([
                'student_id' => $this->student_id,
                'section_id' => $sectionId,
                'term_id' => $this->term_id,
                'registration_date' => now(),
                'status' => 'pending',
                'registration_type' => 'regular'
            ]);
        }
        
        $this->status = self::STATUS_SUBMITTED;
        return $this->save();
    }
}