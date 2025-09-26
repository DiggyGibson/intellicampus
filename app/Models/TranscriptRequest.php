<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TranscriptRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'request_number',
        'requested_by',
        'type',
        'delivery_method',
        'recipient_name',
        'recipient_email',
        'mailing_address',
        'purpose',
        'copies',
        'rush_order',
        'special_instructions',
        'fee',
        'payment_status',
        'payment_date',
        'payment_reference',
        'status',
        'requested_at',
        'processed_by',
        'processed_at',
        'completed_at',
        'verification_code',
        'file_path',
        'tracking_number',
        'notes',
    ];

    protected $casts = [
        'rush_order' => 'boolean',
        'fee' => 'decimal:2',
        'copies' => 'integer',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'payment_date' => 'datetime',
    ];

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate request number before creating
        static::creating(function ($model) {
            if (empty($model->request_number)) {
                $model->request_number = self::generateRequestNumber();
            }
            
            if (empty($model->requested_at)) {
                $model->requested_at = now();
            }

            // Set payment status based on fee
            if ($model->fee == 0 && empty($model->payment_status)) {
                $model->payment_status = 'not_required';
            } elseif (empty($model->payment_status)) {
                $model->payment_status = 'pending';
            }

            // Generate verification code for official transcripts
            if ($model->type === 'official' && empty($model->verification_code)) {
                $model->verification_code = self::generateVerificationCode();
            }
        });
    }

    /**
     * Generate a unique request number
     */
    public static function generateRequestNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        // Get the last request number for this month
        $lastRequest = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRequest && preg_match('/TR' . $year . $month . '(\d{4})/', $lastRequest->request_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('TR%s%s%04d', $year, $month, $sequence);
    }

    /**
     * Generate a unique verification code
     */
    public static function generateVerificationCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 4)) . '-' . 
                    rand(1000, 9999) . '-' . 
                    strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
        } while (self::where('verification_code', $code)->exists());

        return $code;
    }

    /**
     * Relationships
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function verification()
    {
        return $this->hasOne(TranscriptVerification::class);
    }

    public function logs()
    {
        return $this->hasMany(TranscriptLog::class);
    }

    public function payments()
    {
        return $this->hasMany(TranscriptPayment::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeNeedingPayment($query)
    {
        return $query->where('payment_status', 'pending')
                     ->where('fee', '>', 0);
    }

    /**
     * Accessors & Methods
     */
    public function isPaid()
    {
        return in_array($this->payment_status, ['paid', 'not_required', 'waived']);
    }

    public function canProcess()
    {
        return $this->status === 'pending' && $this->isPaid();
    }

    public function needsPayment()
    {
        return $this->fee > 0 && $this->payment_status === 'pending';
    }

    public function markAsPaid($reference = null)
    {
        $this->update([
            'payment_status' => 'paid',
            'payment_date' => now(),
            'payment_reference' => $reference,
        ]);

        // Log the payment
        $this->logs()->create([
            'student_id' => $this->student_id,
            'action' => 'processed',
            'type' => $this->type,
            'purpose' => 'Payment received',
            'performed_by' => auth()->id() ?? $this->requested_by,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }

    public function markAsProcessing($userId)
    {
        $this->update([
            'status' => 'processing',
            'processed_by' => $userId,
            'processed_at' => now(),
        ]);

        // Log the processing
        $this->logs()->create([
            'student_id' => $this->student_id,
            'action' => 'processed',
            'type' => $this->type,
            'purpose' => 'Started processing',
            'performed_by' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }

    public function markAsCompleted($filePath = null, $trackingNumber = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'file_path' => $filePath,
            'tracking_number' => $trackingNumber,
        ]);

        // Create verification record for official transcripts
        if ($this->type === 'official' && $this->verification_code) {
            TranscriptVerification::create([
                'student_id' => $this->student_id,
                'transcript_request_id' => $this->id,
                'verification_code' => $this->verification_code,
                'type' => 'official',
                'file_path' => $filePath,
                'generated_at' => now(),
                'expires_at' => now()->addDays(90),
                'generated_by' => $this->processed_by ?? auth()->id(),
            ]);
        }

        // Log completion
        $this->logs()->create([
            'student_id' => $this->student_id,
            'action' => 'completed',
            'type' => $this->type,
            'purpose' => 'Transcript completed',
            'performed_by' => $this->processed_by ?? auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'notes' => $reason,
        ]);

        // Log cancellation
        $this->logs()->create([
            'student_id' => $this->student_id,
            'action' => 'cancelled',
            'type' => $this->type,
            'purpose' => 'Request cancelled: ' . $reason,
            'performed_by' => auth()->id() ?? $this->requested_by,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now(),
        ]);
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            'on_hold' => 'secondary',
        ][$this->status] ?? 'secondary';
    }

    public function getStatusLabelAttribute()
    {
        return [
            'pending' => 'Pending Review',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'on_hold' => 'On Hold',
        ][$this->status] ?? 'Unknown';
    }

    public function getPaymentStatusLabelAttribute()
    {
        return [
            'not_required' => 'No Payment Required',
            'pending' => 'Payment Pending',
            'paid' => 'Paid',
            'waived' => 'Fee Waived',
            'refunded' => 'Refunded',
        ][$this->payment_status] ?? 'Unknown';
    }

    public function getPaymentStatusColorAttribute()
    {
        return [
            'not_required' => 'secondary',
            'pending' => 'warning',
            'paid' => 'success',
            'waived' => 'info',
            'refunded' => 'danger',
        ][$this->payment_status] ?? 'secondary';
    }

    /**
     * Calculate the transcript fee
     */
    public static function calculateFee($type, $copies = 1, $deliveryMethod = 'electronic', $rushOrder = false)
    {
        $fee = 0;

        // Base fee for official transcripts
        if ($type === 'official') {
            $fee = 10.00; // Base fee for first copy
            
            // Additional copies
            if ($copies > 1) {
                $fee += ($copies - 1) * 5.00;
            }
        }

        // Delivery method fees
        if ($deliveryMethod === 'mail') {
            $fee += 10.00; // Mailing fee
        }

        // Rush processing fee
        if ($rushOrder) {
            $fee += 25.00;
        }

        return $fee;
    }
    
}