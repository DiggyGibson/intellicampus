<?php

// ========================================
// app/Models/SponsorAuthorization.php
// ========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SponsorAuthorization extends Model
{
    use HasFactory;

    protected $fillable = [
        'sponsor_id',
        'student_id',
        'term_id',
        'authorization_number',
        'authorized_amount',
        'used_amount',
        'covered_items',
        'status',
        'valid_from',
        'valid_until',
        'notes',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'authorized_amount' => 'decimal:2',
        'used_amount' => 'decimal:2',
        'covered_items' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'approved_at' => 'datetime'
    ];

    // Relationships
    public function sponsor()
    {
        return $this->belongsTo(ThirdPartySponsor::class, 'sponsor_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function term()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('valid_from', '<=', now())
                    ->where('valid_until', '>=', now());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Methods
    public function getRemainingAmount()
    {
        return max(0, $this->authorized_amount - $this->used_amount);
    }

    public function isValid()
    {
        $now = now();
        return $this->status === 'active' &&
               $this->valid_from <= $now &&
               $this->valid_until >= $now &&
               $this->used_amount < $this->authorized_amount;
    }

    public function canCoverItem($itemType, $amount)
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check if item type is covered
        if ($this->covered_items && !in_array($itemType, $this->covered_items)) {
            return false;
        }

        // Check if amount is within remaining authorization
        return $this->getRemainingAmount() >= $amount;
    }

    public function useAuthorization($amount)
    {
        $this->used_amount += $amount;
        $this->save();
    }

    public function approve($userId)
    {
        $this->status = 'active';
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();
    }
}