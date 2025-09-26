<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EntranceExamRegistration extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'entrance_exam_registrations';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'registration_number',
        'exam_id',
        'application_id',
        'student_id',
        'candidate_name',
        'candidate_email',
        'candidate_phone',
        'date_of_birth',
        'registration_status',
        'fee_paid',
        'fee_amount',
        'payment_reference',
        'payment_date',
        'requires_accommodation',
        'accommodation_details',
        'hall_ticket_number',
        'hall_ticket_generated_at',
        'hall_ticket_downloaded'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'payment_date' => 'datetime',
        'hall_ticket_generated_at' => 'datetime',
        'fee_paid' => 'boolean',
        'requires_accommodation' => 'boolean',
        'hall_ticket_downloaded' => 'boolean',
        'accommodation_details' => 'array',
        'fee_amount' => 'decimal:2'
    ];

    /**
     * Standard accommodation types.
     */
    protected static $accommodationTypes = [
        'extra_time' => 'Extra Time (Time and a half)',
        'large_print' => 'Large Print Question Paper',
        'reader' => 'Reader/Scribe Services',
        'separate_room' => 'Separate Testing Room',
        'wheelchair_access' => 'Wheelchair Accessible Venue',
        'breaks' => 'Additional Breaks',
        'medical_equipment' => 'Medical Equipment Allowed',
        'dietary' => 'Dietary Requirements',
        'other' => 'Other Special Requirements'
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate registration number on creation
        static::creating(function ($registration) {
            if (!$registration->registration_number) {
                $registration->registration_number = self::generateRegistrationNumber();
            }
            
            // Set default status
            if (!$registration->registration_status) {
                $registration->registration_status = 'pending';
            }
            
            // Set candidate name from application or student
            if (!$registration->candidate_name) {
                if ($registration->application_id) {
                    $application = AdmissionApplication::find($registration->application_id);
                    if ($application) {
                        $registration->candidate_name = $application->first_name . ' ' . $application->last_name;
                        $registration->candidate_email = $application->email;
                        $registration->candidate_phone = $application->phone_primary;
                        $registration->date_of_birth = $application->date_of_birth;
                    }
                } elseif ($registration->student_id) {
                    $student = Student::find($registration->student_id);
                    if ($student) {
                        $registration->candidate_name = $student->first_name . ' ' . $student->last_name;
                        $registration->candidate_email = $student->email;
                        $registration->candidate_phone = $student->phone;
                        $registration->date_of_birth = $student->date_of_birth;
                    }
                }
            }
        });

        // Generate hall ticket when confirmed
        static::updating(function ($registration) {
            if ($registration->isDirty('registration_status') && 
                $registration->registration_status === 'confirmed' && 
                !$registration->hall_ticket_number) {
                $registration->hall_ticket_number = self::generateHallTicketNumber($registration->exam_id);
                $registration->hall_ticket_generated_at = now();
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the exam for this registration.
     */
    public function exam()
    {
        return $this->belongsTo(EntranceExam::class, 'exam_id');
    }

    /**
     * Get the application if linked.
     */
    public function application()
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }

    /**
     * Get the student if linked.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Get the seat allocation for this registration.
     */
    public function seatAllocation()
    {
        return $this->hasOne(ExamSeatAllocation::class, 'registration_id');
    }

    /**
     * Get the exam response for this registration.
     */
    public function examResponse()
    {
        return $this->hasOne(ExamResponse::class, 'registration_id');
    }

    /**
     * Get the exam result for this registration.
     */
    public function examResult()
    {
        return $this->hasOne(EntranceExamResult::class, 'registration_id');
    }

    /**
     * Get answer key challenges by this candidate.
     */
    public function answerKeyChallenges()
    {
        return $this->hasMany(AnswerKeyChallenge::class, 'registration_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for confirmed registrations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('registration_status', 'confirmed');
    }

    /**
     * Scope for pending registrations.
     */
    public function scopePending($query)
    {
        return $query->where('registration_status', 'pending');
    }

    /**
     * Scope for paid registrations.
     */
    public function scopePaid($query)
    {
        return $query->where('fee_paid', true);
    }

    /**
     * Scope for registrations requiring accommodation.
     */
    public function scopeRequiringAccommodation($query)
    {
        return $query->where('requires_accommodation', true);
    }

    /**
     * Scope for registrations with hall tickets.
     */
    public function scopeWithHallTicket($query)
    {
        return $query->whereNotNull('hall_ticket_number');
    }

    /**
     * Helper Methods
     */

    /**
     * Generate unique registration number.
     */
    public static function generateRegistrationNumber(): string
    {
        $year = date('Y');
        
        $lastRegistration = self::where('registration_number', 'like', "REG-{$year}-%")
            ->orderBy('registration_number', 'desc')
            ->first();
        
        if ($lastRegistration) {
            $lastNumber = intval(substr($lastRegistration->registration_number, -6));
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }
        
        return "REG-{$year}-{$newNumber}";
    }

    /**
     * Generate unique hall ticket number.
     */
    public static function generateHallTicketNumber($examId): string
    {
        $exam = EntranceExam::find($examId);
        $examCode = $exam ? substr($exam->exam_code, -3) : '000';
        
        $lastTicket = self::where('exam_id', $examId)
            ->whereNotNull('hall_ticket_number')
            ->orderBy('hall_ticket_number', 'desc')
            ->first();
        
        if ($lastTicket) {
            $lastNumber = intval(substr($lastTicket->hall_ticket_number, -5));
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }
        
        return "HT{$examCode}-{$newNumber}";
    }

    /**
     * Confirm the registration.
     */
    public function confirm(): bool
    {
        if (!$this->canBeConfirmed()) {
            return false;
        }
        
        $this->registration_status = 'confirmed';
        
        if (!$this->hall_ticket_number) {
            $this->hall_ticket_number = self::generateHallTicketNumber($this->exam_id);
            $this->hall_ticket_generated_at = now();
        }
        
        return $this->save();
    }

    /**
     * Cancel the registration.
     */
    public function cancel($reason = null): bool
    {
        if ($this->registration_status === 'cancelled') {
            return false;
        }
        
        $this->registration_status = 'cancelled';
        
        // Release any seat allocation
        if ($this->seatAllocation) {
            $this->seatAllocation->delete();
        }
        
        return $this->save();
    }

    /**
     * Process payment for registration.
     */
    public function processPayment($amount, $reference): bool
    {
        $this->fee_paid = true;
        $this->fee_amount = $amount;
        $this->payment_reference = $reference;
        $this->payment_date = now();
        
        // Auto-confirm if payment successful
        if ($this->registration_status === 'pending') {
            $this->registration_status = 'confirmed';
            
            if (!$this->hall_ticket_number) {
                $this->hall_ticket_number = self::generateHallTicketNumber($this->exam_id);
                $this->hall_ticket_generated_at = now();
            }
        }
        
        return $this->save();
    }

    /**
     * Check if registration can be confirmed.
     */
    public function canBeConfirmed(): bool
    {
        // Check if exam registration is still open
        if (!$this->exam || !$this->exam->isRegistrationOpen()) {
            return false;
        }
        
        // Check if fee is paid (if required)
        if ($this->exam->fee_required && !$this->fee_paid) {
            return false;
        }
        
        return $this->registration_status === 'pending';
    }

    /**
     * Mark hall ticket as downloaded.
     */
    public function markHallTicketDownloaded(): bool
    {
        if (!$this->hall_ticket_downloaded) {
            $this->hall_ticket_downloaded = true;
            return $this->save();
        }
        
        return false;
    }

    /**
     * Add accommodation request.
     */
    public function addAccommodation($type, $details = null): bool
    {
        $accommodations = $this->accommodation_details ?? [];
        
        $accommodations[] = [
            'type' => $type,
            'details' => $details,
            'requested_at' => now()->toDateTimeString(),
            'approved' => false,
            'approved_by' => null,
            'approved_at' => null
        ];
        
        $this->accommodation_details = $accommodations;
        $this->requires_accommodation = true;
        
        return $this->save();
    }

    /**
     * Approve accommodation request.
     */
    public function approveAccommodation($type, $approvedBy = null): bool
    {
        $accommodations = $this->accommodation_details ?? [];
        
        foreach ($accommodations as &$accommodation) {
            if ($accommodation['type'] === $type) {
                $accommodation['approved'] = true;
                $accommodation['approved_by'] = $approvedBy ?? auth()->id();
                $accommodation['approved_at'] = now()->toDateTimeString();
            }
        }
        
        $this->accommodation_details = $accommodations;
        
        return $this->save();
    }

    /**
     * Get approved accommodations.
     */
    public function getApprovedAccommodations(): array
    {
        if (!$this->accommodation_details) {
            return [];
        }
        
        return array_filter($this->accommodation_details, function ($accommodation) {
            return $accommodation['approved'] ?? false;
        });
    }

    /**
     * Check if candidate has appeared for exam.
     */
    public function hasAppeared(): bool
    {
        return $this->examResponse && 
               $this->examResponse->status !== 'not_started';
    }

    /**
     * Check if candidate has completed exam.
     */
    public function hasCompleted(): bool
    {
        return $this->examResponse && 
               in_array($this->examResponse->status, ['submitted', 'auto_submitted']);
    }

    /**
     * Get exam status.
     */
    public function getExamStatus(): string
    {
        if (!$this->examResponse) {
            return 'not_started';
        }
        
        return $this->examResponse->status;
    }

    /**
     * Check if result is available.
     */
    public function isResultAvailable(): bool
    {
        return $this->examResult && 
               $this->examResult->is_published;
    }

    /**
     * Get result summary.
     */
    public function getResultSummary(): ?array
    {
        if (!$this->examResult) {
            return null;
        }
        
        return [
            'score' => $this->examResult->final_score,
            'percentage' => $this->examResult->percentage,
            'rank' => $this->examResult->overall_rank,
            'status' => $this->examResult->result_status,
            'qualified' => $this->examResult->is_qualified
        ];
    }

    /**
     * Generate hall ticket QR code.
     */
    public function generateHallTicketQR(): string
    {
        $data = [
            'registration_number' => $this->registration_number,
            'hall_ticket_number' => $this->hall_ticket_number,
            'exam_code' => $this->exam->exam_code,
            'candidate_name' => $this->candidate_name
        ];
        
        return base64_encode(
            QrCode::format('png')
                ->size(200)
                ->generate(json_encode($data))
        );
    }

    /**
     * Get accommodation type label.
     */
    public static function getAccommodationTypeLabel($type): string
    {
        return self::$accommodationTypes[$type] ?? ucwords(str_replace('_', ' ', $type));
    }

    /**
     * Get all available accommodation types.
     */
    public static function getAccommodationTypes(): array
    {
        return self::$accommodationTypes;
    }

    /**
     * Check if registration is expired.
     */
    public function isExpired(): bool
    {
        if ($this->registration_status === 'expired') {
            return true;
        }
        
        // Check if exam registration period has ended
        if ($this->exam && $this->exam->registration_end_date) {
            return $this->exam->registration_end_date < now();
        }
        
        return false;
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->registration_status) {
            'pending' => 'yellow',
            'confirmed' => 'green',
            'cancelled' => 'red',
            'expired' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Calculate days until exam.
     */
    public function getDaysUntilExam(): ?int
    {
        if (!$this->exam) {
            return null;
        }
        
        if ($this->exam->exam_date) {
            return now()->diffInDays($this->exam->exam_date, false);
        }
        
        if ($this->exam->exam_window_start) {
            return now()->diffInDays($this->exam->exam_window_start, false);
        }
        
        return null;
    }

    /**
     * Get registration completion checklist.
     */
    public function getCompletionChecklist(): array
    {
        return [
            'registration_submitted' => [
                'completed' => true,
                'label' => 'Registration Submitted'
            ],
            'fee_paid' => [
                'completed' => $this->fee_paid,
                'label' => 'Exam Fee Paid',
                'amount' => $this->fee_amount
            ],
            'hall_ticket_generated' => [
                'completed' => !is_null($this->hall_ticket_number),
                'label' => 'Hall Ticket Generated'
            ],
            'seat_allocated' => [
                'completed' => !is_null($this->seatAllocation),
                'label' => 'Exam Center & Seat Allocated'
            ],
            'accommodation_approved' => [
                'completed' => !$this->requires_accommodation || count($this->getApprovedAccommodations()) > 0,
                'label' => 'Special Accommodations Approved'
            ]
        ];
    }

    /**
     * Send registration confirmation.
     */
    public function sendConfirmation(): void
    {
        // This would send email/SMS confirmation
        // Implementation would use notification service
    }

    /**
     * Send exam reminder.
     */
    public function sendExamReminder(): void
    {
        // This would send reminder notification
        // Implementation would use notification service
    }

    /**
     * Generate registration summary.
     */
    public function generateSummary(): array
    {
        return [
            'registration_number' => $this->registration_number,
            'hall_ticket_number' => $this->hall_ticket_number,
            'exam' => [
                'code' => $this->exam->exam_code ?? null,
                'name' => $this->exam->exam_name ?? null,
                'date' => $this->exam->exam_date?->format('Y-m-d'),
                'time' => $this->exam->exam_start_time
            ],
            'candidate' => [
                'name' => $this->candidate_name,
                'email' => $this->candidate_email,
                'phone' => $this->candidate_phone,
                'dob' => $this->date_of_birth?->format('Y-m-d')
            ],
            'status' => $this->registration_status,
            'payment' => [
                'paid' => $this->fee_paid,
                'amount' => $this->fee_amount,
                'reference' => $this->payment_reference,
                'date' => $this->payment_date?->format('Y-m-d')
            ],
            'accommodation' => [
                'required' => $this->requires_accommodation,
                'approved' => count($this->getApprovedAccommodations()),
                'details' => $this->getApprovedAccommodations()
            ],
            'seat_allocation' => $this->seatAllocation ? [
                'center' => $this->seatAllocation->center->center_name ?? null,
                'room' => $this->seatAllocation->room_number,
                'seat' => $this->seatAllocation->seat_number
            ] : null,
            'exam_status' => $this->getExamStatus(),
            'result' => $this->getResultSummary(),
            'days_until_exam' => $this->getDaysUntilExam()
        ];
    }
}