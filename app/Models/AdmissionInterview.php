<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionInterview extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'admission_interviews';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'interviewer_id',
        'scheduled_at',
        'duration_minutes',
        'interview_type',
        'location',
        'meeting_link',
        'meeting_id',
        'meeting_password',
        'status',
        'notes',
        'interview_score',
        'feedback',
        'evaluation_criteria',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'interview_score' => 'integer',
        'evaluation_criteria' => 'array'
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Update timestamps based on status changes
        static::updating(function ($interview) {
            if ($interview->isDirty('status')) {
                switch ($interview->status) {
                    case 'confirmed':
                        if (!$interview->confirmed_at) {
                            $interview->confirmed_at = now();
                        }
                        break;
                    case 'completed':
                        if (!$interview->completed_at) {
                            $interview->completed_at = now();
                        }
                        break;
                    case 'cancelled':
                        if (!$interview->cancelled_at) {
                            $interview->cancelled_at = now();
                        }
                        break;
                }
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
     * Get the interviewer.
     */
    public function interviewer()
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for upcoming interviews.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
            ->where('status', '!=', 'cancelled');
    }

    /**
     * Scope for past interviews.
     */
    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<=', now());
    }

    /**
     * Scope for today's interviews.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    /**
     * Scope for completed interviews.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending interviews.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['scheduled', 'confirmed']);
    }

    /**
     * Scope for interviews by interviewer.
     */
    public function scopeByInterviewer($query, $interviewerId)
    {
        return $query->where('interviewer_id', $interviewerId);
    }

    /**
     * Helper Methods
     */

    /**
     * Confirm the interview.
     */
    public function confirm(): bool
    {
        if (!in_array($this->status, ['scheduled', 'rescheduled'])) {
            return false;
        }

        $this->status = 'confirmed';
        $this->confirmed_at = now();
        
        return $this->save();
    }

    /**
     * Mark interview as completed.
     */
    public function markAsCompleted($score = null, $feedback = null): bool
    {
        if ($this->status === 'completed') {
            return false;
        }

        $this->status = 'completed';
        $this->completed_at = now();
        
        if ($score !== null) {
            $this->interview_score = $score;
        }
        
        if ($feedback !== null) {
            $this->feedback = $feedback;
        }
        
        return $this->save();
    }

    /**
     * Cancel the interview.
     */
    public function cancel($reason = null): bool
    {
        if (in_array($this->status, ['completed', 'cancelled'])) {
            return false;
        }

        $this->status = 'cancelled';
        $this->cancelled_at = now();
        
        if ($reason) {
            $this->cancellation_reason = $reason;
        }
        
        return $this->save();
    }

    /**
     * Mark as no-show.
     */
    public function markAsNoShow(): bool
    {
        if ($this->status !== 'confirmed' || $this->scheduled_at > now()) {
            return false;
        }

        $this->status = 'no_show';
        
        return $this->save();
    }

    /**
     * Reschedule the interview.
     */
    public function reschedule($newDateTime): bool
    {
        if (in_array($this->status, ['completed', 'cancelled'])) {
            return false;
        }

        $this->scheduled_at = $newDateTime;
        $this->status = 'rescheduled';
        $this->confirmed_at = null;
        
        return $this->save();
    }

    /**
     * Get or generate meeting link.
     */
    public function getMeetingLink(): ?string
    {
        if ($this->interview_type !== 'video') {
            return null;
        }

        if ($this->meeting_link) {
            return $this->meeting_link;
        }

        // Generate meeting link (integrate with video service)
        return $this->generateMeetingLink();
    }

    /**
     * Generate meeting link.
     */
    protected function generateMeetingLink(): string
    {
        // This would integrate with Zoom, Google Meet, etc.
        // For now, generate a placeholder link
        $meetingId = 'INT-' . $this->id . '-' . time();
        $this->meeting_id = $meetingId;
        $this->meeting_link = config('app.url') . '/interviews/join/' . $meetingId;
        $this->meeting_password = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
        $this->save();
        
        return $this->meeting_link;
    }

    /**
     * Check if interview is soon (within 24 hours).
     */
    public function isSoon(): bool
    {
        if ($this->status === 'cancelled' || $this->status === 'completed') {
            return false;
        }

        return $this->scheduled_at->isBetween(now(), now()->addDay());
    }

    /**
     * Check if interview is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->scheduled_at < now() && 
               !in_array($this->status, ['completed', 'cancelled', 'no_show']);
    }

    /**
     * Get interview duration in readable format.
     */
    public function getFormattedDuration(): string
    {
        if ($this->duration_minutes < 60) {
            return $this->duration_minutes . ' minutes';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        $result = $hours . ' hour' . ($hours > 1 ? 's' : '');
        if ($minutes > 0) {
            $result .= ' ' . $minutes . ' minutes';
        }
        
        return $result;
    }

    /**
     * Get status label.
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'scheduled' => 'Scheduled',
            'confirmed' => 'Confirmed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            'rescheduled' => 'Rescheduled',
            default => 'Unknown'
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'scheduled' => 'blue',
            'confirmed' => 'green',
            'completed' => 'gray',
            'cancelled' => 'red',
            'no_show' => 'orange',
            'rescheduled' => 'yellow',
            default => 'gray'
        };
    }

    /**
     * Get interview type label.
     */
    public function getTypeLabel(): string
    {
        return match($this->interview_type) {
            'in_person' => 'In Person',
            'phone' => 'Phone',
            'video' => 'Video Call',
            'group' => 'Group Interview',
            default => 'Unknown'
        };
    }

    /**
     * Get interview type icon.
     */
    public function getTypeIcon(): string
    {
        return match($this->interview_type) {
            'in_person' => 'users',
            'phone' => 'phone',
            'video' => 'video',
            'group' => 'user-group',
            default => 'question-mark-circle'
        };
    }

    /**
     * Get location or meeting details.
     */
    public function getMeetingDetails(): string
    {
        switch ($this->interview_type) {
            case 'in_person':
                return $this->location ?: 'Location TBD';
            case 'phone':
                return 'Phone interview - details will be provided';
            case 'video':
                return $this->meeting_link ?: 'Video link will be provided';
            case 'group':
                return $this->location ?: 'Location TBD';
            default:
                return 'Details to be confirmed';
        }
    }

    /**
     * Get evaluation criteria with default values.
     */
    public function getEvaluationCriteria(): array
    {
        return $this->evaluation_criteria ?? [
            'communication_skills' => null,
            'academic_preparedness' => null,
            'motivation' => null,
            'fit_with_program' => null,
            'leadership_potential' => null,
            'overall_impression' => null
        ];
    }

    /**
     * Set evaluation scores.
     */
    public function setEvaluationScores(array $scores): bool
    {
        $this->evaluation_criteria = array_merge(
            $this->getEvaluationCriteria(),
            $scores
        );
        
        // Calculate average score
        $validScores = array_filter($this->evaluation_criteria, 'is_numeric');
        if (count($validScores) > 0) {
            $this->interview_score = round(array_sum($validScores) / count($validScores));
        }
        
        return $this->save();
    }

    /**
     * Send reminder notification.
     */
    public function sendReminder(): void
    {
        // Send reminder to applicant and interviewer
        // Implementation depends on notification system
    }

    /**
     * Get calendar event data.
     */
    public function getCalendarEvent(): array
    {
        $endTime = $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
        
        return [
            'title' => 'Interview: ' . $this->application->first_name . ' ' . $this->application->last_name,
            'start' => $this->scheduled_at->toIso8601String(),
            'end' => $endTime->toIso8601String(),
            'location' => $this->getMeetingDetails(),
            'type' => $this->interview_type,
            'status' => $this->status,
            'color' => $this->getStatusColor()
        ];
    }
}