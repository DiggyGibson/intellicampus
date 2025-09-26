<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAccessLog extends Model
{
    use HasFactory;

    /**
     * Disable timestamps as we only need accessed_at
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'document_id',
        'accessed_by',
        'access_type',
        'purpose',
        'ip_address',
        'user_agent',
        'context',
        'accessed_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'context' => 'array',
        'accessed_at' => 'datetime'
    ];

    /**
     * Set accessed_at on creation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->accessed_at = $model->accessed_at ?? now();
        });
    }

    /**
     * Get the document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user who accessed the document
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accessed_by');
    }

    /**
     * Scope for specific access type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('access_type', $type);
    }

    /**
     * Scope for specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('accessed_by', $userId);
    }

    /**
     * Scope for date range
     */
    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('accessed_at', [$start, $end]);
    }

    /**
     * Scope for today's logs
     */
    public function scopeToday($query)
    {
        return $query->whereDate('accessed_at', today());
    }

    /**
     * Scope for this week's logs
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('accessed_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope for this month's logs
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('accessed_at', now()->month)
                     ->whereYear('accessed_at', now()->year);
    }

    /**
     * Get human-readable access type
     */
    public function getAccessTypeDisplayAttribute(): string
    {
        $types = [
            'view' => 'Viewed',
            'download' => 'Downloaded',
            'print' => 'Printed',
            'share' => 'Shared',
            'edit' => 'Edited',
            'verify' => 'Verified',
            'reject' => 'Rejected',
            'delete' => 'Deleted',
            'restore' => 'Restored',
            'upload' => 'Uploaded'
        ];

        return $types[$this->access_type] ?? ucfirst($this->access_type);
    }

    /**
     * Check if this is a read action
     */
    public function isReadAction(): bool
    {
        return in_array($this->access_type, ['view', 'download', 'print']);
    }

    /**
     * Check if this is a write action
     */
    public function isWriteAction(): bool
    {
        return in_array($this->access_type, ['edit', 'upload']);
    }

    /**
     * Check if this is an administrative action
     */
    public function isAdminAction(): bool
    {
        return in_array($this->access_type, ['verify', 'reject', 'delete', 'restore']);
    }
}