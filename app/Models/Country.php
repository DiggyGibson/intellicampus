<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'countries';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'code3',
        'name',
        'native_name',
        'phone_code',
        'capital',
        'currency',
        'region',
        'subregion',
        'is_active',
        'sort_order'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Default countries with Liberia as primary.
     */
    const DEFAULT_COUNTRIES = [
        [
            'code' => 'LR',
            'code3' => 'LBR',
            'name' => 'Liberia',
            'native_name' => 'Republic of Liberia',
            'phone_code' => '231',
            'capital' => 'Monrovia',
            'currency' => 'LRD',
            'region' => 'Africa',
            'subregion' => 'Western Africa',
            'is_active' => true,
            'sort_order' => 1
        ],
        [
            'code' => 'SL',
            'code3' => 'SLE',
            'name' => 'Sierra Leone',
            'native_name' => 'Republic of Sierra Leone',
            'phone_code' => '232',
            'capital' => 'Freetown',
            'currency' => 'SLL',
            'region' => 'Africa',
            'subregion' => 'Western Africa',
            'is_active' => true,
            'sort_order' => 2
        ],
        [
            'code' => 'GN',
            'code3' => 'GIN',
            'name' => 'Guinea',
            'native_name' => 'République de Guinée',
            'phone_code' => '224',
            'capital' => 'Conakry',
            'currency' => 'GNF',
            'region' => 'Africa',
            'subregion' => 'Western Africa',
            'is_active' => true,
            'sort_order' => 3
        ],
        [
            'code' => 'CI',
            'code3' => 'CIV',
            'name' => 'Côte d\'Ivoire',
            'native_name' => 'République de Côte d\'Ivoire',
            'phone_code' => '225',
            'capital' => 'Yamoussoukro',
            'currency' => 'XOF',
            'region' => 'Africa',
            'subregion' => 'Western Africa',
            'is_active' => true,
            'sort_order' => 4
        ],
        [
            'code' => 'GH',
            'code3' => 'GHA',
            'name' => 'Ghana',
            'native_name' => 'Republic of Ghana',
            'phone_code' => '233',
            'capital' => 'Accra',
            'currency' => 'GHS',
            'region' => 'Africa',
            'subregion' => 'Western Africa',
            'is_active' => true,
            'sort_order' => 5
        ],
        [
            'code' => 'NG',
            'code3' => 'NGA',
            'name' => 'Nigeria',
            'native_name' => 'Federal Republic of Nigeria',
            'phone_code' => '234',
            'capital' => 'Abuja',
            'currency' => 'NGN',
            'region' => 'Africa',
            'subregion' => 'Western Africa',
            'is_active' => true,
            'sort_order' => 6
        ],
        [
            'code' => 'US',
            'code3' => 'USA',
            'name' => 'United States',
            'native_name' => 'United States of America',
            'phone_code' => '1',
            'capital' => 'Washington, D.C.',
            'currency' => 'USD',
            'region' => 'Americas',
            'subregion' => 'Northern America',
            'is_active' => true,
            'sort_order' => 10
        ]
    ];

    /**
     * Relationships
     */

    /**
     * Get the states/counties for the country.
     * Note: In Liberia's case, these are counties.
     */
    public function states()
    {
        return $this->hasMany(State::class);
    }

    /**
     * Get the counties for the country (alias for states).
     */
    public function counties()
    {
        return $this->hasMany(State::class);
    }

    /**
     * Get the cities for the country.
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }

    /**
     * Scopes
     */

    /**
     * Scope for active countries.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered countries.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope for West African countries.
     */
    public function scopeWestAfrican($query)
    {
        return $query->where('subregion', 'Western Africa');
    }

    /**
     * Scope for ECOWAS countries.
     */
    public function scopeEcowas($query)
    {
        $ecowasCountries = [
            'BJ', 'BF', 'CV', 'CI', 'GM', 'GH', 'GN', 'GW', 
            'LR', 'ML', 'NE', 'NG', 'SN', 'SL', 'TG'
        ];
        
        return $query->whereIn('code', $ecowasCountries);
    }

    /**
     * Scope for Mano River Union countries.
     */
    public function scopeManoRiverUnion($query)
    {
        $mruCountries = ['LR', 'SL', 'GN', 'CI'];
        
        return $query->whereIn('code', $mruCountries);
    }

    /**
     * Helper Methods
     */

    /**
     * Get Liberia country instance.
     */
    public static function liberia()
    {
        return self::where('code', 'LR')->first();
    }

    /**
     * Check if this is Liberia.
     */
    public function isLiberia(): bool
    {
        return $this->code === 'LR';
    }

    /**
     * Get full name with code.
     */
    public function getFullName(): string
    {
        return "{$this->name} ({$this->code})";
    }

    /**
     * Get formatted phone code.
     */
    public function getFormattedPhoneCode(): string
    {
        return $this->phone_code ? "+{$this->phone_code}" : '';
    }

    /**
     * Get currency symbol.
     */
    public function getCurrencySymbol(): string
    {
        $symbols = [
            'LRD' => 'L$',  // Liberian Dollar
            'USD' => '$',   // US Dollar (also used in Liberia)
            'SLL' => 'Le',  // Sierra Leonean Leone
            'GNF' => 'FG',  // Guinean Franc
            'XOF' => 'CFA', // West African CFA Franc
            'GHS' => '₵',   // Ghanaian Cedi
            'NGN' => '₦'    // Nigerian Naira
        ];
        
        return $symbols[$this->currency] ?? $this->currency;
    }

    /**
     * Check if country uses USD.
     */
    public function usesUSD(): bool
    {
        // Liberia uses both LRD and USD
        return in_array($this->code, ['LR', 'US']);
    }

    /**
     * Get dropdown options with regional grouping.
     */
    public static function getDropdownOptions(): array
    {
        return self::active()
            ->ordered()
            ->get()
            ->mapWithKeys(function ($country) {
                $prefix = $country->isLiberia() ? '🇱🇷 ' : '';
                return [$country->id => $prefix . $country->name];
            })
            ->toArray();
    }

    /**
     * Get regional countries (West Africa priority).
     */
    public static function getRegionalCountries(): array
    {
        $countries = [];
        
        // Liberia first
        $liberia = self::liberia();
        if ($liberia) {
            $countries['primary'] = [$liberia];
        }
        
        // Mano River Union countries
        $countries['mano_river'] = self::manoRiverUnion()
            ->where('code', '!=', 'LR')
            ->ordered()
            ->get();
        
        // Other ECOWAS countries
        $countries['ecowas'] = self::ecowas()
            ->whereNotIn('code', ['LR', 'SL', 'GN', 'CI'])
            ->ordered()
            ->get();
        
        // Other countries
        $countries['others'] = self::active()
            ->where('subregion', '!=', 'Western Africa')
            ->ordered()
            ->get();
        
        return $countries;
    }

    /**
     * Seed default countries.
     */
    public static function seedDefaults(): void
    {
        foreach (self::DEFAULT_COUNTRIES as $countryData) {
            self::firstOrCreate(
                ['code' => $countryData['code']],
                $countryData
            );
        }
    }

    /**
     * Get statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total_counties' => $this->counties()->count(),
            'total_cities' => $this->cities()->count(),
            'active_counties' => $this->counties()->where('is_active', true)->count(),
            'active_cities' => $this->cities()->where('is_active', true)->count()
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
            'phone_code' => $this->getFormattedPhoneCode(),
            'currency' => $this->currency,
            'currency_symbol' => $this->getCurrencySymbol(),
            'capital' => $this->capital,
            'is_primary' => $this->isLiberia(),
            'region' => $this->region,
            'subregion' => $this->subregion
        ];
    }
}