<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAnswerKey extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'exam_answer_keys';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'exam_id',
        'paper_id',
        'key_type',
        'answers',
        'is_published',
        'published_at',
        'created_by',
        'approved_by'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'answers' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime'
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Set creator
        static::creating(function ($answerKey) {
            if (!$answerKey->created_by) {
                $answerKey->created_by = auth()->id();
            }
        });

        // Set published timestamp
        static::updating(function ($answerKey) {
            if ($answerKey->isDirty('is_published') && $answerKey->is_published && !$answerKey->published_at) {
                $answerKey->published_at = now();
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the exam.
     */
    public function exam()
    {
        return $this->belongsTo(EntranceExam::class, 'exam_id');
    }

    /**
     * Get the question paper.
     */
    public function questionPaper()
    {
        return $this->belongsTo(ExamQuestionPaper::class, 'paper_id');
    }

    /**
     * Get the creator.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the approver.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the challenges to this answer key.
     */
    public function challenges()
    {
        return $this->hasMany(AnswerKeyChallenge::class, 'answer_key_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for published answer keys.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for provisional answer keys.
     */
    public function scopeProvisional($query)
    {
        return $query->where('key_type', 'provisional');
    }

    /**
     * Scope for final answer keys.
     */
    public function scopeFinal($query)
    {
        return $query->where('key_type', 'final');
    }

    /**
     * Helper Methods
     */

    /**
     * Get answer for a specific question.
     */
    public function getAnswerForQuestion($questionId)
    {
        return $this->answers[$questionId] ?? null;
    }

    /**
     * Set answer for a specific question.
     */
    public function setAnswerForQuestion($questionId, $answer): bool
    {
        $answers = $this->answers ?? [];
        $answers[$questionId] = $answer;
        $this->answers = $answers;
        
        return $this->save();
    }

    /**
     * Remove answer for a specific question.
     */
    public function removeAnswerForQuestion($questionId): bool
    {
        $answers = $this->answers ?? [];
        unset($answers[$questionId]);
        $this->answers = $answers;
        
        return $this->save();
    }

    /**
     * Bulk update answers.
     */
    public function updateAnswers(array $answers): bool
    {
        $this->answers = array_merge($this->answers ?? [], $answers);
        return $this->save();
    }

    /**
     * Publish the answer key.
     */
    public function publish(): bool
    {
        $this->is_published = true;
        $this->published_at = now();
        
        return $this->save();
    }

    /**
     * Unpublish the answer key.
     */
    public function unpublish(): bool
    {
        $this->is_published = false;
        
        return $this->save();
    }

    /**
     * Approve the answer key.
     */
    public function approve($approverId = null): bool
    {
        $this->approved_by = $approverId ?? auth()->id();
        
        return $this->save();
    }

    /**
     * Convert provisional key to final.
     */
    public function markAsFinal(): bool
    {
        if ($this->key_type !== 'provisional') {
            return false;
        }

        $this->key_type = 'final';
        
        return $this->save();
    }

    /**
     * Check if answer key can be edited.
     */
    public function canBeEdited(): bool
    {
        // Cannot edit if final and published
        if ($this->key_type === 'final' && $this->is_published) {
            return false;
        }

        // Cannot edit if has accepted challenges
        if ($this->challenges()->where('status', 'accepted')->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Apply accepted challenges to update answer key.
     */
    public function applyAcceptedChallenges(): int
    {
        $acceptedChallenges = $this->challenges()
            ->where('status', 'accepted')
            ->get();

        $updatedCount = 0;
        $answers = $this->answers ?? [];

        foreach ($acceptedChallenges as $challenge) {
            $questionId = $challenge->question_id;
            
            // Update the answer based on the challenge
            if (isset($answers[$questionId])) {
                // The actual logic would depend on how challenges are resolved
                // This is a placeholder implementation
                $answers[$questionId] = $challenge->suggested_answer ?? $answers[$questionId];
                $updatedCount++;
            }
        }

        if ($updatedCount > 0) {
            $this->answers = $answers;
            $this->save();
        }

        return $updatedCount;
    }

    /**
     * Get statistics about the answer key.
     */
    public function getStatistics(): array
    {
        $totalQuestions = count($this->answers ?? []);
        $totalChallenges = $this->challenges()->count();
        $pendingChallenges = $this->challenges()->where('status', 'pending')->count();
        $acceptedChallenges = $this->challenges()->where('status', 'accepted')->count();
        $rejectedChallenges = $this->challenges()->where('status', 'rejected')->count();

        return [
            'total_questions' => $totalQuestions,
            'total_challenges' => $totalChallenges,
            'pending_challenges' => $pendingChallenges,
            'accepted_challenges' => $acceptedChallenges,
            'rejected_challenges' => $rejectedChallenges,
            'challenge_rate' => $totalQuestions > 0 
                ? round(($totalChallenges / $totalQuestions) * 100, 2) 
                : 0
        ];
    }

    /**
     * Generate comparison with another answer key.
     */
    public function compareWith(ExamAnswerKey $otherKey): array
    {
        $differences = [];
        $myAnswers = $this->answers ?? [];
        $otherAnswers = $otherKey->answers ?? [];

        $allQuestionIds = array_unique(array_merge(
            array_keys($myAnswers),
            array_keys($otherAnswers)
        ));

        foreach ($allQuestionIds as $questionId) {
            $myAnswer = $myAnswers[$questionId] ?? null;
            $otherAnswer = $otherAnswers[$questionId] ?? null;

            if ($myAnswer !== $otherAnswer) {
                $differences[$questionId] = [
                    'this_key' => $myAnswer,
                    'other_key' => $otherAnswer,
                    'match' => false
                ];
            }
        }

        $totalQuestions = count($allQuestionIds);
        $matchingAnswers = $totalQuestions - count($differences);

        return [
            'total_questions' => $totalQuestions,
            'matching_answers' => $matchingAnswers,
            'differing_answers' => count($differences),
            'match_percentage' => $totalQuestions > 0 
                ? round(($matchingAnswers / $totalQuestions) * 100, 2) 
                : 0,
            'differences' => $differences
        ];
    }

    /**
     * Validate answer key completeness.
     */
    public function validateCompleteness(): array
    {
        $issues = [];

        if (!$this->questionPaper) {
            $issues[] = 'No question paper associated';
            return $issues;
        }

        $paperQuestions = $this->questionPaper->questions_order ?? [];
        $answers = $this->answers ?? [];

        // Check for missing answers
        foreach ($paperQuestions as $questionId) {
            if (!isset($answers[$questionId])) {
                $issues[] = "Missing answer for question ID: {$questionId}";
            }
        }

        // Check for extra answers (questions not in paper)
        foreach (array_keys($answers) as $questionId) {
            if (!in_array($questionId, $paperQuestions)) {
                $issues[] = "Answer provided for question not in paper: {$questionId}";
            }
        }

        return $issues;
    }

    /**
     * Check if answer key is complete.
     */
    public function isComplete(): bool
    {
        return count($this->validateCompleteness()) === 0;
    }

    /**
     * Get formatted answer key for display.
     */
    public function getFormattedAnswers(): array
    {
        $formatted = [];
        $answers = $this->answers ?? [];

        foreach ($answers as $questionId => $answer) {
            $question = ExamQuestion::find($questionId);
            
            if ($question) {
                $formatted[] = [
                    'question_number' => array_search($questionId, $this->questionPaper->questions_order ?? []) + 1,
                    'question_code' => $question->question_code,
                    'question_type' => $question->question_type,
                    'correct_answer' => is_array($answer) ? implode(', ', $answer) : $answer,
                    'subject' => $question->subject,
                    'topic' => $question->topic
                ];
            }
        }

        return $formatted;
    }

    /**
     * Create a duplicate of this answer key.
     */
    public function duplicate(): ExamAnswerKey
    {
        $newKey = $this->replicate();
        $newKey->key_type = 'provisional';
        $newKey->is_published = false;
        $newKey->published_at = null;
        $newKey->approved_by = null;
        $newKey->created_by = auth()->id();
        $newKey->save();

        return $newKey;
    }
}