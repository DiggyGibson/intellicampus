<?php

namespace App\Services;

use App\Models\EntranceExam;
use App\Models\EntranceExamRegistration;
use App\Models\ExamCenter;
use App\Models\ExamSession;
use App\Models\ExamSeatAllocation;
use App\Models\AdmissionApplication;
use App\Models\ApplicationCommunication;
use App\Models\ApplicationFee;
use App\Models\AcademicTerm;
use App\Models\AcademicProgram;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Exception;

class EntranceExamService
{
    /**
     * Exam configuration constants
     */
    private const EXAM_FEES = [
        'entrance' => 75.00,
        'placement' => 50.00,
        'diagnostic' => 25.00,
        'scholarship' => 0.00,
        'transfer_credit' => 100.00,
        'exemption' => 50.00,
    ];

    private const MIN_REGISTRATION_DAYS = 7; // Minimum days before exam to register
    private const HALL_TICKET_AVAILABLE_DAYS = 3; // Days before exam when hall ticket is available
    private const MAX_ATTEMPTS_ALLOWED = 3;
    private const SEAT_ALLOCATION_BUFFER = 0.1; // 10% buffer for no-shows

    /**
     * Create a new entrance exam
     *
     * @param array $examData
     * @return EntranceExam
     * @throws Exception
     */
    public function createExam(array $examData): EntranceExam
    {
        DB::beginTransaction();

        try {
            // Validate exam data
            $this->validateExamData($examData);

            // Generate exam code
            $examCode = $this->generateExamCode($examData['exam_type'] ?? 'entrance');

            // Create exam record
            $exam = new EntranceExam();
            $exam->exam_code = $examCode;
            $exam->exam_name = $examData['exam_name'];
            $exam->description = $examData['description'] ?? null;
            $exam->exam_type = $examData['exam_type'] ?? 'entrance';
            $exam->delivery_mode = $examData['delivery_mode'];
            $exam->term_id = $examData['term_id'];
            
            // Set applicable programs
            $exam->applicable_programs = $examData['applicable_programs'] ?? null;
            $exam->applicable_application_types = $examData['applicable_application_types'] ?? null;
            
            // Exam structure
            $exam->total_marks = $examData['total_marks'];
            $exam->passing_marks = $examData['passing_marks'];
            $exam->duration_minutes = $examData['duration_minutes'];
            $exam->exam_start_time = $examData['exam_start_time'] ?? null;
            $exam->exam_end_time = $examData['exam_end_time'] ?? null;
            
            // Question configuration
            $exam->total_questions = $examData['total_questions'];
            $exam->sections = $examData['sections'] ?? null;
            
            // Instructions and rules
            $exam->general_instructions = $examData['general_instructions'] ?? $this->getDefaultInstructions();
            $exam->exam_rules = $examData['exam_rules'] ?? $this->getDefaultRules();
            $exam->allowed_materials = $examData['allowed_materials'] ?? [];
            $exam->negative_marking = $examData['negative_marking'] ?? false;
            $exam->negative_mark_value = $examData['negative_mark_value'] ?? 0.25;
            
            // Schedule
            $exam->registration_start_date = $examData['registration_start_date'];
            $exam->registration_end_date = $examData['registration_end_date'];
            $exam->exam_date = $examData['exam_date'] ?? null;
            $exam->exam_window_start = $examData['exam_window_start'] ?? null;
            $exam->exam_window_end = $examData['exam_window_end'] ?? null;
            
            // Results
            $exam->result_publish_date = $examData['result_publish_date'] ?? null;
            $exam->show_detailed_results = $examData['show_detailed_results'] ?? false;
            $exam->allow_result_review = $examData['allow_result_review'] ?? false;
            $exam->review_period_days = $examData['review_period_days'] ?? 7;
            
            $exam->status = 'draft';
            $exam->is_active = true;
            $exam->save();

            // Create exam centers if provided
            if (isset($examData['centers'])) {
                $this->createExamCenters($exam->id, $examData['centers']);
            }

            // Create exam sessions if scheduled
            if ($exam->exam_date) {
                $this->createDefaultSessions($exam);
            }

            DB::commit();

            Log::info('Entrance exam created', [
                'exam_id' => $exam->id,
                'exam_code' => $exam->exam_code,
            ]);

            return $exam;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create exam', [
                'error' => $e->getMessage(),
                'data' => $examData,
            ]);
            throw $e;
        }
    }

    /**
     * Schedule exam sessions
     *
     * @param int $examId
     * @param array $scheduleData
     * @return array
     * @throws Exception
     */
    public function scheduleExam(int $examId, array $scheduleData): array
    {
        DB::beginTransaction();

        try {
            $exam = EntranceExam::findOrFail($examId);
            
            // Validate exam can be scheduled
            if ($exam->status !== 'draft' && $exam->status !== 'published') {
                throw new Exception("Exam cannot be scheduled in current status: {$exam->status}");
            }

            $sessions = [];
            
            foreach ($scheduleData['sessions'] as $sessionData) {
                $session = new ExamSession();
                $session->exam_id = $examId;
                $session->center_id = $sessionData['center_id'];
                $session->session_code = $this->generateSessionCode($exam, $sessionData);
                $session->session_date = $sessionData['date'];
                $session->start_time = $sessionData['start_time'];
                $session->end_time = $sessionData['end_time'];
                $session->session_type = $sessionData['session_type'] ?? 'morning';
                $session->capacity = $sessionData['capacity'];
                $session->registered_count = 0;
                $session->proctoring_type = $sessionData['proctoring_type'] ?? 'in_person';
                $session->proctor_assignments = $sessionData['proctors'] ?? null;
                $session->candidates_per_proctor = $sessionData['candidates_per_proctor'] ?? 30;
                $session->status = 'scheduled';
                $session->special_instructions = $sessionData['instructions'] ?? null;
                $session->save();
                
                $sessions[] = $session;
            }

            // Update exam status
            if ($exam->status === 'draft') {
                $exam->status = 'published';
                $exam->save();
            }

            DB::commit();

            Log::info('Exam sessions scheduled', [
                'exam_id' => $examId,
                'sessions_count' => count($sessions),
            ]);

            return [
                'status' => 'success',
                'message' => count($sessions) . ' sessions scheduled successfully',
                'sessions' => $sessions,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to schedule exam', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Register candidate for exam
     *
     * @param int $examId
     * @param array $candidateData
     * @return EntranceExamRegistration
     * @throws Exception
     */
    public function registerCandidate(int $examId, array $candidateData): EntranceExamRegistration
    {
        DB::beginTransaction();

        try {
            $exam = EntranceExam::findOrFail($examId);
            
            // Validate registration period
            $now = now();
            if ($now < $exam->registration_start_date || $now > $exam->registration_end_date) {
                throw new Exception("Registration period is not active");
            }

            // Check if already registered
            $existingRegistration = $this->checkExistingRegistration($examId, $candidateData);
            if ($existingRegistration) {
                throw new Exception("Candidate is already registered for this exam");
            }

            // Check attempt limit
            if (isset($candidateData['application_id'])) {
                $attemptCount = $this->getAttemptCount($candidateData['application_id'], $exam->exam_type);
                if ($attemptCount >= self::MAX_ATTEMPTS_ALLOWED) {
                    throw new Exception("Maximum attempts ({self::MAX_ATTEMPTS_ALLOWED}) exceeded");
                }
            }

            // Generate registration number
            $registrationNumber = $this->generateRegistrationNumber($exam);

            // Create registration
            $registration = new EntranceExamRegistration();
            $registration->registration_number = $registrationNumber;
            $registration->exam_id = $examId;
            $registration->application_id = $candidateData['application_id'] ?? null;
            $registration->student_id = $candidateData['student_id'] ?? null;
            
            // Candidate information
            if (!isset($candidateData['application_id'])) {
                $registration->candidate_name = $candidateData['name'];
                $registration->candidate_email = $candidateData['email'];
                $registration->candidate_phone = $candidateData['phone'];
                $registration->date_of_birth = $candidateData['date_of_birth'];
            } else {
                // Get from application
                $application = AdmissionApplication::find($candidateData['application_id']);
                if ($application) {
                    $registration->candidate_name = $application->first_name . ' ' . $application->last_name;
                    $registration->candidate_email = $application->email;
                    $registration->candidate_phone = $application->phone_primary;
                    $registration->date_of_birth = $application->date_of_birth;
                }
            }
            
            $registration->registration_status = 'pending';
            
            // Special accommodations
            if (isset($candidateData['requires_accommodation'])) {
                $registration->requires_accommodation = true;
                $registration->accommodation_details = $candidateData['accommodation_details'];
            }
            
            // Process fee
            $feeAmount = $this->getExamFee($exam);
            $registration->fee_amount = $feeAmount;
            
            if ($feeAmount > 0) {
                // Process payment
                if (isset($candidateData['payment_data'])) {
                    $paymentResult = $this->processExamFee($registration, $candidateData['payment_data']);
                    if ($paymentResult['status'] === 'success') {
                        $registration->fee_paid = true;
                        $registration->payment_reference = $paymentResult['transaction_id'];
                        $registration->payment_date = now();
                        $registration->registration_status = 'confirmed';
                    }
                }
            } else {
                // Free exam
                $registration->fee_paid = true;
                $registration->registration_status = 'confirmed';
            }
            
            $registration->save();

            // Allocate session and seat if confirmed
            if ($registration->registration_status === 'confirmed') {
                $this->allocateSessionAndSeat($registration);
            }

            // Send confirmation
            $this->sendRegistrationConfirmation($registration);

            DB::commit();

            Log::info('Candidate registered for exam', [
                'registration_id' => $registration->id,
                'exam_id' => $examId,
                'registration_number' => $registration->registration_number,
            ]);

            return $registration;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to register candidate', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate hall ticket for registered candidate
     *
     * @param int $registrationId
     * @return string
     * @throws Exception
     */
    public function generateHallTicket(int $registrationId): string
    {
        $registration = EntranceExamRegistration::with([
            'exam',
            'seatAllocation.session',
            'seatAllocation.center'
        ])->findOrFail($registrationId);

        // Validate registration is confirmed
        if ($registration->registration_status !== 'confirmed') {
            throw new Exception("Registration is not confirmed");
        }

        // Check if hall ticket can be generated
        $exam = $registration->exam;
        $daysUntilExam = now()->diffInDays($exam->exam_date);
        
        if ($daysUntilExam > self::HALL_TICKET_AVAILABLE_DAYS) {
            throw new Exception("Hall ticket will be available " . self::HALL_TICKET_AVAILABLE_DAYS . " days before exam");
        }

        // Check seat allocation
        if (!$registration->seatAllocation) {
            throw new Exception("Seat not allocated yet");
        }

        // Generate hall ticket number if not exists
        if (!$registration->hall_ticket_number) {
            $registration->hall_ticket_number = $this->generateHallTicketNumber($registration);
            $registration->hall_ticket_generated_at = now();
            $registration->save();
        }

        // Generate QR code for verification
        $qrData = [
            'registration_number' => $registration->registration_number,
            'hall_ticket' => $registration->hall_ticket_number,
            'exam_code' => $exam->exam_code,
        ];
        
        $qrCode = QrCode::size(200)->generate(json_encode($qrData));

        // Prepare data for PDF
        $data = [
            'registration' => $registration,
            'exam' => $exam,
            'session' => $registration->seatAllocation->session,
            'center' => $registration->seatAllocation->center,
            'seat' => $registration->seatAllocation,
            'qr_code' => $qrCode,
            'photo' => $this->getCandidatePhoto($registration),
            'instructions' => $exam->general_instructions,
            'rules' => $exam->exam_rules,
        ];

        // Generate PDF
        $pdf = PDF::loadView('exams.hall-ticket', $data);
        
        $filename = "hall_ticket_{$registration->hall_ticket_number}.pdf";
        $path = "exams/hall_tickets/{$registration->id}/{$filename}";
        
        Storage::put($path, $pdf->output());

        // Mark as downloaded
        if (!$registration->hall_ticket_downloaded) {
            $registration->hall_ticket_downloaded = true;
            $registration->save();
        }

        Log::info('Hall ticket generated', [
            'registration_id' => $registrationId,
            'hall_ticket_number' => $registration->hall_ticket_number,
        ]);

        return $path;
    }

    /**
     * Assign exam center to registration
     *
     * @param int $registrationId
     * @param int $centerId
     * @return ExamSeatAllocation
     * @throws Exception
     */
    public function assignExamCenter(int $registrationId, int $centerId): ExamSeatAllocation
    {
        DB::beginTransaction();

        try {
            $registration = EntranceExamRegistration::with('exam')->findOrFail($registrationId);
            $center = ExamCenter::findOrFail($centerId);
            
            // Check if center is active
            if (!$center->is_active) {
                throw new Exception("Selected center is not active");
            }

            // Find available session at this center
            $session = ExamSession::where('exam_id', $registration->exam_id)
                ->where('center_id', $centerId)
                ->where('status', 'scheduled')
                ->where('registered_count', '<', DB::raw('capacity'))
                ->orderBy('session_date')
                ->orderBy('start_time')
                ->first();

            if (!$session) {
                throw new Exception("No available sessions at this center");
            }

            // Check existing allocation
            $existingAllocation = ExamSeatAllocation::where('registration_id', $registrationId)->first();
            
            if ($existingAllocation) {
                // Update existing allocation
                $oldSession = $existingAllocation->session;
                $existingAllocation->session_id = $session->id;
                $existingAllocation->center_id = $centerId;
                $existingAllocation->save();
                
                // Update session counts
                $oldSession->decrement('registered_count');
                $session->increment('registered_count');
            } else {
                // Create new allocation
                $allocation = $this->createSeatAllocation($registration, $session, $center);
                $session->increment('registered_count');
            }

            DB::commit();

            Log::info('Exam center assigned', [
                'registration_id' => $registrationId,
                'center_id' => $centerId,
                'session_id' => $session->id,
            ]);

            return $existingAllocation ?? $allocation;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign exam center', [
                'registration_id' => $registrationId,
                'center_id' => $centerId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Allocate seat for registered candidate
     *
     * @param int $registrationId
     * @param int $sessionId
     * @return ExamSeatAllocation
     * @throws Exception
     */
    public function allocateSeat(int $registrationId, int $sessionId): ExamSeatAllocation
    {
        DB::beginTransaction();

        try {
            $registration = EntranceExamRegistration::findOrFail($registrationId);
            $session = ExamSession::with('center')->findOrFail($sessionId);
            
            // Validate session is available
            if ($session->registered_count >= $session->capacity) {
                throw new Exception("Session is full");
            }

            // Check for existing allocation
            $existingAllocation = ExamSeatAllocation::where('registration_id', $registrationId)->first();
            if ($existingAllocation) {
                throw new Exception("Seat already allocated");
            }

            // Create seat allocation
            $allocation = $this->createSeatAllocation($registration, $session, $session->center);

            // Update session count
            $session->increment('registered_count');

            DB::commit();

            Log::info('Seat allocated', [
                'registration_id' => $registrationId,
                'session_id' => $sessionId,
                'seat_number' => $allocation->seat_number,
            ]);

            return $allocation;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to allocate seat', [
                'registration_id' => $registrationId,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Postpone exam to new date
     *
     * @param int $examId
     * @param Carbon $newDate
     * @param string $reason
     * @return EntranceExam
     * @throws Exception
     */
    public function postponeExam(int $examId, Carbon $newDate, string $reason): EntranceExam
    {
        DB::beginTransaction();

        try {
            $exam = EntranceExam::findOrFail($examId);
            
            // Validate new date is in future
            if ($newDate <= now()) {
                throw new Exception("New exam date must be in the future");
            }

            // Store old date for notification
            $oldDate = $exam->exam_date;

            // Update exam date
            $exam->exam_date = $newDate;
            $exam->postponement_reason = $reason;
            $exam->postponed_at = now();
            $exam->postponed_by = auth()->id();
            $exam->save();

            // Update all sessions
            $sessions = ExamSession::where('exam_id', $examId)->get();
            foreach ($sessions as $session) {
                $daysDiff = Carbon::parse($oldDate)->diffInDays($newDate);
                $session->session_date = Carbon::parse($session->session_date)->addDays($daysDiff);
                $session->save();
            }

            // Notify all registered candidates
            $this->notifyExamPostponement($exam, $oldDate, $newDate, $reason);

            // Log the postponement
            Log::info('Exam postponed', [
                'exam_id' => $examId,
                'old_date' => $oldDate,
                'new_date' => $newDate,
                'reason' => $reason,
            ]);

            DB::commit();

            return $exam;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to postpone exam', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel exam
     *
     * @param int $examId
     * @param string $reason
     * @return array
     * @throws Exception
     */
    public function cancelExam(int $examId, string $reason): array
    {
        DB::beginTransaction();

        try {
            $exam = EntranceExam::findOrFail($examId);
            
            // Check if exam can be cancelled
            if (in_array($exam->status, ['completed', 'results_published'])) {
                throw new Exception("Cannot cancel exam in status: {$exam->status}");
            }

            // Update exam status
            $exam->status = 'cancelled';
            $exam->cancellation_reason = $reason;
            $exam->cancelled_at = now();
            $exam->cancelled_by = auth()->id();
            $exam->is_active = false;
            $exam->save();

            // Cancel all sessions
            ExamSession::where('exam_id', $examId)
                ->update(['status' => 'cancelled']);

            // Get all registrations
            $registrations = EntranceExamRegistration::where('exam_id', $examId)
                ->whereIn('registration_status', ['pending', 'confirmed'])
                ->get();

            $refundCount = 0;
            $notificationCount = 0;

            foreach ($registrations as $registration) {
                // Process refunds
                if ($registration->fee_paid) {
                    $this->processExamFeeRefund($registration, $reason);
                    $refundCount++;
                }

                // Update registration status
                $registration->registration_status = 'cancelled';
                $registration->save();

                // Send cancellation notification
                $this->sendCancellationNotification($registration, $reason);
                $notificationCount++;
            }

            DB::commit();

            Log::info('Exam cancelled', [
                'exam_id' => $examId,
                'reason' => $reason,
                'refunds_processed' => $refundCount,
                'notifications_sent' => $notificationCount,
            ]);

            return [
                'status' => 'success',
                'message' => 'Exam cancelled successfully',
                'registrations_affected' => $registrations->count(),
                'refunds_processed' => $refundCount,
                'notifications_sent' => $notificationCount,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel exam', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Publish exam notice
     *
     * @param int $examId
     * @return array
     * @throws Exception
     */
    public function publishExamNotice(int $examId): array
    {
        DB::beginTransaction();

        try {
            $exam = EntranceExam::findOrFail($examId);
            
            // Validate exam is ready to publish
            if ($exam->status === 'draft') {
                // Check required fields
                $missingFields = $this->validateExamReadiness($exam);
                if (!empty($missingFields)) {
                    throw new Exception("Exam is not ready to publish. Missing: " . implode(', ', $missingFields));
                }
            }

            // Update exam status
            $previousStatus = $exam->status;
            $exam->status = 'published';
            $exam->published_at = now();
            $exam->published_by = auth()->id();
            
            // Open registration if within period
            if (now() >= $exam->registration_start_date && now() <= $exam->registration_end_date) {
                $exam->status = 'registration_open';
            }
            
            $exam->save();

            // Generate exam notice PDF
            $noticePath = $this->generateExamNotice($exam);

            // Notify eligible candidates
            $notificationCount = $this->notifyEligibleCandidates($exam);

            // Update exam sessions status
            ExamSession::where('exam_id', $examId)
                ->where('status', 'scheduled')
                ->update(['status' => 'registration_open']);

            DB::commit();

            Log::info('Exam notice published', [
                'exam_id' => $examId,
                'previous_status' => $previousStatus,
                'notifications_sent' => $notificationCount,
            ]);

            return [
                'status' => 'success',
                'message' => 'Exam notice published successfully',
                'notice_path' => $noticePath,
                'notifications_sent' => $notificationCount,
                'registration_opens' => $exam->registration_start_date->format('Y-m-d'),
                'registration_closes' => $exam->registration_end_date->format('Y-m-d'),
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to publish exam notice', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Private helper methods
     */

    /**
     * Validate exam data
     */
    private function validateExamData(array $data): void
    {
        $required = [
            'exam_name',
            'delivery_mode',
            'term_id',
            'total_marks',
            'passing_marks',
            'duration_minutes',
            'total_questions',
            'registration_start_date',
            'registration_end_date',
        ];

        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Required field '{$field}' is missing");
            }
        }

        // Validate dates
        $regStart = Carbon::parse($data['registration_start_date']);
        $regEnd = Carbon::parse($data['registration_end_date']);
        
        if ($regStart >= $regEnd) {
            throw new Exception("Registration end date must be after start date");
        }

        if (isset($data['exam_date'])) {
            $examDate = Carbon::parse($data['exam_date']);
            if ($examDate <= $regEnd) {
                throw new Exception("Exam date must be after registration end date");
            }
        }
    }

    /**
     * Generate unique exam code
     */
    private function generateExamCode(string $examType): string
    {
        $prefix = strtoupper(substr($examType, 0, 3));
        $year = date('Y');
        
        $lastExam = EntranceExam::where('exam_code', 'like', $prefix . '-' . $year . '%')
            ->orderBy('exam_code', 'desc')
            ->first();

        if ($lastExam && preg_match('/' . $prefix . '-\d{4}-(\d{3})/', $lastExam->exam_code, $matches)) {
            $number = str_pad(intval($matches[1]) + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $number = '001';
        }

        return $prefix . '-' . $year . '-' . $number;
    }

    /**
     * Get default exam instructions
     */
    private function getDefaultInstructions(): string
    {
        return "1. Report to the exam center 30 minutes before the scheduled time.
2. Bring valid photo identification and hall ticket.
3. Electronic devices are not permitted in the exam hall.
4. Use only blue or black ink pen.
5. Rough work should be done on the sheets provided.
6. Do not write anything on the question paper.
7. Return all exam materials before leaving the hall.";
    }

    /**
     * Get default exam rules
     */
    private function getDefaultRules(): string
    {
        return "1. Late entry is not permitted after 30 minutes.
2. No candidate can leave the exam hall before half the exam duration.
3. Any form of malpractice will lead to disqualification.
4. Follow all instructions given by the invigilator.
5. Maintain silence and discipline in the exam hall.";
    }

    /**
     * Create exam centers
     */
    private function createExamCenters(int $examId, array $centers): void
    {
        foreach ($centers as $centerData) {
            $center = ExamCenter::firstOrCreate(
                ['center_code' => $centerData['code']],
                [
                    'center_name' => $centerData['name'],
                    'center_type' => $centerData['type'] ?? 'internal',
                    'address' => $centerData['address'] ?? null,
                    'city' => $centerData['city'] ?? null,
                    'state' => $centerData['state'] ?? null,
                    'country' => $centerData['country'] ?? null,
                    'total_capacity' => $centerData['capacity'] ?? 100,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * Create default sessions for exam
     */
    private function createDefaultSessions(EntranceExam $exam): void
    {
        // Get active centers
        $centers = ExamCenter::where('is_active', true)
            ->where('center_type', '!=', 'online')
            ->get();

        foreach ($centers as $center) {
            // Create morning session
            ExamSession::create([
                'exam_id' => $exam->id,
                'center_id' => $center->id,
                'session_code' => $this->generateSessionCode($exam, ['type' => 'morning']),
                'session_date' => $exam->exam_date,
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
                'session_type' => 'morning',
                'capacity' => intval($center->total_capacity * 0.5),
                'registered_count' => 0,
                'proctoring_type' => 'in_person',
                'status' => 'scheduled',
            ]);

            // Create afternoon session if needed
            if ($center->total_capacity > 100) {
                ExamSession::create([
                    'exam_id' => $exam->id,
                    'center_id' => $center->id,
                    'session_code' => $this->generateSessionCode($exam, ['type' => 'afternoon']),
                    'session_date' => $exam->exam_date,
                    'start_time' => '14:00:00',
                    'end_time' => '17:00:00',
                    'session_type' => 'afternoon',
                    'capacity' => intval($center->total_capacity * 0.5),
                    'registered_count' => 0,
                    'proctoring_type' => 'in_person',
                    'status' => 'scheduled',
                ]);
            }
        }
    }

    /**
     * Generate session code
     */
    private function generateSessionCode(EntranceExam $exam, array $sessionData): string
    {
        $type = substr(strtoupper($sessionData['type'] ?? 'M'), 0, 1);
        return $exam->exam_code . '-' . $type . '-' . Str::random(4);
    }

    /**
     * Check existing registration
     */
    private function checkExistingRegistration(int $examId, array $candidateData): ?EntranceExamRegistration
    {
        $query = EntranceExamRegistration::where('exam_id', $examId);
        
        if (isset($candidateData['application_id'])) {
            $query->where('application_id', $candidateData['application_id']);
        } elseif (isset($candidateData['student_id'])) {
            $query->where('student_id', $candidateData['student_id']);
        } else {
            $query->where('candidate_email', $candidateData['email']);
        }
        
        return $query->whereNotIn('registration_status', ['cancelled', 'expired'])->first();
    }

    /**
     * Get attempt count for candidate
     */
    private function getAttemptCount(int $applicationId, string $examType): int
    {
        return EntranceExamRegistration::where('application_id', $applicationId)
            ->whereHas('exam', function ($query) use ($examType) {
                $query->where('exam_type', $examType);
            })
            ->whereIn('registration_status', ['confirmed', 'completed'])
            ->count();
    }

    /**
     * Generate registration number
     */
    private function generateRegistrationNumber(EntranceExam $exam): string
    {
        $prefix = 'REG-' . $exam->exam_code;
        $count = EntranceExamRegistration::where('exam_id', $exam->id)->count();
        return $prefix . '-' . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get exam fee amount
     */
    private function getExamFee(EntranceExam $exam): float
    {
        return self::EXAM_FEES[$exam->exam_type] ?? 75.00;
    }

    /**
     * Process exam fee payment
     */
    private function processExamFee(EntranceExamRegistration $registration, array $paymentData): array
    {
        try {
            // This would integrate with payment gateway
            $transactionId = 'EXM-TXN-' . date('YmdHis') . '-' . Str::random(6);
            
            // Create fee record
            if ($registration->application_id) {
                ApplicationFee::create([
                    'application_id' => $registration->application_id,
                    'fee_type' => 'entrance_exam',
                    'amount' => $registration->fee_amount,
                    'currency' => 'USD',
                    'status' => 'paid',
                    'payment_method' => $paymentData['method'] ?? 'online',
                    'transaction_id' => $transactionId,
                    'paid_date' => now(),
                ]);
            }
            
            return [
                'status' => 'success',
                'transaction_id' => $transactionId,
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Allocate session and seat for registration
     */
    private function allocateSessionAndSeat(EntranceExamRegistration $registration): void
    {
        // Find best available session
        $session = ExamSession::where('exam_id', $registration->exam_id)
            ->where('status', 'scheduled')
            ->where('registered_count', '<', DB::raw('capacity * ' . (1 - self::SEAT_ALLOCATION_BUFFER)))
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->first();

        if ($session) {
            $center = $session->center;
            $this->createSeatAllocation($registration, $session, $center);
            $session->increment('registered_count');
        }
    }

    /**
     * Create seat allocation
     */
    private function createSeatAllocation(
        EntranceExamRegistration $registration,
        ExamSession $session,
        ExamCenter $center
    ): ExamSeatAllocation {
        
        // Generate seat number
        $existingSeats = ExamSeatAllocation::where('session_id', $session->id)->count();
        $seatNumber = 'S' . str_pad($existingSeats + 1, 3, '0', STR_PAD_LEFT);
        
        $allocation = new ExamSeatAllocation();
        $allocation->registration_id = $registration->id;
        $allocation->session_id = $session->id;
        $allocation->center_id = $center->id;
        $allocation->seat_number = $seatNumber;
        
        // Assign room if center has room information
        if ($center->facilities && isset($center->facilities['rooms'])) {
            $rooms = $center->facilities['rooms'];
            $roomIndex = intval($existingSeats / 30); // 30 seats per room
            if (isset($rooms[$roomIndex])) {
                $allocation->room_number = $rooms[$roomIndex]['number'];
                $allocation->floor = $rooms[$roomIndex]['floor'] ?? null;
                $allocation->building = $rooms[$roomIndex]['building'] ?? null;
            }
        }
        
        // For CBT exams
        if ($session->exam->delivery_mode === 'computer_based') {
            $allocation->computer_number = 'PC' . str_pad($existingSeats + 1, 3, '0', STR_PAD_LEFT);
            $allocation->login_id = $registration->registration_number;
            // Password would be encrypted
            $allocation->password = bcrypt(Str::random(8));
        }
        
        $allocation->save();
        
        return $allocation;
    }

    /**
     * Generate hall ticket number
     */
    private function generateHallTicketNumber(EntranceExamRegistration $registration): string
    {
        $exam = $registration->exam;
        return 'HT-' . $exam->exam_code . '-' . str_pad($registration->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get candidate photo
     */
    private function getCandidatePhoto(EntranceExamRegistration $registration): ?string
    {
        // Get photo from application documents if available
        if ($registration->application_id) {
            $photo = DB::table('application_documents')
                ->where('application_id', $registration->application_id)
                ->where('document_type', 'photo')
                ->first();
            
            if ($photo) {
                return Storage::url($photo->file_path);
            }
        }
        
        return null;
    }

    /**
     * Process exam fee refund
     */
    private function processExamFeeRefund(EntranceExamRegistration $registration, string $reason): void
    {
        if ($registration->fee_paid && $registration->application_id) {
            ApplicationFee::create([
                'application_id' => $registration->application_id,
                'fee_type' => 'entrance_exam_refund',
                'amount' => -$registration->fee_amount,
                'status' => 'refunded',
                'notes' => "Refund due to: {$reason}",
                'refunded_date' => now(),
            ]);
        }
    }

    /**
     * Validate exam readiness for publishing
     */
    private function validateExamReadiness(EntranceExam $exam): array
    {
        $missing = [];
        
        if (!$exam->exam_date && !$exam->exam_window_start) {
            $missing[] = 'exam date or window';
        }
        
        if (ExamSession::where('exam_id', $exam->id)->count() === 0) {
            $missing[] = 'exam sessions';
        }
        
        if (ExamCenter::where('is_active', true)->count() === 0) {
            $missing[] = 'active exam centers';
        }
        
        return $missing;
    }

    /**
     * Generate exam notice PDF
     */
    private function generateExamNotice(EntranceExam $exam): string
    {
        $data = [
            'exam' => $exam,
            'centers' => ExamCenter::where('is_active', true)->get(),
            'sessions' => ExamSession::where('exam_id', $exam->id)->get(),
            'important_dates' => [
                'registration_opens' => $exam->registration_start_date,
                'registration_closes' => $exam->registration_end_date,
                'exam_date' => $exam->exam_date,
                'result_date' => $exam->result_publish_date,
            ],
        ];
        
        $pdf = PDF::loadView('exams.notice', $data);
        $filename = "exam_notice_{$exam->exam_code}.pdf";
        $path = "exams/notices/{$filename}";
        
        Storage::put($path, $pdf->output());
        
        return $path;
    }

    /**
     * Notify eligible candidates about exam
     */
    private function notifyEligibleCandidates(EntranceExam $exam): int
    {
        $count = 0;
        
        // Get eligible applications based on exam configuration
        $query = AdmissionApplication::where('term_id', $exam->term_id)
            ->whereIn('status', ['submitted', 'under_review']);
        
        if ($exam->applicable_programs) {
            $query->whereIn('program_id', $exam->applicable_programs);
        }
        
        if ($exam->applicable_application_types) {
            $query->whereIn('application_type', $exam->applicable_application_types);
        }
        
        $applications = $query->get();
        
        foreach ($applications as $application) {
            try {
                ApplicationCommunication::create([
                    'application_id' => $application->id,
                    'communication_type' => 'email',
                    'direction' => 'outbound',
                    'subject' => 'Entrance Exam Notice - ' . $exam->exam_name,
                    'message' => $this->getExamNoticeMessage($exam),
                    'recipient_email' => $application->email,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                $count++;
            } catch (Exception $e) {
                Log::warning('Failed to send exam notice', [
                    'application_id' => $application->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return $count;
    }

    /**
     * Send notifications
     */
    private function notifyExamPostponement(EntranceExam $exam, $oldDate, Carbon $newDate, string $reason): void
    {
        $registrations = EntranceExamRegistration::where('exam_id', $exam->id)
            ->whereIn('registration_status', ['confirmed', 'pending'])
            ->get();
        
        foreach ($registrations as $registration) {
            $message = "Important: The {$exam->exam_name} scheduled for {$oldDate} has been postponed to {$newDate->format('Y-m-d')}. 
                       Reason: {$reason}. Your registration remains valid for the new date.";
            
            $this->sendNotification($registration, 'Exam Postponed', $message);
        }
    }

    private function sendRegistrationConfirmation(EntranceExamRegistration $registration): void
    {
        $exam = $registration->exam;
        $message = "Your registration for {$exam->exam_name} is confirmed. 
                   Registration Number: {$registration->registration_number}
                   Exam Date: {$exam->exam_date?->format('Y-m-d')}
                   Hall ticket will be available " . self::HALL_TICKET_AVAILABLE_DAYS . " days before the exam.";
        
        $this->sendNotification($registration, 'Exam Registration Confirmed', $message);
    }

    private function sendCancellationNotification(EntranceExamRegistration $registration, string $reason): void
    {
        $message = "We regret to inform you that the entrance exam you registered for has been cancelled. 
                   Reason: {$reason}. 
                   If you have paid the exam fee, a refund will be processed within 7-10 business days.";
        
        $this->sendNotification($registration, 'Exam Cancelled', $message);
    }

    private function sendNotification(EntranceExamRegistration $registration, string $subject, string $message): void
    {
        try {
            if ($registration->application_id) {
                ApplicationCommunication::create([
                    'application_id' => $registration->application_id,
                    'communication_type' => 'email',
                    'direction' => 'outbound',
                    'subject' => $subject,
                    'message' => $message,
                    'recipient_email' => $registration->candidate_email,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            }
        } catch (Exception $e) {
            Log::warning('Failed to send notification', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getExamNoticeMessage(EntranceExam $exam): string
    {
        return "Dear Applicant,
                
                We are pleased to announce the {$exam->exam_name} for admission to our programs.
                
                Important Dates:
                - Registration Opens: {$exam->registration_start_date->format('F d, Y')}
                - Registration Closes: {$exam->registration_end_date->format('F d, Y')}
                - Exam Date: {$exam->exam_date?->format('F d, Y')}
                - Results: {$exam->result_publish_date?->format('F d, Y')}
                
                Exam Fee: $" . $this->getExamFee($exam) . "
                Duration: {$exam->duration_minutes} minutes
                Total Marks: {$exam->total_marks}
                
                Please log in to your applicant portal to register for the exam.
                
                Best regards,
                Admissions Office";
    }
}