<?php

namespace App\Services;

use App\Models\AdmissionApplication;
use App\Models\EnrollmentConfirmation;
use App\Models\Student;
use App\Models\User;
use App\Models\ApplicationCommunication;
use App\Models\ApplicationFee;
use App\Models\ApplicationNote;
use App\Models\AcademicTerm;
use App\Models\AcademicProgram;
use App\Models\StudentAccount;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class EnrollmentConfirmationService
{
    /**
     * Enrollment deadlines configuration (in days)
     */
    private const ENROLLMENT_DEADLINE_DAYS = 30;
    private const DEPOSIT_DEADLINE_DAYS = 14;
    private const DOCUMENT_SUBMISSION_DAYS = 60;
    
    /**
     * Deposit amounts by program type
     */
    private const ENROLLMENT_DEPOSITS = [
        'undergraduate' => 500.00,
        'graduate' => 750.00,
        'doctoral' => 1000.00,
        'certificate' => 250.00,
    ];

    /**
     * Student ID format configuration
     */
    private const STUDENT_ID_PREFIX = 'STU';
    private const STUDENT_ID_LENGTH = 8;

    /**
     * Send admission offer to accepted applicant
     *
     * @param int $applicationId
     * @param array $offerData
     * @return EnrollmentConfirmation
     * @throws Exception
     */
    public function sendAdmissionOffer(int $applicationId, array $offerData = []): EnrollmentConfirmation
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::with(['program', 'term'])->findOrFail($applicationId);

            // Verify application is admitted
            if (!in_array($application->decision, ['admit', 'conditional_admit'])) {
                throw new Exception("Cannot send offer to non-admitted application");
            }

            // Check if enrollment confirmation already exists
            $enrollment = EnrollmentConfirmation::firstOrNew(['application_id' => $applicationId]);

            // Set enrollment details
            $enrollment->decision = 'pending';
            $enrollment->enrollment_deadline = $offerData['deadline'] ?? Carbon::now()->addDays(self::ENROLLMENT_DEADLINE_DAYS);
            $enrollment->deposit_amount = $offerData['deposit_amount'] ?? $this->calculateDepositAmount($application);
            $enrollment->deposit_deadline = $offerData['deposit_deadline'] ?? Carbon::now()->addDays(self::DEPOSIT_DEADLINE_DAYS);
            
            // Set important dates from term
            if ($application->term) {
                $enrollment->orientation_date = $application->term->orientation_date;
                $enrollment->classes_start_date = $application->term->start_date;
                $enrollment->move_in_date = $application->term->move_in_date;
            }

            // Add any special conditions for conditional admits
            if ($application->decision === 'conditional_admit') {
                $enrollment->conditions = $application->admission_conditions;
                $enrollment->conditions_deadline = $offerData['conditions_deadline'] ?? 
                    Carbon::parse($enrollment->classes_start_date)->subDays(30);
            }

            $enrollment->save();

            // Create enrollment checklist
            $this->createEnrollmentChecklist($enrollment);

            // Send offer communication
            $this->sendOfferCommunication($application, $enrollment);

            // Update application status
            $application->enrollment_offer_sent = true;
            $application->enrollment_offer_sent_date = now();
            $application->save();

            // Log the action
            $this->logEnrollmentAction($application, 'offer_sent', 'Admission offer sent to applicant');

            DB::commit();

            Log::info('Admission offer sent', [
                'application_id' => $applicationId,
                'enrollment_id' => $enrollment->id,
                'deadline' => $enrollment->enrollment_deadline,
            ]);

            return $enrollment;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to send admission offer', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Confirm enrollment for admitted student
     *
     * @param int $applicationId
     * @param array $confirmationData
     * @return EnrollmentConfirmation
     * @throws Exception
     */
    public function confirmEnrollment(int $applicationId, array $confirmationData): EnrollmentConfirmation
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::with(['program', 'term'])->findOrFail($applicationId);
            $enrollment = EnrollmentConfirmation::where('application_id', $applicationId)->firstOrFail();

            // Validate enrollment can be confirmed
            if ($enrollment->decision !== 'pending') {
                throw new Exception("Enrollment has already been {$enrollment->decision}");
            }

            if (Carbon::now() > $enrollment->enrollment_deadline) {
                throw new Exception("Enrollment deadline has passed");
            }

            // Check if deposit is required and paid
            if ($enrollment->deposit_amount > 0 && !$enrollment->deposit_paid) {
                throw new Exception("Enrollment deposit has not been paid");
            }

            // Update enrollment confirmation
            $enrollment->decision = 'accept';
            $enrollment->decision_date = now();
            
            // Process confirmation data
            if (isset($confirmationData['housing_required'])) {
                $enrollment->housing_applied = $confirmationData['housing_required'];
            }
            
            if (isset($confirmationData['orientation_attending'])) {
                $enrollment->orientation_registered = $confirmationData['orientation_attending'];
            }

            if (isset($confirmationData['arrival_date'])) {
                $enrollment->expected_arrival_date = $confirmationData['arrival_date'];
            }

            $enrollment->save();

            // Update application
            $application->enrollment_confirmed = true;
            $application->enrollment_confirmation_date = now();
            $application->status = 'enrolled';
            $application->save();

            // Generate student account if not exists
            if (!$enrollment->student_account_created) {
                $student = $this->generateStudentAccount($enrollment->id);
                $enrollment->student_account_created = true;
                $enrollment->student_record_id = $student->id;
                $enrollment->student_id = $student->student_id;
                $enrollment->save();
            }

            // Send confirmation acknowledgment
            $this->sendEnrollmentConfirmation($application, $enrollment);

            // Update enrollment statistics
            $this->updateEnrollmentStatistics($application->program_id, $application->term_id);

            // Log the action
            $this->logEnrollmentAction($application, 'enrollment_confirmed', 'Student confirmed enrollment');

            DB::commit();

            Log::info('Enrollment confirmed', [
                'application_id' => $applicationId,
                'enrollment_id' => $enrollment->id,
                'student_id' => $enrollment->student_id,
            ]);

            return $enrollment->fresh(['application', 'studentRecord']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to confirm enrollment', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Decline enrollment offer
     *
     * @param int $applicationId
     * @param string $reason
     * @return EnrollmentConfirmation
     * @throws Exception
     */
    public function declineEnrollment(int $applicationId, string $reason): EnrollmentConfirmation
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);
            $enrollment = EnrollmentConfirmation::where('application_id', $applicationId)->firstOrFail();

            // Validate enrollment can be declined
            if ($enrollment->decision !== 'pending') {
                throw new Exception("Enrollment has already been {$enrollment->decision}");
            }

            // Update enrollment
            $enrollment->decision = 'decline';
            $enrollment->decision_date = now();
            $enrollment->decision_reason = $reason;
            $enrollment->save();

            // Update application
            $application->enrollment_declined = true;
            $application->enrollment_declined_date = now();
            $application->enrollment_declined_reason = $reason;
            $application->status = 'enrollment_declined';
            $application->save();

            // Process any refunds if applicable
            if ($enrollment->deposit_paid) {
                $this->processDepositRefund($enrollment, $reason);
            }

            // Release the spot for waitlist
            $this->releaseEnrollmentSpot($application);

            // Send decline acknowledgment
            $this->sendDeclineAcknowledgment($application, $reason);

            // Log the action
            $this->logEnrollmentAction($application, 'enrollment_declined', "Student declined enrollment: {$reason}");

            DB::commit();

            Log::info('Enrollment declined', [
                'application_id' => $applicationId,
                'reason' => $reason,
            ]);

            return $enrollment;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to decline enrollment', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process enrollment deposit payment
     *
     * @param int $applicationId
     * @param array $paymentData
     * @return array
     * @throws Exception
     */
    public function processEnrollmentDeposit(int $applicationId, array $paymentData): array
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);
            $enrollment = EnrollmentConfirmation::where('application_id', $applicationId)->firstOrFail();

            // Validate deposit not already paid
            if ($enrollment->deposit_paid) {
                throw new Exception("Enrollment deposit has already been paid");
            }

            // Validate amount
            if ($paymentData['amount'] < $enrollment->deposit_amount) {
                throw new Exception("Payment amount is less than required deposit");
            }

            // Process payment (integrate with payment gateway)
            $transaction = $this->processPayment($application, $paymentData, 'enrollment_deposit');

            if ($transaction['status'] === 'success') {
                // Update enrollment record
                $enrollment->deposit_paid = true;
                $enrollment->deposit_paid_date = now();
                $enrollment->deposit_transaction_id = $transaction['transaction_id'];
                $enrollment->deposit_receipt_number = $this->generateReceiptNumber();
                $enrollment->save();

                // Update application fee record
                ApplicationFee::create([
                    'application_id' => $applicationId,
                    'fee_type' => 'enrollment_deposit',
                    'amount' => $paymentData['amount'],
                    'currency' => $paymentData['currency'] ?? 'USD',
                    'status' => 'paid',
                    'payment_method' => $paymentData['payment_method'],
                    'transaction_id' => $transaction['transaction_id'],
                    'receipt_number' => $enrollment->deposit_receipt_number,
                    'paid_date' => now(),
                ]);

                // Send payment confirmation
                $this->sendDepositConfirmation($application, $enrollment, $transaction);

                // Log the action
                $this->logEnrollmentAction($application, 'deposit_paid', 'Enrollment deposit paid');

                DB::commit();

                return [
                    'status' => 'success',
                    'message' => 'Enrollment deposit processed successfully',
                    'transaction_id' => $transaction['transaction_id'],
                    'receipt_number' => $enrollment->deposit_receipt_number,
                    'amount' => $paymentData['amount'],
                ];
            } else {
                throw new Exception("Payment processing failed: {$transaction['error']}");
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to process enrollment deposit', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate student account for enrolled student
     *
     * @param int $enrollmentId
     * @return Student
     * @throws Exception
     */
    public function generateStudentAccount(int $enrollmentId): Student
    {
        $enrollment = EnrollmentConfirmation::with('application')->findOrFail($enrollmentId);
        $application = $enrollment->application;

        // Check if student already exists
        $existingStudent = Student::where('email', $application->email)->first();
        if ($existingStudent) {
            return $existingStudent;
        }

        // Generate student ID
        $studentId = $this->assignStudentId($enrollmentId);

        // Create user account
        $user = User::create([
            'name' => $application->first_name . ' ' . $application->last_name,
            'email' => $application->email,
            'password' => Hash::make(Str::random(16)), // Temporary password
            'user_type' => 'student',
            'is_active' => true,
        ]);

        // Create student record
        $student = new Student();
        $student->user_id = $user->id;
        $student->student_id = $studentId;
        $student->first_name = $application->first_name;
        $student->middle_name = $application->middle_name;
        $student->last_name = $application->last_name;
        $student->email = $application->email;
        $student->phone_primary = $application->phone_primary;
        $student->phone_secondary = $application->phone_secondary;
        $student->date_of_birth = $application->date_of_birth;
        $student->gender = $application->gender;
        $student->nationality = $application->nationality;
        $student->national_id = $application->national_id;
        $student->passport_number = $application->passport_number;
        
        // Address
        $student->current_address = $application->current_address;
        $student->permanent_address = $application->permanent_address;
        $student->city = $application->city;
        $student->state_province = $application->state_province;
        $student->postal_code = $application->postal_code;
        $student->country = $application->country;
        
        // Academic info
        $student->program_id = $application->program_id;
        $student->program_name = $application->program->name ?? null;
        $student->department = $application->program->department->name ?? null;
        $student->academic_level = 'freshman'; // Default, will be updated
        $student->entry_term = $application->term->code ?? null;
        $student->entry_year = $application->entry_year;
        $student->enrollment_status = 'active';
        $student->enrollment_date = now();
        
        // Emergency contact
        $student->emergency_contact_name = $application->emergency_contact_name;
        $student->emergency_contact_phone = $application->emergency_contact_phone;
        $student->emergency_contact_relationship = $application->emergency_contact_relationship;
        
        $student->save();

        // Create student financial account
        $this->createStudentFinancialAccount($student);

        // Send account creation notification
        $this->sendAccountCreationNotification($student, $user);

        Log::info('Student account created', [
            'student_id' => $student->student_id,
            'user_id' => $user->id,
            'enrollment_id' => $enrollmentId,
        ]);

        return $student;
    }

    /**
     * Assign student ID to enrolled student
     *
     * @param int $enrollmentId
     * @return string
     */
    public function assignStudentId(int $enrollmentId): string
    {
        $enrollment = EnrollmentConfirmation::with('application.term')->findOrFail($enrollmentId);
        
        // Generate student ID format: STU20250001
        $year = $enrollment->application->term->year ?? date('Y');
        
        // Get the last student ID for this year
        $lastStudent = Student::where('student_id', 'like', self::STUDENT_ID_PREFIX . $year . '%')
            ->orderBy('student_id', 'desc')
            ->first();

        if ($lastStudent) {
            $lastNumber = intval(substr($lastStudent->student_id, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return self::STUDENT_ID_PREFIX . $year . $newNumber;
    }

    /**
     * Transfer enrolled student to main student system
     *
     * @param int $enrollmentId
     * @return array
     * @throws Exception
     */
    public function transferToStudentSystem(int $enrollmentId): array
    {
        DB::beginTransaction();

        try {
            $enrollment = EnrollmentConfirmation::with(['application', 'studentRecord'])->findOrFail($enrollmentId);

            // Verify enrollment is confirmed
            if ($enrollment->decision !== 'accept') {
                throw new Exception("Cannot transfer non-confirmed enrollment");
            }

            // Check if already transferred
            if ($enrollment->transferred_to_sis) {
                throw new Exception("Student already transferred to SIS");
            }

            // Ensure student account exists
            if (!$enrollment->student_record_id) {
                $student = $this->generateStudentAccount($enrollmentId);
                $enrollment->student_record_id = $student->id;
                $enrollment->student_id = $student->student_id;
                $enrollment->save();
            }

            $student = $enrollment->studentRecord;

            // Transfer academic records
            $this->transferAcademicRecords($enrollment, $student);

            // Transfer documents
            $this->transferDocuments($enrollment->application_id, $student->id);

            // Set up initial course registration
            $this->setupInitialRegistration($student, $enrollment->application->term_id);

            // Mark as transferred
            $enrollment->transferred_to_sis = true;
            $enrollment->transferred_at = now();
            $enrollment->save();

            // Update application status
            $enrollment->application->status = 'enrolled_active';
            $enrollment->application->save();

            DB::commit();

            Log::info('Student transferred to SIS', [
                'enrollment_id' => $enrollmentId,
                'student_id' => $student->student_id,
            ]);

            return [
                'status' => 'success',
                'student_id' => $student->student_id,
                'message' => 'Student successfully transferred to student information system',
                'transferred_at' => now()->toIso8601String(),
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to transfer to student system', [
                'enrollment_id' => $enrollmentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Track enrollment yield for term and program
     *
     * @param int $termId
     * @param int|null $programId
     * @return array
     */
    public function trackEnrollmentYield(int $termId, ?int $programId = null): array
    {
        $query = AdmissionApplication::where('term_id', $termId)
            ->whereIn('decision', ['admit', 'conditional_admit']);

        if ($programId) {
            $query->where('program_id', $programId);
        }

        $admittedApplications = $query->get();

        $statistics = [
            'term_id' => $termId,
            'program_id' => $programId,
            'total_admitted' => $admittedApplications->count(),
            'total_deposits_paid' => 0,
            'total_enrolled' => 0,
            'total_declined' => 0,
            'total_pending' => 0,
            'deposit_rate' => 0,
            'yield_rate' => 0,
            'melt_rate' => 0,
            'by_decision_date' => [],
            'by_program' => [],
            'by_application_type' => [],
        ];

        foreach ($admittedApplications as $application) {
            $enrollment = $application->enrollmentConfirmation;
            
            if (!$enrollment) {
                $statistics['total_pending']++;
                continue;
            }

            // Count deposits
            if ($enrollment->deposit_paid) {
                $statistics['total_deposits_paid']++;
            }

            // Count by decision
            switch ($enrollment->decision) {
                case 'accept':
                    $statistics['total_enrolled']++;
                    break;
                case 'decline':
                    $statistics['total_declined']++;
                    break;
                case 'pending':
                    $statistics['total_pending']++;
                    break;
            }

            // Track by date
            if ($enrollment->decision_date) {
                $dateKey = Carbon::parse($enrollment->decision_date)->format('Y-m-d');
                if (!isset($statistics['by_decision_date'][$dateKey])) {
                    $statistics['by_decision_date'][$dateKey] = [
                        'enrolled' => 0,
                        'declined' => 0,
                    ];
                }
                
                if ($enrollment->decision === 'accept') {
                    $statistics['by_decision_date'][$dateKey]['enrolled']++;
                } elseif ($enrollment->decision === 'decline') {
                    $statistics['by_decision_date'][$dateKey]['declined']++;
                }
            }

            // Track by program
            $programName = $application->program->name ?? 'Unknown';
            if (!isset($statistics['by_program'][$programName])) {
                $statistics['by_program'][$programName] = [
                    'admitted' => 0,
                    'enrolled' => 0,
                    'declined' => 0,
                ];
            }
            $statistics['by_program'][$programName]['admitted']++;
            
            if ($enrollment->decision === 'accept') {
                $statistics['by_program'][$programName]['enrolled']++;
            } elseif ($enrollment->decision === 'decline') {
                $statistics['by_program'][$programName]['declined']++;
            }

            // Track by application type
            $appType = $application->application_type;
            if (!isset($statistics['by_application_type'][$appType])) {
                $statistics['by_application_type'][$appType] = [
                    'admitted' => 0,
                    'enrolled' => 0,
                ];
            }
            $statistics['by_application_type'][$appType]['admitted']++;
            
            if ($enrollment->decision === 'accept') {
                $statistics['by_application_type'][$appType]['enrolled']++;
            }
        }

        // Calculate rates
        if ($statistics['total_admitted'] > 0) {
            $statistics['deposit_rate'] = round(
                ($statistics['total_deposits_paid'] / $statistics['total_admitted']) * 100, 2
            );
            $statistics['yield_rate'] = round(
                ($statistics['total_enrolled'] / $statistics['total_admitted']) * 100, 2
            );
            
            // Melt rate: students who paid deposit but didn't enroll
            $depositedNotEnrolled = $statistics['total_deposits_paid'] - $statistics['total_enrolled'];
            if ($statistics['total_deposits_paid'] > 0) {
                $statistics['melt_rate'] = round(
                    ($depositedNotEnrolled / $statistics['total_deposits_paid']) * 100, 2
                );
            }
        }

        // Add predictions based on historical data
        $statistics['predictions'] = $this->predictYield($termId, $programId, $statistics);

        return $statistics;
    }

    /**
     * Send enrollment reminders to pending students
     *
     * @return array
     */
    public function sendEnrollmentReminders(): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'details' => [],
        ];

        // Find pending enrollments approaching deadline
        $pendingEnrollments = EnrollmentConfirmation::with('application')
            ->where('decision', 'pending')
            ->where('enrollment_deadline', '>', now())
            ->where('enrollment_deadline', '<=', now()->addDays(7))
            ->get();

        foreach ($pendingEnrollments as $enrollment) {
            try {
                // Check if reminder already sent recently
                $recentReminder = ApplicationCommunication::where('application_id', $enrollment->application_id)
                    ->where('communication_type', 'email')
                    ->where('subject', 'like', '%Enrollment Deadline Reminder%')
                    ->where('created_at', '>', now()->subDays(3))
                    ->exists();

                if (!$recentReminder) {
                    $this->sendReminderNotification($enrollment);
                    $results['sent']++;
                    $results['details'][] = [
                        'application_id' => $enrollment->application_id,
                        'deadline' => $enrollment->enrollment_deadline->format('Y-m-d'),
                    ];
                }
            } catch (Exception $e) {
                $results['failed']++;
                Log::error('Failed to send enrollment reminder', [
                    'enrollment_id' => $enrollment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Send deposit reminders
        $depositPending = EnrollmentConfirmation::with('application')
            ->where('decision', 'accept')
            ->where('deposit_paid', false)
            ->where('deposit_deadline', '>', now())
            ->where('deposit_deadline', '<=', now()->addDays(3))
            ->get();

        foreach ($depositPending as $enrollment) {
            try {
                $this->sendDepositReminder($enrollment);
                $results['sent']++;
            } catch (Exception $e) {
                $results['failed']++;
            }
        }

        Log::info('Enrollment reminders sent', $results);

        return $results;
    }

    /**
     * Private helper methods
     */

    /**
     * Calculate deposit amount based on program
     */
    private function calculateDepositAmount(AdmissionApplication $application): float
    {
        $program = $application->program;
        
        if (!$program) {
            return self::ENROLLMENT_DEPOSITS['undergraduate'];
        }

        $programType = strtolower($program->degree_type ?? 'undergraduate');
        
        return self::ENROLLMENT_DEPOSITS[$programType] ?? self::ENROLLMENT_DEPOSITS['undergraduate'];
    }

    /**
     * Create enrollment checklist
     */
    private function createEnrollmentChecklist(EnrollmentConfirmation $enrollment): void
    {
        $checklistItems = [
            'Accept/Decline Offer' => false,
            'Pay Enrollment Deposit' => false,
            'Submit Final Transcripts' => false,
            'Complete Health Forms' => false,
            'Submit Immunization Records' => false,
            'Apply for Housing' => false,
            'Register for Orientation' => false,
            'Apply for Student ID' => false,
            'Set up Student Email' => false,
            'Register for Classes' => false,
        ];

        $enrollment->enrollment_checklist = $checklistItems;
        $enrollment->save();
    }

    /**
     * Process payment through payment gateway
     */
    private function processPayment(AdmissionApplication $application, array $paymentData, string $type): array
    {
        // This would integrate with actual payment gateway
        // For now, simulate successful payment
        
        try {
            // Validate payment data
            if (!isset($paymentData['amount']) || $paymentData['amount'] <= 0) {
                throw new Exception("Invalid payment amount");
            }

            // Process payment (would call payment gateway API)
            $transactionId = 'TXN' . date('Ymd') . Str::random(8);
            
            // Log transaction
            if (class_exists(FinancialTransaction::class)) {
                FinancialTransaction::create([
                    'student_id' => $application->user_id,
                    'transaction_type' => $type,
                    'amount' => $paymentData['amount'],
                    'transaction_id' => $transactionId,
                    'payment_method' => $paymentData['payment_method'] ?? 'credit_card',
                    'status' => 'completed',
                    'processed_at' => now(),
                ]);
            }

            return [
                'status' => 'success',
                'transaction_id' => $transactionId,
                'amount' => $paymentData['amount'],
                'processed_at' => now()->toIso8601String(),
            ];

        } catch (Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate receipt number
     */
    private function generateReceiptNumber(): string
    {
        return 'RCP' . date('Ymd') . Str::random(6);
    }

    /**
     * Process deposit refund
     */
    private function processDepositRefund(EnrollmentConfirmation $enrollment, string $reason): void
    {
        if (!$enrollment->deposit_paid) {
            return;
        }

        try {
            // Calculate refund amount based on policy
            $refundAmount = $this->calculateRefundAmount($enrollment);
            
            if ($refundAmount > 0) {
                ApplicationFee::create([
                    'application_id' => $enrollment->application_id,
                    'fee_type' => 'enrollment_deposit_refund',
                    'amount' => -$refundAmount, // Negative for refund
                    'status' => 'refunded',
                    'notes' => "Refund due to: {$reason}",
                    'refunded_date' => now(),
                ]);
                
                Log::info('Deposit refund processed', [
                    'enrollment_id' => $enrollment->id,
                    'amount' => $refundAmount,
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to process deposit refund', [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calculate refund amount based on policy
     */
    private function calculateRefundAmount(EnrollmentConfirmation $enrollment): float
    {
        // Example refund policy
        $daysSincePayment = Carbon::parse($enrollment->deposit_paid_date)->diffInDays(now());
        
        if ($daysSincePayment <= 7) {
            return $enrollment->deposit_amount; // Full refund within 7 days
        } elseif ($daysSincePayment <= 14) {
            return $enrollment->deposit_amount * 0.5; // 50% refund within 14 days
        } else {
            return 0; // No refund after 14 days
        }
    }

    /**
     * Release enrollment spot for waitlist
     */
    private function releaseEnrollmentSpot(AdmissionApplication $application): void
    {
        // This would trigger waitlist movement
        Log::info('Enrollment spot released', [
            'program_id' => $application->program_id,
            'term_id' => $application->term_id,
        ]);
        
        // Trigger waitlist service to offer spot to next candidate
        // WaitlistManagementService::offerNextSpot($application->program_id, $application->term_id);
    }

    /**
     * Create student financial account
     */
    private function createStudentFinancialAccount(Student $student): void
    {
        if (class_exists(StudentAccount::class)) {
            StudentAccount::create([
                'student_id' => $student->id,
                'account_number' => 'SA' . $student->student_id,
                'balance' => 0,
                'status' => 'active',
            ]);
        }
    }

    /**
     * Transfer academic records from application
     */
    private function transferAcademicRecords(EnrollmentConfirmation $enrollment, Student $student): void
    {
        $application = $enrollment->application;
        
        // Transfer test scores
        if ($application->test_scores) {
            $student->test_scores = $application->test_scores;
        }
        
        // Transfer GPA
        if ($application->previous_gpa) {
            $student->transfer_gpa = $application->previous_gpa;
        }
        
        // Transfer other academic data
        $student->previous_institution = $application->previous_institution;
        $student->previous_degree = $application->previous_degree;
        
        $student->save();
    }

    /**
     * Transfer documents from application to student
     */
    private function transferDocuments(int $applicationId, int $studentId): void
    {
        // Update document ownership
        DB::table('application_documents')
            ->where('application_id', $applicationId)
            ->update(['student_id' => $studentId]);
    }

    /**
     * Setup initial registration for new student
     */
    private function setupInitialRegistration(Student $student, int $termId): void
    {
        // This would create initial course registration
        // Could auto-register for required freshman courses
        Log::info('Initial registration setup', [
            'student_id' => $student->student_id,
            'term_id' => $termId,
        ]);
    }

    /**
     * Update enrollment statistics
     */
    private function updateEnrollmentStatistics(int $programId, int $termId): void
    {
        $stats = $this->trackEnrollmentYield($termId, $programId);
        
        // Cache statistics
        cache()->put(
            "enrollment_stats_{$termId}_{$programId}",
            $stats,
            now()->addHours(1)
        );
    }

    /**
     * Predict yield based on historical data
     */
    private function predictYield(int $termId, ?int $programId, array $currentStats): array
    {
        // Simple prediction based on historical averages
        // In production, this could use ML models
        
        return [
            'predicted_yield_rate' => 35.0, // Historical average
            'predicted_melt_rate' => 5.0,
            'confidence_interval' => [30.0, 40.0],
            'factors' => [
                'historical_average' => 35.0,
                'current_trend' => $currentStats['yield_rate'],
                'days_until_deadline' => 15,
            ],
        ];
    }

    /**
     * Log enrollment action
     */
    private function logEnrollmentAction(AdmissionApplication $application, string $action, string $description): void
    {
        if (class_exists(ApplicationNote::class)) {
            ApplicationNote::create([
                'application_id' => $application->id,
                'note' => $description,
                'type' => 'enrollment',
                'action' => $action,
                'created_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Send notifications
     */
    private function sendOfferCommunication(AdmissionApplication $application, EnrollmentConfirmation $enrollment): void
    {
        ApplicationCommunication::create([
            'application_id' => $application->id,
            'communication_type' => 'email',
            'direction' => 'outbound',
            'subject' => 'Admission Offer - Action Required',
            'message' => $this->getOfferMessage($application, $enrollment),
            'recipient_email' => $application->email,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    private function sendEnrollmentConfirmation(AdmissionApplication $application, EnrollmentConfirmation $enrollment): void
    {
        ApplicationCommunication::create([
            'application_id' => $application->id,
            'communication_type' => 'email',
            'direction' => 'outbound',
            'subject' => 'Enrollment Confirmed - Welcome to ' . ($application->program->name ?? 'Our University'),
            'message' => $this->getConfirmationMessage($application, $enrollment),
            'recipient_email' => $application->email,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    private function sendDeclineAcknowledgment(AdmissionApplication $application, string $reason): void
    {
        ApplicationCommunication::create([
            'application_id' => $application->id,
            'communication_type' => 'email',
            'direction' => 'outbound',
            'subject' => 'Enrollment Declined - Thank You',
            'message' => "We have received your decision to decline our admission offer. 
                         We wish you the best in your future academic endeavors. Reason provided: {$reason}",
            'recipient_email' => $application->email,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    private function sendDepositConfirmation(AdmissionApplication $application, EnrollmentConfirmation $enrollment, array $transaction): void
    {
        ApplicationCommunication::create([
            'application_id' => $application->id,
            'communication_type' => 'email',
            'direction' => 'outbound',
            'subject' => 'Enrollment Deposit Received - Receipt #' . $enrollment->deposit_receipt_number,
            'message' => "Your enrollment deposit has been successfully processed. 
                         Transaction ID: {$transaction['transaction_id']}
                         Amount: \${$enrollment->deposit_amount}
                         Receipt Number: {$enrollment->deposit_receipt_number}",
            'recipient_email' => $application->email,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    private function sendAccountCreationNotification(Student $student, User $user): void
    {
        // Send email with login credentials
        Log::info('Student account creation notification sent', [
            'student_id' => $student->student_id,
            'email' => $student->email,
        ]);
    }

    private function sendReminderNotification(EnrollmentConfirmation $enrollment): void
    {
        $daysRemaining = now()->diffInDays($enrollment->enrollment_deadline);
        
        ApplicationCommunication::create([
            'application_id' => $enrollment->application_id,
            'communication_type' => 'email',
            'direction' => 'outbound',
            'subject' => "Enrollment Deadline Reminder - {$daysRemaining} days remaining",
            'message' => "This is a reminder that your enrollment deadline is approaching. 
                         Please log in to your portal to accept or decline your admission offer.",
            'recipient_email' => $enrollment->application->email,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    private function sendDepositReminder(EnrollmentConfirmation $enrollment): void
    {
        ApplicationCommunication::create([
            'application_id' => $enrollment->application_id,
            'communication_type' => 'email',
            'direction' => 'outbound',
            'subject' => 'Enrollment Deposit Reminder',
            'message' => "Your enrollment deposit of \${$enrollment->deposit_amount} is due soon. 
                         Please complete your payment to secure your spot.",
            'recipient_email' => $enrollment->application->email,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Get message templates
     */
    private function getOfferMessage(AdmissionApplication $application, EnrollmentConfirmation $enrollment): string
    {
        return "Congratulations! You have been offered admission to {$application->program->name}.
                
                Important Dates:
                - Enrollment Decision Deadline: {$enrollment->enrollment_deadline->format('F d, Y')}
                - Deposit Deadline: {$enrollment->deposit_deadline->format('F d, Y')}
                - Orientation Date: {$enrollment->orientation_date?->format('F d, Y')}
                - Classes Begin: {$enrollment->classes_start_date?->format('F d, Y')}
                
                Required Deposit: \${$enrollment->deposit_amount}
                
                Please log in to your applicant portal to accept or decline this offer.";
    }

    private function getConfirmationMessage(AdmissionApplication $application, EnrollmentConfirmation $enrollment): string
    {
        return "Welcome to {$application->program->name}!
                
                Your enrollment has been confirmed. Your Student ID is: {$enrollment->student_id}
                
                Next Steps:
                1. Complete your enrollment checklist
                2. Submit final transcripts
                3. Complete health and immunization forms
                4. Register for orientation
                5. Apply for housing (if needed)
                
                We look forward to welcoming you to campus!";
    }
}