<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ApplicationReview extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'application_reviews';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'reviewer_id',
        'review_stage',
        'academic_rating',
        'extracurricular_rating',
        'essay_rating',
        'recommendation_rating',
        'interview_rating',
        'overall_rating',
        'academic_comments',
        'extracurricular_comments',
        'essay_comments',
        'strengths',
        'weaknesses',
        'additional_comments',
        'recommendation',
        'status',
        'assigned_at',
        'started_at',
        'completed_at',
        'review_duration_minutes'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'academic_rating' => 'integer',
        'extracurricular_rating' => 'integer',
        'essay_rating' => 'integer',
        'recommendation_rating' => 'integer',
        'interview_rating' => 'integer',
        'overall_rating' => 'integer',
        'review_duration_minutes' => 'integer',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Review stages with their order.
     */
    protected static $stageOrder = [
        'initial_review' => 1,
        'academic_review' => 2,
        'department_review' => 3,
        'committee_review' => 4,
        'final_review' => 5
    ];

    /**
     * Rating criteria weights for calculating overall score.
     */
    protected static $ratingWeights = [
        'academic_rating' => 0.35,
        'extracurricular_rating' => 0.20,
        'essay_rating' => 0.20,
        'recommendation_rating' => 0.15,
        'interview_rating' => 0.10
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Set assigned_at when creating
        static::creating(function ($review) {
            if (!$review->assigned_at) {
                $review->assigned_at = now();
            }
        });

        // Calculate review duration when completing
        static::updating(function ($review) {
            if ($review->isDirty('status') && $review->status === 'completed') {
                if (!$review->completed_at) {
                    $review->completed_at = now();
                }
                
                if ($review->started_at && $review->completed_at) {
                    $review->review_duration_minutes = $review->started_at->diffInMinutes($review->completed_at);
                }
            }
            
            // Set started_at when changing from pending to in_progress
            if ($review->isDirty('status') && 
                $review->getOriginal('status') === 'pending' && 
                $review->status === 'in_progress' &&
                !$review->started_at) {
                $review->started_at = now();
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the application being reviewed.
     */
    public function application()
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }

    /**
     * Get the reviewer.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for pending reviews.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed reviews.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for reviews in progress.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for reviews by stage.
     */
    public function scopeByStage($query, $stage)
    {
        return $query->where('review_stage', $stage);
    }

    /**
     * Scope for overdue reviews.
     */
    public function scopeOverdue($query, $days = 7)
    {
        return $query->where('status', 'pending')
            ->where('assigned_at', '<=', now()->subDays($days));
    }

    /**
     * Scope for reviews by reviewer.
     */
    public function scopeByReviewer($query, $reviewerId)
    {
        return $query->where('reviewer_id', $reviewerId);
    }

    /**
     * Helper Methods
     */

    /**
     * Start the review process.
     */
    public function start(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }
        
        $this->status = 'in_progress';
        $this->started_at = now();
        
        return $this->save();
    }

    /**
     * Complete the review.
     */
    public function complete(): bool
    {
        if ($this->status === 'completed') {
            return false;
        }
        
        // Validate that all required ratings are provided
        if (!$this->isComplete()) {
            return false;
        }
        
        $this->status = 'completed';
        $this->completed_at = now();
        
        if ($this->started_at) {
            $this->review_duration_minutes = $this->started_at->diffInMinutes($this->completed_at);
        }
        
        return $this->save();
    }

    /**
     * Skip the review.
     */
    public function skip($reason = null): bool
    {
        $this->status = 'skipped';
        $this->additional_comments = $reason ?? 'Review skipped';
        
        return $this->save();
    }

    /**
     * Check if review has all required ratings.
     */
    public function isComplete(): bool
    {
        $requiredRatings = ['academic_rating', 'overall_rating'];
        
        foreach ($requiredRatings as $rating) {
            if (is_null($this->$rating)) {
                return false;
            }
        }
        
        return !is_null($this->recommendation);
    }

    /**
     * Calculate weighted overall score.
     */
    public function calculateWeightedScore(): ?float
    {
        $totalWeight = 0;
        $weightedSum = 0;
        
        foreach (self::$ratingWeights as $rating => $weight) {
            if (!is_null($this->$rating)) {
                $weightedSum += $this->$rating * $weight;
                $totalWeight += $weight;
            }
        }
        
        if ($totalWeight == 0) {
            return null;
        }
        
        return round($weightedSum / $totalWeight, 2);
    }

    /**
     * Get average rating across all criteria.
     */
    public function getAverageRating(): ?float
    {
        $ratings = array_filter([
            $this->academic_rating,
            $this->extracurricular_rating,
            $this->essay_rating,
            $this->recommendation_rating,
            $this->interview_rating
        ], function ($rating) {
            return !is_null($rating);
        });
        
        if (empty($ratings)) {
            return null;
        }
        
        return round(array_sum($ratings) / count($ratings), 2);
    }

    /**
     * Get the stage order number.
     */
    public function getStageOrder(): int
    {
        return self::$stageOrder[$this->review_stage] ?? 999;
    }

    /**
     * Check if this is the final review stage.
     */
    public function isFinalStage(): bool
    {
        return $this->review_stage === 'final_review';
    }

    /**
     * Get the next review stage.
     */
    public function getNextStage(): ?string
    {
        $currentOrder = $this->getStageOrder();
        
        foreach (self::$stageOrder as $stage => $order) {
            if ($order === $currentOrder + 1) {
                return $stage;
            }
        }
        
        return null;
    }

    /**
     * Get recommendation label.
     */
    public function getRecommendationLabel(): string
    {
        return match($this->recommendation) {
            'strongly_recommend' => 'Strongly Recommend',
            'recommend' => 'Recommend',
            'recommend_with_reservations' => 'Recommend with Reservations',
            'do_not_recommend' => 'Do Not Recommend',
            'defer_decision' => 'Defer Decision',
            default => 'Pending'
        };
    }

    /**
     * Get recommendation color for UI.
     */
    public function getRecommendationColor(): string
    {
        return match($this->recommendation) {
            'strongly_recommend' => 'green',
            'recommend' => 'blue',
            'recommend_with_reservations' => 'yellow',
            'do_not_recommend' => 'red',
            'defer_decision' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get status badge color.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'in_progress' => 'blue',
            'completed' => 'green',
            'skipped' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Check if review is overdue.
     */
    public function isOverdue($days = 7): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }
        
        return $this->assigned_at->diffInDays(now()) > $days;
    }

    /**
     * Get days since assignment.
     */
    public function getDaysSinceAssignment(): int
    {
        return $this->assigned_at->diffInDays(now());
    }

    /**
     * Generate review summary.
     */
    public function generateSummary(): array
    {
        return [
            'reviewer' => $this->reviewer->name ?? 'Unknown',
            'stage' => $this->review_stage,
            'status' => $this->status,
            'ratings' => [
                'academic' => $this->academic_rating,
                'extracurricular' => $this->extracurricular_rating,
                'essay' => $this->essay_rating,
                'recommendation' => $this->recommendation_rating,
                'interview' => $this->interview_rating,
                'overall' => $this->overall_rating,
                'weighted_score' => $this->calculateWeightedScore(),
                'average' => $this->getAverageRating()
            ],
            'recommendation' => $this->recommendation,
            'completed_at' => $this->completed_at?->format('Y-m-d H:i:s'),
            'duration_minutes' => $this->review_duration_minutes,
            'comments' => [
                'strengths' => $this->strengths,
                'weaknesses' => $this->weaknesses,
                'additional' => $this->additional_comments
            ]
        ];
    }

    /**
     * Check if reviewer can edit this review.
     */
    public function canEdit($userId = null): bool
    {
        $userId = $userId ?? auth()->id();
        
        return $this->reviewer_id === $userId && 
               in_array($this->status, ['pending', 'in_progress']);
    }
}