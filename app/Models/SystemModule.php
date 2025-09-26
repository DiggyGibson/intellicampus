<?php

// File: app/Models/SystemModule.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemModule extends Model
{
    protected $fillable = [
        'module_name',
        'module_code',
        'description',
        'is_enabled',
        'configuration',
        'permissions',
        'display_order'
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'configuration' => 'array',
        'permissions' => 'array',
    ];

    /**
     * Scope for enabled modules
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Get all enabled modules (cached)
     */
    public static function getEnabled()
    {
        return Cache::remember('enabled_modules', 3600, function () {
            return self::where('is_enabled', true)
                ->orderBy('display_order')
                ->get();
        });
    }

    /**
     * Check if module is enabled
     */
    public static function isEnabled($moduleCode)
    {
        $enabledModules = self::getEnabled();
        return $enabledModules->where('module_code', $moduleCode)->isNotEmpty();
    }
}