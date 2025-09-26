<?php
// File: app/Models/SystemSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'category',
        'key',
        'value',
        'type',
        'description',
        'options',
        'is_public',
        'is_editable'
    ];

    protected $casts = [
        'options' => 'array',
        'is_public' => 'boolean',
        'is_editable' => 'boolean',
    ];

    /**
     * Get setting value by key
     */
    public static function getValue($key, $default = null)
    {
        return Cache::remember("system_setting_$key", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }
            
            // Cast value based on type
            switch ($setting->type) {
                case 'boolean':
                    return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
                case 'number':
                    return is_numeric($setting->value) ? (float)$setting->value : $default;
                case 'json':
                    return json_decode($setting->value, true) ?: $default;
                default:
                    return $setting->value;
            }
        });
    }

    /**
     * Set setting value
     */
    public static function setValue($key, $value)
    {
        $setting = self::where('key', $key)->first();
        
        if ($setting) {
            $setting->value = is_array($value) ? json_encode($value) : $value;
            $setting->save();
            
            Cache::forget("system_setting_$key");
            return true;
        }
        
        return false;
    }
}