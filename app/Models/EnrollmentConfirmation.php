<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EnrollmentConfirmation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'enrollment_confirmations';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'decision',
        'decision_date',
        'decision_reason',
        'deposit_paid',
        'deposit_amount',
        'deposit_paid_date',
        'deposit_transaction_id',
        'health_form_submitted',
        'immunization_submitted',
        'housing_applied',
        'orientation_registered',
        'id_card_processed',
        'enrollment_deadline',
        'orientation_date',
        'move_in_date',
        'classes_start_date',
        'student_account_created',
        'student_id',
        'student_record_id'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'decision_date' => 'datetime',
        'deposit_paid_date' => 'datetime',
        'enrollment_deadline' => 'date',
        'orientation_date' => 'date',
        'move_in_date' => 'date',
        'classes_start_date' => 'date',
        'deposit_paid' => 'boolean',
        'health_form_submitted' => 'boolean',
        'immunization_submitted' => 'boolean',
        'housing_applied' => 'boolean',
        'orientation_registered' => 'boolean',
        'id_card_processed' => 'boolean',
        'student_account_created' => 'boolean',
        'deposit_amount' => 'decimal:2'
    ];

    /**
     * Required items for enrollment completion.
     */
    protected static $requiredItems = [
        'deposit_paid' => 'Enrollment Deposit',
        'health_form_submitted' => 'Health Form',
        'immunization_submitted' => 'Immunization Records',
        'orientation_registered' => 'Orientation Registration'
    ];

    /**
     * Optional enrollment items.
     */
    protected static $optionalItems = [
        'housing_applied' => 'Housing Application',
        'id_card_processed' => 'ID Card Processing'
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Set decision date when decision is made
        static::updating(function ($confirmation) {
            if ($confirmation->isDirty('decision') && !$confirmation->decision_date) {
                $confirmation->decision_date = now();
            }
            
            // Generate student ID when confirming enrollment
            if ($confirmation->isDirty('decision') && 
                $confirmation->decision === 'accept' && 
                !$confirmation->student_id) {
                $confirmation->student_id = self::generateStudentId();
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the application for this enrollment confirmation.
     */
    public function application()
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }

    /**
     * Get the student record if created.
     */
    public function studentRecord()
    {
        return $this->belongsTo(Student::class, 'student_record_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for accepted enrollments.
     */
    public function scopeAccepted($query)
    {
        return $query->where('decision', 'accept');
    }

    /**
     * Scope for declined enrollments.
     */
    public function scopeDeclined($query)
    {
        return $query->where('decision', 'decline');
    }

    /**
     * Scope for pending enrollments.
     */
    public function scopePending($query)
    {
        return $query->where('decision', 'pending');
    }

    /**
     * Scope for enrollments with paid deposits.
     */
    public function scopeDepositPaid($query)
    {
        return $query->where('deposit_paid', true);
    }

    /**
     * Scope for complete enrollments.
     */
    public function scopeComplete($query)
    {
        return $query->where('student_account_created', true);
    }

    /**
     * Scope for enrollments approaching deadline.
     */
    public function scopeApproachingDeadline($query, $days = 7)
    {
        return $query->where('decision', 'pending')
            ->where('enrollment_deadline', '<=', now()->addDays($days))
            ->where('enrollment_deadline', '>', now());
    }

    /**
     * Scope for expired enrollments.
     */
    public function scopeExpired($query)
    {
        return $query->where('decision', 'pending')
            ->where('enrollment_deadline', '<', now());
    }

    /**
     * Helper Methods
     */

    /**
     * Accept the enrollment offer.
     */
    public function accept($reason = null): bool
    {
        if (!$this->canAccept()) {
            return false;
        }
        
        $this->decision = 'accept';
        $this->decision_date = now();
        $this->decision_reason = $reason;
        
        // Generate student ID if not exists
        if (!$this->student_id) {
            $this->student_id = self::generateStudentId();
        }
        
        $saved = $this->save();
        
        // Update application status
        if ($saved) {
            $this->application->update([
                'enrollment_confirmed' => true,
                'enrollment_confirmation_date' => now()
            ]);
        }
        
        return $saved;
    }

    /**
     * Decline the enrollment offer.
     */
    public function decline($reason = null): bool
    {
        if (!$this->canDecline()) {
            return false;
        }
        
        $this->decision = 'decline';
        $this->decision_date = now();
        $this->decision_reason = $reason;
        
        $saved = $this->save();
        
        // Update application status
        if ($saved) {
            $this->application->update([
                'enrollment_declined' => true,
                'enrollment_declined_date' => now(),
                'enrollment_declined_reason' => $reason
            ]);
        }
        
        return $saved;
    }

    /**
     * Defer the enrollment.
     */
    public function defer($reason = null): bool
    {
        $this->decision = 'defer';
        $this->decision_date = now();
        $this->decision_reason = $reason;
        
        return $this->save();
    }

    /**
     * Check if enrollment can be accepted.
     */
    public function canAccept(): bool
    {
        return $this->decision === 'pending' && 
               $this->enrollment_deadline >= now();
    }

    /**
     * Check if enrollment can be declined.
     */
    public function canDecline(): bool
    {
        return $this->decision === 'pending';
    }

    /**
     * Process deposit payment.
     */
    public function processDeposit($amount, $transactionId): bool
    {
        $this->deposit_paid = true;
        $this->deposit_amount = $amount;
        $this->deposit_paid_date = now();
        $this->deposit_transaction_id = $transactionId;
        
        return $this->save();
    }

    /**
     * Check if enrollment is complete.
     */
    public function isComplete(): bool
    {
        foreach (array_keys(self::$requiredItems) as $item) {
            if (!$this->$item) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get completion percentage.
     */
    public function getCompletionPercentage(): int
    {
        $totalItems = count(self::$requiredItems) + count(self::$optionalItems);
        $completedItems = 0;
        
        foreach (array_keys(self::$requiredItems) as $item) {
            if ($this->$item) {
                $completedItems++;
            }
        }
        
        foreach (array_keys(self::$optionalItems) as $item) {
            if ($this->$item) {
                $completedItems++;
            }
        }
        
        return round(($completedItems / $totalItems) * 100);
    }

    /**
     * Get pending requirements.
     */
    public function getPendingRequirements(): array
    {
        $pending = [];
        
        foreach (self::$requiredItems as $field => $label) {
            if (!$this->$field) {
                $pending[$field] = $label;
            }
        }
        
        return $pending;
    }

    /**
     * Get completed requirements.
     */
    public function getCompletedRequirements(): array
    {
        $completed = [];
        
        foreach (array_merge(self::$requiredItems, self::$optionalItems) as $field => $label) {
            if ($this->$field) {
                $completed[$field] = $label;
            }
        }
        
        return $completed;
    }

    /**
     * Generate unique student ID.
     */
    public static function generateStudentId(): string
    {
        $year = date('y'); // Two-digit year
        
        // Get the last student ID for this year
        $lastStudent = self::where('student_id', 'like', $year . '%')
            ->orderBy('student_id', 'desc')
            ->first();
        
        if ($lastStudent && $lastStudent->student_id) {
            $lastNumber = intval(substr($lastStudent->student_id, 2));
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }
        
        return $year . $newNumber; // Format: YYXXXXXX (e.g., 25000001)
    }

    /**
     * Create student account.
     */
    public function createStudentAccount(): bool
    {
        if ($this->student_account_created) {
            return true;
        }
        
        if (!$this->isComplete()) {
            return false;
        }
        
        // This would typically create the actual student record
        // For now, we'll just mark it as created
        $this->student_account_created = true;
        
        return $this->save();
    }

    /**
     * Get days until deadline.
     */
    public function getDaysUntilDeadline(): ?int
    {
        if (!$this->enrollment_deadline) {
            return null;
        }
        
        return now()->diffInDays($this->enrollment_deadline, false);
    }

    /**
     * Check if deadline has passed.
     */
    public function isExpired(): bool
    {
        return $this->enrollment_deadline && $this->enrollment_deadline < now();
    }

    /**
     * Get decision color for UI.
     */
    public function getDecisionColor(): string
    {
        return match($this->decision) {
            'accept' => 'green',
            'decline' => 'red',
            'defer' => 'yellow',
            'pending' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get enrollment checklist.
     */
    public function getChecklist(): array
    {
        $checklist = [];
        
        // Required items
        foreach (self::$requiredItems as $field => $label) {
            $checklist[] = [
                'label' => $label,
                'field' => $field,
                'required' => true,
                'completed' => (bool) $this->$field,
                'type' => 'required'
            ];
        }
        
        // Optional items
        foreach (self::$optionalItems as $field => $label) {
            $checklist[] = [
                'label' => $label,
                'field' => $field,
                'required' => false,
                'completed' => (bool) $this->$field,
                'type' => 'optional'
            ];
        }
        
        return $checklist;
    }

    /**
     * Send reminder notification.
     */
    public function sendReminder(): void
    {
        // This would send an email/SMS reminder about pending items
        // Implementation would use notification service
    }

    /**
     * Generate enrollment summary.
     */
    public function generateSummary(): array
    {
        return [
            'decision' => $this->decision,
            'decision_date' => $this->decision_date?->format('Y-m-d'),
            'student_id' => $this->student_id,
            'deadline' => $this->enrollment_deadline?->format('Y-m-d'),
            'days_remaining' => $this->getDaysUntilDeadline(),
            'completion_percentage' => $this->getCompletionPercentage(),
            'deposit' => [
                'paid' => $this->deposit_paid,
                'amount' => $this->deposit_amount,
                'date' => $this->deposit_paid_date?->format('Y-m-d'),
                'transaction_id' => $this->deposit_transaction_id
            ],
            'requirements' => [
                'completed' => $this->getCompletedRequirements(),
                'pending' => $this->getPendingRequirements()
            ],
            'important_dates' => [
                'orientation' => $this->orientation_date?->format('Y-m-d'),
                'move_in' => $this->move_in_date?->format('Y-m-d'),
                'classes_start' => $this->classes_start_date?->format('Y-m-d')
            ],
            'account_created' => $this->student_account_created
        ];
    }
}