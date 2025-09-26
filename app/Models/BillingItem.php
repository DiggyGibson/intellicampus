<?php
// app/Models/BillingItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'term_id',
        'fee_structure_id',
        'description',
        'type',
        'amount',
        'balance',
        'due_date',
        'status',
        'reference_type',
        'reference_id',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'due_date' => 'date'
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function term()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentAllocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    // Scopes
    public function scopeUnpaid($query)
    {
        return $query->where('balance', '>', 0);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->where('balance', '>', 0);
    }

    public function scopeForTerm($query, $termId)
    {
        return $query->where('term_id', $termId);
    }

    // Methods
    public function isOverdue()
    {
        return $this->balance > 0 && $this->due_date < now();
    }

    public function isPaid()
    {
        return $this->balance <= 0;
    }

    public function getPaymentProgress()
    {
        if ($this->amount <= 0) return 100;
        return round((($this->amount - $this->balance) / $this->amount) * 100, 2);
    }
}