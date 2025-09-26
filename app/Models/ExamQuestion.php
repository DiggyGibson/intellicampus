<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExamQuestion extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'exam_questions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'question_code',
        'exam_id',
        'question_text',
        'question_type',
        'subject',
        'topic',
        'subtopic',
        'difficulty_level',
        'marks',
        'negative_marks',
        'time_limit_seconds',
        'options',
        'correct_answer',
        'answer_explanation',
        'question_image',
        'question_audio',
        'question_video',
        'times_used',
        'average_score',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
        'marks' => 'integer',
        'negative_marks' => 'integer',
        'time_limit_seconds' => 'integer',
        'times_used' => 'integer',
        'average_score' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Question type configurations.
     */
    protected static $questionTypeConfigs = [
        'multiple_choice' => [
            'has_options' => true,
            'single_answer' => true,
            'auto_gradable' => true
        ],
        'multiple_answer' => [
            'has_options' => true,
            'single_answer' => false,
            'auto_gradable' => true
        ],
        'true_false' => [
            'has_options' => true,
            'single_answer' => true,
            'auto_gradable' => true,
            'fixed_options' => ['True', 'False']
        ],
        'fill_blanks' => [
            'has_options' => false,
            'single_answer' => false,
            'auto_gradable' => true
        ],
        'short_answer' => [
            'has_options' => false,
            'single_answer' => true,
            'auto_gradable' => false
        ],
        'essay' => [
            'has_options' => false,
            'single_answer' => true,
            'auto_gradable' => false
        ],
        'numerical' => [
            'has_options' => false,
            'single_answer' => true,
            'auto_gradable' => true
        ],
        'matching' => [
            'has_options' => true,
            'single_answer' => false,
            'auto_gradable' => true
        ],
        'ordering' => [
            'has_options' => true,
            'single_answer' => false,
            'auto_gradable' => true
        ]
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate question code on creation
        static::creating(function ($question) {
            if (!$question->question_code) {
                $question->question_code = self::generateQuestionCode($question);
            }
            
            // Set default values
            if (is_null($question->times_used)) {
                $question->times_used = 0;
            }
            
            if (is_null($question->is_active)) {
                $question->is_active = true;
            }
            
            // Set negative marks default
            if (is_null($question->negative_marks)) {
                $question->negative_marks = 0;
            }
        });

        // Validate question structure
        static::saving(function ($question) {
            $question->validateQuestionStructure();
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the exam this question belongs to.
     */
    public function exam()
    {
        return $this->belongsTo(EntranceExam::class, 'exam_id');
    }

    /**
     * Get response details for this question.
     */
    public function responseDetails()
    {
        return $this->hasMany(ExamResponseDetail::class, 'question_id');
    }

    /**
     * Get answer key challenges for this question.
     */
    public function challenges()
    {
        return $this->hasMany(AnswerKeyChallenge::class, 'question_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for active questions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for questions by difficulty.
     */
    public function scopeByDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    /**
     * Scope for questions by subject.
     */
    public function scopeBySubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }

    /**
     * Scope for questions by topic.
     */
    public function scopeByTopic($query, $topic)
    {
        return $query->where('topic', $topic);
    }

    /**
     * Scope for questions by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('question_type', $type);
    }

    /**
     * Scope for auto-gradable questions.
     */
    public function scopeAutoGradable($query)
    {
        $autoGradableTypes = array_keys(array_filter(
            self::$questionTypeConfigs,
            fn($config) => $config['auto_gradable'] ?? false
        ));
        
        return $query->whereIn('question_type', $autoGradableTypes);
    }

    /**
     * Scope for manual grading required.
     */
    public function scopeManualGrading($query)
    {
        $manualTypes = array_keys(array_filter(
            self::$questionTypeConfigs,
            fn($config) => !($config['auto_gradable'] ?? true)
        ));
        
        return $query->whereIn('question_type', $manualTypes);
    }

    /**
     * Helper Methods
     */

    /**
     * Generate unique question code.
     */
    public static function generateQuestionCode($question): string
    {
        $typePrefix = match($question->question_type) {
            'multiple_choice' => 'MCQ',
            'multiple_answer' => 'MAQ',
            'true_false' => 'TFQ',
            'fill_blanks' => 'FBQ',
            'short_answer' => 'SAQ',
            'essay' => 'ESQ',
            'numerical' => 'NMQ',
            'matching' => 'MTQ',
            'ordering' => 'ORQ',
            default => 'QST'
        };
        
        $subjectCode = $question->subject ? strtoupper(substr($question->subject, 0, 3)) : 'GEN';
        
        $random = strtoupper(Str::random(4));
        
        return "{$typePrefix}-{$subjectCode}-{$random}";
    }

    /**
     * Validate question structure based on type.
     */
    protected function validateQuestionStructure(): void
    {
        $config = self::$questionTypeConfigs[$this->question_type] ?? null;
        
        if (!$config) {
            return;
        }
        
        // Validate options requirement
        if ($config['has_options'] && empty($this->options)) {
            throw new \Exception("Question type {$this->question_type} requires options");
        }
        
        // Validate correct answer format
        if ($config['single_answer'] && is_array($this->correct_answer) && count($this->correct_answer) > 1) {
            throw new \Exception("Question type {$this->question_type} requires single answer");
        }
        
        // Set fixed options for true/false
        if ($this->question_type === 'true_false' && isset($config['fixed_options'])) {
            $this->options = array_combine(['a', 'b'], $config['fixed_options']);
        }
    }

    /**
     * Check if question is auto-gradable.
     */
    public function isAutoGradable(): bool
    {
        $config = self::$questionTypeConfigs[$this->question_type] ?? null;
        return $config['auto_gradable'] ?? false;
    }

    /**
     * Check if question requires manual grading.
     */
    public function requiresManualGrading(): bool
    {
        return !$this->isAutoGradable();
    }

    /**
     * Check if question has options.
     */
    public function hasOptions(): bool
    {
        $config = self::$questionTypeConfigs[$this->question_type] ?? null;
        return $config['has_options'] ?? false;
    }

    /**
     * Check if question allows single answer.
     */
    public function isSingleAnswer(): bool
    {
        $config = self::$questionTypeConfigs[$this->question_type] ?? null;
        return $config['single_answer'] ?? true;
    }

    /**
     * Grade a response automatically.
     */
    public function gradeResponse($response): array
    {
        if (!$this->isAutoGradable()) {
            return [
                'is_correct' => null,
                'marks_obtained' => null,
                'requires_manual_grading' => true
            ];
        }
        
        $isCorrect = false;
        $marksObtained = 0;
        
        switch ($this->question_type) {
            case 'multiple_choice':
            case 'true_false':
                $isCorrect = $this->gradeMultipleChoice($response);
                break;
                
            case 'multiple_answer':
                $isCorrect = $this->gradeMultipleAnswer($response);
                break;
                
            case 'fill_blanks':
                $isCorrect = $this->gradeFillBlanks($response);
                break;
                
            case 'numerical':
                $isCorrect = $this->gradeNumerical($response);
                break;
                
            case 'matching':
                $result = $this->gradeMatching($response);
                $isCorrect = $result['all_correct'];
                $marksObtained = $result['marks'];
                break;
                
            case 'ordering':
                $isCorrect = $this->gradeOrdering($response);
                break;
        }
        
        // Calculate marks
        if ($marksObtained == 0) {
            $marksObtained = $isCorrect ? $this->marks : -$this->negative_marks;
        }
        
        return [
            'is_correct' => $isCorrect,
            'marks_obtained' => $marksObtained,
            'requires_manual_grading' => false
        ];
    }

    /**
     * Grade multiple choice question.
     */
    protected function gradeMultipleChoice($response): bool
    {
        $correctAnswer = is_array($this->correct_answer) 
            ? $this->correct_answer[0] 
            : $this->correct_answer;
            
        return $response === $correctAnswer;
    }

    /**
     * Grade multiple answer question.
     */
    protected function gradeMultipleAnswer($response): bool
    {
        if (!is_array($response)) {
            return false;
        }
        
        $correctAnswers = (array) $this->correct_answer;
        sort($correctAnswers);
        sort($response);
        
        return $correctAnswers === $response;
    }

    /**
     * Grade fill in the blanks question.
     */
    protected function gradeFillBlanks($response): bool
    {
        $correctAnswers = (array) $this->correct_answer;
        
        if (!is_array($response)) {
            $response = [$response];
        }
        
        // Check if all blanks are correctly filled
        foreach ($correctAnswers as $index => $correct) {
            $userAnswer = $response[$index] ?? '';
            
            // Case-insensitive comparison with trimming
            if (strtolower(trim($userAnswer)) !== strtolower(trim($correct))) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Grade numerical question.
     */
    protected function gradeNumerical($response, $tolerance = 0.01): bool
    {
        $correctAnswer = is_array($this->correct_answer) 
            ? floatval($this->correct_answer[0])
            : floatval($this->correct_answer);
            
        $userAnswer = floatval($response);
        
        return abs($correctAnswer - $userAnswer) <= $tolerance;
    }

    /**
     * Grade matching question.
     */
    protected function gradeMatching($response): array
    {
        if (!is_array($response)) {
            return ['all_correct' => false, 'marks' => 0];
        }
        
        $correctPairs = (array) $this->correct_answer;
        $correctCount = 0;
        $totalPairs = count($correctPairs);
        
        foreach ($correctPairs as $left => $right) {
            if (isset($response[$left]) && $response[$left] === $right) {
                $correctCount++;
            }
        }
        
        $marksPerPair = $this->marks / $totalPairs;
        $marksObtained = $correctCount * $marksPerPair;
        
        return [
            'all_correct' => $correctCount === $totalPairs,
            'marks' => round($marksObtained, 2),
            'correct_count' => $correctCount,
            'total_pairs' => $totalPairs
        ];
    }

    /**
     * Grade ordering question.
     */
    protected function gradeOrdering($response): bool
    {
        if (!is_array($response)) {
            return false;
        }
        
        $correctOrder = (array) $this->correct_answer;
        
        return $correctOrder === $response;
    }

    /**
     * Update usage statistics.
     */
    public function updateUsageStats($score = null): void
    {
        $this->times_used++;
        
        if ($score !== null) {
            // Update average score
            if ($this->average_score === null) {
                $this->average_score = $score;
            } else {
                // Calculate new average
                $totalScore = $this->average_score * ($this->times_used - 1) + $score;
                $this->average_score = round($totalScore / $this->times_used, 2);
            }
        }
        
        $this->saveQuietly();
    }

    /**
     * Clone question for another exam.
     */
    public function cloneForExam($examId): self
    {
        $clone = $this->replicate();
        $clone->exam_id = $examId;
        $clone->question_code = null; // Will be regenerated
        $clone->times_used = 0;
        $clone->average_score = null;
        $clone->save();
        
        return $clone;
    }

    /**
     * Get difficulty color for UI.
     */
    public function getDifficultyColor(): string
    {
        return match($this->difficulty_level) {
            'easy' => 'green',
            'medium' => 'yellow',
            'hard' => 'orange',
            'expert' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get question type label.
     */
    public function getTypeLabel(): string
    {
        return match($this->question_type) {
            'multiple_choice' => 'Multiple Choice',
            'multiple_answer' => 'Multiple Answer',
            'true_false' => 'True/False',
            'fill_blanks' => 'Fill in the Blanks',
            'short_answer' => 'Short Answer',
            'essay' => 'Essay',
            'numerical' => 'Numerical',
            'matching' => 'Matching',
            'ordering' => 'Ordering',
            default => ucwords(str_replace('_', ' ', $this->question_type))
        };
    }

    /**
     * Format options for display.
     */
    public function getFormattedOptions(): array
    {
        if (!$this->options || !is_array($this->options)) {
            return [];
        }
        
        $formatted = [];
        foreach ($this->options as $key => $value) {
            $formatted[] = [
                'key' => $key,
                'value' => $value,
                'label' => strtoupper($key) . '. ' . $value
            ];
        }
        
        return $formatted;
    }

    /**
     * Get question preview HTML.
     */
    public function getPreviewHtml(): string
    {
        $html = '<div class="question-preview">';
        $html .= '<p class="question-text">' . htmlspecialchars($this->question_text) . '</p>';
        
        if ($this->question_image) {
            $html .= '<img src="' . $this->question_image . '" class="question-image" />';
        }
        
        if ($this->hasOptions()) {
            $html .= '<ul class="options">';
            foreach ($this->getFormattedOptions() as $option) {
                $html .= '<li>' . htmlspecialchars($option['label']) . '</li>';
            }
            $html .= '</ul>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Generate question summary.
     */
    public function generateSummary(): array
    {
        return [
            'code' => $this->question_code,
            'type' => $this->question_type,
            'subject' => $this->subject,
            'topic' => $this->topic,
            'difficulty' => $this->difficulty_level,
            'marks' => $this->marks,
            'negative_marks' => $this->negative_marks,
            'time_limit' => $this->time_limit_seconds,
            'auto_gradable' => $this->isAutoGradable(),
            'has_media' => !empty($this->question_image) || !empty($this->question_audio) || !empty($this->question_video),
            'usage' => [
                'times_used' => $this->times_used,
                'average_score' => $this->average_score
            ],
            'status' => $this->is_active ? 'Active' : 'Inactive'
        ];
    }
}