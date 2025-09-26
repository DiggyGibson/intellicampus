<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationStatusHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'application_status_history';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'old_status',
        'new_status',
        'reason',
        'notes',
        'changed_by',
        'changed_by_role',
        'metadata',
        'changed_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'metadata' => 'array',
        'changed_at' => 'datetime'
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Set default values on creation
        static::creating(function ($history) {
            if (!$history->changed_at) {
                $history->changed_at = now();
            }
            
            if (!$history->changed_by && auth()->check()) {
                $history->changed_by = auth()->id();
                $history->changed_by_role = auth()->user()->getRoleNames()->first() ?? 'user';
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
     * Get the user who made the change.
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scopes
     */

    /**
     * Scope for changes by a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('changed_by', $userId);
    }

    /**
     * Scope for changes within a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('changed_at', [$startDate, $endDate]);
    }

    /**
     * Scope for specific status transitions.
     */
    public function scopeTransition($query, $from, $to)
    {
        return $query->where('old_status', $from)->where('new_status', $to);
    }

    /**
     * Scope for changes to a specific status.
     */
    public function scopeToStatus($query, $status)
    {
        return $query->where('new_status', $status);
    }

    /**
     * Scope for changes from a specific status.
     */
    public function scopeFromStatus($query, $status)
    {
        return $query->where('old_status', $status);
    }

    /**
     * Scope for changes by role.
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('changed_by_role', $role);
    }

    /**
     * Helper Methods
     */

    /**
     * Get formatted change description.
     */
    public function getChangeDescription(): string
    {
        $description = '';
        
        if ($this->old_status) {
            $description = "Status changed from '{$this->getStatusLabel($this->old_status)}' to '{$this->getStatusLabel($this->new_status)}'";
        } else {
            $description = "Status set to '{$this->getStatusLabel($this->new_status)}'";
        }
        
        if ($this->changedBy) {
            $description .= " by {$this->changedBy->name}";
            
            if ($this->changed_by_role) {
                $description .= " ({$this->getRoleLabel($this->changed_by_role)})";
            }
        }
        
        return $description;
    }

    /**
     * Get short change description.
     */
    public function getShortDescription(): string
    {
        if ($this->old_status) {
            return "{$this->getStatusLabel($this->old_status)} → {$this->getStatusLabel($this->new_status)}";
        }
        
        return "→ {$this->getStatusLabel($this->new_status)}";
    }

    /**
     * Get time since change.
     */
    public function getTimeSinceChange(): string
    {
        return $this->changed_at->diffForHumans();
    }

    /**
     * Get formatted change date.
     */
    public function getFormattedDate(): string
    {
        return $this->changed_at->format('M d, Y g:i A');
    }

    /**
     * Get status label.
     */
    protected function getStatusLabel($status): string
    {
        $labels = [
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'documents_pending' => 'Documents Pending',
            'committee_review' => 'Committee Review',
            'interview_scheduled' => 'Interview Scheduled',
            'decision_pending' => 'Decision Pending',
            'admitted' => 'Admitted',
            'conditional_admit' => 'Conditionally Admitted',
            'waitlisted' => 'Waitlisted',
            'waitlist_offered' => 'Waitlist Offer Extended',
            'denied' => 'Denied',
            'deferred' => 'Deferred',
            'withdrawn' => 'Withdrawn',
            'expired' => 'Expired'
        ];
        
        return $labels[$status] ?? ucwords(str_replace('_', ' ', $status));
    }

    /**
     * Get role label.
     */
    protected function getRoleLabel($role): string
    {
        $labels = [
            'admin' => 'Administrator',
            'admissions_officer' => 'Admissions Officer',
            'reviewer' => 'Reviewer',
            'committee_member' => 'Committee Member',
            'registrar' => 'Registrar',
            'system' => 'System',
            'applicant' => 'Applicant'
        ];
        
        return $labels[$role] ?? ucwords(str_replace('_', ' ', $role));
    }

    /**
     * Check if this was an automatic change.
     */
    public function isAutomatic(): bool
    {
        return $this->changed_by_role === 'system' || 
               ($this->metadata && isset($this->metadata['automatic']) && $this->metadata['automatic']);
    }

    /**
     * Check if this was a significant change.
     */
    public function isSignificant(): bool
    {
        $significantTransitions = [
            'submitted',
            'admitted',
            'conditional_admit',
            'waitlisted',
            'denied',
            'deferred',
            'withdrawn'
        ];
        
        return in_array($this->new_status, $significantTransitions);
    }

    /**
     * Get the color for the status change.
     */
    public function getChangeColor(): string
    {
        // Positive changes
        if (in_array($this->new_status, ['admitted', 'conditional_admit', 'interview_scheduled'])) {
            return 'green';
        }
        
        // Negative changes
        if (in_array($this->new_status, ['denied', 'withdrawn', 'expired'])) {
            return 'red';
        }
        
        // Neutral/waiting changes
        if (in_array($this->new_status, ['waitlisted', 'deferred', 'documents_pending'])) {
            return 'yellow';
        }
        
        // Progress changes
        if (in_array($this->new_status, ['submitted', 'under_review', 'committee_review'])) {
            return 'blue';
        }
        
        return 'gray';
    }

    /**
     * Get icon for the status change.
     */
    public function getChangeIcon(): string
    {
        return match($this->new_status) {
            'admitted', 'conditional_admit' => 'check-circle',
            'denied' => 'x-circle',
            'waitlisted' => 'clock',
            'deferred' => 'pause-circle',
            'withdrawn' => 'arrow-left-circle',
            'expired' => 'exclamation-circle',
            'submitted' => 'send',
            'under_review' => 'eye',
            'committee_review' => 'users',
            'interview_scheduled' => 'calendar',
            'documents_pending' => 'document',
            default => 'arrow-right'
        };
    }

    /**
     * Log a status change.
     */
    public static function logChange(
        AdmissionApplication $application, 
        $oldStatus, 
        $newStatus, 
        $reason = null, 
        $notes = null,
        $metadata = []
    ): self {
        return self::create([
            'application_id' => $application->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
            'notes' => $notes,
            'metadata' => $metadata
        ]);
    }

    /**
     * Get timeline for an application.
     */
    public static function getTimeline($applicationId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('application_id', $applicationId)
            ->with('changedBy')
            ->orderBy('changed_at', 'desc')
            ->get();
    }

    /**
     * Get significant events for an application.
     */
    public static function getSignificantEvents($applicationId): \Illuminate\Database\Eloquent\Collection
    {
        $significantStatuses = [
            'submitted',
            'interview_scheduled',
            'admitted',
            'conditional_admit',
            'waitlisted',
            'denied',
            'deferred',
            'withdrawn'
        ];
        
        return self::where('application_id', $applicationId)
            ->whereIn('new_status', $significantStatuses)
            ->with('changedBy')
            ->orderBy('changed_at', 'desc')
            ->get();
    }

    /**
     * Get statistics for status changes.
     */
    public static function getStatistics($startDate = null, $endDate = null): array
    {
        $query = self::query();
        
        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        }
        
        $totalChanges = $query->count();
        $byStatus = $query->groupBy('new_status')
            ->selectRaw('new_status, count(*) as count')
            ->pluck('count', 'new_status')
            ->toArray();
        
        $byRole = $query->groupBy('changed_by_role')
            ->selectRaw('changed_by_role, count(*) as count')
            ->pluck('count', 'changed_by_role')
            ->toArray();
        
        $averageTimeToDecision = self::calculateAverageTimeToDecision($startDate, $endDate);
        
        return [
            'total_changes' => $totalChanges,
            'by_status' => $byStatus,
            'by_role' => $byRole,
            'average_time_to_decision' => $averageTimeToDecision,
            'automatic_changes' => $query->where('changed_by_role', 'system')->count(),
            'manual_changes' => $query->where('changed_by_role', '!=', 'system')->count()
        ];
    }

    /**
     * Calculate average time from submission to decision.
     */
    protected static function calculateAverageTimeToDecision($startDate = null, $endDate = null): ?float
    {
        $decisionStatuses = ['admitted', 'denied', 'waitlisted', 'deferred'];
        
        $query = self::whereIn('new_status', $decisionStatuses);
        
        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        }
        
        $decisions = $query->get();
        
        if ($decisions->isEmpty()) {
            return null;
        }
        
        $totalDays = 0;
        $count = 0;
        
        foreach ($decisions as $decision) {
            $submission = self::where('application_id', $decision->application_id)
                ->where('new_status', 'submitted')
                ->first();
            
            if ($submission) {
                $totalDays += $submission->changed_at->diffInDays($decision->changed_at);
                $count++;
            }
        }
        
        return $count > 0 ? round($totalDays / $count, 1) : null;
    }

    /**
     * Format for activity feed.
     */
    public function formatForActivityFeed(): array
    {
        return [
            'id' => $this->id,
            'type' => 'status_change',
            'description' => $this->getChangeDescription(),
            'short_description' => $this->getShortDescription(),
            'reason' => $this->reason,
            'notes' => $this->notes,
            'icon' => $this->getChangeIcon(),
            'color' => $this->getChangeColor(),
            'user' => $this->changedBy ? [
                'id' => $this->changedBy->id,
                'name' => $this->changedBy->name,
                'role' => $this->changed_by_role
            ] : null,
            'timestamp' => $this->changed_at->toIso8601String(),
            'time_ago' => $this->getTimeSinceChange(),
            'is_automatic' => $this->isAutomatic(),
            'is_significant' => $this->isSignificant()
        ];
    }
}