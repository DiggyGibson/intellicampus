<?php

namespace App\Services;

use App\Models\ExamSession;
use App\Models\ExamSeatAllocation;
use App\Models\EntranceExamRegistration;
use App\Models\ExamQuestionPaper;
use App\Models\ExamResponse;
use App\Models\ExamCenter;
use App\Models\EntranceExam;
use App\Models\User;
use App\Models\ApplicationCommunication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ExamConductService
{
    /**
     * Session states
     */
    private const SESSION_STATES = [
        'scheduled' => 'Scheduled',
        'registration_open' => 'Registration Open',
        'registration_closed' => 'Registration Closed',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'postponed' => 'Postponed',
    ];

    /**
     * Technical issue priorities
     */
    private const ISSUE_PRIORITIES = [
        'critical' => 1,
        'high' => 2,
        'medium' => 3,
        'low' => 4,
    ];

    /**
     * Start an exam session
     *
     * @param int $sessionId
     * @return ExamSession
     * @throws Exception
     */
    public function startExamSession(int $sessionId): ExamSession
    {
        DB::beginTransaction();

        try {
            $session = ExamSession::with(['exam', 'center'])->findOrFail($sessionId);

            // Validate session can be started
            if ($session->status !== 'registration_closed') {
                throw new Exception("Session cannot be started. Current status: {$session->status}");
            }

            // Check if it's time to start
            $now = Carbon::now();
            $sessionStartTime = Carbon::parse($session->session_date . ' ' . $session->start_time);
            
            // Allow starting 15 minutes before scheduled time
            if ($now->lt($sessionStartTime->subMinutes(15))) {
                throw new Exception("Session cannot be started yet. Scheduled time: {$sessionStartTime->format('Y-m-d H:i')}");
            }

            // Update session status
            $session->status = 'in_progress';
            $session->actual_start_time = $now;
            $session->save();

            // Initialize all registered candidates' exam responses
            $this->initializeCandidateResponses($session);

            // Generate and distribute question papers
            $this->distributeQuestionPapers($session);

            // Send notifications to proctors
            $this->notifyProctors($session, 'Exam session has started');

            // Log session start
            $this->logSessionEvent($session, 'session_started', [
                'scheduled_time' => $sessionStartTime->format('Y-m-d H:i'),
                'actual_time' => $now->format('Y-m-d H:i'),
                'candidates_present' => $this->getPresentCandidatesCount($sessionId),
            ]);

            // Start monitoring
            $this->startSessionMonitoring($session);

            DB::commit();

            Log::info('Exam session started', [
                'session_id' => $sessionId,
                'center_id' => $session->center_id,
                'candidates' => $session->registered_count,
            ]);

            return $session->fresh(['exam', 'center', 'allocations']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to start exam session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Mark attendance for a candidate
     *
     * @param int $registrationId
     * @param bool $present
     * @param array $biometricData
     * @return ExamSeatAllocation
     * @throws Exception
     */
    public function markAttendance(int $registrationId, bool $present, array $biometricData = []): ExamSeatAllocation
    {
        DB::beginTransaction();

        try {
            $allocation = ExamSeatAllocation::where('registration_id', $registrationId)
                ->with(['registration', 'session'])
                ->firstOrFail();

            // Check if session is active
            if (!in_array($allocation->session->status, ['registration_closed', 'in_progress'])) {
                throw new Exception("Attendance cannot be marked. Session status: {$allocation->session->status}");
            }

            // Check if already marked
            if ($allocation->attendance_marked) {
                throw new Exception("Attendance already marked for this candidate");
            }

            // Mark attendance
            $allocation->attendance_marked = true;
            $allocation->check_in_time = $present ? now() : null;
            $allocation->marked_by = auth()->id();
            
            // Store biometric data if provided
            if (!empty($biometricData)) {
                $allocation->biometric_verification = $this->verifyBiometrics($biometricData);
                $allocation->biometric_data = encrypt(json_encode($biometricData));
            }

            $allocation->save();

            // Update registration status
            $registration = $allocation->registration;
            $registration->attendance_status = $present ? 'present' : 'absent';
            $registration->save();

            // If absent, update exam response
            if (!$present) {
                $this->markCandidateAbsent($registrationId);
            } else {
                // Enable exam access for present candidates
                $this->enableExamAccess($registrationId);
            }

            // Log attendance
            $this->logAttendance($allocation, $present);

            DB::commit();

            Log::info('Attendance marked', [
                'registration_id' => $registrationId,
                'present' => $present,
                'session_id' => $allocation->session_id,
            ]);

            return $allocation->fresh(['registration', 'session']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark attendance', [
                'registration_id' => $registrationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Distribute question papers to a session
     *
     * @param int $sessionId
     * @param int|null $paperId
     * @return array
     * @throws Exception
     */
    public function distributeQuestionPaper(int $sessionId, ?int $paperId = null): array
    {
        DB::beginTransaction();

        try {
            $session = ExamSession::with(['exam', 'allocations.registration'])->findOrFail($sessionId);

            // Validate session status
            if ($session->status !== 'in_progress') {
                throw new Exception("Question papers can only be distributed to active sessions");
            }

            // Get or generate question paper
            if ($paperId) {
                $paper = ExamQuestionPaper::findOrFail($paperId);
            } else {
                $paper = $this->generateQuestionPaper($session->exam_id);
            }

            // Lock the paper
            $paper->is_locked = true;
            $paper->locked_at = now();
            $paper->save();

            // Distribute to all present candidates
            $distributions = [];
            $presentAllocations = $session->allocations
                ->where('attendance_marked', true)
                ->where('check_in_time', '!=', null);

            foreach ($presentAllocations as $allocation) {
                // Assign paper to candidate's response
                $response = ExamResponse::where('registration_id', $allocation->registration_id)
                    ->where('session_id', $sessionId)
                    ->first();

                if ($response) {
                    $response->paper_id = $paper->id;
                    $response->save();
                    
                    $distributions[] = [
                        'registration_id' => $allocation->registration_id,
                        'seat_number' => $allocation->seat_number,
                        'paper_id' => $paper->id,
                        'distributed_at' => now(),
                    ];
                }
            }

            // Log distribution
            $this->logSessionEvent($session, 'papers_distributed', [
                'paper_id' => $paper->id,
                'paper_code' => $paper->paper_code,
                'candidates_count' => count($distributions),
            ]);

            DB::commit();

            Log::info('Question papers distributed', [
                'session_id' => $sessionId,
                'paper_id' => $paper->id,
                'distributions' => count($distributions),
            ]);

            return [
                'status' => 'success',
                'paper_id' => $paper->id,
                'paper_code' => $paper->paper_code,
                'distributed_to' => count($distributions),
                'distributions' => $distributions,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to distribute question papers', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle technical issue during exam
     *
     * @param int $registrationId
     * @param array $issueData
     * @return array
     * @throws Exception
     */
    public function handleTechnicalIssue(int $registrationId, array $issueData): array
    {
        DB::beginTransaction();

        try {
            $registration = EntranceExamRegistration::with(['exam', 'seatAllocation.session'])
                ->findOrFail($registrationId);

            $session = $registration->seatAllocation->session ?? null;
            
            if (!$session || $session->status !== 'in_progress') {
                throw new Exception("Technical issues can only be reported during active exam sessions");
            }

            // Categorize issue
            $priority = $this->categorizeIssuePriority($issueData['type'] ?? 'other');
            
            // Create issue record
            $issue = [
                'registration_id' => $registrationId,
                'session_id' => $session->id,
                'issue_type' => $issueData['type'],
                'description' => $issueData['description'],
                'priority' => $priority,
                'reported_at' => now(),
                'reported_by' => auth()->id(),
                'status' => 'reported',
            ];

            // Store issue
            DB::table('exam_technical_issues')->insert($issue);
            $issueId = DB::getPdo()->lastInsertId();

            // Take immediate action based on priority
            $action = $this->takeImmediateAction($registrationId, $issueData['type'], $priority);

            // Notify technical support
            if ($priority <= 2) { // Critical or High
                $this->notifyTechnicalSupport($issue);
            }

            // Log the issue
            $this->logTechnicalIssue($registration, $issueData, $action);

            DB::commit();

            Log::info('Technical issue handled', [
                'registration_id' => $registrationId,
                'issue_type' => $issueData['type'],
                'priority' => $priority,
                'action' => $action,
            ]);

            return [
                'issue_id' => $issueId,
                'priority' => self::ISSUE_PRIORITIES[$priority] ?? 'unknown',
                'action_taken' => $action,
                'status' => 'handling',
                'estimated_resolution' => $this->estimateResolutionTime($priority),
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle technical issue', [
                'registration_id' => $registrationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Extend exam time for a candidate
     *
     * @param int $registrationId
     * @param int $minutes
     * @param string $reason
     * @return ExamResponse
     * @throws Exception
     */
    public function extendTime(int $registrationId, int $minutes, string $reason): ExamResponse
    {
        DB::beginTransaction();

        try {
            // Validate authorization
            if (!$this->canExtendTime(auth()->user())) {
                throw new Exception("You are not authorized to extend exam time");
            }

            $response = ExamResponse::where('registration_id', $registrationId)
                ->with(['registration', 'session'])
                ->firstOrFail();

            // Check if exam is in progress
            if ($response->status !== 'in_progress') {
                throw new Exception("Time can only be extended for ongoing exams");
            }

            // Check maximum extension limit (e.g., 30 minutes)
            $currentExtension = $response->time_extension_minutes ?? 0;
            if ($currentExtension + $minutes > 30) {
                throw new Exception("Maximum time extension limit (30 minutes) exceeded");
            }

            // Update time extension
            $response->time_extension_minutes = $currentExtension + $minutes;
            $response->time_extension_reason = $reason;
            $response->time_extended_by = auth()->id();
            $response->save();

            // Log extension
            $this->logTimeExtension($response, $minutes, $reason);

            // Notify candidate
            $this->notifyTimeExtension($response->registration, $minutes, $reason);

            DB::commit();

            Log::info('Exam time extended', [
                'registration_id' => $registrationId,
                'minutes' => $minutes,
                'reason' => $reason,
            ]);

            return $response->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to extend exam time', [
                'registration_id' => $registrationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Terminate exam for a candidate
     *
     * @param int $registrationId
     * @param string $reason
     * @return ExamResponse
     * @throws Exception
     */
    public function terminateExam(int $registrationId, string $reason): ExamResponse
    {
        DB::beginTransaction();

        try {
            // Validate authorization
            if (!$this->canTerminateExam(auth()->user())) {
                throw new Exception("You are not authorized to terminate exams");
            }

            $response = ExamResponse::where('registration_id', $registrationId)
                ->with(['registration'])
                ->firstOrFail();

            // Check if exam is in progress
            if ($response->status !== 'in_progress') {
                throw new Exception("Only ongoing exams can be terminated");
            }

            // Update response status
            $previousStatus = $response->status;
            $response->status = 'terminated';
            $response->exam_submitted_at = now();
            $response->termination_reason = $reason;
            $response->terminated_by = auth()->id();
            $response->save();

            // Calculate score for attempted questions
            $this->calculatePartialScore($response);

            // Update seat allocation
            $allocation = ExamSeatAllocation::where('registration_id', $registrationId)->first();
            if ($allocation) {
                $allocation->check_out_time = now();
                $allocation->save();
            }

            // Log termination
            $this->logExamTermination($response, $reason);

            // Send notification
            $this->sendTerminationNotification($response->registration, $reason);

            DB::commit();

            Log::info('Exam terminated', [
                'registration_id' => $registrationId,
                'reason' => $reason,
            ]);

            return $response->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to terminate exam', [
                'registration_id' => $registrationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Collect responses from a session
     *
     * @param int $sessionId
     * @return array
     * @throws Exception
     */
    public function collectResponses(int $sessionId): array
    {
        DB::beginTransaction();

        try {
            $session = ExamSession::with(['exam'])->findOrFail($sessionId);

            // Check if session is ending or has ended
            $sessionEndTime = Carbon::parse($session->session_date . ' ' . $session->end_time);
            if (Carbon::now()->lt($sessionEndTime->subMinutes(5)) && $session->status === 'in_progress') {
                throw new Exception("Cannot collect responses. Session is still in progress.");
            }

            // Get all responses for the session
            $responses = ExamResponse::where('session_id', $sessionId)->get();
            
            $collectionStats = [
                'total' => $responses->count(),
                'submitted' => 0,
                'auto_submitted' => 0,
                'not_started' => 0,
                'terminated' => 0,
            ];

            foreach ($responses as $response) {
                // Auto-submit responses that are still in progress
                if ($response->status === 'in_progress') {
                    $response->status = 'auto_submitted';
                    $response->exam_submitted_at = now();
                    $response->save();
                    $collectionStats['auto_submitted']++;
                } elseif ($response->status === 'submitted') {
                    $collectionStats['submitted']++;
                } elseif ($response->status === 'not_started') {
                    $collectionStats['not_started']++;
                } elseif ($response->status === 'terminated') {
                    $collectionStats['terminated']++;
                }

                // Lock the response to prevent further changes
                $this->lockResponse($response);
            }

            // Update session status
            if ($session->status === 'in_progress') {
                $session->status = 'completed';
                $session->actual_end_time = now();
                $session->save();
            }

            // Generate session report
            $reportPath = $this->generateSessionReport($session, $collectionStats);

            // Archive session data
            $this->archiveSessionData($session);

            DB::commit();

            Log::info('Responses collected', [
                'session_id' => $sessionId,
                'stats' => $collectionStats,
            ]);

            return [
                'status' => 'success',
                'session_id' => $sessionId,
                'collection_stats' => $collectionStats,
                'report_path' => $reportPath,
                'collected_at' => now()->format('Y-m-d H:i:s'),
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to collect responses', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate attendance report for a session
     *
     * @param int $sessionId
     * @return string
     * @throws Exception
     */
    public function generateAttendanceReport(int $sessionId): string
    {
        $session = ExamSession::with([
            'exam',
            'center',
            'allocations.registration.application'
        ])->findOrFail($sessionId);

        $attendanceData = [];
        $statistics = [
            'total_registered' => 0,
            'present' => 0,
            'absent' => 0,
            'percentage' => 0,
        ];

        foreach ($session->allocations as $allocation) {
            $registration = $allocation->registration;
            $application = $registration->application ?? null;
            
            $attendanceData[] = [
                'registration_number' => $registration->registration_number,
                'hall_ticket' => $registration->hall_ticket_number,
                'candidate_name' => $application ? 
                    $application->first_name . ' ' . $application->last_name : 
                    $registration->candidate_name,
                'seat_number' => $allocation->seat_number,
                'room_number' => $allocation->room_number,
                'attendance' => $allocation->attendance_marked ? 
                    ($allocation->check_in_time ? 'Present' : 'Absent') : 
                    'Not Marked',
                'check_in_time' => $allocation->check_in_time?->format('H:i:s'),
                'check_out_time' => $allocation->check_out_time?->format('H:i:s'),
                'marked_by' => $allocation->marked_by ? 
                    User::find($allocation->marked_by)->name : 
                    null,
            ];

            $statistics['total_registered']++;
            if ($allocation->attendance_marked && $allocation->check_in_time) {
                $statistics['present']++;
            } elseif ($allocation->attendance_marked && !$allocation->check_in_time) {
                $statistics['absent']++;
            }
        }

        $statistics['percentage'] = $statistics['total_registered'] > 0 ? 
            round(($statistics['present'] / $statistics['total_registered']) * 100, 2) : 0;

        // Generate PDF report
        $data = [
            'session' => $session,
            'exam' => $session->exam,
            'center' => $session->center,
            'attendance' => $attendanceData,
            'statistics' => $statistics,
            'generated_at' => now(),
            'generated_by' => auth()->user()->name,
        ];

        $pdf = PDF::loadView('exams.attendance-report', $data);
        
        $filename = "attendance_report_session_{$session->session_code}_{$session->id}.pdf";
        $path = "exams/reports/attendance/{$filename}";
        
        Storage::put($path, $pdf->output());

        Log::info('Attendance report generated', [
            'session_id' => $sessionId,
            'path' => $path,
            'statistics' => $statistics,
        ]);

        return $path;
    }

    /**
     * Private helper methods
     */

    /**
     * Initialize candidate responses for a session
     */
    private function initializeCandidateResponses(ExamSession $session): void
    {
        $allocations = ExamSeatAllocation::where('session_id', $session->id)->get();
        
        foreach ($allocations as $allocation) {
            ExamResponse::firstOrCreate(
                [
                    'registration_id' => $allocation->registration_id,
                    'session_id' => $session->id,
                ],
                [
                    'paper_id' => null, // Will be assigned when papers are distributed
                    'status' => 'not_started',
                    'exam_started_at' => null,
                    'remaining_time_seconds' => $session->exam->duration_minutes * 60,
                ]
            );
        }
    }

    /**
     * Distribute question papers to candidates
     */
    private function distributeQuestionPapers(ExamSession $session): void
    {
        // This would integrate with the question paper generation service
        // For now, we'll use existing papers or generate new ones
        
        $exam = $session->exam;
        
        // Check if papers are already generated for this session
        $papers = ExamQuestionPaper::where('exam_id', $exam->id)
            ->where('session_id', $session->id)
            ->get();
        
        if ($papers->isEmpty()) {
            // Generate papers if not exists
            $this->generateQuestionPapersForSession($session);
        }
    }

    /**
     * Generate question papers for a session
     */
    private function generateQuestionPapersForSession(ExamSession $session): void
    {
        // Generate different sets (A, B, C, D) for the session
        $sets = ['A', 'B', 'C', 'D'];
        
        foreach ($sets as $set) {
            $paper = ExamQuestionPaper::create([
                'exam_id' => $session->exam_id,
                'session_id' => $session->id,
                'paper_code' => $session->exam->exam_code . '_' . $session->session_code . '_' . $set,
                'paper_set' => $set,
                'generation_method' => 'random',
                'questions_order' => $this->generateRandomQuestionOrder($session->exam_id),
                'total_questions' => $session->exam->total_questions,
                'total_marks' => $session->exam->total_marks,
                'created_by' => auth()->id(),
                'is_locked' => false,
            ]);
        }
    }

    /**
     * Generate random question order
     */
    private function generateRandomQuestionOrder(int $examId): array
    {
        // Get questions for the exam
        $questions = DB::table('exam_questions')
            ->where('exam_id', $examId)
            ->pluck('id')
            ->toArray();
        
        // Shuffle questions
        shuffle($questions);
        
        return $questions;
    }

    /**
     * Generate question paper
     */
    private function generateQuestionPaper(int $examId): ExamQuestionPaper
    {
        // This would be more complex in production
        return ExamQuestionPaper::where('exam_id', $examId)
            ->where('is_locked', false)
            ->firstOrFail();
    }

    /**
     * Get count of present candidates
     */
    private function getPresentCandidatesCount(int $sessionId): int
    {
        return ExamSeatAllocation::where('session_id', $sessionId)
            ->where('attendance_marked', true)
            ->whereNotNull('check_in_time')
            ->count();
    }

    /**
     * Notify proctors about session events
     */
    private function notifyProctors(ExamSession $session, string $message): void
    {
        $proctorIds = $session->proctor_assignments ?? [];
        
        foreach ($proctorIds as $proctorId) {
            // Send notification to proctor
            // This would integrate with notification service
            Log::info('Proctor notified', [
                'proctor_id' => $proctorId,
                'session_id' => $session->id,
                'message' => $message,
            ]);
        }
    }

    /**
     * Start monitoring for the session
     */
    private function startSessionMonitoring(ExamSession $session): void
    {
        // Set up monitoring cache keys
        $monitoringKey = "exam_session_monitoring_{$session->id}";
        
        Cache::put($monitoringKey, [
            'session_id' => $session->id,
            'started_at' => now(),
            'status' => 'active',
            'candidates_present' => $this->getPresentCandidatesCount($session->id),
            'issues_reported' => 0,
        ], $session->exam->duration_minutes * 60);
    }

    /**
     * Verify biometric data
     */
    private function verifyBiometrics(array $biometricData): bool
    {
        // This would integrate with biometric verification system
        // For now, return true if data is provided
        return !empty($biometricData);
    }

    /**
     * Mark candidate as absent in exam response
     */
    private function markCandidateAbsent(int $registrationId): void
    {
        $response = ExamResponse::where('registration_id', $registrationId)->first();
        if ($response) {
            $response->status = 'absent';
            $response->save();
        }
    }

    /**
     * Enable exam access for present candidate
     */
    private function enableExamAccess(int $registrationId): void
    {
        $response = ExamResponse::where('registration_id', $registrationId)->first();
        if ($response && $response->status === 'not_started') {
            $response->access_enabled = true;
            $response->save();
        }
    }

    /**
     * Categorize issue priority
     */
    private function categorizeIssuePriority(string $issueType): string
    {
        $priorities = [
            'system_crash' => 'critical',
            'cannot_login' => 'critical',
            'questions_not_loading' => 'critical',
            'power_failure' => 'high',
            'network_issue' => 'high',
            'computer_malfunction' => 'high',
            'slow_response' => 'medium',
            'display_issue' => 'medium',
            'other' => 'low',
        ];

        return $priorities[$issueType] ?? 'medium';
    }

    /**
     * Take immediate action based on issue
     */
    private function takeImmediateAction(int $registrationId, string $issueType, string $priority): string
    {
        $action = 'Issue logged and assigned to support team';

        if ($priority === 'critical') {
            // Pause exam timer
            $this->pauseExamTimer($registrationId);
            $action = 'Exam timer paused. Technical support notified immediately.';
        } elseif ($priority === 'high') {
            // Extend time automatically
            $this->extendTime($registrationId, 10, 'Technical issue compensation');
            $action = '10 minutes added to exam time. Support team notified.';
        }

        return $action;
    }

    /**
     * Pause exam timer
     */
    private function pauseExamTimer(int $registrationId): void
    {
        $response = ExamResponse::where('registration_id', $registrationId)->first();
        if ($response) {
            $response->timer_paused = true;
            $response->timer_paused_at = now();
            $response->save();
        }
    }

    /**
     * Estimate resolution time based on priority
     */
    private function estimateResolutionTime(string $priority): string
    {
        $estimates = [
            'critical' => '5-10 minutes',
            'high' => '10-15 minutes',
            'medium' => '15-30 minutes',
            'low' => '30-60 minutes',
        ];

        return $estimates[$priority] ?? 'Under review';
    }

    /**
     * Notify technical support
     */
    private function notifyTechnicalSupport(array $issue): void
    {
        // This would send urgent notification to technical team
        Log::alert('Technical support needed', $issue);
    }

    /**
     * Check if user can extend time
     */
    private function canExtendTime(User $user): bool
    {
        $allowedRoles = ['exam_coordinator', 'center_supervisor', 'exam_administrator'];
        return $user->hasAnyRole($allowedRoles);
    }

    /**
     * Check if user can terminate exam
     */
    private function canTerminateExam(User $user): bool
    {
        $allowedRoles = ['exam_coordinator', 'center_supervisor', 'exam_administrator'];
        return $user->hasAnyRole($allowedRoles);
    }

    /**
     * Calculate partial score for terminated exam
     */
    private function calculatePartialScore(ExamResponse $response): void
    {
        // This would calculate score for attempted questions
        // Implementation depends on exam evaluation service
    }

    /**
     * Lock response to prevent changes
     */
    private function lockResponse(ExamResponse $response): void
    {
        $response->is_locked = true;
        $response->locked_at = now();
        $response->save();
    }

    /**
     * Generate session report
     */
    private function generateSessionReport(ExamSession $session, array $stats): string
    {
        $data = [
            'session' => $session,
            'statistics' => $stats,
            'generated_at' => now(),
        ];

        $pdf = PDF::loadView('exams.session-report', $data);
        $filename = "session_report_{$session->session_code}.pdf";
        $path = "exams/reports/sessions/{$filename}";
        
        Storage::put($path, $pdf->output());
        
        return $path;
    }

    /**
     * Archive session data
     */
    private function archiveSessionData(ExamSession $session): void
    {
        // Archive responses and attendance data
        $archivePath = "archives/exams/sessions/{$session->id}";
        
        // Create archive manifest
        $manifest = [
            'session_id' => $session->id,
            'session_code' => $session->session_code,
            'archived_at' => now()->toIso8601String(),
            'total_candidates' => $session->registered_count,
        ];
        
        Storage::put("{$archivePath}/manifest.json", json_encode($manifest));
    }

    /**
     * Logging methods
     */
    private function logSessionEvent(ExamSession $session, string $event, array $data = []): void
    {
        Log::info("Session event: {$event}", array_merge([
            'session_id' => $session->id,
            'exam_id' => $session->exam_id,
            'center_id' => $session->center_id,
        ], $data));
    }

    private function logAttendance(ExamSeatAllocation $allocation, bool $present): void
    {
        Log::info('Attendance logged', [
            'allocation_id' => $allocation->id,
            'registration_id' => $allocation->registration_id,
            'present' => $present,
            'marked_by' => auth()->id(),
        ]);
    }

    private function logTechnicalIssue($registration, array $issueData, string $action): void
    {
        Log::warning('Technical issue reported', [
            'registration_id' => $registration->id,
            'issue' => $issueData,
            'action' => $action,
        ]);
    }

    private function logTimeExtension(ExamResponse $response, int $minutes, string $reason): void
    {
        Log::info('Time extended', [
            'response_id' => $response->id,
            'minutes' => $minutes,
            'reason' => $reason,
            'extended_by' => auth()->id(),
        ]);
    }

    private function logExamTermination(ExamResponse $response, string $reason): void
    {
        Log::warning('Exam terminated', [
            'response_id' => $response->id,
            'reason' => $reason,
            'terminated_by' => auth()->id(),
        ]);
    }

    /**
     * Notification methods
     */
    private function notifyTimeExtension($registration, int $minutes, string $reason): void
    {
        // Send notification to candidate about time extension
    }

    private function sendTerminationNotification($registration, string $reason): void
    {
        // Send notification about exam termination
    }
}