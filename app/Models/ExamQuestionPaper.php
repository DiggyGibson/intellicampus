<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExamQuestionPaper extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'exam_question_papers';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'exam_id',
        'session_id',
        'paper_code',
        'paper_set',
        'generation_method',
        'questions_order',
        'total_questions',
        'total_marks',
        'paper_hash',
        'is_locked',
        'locked_at',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'questions_order' => 'array',
        'total_questions' => 'integer',
        'total_marks' => 'integer',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'approved_at' => 'datetime'
    ];

    /**
     * Paper generation templates.
     */
    protected static $generationTemplates = [
        'balanced' => [
            'easy' => 30,      // 30% easy questions
            'medium' => 50,    // 50% medium questions
            'hard' => 20       // 20% hard questions
        ],
        'progressive' => [
            'easy' => 40,
            'medium' => 40,
            'hard' => 20
        ],
        'challenging' => [
            'easy' => 20,
            'medium' => 40,
            'hard' => 40
        ],
        'screening' => [
            'easy' => 10,
            'medium' => 30,
            'hard' => 60
        ]
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate paper code on creation
        static::creating(function ($paper) {
            if (!$paper->paper_code) {
                $paper->paper_code = self::generatePaperCode($paper);
            }
            
            // Set creator
            if (!$paper->created_by) {
                $paper->created_by = auth()->id();
            }
            
            // Calculate totals if questions are set
            if ($paper->questions_order) {
                $paper->calculateTotals();
            }
        });

        // Generate hash when locking
        static::updating(function ($paper) {
            if ($paper->isDirty('is_locked') && $paper->is_locked) {
                $paper->locked_at = now();
                $paper->paper_hash = $paper->generateHash();
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the exam for this paper.
     */
    public function exam()
    {
        return $this->belongsTo(EntranceExam::class, 'exam_id');
    }

    /**
     * Get the session for this paper.
     */
    public function session()
    {
        return $this->belongsTo(ExamSession::class, 'session_id');
    }

    /**
     * Get the creator of this paper.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the approver of this paper.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the questions in this paper.
     */
    public function questions()
    {
        if ($this->questions_order) {
            return ExamQuestion::whereIn('id', $this->questions_order)
                ->orderByRaw('FIELD(id, ' . implode(',', $this->questions_order) . ')')
                ->get();
        }
        return collect();
    }

    /**
     * Get exam responses using this paper.
     */
    public function examResponses()
    {
        return $this->hasMany(ExamResponse::class, 'paper_id');
    }

    /**
     * Get answer keys for this paper.
     */
    public function answerKeys()
    {
        return $this->hasMany(ExamAnswerKey::class, 'paper_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for locked papers.
     */
    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    /**
     * Scope for unlocked papers.
     */
    public function scopeUnlocked($query)
    {
        return $query->where('is_locked', false);
    }

    /**
     * Scope for approved papers.
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_by');
    }

    /**
     * Scope for pending approval.
     */
    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_by')->where('is_locked', true);
    }

    /**
     * Scope for papers by generation method.
     */
    public function scopeByGenerationMethod($query, $method)
    {
        return $query->where('generation_method', $method);
    }

    /**
     * Scope for papers by set.
     */
    public function scopeBySet($query, $set)
    {
        return $query->where('paper_set', $set);
    }

    /**
     * Helper Methods
     */

    /**
     * Generate unique paper code.
     */
    public static function generatePaperCode($paper): string
    {
        $exam = EntranceExam::find($paper->exam_id);
        $examCode = $exam ? substr($exam->exam_code, -3) : '000';
        
        $setCode = $paper->paper_set ?? 'A';
        $random = strtoupper(Str::random(4));
        
        return "QPP-{$examCode}-{$setCode}-{$random}";
    }

    /**
     * Generate paper manually by selecting questions.
     */
    public function generateManually(array $questionIds): bool
    {
        if ($this->is_locked) {
            return false;
        }
        
        $this->questions_order = $questionIds;
        $this->generation_method = 'manual';
        $this->calculateTotals();
        
        return $this->save();
    }

    /**
     * Generate paper randomly based on criteria.
     */
    public function generateRandomly(array $criteria = []): bool
    {
        if ($this->is_locked) {
            return false;
        }
        
        $questions = $this->selectRandomQuestions($criteria);
        
        if (empty($questions)) {
            return false;
        }
        
        $this->questions_order = $questions->pluck('id')->toArray();
        $this->generation_method = 'random';
        $this->calculateTotals();
        
        return $this->save();
    }

    /**
     * Generate paper from template.
     */
    public function generateFromTemplate($templateName = 'balanced'): bool
    {
        if ($this->is_locked) {
            return false;
        }
        
        $template = self::$generationTemplates[$templateName] ?? self::$generationTemplates['balanced'];
        $exam = $this->exam;
        
        if (!$exam) {
            return false;
        }
        
        $totalQuestions = $exam->total_questions;
        $questions = collect();
        
        // Select questions based on difficulty distribution
        foreach ($template as $difficulty => $percentage) {
            $count = (int) ceil($totalQuestions * $percentage / 100);
            
            $difficultyQuestions = ExamQuestion::where('exam_id', $this->exam_id)
                ->where('difficulty_level', $difficulty)
                ->where('is_active', true)
                ->inRandomOrder()
                ->limit($count)
                ->get();
            
            $questions = $questions->concat($difficultyQuestions);
        }
        
        // Shuffle questions
        $questions = $questions->shuffle();
        
        $this->questions_order = $questions->pluck('id')->toArray();
        $this->generation_method = 'template';
        $this->calculateTotals();
        
        return $this->save();
    }

    /**
     * Generate paper with adaptive difficulty.
     */
    public function generateAdaptive($startDifficulty = 'medium'): bool
    {
        if ($this->is_locked) {
            return false;
        }
        
        // This would implement adaptive testing logic
        // Starting with medium difficulty and adjusting based on performance
        // For now, we'll use a simple implementation
        
        $questions = ExamQuestion::where('exam_id', $this->exam_id)
            ->where('is_active', true)
            ->orderBy('difficulty_level')
            ->limit($this->exam->total_questions ?? 50)
            ->get()
            ->shuffle();
        
        $this->questions_order = $questions->pluck('id')->toArray();
        $this->generation_method = 'adaptive';
        $this->calculateTotals();
        
        return $this->save();
    }

    /**
     * Select random questions based on criteria.
     */
    protected function selectRandomQuestions(array $criteria): \Illuminate\Support\Collection
    {
        $query = ExamQuestion::where('exam_id', $this->exam_id)
            ->where('is_active', true);
        
        // Apply subject filter
        if (!empty($criteria['subjects'])) {
            $query->whereIn('subject', $criteria['subjects']);
        }
        
        // Apply topic filter
        if (!empty($criteria['topics'])) {
            $query->whereIn('topic', $criteria['topics']);
        }
        
        // Apply difficulty filter
        if (!empty($criteria['difficulty_levels'])) {
            $query->whereIn('difficulty_level', $criteria['difficulty_levels']);
        }
        
        // Apply question type filter
        if (!empty($criteria['question_types'])) {
            $query->whereIn('question_type', $criteria['question_types']);
        }
        
        $totalQuestions = $criteria['total_questions'] ?? $this->exam->total_questions ?? 50;
        
        return $query->inRandomOrder()->limit($totalQuestions)->get();
    }

    /**
     * Calculate totals from questions.
     */
    protected function calculateTotals(): void
    {
        if (!$this->questions_order) {
            return;
        }
        
        $questions = ExamQuestion::whereIn('id', $this->questions_order)->get();
        
        $this->total_questions = $questions->count();
        $this->total_marks = $questions->sum('marks');
    }

    /**
     * Lock the paper for use.
     */
    public function lock(): bool
    {
        if ($this->is_locked) {
            return false;
        }
        
        if (empty($this->questions_order)) {
            return false;
        }
        
        $this->is_locked = true;
        $this->locked_at = now();
        $this->paper_hash = $this->generateHash();
        
        return $this->save();
    }

    /**
     * Unlock the paper for editing.
     */
    public function unlock(): bool
    {
        if (!$this->is_locked) {
            return false;
        }
        
        // Check if paper is already in use
        if ($this->isInUse()) {
            return false;
        }
        
        $this->is_locked = false;
        $this->locked_at = null;
        $this->approved_by = null;
        $this->approved_at = null;
        
        return $this->save();
    }

    /**
     * Approve the paper.
     */
    public function approve($approverId = null): bool
    {
        if (!$this->is_locked) {
            return false;
        }
        
        $this->approved_by = $approverId ?? auth()->id();
        $this->approved_at = now();
        
        return $this->save();
    }

    /**
     * Check if paper is in use.
     */
    public function isInUse(): bool
    {
        return $this->examResponses()->exists();
    }

    /**
     * Generate hash for paper integrity.
     */
    public function generateHash(): string
    {
        $data = [
            'exam_id' => $this->exam_id,
            'questions' => $this->questions_order,
            'total_marks' => $this->total_marks,
            'paper_set' => $this->paper_set
        ];
        
        return hash('sha256', json_encode($data));
    }

    /**
     * Verify paper integrity.
     */
    public function verifyIntegrity(): bool
    {
        if (!$this->paper_hash) {
            return true;
        }
        
        return $this->paper_hash === $this->generateHash();
    }

    /**
     * Shuffle questions order.
     */
    public function shuffleQuestions(): bool
    {
        if ($this->is_locked) {
            return false;
        }
        
        if (!$this->questions_order) {
            return false;
        }
        
        $this->questions_order = collect($this->questions_order)->shuffle()->toArray();
        
        return $this->save();
    }

    /**
     * Create a duplicate paper with different set.
     */
    public function duplicateAsSet($newSet): self
    {
        $duplicate = $this->replicate();
        $duplicate->paper_set = $newSet;
        $duplicate->paper_code = null; // Will be regenerated
        $duplicate->is_locked = false;
        $duplicate->locked_at = null;
        $duplicate->paper_hash = null;
        $duplicate->approved_by = null;
        $duplicate->approved_at = null;
        
        // Shuffle questions for different set
        if ($duplicate->questions_order) {
            $duplicate->questions_order = collect($duplicate->questions_order)->shuffle()->toArray();
        }
        
        $duplicate->save();
        
        return $duplicate;
    }

    /**
     * Add question to paper.
     */
    public function addQuestion($questionId, $position = null): bool
    {
        if ($this->is_locked) {
            return false;
        }
        
        $questions = $this->questions_order ?? [];
        
        if (in_array($questionId, $questions)) {
            return false; // Question already exists
        }
        
        if ($position !== null && $position >= 0 && $position <= count($questions)) {
            array_splice($questions, $position, 0, $questionId);
        } else {
            $questions[] = $questionId;
        }
        
        $this->questions_order = $questions;
        $this->calculateTotals();
        
        return $this->save();
    }

    /**
     * Remove question from paper.
     */
    public function removeQuestion($questionId): bool
    {
        if ($this->is_locked) {
            return false;
        }
        
        $questions = $this->questions_order ?? [];
        
        if (($key = array_search($questionId, $questions)) !== false) {
            unset($questions[$key]);
            $this->questions_order = array_values($questions);
            $this->calculateTotals();
            
            return $this->save();
        }
        
        return false;
    }

    /**
     * Reorder questions.
     */
    public function reorderQuestions(array $newOrder): bool
    {
        if ($this->is_locked) {
            return false;
        }
        
        // Validate that all questions are present
        if (count($newOrder) !== count($this->questions_order)) {
            return false;
        }
        
        if (array_diff($newOrder, $this->questions_order) || array_diff($this->questions_order, $newOrder)) {
            return false;
        }
        
        $this->questions_order = $newOrder;
        
        return $this->save();
    }

    /**
     * Get question at specific position.
     */
    public function getQuestionAt($position): ?ExamQuestion
    {
        if (!$this->questions_order || !isset($this->questions_order[$position - 1])) {
            return null;
        }
        
        return ExamQuestion::find($this->questions_order[$position - 1]);
    }

    /**
     * Get paper statistics.
     */
    public function getStatistics(): array
    {
        $questions = $this->questions();
        
        return [
            'total_questions' => $this->total_questions,
            'total_marks' => $this->total_marks,
            'by_difficulty' => [
                'easy' => $questions->where('difficulty_level', 'easy')->count(),
                'medium' => $questions->where('difficulty_level', 'medium')->count(),
                'hard' => $questions->where('difficulty_level', 'hard')->count(),
                'expert' => $questions->where('difficulty_level', 'expert')->count()
            ],
            'by_type' => $questions->groupBy('question_type')->map->count(),
            'by_subject' => $questions->groupBy('subject')->map->count(),
            'average_marks_per_question' => $this->total_questions > 0 
                ? round($this->total_marks / $this->total_questions, 2) 
                : 0,
            'auto_gradable_questions' => $questions->filter->isAutoGradable()->count(),
            'manual_grading_required' => $questions->filter->requiresManualGrading()->count()
        ];
    }

    /**
     * Generate answer key.
     */
    public function generateAnswerKey(): array
    {
        $answerKey = [];
        $questions = $this->questions();
        
        foreach ($questions as $index => $question) {
            $answerKey[$index + 1] = [
                'question_id' => $question->id,
                'question_code' => $question->question_code,
                'correct_answer' => $question->correct_answer,
                'marks' => $question->marks,
                'negative_marks' => $question->negative_marks
            ];
        }
        
        return $answerKey;
    }

    /**
     * Export paper as PDF.
     */
    public function exportAsPdf(): string
    {
        // This would generate a PDF version of the question paper
        // Implementation would use a PDF library like DomPDF or TCPDF
        
        $questions = $this->questions();
        $html = view('exams.paper-pdf', [
            'paper' => $this,
            'questions' => $questions,
            'exam' => $this->exam
        ])->render();
        
        // Generate PDF and return path
        return storage_path('app/papers/' . $this->paper_code . '.pdf');
    }

    /**
     * Get generation method label.
     */
    public function getGenerationMethodLabel(): string
    {
        return match($this->generation_method) {
            'manual' => 'Manually Selected',
            'random' => 'Randomly Generated',
            'template' => 'Template-based',
            'adaptive' => 'Adaptive Generation',
            default => ucwords(str_replace('_', ' ', $this->generation_method))
        };
    }

    /**
     * Generate paper summary.
     */
    public function generateSummary(): array
    {
        return [
            'paper_code' => $this->paper_code,
            'paper_set' => $this->paper_set,
            'exam' => [
                'code' => $this->exam->exam_code ?? null,
                'name' => $this->exam->exam_name ?? null
            ],
            'session' => [
                'code' => $this->session->session_code ?? null,
                'date' => $this->session->session_date->format('Y-m-d') ?? null
            ],
            'generation' => [
                'method' => $this->generation_method,
                'created_by' => $this->creator->name ?? null,
                'created_at' => $this->created_at->format('Y-m-d H:i:s')
            ],
            'approval' => [
                'is_locked' => $this->is_locked,
                'locked_at' => $this->locked_at?->format('Y-m-d H:i:s'),
                'approved_by' => $this->approver->name ?? null,
                'approved_at' => $this->approved_at?->format('Y-m-d H:i:s')
            ],
            'statistics' => $this->getStatistics(),
            'integrity' => [
                'hash' => $this->paper_hash,
                'verified' => $this->verifyIntegrity()
            ],
            'in_use' => $this->isInUse()
        ];
    }
}