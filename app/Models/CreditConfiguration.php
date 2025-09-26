<?php

// File: app/Models/CreditConfiguration.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CreditConfiguration extends Model
{
    protected $fillable = [
        'credit_system',
        'min_credits_full_time',
        'max_credits_regular',
        'max_credits_overload',
        'min_credits_graduation',
        'hours_per_credit',
        'credit_rules'
    ];

    protected $casts = [
        'credit_rules' => 'array',
        'hours_per_credit' => 'decimal:2',
    ];

    /**
     * Get cached configuration
     */
    public static function getCached()
    {
        return Cache::remember('credit_configuration', 3600, function () {
            return self::first();
        });
    }

    /**
     * Check if credit load is valid
     */
    public function isValidCreditLoad($credits)
    {
        return $credits <= $this->max_credits_overload;
    }

    /**
     * Check if student is full-time
     */
    public function isFullTime($credits)
    {
        return $credits >= $this->min_credits_full_time;
    }

    /**
     * Check if overload approval needed
     */
    public function needsOverloadApproval($credits)
    {
        return $credits > $this->max_credits_regular;
    }
}
