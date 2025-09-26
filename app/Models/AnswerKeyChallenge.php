<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnswerKeyChallenge extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'answer_key_challenges';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'registration_id',
        'question_id',
        'answer_key_id',
        'challenge_reason',
        'supporting_documents',
        'status',
        'review_comments',
        'reviewed_by',
        'reviewed_at',
        'suggested_answer',
        'reference_materials',
        'expert_opinion'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'supporting_documents' => 'array',
        'reviewed_at' => 'datetime',
        'suggested_answer' => 'array',
        'reference_materials' => 'array'
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Set reviewed timestamp when status changes
        static::updating(function ($challenge) {
            if ($challenge->isDirty('status') && 
                in_array($challenge->status, ['accepted', 'rejected']) && 
                !$challenge->reviewed_at) {
                $challenge->reviewed_at = now();
                
                if (!$challenge->reviewed_by) {
                    $challenge->reviewed_by = auth()->id();
                }
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the registration that submitted the challenge.
     */
    public function registration()
    {
        return $this->belongsTo(EntranceExamRegistration::class, 'registration_id');
    }

    /**
     * Get the question being challenged.
     */
    public function question()
    {
        return $this->belongsTo(ExamQuestion::class, 'question_id');
    }

    /**
     * Get the answer key being challenged.
     */
    public function answerKey()
    {
        return $this->belongsTo(ExamAnswerKey::class, 'answer_key_id');
    }

    /**
     * Get the reviewer.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scopes
     */

    /**
     * Scope for pending challenges.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for under review challenges.
     */
    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    /**
     * Scope for accepted challenges.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope for rejected challenges.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for reviewed challenges.
     */
    public function scopeReviewed($query)
    {
        return $query->whereIn('status', ['accepted', 'rejected']);
    }

    /**
     * Helper Methods
     */

    /**
     * Submit the challenge.
     */
    public function submit(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        // Validate that challenge period is still open
        if (!$this->isWithinChallengeWindow()) {
            return false;
        }

        $this->status = 'under_review';
        return $this->save();
    }

    /**
     * Accept the challenge.
     */
    public function accept($comments = null, $reviewerId = null): bool
    {
        $this->status = 'accepted';
        $this->review_comments = $comments;
        $this->reviewed_by = $reviewerId ?? auth()->id();
        $this->reviewed_at = now();
        
        $saved = $this->save();

        if ($saved) {
            // Update the answer key if needed
            $this->applyToAnswerKey();
            
            // Recalculate affected results
            $this->recalculateAffectedResults();
        }

        return $saved;
    }

    /**
     * Reject the challenge.
     */
    public function reject($comments = null, $reviewerId = null): bool
    {
        $this->status = 'rejected';
        $this->review_comments = $comments;
        $this->reviewed_by = $reviewerId ?? auth()->id();
        $this->reviewed_at = now();
        
        return $this->save();
    }

    /**
     * Apply accepted challenge to answer key.
     */
    protected function applyToAnswerKey(): void
    {
        if ($this->status !== 'accepted' || !$this->answerKey || !$this->suggested_answer) {
            return;
        }

        // Update the answer in the answer key
        $this->answerKey->setAnswerForQuestion(
            $this->question_id,
            $this->suggested_answer
        );

        // If this was a provisional key, it might need to be marked as updated
        if ($this->answerKey->key_type === 'provisional') {
            // Log the change or create a new version
            $this->logAnswerKeyChange();
        }
    }

    /**
     * Recalculate results affected by this challenge.
     */
    protected function recalculateAffectedResults(): void
    {
        if ($this->status !== 'accepted') {
            return;
        }

        // Find all results for this exam
        $exam = $this->answerKey->exam;
        if (!$exam) {
            return;
        }

        // Get all responses that answered this question
        $affectedResponses = ExamResponse::whereHas('responseDetails', function ($query) {
            $query->where('question_id', $this->question_id);
        })->where('session_id', function ($query) use ($exam) {
            $query->select('id')
                ->from('exam_sessions')
                ->where('exam_id', $exam->id);
        })->get();

        foreach ($affectedResponses as $response) {
            // Re-evaluate the specific question
            $responseDetail = $response->responseDetails()
                ->where('question_id', $this->question_id)
                ->first();
            
            if ($responseDetail) {
                $responseDetail->evaluate();
                
                // Recalculate the overall result
                $result = EntranceExamResult::where('response_id', $response->id)->first();
                if ($result) {
                    $result->calculateFromResponse();
                }
            }
        }
    }

    /**
     * Check if challenge is within allowed window.
     */
    public function isWithinChallengeWindow(): bool
    {
        $answerKey = $this->answerKey;
        if (!$answerKey || !$answerKey->published_at) {
            return false;
        }

        $exam = $answerKey->exam;
        $challengeWindowDays = $exam->review_period_days ?? 7;
        $deadline = $answerKey->published_at->addDays($challengeWindowDays);
        
        return now() <= $deadline;
    }

    /**
     * Upload supporting document.
     */
    public function addSupportingDocument($path, $type = 'general'): bool
    {
        $documents = $this->supporting_documents ?? [];
        $documents[] = [
            'path' => $path,
            'type' => $type,
            'uploaded_at' => now()->toIso8601String()
        ];
        
        $this->supporting_documents = $documents;
        return $this->save();
    }

    /**
     * Add reference material.
     */
    public function addReferenceMaterial($title, $source, $page = null): bool
    {
        $materials = $this->reference_materials ?? [];
        $materials[] = [
            'title' => $title,
            'source' => $source,
            'page' => $page,
            'added_at' => now()->toIso8601String()
        ];
        
        $this->reference_materials = $materials;
        return $this->save();
    }

    /**
     * Set expert opinion.
     */
    public function setExpertOpinion($opinion, $expertName = null): bool
    {
        $this->expert_opinion = [
            'opinion' => $opinion,
            'expert_name' => $expertName,
            'provided_at' => now()->toIso8601String()
        ];
        
        return $this->save();
    }

    /**
     * Get status label.
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending Review',
            'under_review' => 'Under Review',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
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
            'under_review' => 'yellow',
            'accepted' => 'green',
            'rejected' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get the original answer from answer key.
     */
    public function getOriginalAnswer()
    {
        if (!$this->answerKey) {
            return null;
        }

        return $this->answerKey->getAnswerForQuestion($this->question_id);
    }

    /**
     * Get the candidate's answer.
     */
    public function getCandidateAnswer()
    {
        $response = ExamResponse::where('registration_id', $this->registration_id)
            ->whereHas('session', function ($query) {
                $query->where('exam_id', $this->answerKey->exam_id);
            })
            ->first();

        if (!$response) {
            return null;
        }

        $responseDetail = $response->responseDetails()
            ->where('question_id', $this->question_id)
            ->first();

        return $responseDetail ? $responseDetail->answer : null;
    }

    /**
     * Calculate potential score change if accepted.
     */
    public function calculatePotentialScoreChange(): array
    {
        $candidateAnswer = $this->getCandidateAnswer();
        $originalAnswer = $this->getOriginalAnswer();
        $suggestedAnswer = $this->suggested_answer;

        $question = $this->question;
        if (!$question) {
            return ['change' => 0, 'reason' => 'Question not found'];
        }

        $currentlyCorrect = $this->compareAnswers($candidateAnswer, $originalAnswer);
        $wouldBeCorrect = $this->compareAnswers($candidateAnswer, $suggestedAnswer);

        $change = 0;
        $reason = '';

        if (!$currentlyCorrect && $wouldBeCorrect) {
            // Would gain marks
            $change = $question->marks + ($question->negative_marks ?? 0);
            $reason = 'Would be marked correct instead of wrong';
        } elseif ($currentlyCorrect && !$wouldBeCorrect) {
            // Would lose marks
            $change = -($question->marks + ($question->negative_marks ?? 0));
            $reason = 'Would be marked wrong instead of correct';
        } else {
            $reason = 'No change in scoring';
        }

        return [
            'change' => $change,
            'reason' => $reason,
            'currently_correct' => $currentlyCorrect,
            'would_be_correct' => $wouldBeCorrect
        ];
    }

    /**
     * Compare two answers for equality.
     */
    protected function compareAnswers($answer1, $answer2): bool
    {
        // Handle null cases
        if (is_null($answer1) || is_null($answer2)) {
            return $answer1 === $answer2;
        }

        // Convert to arrays if needed
        $answer1 = is_array($answer1) ? $answer1 : [$answer1];
        $answer2 = is_array($answer2) ? $answer2 : [$answer2];

        // Sort for comparison
        sort($answer1);
        sort($answer2);

        return $answer1 == $answer2;
    }

    /**
     * Log answer key changes.
     */
    protected function logAnswerKeyChange(): void
    {
        // This would typically write to an audit log
        // Implementation depends on your audit logging system
        
        activity()
            ->performedOn($this->answerKey)
            ->causedBy($this->reviewer)
            ->withProperties([
                'challenge_id' => $this->id,
                'question_id' => $this->question_id,
                'old_answer' => $this->getOriginalAnswer(),
                'new_answer' => $this->suggested_answer,
                'reason' => $this->challenge_reason
            ])
            ->log('Answer key updated due to accepted challenge');
    }

    /**
     * Get summary for display.
     */
    public function getSummary(): array
    {
        return [
            'question' => $this->question ? $this->question->question_code : 'N/A',
            'original_answer' => $this->getOriginalAnswer(),
            'suggested_answer' => $this->suggested_answer,
            'candidate_answer' => $this->getCandidateAnswer(),
            'status' => $this->getStatusLabel(),
            'potential_impact' => $this->calculatePotentialScoreChange(),
            'submitted_at' => $this->created_at->format('Y-m-d H:i:s'),
            'reviewed_at' => $this->reviewed_at?->format('Y-m-d H:i:s'),
            'reviewer' => $this->reviewer?->name
        ];
    }
}