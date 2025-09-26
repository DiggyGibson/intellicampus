<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_number',
        'student_id',
        'amount',
        'payment_method',
        'status',
        'reference_number',
        'transaction_id',
        'payment_date',
        'payment_details',
        'notes',
        'processed_by',
        'processed_at',
        'applied_to_account',
        'refunded_amount',
        'refund_date',
        'refund_reason',
        'gateway_response',
        'ip_address',
        'user_agent'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'payment_date' => 'date',
        'refund_date' => 'datetime',
        'processed_at' => 'datetime',
        'payment_details' => 'array',
        'gateway_response' => 'array',
        'applied_to_account' => 'boolean'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'gateway_response',
        'ip_address'
    ];

    /**
     * Payment method options
     */
    const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'check' => 'Check',
        'card' => 'Credit/Debit Card',
        'bank_transfer' => 'Bank Transfer',
        'mobile_money' => 'Mobile Money',
        'financial_aid' => 'Financial Aid',
        'scholarship' => 'Scholarship',
        'wire_transfer' => 'Wire Transfer',
        'online' => 'Online Payment',
        'other' => 'Other'
    ];

    /**
     * Payment status options
     */
    const STATUSES = [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
        'partial_refund' => 'Partially Refunded',
        'disputed' => 'Disputed'
    ];

    /**
     * Boot method for the model
     */
    protected static function boot()
    {
        parent::boot();

        // Generate payment number on creation
        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = self::generatePaymentNumber();
            }

            // Capture request details for audit
            if (request()) {
                $payment->ip_address = request()->ip();
                $payment->user_agent = request()->userAgent();
            }
        });

        // Log payment creation
        static::created(function ($payment) {
            Log::info('Payment created', [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'student_id' => $payment->student_id,
                'amount' => $payment->amount,
                'method' => $payment->payment_method
            ]);

            // Auto-process completed payments (but don't apply twice)
            if ($payment->status === 'completed' && !$payment->applied_to_account) {
                $payment->applyToAccount();
            }
        });

        // Handle status changes
        static::updating(function ($payment) {
            // If status is changing to completed and not yet applied
            if ($payment->isDirty('status') && 
                $payment->status === 'completed' && 
                !$payment->applied_to_account) {
                // This will be applied after the update is saved
            }
        });

        static::updated(function ($payment) {
            // Apply payment if just marked as completed
            if ($payment->wasChanged('status') && 
                $payment->status === 'completed' && 
                !$payment->applied_to_account) {
                $payment->applyToAccount();
            }

            // Log significant changes
            if ($payment->wasChanged(['status', 'amount'])) {
                Log::info('Payment updated', [
                    'payment_id' => $payment->id,
                    'changes' => $payment->getChanges()
                ]);
            }
        });
    }

    /**
     * Relationships
     */
    
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function account()
    {
        return $this->hasOneThrough(
            StudentAccount::class,
            Student::class,
            'id',
            'student_id',
            'student_id',
            'id'
        );
    }

    /**
     * Scopes
     */
    
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForTerm($query, $termId)
    {
        return $query->whereHas('allocations.billingItem', function ($q) use ($termId) {
            $q->where('term_id', $termId);
        });
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeNotApplied($query)
    {
        return $query->where('applied_to_account', false);
    }

    /**
     * Methods
     */
    
    /**
     * Generate unique payment number
     */
    public static function generatePaymentNumber()
    {
        $prefix = 'PAY';
        $timestamp = now()->format('YmdHis');
        $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        
        $paymentNumber = sprintf('%s-%s-%s', $prefix, $timestamp, $random);
        
        // Ensure uniqueness
        while (self::where('payment_number', $paymentNumber)->exists()) {
            $random = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
            $paymentNumber = sprintf('%s-%s-%s', $prefix, $timestamp, $random);
        }
        
        return $paymentNumber;
    }

    /**
     * Apply payment to student account
     */
    public function applyToAccount()
    {
        // Prevent double application
        if ($this->applied_to_account) {
            Log::warning('Attempted to apply payment twice', [
                'payment_id' => $this->id,
                'payment_number' => $this->payment_number
            ]);
            return false;
        }

        // Only apply completed payments
        if ($this->status !== 'completed') {
            Log::warning('Attempted to apply non-completed payment', [
                'payment_id' => $this->id,
                'status' => $this->status
            ]);
            return false;
        }

        DB::transaction(function () {
            // Get or create student account
            $account = StudentAccount::firstOrCreate(
                ['student_id' => $this->student_id],
                [
                    'balance' => 0,
                    'status' => 'active',
                    'credit_limit' => 500
                ]
            );

            // Apply payment to account
            $account->applyPayment($this);

            // Mark as applied
            $this->applied_to_account = true;
            $this->save();

            Log::info('Payment applied to account', [
                'payment_id' => $this->id,
                'student_id' => $this->student_id,
                'amount' => $this->amount
            ]);
        });

        return true;
    }

    /**
     * Process the payment (for manual processing)
     */
    public function process($processedBy = null)
    {
        if ($this->status !== 'pending' && $this->status !== 'processing') {
            return false;
        }

        DB::transaction(function () use ($processedBy) {
            $this->status = 'completed';
            $this->processed_at = now();
            $this->processed_by = $processedBy ?? auth()->id();
            $this->save();

            // Apply to account if not already applied
            if (!$this->applied_to_account) {
                $this->applyToAccount();
            }
        });

        return true;
    }

    /**
     * Cancel the payment
     */
    public function cancel($reason = null)
    {
        if (!in_array($this->status, ['pending', 'processing'])) {
            return false;
        }

        $this->status = 'cancelled';
        $this->notes = $this->notes . "\nCancelled: " . ($reason ?? 'No reason provided');
        $this->save();

        Log::info('Payment cancelled', [
            'payment_id' => $this->id,
            'reason' => $reason
        ]);

        return true;
    }

    /**
     * Refund the payment
     */
    public function refund($amount = null, $reason = null)
    {
        if ($this->status !== 'completed') {
            throw new \Exception('Only completed payments can be refunded');
        }

        $refundAmount = $amount ?? $this->amount;
        
        if ($refundAmount > $this->amount) {
            throw new \Exception('Refund amount cannot exceed payment amount');
        }

        DB::transaction(function () use ($refundAmount, $reason) {
            // Update payment record
            $this->refunded_amount = ($this->refunded_amount ?? 0) + $refundAmount;
            $this->refund_date = now();
            $this->refund_reason = $reason;
            
            if ($this->refunded_amount >= $this->amount) {
                $this->status = 'refunded';
            } else {
                $this->status = 'partial_refund';
            }
            
            $this->save();

            // Create refund transaction in student account
            if ($this->applied_to_account) {
                $account = StudentAccount::where('student_id', $this->student_id)->first();
                if ($account) {
                    $account->addCharge(
                        -$refundAmount,
                        "Refund for payment {$this->payment_number}",
                        null,
                        now()
                    );
                }
            }

            Log::info('Payment refunded', [
                'payment_id' => $this->id,
                'refund_amount' => $refundAmount,
                'reason' => $reason
            ]);
        });

        return true;
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed($reason = null)
    {
        $this->status = 'failed';
        $this->notes = $this->notes . "\nFailed: " . ($reason ?? 'Transaction failed');
        $this->save();

        Log::warning('Payment failed', [
            'payment_id' => $this->id,
            'reason' => $reason
        ]);

        return true;
    }

    /**
     * Generate payment receipt
     */
    public function generateReceipt()
    {
        return [
            'receipt_number' => $this->payment_number,
            'date' => $this->payment_date->format('F d, Y'),
            'time' => $this->created_at->format('g:i A'),
            'student' => [
                'name' => $this->student->full_name,
                'id' => $this->student->student_id,
                'email' => $this->student->email
            ],
            'payment' => [
                'amount' => $this->amount,
                'method' => self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method,
                'reference' => $this->reference_number,
                'transaction_id' => $this->transaction_id,
                'status' => self::STATUSES[$this->status] ?? $this->status
            ],
            'allocations' => $this->allocations->map(function ($allocation) {
                return [
                    'description' => $allocation->billingItem->description,
                    'amount' => $allocation->amount
                ];
            }),
            'processed_by' => $this->processedBy ? $this->processedBy->name : 'System',
            'notes' => $this->notes
        ];
    }

    /**
     * Check if payment is editable
     */
    public function isEditable()
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Check if payment can be refunded
     */
    public function canBeRefunded()
    {
        return $this->status === 'completed' && 
               $this->applied_to_account &&
               ($this->refunded_amount ?? 0) < $this->amount;
    }

    /**
     * Get remaining refundable amount
     */
    public function getRefundableAmount()
    {
        if (!$this->canBeRefunded()) {
            return 0;
        }
        
        return $this->amount - ($this->refunded_amount ?? 0);
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            'refunded' => 'dark',
            'partial_refund' => 'warning',
            'disputed' => 'danger'
        ];

        $color = $colors[$this->status] ?? 'secondary';
        $label = self::STATUSES[$this->status] ?? $this->status;

        return sprintf(
            '<span class="badge bg-%s">%s</span>',
            $color,
            $label
        );
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabelAttribute()
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Verify payment with gateway
     */
    public function verifyWithGateway()
    {
        if (!$this->transaction_id) {
            return false;
        }

        // This would call the payment gateway service
        // Example implementation:
        try {
            $gatewayService = app(\App\Services\PaymentGatewayService::class);
            $result = $gatewayService->verifyPayment($this->transaction_id);
            
            if ($result) {
                $this->gateway_response = $result;
                $this->save();
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'payment_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send payment confirmation email
     */
    public function sendConfirmation()
    {
        try {
            \Mail::to($this->student->email)
                ->send(new \App\Mail\PaymentConfirmation($this));
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation', [
                'payment_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}