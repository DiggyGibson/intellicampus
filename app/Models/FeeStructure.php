<?php
// app/Models/FeeStructure.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'frequency',
        'amount',
        'academic_level',
        'program_id',
        'is_mandatory',
        'is_active',
        'effective_from',
        'effective_until'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date'
    ];

    // Relationships
    public function billingItems()
    {
        return $this->hasMany(BillingItem::class);
    }

    public function program()
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('effective_from', '<=', now())
                     ->where(function($q) {
                         $q->whereNull('effective_until')
                           ->orWhere('effective_until', '>=', now());
                     });
    }

    public function scopeForLevel($query, $level)
    {
        return $query->where(function($q) use ($level) {
            $q->whereNull('academic_level')
              ->orWhere('academic_level', $level);
        });
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    // Methods
    public function calculateAmount($credits = null)
    {
        if ($this->frequency === 'per_credit' && $credits) {
            return $this->amount * $credits;
        }
        return $this->amount;
    }

    public function isApplicableToStudent($student)
    {
        // Check academic level
        if ($this->academic_level && $student->academic_level !== $this->academic_level) {
            return false;
        }

        // Check program
        if ($this->program_id && $student->program_id !== $this->program_id) {
            return false;
        }

        // Check dates
        if ($this->effective_from > now() || ($this->effective_until && $this->effective_until < now())) {
            return false;
        }

        return $this->is_active;
    }
}