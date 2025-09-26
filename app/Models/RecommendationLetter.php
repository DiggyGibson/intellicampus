<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RecommendationLetter extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'recommendation_letters';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'recommender_name',
        'recommender_email',
        'recommender_phone',
        'recommender_title',
        'recommender_institution',
        'relationship_to_applicant',
        'years_known',
        'request_token',
        'request_sent_at',
        'reminder_sent_at',
        'reminder_count',
        'status',
        'letter_content',
        'letter_file_path',
        'ratings',
        'submitted_at',
        'declined_at',
        'decline_reason',
        'waived_right_to_view'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'years_known' => 'integer',
        'reminder_count' => 'integer',
        'ratings' => 'array',
        'request_sent_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'submitted_at' => 'datetime',
        'declined_at' => 'datetime',
        'waived_right_to_view' => 'boolean'
    ];

    /**
     * Rating categories for recommendations.
     */
    const RATING_CATEGORIES = [
        'academic_ability' => 'Academic Ability',
        'intellectual_curiosity' => 'Intellectual Curiosity',
        'creativity' => 'Creativity and Innovation',
        'leadership' => 'Leadership Potential',
        'communication' => 'Communication Skills',
        'teamwork' => 'Teamwork and Collaboration',
        'integrity' => 'Integrity and Character',
        'motivation' => 'Motivation and Drive',
        'resilience' => 'Resilience and Perseverance',
        'overall' => 'Overall Recommendation'
    ];

    /**
     * Rating scale options.
     */
    const RATING_SCALE = [
        5 => 'Exceptional (Top 5%)',
        4 => 'Excellent (Top 10%)',
        3 => 'Very Good (Top 25%)',
        2 => 'Good (Top 50%)',
        1 => 'Average (Bottom 50%)'
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate unique token on creation
        static::creating(function ($letter) {
            if (!$letter->request_token) {
                $letter->request_token = self::generateUniqueToken();
            }
            
            // Default to waiving right to view
            if (is_null($letter->waived_right_to_view)) {
                $letter->waived_right_to_view = true;
            }
        });

        // Update status timestamps
        static::updating(function ($letter) {
            if ($letter->isDirty('status')) {
                switch ($letter->status) {
                    case 'invited':
                        if (!$letter->request_sent_at) {
                            $letter->request_sent_at = now();
                        }
                        break;
                    case 'submitted':
                        if (!$letter->submitted_at) {
                            $letter->submitted_at = now();
                        }
                        break;
                    case 'declined':
                        if (!$letter->declined_at) {
                            $letter->declined_at = now();
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
     * Scopes
     */

    /**
     * Scope for pending letters.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'invited']);
    }

    /**
     * Scope for submitted letters.
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope for in-progress letters.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for declined letters.
     */
    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    /**
     * Scope for overdue letters.
     */
    public function scopeOverdue($query, $days = 14)
    {
        return $query->where('status', 'invited')
            ->where('request_sent_at', '<=', now()->subDays($days));
    }

    /**
     * Scope for letters needing reminders.
     */
    public function scopeNeedingReminder($query, $daysSinceLastContact = 7)
    {
        return $query->where('status', 'invited')
            ->where(function ($q) use ($daysSinceLastContact) {
                $q->whereNull('reminder_sent_at')
                    ->where('request_sent_at', '<=', now()->subDays($daysSinceLastContact))
                    ->orWhere('reminder_sent_at', '<=', now()->subDays($daysSinceLastContact));
            });
    }

    /**
     * Helper Methods
     */

    /**
     * Generate unique request token.
     */
    public static function generateUniqueToken(): string
    {
        do {
            $token = Str::random(32);
        } while (self::where('request_token', $token)->exists());

        return $token;
    }

    /**
     * Send invitation to recommender.
     */
    public function sendInvitation(): bool
    {
        if (!in_array($this->status, ['pending'])) {
            return false;
        }

        // Send email invitation
        // Mail::to($this->recommender_email)->send(new RecommendationRequest($this));

        $this->status = 'invited';
        $this->request_sent_at = now();
        
        return $this->save();
    }

    /**
     * Send reminder to recommender.
     */
    public function sendReminder(): bool
    {
        if ($this->status !== 'invited') {
            return false;
        }

        // Check if too many reminders already sent
        if ($this->reminder_count >= 3) {
            return false;
        }

        // Send reminder email
        // Mail::to($this->recommender_email)->send(new RecommendationReminder($this));

        $this->reminder_sent_at = now();
        $this->reminder_count++;
        
        return $this->save();
    }

    /**
     * Mark as in progress.
     */
    public function markInProgress(): bool
    {
        if (!in_array($this->status, ['invited'])) {
            return false;
        }

        $this->status = 'in_progress';
        
        return $this->save();
    }

    /**
     * Submit the recommendation.
     */
    public function submit(array $data): bool
    {
        if (!in_array($this->status, ['invited', 'in_progress'])) {
            return false;
        }

        $this->letter_content = $data['letter_content'] ?? null;
        $this->letter_file_path = $data['letter_file_path'] ?? null;
        $this->ratings = $data['ratings'] ?? null;
        $this->status = 'submitted';
        $this->submitted_at = now();
        
        $saved = $this->save();
        
        if ($saved) {
            // Notify applicant/admissions office
            $this->notifySubmission();
        }
        
        return $saved;
    }

    /**
     * Decline to provide recommendation.
     */
    public function decline($reason = null): bool
    {
        if (!in_array($this->status, ['invited', 'in_progress'])) {
            return false;
        }

        $this->status = 'declined';
        $this->declined_at = now();
        $this->decline_reason = $reason;
        
        $saved = $this->save();
        
        if ($saved) {
            // Notify applicant
            $this->notifyDecline();
        }
        
        return $saved;
    }

    /**
     * Mark as expired.
     */
    public function markExpired(): bool
    {
        if (!in_array($this->status, ['invited', 'in_progress'])) {
            return false;
        }

        $this->status = 'expired';
        
        return $this->save();
    }

    /**
     * Check if reminder can be sent.
     */
    public function canSendReminder($daysSinceLastReminder = 7): bool
    {
        if ($this->status !== 'invited') {
            return false;
        }

        if ($this->reminder_count >= 3) {
            return false;
        }

        if (!$this->reminder_sent_at) {
            return $this->request_sent_at <= now()->subDays($daysSinceLastReminder);
        }

        return $this->reminder_sent_at <= now()->subDays($daysSinceLastReminder);
    }

    /**
     * Get submission URL for recommender.
     */
    public function getSubmissionUrl(): string
    {
        return route('recommendations.submit', ['token' => $this->request_token]);
    }

    /**
     * Get days since request.
     */
    public function getDaysSinceRequest(): ?int
    {
        if (!$this->request_sent_at) {
            return null;
        }

        return $this->request_sent_at->diffInDays(now());
    }

    /**
     * Get days until deadline.
     */
    public function getDaysUntilDeadline(): ?int
    {
        if (!$this->application || !$this->application->term) {
            return null;
        }

        $deadline = $this->application->term->application_close_date;
        
        if (!$deadline || $deadline < now()) {
            return 0;
        }

        return now()->diffInDays($deadline);
    }

    /**
     * Get average rating.
     */
    public function getAverageRating(): ?float
    {
        if (!$this->ratings || !is_array($this->ratings)) {
            return null;
        }

        $numericRatings = array_filter($this->ratings, 'is_numeric');
        
        if (empty($numericRatings)) {
            return null;
        }

        return round(array_sum($numericRatings) / count($numericRatings), 2);
    }

    /**
     * Get rating for specific category.
     */
    public function getRating($category): ?int
    {
        return $this->ratings[$category] ?? null;
    }

    /**
     * Get formatted ratings.
     */
    public function getFormattedRatings(): array
    {
        $formatted = [];
        
        foreach (self::RATING_CATEGORIES as $key => $label) {
            $rating = $this->getRating($key);
            $formatted[$key] = [
                'label' => $label,
                'rating' => $rating,
                'description' => $rating ? self::RATING_SCALE[$rating] : 'Not Rated'
            ];
        }
        
        return $formatted;
    }

    /**
     * Get recommender info formatted.
     */
    public function getRecommenderInfo(): string
    {
        $info = $this->recommender_name;
        
        if ($this->recommender_title) {
            $info .= ', ' . $this->recommender_title;
        }
        
        if ($this->recommender_institution) {
            $info .= ' at ' . $this->recommender_institution;
        }
        
        return $info;
    }

    /**
     * Get relationship description.
     */
    public function getRelationshipDescription(): string
    {
        $description = $this->relationship_to_applicant;
        
        if ($this->years_known) {
            $years = $this->years_known == 1 ? '1 year' : $this->years_known . ' years';
            $description .= ' (known for ' . $years . ')';
        }
        
        return $description;
    }

    /**
     * Check if letter can be viewed by applicant.
     */
    public function canBeViewedByApplicant(): bool
    {
        return $this->status === 'submitted' && !$this->waived_right_to_view;
    }

    /**
     * Get status label.
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending Invitation',
            'invited' => 'Invitation Sent',
            'in_progress' => 'In Progress',
            'submitted' => 'Submitted',
            'declined' => 'Declined',
            'expired' => 'Expired',
            default => 'Unknown'
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'gray',
            'invited' => 'blue',
            'in_progress' => 'yellow',
            'submitted' => 'green',
            'declined' => 'red',
            'expired' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get status icon.
     */
    public function getStatusIcon(): string
    {
        return match($this->status) {
            'pending' => 'clock',
            'invited' => 'mail',
            'in_progress' => 'pencil',
            'submitted' => 'check-circle',
            'declined' => 'x-circle',
            'expired' => 'exclamation-circle',
            default => 'question-mark-circle'
        };
    }

    /**
     * Notify about submission.
     */
    protected function notifySubmission(): void
    {
        // Notify applicant and admissions office
        // Implementation depends on notification system
    }

    /**
     * Notify about decline.
     */
    protected function notifyDecline(): void
    {
        // Notify applicant
        // Implementation depends on notification system
    }

    /**
     * Process expired recommendations.
     */
    public static function processExpired($daysUntilExpiry = 30): int
    {
        $expired = self::where('status', 'invited')
            ->where('request_sent_at', '<=', now()->subDays($daysUntilExpiry))
            ->update(['status' => 'expired']);
        
        return $expired;
    }

    /**
     * Send bulk reminders.
     */
    public static function sendBulkReminders(): int
    {
        $sent = 0;
        $needingReminder = self::needingReminder()->get();
        
        foreach ($needingReminder as $letter) {
            if ($letter->sendReminder()) {
                $sent++;
            }
        }
        
        return $sent;
    }

    /**
     * Get statistics for an application.
     */
    public static function getApplicationStatistics($applicationId): array
    {
        $letters = self::where('application_id', $applicationId)->get();
        
        return [
            'total_requested' => $letters->count(),
            'submitted' => $letters->where('status', 'submitted')->count(),
            'pending' => $letters->whereIn('status', ['pending', 'invited', 'in_progress'])->count(),
            'declined' => $letters->where('status', 'declined')->count(),
            'average_rating' => $letters->where('status', 'submitted')
                ->map(fn($l) => $l->getAverageRating())
                ->filter()
                ->average(),
            'submission_rate' => $letters->count() > 0 
                ? round(($letters->where('status', 'submitted')->count() / $letters->count()) * 100, 2)
                : 0
        ];
    }
}