<?php

// File: app/Models/Building.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    protected $fillable = [
        'building_code',
        'building_name',
        'address',
        'total_floors',
        'facilities',
        'latitude',
        'longitude',
        'is_active'
    ];

    protected $casts = [
        'facilities' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean'
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function getActiveRoomsAttribute()
    {
        return $this->rooms()->where('is_active', true)->get();
    }

    public function getTotalCapacityAttribute()
    {
        return $this->rooms()->sum('capacity');
    }
}
