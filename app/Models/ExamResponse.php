<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ExamResponse extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'exam_responses';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'registration_id',
        'session_id',
        'paper_id',
        'exam_started_at',
        'exam_submitted_at',
        'last_activity_at',
        'status',
        'time_spent_seconds',
        'remaining_time_seconds',
        'ip_address',
        'user_agent',
        'browser_fingerprint',
        'tab_switches',
        'copy_attempts',
        'paste_attempts',
        'violations'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'exam_started_at' => 'datetime',
        'exam_submitted_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'time_spent_seconds' => 'integer',
        'remaining_time_seconds' => 'integer',
        'tab_switches' => 'integer',
        'copy_attempts' => 'integer',
        'paste_attempts' => 'integer',
        'violations' => 'array'
    ];

    /**
     * Violation severity levels.
     */
    protected static $violationSeverity = [
        'tab_switch' => 'low',
        'window_blur' => 'low',
        'right_click' => 'low',
        'copy_attempt' => 'medium',
        'paste_attempt' => 'medium',
        'print_attempt' => 'medium',
        'screenshot_attempt' => 'high',
        'fullscreen_exit' => 'high',
        'face_not_detected' => 'high',
        'multiple_faces' => 'critical',
        'face_mismatch' => 'critical',
        'network_disconnect' => 'critical',
        'unusual_activity' => 'critical'
    ];

    /**
     * Maximum allowed violations before auto-termination.
     */
    protected static $maxViolations = [
        'low' => 10,
        'medium' => 5,
        'high' => 3,
        'critical' => 1
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Set initial values on creation
        static::creating(function ($response) {
            if (!$response->status) {
                $response->status = 'not_started';
            }
            
            if (!$response->time_spent_seconds) {
                $response->time_spent_seconds = 0;
            }
            
            if (!$response->tab_switches) {
                $response->tab_switches = 0;
            }
            
            if (!$response->copy_attempts) {
                $response->copy_attempts = 0;
            }
            
            if (!$response->paste_attempts) {
                $response->paste_attempts = 0;
            }
            
            // Capture browser info
            if (!$response->ip_address) {
                $response->ip_address = request()->ip();
            }
            
            if (!$response->user_agent) {
                $response->user_agent = request()->userAgent();
            }
        });

        // Update time tracking
        static::updating(function ($response) {
            // Update last activity
            $response->last_activity_at = now();
            
            // Calculate time spent when submitting
            if ($response->isDirty('status') && 
                in_array($response->status, ['submitted', 'auto_submitted'])) {
                if ($response->exam_started_at && !$response->exam_submitted_at) {
                    $response->exam_submitted_at = now();
                    $response->time_spent_seconds = $response->exam_started_at->diffInSeconds($response->exam_submitted_at);
                }
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the registration for this response.
     */
    public function registration()
    {
        return $this->belongsTo(EntranceExamRegistration::class, 'registration_id');
    }

    /**
     * Get the session for this response.
     */
    public function session()
    {
        return $this->belongsTo(ExamSession::class, 'session_id');
    }

    /**
     * Get the question paper for this response.
     */
    public function paper()
    {
        return $this->belongsTo(ExamQuestionPaper::class, 'paper_id');
    }

    /**
     * Get response details for each question.
     */
    public function responseDetails()
    {
        return $this->hasMany(ExamResponseDetail::class, 'response_id');
    }

    /**
     * Get the exam result for this response.
     */
    public function result()
    {
        return $this->hasOne(EntranceExamResult::class, 'response_id');
    }

    /**
     * Get proctoring logs for this response.
     */
    public function proctoringLogs()
    {
        return $this->hasMany(ExamProctoringLog::class, 'response_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for in-progress responses.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for submitted responses.
     */
    public function scopeSubmitted($query)
    {
        return $query->whereIn('status', ['submitted', 'auto_submitted']);
    }

    /**
     * Scope for terminated responses.
     */
    public function scopeTerminated($query)
    {
        return $query->where('status', 'terminated');
    }

    /**
     * Scope for responses with violations.
     */
    public function scopeWithViolations($query)
    {
        return $query->where(function ($q) {
            $q->where('tab_switches', '>', 0)
              ->orWhere('copy_attempts', '>', 0)
              ->orWhere('paste_attempts', '>', 0)
              ->orWhereNotNull('violations');
        });
    }

    /**
     * Scope for suspicious responses.
     */
    public function scopeSuspicious($query, $threshold = 5)
    {
        return $query->where(function ($q) use ($threshold) {
            $q->where('tab_switches', '>', $threshold)
              ->orWhere('copy_attempts', '>', $threshold / 2)
              ->orWhere('paste_attempts', '>', $threshold / 2);
        });
    }

    /**
     * Helper Methods
     */

    /**
     * Start the exam.
     */
    public function startExam(): bool
    {
        if ($this->status !== 'not_started') {
            return false;
        }
        
        // Check if session is active
        if (!$this->session || !$this->session->isOngoing()) {
            return false;
        }
        
        $this->status = 'in_progress';
        $this->exam_started_at = now();
        $this->last_activity_at = now();
        
        // Calculate remaining time based on exam duration
        if ($this->paper && $this->paper->exam) {
            $this->remaining_time_seconds = $this->paper->exam->duration_minutes * 60;
        }
        
        return $this->save();
    }

    /**
     * Submit the exam.
     */
    public function submitExam(): bool
    {
        if (!in_array($this->status, ['in_progress', 'not_started'])) {
            return false;
        }
        
        $this->status = 'submitted';
        $this->exam_submitted_at = now();
        
        if ($this->exam_started_at) {
            $this->time_spent_seconds = $this->exam_started_at->diffInSeconds($this->exam_submitted_at);
        }
        
        $saved = $this->save();
        
        // Trigger result calculation
        if ($saved) {
            $this->calculateResult();
        }
        
        return $saved;
    }

    /**
     * Auto-submit the exam (when time runs out).
     */
    public function autoSubmit(): bool
    {
        if ($this->status !== 'in_progress') {
            return false;
        }
        
        $this->status = 'auto_submitted';
        $this->exam_submitted_at = now();
        
        if ($this->exam_started_at) {
            $this->time_spent_seconds = $this->exam_started_at->diffInSeconds($this->exam_submitted_at);
        }
        
        $saved = $this->save();
        
        // Trigger result calculation
        if ($saved) {
            $this->calculateResult();
        }
        
        return $saved;
    }

    /**
     * Terminate the exam (due to violations).
     */
    public function terminate($reason = null): bool
    {
        if (!in_array($this->status, ['in_progress', 'not_started'])) {
            return false;
        }
        
        $this->status = 'terminated';
        $this->exam_submitted_at = now();
        
        if ($this->exam_started_at) {
            $this->time_spent_seconds = $this->exam_started_at->diffInSeconds($this->exam_submitted_at);
        }
        
        // Add termination reason to violations
        $violations = $this->violations ?? [];
        $violations[] = [
            'type' => 'terminated',
            'reason' => $reason ?? 'Exam terminated due to violations',
            'timestamp' => now()->toIso8601String()
        ];
        $this->violations = $violations;
        
        return $this->save();
    }

    /**
     * Update remaining time.
     */
    public function updateRemainingTime(): bool
    {
        if ($this->status !== 'in_progress') {
            return false;
        }
        
        if (!$this->exam_started_at) {
            return false;
        }
        
        $timeSpent = $this->exam_started_at->diffInSeconds(now());
        $this->time_spent_seconds = $timeSpent;
        
        if ($this->paper && $this->paper->exam) {
            $totalTime = $this->paper->exam->duration_minutes * 60;
            $this->remaining_time_seconds = max(0, $totalTime - $timeSpent);
            
            // Auto-submit if time is up
            if ($this->remaining_time_seconds === 0) {
                return $this->autoSubmit();
            }
        }
        
        $this->last_activity_at = now();
        
        return $this->save();
    }

    /**
     * Record a violation.
     */
    public function recordViolation($type, $details = null): bool
    {
        $violations = $this->violations ?? [];
        
        $violation = [
            'type' => $type,
            'timestamp' => now()->toIso8601String(),
            'severity' => self::$violationSeverity[$type] ?? 'low'
        ];
        
        if ($details) {
            $violation['details'] = $details;
        }
        
        $violations[] = $violation;
        $this->violations = $violations;
        
        // Update specific counters
        switch ($type) {
            case 'tab_switch':
            case 'window_blur':
                $this->tab_switches++;
                break;
            case 'copy_attempt':
                $this->copy_attempts++;
                break;
            case 'paste_attempt':
                $this->paste_attempts++;
                break;
        }
        
        $saved = $this->save();
        
        // Check if should terminate due to violations
        if ($saved && $this->shouldTerminate()) {
            $this->terminate('Maximum violations exceeded');
        }
        
        // Log to proctoring system
        if ($saved) {
            $this->logToProctoringSystem($violation);
        }
        
        return $saved;
    }

    /**
     * Check if exam should be terminated due to violations.
     */
    protected function shouldTerminate(): bool
    {
        if ($this->status !== 'in_progress') {
            return false;
        }
        
        $violationCounts = [
            'low' => 0,
            'medium' => 0,
            'high' => 0,
            'critical' => 0
        ];
        
        foreach ($this->violations ?? [] as $violation) {
            $severity = $violation['severity'] ?? 'low';
            $violationCounts[$severity]++;
        }
        
        foreach ($violationCounts as $severity => $count) {
            if ($count >= (self::$maxViolations[$severity] ?? PHP_INT_MAX)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Log violation to proctoring system.
     */
    protected function logToProctoringSystem($violation): void
    {
        ExamProctoringLog::create([
            'response_id' => $this->id,
            'registration_id' => $this->registration_id,
            'event_type' => $violation['type'],
            'severity' => $violation['severity'],
            'description' => $violation['details'] ?? null,
            'occurred_at' => $violation['timestamp']
        ]);
    }

    /**
     * Save answer for a question.
     */
    public function saveAnswer($questionId, $answer, $status = 'answered'): bool
    {
        if ($this->status !== 'in_progress') {
            return false;
        }
        
        $detail = $this->responseDetails()
            ->where('question_id', $questionId)
            ->first();
        
        if (!$detail) {
            // Get question number from paper
            $questionNumber = $this->getQuestionNumber($questionId);
            
            $detail = $this->responseDetails()->create([
                'question_id' => $questionId,
                'question_number' => $questionNumber,
                'status' => 'not_visited'
            ]);
        }
        
        $detail->answer = $answer;
        $detail->status = $status;
        $detail->last_updated_at = now();
        
        if (!$detail->first_viewed_at) {
            $detail->first_viewed_at = now();
        }
        
        $detail->visit_count++;
        
        return $detail->save();
    }

    /**
     * Get question number from paper.
     */
    protected function getQuestionNumber($questionId): int
    {
        if (!$this->paper || !$this->paper->questions_order) {
            return 1;
        }
        
        $position = array_search($questionId, $this->paper->questions_order);
        
        return $position !== false ? $position + 1 : 1;
    }

    /**
     * Mark question for review.
     */
    public function markForReview($questionId): bool
    {
        if ($this->status !== 'in_progress') {
            return false;
        }
        
        $detail = $this->responseDetails()
            ->where('question_id', $questionId)
            ->first();
        
        if (!$detail) {
            return false;
        }
        
        $currentStatus = $detail->status;
        
        if ($currentStatus === 'answered') {
            $detail->status = 'answered_marked_review';
        } else {
            $detail->status = 'marked_review';
        }
        
        return $detail->save();
    }

    /**
     * Clear response for a question.
     */
    public function clearAnswer($questionId): bool
    {
        if ($this->status !== 'in_progress') {
            return false;
        }
        
        $detail = $this->responseDetails()
            ->where('question_id', $questionId)
            ->first();
        
        if (!$detail) {
            return false;
        }
        
        $detail->answer = null;
        $detail->status = 'not_answered';
        
        return $detail->save();
    }

    /**
     * Get response summary.
     */
    public function getResponseSummary(): array
    {
        $details = $this->responseDetails;
        
        return [
            'total_questions' => $this->paper ? $this->paper->total_questions : 0,
            'attempted' => $details->whereIn('status', ['answered', 'answered_marked_review'])->count(),
            'not_attempted' => $details->whereIn('status', ['not_visited', 'not_answered'])->count(),
            'marked_for_review' => $details->whereIn('status', ['marked_review', 'answered_marked_review'])->count(),
            'not_visited' => $details->where('status', 'not_visited')->count()
        ];
    }

    /**
     * Calculate and create result.
     */
    public function calculateResult(): bool
    {
        if (!in_array($this->status, ['submitted', 'auto_submitted'])) {
            return false;
        }
        
        // Check if result already exists
        if ($this->result) {
            return false;
        }
        
        $result = EntranceExamResult::createFromResponse($this);
        
        return $result !== null;
    }

    /**
     * Get time spent in minutes.
     */
    public function getTimeSpentMinutes(): int
    {
        return (int) ceil($this->time_spent_seconds / 60);
    }

    /**
     * Get remaining time in minutes.
     */
    public function getRemainingTimeMinutes(): int
    {
        return (int) floor($this->remaining_time_seconds / 60);
    }

    /**
     * Check if exam is timed out.
     */
    public function isTimedOut(): bool
    {
        if ($this->status !== 'in_progress') {
            return false;
        }
        
        return $this->remaining_time_seconds === 0;
    }

    /**
     * Get violation statistics.
     */
    public function getViolationStats(): array
    {
        $stats = [
            'total_violations' => count($this->violations ?? []),
            'tab_switches' => $this->tab_switches,
            'copy_attempts' => $this->copy_attempts,
            'paste_attempts' => $this->paste_attempts,
            'by_severity' => [
                'low' => 0,
                'medium' => 0,
                'high' => 0,
                'critical' => 0
            ]
        ];
        
        foreach ($this->violations ?? [] as $violation) {
            $severity = $violation['severity'] ?? 'low';
            $stats['by_severity'][$severity]++;
        }
        
        return $stats;
    }

    /**
     * Check if response is suspicious.
     */
    public function isSuspicious(): bool
    {
        $violationStats = $this->getViolationStats();
        
        return $violationStats['total_violations'] > 5 ||
               $violationStats['by_severity']['critical'] > 0 ||
               $violationStats['by_severity']['high'] > 2;
    }

    /**
     * Get browser fingerprint.
     */
    public function generateBrowserFingerprint(): string
    {
        $data = [
            'user_agent' => $this->user_agent,
            'ip_address' => $this->ip_address,
            'screen_resolution' => request()->header('X-Screen-Resolution'),
            'timezone' => request()->header('X-Timezone'),
            'language' => request()->header('Accept-Language')
        ];
        
        return hash('sha256', json_encode($data));
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'not_started' => 'gray',
            'in_progress' => 'blue',
            'submitted' => 'green',
            'auto_submitted' => 'yellow',
            'terminated' => 'red',
            'system_error' => 'orange',
            default => 'gray'
        };
    }

    /**
     * Generate response summary.
     */
    public function generateSummary(): array
    {
        return [
            'registration' => [
                'number' => $this->registration->registration_number ?? null,
                'candidate' => $this->registration->candidate_name ?? null
            ],
            'session' => [
                'code' => $this->session->session_code ?? null,
                'date' => $this->session->session_date->format('Y-m-d')
            ],
            'paper' => [
                'code' => $this->paper->paper_code ?? null,
                'set' => $this->paper->paper_set ?? null
            ],
            'timing' => [
                'started_at' => $this->exam_started_at?->format('H:i:s'),
                'submitted_at' => $this->exam_submitted_at?->format('H:i:s'),
                'time_spent_minutes' => $this->getTimeSpentMinutes(),
                'remaining_minutes' => $this->getRemainingTimeMinutes()
            ],
            'progress' => $this->getResponseSummary(),
            'violations' => $this->getViolationStats(),
            'status' => $this->status,
            'is_suspicious' => $this->isSuspicious(),
            'browser_info' => [
                'ip_address' => $this->ip_address,
                'user_agent' => $this->user_agent,
                'fingerprint' => $this->browser_fingerprint
            ]
        ];
    }
}