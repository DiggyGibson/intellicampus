<?php

namespace App\Services;

use App\Models\EntranceExamRegistration;
use App\Models\ExamResponse;
use App\Models\ExamResponseDetail;
use App\Models\ExamQuestionPaper;
use App\Models\ExamQuestion;
use App\Models\ExamSession;
use App\Models\ExamSeatAllocation;
use App\Models\EntranceExam;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class OnlineExamService
{
    /**
     * Response statuses
     */
    private const RESPONSE_STATUSES = [
        'not_started' => 'Not Started',
        'in_progress' => 'In Progress',
        'submitted' => 'Submitted',
        'auto_submitted' => 'Auto Submitted',
        'terminated' => 'Terminated',
        'system_error' => 'System Error',
    ];

    /**
     * Question navigation states
     */
    private const QUESTION_STATES = [
        'not_visited' => 'Not Visited',
        'not_answered' => 'Not Answered',
        'answered' => 'Answered',
        'marked_review' => 'Marked for Review',
        'answered_marked_review' => 'Answered & Marked for Review',
    ];

    /**
     * Auto-save interval in seconds
     */
    private const AUTO_SAVE_INTERVAL = 30;

    /**
     * Initialize online exam for a candidate
     *
     * @param int $registrationId
     * @return array
     * @throws Exception
     */
    public function initializeOnlineExam(int $registrationId): array
    {
        DB::beginTransaction();

        try {
            $registration = EntranceExamRegistration::with(['exam', 'seatAllocation.session'])
                ->findOrFail($registrationId);

            // Validate exam can be started
            $validation = $this->validateExamStart($registration);
            if (!$validation['can_start']) {
                throw new Exception($validation['message']);
            }

            // Get or create exam response
            $response = ExamResponse::firstOrCreate(
                [
                    'registration_id' => $registrationId,
                    'session_id' => $registration->seatAllocation->session_id,
                ],
                [
                    'paper_id' => $this->assignQuestionPaper($registration),
                    'status' => 'not_started',
                    'remaining_time_seconds' => $registration->exam->duration_minutes * 60,
                ]
            );

            // Initialize exam session
            $sessionData = $this->initializeExamSession($response, $registration);

            // Generate secure exam token
            $examToken = $this->generateExamToken($registration);

            // Store session in cache
            $this->storeExamSession($registrationId, $sessionData, $examToken);

            // Log initialization
            $this->logExamEvent($response, 'exam_initialized', [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            DB::commit();

            Log::info('Online exam initialized', [
                'registration_id' => $registrationId,
                'response_id' => $response->id,
            ]);

            return [
                'status' => 'success',
                'exam_token' => $examToken,
                'session_data' => $sessionData,
                'exam_config' => [
                    'duration_minutes' => $registration->exam->duration_minutes,
                    'total_questions' => $registration->exam->total_questions,
                    'total_marks' => $registration->exam->total_marks,
                    'negative_marking' => $registration->exam->negative_marking,
                    'auto_save_interval' => self::AUTO_SAVE_INTERVAL,
                ],
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to initialize online exam', [
                'registration_id' => $registrationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Authenticate candidate for exam
     *
     * @param int $registrationId
     * @param array $credentials
     * @return array
     * @throws Exception
     */
    public function authenticateCandidate(int $registrationId, array $credentials): array
    {
        try {
            $registration = EntranceExamRegistration::findOrFail($registrationId);

            // Validate credentials
            $isValid = $this->validateCandidateCredentials($registration, $credentials);
            
            if (!$isValid) {
                // Log failed attempt
                $this->logFailedAuthentication($registration);
                throw new Exception("Invalid credentials");
            }

            // Check if candidate is already in an active session
            $activeSession = $this->checkActiveSession($registrationId);
            if ($activeSession) {
                return [
                    'status' => 'active_session',
                    'message' => 'You already have an active exam session',
                    'session_id' => $activeSession['session_id'],
                    'can_resume' => true,
                ];
            }

            // Generate authentication token
            $authToken = $this->generateAuthToken($registration);

            // Store authentication
            $this->storeAuthentication($registrationId, $authToken);

            Log::info('Candidate authenticated', [
                'registration_id' => $registrationId,
            ]);

            return [
                'status' => 'authenticated',
                'auth_token' => $authToken,
                'can_start_exam' => true,
                'registration_details' => [
                    'registration_number' => $registration->registration_number,
                    'hall_ticket' => $registration->hall_ticket_number,
                    'exam_name' => $registration->exam->exam_name,
                ],
            ];

        } catch (Exception $e) {
            Log::error('Authentication failed', [
                'registration_id' => $registrationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Load question paper for candidate
     *
     * @param int $registrationId
     * @return array
     * @throws Exception
     */
    public function loadQuestionPaper(int $registrationId): array
    {
        try {
            $response = ExamResponse::where('registration_id', $registrationId)
                ->with(['paper', 'registration.exam'])
                ->firstOrFail();

            // Check if exam has started
            if ($response->status === 'not_started') {
                // Start the exam
                $response->status = 'in_progress';
                $response->exam_started_at = now();
                $response->save();
            }

            // Get question paper
            $paper = $response->paper;
            if (!$paper) {
                throw new Exception("No question paper assigned");
            }

            // Load questions
            $questions = $this->loadQuestions($paper);

            // Initialize response details for each question
            $this->initializeResponseDetails($response, $questions);

            // Get current progress
            $progress = $this->getExamProgress($response);

            return [
                'paper_id' => $paper->id,
                'paper_code' => $paper->paper_code,
                'questions' => $questions,
                'progress' => $progress,
                'start_time' => $response->exam_started_at,
                'remaining_time' => $this->calculateRemainingTime($response),
            ];

        } catch (Exception $e) {
            Log::error('Failed to load question paper', [
                'registration_id' => $registrationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Save response for a question
     *
     * @param int $responseId
     * @param int $questionId
     * @param mixed $answer
     * @return array
     * @throws Exception
     */
    public function saveResponse(int $responseId, int $questionId, $answer): array
    {
        DB::beginTransaction();

        try {
            $response = ExamResponse::findOrFail($responseId);

            // Validate exam is in progress
            if ($response->status !== 'in_progress') {
                throw new Exception("Exam is not in progress");
            }

            // Check time limit
            if ($this->hasExamExpired($response)) {
                throw new Exception("Exam time has expired");
            }

            // Get or create response detail
            $responseDetail = ExamResponseDetail::firstOrCreate(
                [
                    'response_id' => $responseId,
                    'question_id' => $questionId,
                ],
                [
                    'question_number' => $this->getQuestionNumber($response->paper_id, $questionId),
                    'status' => 'not_visited',
                ]
            );

            // Update response
            $previousAnswer = $responseDetail->answer;
            $responseDetail->answer = is_array($answer) ? json_encode($answer) : $answer;
            $responseDetail->status = empty($answer) ? 'not_answered' : 'answered';
            $responseDetail->last_updated_at = now();
            
            // Track time spent
            if (!$responseDetail->first_viewed_at) {
                $responseDetail->first_viewed_at = now();
            }
            $responseDetail->visit_count = ($responseDetail->visit_count ?? 0) + 1;
            
            $responseDetail->save();

            // Update last activity
            $response->last_activity_at = now();
            $response->save();

            // Log the response
            $this->logQuestionResponse($responseDetail, $previousAnswer);

            DB::commit();

            return [
                'status' => 'saved',
                'question_id' => $questionId,
                'response_status' => $responseDetail->status,
                'saved_at' => now()->format('Y-m-d H:i:s'),
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to save response', [
                'response_id' => $responseId,
                'question_id' => $questionId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Auto-save multiple responses
     *
     * @param int $responseId
     * @param array $responses
     * @return array
     */
    public function autoSave(int $responseId, array $responses): array
    {
        $saved = [];
        $failed = [];

        foreach ($responses as $questionId => $answer) {
            try {
                $this->saveResponse($responseId, $questionId, $answer);
                $saved[] = $questionId;
            } catch (Exception $e) {
                $failed[] = [
                    'question_id' => $questionId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Update auto-save timestamp
        $response = ExamResponse::find($responseId);
        if ($response) {
            $response->last_auto_save_at = now();
            $response->save();
        }

        Log::info('Auto-save completed', [
            'response_id' => $responseId,
            'saved_count' => count($saved),
            'failed_count' => count($failed),
        ]);

        return [
            'saved' => $saved,
            'failed' => $failed,
            'auto_saved_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Navigate to a question
     *
     * @param int $responseId
     * @param string $direction
     * @param int|null $targetQuestion
     * @return array
     * @throws Exception
     */
    public function navigateQuestion(int $responseId, string $direction, ?int $targetQuestion = null): array
    {
        try {
            $response = ExamResponse::with('paper')->findOrFail($responseId);
            
            // Get question order
            $questionOrder = $response->paper->questions_order;
            $totalQuestions = count($questionOrder);
            
            // Get current question from cache or session
            $currentQuestion = $this->getCurrentQuestion($responseId);
            $currentIndex = array_search($currentQuestion, $questionOrder);
            
            // Determine next question
            $nextIndex = match($direction) {
                'next' => min($currentIndex + 1, $totalQuestions - 1),
                'previous' => max($currentIndex - 1, 0),
                'first' => 0,
                'last' => $totalQuestions - 1,
                'specific' => $targetQuestion ? array_search($targetQuestion, $questionOrder) : $currentIndex,
                default => $currentIndex,
            };
            
            $nextQuestionId = $questionOrder[$nextIndex];
            
            // Update current question in session
            $this->setCurrentQuestion($responseId, $nextQuestionId);
            
            // Track navigation
            $this->trackQuestionNavigation($responseId, $currentQuestion, $nextQuestionId);
            
            // Get question details
            $question = ExamQuestion::find($nextQuestionId);
            $responseDetail = ExamResponseDetail::where('response_id', $responseId)
                ->where('question_id', $nextQuestionId)
                ->first();
            
            return [
                'question_id' => $nextQuestionId,
                'question_number' => $nextIndex + 1,
                'question' => $question,
                'current_answer' => $responseDetail?->answer,
                'status' => $responseDetail?->status ?? 'not_visited',
                'is_marked_for_review' => in_array($responseDetail?->status, ['marked_review', 'answered_marked_review']),
                'navigation' => [
                    'current' => $nextIndex + 1,
                    'total' => $totalQuestions,
                    'has_previous' => $nextIndex > 0,
                    'has_next' => $nextIndex < $totalQuestions - 1,
                ],
            ];

        } catch (Exception $e) {
            Log::error('Navigation failed', [
                'response_id' => $responseId,
                'direction' => $direction,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark question for review
     *
     * @param int $responseId
     * @param int $questionId
     * @return array
     * @throws Exception
     */
    public function markForReview(int $responseId, int $questionId): array
    {
        try {
            $responseDetail = ExamResponseDetail::where('response_id', $responseId)
                ->where('question_id', $questionId)
                ->firstOrFail();
            
            // Update status based on current state
            $newStatus = match($responseDetail->status) {
                'not_answered', 'not_visited' => 'marked_review',
                'answered' => 'answered_marked_review',
                'marked_review' => 'not_answered',
                'answered_marked_review' => 'answered',
                default => $responseDetail->status,
            };
            
            $responseDetail->status = $newStatus;
            $responseDetail->save();
            
            // Get review statistics
            $reviewStats = $this->getReviewStatistics($responseId);
            
            return [
                'question_id' => $questionId,
                'new_status' => $newStatus,
                'is_marked' => in_array($newStatus, ['marked_review', 'answered_marked_review']),
                'review_stats' => $reviewStats,
            ];

        } catch (Exception $e) {
            Log::error('Failed to mark for review', [
                'response_id' => $responseId,
                'question_id' => $questionId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Submit exam
     *
     * @param int $responseId
     * @return array
     * @throws Exception
     */
    public function submitExam(int $responseId): array
    {
        DB::beginTransaction();

        try {
            $response = ExamResponse::with(['registration', 'responseDetails'])->findOrFail($responseId);

            // Validate submission
            if (!in_array($response->status, ['in_progress', 'auto_submitted'])) {
                throw new Exception("Exam cannot be submitted in current status: {$response->status}");
            }

            // Get submission statistics
            $stats = $this->getSubmissionStatistics($response);

            // Confirm if there are unanswered questions
            if ($stats['unanswered'] > 0) {
                Log::warning('Exam submitted with unanswered questions', [
                    'response_id' => $responseId,
                    'unanswered_count' => $stats['unanswered'],
                ]);
            }

            // Update response status
            $response->status = 'submitted';
            $response->exam_submitted_at = now();
            $response->time_spent_seconds = $this->calculateTimeSpent($response);
            $response->submission_ip = request()->ip();
            $response->save();

            // Lock all response details
            $this->lockResponseDetails($response);

            // Clear exam session from cache
            $this->clearExamSession($response->registration_id);

            // Generate submission receipt
            $receiptNumber = $this->generateSubmissionReceipt($response);

            // Log submission
            $this->logExamEvent($response, 'exam_submitted', $stats);

            DB::commit();

            Log::info('Exam submitted', [
                'response_id' => $responseId,
                'stats' => $stats,
            ]);

            return [
                'status' => 'submitted',
                'receipt_number' => $receiptNumber,
                'submission_time' => now()->format('Y-m-d H:i:s'),
                'statistics' => $stats,
                'message' => 'Your exam has been successfully submitted.',
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit exam', [
                'response_id' => $responseId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle connection loss
     *
     * @param int $responseId
     * @return array
     * @throws Exception
     */
    public function handleConnectionLoss(int $responseId): array
    {
        try {
            $response = ExamResponse::findOrFail($responseId);

            // Log connection loss
            $this->logExamEvent($response, 'connection_lost', [
                'timestamp' => now(),
                'ip_address' => request()->ip(),
            ]);

            // Save current state
            $savedState = $this->saveExamState($response);

            // Pause timer if configured
            if ($response->registration->exam->pause_on_disconnect ?? false) {
                $response->timer_paused = true;
                $response->timer_paused_at = now();
                $response->save();
            }

            // Generate recovery token
            $recoveryToken = $this->generateRecoveryToken($response);

            return [
                'status' => 'connection_lost',
                'state_saved' => true,
                'recovery_token' => $recoveryToken,
                'timer_paused' => $response->timer_paused,
                'can_resume' => true,
                'saved_state' => $savedState,
            ];

        } catch (Exception $e) {
            Log::error('Failed to handle connection loss', [
                'response_id' => $responseId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Resume exam after interruption
     *
     * @param int $responseId
     * @return array
     * @throws Exception
     */
    public function resumeExam(int $responseId): array
    {
        DB::beginTransaction();

        try {
            $response = ExamResponse::with(['registration.exam', 'paper'])->findOrFail($responseId);

            // Validate resume is allowed
            if ($response->status !== 'in_progress') {
                throw new Exception("Cannot resume exam. Status: {$response->status}");
            }

            // Check if exam time has expired
            if ($this->hasExamExpired($response)) {
                // Auto-submit the exam
                $this->submitExam($responseId);
                throw new Exception("Exam time has expired. Your exam has been auto-submitted.");
            }

            // Resume timer if paused
            if ($response->timer_paused) {
                $pauseDuration = $response->timer_paused_at ? 
                    now()->diffInSeconds($response->timer_paused_at) : 0;
                
                // Add paused time to remaining time
                $response->remaining_time_seconds += $pauseDuration;
                $response->timer_paused = false;
                $response->timer_paused_at = null;
                $response->save();
            }

            // Restore exam state
            $examState = $this->restoreExamState($response);

            // Log resume
            $this->logExamEvent($response, 'exam_resumed', [
                'pause_duration' => $pauseDuration ?? 0,
            ]);

            DB::commit();

            Log::info('Exam resumed', [
                'response_id' => $responseId,
            ]);

            return [
                'status' => 'resumed',
                'exam_state' => $examState,
                'remaining_time' => $this->calculateRemainingTime($response),
                'message' => 'Your exam has been successfully resumed.',
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to resume exam', [
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
     * Validate if exam can be started
     */
    private function validateExamStart(EntranceExamRegistration $registration): array
    {
        // Check registration status
        if ($registration->registration_status !== 'confirmed') {
            return [
                'can_start' => false,
                'message' => 'Registration is not confirmed',
            ];
        }

        // Check if exam window is open
        $exam = $registration->exam;
        $now = Carbon::now();
        
        if ($exam->exam_date) {
            $examDateTime = Carbon::parse($exam->exam_date . ' ' . $exam->exam_start_time);
            $examEndTime = Carbon::parse($exam->exam_date . ' ' . $exam->exam_end_time);
            
            if ($now->lt($examDateTime->subMinutes(15))) {
                return [
                    'can_start' => false,
                    'message' => 'Exam has not started yet',
                ];
            }
            
            if ($now->gt($examEndTime)) {
                return [
                    'can_start' => false,
                    'message' => 'Exam window has closed',
                ];
            }
        } elseif ($exam->exam_window_start && $exam->exam_window_end) {
            if ($now->lt($exam->exam_window_start) || $now->gt($exam->exam_window_end)) {
                return [
                    'can_start' => false,
                    'message' => 'Outside exam window',
                ];
            }
        }

        // Check if already attempted
        $existingResponse = ExamResponse::where('registration_id', $registration->id)
            ->whereIn('status', ['submitted', 'auto_submitted'])
            ->first();
        
        if ($existingResponse) {
            return [
                'can_start' => false,
                'message' => 'Exam already attempted',
            ];
        }

        return [
            'can_start' => true,
            'message' => 'Exam can be started',
        ];
    }

    /**
     * Assign question paper to candidate
     */
    private function assignQuestionPaper(EntranceExamRegistration $registration): int
    {
        // Get available papers for the exam
        $papers = ExamQuestionPaper::where('exam_id', $registration->exam_id)
            ->where('is_locked', false)
            ->pluck('id')
            ->toArray();
        
        if (empty($papers)) {
            throw new Exception("No question papers available");
        }
        
        // Randomly assign a paper
        return $papers[array_rand($papers)];
    }

    /**
     * Initialize exam session data
     */
    private function initializeExamSession(ExamResponse $response, EntranceExamRegistration $registration): array
    {
        return [
            'response_id' => $response->id,
            'registration_id' => $registration->id,
            'exam_id' => $registration->exam_id,
            'paper_id' => $response->paper_id,
            'started_at' => now(),
            'duration_minutes' => $registration->exam->duration_minutes,
            'total_questions' => $registration->exam->total_questions,
            'browser_info' => [
                'user_agent' => request()->userAgent(),
                'ip_address' => request()->ip(),
                'screen_resolution' => request()->input('screen_resolution'),
            ],
        ];
    }

    /**
     * Generate secure exam token
     */
    private function generateExamToken(EntranceExamRegistration $registration): string
    {
        $payload = [
            'registration_id' => $registration->id,
            'exam_id' => $registration->exam_id,
            'timestamp' => now()->timestamp,
            'random' => Str::random(32),
        ];
        
        return Crypt::encryptString(json_encode($payload));
    }

    /**
     * Store exam session in cache
     */
    private function storeExamSession(int $registrationId, array $sessionData, string $token): void
    {
        $cacheKey = "exam_session_{$registrationId}";
        $duration = $sessionData['duration_minutes'] * 60 + 300; // Add 5 minute buffer
        
        Cache::put($cacheKey, [
            'token' => $token,
            'session_data' => $sessionData,
            'last_activity' => now(),
        ], $duration);
    }

    /**
     * Validate candidate credentials
     */
    private function validateCandidateCredentials(EntranceExamRegistration $registration, array $credentials): bool
    {
        // Validate hall ticket number
        if (isset($credentials['hall_ticket'])) {
            if ($registration->hall_ticket_number !== $credentials['hall_ticket']) {
                return false;
            }
        }
        
        // Validate date of birth
        if (isset($credentials['date_of_birth'])) {
            $application = $registration->application;
            if ($application && $application->date_of_birth !== $credentials['date_of_birth']) {
                return false;
            }
        }
        
        // Additional validation can be added here
        
        return true;
    }

    /**
     * Check for active exam session
     */
    private function checkActiveSession(int $registrationId): ?array
    {
        $cacheKey = "exam_session_{$registrationId}";
        return Cache::get($cacheKey);
    }

    /**
     * Generate authentication token
     */
    private function generateAuthToken(EntranceExamRegistration $registration): string
    {
        return hash('sha256', $registration->id . now()->timestamp . Str::random(32));
    }

    /**
     * Store authentication in cache
     */
    private function storeAuthentication(int $registrationId, string $token): void
    {
        $cacheKey = "exam_auth_{$registrationId}";
        Cache::put($cacheKey, [
            'token' => $token,
            'authenticated_at' => now(),
            'ip_address' => request()->ip(),
        ], 3600); // 1 hour
    }

    /**
     * Load questions from paper
     */
    private function loadQuestions(ExamQuestionPaper $paper): array
    {
        $questionIds = $paper->questions_order;
        $questions = ExamQuestion::whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');
        
        $orderedQuestions = [];
        foreach ($questionIds as $index => $questionId) {
            if (isset($questions[$questionId])) {
                $question = $questions[$questionId];
                $orderedQuestions[] = [
                    'question_number' => $index + 1,
                    'question_id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'options' => $question->options,
                    'marks' => $question->marks,
                    'negative_marks' => $question->negative_marks,
                    'time_limit' => $question->time_limit_seconds,
                    'has_image' => !empty($question->question_image),
                    'has_audio' => !empty($question->question_audio),
                    'has_video' => !empty($question->question_video),
                ];
            }
        }
        
        return $orderedQuestions;
    }

    /**
     * Initialize response details for questions
     */
    private function initializeResponseDetails(ExamResponse $response, array $questions): void
    {
        foreach ($questions as $index => $question) {
            ExamResponseDetail::firstOrCreate(
                [
                    'response_id' => $response->id,
                    'question_id' => $question['question_id'],
                ],
                [
                    'question_number' => $index + 1,
                    'status' => 'not_visited',
                ]
            );
        }
    }

    /**
     * Get exam progress
     */
    private function getExamProgress(ExamResponse $response): array
    {
        $details = ExamResponseDetail::where('response_id', $response->id)->get();
        
        $progress = [
            'total' => $details->count(),
            'visited' => 0,
            'answered' => 0,
            'not_answered' => 0,
            'marked_review' => 0,
            'answered_marked_review' => 0,
        ];
        
        foreach ($details as $detail) {
            if ($detail->status !== 'not_visited') {
                $progress['visited']++;
            }
            
            switch ($detail->status) {
                case 'answered':
                    $progress['answered']++;
                    break;
                case 'not_answered':
                    $progress['not_answered']++;
                    break;
                case 'marked_review':
                    $progress['marked_review']++;
                    break;
                case 'answered_marked_review':
                    $progress['answered_marked_review']++;
                    break;
            }
        }
        
        return $progress;
    }

    /**
     * Calculate remaining time
     */
    private function calculateRemainingTime(ExamResponse $response): int
    {
        if ($response->timer_paused) {
            return $response->remaining_time_seconds;
        }
        
        $exam = $response->registration->exam;
        $totalSeconds = $exam->duration_minutes * 60;
        
        if ($response->exam_started_at) {
            $elapsedSeconds = now()->diffInSeconds($response->exam_started_at);
            $extensionSeconds = ($response->time_extension_minutes ?? 0) * 60;
            $remainingSeconds = $totalSeconds + $extensionSeconds - $elapsedSeconds;
            
            return max(0, $remainingSeconds);
        }
        
        return $totalSeconds;
    }

    /**
     * Check if exam has expired
     */
    private function hasExamExpired(ExamResponse $response): bool
    {
        return $this->calculateRemainingTime($response) <= 0;
    }

    /**
     * Get question number from paper
     */
    private function getQuestionNumber(int $paperId, int $questionId): int
    {
        $paper = ExamQuestionPaper::find($paperId);
        if ($paper) {
            $position = array_search($questionId, $paper->questions_order);
            return $position !== false ? $position + 1 : 1;
        }
        return 1;
    }

    /**
     * Get current question from session
     */
    private function getCurrentQuestion(int $responseId): ?int
    {
        $cacheKey = "exam_current_question_{$responseId}";
        return Cache::get($cacheKey);
    }

    /**
     * Set current question in session
     */
    private function setCurrentQuestion(int $responseId, int $questionId): void
    {
        $cacheKey = "exam_current_question_{$responseId}";
        Cache::put($cacheKey, $questionId, 3600);
    }

    /**
     * Track question navigation
     */
    private function trackQuestionNavigation(int $responseId, ?int $fromQuestion, int $toQuestion): void
    {
        $navigationKey = "exam_navigation_{$responseId}";
        $navigation = Cache::get($navigationKey, []);
        
        $navigation[] = [
            'from' => $fromQuestion,
            'to' => $toQuestion,
            'timestamp' => now()->timestamp,
        ];
        
        Cache::put($navigationKey, $navigation, 3600);
    }

    /**
     * Get review statistics
     */
    private function getReviewStatistics(int $responseId): array
    {
        $details = ExamResponseDetail::where('response_id', $responseId)
            ->whereIn('status', ['marked_review', 'answered_marked_review'])
            ->get();
        
        return [
            'total_marked' => $details->count(),
            'marked_answered' => $details->where('status', 'answered_marked_review')->count(),
            'marked_unanswered' => $details->where('status', 'marked_review')->count(),
        ];
    }

    /**
     * Get submission statistics
     */
    private function getSubmissionStatistics(ExamResponse $response): array
    {
        $details = $response->responseDetails;
        
        return [
            'total_questions' => $details->count(),
            'answered' => $details->whereIn('status', ['answered', 'answered_marked_review'])->count(),
            'unanswered' => $details->whereIn('status', ['not_visited', 'not_answered', 'marked_review'])->count(),
            'marked_for_review' => $details->whereIn('status', ['marked_review', 'answered_marked_review'])->count(),
            'time_spent_seconds' => $this->calculateTimeSpent($response),
        ];
    }

    /**
     * Calculate time spent on exam
     */
    private function calculateTimeSpent(ExamResponse $response): int
    {
        if ($response->exam_started_at) {
            $endTime = $response->exam_submitted_at ?? now();
            return $endTime->diffInSeconds($response->exam_started_at);
        }
        return 0;
    }

    /**
     * Lock response details
     */
    private function lockResponseDetails(ExamResponse $response): void
    {
        ExamResponseDetail::where('response_id', $response->id)
            ->update(['is_locked' => true]);
    }

    /**
     * Clear exam session from cache
     */
    private function clearExamSession(int $registrationId): void
    {
        Cache::forget("exam_session_{$registrationId}");
        Cache::forget("exam_auth_{$registrationId}");
        Cache::forget("exam_current_question_{$registrationId}");
        Cache::forget("exam_navigation_{$registrationId}");
    }

    /**
     * Generate submission receipt
     */
    private function generateSubmissionReceipt(ExamResponse $response): string
    {
        $receiptNumber = 'EXM' . date('Ymd') . str_pad($response->id, 6, '0', STR_PAD_LEFT);
        
        // Store receipt in database or cache
        Cache::put("exam_receipt_{$response->id}", [
            'receipt_number' => $receiptNumber,
            'submitted_at' => now(),
            'registration_id' => $response->registration_id,
        ], 86400 * 30); // Keep for 30 days
        
        return $receiptNumber;
    }

    /**
     * Save exam state
     */
    private function saveExamState(ExamResponse $response): array
    {
        $state = [
            'response_id' => $response->id,
            'current_question' => $this->getCurrentQuestion($response->id),
            'remaining_time' => $this->calculateRemainingTime($response),
            'progress' => $this->getExamProgress($response),
            'saved_at' => now(),
        ];
        
        $cacheKey = "exam_state_{$response->id}";
        Cache::put($cacheKey, $state, 3600);
        
        return $state;
    }

    /**
     * Generate recovery token
     */
    private function generateRecoveryToken(ExamResponse $response): string
    {
        $token = Str::random(64);
        
        Cache::put("exam_recovery_{$token}", [
            'response_id' => $response->id,
            'created_at' => now(),
        ], 1800); // Valid for 30 minutes
        
        return $token;
    }

    /**
     * Restore exam state
     */
    private function restoreExamState(ExamResponse $response): array
    {
        $cacheKey = "exam_state_{$response->id}";
        $state = Cache::get($cacheKey, []);
        
        if (empty($state)) {
            // Rebuild state from database
            $state = [
                'response_id' => $response->id,
                'current_question' => null,
                'remaining_time' => $this->calculateRemainingTime($response),
                'progress' => $this->getExamProgress($response),
                'restored_at' => now(),
            ];
        }
        
        return $state;
    }

    /**
     * Logging methods
     */
    private function logExamEvent(ExamResponse $response, string $event, array $data = []): void
    {
        Log::info("Exam event: {$event}", array_merge([
            'response_id' => $response->id,
            'registration_id' => $response->registration_id,
        ], $data));
    }

    private function logFailedAuthentication(EntranceExamRegistration $registration): void
    {
        Log::warning('Failed authentication attempt', [
            'registration_id' => $registration->id,
            'ip_address' => request()->ip(),
        ]);
    }

    private function logQuestionResponse(ExamResponseDetail $detail, $previousAnswer): void
    {
        if ($previousAnswer !== $detail->answer) {
            Log::info('Question response updated', [
                'response_detail_id' => $detail->id,
                'question_id' => $detail->question_id,
                'changed_from' => $previousAnswer ? 'answered' : 'unanswered',
                'changed_to' => $detail->answer ? 'answered' : 'unanswered',
            ]);
        }
    }
}