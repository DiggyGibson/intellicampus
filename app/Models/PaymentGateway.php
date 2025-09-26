<?php
// app/Models/PaymentGateway.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'provider',
        'api_key_encrypted',
        'api_secret_encrypted',
        'webhook_secret',
        'is_active',
        'is_test_mode',
        'settings',
        'supported_currencies',
        'transaction_fee_percent',
        'transaction_fee_fixed'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
        'settings' => 'array',
        'supported_currencies' => 'array',
        'transaction_fee_percent' => 'decimal:2',
        'transaction_fee_fixed' => 'decimal:2'
    ];

    // Relationships
    public function transactions()
    {
        return $this->hasMany(PaymentGatewayTransaction::class, 'gateway_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'gateway_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    // Methods
    public function calculateFee($amount)
    {
        $percentageFee = ($amount * $this->transaction_fee_percent) / 100;
        return round($percentageFee + $this->transaction_fee_fixed, 2);
    }

    public function isStripe()
    {
        return $this->provider === 'stripe';
    }

    public function isPayPal()
    {
        return $this->provider === 'paypal';
    }
}
