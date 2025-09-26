<?php

// File: app/Models/InstitutionConfig.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InstitutionConfig extends Model
{
    protected $table = 'institution_config';
    
    protected $fillable = [
        'institution_name',
        'institution_code',
        'institution_type',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'phone',
        'email',
        'website',
        'logo_path',
        'timezone',
        'currency_code',
        'currency_symbol',
        'date_format',
        'time_format',
        'social_media',
        'accreditations',
        'is_active'
    ];

    protected $casts = [
        'social_media' => 'array',
        'accreditations' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get cached institution config
     */
    public static function getCached()
    {
        return Cache::remember('institution_config', 3600, function () {
            return self::first();
        });
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute()
    {
        return "{$this->address}, {$this->city}, {$this->state} {$this->postal_code}, {$this->country}";
    }

    /**
     * Clear cache when updated
     */
    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('institution_config');
        });
    }
}