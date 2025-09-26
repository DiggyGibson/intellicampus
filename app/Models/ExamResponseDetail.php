<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ExamResponseDetail extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'exam_response_details';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'response_id',
        'question_id',
        'question_number',
        'answer',
        'status',
        'time_spent_seconds',
        'first_viewed_at',
        'last_updated_at',
        'visit_count',
        'marks_obtained',
        'is_correct',
        'evaluator_comments'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'answer' => 'array',
        'time_spent_seconds' => 'integer',
        'visit_count' => 'integer',
        'marks_obtained' => 'decimal:2',
        'is_correct' => 'boolean',
        'first_viewed_at' => 'datetime',
        'last_updated_at' => 'datetime'
    ];

    /**
     * Question status progression.
     */
    protected static $statusProgression = [
        'not_visited' => ['not_answered', 'answered', 'marked_review'],
        'not_answered' => ['answered', 'marked_review'],
        'answered' => ['answered_marked_review', 'not_answered'],
        'marked_review' => ['answered_marked_review', 'not_answered'],
        'answered_marked_review' => ['answered', 'marked_review', 'not_answered']
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Set default values on creation
        static::creating(function ($detail) {
            if (!$detail->status) {
                $detail->status = 'not_visited';
            }
            
            if (!$detail->visit_count) {
                $detail->visit_count = 0;
            }
            
            if (!$detail->time_spent_seconds) {
                $detail->time_spent_seconds = 0;
            }
        });

        // Track time and visits on update
        static::updating(function ($detail) {
            // Update last updated time
            if ($detail->isDirty('answer')) {
                $detail->last_updated_at = now();
            }
            
            // Track first view
            if ($detail->isDirty('status') && 
                $detail->getOriginal('status') === 'not_visited' &&
                !$detail->first_viewed_at) {
                $detail->first_viewed_at = now();
                $detail->visit_count = 1;
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the response this detail belongs to.
     */
    public function response()
    {
        return $this->belongsTo(ExamResponse::class, 'response_id');
    }

    /**
     * Get the question for this detail.
     */
    public function question()
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for answered questions.
     */
    public function scopeAnswered($query)
    {
        return $query->whereIn('status', ['answered', 'answered_marked_review']);
    }

    /**
     * Scope for unanswered questions.
     */
    public function scopeUnanswered($query)
    {
        return $query->whereIn('status', ['not_visited', 'not_answered', 'marked_review']);
    }

    /**
     * Scope for questions marked for review.
     */
    public function scopeMarkedForReview($query)
    {
        return $query->whereIn('status', ['marked_review', 'answered_marked_review']);
    }

    /**
     * Scope for visited questions.
     */
    public function scopeVisited($query)
    {
        return $query->where('status', '!=', 'not_visited');
    }

    /**
     * Scope for correct answers.
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope for incorrect answers.
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    /**
     * Scope for evaluated responses.
     */
    public function scopeEvaluated($query)
    {
        return $query->whereNotNull('marks_obtained');
    }

    /**
     * Helper Methods
     */

    /**
     * Update answer for the question.
     */
    public function updateAnswer($answer): bool
    {
        $this->answer = $answer;
        
        // Update status if needed
        if (in_array($this->status, ['not_visited', 'not_answered', 'marked_review'])) {
            $this->status = 'answered';
        } elseif ($this->status === 'answered_marked_review') {
            // Keep the review flag
            $this->status = 'answered_marked_review';
        }
        
        $this->last_updated_at = now();
        
        return $this->save();
    }

    /**
     * Clear the answer.
     */
    public function clearAnswer(): bool
    {
        $this->answer = null;
        
        // Update status
        if ($this->status === 'answered') {
            $this->status = 'not_answered';
        } elseif ($this->status === 'answered_marked_review') {
            $this->status = 'marked_review';
        }
        
        $this->last_updated_at = now();
        
        return $this->save();
    }

    /**
     * Mark question for review.
     */
    public function markForReview(): bool
    {
        if ($this->status === 'answered') {
            $this->status = 'answered_marked_review';
        } elseif (in_array($this->status, ['not_visited', 'not_answered'])) {
            $this->status = 'marked_review';
        }
        
        return $this->save();
    }

    /**
     * Unmark from review.
     */
    public function unmarkFromReview(): bool
    {
        if ($this->status === 'answered_marked_review') {
            $this->status = 'answered';
        } elseif ($this->status === 'marked_review') {
            $this->status = $this->answer ? 'answered' : 'not_answered';
        }
        
        return $this->save();
    }

    /**
     * Record a visit to the question.
     */
    public function recordVisit(): bool
    {
        if ($this->status === 'not_visited') {
            $this->status = 'not_answered';
            $this->first_viewed_at = now();
        }
        
        $this->visit_count++;
        $this->last_updated_at = now();
        
        return $this->save();
    }

    /**
     * Update time spent on the question.
     */
    public function updateTimeSpent($additionalSeconds): bool
    {
        $this->time_spent_seconds += $additionalSeconds;
        
        return $this->save();
    }

    /**
     * Evaluate the answer (auto-grading).
     */
    public function evaluate(): bool
    {
        if (!$this->question) {
            return false;
        }
        
        // Check if question is auto-gradable
        if (!$this->question->isAutoGradable()) {
            return false;
        }
        
        // Grade the response
        $result = $this->question->gradeResponse($this->answer);
        
        $this->is_correct = $result['is_correct'];
        $this->marks_obtained = $result['marks_obtained'];
        
        if (isset($result['evaluator_comments'])) {
            $this->evaluator_comments = $result['evaluator_comments'];
        }
        
        return $this->save();
    }

    /**
     * Manually evaluate the answer.
     */
    public function manuallyEvaluate($marksObtained, $isCorrect = null, $comments = null): bool
    {
        $this->marks_obtained = $marksObtained;
        
        if ($isCorrect !== null) {
            $this->is_correct = $isCorrect;
        }
        
        if ($comments !== null) {
            $this->evaluator_comments = $comments;
        }
        
        return $this->save();
    }

    /**
     * Check if answer is complete.
     */
    public function isAnswered(): bool
    {
        return in_array($this->status, ['answered', 'answered_marked_review']) && 
               !is_null($this->answer);
    }

    /**
     * Check if question was visited.
     */
    public function wasVisited(): bool
    {
        return $this->status !== 'not_visited';
    }

    /**
     * Check if marked for review.
     */
    public function isMarkedForReview(): bool
    {
        return in_array($this->status, ['marked_review', 'answered_marked_review']);
    }

    /**
     * Check if evaluation is pending.
     */
    public function isEvaluationPending(): bool
    {
        return $this->isAnswered() && is_null($this->marks_obtained);
    }

    /**
     * Get formatted answer for display.
     */
    public function getFormattedAnswer(): string
    {
        if (!$this->answer) {
            return 'Not Answered';
        }
        
        if (!$this->question) {
            return json_encode($this->answer);
        }
        
        switch ($this->question->question_type) {
            case 'multiple_choice':
            case 'true_false':
                return is_array($this->answer) ? $this->answer[0] : $this->answer;
                
            case 'multiple_answer':
                return is_array($this->answer) ? implode(', ', $this->answer) : $this->answer;
                
            case 'fill_blanks':
                return is_array($this->answer) ? implode(' | ', $this->answer) : $this->answer;
                
            case 'short_answer':
            case 'essay':
                return is_array($this->answer) ? $this->answer[0] : $this->answer;
                
            case 'numerical':
                return is_array($this->answer) ? $this->answer[0] : $this->answer;
                
            case 'matching':
                if (is_array($this->answer)) {
                    $formatted = [];
                    foreach ($this->answer as $left => $right) {
                        $formatted[] = "{$left} → {$right}";
                    }
                    return implode(', ', $formatted);
                }
                return $this->answer;
                
            case 'ordering':
                return is_array($this->answer) ? implode(' → ', $this->answer) : $this->answer;
                
            default:
                return json_encode($this->answer);
        }
    }

    /**
     * Get time spent in readable format.
     */
    public function getTimeSpentFormatted(): string
    {
        $minutes = floor($this->time_spent_seconds / 60);
        $seconds = $this->time_spent_seconds % 60;
        
        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }
        
        return "{$seconds}s";
    }

    /**
     * Get average time per visit.
     */
    public function getAverageTimePerVisit(): float
    {
        if ($this->visit_count == 0) {
            return 0;
        }
        
        return round($this->time_spent_seconds / $this->visit_count, 2);
    }

    /**
     * Check if answer changed multiple times.
     */
    public function hasMultipleChanges(): bool
    {
        return $this->visit_count > 2 && $this->wasVisited();
    }

    /**
     * Get status label.
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'not_visited' => 'Not Visited',
            'not_answered' => 'Not Answered',
            'answered' => 'Answered',
            'marked_review' => 'Marked for Review',
            'answered_marked_review' => 'Answered & Marked',
            default => ucwords(str_replace('_', ' ', $this->status))
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'not_visited' => 'gray',
            'not_answered' => 'red',
            'answered' => 'green',
            'marked_review' => 'yellow',
            'answered_marked_review' => 'blue',
            default => 'gray'
        };
    }

    /**
     * Get evaluation status.
     */
    public function getEvaluationStatus(): string
    {
        if (!$this->isAnswered()) {
            return 'not_applicable';
        }
        
        if (is_null($this->marks_obtained)) {
            return 'pending';
        }
        
        if ($this->is_correct === true) {
            return 'correct';
        }
        
        if ($this->is_correct === false) {
            return 'incorrect';
        }
        
        return 'partially_correct';
    }

    /**
     * Calculate percentage score.
     */
    public function getPercentageScore(): ?float
    {
        if (!$this->question || is_null($this->marks_obtained)) {
            return null;
        }
        
        if ($this->question->marks == 0) {
            return 0;
        }
        
        return round(($this->marks_obtained / $this->question->marks) * 100, 2);
    }

    /**
     * Check if response is optimal.
     */
    public function isOptimal(): bool
    {
        // Optimal means answered correctly in first attempt with minimal time
        return $this->is_correct === true && 
               $this->visit_count <= 2 && 
               $this->time_spent_seconds < 120; // Less than 2 minutes
    }

    /**
     * Get response analysis.
     */
    public function getAnalysis(): array
    {
        return [
            'question_number' => $this->question_number,
            'question_type' => $this->question->question_type ?? 'unknown',
            'difficulty' => $this->question->difficulty_level ?? 'unknown',
            'status' => $this->status,
            'is_answered' => $this->isAnswered(),
            'is_correct' => $this->is_correct,
            'marks' => [
                'possible' => $this->question->marks ?? 0,
                'obtained' => $this->marks_obtained,
                'percentage' => $this->getPercentageScore()
            ],
            'time' => [
                'spent_seconds' => $this->time_spent_seconds,
                'formatted' => $this->getTimeSpentFormatted(),
                'visits' => $this->visit_count,
                'avg_per_visit' => $this->getAverageTimePerVisit()
            ],
            'behavior' => [
                'multiple_changes' => $this->hasMultipleChanges(),
                'marked_for_review' => $this->isMarkedForReview(),
                'is_optimal' => $this->isOptimal()
            ]
        ];
    }

    /**
     * Generate detail summary.
     */
    public function generateSummary(): array
    {
        return [
            'question' => [
                'number' => $this->question_number,
                'code' => $this->question->question_code ?? null,
                'type' => $this->question->question_type ?? null,
                'marks' => $this->question->marks ?? 0
            ],
            'response' => [
                'status' => $this->status,
                'answer' => $this->getFormattedAnswer(),
                'is_answered' => $this->isAnswered()
            ],
            'evaluation' => [
                'is_correct' => $this->is_correct,
                'marks_obtained' => $this->marks_obtained,
                'percentage' => $this->getPercentageScore(),
                'comments' => $this->evaluator_comments
            ],
            'metrics' => [
                'time_spent' => $this->getTimeSpentFormatted(),
                'visit_count' => $this->visit_count,
                'first_viewed' => $this->first_viewed_at?->format('H:i:s'),
                'last_updated' => $this->last_updated_at?->format('H:i:s')
            ],
            'flags' => [
                'marked_for_review' => $this->isMarkedForReview(),
                'evaluation_pending' => $this->isEvaluationPending(),
                'is_optimal' => $this->isOptimal()
            ]
        ];
    }
}