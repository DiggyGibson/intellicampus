<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationNote extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'application_notes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'user_id',
        'note',
        'visibility',
        'type',
        'is_important',
        'requires_action',
        'action_due_date',
        'action_completed'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_important' => 'boolean',
        'requires_action' => 'boolean',
        'action_completed' => 'boolean',
        'action_due_date' => 'date'
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Set user_id on creation
        static::creating(function ($note) {
            if (!$note->user_id && auth()->check()) {
                $note->user_id = auth()->id();
            }
        });

        // Send notification if action is required
        static::created(function ($note) {
            if ($note->requires_action && $note->action_due_date) {
                $note->scheduleActionReminder();
            }
        });

        // Log when action is completed
        static::updating(function ($note) {
            if ($note->isDirty('action_completed') && $note->action_completed) {
                $note->logActionCompletion();
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the application.
     */
    public function application()
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }

    /**
     * Get the user who created the note.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */

    /**
     * Scope for important notes.
     */
    public function scopeImportant($query)
    {
        return $query->where('is_important', true);
    }

    /**
     * Scope for notes requiring action.
     */
    public function scopeRequiringAction($query)
    {
        return $query->where('requires_action', true)
            ->where('action_completed', false);
    }

    /**
     * Scope for overdue actions.
     */
    public function scopeOverdueActions($query)
    {
        return $query->where('requires_action', true)
            ->where('action_completed', false)
            ->where('action_due_date', '<', now()->toDateString());
    }

    /**
     * Scope for upcoming actions.
     */
    public function scopeUpcomingActions($query, $days = 7)
    {
        return $query->where('requires_action', true)
            ->where('action_completed', false)
            ->whereBetween('action_due_date', [now(), now()->addDays($days)]);
    }

    /**
     * Scope for completed actions.
     */
    public function scopeCompletedActions($query)
    {
        return $query->where('requires_action', true)
            ->where('action_completed', true);
    }

    /**
     * Scope for notes by visibility.
     */
    public function scopeVisibleTo($query, $role)
    {
        return $query->where(function ($q) use ($role) {
            // Public notes are visible to all
            $q->where('visibility', 'public');
            
            // Staff notes visible to staff and admin
            if (in_array($role, ['staff', 'admin', 'admissions_officer'])) {
                $q->orWhere('visibility', 'staff');
            }
            
            // Reviewer notes visible to reviewers, staff, and admin
            if (in_array($role, ['reviewer', 'committee_member', 'staff', 'admin', 'admissions_officer'])) {
                $q->orWhere('visibility', 'reviewers');
            }
            
            // Private notes only visible to creator (handled separately)
        });
    }

    /**
     * Scope for notes by type.
     */
    public function scopeOfType($query, $type)
    {
        if (is_array($type)) {
            return $query->whereIn('type', $type);
        }
        
        return $query->where('type', $type);
    }

    /**
     * Scope for recent notes.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for notes by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Helper Methods
     */

    /**
     * Mark action as completed.
     */
    public function completeAction(): bool
    {
        if (!$this->requires_action) {
            return false;
        }

        $this->action_completed = true;
        return $this->save();
    }

    /**
     * Reopen action.
     */
    public function reopenAction(): bool
    {
        if (!$this->requires_action) {
            return false;
        }

        $this->action_completed = false;
        return $this->save();
    }

    /**
     * Mark as important.
     */
    public function markAsImportant(): bool
    {
        $this->is_important = true;
        return $this->save();
    }

    /**
     * Unmark as important.
     */
    public function unmarkAsImportant(): bool
    {
        $this->is_important = false;
        return $this->save();
    }

    /**
     * Check if action is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->requires_action && 
               !$this->action_completed && 
               $this->action_due_date && 
               $this->action_due_date < now();
    }

    /**
     * Check if action is due soon.
     */
    public function isDueSoon($days = 3): bool
    {
        return $this->requires_action && 
               !$this->action_completed && 
               $this->action_due_date && 
               $this->action_due_date->isBetween(now(), now()->addDays($days));
    }

    /**
     * Check if user can view this note.
     */
    public function canBeViewedBy(User $user): bool
    {
        // Creator can always view their own notes
        if ($this->user_id === $user->id) {
            return true;
        }
        
        // Check visibility rules
        switch ($this->visibility) {
            case 'private':
                return false;
                
            case 'reviewers':
                return $user->hasAnyRole(['reviewer', 'committee_member', 'staff', 'admin', 'admissions_officer']);
                
            case 'staff':
                return $user->hasAnyRole(['staff', 'admin', 'admissions_officer']);
                
            case 'public':
                return true;
                
            default:
                return false;
        }
    }

    /**
     * Check if user can edit this note.
     */
    public function canBeEditedBy(User $user): bool
    {
        // Only creator can edit their notes
        // Admins can edit any note
        return $this->user_id === $user->id || $user->hasRole('admin');
    }

    /**
     * Get type label.
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'general' => 'General Note',
            'academic' => 'Academic',
            'financial' => 'Financial',
            'document' => 'Document Related',
            'interview' => 'Interview',
            'decision' => 'Decision',
            'follow_up' => 'Follow Up',
            default => 'Note'
        };
    }

    /**
     * Get type icon.
     */
    public function getTypeIcon(): string
    {
        return match($this->type) {
            'general' => 'document-text',
            'academic' => 'academic-cap',
            'financial' => 'currency-dollar',
            'document' => 'document',
            'interview' => 'users',
            'decision' => 'scale',
            'follow_up' => 'clock',
            default => 'annotation'
        };
    }

    /**
     * Get type color.
     */
    public function getTypeColor(): string
    {
        return match($this->type) {
            'general' => 'gray',
            'academic' => 'blue',
            'financial' => 'green',
            'document' => 'yellow',
            'interview' => 'purple',
            'decision' => 'red',
            'follow_up' => 'orange',
            default => 'gray'
        };
    }

    /**
     * Get visibility label.
     */
    public function getVisibilityLabel(): string
    {
        return match($this->visibility) {
            'private' => 'Private (Only Me)',
            'reviewers' => 'Reviewers Only',
            'staff' => 'Staff Only',
            'public' => 'Public',
            default => 'Unknown'
        };
    }

    /**
     * Get visibility icon.
     */
    public function getVisibilityIcon(): string
    {
        return match($this->visibility) {
            'private' => 'lock-closed',
            'reviewers' => 'user-group',
            'staff' => 'briefcase',
            'public' => 'globe',
            default => 'question-mark-circle'
        };
    }

    /**
     * Get action status.
     */
    public function getActionStatus(): string
    {
        if (!$this->requires_action) {
            return 'no_action';
        }
        
        if ($this->action_completed) {
            return 'completed';
        }
        
        if ($this->isOverdue()) {
            return 'overdue';
        }
        
        if ($this->isDueSoon()) {
            return 'due_soon';
        }
        
        return 'pending';
    }

    /**
     * Get action status label.
     */
    public function getActionStatusLabel(): string
    {
        return match($this->getActionStatus()) {
            'no_action' => 'No Action Required',
            'completed' => 'Completed',
            'overdue' => 'Overdue',
            'due_soon' => 'Due Soon',
            'pending' => 'Pending',
            default => 'Unknown'
        };
    }

    /**
     * Get action status color.
     */
    public function getActionStatusColor(): string
    {
        return match($this->getActionStatus()) {
            'no_action' => 'gray',
            'completed' => 'green',
            'overdue' => 'red',
            'due_soon' => 'yellow',
            'pending' => 'blue',
            default => 'gray'
        };
    }

    /**
     * Get formatted note for display.
     */
    public function getFormattedNote(): array
    {
        return [
            'id' => $this->id,
            'note' => $this->note,
            'type' => $this->getTypeLabel(),
            'type_icon' => $this->getTypeIcon(),
            'type_color' => $this->getTypeColor(),
            'author' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatar_url ?? null
            ],
            'created' => $this->created_at->format('M d, Y g:i A'),
            'created_ago' => $this->created_at->diffForHumans(),
            'visibility' => $this->getVisibilityLabel(),
            'visibility_icon' => $this->getVisibilityIcon(),
            'is_important' => $this->is_important,
            'requires_action' => $this->requires_action,
            'action_status' => $this->getActionStatus(),
            'action_status_label' => $this->getActionStatusLabel(),
            'action_status_color' => $this->getActionStatusColor(),
            'action_due' => $this->action_due_date?->format('M d, Y'),
            'action_completed' => $this->action_completed,
            'is_overdue' => $this->isOverdue(),
            'is_due_soon' => $this->isDueSoon()
        ];
    }

    /**
     * Schedule action reminder.
     */
    protected function scheduleActionReminder(): void
    {
        // This would typically dispatch a job to send reminder
        // Implementation depends on your notification system
    }

    /**
     * Log action completion.
     */
    protected function logActionCompletion(): void
    {
        // Log the completion in activity log
        activity()
            ->performedOn($this)
            ->causedBy(auth()->user())
            ->withProperties([
                'application_id' => $this->application_id,
                'note_id' => $this->id,
                'action_due_date' => $this->action_due_date
            ])
            ->log('Action item completed');
    }

    /**
     * Create a quick note.
     */
    public static function quickNote(
        AdmissionApplication $application, 
        string $note, 
        string $type = 'general',
        string $visibility = 'reviewers'
    ): self {
        return self::create([
            'application_id' => $application->id,
            'note' => $note,
            'type' => $type,
            'visibility' => $visibility
        ]);
    }

    /**
     * Create an action item.
     */
    public static function createAction(
        AdmissionApplication $application, 
        string $note, 
        $dueDate, 
        string $type = 'follow_up',
        bool $isImportant = false
    ): self {
        return self::create([
            'application_id' => $application->id,
            'note' => $note,
            'type' => $type,
            'requires_action' => true,
            'action_due_date' => $dueDate,
            'is_important' => $isImportant,
            'visibility' => 'staff'
        ]);
    }

    /**
     * Get statistics for notes.
     */
    public static function getStatistics($applicationId = null): array
    {
        $query = self::query();
        
        if ($applicationId) {
            $query->where('application_id', $applicationId);
        }
        
        return [
            'total_notes' => $query->count(),
            'important_notes' => $query->where('is_important', true)->count(),
            'action_items' => $query->where('requires_action', true)->count(),
            'pending_actions' => $query->requiringAction()->count(),
            'overdue_actions' => $query->overdueActions()->count(),
            'completed_actions' => $query->completedActions()->count(),
            'by_type' => $query->groupBy('type')
                ->selectRaw('type, count(*) as count')
                ->pluck('count', 'type')
                ->toArray(),
            'by_visibility' => $query->groupBy('visibility')
                ->selectRaw('visibility, count(*) as count')
                ->pluck('count', 'visibility')
                ->toArray()
        ];
    }

    /**
     * Get action items summary.
     */
    public static function getActionItemsSummary($userId = null): array
    {
        $query = self::requiringAction();
        
        if ($userId) {
            // Get actions assigned to or created by user
            $query->where(function ($q) use ($userId) {
                $q->where('user_id', $userId);
                // Could add assigned_to field if needed
            });
        }
        
        return [
            'total' => $query->count(),
            'overdue' => $query->overdueActions()->count(),
            'due_today' => $query->whereDate('action_due_date', today())->count(),
            'due_this_week' => $query->upcomingActions(7)->count(),
            'due_this_month' => $query->upcomingActions(30)->count()
        ];
    }
}