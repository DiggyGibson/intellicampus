<?php

// ========================================
// app/Models/ThirdPartySponsor.php
// ========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThirdPartySponsor extends Model
{
    use HasFactory;

    protected $fillable = [
        'sponsor_code',
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'type',
        'credit_limit',
        'current_balance',
        'is_active',
        'billing_preferences',
        'contract_start_date',
        'contract_end_date'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'billing_preferences' => 'array',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date'
    ];

    // Relationships
    public function authorizations()
    {
        return $this->hasMany(SponsorAuthorization::class, 'sponsor_id');
    }

    public function students()
    {
        return $this->hasManyThrough(
            Student::class,
            SponsorAuthorization::class,
            'sponsor_id',
            'id',
            'id',
            'student_id'
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Methods
    public function hasAvailableCredit()
    {
        return $this->credit_limit === null || 
               $this->current_balance < $this->credit_limit;
    }

    public function getAvailableCredit()
    {
        if ($this->credit_limit === null) {
            return null; // Unlimited
        }
        return max(0, $this->credit_limit - $this->current_balance);
    }

    public function isContractActive()
    {
        $now = now();
        return $this->is_active &&
               (!$this->contract_start_date || $this->contract_start_date <= $now) &&
               (!$this->contract_end_date || $this->contract_end_date >= $now);
    }
}
