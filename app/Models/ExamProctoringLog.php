<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamProctoringLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'exam_proctoring_logs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'response_id',
        'registration_id',
        'event_type',
        'severity',
        'description',
        'screenshot_path',
        'metadata',
        'occurred_at',
        'is_reviewed',
        'reviewed_by',
        'review_notes',
        'action_taken',
        'is_violation',
        'violation_level'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'metadata' => 'array',
        'occurred_at' => 'datetime',
        'is_reviewed' => 'boolean',
        'is_violation' => 'boolean',
        'violation_level' => 'integer'
    ];

    /**
     * Event type definitions with severity levels.
     */
    const EVENT_SEVERITIES = [
        'face_not_detected' => 'high',
        'multiple_faces' => 'critical',
        'face_mismatch' => 'critical',
        'tab_switch' => 'medium',
        'window_blur' => 'low',
        'copy_attempt' => 'high',
        'paste_attempt' => 'high',
        'right_click' => 'low',
        'print_attempt' => 'high',
        'screenshot_attempt' => 'high',
        'fullscreen_exit' => 'medium',
        'network_disconnect' => 'medium',
        'unusual_activity' => 'high',
        'browser_console_open' => 'critical',
        'developer_tools_open' => 'critical',
        'virtual_machine_detected' => 'critical',
        'screen_recording_detected' => 'critical',
        'external_monitor_connected' => 'medium',
        'microphone_muted' => 'low',
        'camera_blocked' => 'high',
        'location_changed' => 'medium',
        'system_time_changed' => 'critical',
        'prohibited_software_detected' => 'critical'
    ];

    /**
     * Violation thresholds for automatic actions.
     */
    const VIOLATION_THRESHOLDS = [
        'warning' => 3,
        'flag_for_review' => 5,
        'suspend_exam' => 10,
        'terminate_exam' => 15
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Set default severity based on event type
        static::creating(function ($log) {
            if (!$log->severity && isset(self::EVENT_SEVERITIES[$log->event_type])) {
                $log->severity = self::EVENT_SEVERITIES[$log->event_type];
            }
            
            if (!$log->occurred_at) {
                $log->occurred_at = now();
            }

            // Determine if this is a violation
            $log->determineViolationStatus();
        });

        // Update review timestamp
        static::updating(function ($log) {
            if ($log->isDirty('is_reviewed') && $log->is_reviewed && !$log->reviewed_by) {
                $log->reviewed_by = auth()->id();
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the exam response.
     */
    public function response()
    {
        return $this->belongsTo(ExamResponse::class, 'response_id');
    }

    /**
     * Get the registration.
     */
    public function registration()
    {
        return $this->belongsTo(EntranceExamRegistration::class, 'registration_id');
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
     * Scope for unreviewed logs.
     */
    public function scopeUnreviewed($query)
    {
        return $query->where('is_reviewed', false);
    }

    /**
     * Scope for reviewed logs.
     */
    public function scopeReviewed($query)
    {
        return $query->where('is_reviewed', true);
    }

    /**
     * Scope for violations.
     */
    public function scopeViolations($query)
    {
        return $query->where('is_violation', true);
    }

    /**
     * Scope for critical events.
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Scope for high severity events.
     */
    public function scopeHighSeverity($query)
    {
        return $query->whereIn('severity', ['critical', 'high']);
    }

    /**
     * Scope for specific event types.
     */
    public function scopeOfType($query, $type)
    {
        if (is_array($type)) {
            return $query->whereIn('event_type', $type);
        }
        return $query->where('event_type', $type);
    }

    /**
     * Scope for events within time range.
     */
    public function scopeOccurredBetween($query, $start, $end)
    {
        return $query->whereBetween('occurred_at', [$start, $end]);
    }

    /**
     * Helper Methods
     */

    /**
     * Determine if this event constitutes a violation.
     */
    protected function determineViolationStatus(): void
    {
        // Critical events are always violations
        if ($this->severity === 'critical') {
            $this->is_violation = true;
            $this->violation_level = 3;
            return;
        }

        // High severity events are usually violations
        if ($this->severity === 'high') {
            $this->is_violation = true;
            $this->violation_level = 2;
            return;
        }

        // Medium severity might be violations based on frequency
        if ($this->severity === 'medium') {
            $recentSimilarEvents = self::where('response_id', $this->response_id)
                ->where('event_type', $this->event_type)
                ->where('occurred_at', '>=', now()->subMinutes(5))
                ->count();
            
            if ($recentSimilarEvents >= 3) {
                $this->is_violation = true;
                $this->violation_level = 1;
            }
        }
    }

    /**
     * Mark log as reviewed.
     */
    public function markAsReviewed($notes = null, $actionTaken = null): bool
    {
        $this->is_reviewed = true;
        $this->review_notes = $notes;
        $this->action_taken = $actionTaken;
        $this->reviewed_by = auth()->id();
        
        return $this->save();
    }

    /**
     * Capture screenshot for the event.
     */
    public function captureScreenshot($imageData): bool
    {
        // Save screenshot to storage
        $filename = sprintf(
            'proctoring/%s/%s_%s.jpg',
            $this->response_id,
            $this->event_type,
            now()->timestamp
        );
        
        // This would save to storage (S3, local, etc.)
        // Storage::disk('proctoring')->put($filename, $imageData);
        
        $this->screenshot_path = $filename;
        return $this->save();
    }

    /**
     * Get violation count for the response.
     */
    public function getResponseViolationCount(): int
    {
        return self::where('response_id', $this->response_id)
            ->where('is_violation', true)
            ->count();
    }

    /**
     * Check if automatic action should be taken.
     */
    public function checkAutomaticAction(): ?string
    {
        $violationCount = $this->getResponseViolationCount();
        
        if ($violationCount >= self::VIOLATION_THRESHOLDS['terminate_exam']) {
            return 'terminate_exam';
        }
        
        if ($violationCount >= self::VIOLATION_THRESHOLDS['suspend_exam']) {
            return 'suspend_exam';
        }
        
        if ($violationCount >= self::VIOLATION_THRESHOLDS['flag_for_review']) {
            return 'flag_for_review';
        }
        
        if ($violationCount >= self::VIOLATION_THRESHOLDS['warning']) {
            return 'warning';
        }
        
        return null;
    }

    /**
     * Execute automatic action based on violations.
     */
    public function executeAutomaticAction(): ?string
    {
        $action = $this->checkAutomaticAction();
        
        if (!$action || !$this->response) {
            return null;
        }

        switch ($action) {
            case 'terminate_exam':
                $this->response->status = 'terminated';
                $this->response->save();
                $this->logAction('Exam automatically terminated due to violations');
                break;
                
            case 'suspend_exam':
                $this->response->status = 'suspended';
                $this->response->save();
                $this->logAction('Exam automatically suspended pending review');
                break;
                
            case 'flag_for_review':
                $this->response->flagged_for_review = true;
                $this->response->save();
                $this->logAction('Exam flagged for manual review');
                break;
                
            case 'warning':
                // Send warning to candidate
                $this->sendWarningToCandidate();
                $this->logAction('Warning sent to candidate');
                break;
        }
        
        return $action;
    }

    /**
     * Send warning to candidate.
     */
    protected function sendWarningToCandidate(): void
    {
        // This would typically send a real-time notification
        // Implementation depends on your notification system
        
        broadcast(new ProctoringWarning(
            $this->response_id,
            $this->event_type,
            $this->getResponseViolationCount()
        ));
    }

    /**
     * Log action taken.
     */
    protected function logAction($action): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['automatic_actions'] = $metadata['automatic_actions'] ?? [];
        $metadata['automatic_actions'][] = [
            'action' => $action,
            'timestamp' => now()->toIso8601String()
        ];
        
        $this->metadata = $metadata;
        $this->save();
    }

    /**
     * Get event type label.
     */
    public function getEventTypeLabel(): string
    {
        return str_replace('_', ' ', ucfirst($this->event_type));
    }

    /**
     * Get severity color for UI.
     */
    public function getSeverityColor(): string
    {
        return match($this->severity) {
            'critical' => 'red',
            'high' => 'orange',
            'medium' => 'yellow',
            'low' => 'blue',
            default => 'gray'
        };
    }

    /**
     * Get severity icon for UI.
     */
    public function getSeverityIcon(): string
    {
        return match($this->severity) {
            'critical' => 'exclamation-circle',
            'high' => 'exclamation-triangle',
            'medium' => 'exclamation',
            'low' => 'info-circle',
            default => 'question-circle'
        };
    }

    /**
     * Generate AI analysis of the event.
     */
    public function generateAIAnalysis(): array
    {
        // This would integrate with an AI service for pattern analysis
        // Placeholder implementation
        
        $analysis = [
            'risk_score' => $this->calculateRiskScore(),
            'pattern_detected' => $this->detectPattern(),
            'recommended_action' => $this->getRecommendedAction(),
            'confidence_level' => $this->calculateConfidenceLevel()
        ];
        
        return $analysis;
    }

    /**
     * Calculate risk score based on event patterns.
     */
    protected function calculateRiskScore(): int
    {
        $score = 0;
        
        // Base score from severity
        $score += match($this->severity) {
            'critical' => 40,
            'high' => 30,
            'medium' => 20,
            'low' => 10,
            default => 0
        };
        
        // Frequency factor
        $recentEvents = self::where('response_id', $this->response_id)
            ->where('occurred_at', '>=', now()->subMinutes(10))
            ->count();
        
        $score += min($recentEvents * 5, 30);
        
        // Pattern factor
        if ($this->detectPattern()) {
            $score += 20;
        }
        
        return min($score, 100); // Cap at 100
    }

    /**
     * Detect suspicious patterns.
     */
    protected function detectPattern(): bool
    {
        // Check for suspicious patterns
        $patterns = [
            'rapid_tab_switching' => $this->checkRapidTabSwitching(),
            'copy_paste_pattern' => $this->checkCopyPastePattern(),
            'face_detection_pattern' => $this->checkFaceDetectionPattern()
        ];
        
        return in_array(true, $patterns);
    }

    /**
     * Check for rapid tab switching pattern.
     */
    protected function checkRapidTabSwitching(): bool
    {
        $recentTabSwitches = self::where('response_id', $this->response_id)
            ->where('event_type', 'tab_switch')
            ->where('occurred_at', '>=', now()->subMinutes(2))
            ->count();
        
        return $recentTabSwitches >= 5;
    }

    /**
     * Check for copy-paste pattern.
     */
    protected function checkCopyPastePattern(): bool
    {
        $copyEvents = self::where('response_id', $this->response_id)
            ->whereIn('event_type', ['copy_attempt', 'paste_attempt'])
            ->where('occurred_at', '>=', now()->subMinutes(5))
            ->count();
        
        return $copyEvents >= 3;
    }

    /**
     * Check for face detection issues pattern.
     */
    protected function checkFaceDetectionPattern(): bool
    {
        $faceEvents = self::where('response_id', $this->response_id)
            ->whereIn('event_type', ['face_not_detected', 'multiple_faces', 'face_mismatch'])
            ->where('occurred_at', '>=', now()->subMinutes(5))
            ->count();
        
        return $faceEvents >= 3;
    }

    /**
     * Get recommended action based on analysis.
     */
    protected function getRecommendedAction(): string
    {
        $riskScore = $this->calculateRiskScore();
        
        return match(true) {
            $riskScore >= 80 => 'Immediate manual review required',
            $riskScore >= 60 => 'Flag for post-exam review',
            $riskScore >= 40 => 'Send warning to candidate',
            $riskScore >= 20 => 'Monitor closely',
            default => 'No action required'
        };
    }

    /**
     * Calculate confidence level of violation detection.
     */
    protected function calculateConfidenceLevel(): float
    {
        // Factors affecting confidence
        $factors = [
            'has_screenshot' => $this->screenshot_path ? 0.3 : 0,
            'severity_match' => 0.3,
            'pattern_detected' => $this->detectPattern() ? 0.2 : 0,
            'metadata_complete' => !empty($this->metadata) ? 0.2 : 0
        ];
        
        return array_sum($factors);
    }

    /**
     * Get summary statistics for the exam session.
     */
    public static function getSessionStatistics($responseId): array
    {
        $logs = self::where('response_id', $responseId)->get();
        
        return [
            'total_events' => $logs->count(),
            'violations' => $logs->where('is_violation', true)->count(),
            'critical_events' => $logs->where('severity', 'critical')->count(),
            'high_severity_events' => $logs->where('severity', 'high')->count(),
            'reviewed_events' => $logs->where('is_reviewed', true)->count(),
            'event_types' => $logs->groupBy('event_type')->map->count(),
            'timeline' => $logs->groupBy(function ($log) {
                return $log->occurred_at->format('H:i');
            })->map->count()
        ];
    }
}