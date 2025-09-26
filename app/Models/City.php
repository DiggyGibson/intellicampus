<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * City Model - Represents Cities and Districts in Liberia
 */
class City extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'cities';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'state_id',
        'country_id',
        'name',
        'latitude',
        'longitude',
        'population',
        'is_capital',
        'is_active',
        'sort_order'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'population' => 'integer',
        'is_capital' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Major Liberian cities and their details.
     */
    const MAJOR_LIBERIAN_CITIES = [
        // Montserrado County
        [
            'county' => 'Montserrado',
            'name' => 'Monrovia',
            'latitude' => 6.3106,
            'longitude' => -10.8047,
            'population' => 1021762,
            'is_capital' => true,
            'sort_order' => 1
        ],
        [
            'county' => 'Montserrado',
            'name' => 'Paynesville',
            'latitude' => 6.2832,
            'longitude' => -10.7197,
            'population' => 150000,
            'is_capital' => false,
            'sort_order' => 2
        ],
        [
            'county' => 'Montserrado',
            'name' => 'New Georgia',
            'latitude' => 6.2667,
            'longitude' => -10.6833,
            'population' => 35000,
            'is_capital' => false,
            'sort_order' => 3
        ],
        // Nimba County
        [
            'county' => 'Nimba',
            'name' => 'Ganta',
            'latitude' => 7.2361,
            'longitude' => -8.9806,
            'population' => 41106,
            'is_capital' => false,
            'sort_order' => 4
        ],
        [
            'county' => 'Nimba',
            'name' => 'Sanniquellie',
            'latitude' => 7.3614,
            'longitude' => -8.7060,
            'population' => 11415,
            'is_capital' => true,
            'sort_order' => 5
        ],
        // Grand Bassa County
        [
            'county' => 'Grand Bassa',
            'name' => 'Buchanan',
            'latitude' => 5.8808,
            'longitude' => -10.0467,
            'population' => 34270,
            'is_capital' => true,
            'sort_order' => 6
        ],
        // Margibi County
        [
            'county' => 'Margibi',
            'name' => 'Kakata',
            'latitude' => 6.5295,
            'longitude' => -10.3517,
            'population' => 33945,
            'is_capital' => true,
            'sort_order' => 7
        ],
        [
            'county' => 'Margibi',
            'name' => 'Harbel',
            'latitude' => 6.2824,
            'longitude' => -10.3505,
            'population' => 25309,
            'is_capital' => false,
            'sort_order' => 8
        ],
        // Bong County
        [
            'county' => 'Bong',
            'name' => 'Gbarnga',
            'latitude' => 6.9976,
            'longitude' => -9.4712,
            'population' => 34046,
            'is_capital' => true,
            'sort_order' => 9
        ],
        // Lofa County
        [
            'county' => 'Lofa',
            'name' => 'Voinjama',
            'latitude' => 8.4219,
            'longitude' => -9.7478,
            'population' => 26594,
            'is_capital' => true,
            'sort_order' => 10
        ],
        // Maryland County
        [
            'county' => 'Maryland',
            'name' => 'Harper',
            'latitude' => 4.3750,
            'longitude' => -7.7169,
            'population' => 17837,
            'is_capital' => true,
            'sort_order' => 11
        ],
        [
            'county' => 'Maryland',
            'name' => 'Pleebo',
            'latitude' => 4.5485,
            'longitude' => -7.4935,
            'population' => 22963,
            'is_capital' => false,
            'sort_order' => 12
        ],
        // Grand Gedeh County
        [
            'county' => 'Grand Gedeh',
            'name' => 'Zwedru',
            'latitude' => 6.0667,
            'longitude' => -8.1281,
            'population' => 23903,
            'is_capital' => true,
            'sort_order' => 13
        ],
        // Grand Cape Mount County
        [
            'county' => 'Grand Cape Mount',
            'name' => 'Robertsport',
            'latitude' => 6.7533,
            'longitude' => -11.3686,
            'population' => 11969,
            'is_capital' => true,
            'sort_order' => 14
        ],
        // Sinoe County
        [
            'county' => 'Sinoe',
            'name' => 'Greenville',
            'latitude' => 5.0116,
            'longitude' => -9.0388,
            'population' => 16434,
            'is_capital' => true,
            'sort_order' => 15
        ]
    ];

    /**
     * University towns in Liberia.
     */
    const UNIVERSITY_TOWNS = [
        'Monrovia' => ['University of Liberia', 'Stella Maris Polytechnic', 'AME University'],
        'Harbel' => ['Cuttington University'],
        'Gbarnga' => ['Cuttington University Graduate School'],
        'Harper' => ['William V.S. Tubman University'],
        'Buchanan' => ['Grand Bassa Community College'],
        'Voinjama' => ['Lofa County Community College'],
        'Sanniquellie' => ['Nimba County Community College'],
        'Zwedru' => ['Grand Gedeh County Community College']
    ];

    /**
     * Relationships
     */

    /**
     * Get the state/county that owns the city.
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the county (alias for state in Liberian context).
     */
    public function county()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    /**
     * Get the country that owns the city.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Scopes
     */

    /**
     * Scope for active cities.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for capital cities.
     */
    public function scopeCapitals($query)
    {
        return $query->where('is_capital', true);
    }

    /**
     * Scope for ordered cities.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')
            ->orderBy('is_capital', 'desc')
            ->orderBy('population', 'desc')
            ->orderBy('name');
    }

    /**
     * Scope for cities by state/county.
     */
    public function scopeForState($query, $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    /**
     * Scope for cities by county (alias).
     */
    public function scopeForCounty($query, $countyId)
    {
        return $query->where('state_id', $countyId);
    }

    /**
     * Scope for cities by country.
     */
    public function scopeForCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    /**
     * Scope for large cities.
     */
    public function scopeLarge($query, $minPopulation = 10000)
    {
        return $query->where('population', '>=', $minPopulation);
    }

    /**
     * Scope for university towns.
     */
    public function scopeUniversityTowns($query)
    {
        $towns = array_keys(self::UNIVERSITY_TOWNS);
        return $query->whereIn('name', $towns);
    }

    /**
     * Scope for Liberian cities.
     */
    public function scopeLiberianCities($query)
    {
        $liberia = Country::liberia();
        
        if ($liberia) {
            return $query->where('country_id', $liberia->id);
        }
        
        return $query;
    }

    /**
     * Helper Methods
     */

    /**
     * Check if this is a Liberian city.
     */
    public function isLiberianCity(): bool
    {
        return $this->country && $this->country->code === 'LR';
    }

    /**
     * Check if this is Monrovia.
     */
    public function isMonrovia(): bool
    {
        return $this->name === 'Monrovia' && $this->isLiberianCity();
    }

    /**
     * Check if this is a county capital.
     */
    public function isCountyCapital(): bool
    {
        return $this->is_capital && $this->isLiberianCity();
    }

    /**
     * Check if this is a university town.
     */
    public function isUniversityTown(): bool
    {
        return isset(self::UNIVERSITY_TOWNS[$this->name]);
    }

    /**
     * Get universities in this city.
     */
    public function getUniversities(): array
    {
        return self::UNIVERSITY_TOWNS[$this->name] ?? [];
    }

    /**
     * Get full location string.
     */
    public function getFullLocation(): string
    {
        $parts = [$this->name];
        
        if ($this->county) {
            if ($this->isLiberianCity()) {
                $parts[] = $this->county->name . ' County';
            } else {
                $parts[] = $this->county->name;
            }
        }
        
        if ($this->country) {
            $parts[] = $this->country->name;
        }
        
        return implode(', ', $parts);
    }

    /**
     * Get short location (city, county).
     */
    public function getShortLocation(): string
    {
        if ($this->county) {
            return "{$this->name}, {$this->county->name}";
        }
        
        return $this->name;
    }

    /**
     * Get formatted population.
     */
    public function getFormattedPopulation(): string
    {
        if (!$this->population) {
            return 'Unknown';
        }
        
        if ($this->population >= 1000000) {
            return number_format($this->population / 1000000, 1) . 'M';
        }
        
        if ($this->population >= 1000) {
            return number_format($this->population / 1000, 0) . 'K';
        }
        
        return number_format($this->population);
    }

    /**
     * Get city size category.
     */
    public function getSizeCategory(): string
    {
        if (!$this->population) {
            return 'Unknown';
        }
        
        return match(true) {
            $this->population >= 500000 => 'Major City',
            $this->population >= 100000 => 'Large City',
            $this->population >= 50000 => 'Medium City',
            $this->population >= 10000 => 'Small City',
            $this->population >= 5000 => 'Town',
            default => 'Village'
        };
    }

    /**
     * Get distance to Monrovia (in km).
     */
    public function getDistanceToMonrovia(): ?float
    {
        if ($this->isMonrovia()) {
            return 0;
        }
        
        if (!$this->latitude || !$this->longitude) {
            return null;
        }
        
        // Monrovia coordinates
        $monroviaLat = 6.3106;
        $monroviaLon = -10.8047;
        
        // Haversine formula for distance calculation
        $earthRadius = 6371; // km
        
        $latDiff = deg2rad($this->latitude - $monroviaLat);
        $lonDiff = deg2rad($this->longitude - $monroviaLon);
        
        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($monroviaLat)) * cos(deg2rad($this->latitude)) *
             sin($lonDiff / 2) * sin($lonDiff / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return round($earthRadius * $c, 1);
    }

    /**
     * Get dropdown options for a state/county.
     */
    public static function getDropdownOptions($stateId): array
    {
        return self::forState($stateId)
            ->active()
            ->ordered()
            ->get()
            ->mapWithKeys(function ($city) {
                $prefix = $city->is_capital ? '⭐ ' : '';
                return [$city->id => $prefix . $city->name];
            })
            ->toArray();
    }

    /**
     * Get major cities for a country.
     */
    public static function getMajorCities($countryId, $limit = 10)
    {
        return self::forCountry($countryId)
            ->active()
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * Search cities by name.
     */
    public static function search($query, $limit = 10)
    {
        return self::where('name', 'LIKE', "%{$query}%")
            ->active()
            ->with(['county', 'country'])
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * Seed Liberian cities.
     */
    public static function seedLiberianCities(): void
    {
        $liberia = Country::liberia();
        
        if (!$liberia) {
            return;
        }

        foreach (self::MAJOR_LIBERIAN_CITIES as $cityData) {
            $county = State::where('name', $cityData['county'])
                ->where('country_id', $liberia->id)
                ->first();
            
            if ($county) {
                self::firstOrCreate(
                    [
                        'name' => $cityData['name'],
                        'state_id' => $county->id
                    ],
                    [
                        'state_id' => $county->id,
                        'country_id' => $liberia->id,
                        'latitude' => $cityData['latitude'],
                        'longitude' => $cityData['longitude'],
                        'population' => $cityData['population'],
                        'is_capital' => $cityData['is_capital'],
                        'sort_order' => $cityData['sort_order'],
                        'is_active' => true
                    ]
                );
            }
        }
    }

    /**
     * Get statistics.
     */
    public function getStatistics(): array
    {
        return [
            'population' => $this->population,
            'formatted_population' => $this->getFormattedPopulation(),
            'size_category' => $this->getSizeCategory(),
            'is_capital' => $this->is_capital,
            'is_university_town' => $this->isUniversityTown(),
            'universities' => $this->getUniversities(),
            'distance_to_monrovia' => $this->getDistanceToMonrovia()
        ];
    }

    /**
     * Format for API response.
     */
    public function formatForApi(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'population' => $this->population,
            'formatted_population' => $this->getFormattedPopulation(),
            'is_capital' => $this->is_capital,
            'is_university_town' => $this->isUniversityTown(),
            'county' => $this->county ? [
                'id' => $this->county->id,
                'name' => $this->county->name,
                'code' => $this->county->code
            ] : null,
            'country' => $this->country ? [
                'id' => $this->country->id,
                'name' => $this->country->name,
                'code' => $this->country->code
            ] : null,
            'coordinates' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ],
            'location' => [
                'full' => $this->getFullLocation(),
                'short' => $this->getShortLocation()
            ]
        ];
    }
}