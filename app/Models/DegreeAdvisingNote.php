<?php
// Save as: backend/app/Models/DegreeAdvisingNote.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DegreeAdvisingNote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'student_id',
        'advisor_id',
        'plan_id',
        'note_type',
        'content',
        'action_items',
        'follow_up_date',
        'visible_to_student',
        'is_confidential'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'action_items' => 'array',
        'follow_up_date' => 'date',
        'visible_to_student' => 'boolean',
        'is_confidential' => 'boolean'
    ];

    /**
     * Note types
     */
    const TYPE_DEGREE_PLANNING = 'degree_planning';
    const TYPE_COURSE_SELECTION = 'course_selection';
    const TYPE_REQUIREMENT_OVERRIDE = 'requirement_override';
    const TYPE_GRADUATION_REVIEW = 'graduation_review';
    const TYPE_WHAT_IF_DISCUSSION = 'what_if_discussion';
    const TYPE_GENERAL = 'general';

    /**
     * Get the student this note is about
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the advisor who created this note
     */
    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    /**
     * Get the academic plan this note relates to
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(AcademicPlan::class, 'plan_id');
    }

    /**
     * Get formatted note type label
     */
    public function getNoteTypeLabelAttribute(): string
    {
        $labels = [
            self::TYPE_DEGREE_PLANNING => 'Degree Planning',
            self::TYPE_COURSE_SELECTION => 'Course Selection',
            self::TYPE_REQUIREMENT_OVERRIDE => 'Requirement Override',
            self::TYPE_GRADUATION_REVIEW => 'Graduation Review',
            self::TYPE_WHAT_IF_DISCUSSION => 'What-If Scenario Discussion',
            self::TYPE_GENERAL => 'General Advising'
        ];

        return $labels[$this->note_type] ?? 'Advising Note';
    }

    /**
     * Check if note should be visible to the current user
     */
    public function isVisibleTo(User $user): bool
    {
        // Advisor who created it can always see it
        if ($user->id === $this->advisor_id) {
            return true;
        }

        // Other advisors/admins can see non-confidential notes
        if ($user->hasAnyRole(['advisor', 'admin', 'registrar'])) {
            return !$this->is_confidential;
        }

        // Students can only see notes marked visible to them
        if ($user->student && $user->student->id === $this->student_id) {
            return $this->visible_to_student && !$this->is_confidential;
        }

        return false;
    }

    /**
     * Get action items that are still pending
     */
    public function getPendingActionItemsAttribute(): array
    {
        if (!$this->action_items) {
            return [];
        }

        return array_filter($this->action_items, function ($item) {
            return !($item['completed'] ?? false);
        });
    }

    /**
     * Mark an action item as completed
     */
    public function markActionItemCompleted(int $index): void
    {
        $items = $this->action_items ?? [];
        if (isset($items[$index])) {
            $items[$index]['completed'] = true;
            $items[$index]['completed_at'] = now()->toDateTimeString();
            $this->action_items = $items;
            $this->save();
        }
    }

    /**
     * Add a new action item
     */
    public function addActionItem(string $description, ?string $dueDate = null): void
    {
        $items = $this->action_items ?? [];
        $items[] = [
            'description' => $description,
            'due_date' => $dueDate,
            'completed' => false,
            'created_at' => now()->toDateTimeString()
        ];
        $this->action_items = $items;
        $this->save();
    }

    /**
     * Check if follow-up is overdue
     */
    public function getIsFollowUpOverdueAttribute(): bool
    {
        if (!$this->follow_up_date) {
            return false;
        }

        return $this->follow_up_date->isPast();
    }

    /**
     * Get notes for a student's degree audit
     */
    public static function getAuditNotes(Student $student, ?User $viewer = null)
    {
        $query = self::where('student_id', $student->id)
            ->whereIn('note_type', [
                self::TYPE_DEGREE_PLANNING,
                self::TYPE_REQUIREMENT_OVERRIDE,
                self::TYPE_GRADUATION_REVIEW
            ])
            ->orderBy('created_at', 'desc');

        // Filter based on viewer permissions
        if ($viewer) {
            if ($viewer->hasRole('student') && $viewer->student_id === $student->id) {
                $query->where('visible_to_student', true)
                      ->where('is_confidential', false);
            } elseif (!$viewer->hasAnyRole(['advisor', 'admin', 'registrar'])) {
                $query->whereRaw('1 = 0'); // No access
            }
        }

        return $query->get();
    }

    /**
     * Create a note for requirement override
     */
    public static function createOverrideNote(
        Student $student,
        User $advisor,
        DegreeRequirement $requirement,
        string $reason
    ): self {
        return self::create([
            'student_id' => $student->id,
            'advisor_id' => $advisor->id,
            'note_type' => self::TYPE_REQUIREMENT_OVERRIDE,
            'content' => "Override approved for {$requirement->name}: {$reason}",
            'visible_to_student' => true,
            'is_confidential' => false
        ]);
    }

    /**
     * Create a note for graduation review
     */
    public static function createGraduationReviewNote(
        Student $student,
        User $advisor,
        array $reviewResults
    ): self {
        $content = "Graduation Review conducted.\n";
        $content .= "Eligible: " . ($reviewResults['eligible'] ? 'Yes' : 'No') . "\n";
        
        if (!empty($reviewResults['missing_requirements'])) {
            $content .= "Missing Requirements:\n";
            foreach ($reviewResults['missing_requirements'] as $req) {
                $content .= "- {$req}\n";
            }
        }

        $actionItems = [];
        foreach ($reviewResults['action_items'] ?? [] as $item) {
            $actionItems[] = [
                'description' => $item,
                'completed' => false
            ];
        }

        return self::create([
            'student_id' => $student->id,
            'advisor_id' => $advisor->id,
            'note_type' => self::TYPE_GRADUATION_REVIEW,
            'content' => $content,
            'action_items' => $actionItems,
            'follow_up_date' => $reviewResults['follow_up_date'] ?? null,
            'visible_to_student' => true,
            'is_confidential' => false
        ]);
    }

    /**
     * Scope for visible notes
     */
    public function scopeVisibleToStudent($query)
    {
        return $query->where('visible_to_student', true)
                    ->where('is_confidential', false);
    }

    /**
     * Scope for notes requiring follow-up
     */
    public function scopeRequiringFollowUp($query)
    {
        return $query->whereNotNull('follow_up_date')
                    ->where('follow_up_date', '>=', now());
    }

    /**
     * Scope by note type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('note_type', $type);
    }
}