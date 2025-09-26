<?php

// File: app/Models/RegistrationConfiguration.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RegistrationConfiguration extends Model
{
    protected $fillable = [
        'allow_online_registration',
        'registration_priority_days',
        'enforce_prerequisites',
        'allow_time_conflicts',
        'allow_waitlist',
        'max_waitlist_size',
        'drop_deadline_weeks',
        'withdraw_deadline_weeks',
        'late_registration_fee',
        'registration_rules'
    ];

    protected $casts = [
        'allow_online_registration' => 'boolean',
        'enforce_prerequisites' => 'boolean',
        'allow_time_conflicts' => 'boolean',
        'allow_waitlist' => 'boolean',
        'registration_rules' => 'array',
        'late_registration_fee' => 'decimal:2',
    ];

    /**
     * Get cached configuration
     */
    public static function getCached()
    {
        return Cache::remember('registration_configuration', 3600, function () {
            return self::first();
        });
    }

    /**
     * Calculate drop deadline date
     */
    public function getDropDeadline($termStartDate)
    {
        return $termStartDate->addWeeks($this->drop_deadline_weeks);
    }

    /**
     * Calculate withdraw deadline date
     */
    public function getWithdrawDeadline($termStartDate)
    {
        return $termStartDate->addWeeks($this->withdraw_deadline_weeks);
    }

    /**
     * Check if past drop deadline
     */
    public function isPastDropDeadline($termStartDate)
    {
        return now()->isAfter($this->getDropDeadline($termStartDate));
    }

    /**
     * Check if past withdraw deadline
     */
    public function isPastWithdrawDeadline($termStartDate)
    {
        return now()->isAfter($this->getWithdrawDeadline($termStartDate));
    }
}
