<?php

namespace App\Services;

use App\Models\ExamResponse;
use App\Models\ExamProctoringLog;
use App\Models\EntranceExamRegistration;
use App\Models\ExamSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class ExamProctoringService
{
    /**
     * Proctoring event types and their severity levels
     */
    private const PROCTORING_EVENTS = [
        'face_not_detected' => ['severity' => 'high', 'threshold' => 3],
        'multiple_faces' => ['severity' => 'critical', 'threshold' => 1],
        'face_mismatch' => ['severity' => 'critical', 'threshold' => 2],
        'tab_switch' => ['severity' => 'medium', 'threshold' => 5],
        'window_blur' => ['severity' => 'low', 'threshold' => 10],
        'copy_attempt' => ['severity' => 'high', 'threshold' => 2],
        'paste_attempt' => ['severity' => 'high', 'threshold' => 2],
        'right_click' => ['severity' => 'medium', 'threshold' => 5],
        'print_attempt' => ['severity' => 'critical', 'threshold' => 1],
        'screenshot_attempt' => ['severity' => 'critical', 'threshold' => 1],
        'fullscreen_exit' => ['severity' => 'high', 'threshold' => 2],
        'network_disconnect' => ['severity' => 'medium', 'threshold' => 3],
        'unusual_activity' => ['severity' => 'high', 'threshold' => 2],
    ];

    /**
     * Severity weights for calculating violation score
     */
    private const SEVERITY_WEIGHTS = [
        'low' => 1,
        'medium' => 3,
        'high' => 5,
        'critical' => 10,
    ];

    /**
     * AI confidence thresholds
     */
    private const AI_CONFIDENCE_THRESHOLDS = [
        'face_detection' => 0.85,
        'face_recognition' => 0.90,
        'suspicious_behavior' => 0.75,
        'object_detection' => 0.80,
    ];

    /**
     * Start proctoring for an exam response
     *
     * @param int $responseId
     * @return array
     * @throws Exception
     */
    public function startProctoring(int $responseId): array
    {
        DB::beginTransaction();

        try {
            $response = ExamResponse::with(['registration'])->findOrFail($responseId);

            // Validate exam is in progress
            if ($response->status !== 'in_progress') {
                throw new Exception("Proctoring can only be started for ongoing exams");
            }

            // Initialize proctoring session
            $proctoringSession = $this->initializeProctoringSession($response);

            // Start continuous monitoring
            $this->startContinuousMonitoring($response);

            // Capture initial snapshot
            $initialSnapshot = $this->captureInitialSnapshot($response);

            // Store proctoring configuration
            $this->storeProctoringConfig($response, $proctoringSession);

            DB::commit();

            Log::info('Proctoring started', [
                'response_id' => $responseId,
                'session_id' => $proctoringSession['session_id'],
            ]);

            return [
                'status' => 'active',
                'session_id' => $proctoringSession['session_id'],
                'monitoring_config' => [
                    'screenshot_interval' => 30, // seconds
                    'face_check_interval' => 60, // seconds
                    'activity_check_interval' => 5, // seconds
                    'bandwidth_requirement' => '1mbps',
                ],
                'initial_checks' => $initialSnapshot,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to start proctoring', [
                'response_id' => $responseId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Capture screenshot during exam
     *
     * @param int $responseId
     * @param string $imageData Base64 encoded image
     * @return array
     * @throws Exception
     */
    public function captureScreenshot(int $responseId, string $imageData): array
    {
        try {
            $response = ExamResponse::findOrFail($responseId);

            // Validate exam is in progress
            if ($response->status !== 'in_progress') {
                throw new Exception("Cannot capture screenshot for inactive exam");
            }

            // Decode and validate image
            $image = $this->decodeAndValidateImage($imageData);

            // Generate unique filename
            $filename = $this->generateScreenshotFilename($response);
            $path = "proctoring/screenshots/{$response->registration_id}/{$filename}";

            // Store screenshot
            Storage::put($path, $image);

            // Analyze screenshot for violations
            $analysis = $this->analyzeScreenshot($image, $response);

            // Log if violations detected
            if (!empty($analysis['violations'])) {
                foreach ($analysis['violations'] as $violation) {
                    $this->logProctoringEvent($response, $violation['type'], [
                        'screenshot_path' => $path,
                        'confidence' => $violation['confidence'],
                        'details' => $violation['details'],
                    ]);
                }
            }

            // Store screenshot record
            DB::table('exam_screenshots')->insert([
                'response_id' => $responseId,
                'file_path' => $path,
                'captured_at' => now(),
                'analysis_result' => json_encode($analysis),
                'has_violations' => !empty($analysis['violations']),
            ]);

            return [
                'status' => 'captured',
                'screenshot_id' => DB::getPdo()->lastInsertId(),
                'analysis' => $analysis,
                'violations_detected' => !empty($analysis['violations']),
            ];

        } catch (Exception $e) {
            Log::error('Failed to capture screenshot', [
                'response_id' => $responseId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Detect face anomaly
     *
     * @param int $responseId
     * @param string $imageData Base64 encoded face image
     * @return array
     * @throws Exception
     */
    public function detectFaceAnomaly(int $responseId, string $imageData): array
    {
        try {
            $response = ExamResponse::with(['registration'])->findOrFail($responseId);

            // Decode image
            $image = $this->decodeAndValidateImage($imageData);

            // Perform face detection
            $faceDetection = $this->performFaceDetection($image);

            // Check for anomalies
            $anomalies = [];

            // Check if face is detected
            if ($faceDetection['faces_count'] === 0) {
                $anomalies[] = [
                    'type' => 'face_not_detected',
                    'severity' => 'high',
                    'message' => 'No face detected in frame',
                ];
            }

            // Check for multiple faces
            if ($faceDetection['faces_count'] > 1) {
                $anomalies[] = [
                    'type' => 'multiple_faces',
                    'severity' => 'critical',
                    'message' => "Multiple faces detected: {$faceDetection['faces_count']}",
                ];
            }

            // Verify face identity if face detected
            if ($faceDetection['faces_count'] === 1) {
                $verification = $this->verifyFaceIdentity($response, $faceDetection['face_data']);
                
                if (!$verification['match']) {
                    $anomalies[] = [
                        'type' => 'face_mismatch',
                        'severity' => 'critical',
                        'message' => 'Face does not match registered candidate',
                        'confidence' => $verification['confidence'],
                    ];
                }
            }

            // Log anomalies
            foreach ($anomalies as $anomaly) {
                $this->logProctoringEvent($response, $anomaly['type'], [
                    'message' => $anomaly['message'],
                    'confidence' => $anomaly['confidence'] ?? null,
                ]);
            }

            // Take action if critical anomalies
            if ($this->hasCriticalAnomalies($anomalies)) {
                $this->handleCriticalAnomaly($response, $anomalies);
            }

            return [
                'status' => 'analyzed',
                'faces_detected' => $faceDetection['faces_count'],
                'anomalies' => $anomalies,
                'requires_action' => !empty($anomalies),
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ];

        } catch (Exception $e) {
            Log::error('Failed to detect face anomaly', [
                'response_id' => $responseId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Monitor tab switch
     *
     * @param int $responseId
     * @return array
     */
    public function monitorTabSwitch(int $responseId): array
    {
        try {
            $response = ExamResponse::findOrFail($responseId);

            // Log tab switch event
            $log = $this->logProctoringEvent($response, 'tab_switch', [
                'timestamp' => now(),
                'user_agent' => request()->userAgent(),
            ]);

            // Get violation count
            $violationCount = $this->getViolationCount($response, 'tab_switch');

            // Check if threshold exceeded
            $threshold = self::PROCTORING_EVENTS['tab_switch']['threshold'];
            $action = null;

            if ($violationCount >= $threshold) {
                $action = $this->takeAutomatedAction($response, 'tab_switch', $violationCount);
            }

            return [
                'violation_count' => $violationCount,
                'threshold' => $threshold,
                'warning_issued' => $violationCount >= ($threshold / 2),
                'action_taken' => $action,
            ];

        } catch (Exception $e) {
            Log::error('Failed to monitor tab switch', [
                'response_id' => $responseId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Detect copy/paste attempts
     *
     * @param int $responseId
     * @param string $action 'copy' or 'paste'
     * @return array
     */
    public function detectCopyPaste(int $responseId, string $action): array
    {
        try {
            $response = ExamResponse::findOrFail($responseId);

            $eventType = $action === 'copy' ? 'copy_attempt' : 'paste_attempt';

            // Log the event
            $log = $this->logProctoringEvent($response, $eventType, [
                'timestamp' => now(),
                'action' => $action,
            ]);

            // Get violation count
            $violationCount = $this->getViolationCount($response, $eventType);

            // Check threshold
            $threshold = self::PROCTORING_EVENTS[$eventType]['threshold'];
            
            // Issue warning or take action
            $response = [
                'action' => $action,
                'violation_count' => $violationCount,
                'threshold' => $threshold,
                'blocked' => false,
                'warning' => null,
            ];

            if ($violationCount >= $threshold) {
                $response['blocked'] = true;
                $response['warning'] = "Copy/paste is not allowed during the exam";
                $this->takeAutomatedAction($response, $eventType, $violationCount);
            } elseif ($violationCount >= ($threshold / 2)) {
                $response['warning'] = "Warning: Copy/paste attempts are being monitored";
            }

            return $response;

        } catch (Exception $e) {
            Log::error('Failed to detect copy/paste', [
                'response_id' => $responseId,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Flag a proctoring violation
     *
     * @param int $responseId
     * @param string $violationType
     * @param array $details
     * @return array
     * @throws Exception
     */
    public function flagViolation(int $responseId, string $violationType, array $details = []): array
    {
        DB::beginTransaction();

        try {
            $response = ExamResponse::with(['registration'])->findOrFail($responseId);

            // Validate violation type
            if (!isset(self::PROCTORING_EVENTS[$violationType])) {
                throw new Exception("Invalid violation type: {$violationType}");
            }

            // Log the violation
            $log = $this->logProctoringEvent($response, $violationType, $details);

            // Calculate violation score
            $violationScore = $this->calculateViolationScore($response);

            // Determine action based on severity and score
            $action = $this->determineAction($response, $violationType, $violationScore);

            // Execute action
            if ($action['type'] !== 'none') {
                $this->executeAction($response, $action);
            }

            // Notify proctor if high severity
            if (self::PROCTORING_EVENTS[$violationType]['severity'] === 'critical') {
                $this->notifyProctor($response, $violationType, $details);
            }

            DB::commit();

            return [
                'violation_id' => $log->id,
                'violation_type' => $violationType,
                'severity' => self::PROCTORING_EVENTS[$violationType]['severity'],
                'total_violations' => $violationScore['total_violations'],
                'violation_score' => $violationScore['score'],
                'action_taken' => $action,
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to flag violation', [
                'response_id' => $responseId,
                'violation_type' => $violationType,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate proctoring report
     *
     * @param int $responseId
     * @return array
     * @throws Exception
     */
    public function generateProctoringReport(int $responseId): array
    {
        try {
            $response = ExamResponse::with(['registration.application', 'proctoringLogs'])
                ->findOrFail($responseId);

            // Get all proctoring logs
            $logs = $response->proctoringLogs;

            // Categorize violations
            $violations = $this->categorizeViolations($logs);

            // Calculate integrity score
            $integrityScore = $this->calculateIntegrityScore($violations);

            // Get screenshot analysis summary
            $screenshotSummary = $this->getScreenshotSummary($responseId);

            // Generate timeline of events
            $timeline = $this->generateEventTimeline($logs);

            // Determine overall assessment
            $assessment = $this->determineOverallAssessment($integrityScore, $violations);

            $report = [
                'response_id' => $responseId,
                'candidate' => [
                    'registration_number' => $response->registration->registration_number,
                    'name' => $response->registration->application ? 
                        $response->registration->application->first_name . ' ' . 
                        $response->registration->application->last_name : 
                        $response->registration->candidate_name,
                ],
                'exam_details' => [
                    'started_at' => $response->exam_started_at,
                    'submitted_at' => $response->exam_submitted_at,
                    'duration_minutes' => $response->exam_started_at ? 
                        $response->exam_started_at->diffInMinutes($response->exam_submitted_at ?? now()) : 0,
                ],
                'violations_summary' => $violations,
                'integrity_score' => $integrityScore,
                'screenshot_summary' => $screenshotSummary,
                'event_timeline' => $timeline,
                'assessment' => $assessment,
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ];

            // Store report
            $this->storeReport($response, $report);

            return $report;

        } catch (Exception $e) {
            Log::error('Failed to generate proctoring report', [
                'response_id' => $responseId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Review proctoring logs for a session
     *
     * @param int $sessionId
     * @return array
     */
    public function reviewProctoringLogs(int $sessionId): array
    {
        try {
            $session = ExamSession::with(['responses.proctoringLogs'])->findOrFail($sessionId);

            $summary = [
                'session_id' => $sessionId,
                'total_candidates' => 0,
                'flagged_candidates' => [],
                'violation_statistics' => [],
                'requires_manual_review' => [],
            ];

            foreach ($session->responses as $response) {
                $summary['total_candidates']++;
                
                $logs = $response->proctoringLogs;
                if ($logs->isEmpty()) {
                    continue;
                }

                // Check for critical violations
                $criticalViolations = $logs->filter(function ($log) {
                    return $log->severity === 'critical';
                });

                if ($criticalViolations->isNotEmpty()) {
                    $summary['flagged_candidates'][] = [
                        'response_id' => $response->id,
                        'registration_id' => $response->registration_id,
                        'critical_violations' => $criticalViolations->count(),
                        'total_violations' => $logs->count(),
                    ];
                }

                // Check if manual review needed
                $violationScore = $this->calculateViolationScore($response);
                if ($violationScore['score'] > 50 || $criticalViolations->count() > 2) {
                    $summary['requires_manual_review'][] = $response->id;
                }
            }

            // Calculate violation statistics
            $summary['violation_statistics'] = $this->calculateSessionViolationStats($session);

            return $summary;

        } catch (Exception $e) {
            Log::error('Failed to review proctoring logs', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * AI-based proctoring analysis
     *
     * @param int $responseId
     * @return array
     * @throws Exception
     */
    public function aiProctoringAnalysis(int $responseId): array
    {
        try {
            $response = ExamResponse::with(['proctoringLogs'])->findOrFail($responseId);

            // Collect all proctoring data
            $proctoringData = $this->collectProctoringData($response);

            // Perform AI analysis (would integrate with AI service)
            $aiAnalysis = $this->performAIAnalysis($proctoringData);

            // Identify patterns
            $patterns = $this->identifyBehaviorPatterns($aiAnalysis);

            // Calculate risk score
            $riskScore = $this->calculateRiskScore($aiAnalysis, $patterns);

            // Generate recommendations
            $recommendations = $this->generateAIRecommendations($riskScore, $patterns);

            return [
                'response_id' => $responseId,
                'ai_confidence' => $aiAnalysis['confidence'],
                'risk_score' => $riskScore,
                'behavior_patterns' => $patterns,
                'suspicious_activities' => $aiAnalysis['suspicious_activities'],
                'recommendations' => $recommendations,
                'requires_human_review' => $riskScore > 70 || $aiAnalysis['confidence'] < 0.7,
                'analysis_timestamp' => now()->format('Y-m-d H:i:s'),
            ];

        } catch (Exception $e) {
            Log::error('AI proctoring analysis failed', [
                'response_id' => $responseId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Private helper methods
     */

    /**
     * Initialize proctoring session
     */
    private function initializeProctoringSession(ExamResponse $response): array
    {
        $sessionId = Str::uuid();
        
        $session = [
            'session_id' => $sessionId,
            'response_id' => $response->id,
            'started_at' => now(),
            'config' => [
                'enable_face_detection' => true,
                'enable_screen_recording' => true,
                'enable_activity_monitoring' => true,
                'enable_ai_analysis' => true,
            ],
        ];

        // Store in cache
        Cache::put("proctoring_session_{$response->id}", $session, 7200);

        return $session;
    }

    /**
     * Start continuous monitoring
     */
    private function startContinuousMonitoring(ExamResponse $response): void
    {
        // This would typically trigger background jobs or websocket connections
        // For monitoring various activities
        
        $monitoringKey = "proctoring_monitoring_{$response->id}";
        Cache::put($monitoringKey, [
            'status' => 'active',
            'started_at' => now(),
            'last_check' => now(),
        ], 7200);
    }

    /**
     * Capture initial snapshot for comparison
     */
    private function captureInitialSnapshot(ExamResponse $response): array
    {
        return [
            'face_captured' => true,
            'environment_check' => [
                'lighting' => 'adequate',
                'background' => 'clear',
                'audio_level' => 'normal',
            ],
            'system_check' => [
                'camera' => 'working',
                'microphone' => 'working',
                'screen_share' => 'enabled',
            ],
        ];
    }

    /**
     * Store proctoring configuration
     */
    private function storeProctoringConfig(ExamResponse $response, array $config): void
    {
        DB::table('exam_proctoring_configs')->insert([
            'response_id' => $response->id,
            'config' => json_encode($config),
            'created_at' => now(),
        ]);
    }

    /**
     * Decode and validate base64 image
     */
    private function decodeAndValidateImage(string $imageData): string
    {
        // Remove data URL prefix if present
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
        
        $image = base64_decode($imageData);
        
        if ($image === false) {
            throw new Exception("Invalid image data");
        }

        // Validate image size (max 5MB)
        if (strlen($image) > 5242880) {
            throw new Exception("Image size exceeds limit");
        }

        return $image;
    }

    /**
     * Generate screenshot filename
     */
    private function generateScreenshotFilename(ExamResponse $response): string
    {
        return sprintf(
            'screenshot_%s_%s.jpg',
            $response->id,
            now()->format('YmdHis')
        );
    }

    /**
     * Analyze screenshot for violations
     */
    private function analyzeScreenshot(string $image, ExamResponse $response): array
    {
        // This would integrate with image analysis service
        // For now, return mock analysis
        
        return [
            'clean' => true,
            'violations' => [],
            'objects_detected' => [],
            'text_detected' => false,
            'confidence' => 0.95,
        ];
    }

    /**
     * Perform face detection on image
     */
    private function performFaceDetection(string $image): array
    {
        // This would integrate with face detection service
        // For now, return mock data
        
        return [
            'faces_count' => 1,
            'face_data' => [
                'encoding' => base64_encode(random_bytes(128)),
                'confidence' => 0.92,
            ],
        ];
    }

    /**
     * Verify face identity
     */
    private function verifyFaceIdentity(ExamResponse $response, array $faceData): array
    {
        // This would compare with stored face data
        // For now, return mock verification
        
        return [
            'match' => true,
            'confidence' => 0.91,
        ];
    }

    /**
     * Log proctoring event
     */
    private function logProctoringEvent(ExamResponse $response, string $eventType, array $metadata = []): ExamProctoringLog
    {
        return ExamProctoringLog::create([
            'response_id' => $response->id,
            'registration_id' => $response->registration_id,
            'event_type' => $eventType,
            'severity' => self::PROCTORING_EVENTS[$eventType]['severity'] ?? 'low',
            'description' => $metadata['message'] ?? "Event: {$eventType}",
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }

    /**
     * Get violation count for specific type
     */
    private function getViolationCount(ExamResponse $response, string $eventType): int
    {
        return ExamProctoringLog::where('response_id', $response->id)
            ->where('event_type', $eventType)
            ->count();
    }

    /**
     * Check for critical anomalies
     */
    private function hasCriticalAnomalies(array $anomalies): bool
    {
        foreach ($anomalies as $anomaly) {
            if ($anomaly['severity'] === 'critical') {
                return true;
            }
        }
        return false;
    }

    /**
     * Handle critical anomaly
     */
    private function handleCriticalAnomaly(ExamResponse $response, array $anomalies): void
    {
        // Notify exam coordinator immediately
        $this->notifyExamCoordinator($response, $anomalies);
        
        // Flag response for review
        $response->flagged_for_review = true;
        $response->flag_reason = 'Critical proctoring anomaly detected';
        $response->save();
    }

    /**
     * Take automated action based on violations
     */
    private function takeAutomatedAction(ExamResponse $response, string $violationType, int $count): array
    {
        $severity = self::PROCTORING_EVENTS[$violationType]['severity'];
        
        $action = [
            'type' => 'warning',
            'message' => '',
        ];

        if ($severity === 'critical' || $count > self::PROCTORING_EVENTS[$violationType]['threshold'] * 2) {
            $action['type'] = 'terminate';
            $action['message'] = 'Exam terminated due to policy violations';
            
            // Terminate exam
            $response->status = 'terminated';
            $response->termination_reason = "Proctoring violation: {$violationType}";
            $response->save();
        } elseif ($severity === 'high') {
            $action['type'] = 'final_warning';
            $action['message'] = 'Final warning: Next violation will result in exam termination';
        } else {
            $action['message'] = 'Warning: Your activity is being monitored';
        }

        return $action;
    }

    /**
     * Calculate violation score
     */
    private function calculateViolationScore(ExamResponse $response): array
    {
        $logs = ExamProctoringLog::where('response_id', $response->id)->get();
        
        $score = 0;
        $violations = [];

        foreach ($logs as $log) {
            $weight = self::SEVERITY_WEIGHTS[$log->severity] ?? 1;
            $score += $weight;
            
            if (!isset($violations[$log->event_type])) {
                $violations[$log->event_type] = 0;
            }
            $violations[$log->event_type]++;
        }

        return [
            'score' => $score,
            'total_violations' => $logs->count(),
            'violations_by_type' => $violations,
        ];
    }

    /**
     * Determine action based on violation
     */
    private function determineAction(ExamResponse $response, string $violationType, array $violationScore): array
    {
        $severity = self::PROCTORING_EVENTS[$violationType]['severity'];
        $score = $violationScore['score'];

        if ($score > 100 || $severity === 'critical') {
            return [
                'type' => 'terminate',
                'reason' => 'Excessive violations detected',
            ];
        } elseif ($score > 50) {
            return [
                'type' => 'flag_for_review',
                'reason' => 'Multiple violations require manual review',
            ];
        } elseif ($score > 25) {
            return [
                'type' => 'warning',
                'reason' => 'Violations detected - warning issued',
            ];
        }

        return [
            'type' => 'none',
            'reason' => 'Within acceptable limits',
        ];
    }

    /**
     * Execute determined action
     */
    private function executeAction(ExamResponse $response, array $action): void
    {
        switch ($action['type']) {
            case 'terminate':
                $response->status = 'terminated';
                $response->termination_reason = $action['reason'];
                $response->save();
                break;
                
            case 'flag_for_review':
                $response->flagged_for_review = true;
                $response->flag_reason = $action['reason'];
                $response->save();
                break;
                
            case 'warning':
                // Send warning notification to candidate
                $this->sendWarningNotification($response, $action['reason']);
                break;
        }
    }

    // Additional helper methods for notifications, AI analysis, etc. would go here...
}