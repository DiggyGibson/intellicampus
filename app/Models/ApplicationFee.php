<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApplicationFee extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'application_fees';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'fee_type',
        'amount',
        'currency',
        'status',
        'payment_method',
        'transaction_id',
        'receipt_number',
        'due_date',
        'paid_date',
        'refunded_date',
        'notes',
        'processed_by'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'datetime',
        'paid_date' => 'datetime',
        'refunded_date' => 'datetime'
    ];

    /**
     * Fee amounts by type (in USD).
     */
    protected static $standardFees = [
        'application_fee' => 75.00,
        'enrollment_deposit' => 500.00,
        'housing_deposit' => 300.00,
        'orientation_fee' => 150.00,
        'document_evaluation_fee' => 100.00
    ];

    /**
     * Refund policies by fee type.
     */
    protected static $refundPolicies = [
        'application_fee' => ['refundable' => false, 'conditions' => 'Non-refundable'],
        'enrollment_deposit' => ['refundable' => true, 'conditions' => 'Refundable until 30 days before term start'],
        'housing_deposit' => ['refundable' => true, 'conditions' => 'Refundable with 60 days notice'],
        'orientation_fee' => ['refundable' => true, 'conditions' => 'Refundable until orientation date'],
        'document_evaluation_fee' => ['refundable' => false, 'conditions' => 'Non-refundable once evaluation begins']
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate receipt number on payment
        static::updating(function ($fee) {
            if ($fee->isDirty('status') && $fee->status === 'paid' && !$fee->receipt_number) {
                $fee->receipt_number = self::generateReceiptNumber();
                if (!$fee->paid_date) {
                    $fee->paid_date = now();
                }
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the application for this fee.
     */
    public function application()
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }

    /**
     * Get the user who processed this fee.
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scopes
     */

    /**
     * Scope for pending fees.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for paid fees.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for waived fees.
     */
    public function scopeWaived($query)
    {
        return $query->where('status', 'waived');
    }

    /**
     * Scope for refunded fees.
     */
    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    /**
     * Scope for overdue fees.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
            ->where('due_date', '<', now());
    }

    /**
     * Scope for fees by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('fee_type', $type);
    }

    /**
     * Helper Methods
     */

    /**
     * Process payment for the fee.
     */
    public function processPayment($paymentMethod, $transactionId, $processedBy = null): bool
    {
        if (!$this->canBePaid()) {
            return false;
        }
        
        $this->status = 'paid';
        $this->payment_method = $paymentMethod;
        $this->transaction_id = $transactionId;
        $this->paid_date = now();
        $this->processed_by = $processedBy ?? auth()->id();
        $this->receipt_number = self::generateReceiptNumber();
        
        $saved = $this->save();
        
        // Update application if this is the application fee
        if ($saved && $this->fee_type === 'application_fee') {
            $this->application->update([
                'application_fee_paid' => true,
                'application_fee_amount' => $this->amount,
                'application_fee_date' => $this->paid_date,
                'application_fee_receipt' => $this->receipt_number
            ]);
        }
        
        // Update enrollment confirmation if this is enrollment deposit
        if ($saved && $this->fee_type === 'enrollment_deposit') {
            $enrollment = $this->application->enrollmentConfirmation;
            if ($enrollment) {
                $enrollment->processDeposit($this->amount, $this->transaction_id);
            }
        }
        
        return $saved;
    }

    /**
     * Waive the fee.
     */
    public function waive($reason = null, $processedBy = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }
        
        $this->status = 'waived';
        $this->payment_method = 'waiver';
        $this->notes = $reason ?? 'Fee waived';
        $this->processed_by = $processedBy ?? auth()->id();
        
        $saved = $this->save();
        
        // Update application if this is the application fee
        if ($saved && $this->fee_type === 'application_fee') {
            $this->application->update([
                'fee_waiver_approved' => true,
                'fee_waiver_reason' => $reason
            ]);
        }
        
        return $saved;
    }

    /**
     * Process refund for the fee.
     */
    public function refund($reason = null, $processedBy = null): bool
    {
        if (!$this->canBeRefunded()) {
            return false;
        }
        
        $this->status = 'refunded';
        $this->refunded_date = now();
        $this->processed_by = $processedBy ?? auth()->id();
        
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . ' | ' : '') . 'Refund: ' . $reason;
        }
        
        return $this->save();
    }

    /**
     * Cancel the fee.
     */
    public function cancel($reason = null): bool
    {
        if (!in_array($this->status, ['pending', 'paid'])) {
            return false;
        }
        
        $this->status = 'cancelled';
        $this->notes = $reason ?? 'Fee cancelled';
        
        return $this->save();
    }

    /**
     * Check if fee can be paid.
     */
    public function canBePaid(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if fee can be refunded.
     */
    public function canBeRefunded(): bool
    {
        if ($this->status !== 'paid') {
            return false;
        }
        
        $policy = self::$refundPolicies[$this->fee_type] ?? null;
        
        if (!$policy || !$policy['refundable']) {
            return false;
        }
        
        // Check refund deadline based on fee type
        if ($this->fee_type === 'enrollment_deposit') {
            $term = $this->application->term;
            if ($term && $term->start_date) {
                return now()->diffInDays($term->start_date, false) > 30;
            }
        }
        
        return true;
    }

    /**
     * Check if fee is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && 
               $this->due_date && 
               $this->due_date < now();
    }

    /**
     * Get days until due date.
     */
    public function getDaysUntilDue(): ?int
    {
        if (!$this->due_date || $this->status !== 'pending') {
            return null;
        }
        
        return now()->diffInDays($this->due_date, false);
    }

    /**
     * Generate unique receipt number.
     */
    public static function generateReceiptNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        $lastReceipt = self::where('receipt_number', 'like', "RCP-{$year}{$month}%")
            ->orderBy('receipt_number', 'desc')
            ->first();
        
        if ($lastReceipt && $lastReceipt->receipt_number) {
            $lastNumber = intval(substr($lastReceipt->receipt_number, -5));
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }
        
        return "RCP-{$year}{$month}-{$newNumber}";
    }

    /**
     * Get standard fee amount for a type.
     */
    public static function getStandardAmount($feeType): ?float
    {
        return self::$standardFees[$feeType] ?? null;
    }

    /**
     * Get refund policy for fee type.
     */
    public function getRefundPolicy(): array
    {
        return self::$refundPolicies[$this->fee_type] ?? [
            'refundable' => false,
            'conditions' => 'Please contact admissions office'
        ];
    }

    /**
     * Get fee type label.
     */
    public function getTypeLabel(): string
    {
        return match($this->fee_type) {
            'application_fee' => 'Application Fee',
            'enrollment_deposit' => 'Enrollment Deposit',
            'housing_deposit' => 'Housing Deposit',
            'orientation_fee' => 'Orientation Fee',
            'document_evaluation_fee' => 'Document Evaluation Fee',
            default => ucwords(str_replace('_', ' ', $this->fee_type))
        };
    }

    /**
     * Get payment method label.
     */
    public function getPaymentMethodLabel(): string
    {
        return match($this->payment_method) {
            'credit_card' => 'Credit Card',
            'debit_card' => 'Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'mobile_money' => 'Mobile Money',
            'cash' => 'Cash',
            'check' => 'Check',
            'waiver' => 'Fee Waiver',
            default => ucwords(str_replace('_', ' ', $this->payment_method ?? 'Not Paid'))
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'paid' => 'green',
            'waived' => 'blue',
            'refunded' => 'purple',
            'cancelled' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Calculate total fees for an application.
     */
    public static function calculateTotalForApplication($applicationId): array
    {
        $fees = self::where('application_id', $applicationId)->get();
        
        return [
            'total_assessed' => $fees->sum('amount'),
            'total_paid' => $fees->where('status', 'paid')->sum('amount'),
            'total_pending' => $fees->where('status', 'pending')->sum('amount'),
            'total_waived' => $fees->where('status', 'waived')->sum('amount'),
            'total_refunded' => $fees->where('status', 'refunded')->sum('amount'),
            'balance_due' => $fees->where('status', 'pending')->sum('amount')
        ];
    }

    /**
     * Create standard fees for an application.
     */
    public static function createStandardFees($applicationId, $applicationType = 'freshman'): void
    {
        // Application fee (always required)
        self::create([
            'application_id' => $applicationId,
            'fee_type' => 'application_fee',
            'amount' => self::$standardFees['application_fee'],
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(30)
        ]);
        
        // Document evaluation fee for international students
        if ($applicationType === 'international') {
            self::create([
                'application_id' => $applicationId,
                'fee_type' => 'document_evaluation_fee',
                'amount' => self::$standardFees['document_evaluation_fee'],
                'currency' => 'USD',
                'status' => 'pending',
                'due_date' => now()->addDays(30)
            ]);
        }
    }

    /**
     * Generate fee summary.
     */
    public function generateSummary(): array
    {
        return [
            'type' => $this->fee_type,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'receipt_number' => $this->receipt_number,
            'dates' => [
                'due' => $this->due_date?->format('Y-m-d'),
                'paid' => $this->paid_date?->format('Y-m-d'),
                'refunded' => $this->refunded_date?->format('Y-m-d')
            ],
            'refund_policy' => $this->getRefundPolicy(),
            'is_overdue' => $this->isOverdue(),
            'days_until_due' => $this->getDaysUntilDue()
        ];
    }
}