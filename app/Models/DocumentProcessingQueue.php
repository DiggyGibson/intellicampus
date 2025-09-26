<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentProcessingQueue extends Model
{
    use HasFactory;

    /**
     * The table name
     */
    protected $table = 'document_processing_queue';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'document_id',
        'process_type',
        'status',
        'attempts',
        'max_attempts',
        'options',
        'result',
        'error_message',
        'started_at',
        'completed_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'options' => 'array',
        'result' => 'array',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    /**
     * Process type constants
     */
    const PROCESS_VIRUS_SCAN = 'virus_scan';
    const PROCESS_OCR = 'ocr';
    const PROCESS_THUMBNAIL = 'thumbnail';
    const PROCESS_COMPRESS = 'compress';
    const PROCESS_WATERMARK = 'watermark';
    const PROCESS_CONVERT = 'convert';
    const PROCESS_EXTRACT_METADATA = 'extract_metadata';

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Scope for pending items
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for processing items
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope for completed items
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for failed items
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for specific process type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('process_type', $type);
    }

    /**
     * Scope for retryable items
     */
    public function scopeRetryable($query)
    {
        return $query->where('status', self::STATUS_FAILED)
                     ->whereColumn('attempts', '<', 'max_attempts');
    }

    /**
     * Check if can retry
     */
    public function canRetry(): bool
    {
        return $this->status === self::STATUS_FAILED && 
               $this->attempts < $this->max_attempts;
    }

    /**
     * Check if is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if has failed
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'started_at' => now(),
            'attempts' => $this->attempts + 1
        ]);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(array $result = []): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'result' => $result,
            'error_message' => null
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $error,
            'completed_at' => now()
        ]);
    }

    /**
     * Mark as cancelled
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => now()
        ]);
    }

    /**
     * Reset for retry
     */
    public function resetForRetry(): void
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'started_at' => null,
            'completed_at' => null,
            'error_message' => null,
            'result' => null
        ]);
    }

    /**
     * Get processing duration in seconds
     */
    public function getProcessingDurationAttribute(): ?int
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInSeconds($this->started_at);
    }

    /**
     * Get human-readable process type
     */
    public function getProcessTypeDisplayAttribute(): string
    {
        $types = [
            self::PROCESS_VIRUS_SCAN => 'Virus Scan',
            self::PROCESS_OCR => 'OCR Text Extraction',
            self::PROCESS_THUMBNAIL => 'Thumbnail Generation',
            self::PROCESS_COMPRESS => 'Compression',
            self::PROCESS_WATERMARK => 'Watermark',
            self::PROCESS_CONVERT => 'Format Conversion',
            self::PROCESS_EXTRACT_METADATA => 'Metadata Extraction'
        ];

        return $types[$this->process_type] ?? ucfirst(str_replace('_', ' ', $this->process_type));
    }
}