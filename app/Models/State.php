<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * State Model - Represents Counties in Liberia context
 * Using 'states' table but represents counties for Liberia
 */
class State extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'states';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'country_id',
        'code',
        'name',
        'type',
        'latitude',
        'longitude',
        'is_active',
        'sort_order'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Liberia's 15 Counties with their details.
     */
    const LIBERIA_COUNTIES = [
        [
            'code' => 'MO',
            'name' => 'Montserrado',
            'type' => 'County',
            'latitude' => 6.5526,
            'longitude' => -10.5297,
            'sort_order' => 1  // Capital county first
        ],
        [
            'code' => 'BM',
            'name' => 'Bomi',
            'type' => 'County',
            'latitude' => 6.7562,
            'longitude' => -10.8451,
            'sort_order' => 2
        ],
        [
            'code' => 'BG',
            'name' => 'Bong',
            'type' => 'County',
            'latitude' => 6.8295,
            'longitude' => -9.3673,
            'sort_order' => 3
        ],
        [
            'code' => 'GP',
            'name' => 'Gbarpolu',
            'type' => 'County',
            'latitude' => 7.4953,
            'longitude' => -10.0807,
            'sort_order' => 4
        ],
        [
            'code' => 'GB',
            'name' => 'Grand Bassa',
            'type' => 'County',
            'latitude' => 6.2308,
            'longitude' => -9.8124,
            'sort_order' => 5
        ],
        [
            'code' => 'CM',
            'name' => 'Grand Cape Mount',
            'type' => 'County',
            'latitude' => 7.0467,
            'longitude' => -10.8073,
            'sort_order' => 6
        ],
        [
            'code' => 'GG',
            'name' => 'Grand Gedeh',
            'type' => 'County',
            'latitude' => 5.9221,
            'longitude' => -8.2213,
            'sort_order' => 7
        ],
        [
            'code' => 'GK',
            'name' => 'Grand Kru',
            'type' => 'County',
            'latitude' => 4.7614,
            'longitude' => -8.2191,
            'sort_order' => 8
        ],
        [
            'code' => 'LF',
            'name' => 'Lofa',
            'type' => 'County',
            'latitude' => 8.1911,
            'longitude' => -9.7232,
            'sort_order' => 9
        ],
        [
            'code' => 'MG',
            'name' => 'Margibi',
            'type' => 'County',
            'latitude' => 6.5151,
            'longitude' => -10.3048,
            'sort_order' => 10
        ],
        [
            'code' => 'MY',
            'name' => 'Maryland',
            'type' => 'County',
            'latitude' => 4.7259,
            'longitude' => -7.7416,
            'sort_order' => 11
        ],
        [
            'code' => 'NI',
            'name' => 'Nimba',
            'type' => 'County',
            'latitude' => 6.8428,
            'longitude' => -8.6600,
            'sort_order' => 12
        ],
        [
            'code' => 'RG',
            'name' => 'River Gee',
            'type' => 'County',
            'latitude' => 5.2604,
            'longitude' => -7.8721,
            'sort_order' => 13
        ],
        [
            'code' => 'RI',
            'name' => 'Rivercess',
            'type' => 'County',
            'latitude' => 5.9025,
            'longitude' => -9.4561,
            'sort_order' => 14
        ],
        [
            'code' => 'SI',
            'name' => 'Sinoe',
            'type' => 'County',
            'latitude' => 5.4985,
            'longitude' => -8.6600,
            'sort_order' => 15
        ]
    ];

    /**
     * County capitals/headquarters.
     */
    const COUNTY_CAPITALS = [
        'Montserrado' => 'Monrovia',
        'Bomi' => 'Tubmanburg',
        'Bong' => 'Gbarnga',
        'Gbarpolu' => 'Bopolu',
        'Grand Bassa' => 'Buchanan',
        'Grand Cape Mount' => 'Robertsport',
        'Grand Gedeh' => 'Zwedru',
        'Grand Kru' => 'Barclayville',
        'Lofa' => 'Voinjama',
        'Margibi' => 'Kakata',
        'Maryland' => 'Harper',
        'Nimba' => 'Sanniquellie',
        'River Gee' => 'Fish Town',
        'Rivercess' => 'Cestos City',
        'Sinoe' => 'Greenville'
    ];

    /**
     * Relationships
     */

    /**
     * Get the country that owns the state/county.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the cities/districts for the state/county.
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }

    /**
     * Get the districts for the county (alias for cities).
     */
    public function districts()
    {
        return $this->hasMany(City::class);
    }

    /**
     * Scopes
     */

    /**
     * Scope for active states/counties.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered states/counties.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope for counties (type = 'County').
     */
    public function scopeCounties($query)
    {
        return $query->where('type', 'County');
    }

    /**
     * Scope for Liberian counties.
     */
    public function scopeLiberianCounties($query)
    {
        $liberia = Country::liberia();
        
        if ($liberia) {
            return $query->where('country_id', $liberia->id);
        }
        
        return $query;
    }

    /**
     * Scope for states/counties by country.
     */
    public function scopeForCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    /**
     * Helper Methods
     */

    /**
     * Check if this is a Liberian county.
     */
    public function isLiberianCounty(): bool
    {
        return $this->country && $this->country->code === 'LR';
    }

    /**
     * Get the capital/headquarters of the county.
     */
    public function getCapital(): ?string
    {
        return self::COUNTY_CAPITALS[$this->name] ?? null;
    }

    /**
     * Get full name with type.
     */
    public function getFullName(): string
    {
        if ($this->isLiberianCounty()) {
            return "{$this->name} County";
        }
        
        $type = $this->type ?: 'State';
        return "{$this->name} {$type}";
    }

    /**
     * Get display name with code.
     */
    public function getDisplayName(): string
    {
        if ($this->code) {
            return "{$this->name} ({$this->code})";
        }
        
        return $this->name;
    }

    /**
     * Get type label.
     */
    public function getTypeLabel(): string
    {
        return $this->type ?: 'State';
    }

    /**
     * Get region based on county grouping (for Liberia).
     */
    public function getRegion(): ?string
    {
        if (!$this->isLiberianCounty()) {
            return null;
        }

        $regions = [
            'Western' => ['Montserrado', 'Bomi', 'Grand Cape Mount', 'Gbarpolu'],
            'North Central' => ['Bong', 'Lofa', 'Nimba'],
            'South Central' => ['Margibi', 'Grand Bassa'],
            'Southeastern' => ['Grand Gedeh', 'River Gee', 'Sinoe', 'Maryland', 'Grand Kru', 'Rivercess']
        ];

        foreach ($regions as $region => $counties) {
            if (in_array($this->name, $counties)) {
                return $region;
            }
        }

        return null;
    }

    /**
     * Get neighboring counties (for Liberia).
     */
    public function getNeighbors(): array
    {
        $neighbors = [
            'Montserrado' => ['Margibi', 'Bomi', 'Grand Cape Mount'],
            'Bomi' => ['Montserrado', 'Grand Cape Mount', 'Gbarpolu'],
            'Bong' => ['Lofa', 'Gbarpolu', 'Margibi', 'Grand Bassa', 'Nimba'],
            'Gbarpolu' => ['Grand Cape Mount', 'Bomi', 'Bong', 'Lofa'],
            'Grand Bassa' => ['Montserrado', 'Margibi', 'Bong', 'Nimba', 'Rivercess'],
            'Grand Cape Mount' => ['Gbarpolu', 'Bomi', 'Montserrado'],
            'Grand Gedeh' => ['Nimba', 'River Gee', 'Sinoe'],
            'Grand Kru' => ['Maryland', 'River Gee', 'Sinoe'],
            'Lofa' => ['Gbarpolu', 'Bong', 'Nimba'],
            'Margibi' => ['Montserrado', 'Bong', 'Grand Bassa'],
            'Maryland' => ['Grand Kru', 'River Gee'],
            'Nimba' => ['Lofa', 'Bong', 'Grand Bassa', 'Rivercess', 'Grand Gedeh'],
            'River Gee' => ['Grand Gedeh', 'Maryland', 'Grand Kru', 'Sinoe'],
            'Rivercess' => ['Grand Bassa', 'Nimba', 'Sinoe'],
            'Sinoe' => ['Grand Gedeh', 'River Gee', 'Grand Kru', 'Rivercess']
        ];

        return $neighbors[$this->name] ?? [];
    }

    /**
     * Get dropdown options for a country.
     */
    public static function getDropdownOptions($countryId): array
    {
        return self::forCountry($countryId)
            ->active()
            ->ordered()
            ->get()
            ->mapWithKeys(function ($state) {
                $name = $state->isLiberianCounty() ? "{$state->name} County" : $state->name;
                return [$state->id => $name];
            })
            ->toArray();
    }

    /**
     * Get Liberian counties grouped by region.
     */
    public static function getLiberianCountiesByRegion(): array
    {
        $liberia = Country::liberia();
        
        if (!$liberia) {
            return [];
        }

        $counties = self::liberianCounties()->ordered()->get();
        $grouped = [];

        foreach ($counties as $county) {
            $region = $county->getRegion() ?? 'Other';
            $grouped[$region][] = $county;
        }

        return $grouped;
    }

    /**
     * Seed Liberian counties.
     */
    public static function seedLiberianCounties(): void
    {
        $liberia = Country::liberia();
        
        if (!$liberia) {
            return;
        }

        foreach (self::LIBERIA_COUNTIES as $countyData) {
            self::firstOrCreate(
                [
                    'country_id' => $liberia->id,
                    'code' => $countyData['code']
                ],
                array_merge($countyData, [
                    'country_id' => $liberia->id,
                    'is_active' => true
                ])
            );
        }
    }

    /**
     * Get statistics for the county.
     */
    public function getStatistics(): array
    {
        return [
            'total_districts' => $this->districts()->count(),
            'active_districts' => $this->districts()->where('is_active', true)->count(),
            'capital' => $this->getCapital(),
            'region' => $this->getRegion(),
            'neighbor_count' => count($this->getNeighbors())
        ];
    }

    /**
     * Format for API response.
     */
    public function formatForApi(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->getTypeLabel(),
            'full_name' => $this->getFullName(),
            'capital' => $this->getCapital(),
            'region' => $this->getRegion(),
            'country' => $this->country ? [
                'id' => $this->country->id,
                'name' => $this->country->name,
                'code' => $this->country->code
            ] : null,
            'coordinates' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ]
        ];
    }

    /**
     * Get universities in this county.
     */
    public function getUniversities()
    {
        // This would connect to your universities table
        // Example implementation:
        // return University::whereHas('city', function($q) {
        //     $q->where('state_id', $this->id);
        // })->get();
    }
}