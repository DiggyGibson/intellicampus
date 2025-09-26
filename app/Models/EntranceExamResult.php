<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntranceExamResult extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'entrance_exam_results';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'registration_id',
        'exam_id',
        'response_id',
        'total_questions_attempted',
        'correct_answers',
        'wrong_answers',
        'unanswered',
        'marks_obtained',
        'negative_marks',
        'final_score',
        'percentage',
        'section_scores',
        'overall_rank',
        'category_rank',
        'center_rank',
        'percentile',
        'result_status',
        'is_qualified',
        'remarks',
        'evaluated_by',
        'evaluated_at',
        'verified_by',
        'verified_at',
        'is_published',
        'published_at',
        'candidate_notified'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'total_questions_attempted' => 'integer',
        'correct_answers' => 'integer',
        'wrong_answers' => 'integer',
        'unanswered' => 'integer',
        'marks_obtained' => 'decimal:2',
        'negative_marks' => 'decimal:2',
        'final_score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'section_scores' => 'array',
        'overall_rank' => 'integer',
        'category_rank' => 'integer',
        'center_rank' => 'integer',
        'percentile' => 'decimal:2',
        'is_qualified' => 'boolean',
        'is_published' => 'boolean',
        'candidate_notified' => 'boolean',
        'evaluated_at' => 'datetime',
        'verified_at' => 'datetime',
        'published_at' => 'datetime'
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Calculate final score when marks are updated
        static::saving(function ($result) {
            if ($result->isDirty(['marks_obtained', 'negative_marks'])) {
                $result->final_score = $result->marks_obtained - abs($result->negative_marks);
            }

            // Calculate percentage if exam total marks available
            if ($result->exam && $result->isDirty('final_score')) {
                $totalMarks = $result->exam->total_marks;
                if ($totalMarks > 0) {
                    $result->percentage = ($result->final_score / $totalMarks) * 100;
                }
            }

            // Determine qualification status
            if ($result->exam && $result->isDirty('final_score')) {
                $result->is_qualified = $result->final_score >= $result->exam->passing_marks;
                $result->result_status = $result->is_qualified ? 'pass' : 'fail';
            }
        });

        // Set published timestamp
        static::updating(function ($result) {
            if ($result->isDirty('is_published') && $result->is_published && !$result->published_at) {
                $result->published_at = now();
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the exam registration.
     */
    public function registration()
    {
        return $this->belongsTo(EntranceExamRegistration::class, 'registration_id');
    }

    /**
     * Get the exam.
     */
    public function exam()
    {
        return $this->belongsTo(EntranceExam::class, 'exam_id');
    }

    /**
     * Get the exam response.
     */
    public function response()
    {
        return $this->belongsTo(ExamResponse::class, 'response_id');
    }

    /**
     * Get the evaluator.
     */
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }

    /**
     * Get the verifier.
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the certificate if generated.
     */
    public function certificate()
    {
        return $this->hasOne(ExamCertificate::class, 'result_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for published results.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for unpublished results.
     */
    public function scopeUnpublished($query)
    {
        return $query->where('is_published', false);
    }

    /**
     * Scope for qualified candidates.
     */
    public function scopeQualified($query)
    {
        return $query->where('is_qualified', true);
    }

    /**
     * Scope for passed candidates.
     */
    public function scopePassed($query)
    {
        return $query->where('result_status', 'pass');
    }

    /**
     * Scope for failed candidates.
     */
    public function scopeFailed($query)
    {
        return $query->where('result_status', 'fail');
    }

    /**
     * Scope for top performers.
     */
    public function scopeTopPerformers($query, $limit = 10)
    {
        return $query->whereNotNull('overall_rank')
            ->orderBy('overall_rank')
            ->limit($limit);
    }

    /**
     * Scope for results by percentile range.
     */
    public function scopeInPercentileRange($query, $min, $max)
    {
        return $query->whereBetween('percentile', [$min, $max]);
    }

    /**
     * Helper Methods
     */

    /**
     * Calculate and update result from response.
     */
    public function calculateFromResponse(): void
    {
        if (!$this->response) {
            return;
        }

        $responseDetails = $this->response->responseDetails;
        
        // Count questions
        $this->total_questions_attempted = $responseDetails->whereIn('status', [
            'answered', 
            'answered_marked_review'
        ])->count();
        
        $this->correct_answers = $responseDetails->where('is_correct', true)->count();
        $this->wrong_answers = $responseDetails->where('is_correct', false)->count();
        $this->unanswered = $responseDetails->whereIn('status', [
            'not_visited', 
            'not_answered', 
            'marked_review'
        ])->count();

        // Calculate marks
        $this->marks_obtained = $responseDetails->sum('marks_obtained');
        $this->negative_marks = abs($responseDetails->where('marks_obtained', '<', 0)->sum('marks_obtained'));
        $this->final_score = $this->marks_obtained - $this->negative_marks;

        // Calculate section-wise scores
        $this->calculateSectionScores();

        $this->save();
    }

    /**
     * Calculate section-wise scores.
     */
    protected function calculateSectionScores(): void
    {
        if (!$this->exam || !$this->response) {
            return;
        }

        $sections = $this->exam->sections ?? [];
        $sectionScores = [];

        foreach ($sections as $section) {
            $sectionQuestions = $this->response->responseDetails()
                ->whereHas('question', function ($q) use ($section) {
                    $q->where('subject', $section['name']);
                })
                ->get();

            $sectionScores[$section['name']] = [
                'attempted' => $sectionQuestions->whereIn('status', ['answered', 'answered_marked_review'])->count(),
                'correct' => $sectionQuestions->where('is_correct', true)->count(),
                'marks' => $sectionQuestions->sum('marks_obtained'),
                'max_marks' => $section['marks'] ?? 0,
                'percentage' => 0
            ];

            if ($sectionScores[$section['name']]['max_marks'] > 0) {
                $sectionScores[$section['name']]['percentage'] = 
                    ($sectionScores[$section['name']]['marks'] / $sectionScores[$section['name']]['max_marks']) * 100;
            }
        }

        $this->section_scores = $sectionScores;
    }

    /**
     * Calculate ranks among all results for the exam.
     */
    public function calculateRanks(): void
    {
        if (!$this->exam_id) {
            return;
        }

        // Get all results for this exam
        $allResults = self::where('exam_id', $this->exam_id)
            ->where('result_status', '!=', 'absent')
            ->orderBy('final_score', 'desc')
            ->get();

        $totalCandidates = $allResults->count();
        
        // Calculate overall rank
        $rank = 1;
        $prevScore = null;
        $sameRankCount = 0;

        foreach ($allResults as $index => $result) {
            if ($prevScore !== null && $result->final_score < $prevScore) {
                $rank += $sameRankCount + 1;
                $sameRankCount = 0;
            } elseif ($prevScore !== null && $result->final_score == $prevScore) {
                $sameRankCount++;
            }

            if ($result->id == $this->id) {
                $this->overall_rank = $rank;
                
                // Calculate percentile
                $candidatesBelowScore = $allResults->where('final_score', '<', $this->final_score)->count();
                $this->percentile = ($candidatesBelowScore / $totalCandidates) * 100;
                break;
            }

            $prevScore = $result->final_score;
        }

        // Calculate category rank if applicable
        if ($this->registration && $this->registration->candidate_category) {
            $categoryResults = self::where('exam_id', $this->exam_id)
                ->whereHas('registration', function ($q) {
                    $q->where('candidate_category', $this->registration->candidate_category);
                })
                ->orderBy('final_score', 'desc')
                ->get();

            $this->category_rank = $categoryResults->search(function ($item) {
                return $item->id == $this->id;
            }) + 1;
        }

        // Calculate center rank
        if ($this->registration && $this->registration->center_id) {
            $centerResults = self::where('exam_id', $this->exam_id)
                ->whereHas('registration', function ($q) {
                    $q->where('center_id', $this->registration->center_id);
                })
                ->orderBy('final_score', 'desc')
                ->get();

            $this->center_rank = $centerResults->search(function ($item) {
                return $item->id == $this->id;
            }) + 1;
        }

        $this->save();
    }

    /**
     * Publish the result.
     */
    public function publish(): bool
    {
        $this->is_published = true;
        $this->published_at = now();
        return $this->save();
    }

    /**
     * Unpublish the result.
     */
    public function unpublish(): bool
    {
        $this->is_published = false;
        return $this->save();
    }

    /**
     * Mark as evaluated.
     */
    public function markAsEvaluated($userId = null): bool
    {
        $this->evaluated_by = $userId ?? auth()->id();
        $this->evaluated_at = now();
        return $this->save();
    }

    /**
     * Mark as verified.
     */
    public function markAsVerified($userId = null): bool
    {
        $this->verified_by = $userId ?? auth()->id();
        $this->verified_at = now();
        return $this->save();
    }

    /**
     * Send result notification to candidate.
     */
    public function notifyCandidate(): bool
    {
        // Implementation would send email/SMS
        // This is a placeholder for notification logic
        
        $this->candidate_notified = true;
        return $this->save();
    }

    /**
     * Get grade based on percentage.
     */
    public function getGrade(): string
    {
        $percentage = $this->percentage;

        return match(true) {
            $percentage >= 90 => 'A+',
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B+',
            $percentage >= 60 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 40 => 'D',
            default => 'F'
        };
    }

    /**
     * Get performance level.
     */
    public function getPerformanceLevel(): string
    {
        $percentile = $this->percentile;

        return match(true) {
            $percentile >= 95 => 'Outstanding',
            $percentile >= 85 => 'Excellent',
            $percentile >= 75 => 'Very Good',
            $percentile >= 60 => 'Good',
            $percentile >= 50 => 'Above Average',
            $percentile >= 40 => 'Average',
            $percentile >= 25 => 'Below Average',
            default => 'Poor'
        };
    }

    /**
     * Get result status label.
     */
    public function getStatusLabel(): string
    {
        return match($this->result_status) {
            'pass' => 'Passed',
            'fail' => 'Failed',
            'absent' => 'Absent',
            'disqualified' => 'Disqualified',
            'withheld' => 'Withheld',
            'under_review' => 'Under Review',
            default => 'Unknown'
        };
    }

    /**
     * Get result status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->result_status) {
            'pass' => 'green',
            'fail' => 'red',
            'absent' => 'gray',
            'disqualified' => 'red',
            'withheld' => 'orange',
            'under_review' => 'yellow',
            default => 'gray'
        };
    }

    /**
     * Format result for display.
     */
    public function getFormattedResult(): array
    {
        return [
            'score' => number_format($this->final_score, 2) . ' / ' . $this->exam->total_marks,
            'percentage' => number_format($this->percentage, 2) . '%',
            'rank' => $this->overall_rank ? "#{$this->overall_rank}" : 'N/A',
            'percentile' => $this->percentile ? number_format($this->percentile, 2) . ' percentile' : 'N/A',
            'grade' => $this->getGrade(),
            'status' => $this->getStatusLabel(),
            'performance' => $this->getPerformanceLevel()
        ];
    }

    /**
     * Check if result can be challenged.
     */
    public function canBeChallenged(): bool
    {
        if (!$this->exam || !$this->is_published) {
            return false;
        }

        // Check if within challenge period
        $challengeDeadline = $this->published_at?->addDays($this->exam->review_period_days ?? 7);
        
        return $challengeDeadline && now() <= $challengeDeadline;
    }
}