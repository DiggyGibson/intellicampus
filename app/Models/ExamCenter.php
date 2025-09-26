<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamCenter extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'exam_centers';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'center_code',
        'center_name',
        'center_type',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'total_capacity',
        'computer_seats',
        'paper_seats',
        'facilities',
        'contact_person',
        'contact_phone',
        'contact_email',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'facilities' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'total_capacity' => 'integer',
        'computer_seats' => 'integer',
        'paper_seats' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Standard facility types.
     */
    protected static $facilityTypes = [
        'computers' => 'Computer Lab',
        'parking' => 'Parking Available',
        'cafeteria' => 'Cafeteria/Canteen',
        'medical_room' => 'Medical Room',
        'backup_power' => 'Backup Power Supply',
        'internet_bandwidth' => 'High-Speed Internet',
        'cctv' => 'CCTV Surveillance',
        'biometric' => 'Biometric Attendance',
        'air_conditioning' => 'Air Conditioning',
        'wheelchair_access' => 'Wheelchair Accessible',
        'washrooms' => 'Adequate Washrooms',
        'drinking_water' => 'Drinking Water',
        'waiting_area' => 'Waiting Area for Parents',
        'locker_facility' => 'Locker Facility'
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate center code if not provided
        static::creating(function ($center) {
            if (!$center->center_code) {
                $center->center_code = self::generateCenterCode($center->city);
            }
            
            // Set default capacities if not provided
            if (!$center->total_capacity && ($center->computer_seats || $center->paper_seats)) {
                $center->total_capacity = ($center->computer_seats ?? 0) + ($center->paper_seats ?? 0);
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get exam sessions at this center.
     */
    public function sessions()
    {
        return $this->hasMany(ExamSession::class, 'center_id');
    }

    /**
     * Get seat allocations at this center.
     */
    public function seatAllocations()
    {
        return $this->hasMany(ExamSeatAllocation::class, 'center_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for active centers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for centers by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('center_type', $type);
    }

    /**
     * Scope for centers in a city.
     */
    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope for centers in a state.
     */
    public function scopeInState($query, $state)
    {
        return $query->where('state', $state);
    }

    /**
     * Scope for centers with computer facilities.
     */
    public function scopeWithComputers($query, $minSeats = 1)
    {
        return $query->where('computer_seats', '>=', $minSeats);
    }

    /**
     * Scope for centers with specific facilities.
     */
    public function scopeWithFacility($query, $facility)
    {
        return $query->whereJsonContains('facilities', $facility);
    }

    /**
     * Helper Methods
     */

    /**
     * Generate unique center code.
     */
    public static function generateCenterCode($city = null): string
    {
        $prefix = $city ? strtoupper(substr($city, 0, 3)) : 'CTR';
        
        $lastCenter = self::where('center_code', 'like', "{$prefix}%")
            ->orderBy('center_code', 'desc')
            ->first();
        
        if ($lastCenter) {
            $lastNumber = intval(substr($lastCenter->center_code, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return "{$prefix}{$newNumber}";
    }

    /**
     * Check availability for a date.
     */
    public function isAvailable($date, $sessionType = null): bool
    {
        $query = $this->sessions()
            ->where('session_date', $date)
            ->whereIn('status', ['scheduled', 'registration_open', 'registration_closed']);
        
        if ($sessionType) {
            $query->where('session_type', $sessionType);
        }
        
        $bookedCapacity = $query->sum('capacity');
        
        return $bookedCapacity < $this->total_capacity;
    }

    /**
     * Get available capacity for a date.
     */
    public function getAvailableCapacity($date, $examType = 'computer_based'): int
    {
        $bookedSeats = $this->sessions()
            ->where('session_date', $date)
            ->whereIn('status', ['scheduled', 'registration_open', 'registration_closed'])
            ->sum('registered_count');
        
        $capacity = match($examType) {
            'computer_based' => $this->computer_seats ?? 0,
            'paper_based' => $this->paper_seats ?? 0,
            default => $this->total_capacity
        };
        
        return max(0, $capacity - $bookedSeats);
    }

    /**
     * Book seats for an exam session.
     */
    public function bookSeats($numberOfSeats, $sessionId): bool
    {
        $availableCapacity = $this->getAvailableCapacity(
            ExamSession::find($sessionId)->session_date
        );
        
        if ($availableCapacity >= $numberOfSeats) {
            // Update session registered count
            $session = ExamSession::find($sessionId);
            $session->registered_count += $numberOfSeats;
            return $session->save();
        }
        
        return false;
    }

    /**
     * Check if center has specific facility.
     */
    public function hasFacility($facility): bool
    {
        if (!$this->facilities) {
            return false;
        }
        
        if (is_array($this->facilities)) {
            return isset($this->facilities[$facility]) && $this->facilities[$facility];
        }
        
        return false;
    }

    /**
     * Get list of available facilities.
     */
    public function getAvailableFacilities(): array
    {
        if (!$this->facilities) {
            return [];
        }
        
        $available = [];
        foreach ($this->facilities as $key => $value) {
            if ($value) {
                $available[] = self::$facilityTypes[$key] ?? $key;
            }
        }
        
        return $available;
    }

    /**
     * Calculate distance from coordinates.
     */
    public function distanceFrom($latitude, $longitude, $unit = 'km'): ?float
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }
        
        $earthRadius = $unit === 'miles' ? 3959 : 6371; // Earth radius in km or miles
        
        $latDelta = deg2rad($latitude - $this->latitude);
        $lonDelta = deg2rad($longitude - $this->longitude);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return round($earthRadius * $c, 2);
    }

    /**
     * Find nearest centers to coordinates.
     */
    public static function findNearest($latitude, $longitude, $limit = 5, $maxDistance = 50)
    {
        $centers = self::active()->get();
        
        $distances = [];
        foreach ($centers as $center) {
            $distance = $center->distanceFrom($latitude, $longitude);
            if ($distance !== null && $distance <= $maxDistance) {
                $distances[] = [
                    'center' => $center,
                    'distance' => $distance
                ];
            }
        }
        
        // Sort by distance
        usort($distances, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });
        
        return array_slice($distances, 0, $limit);
    }

    /**
     * Get center type label.
     */
    public function getTypeLabel(): string
    {
        return match($this->center_type) {
            'internal' => 'University Campus',
            'external' => 'External Venue',
            'online' => 'Online Center',
            'home' => 'Home-based',
            default => ucwords(str_replace('_', ' ', $this->center_type))
        };
    }

    /**
     * Get center statistics.
     */
    public function getStatistics(): array
    {
        $sessions = $this->sessions;
        $allocations = $this->seatAllocations;
        
        return [
            'total_sessions' => $sessions->count(),
            'upcoming_sessions' => $sessions->where('session_date', '>', now())->count(),
            'completed_sessions' => $sessions->where('status', 'completed')->count(),
            'total_candidates_served' => $allocations->count(),
            'attendance_rate' => $allocations->where('attendance_marked', true)->count() / max($allocations->count(), 1) * 100,
            'capacity_utilization' => [
                'total' => $this->total_capacity,
                'computer' => $this->computer_seats,
                'paper' => $this->paper_seats,
                'average_utilization' => $sessions->avg('registered_count') / max($this->total_capacity, 1) * 100
            ]
        ];
    }

    /**
     * Check if center is suitable for exam type.
     */
    public function isSuitableFor($examDeliveryMode): bool
    {
        return match($examDeliveryMode) {
            'computer_based' => $this->computer_seats > 0,
            'paper_based' => $this->paper_seats > 0,
            'online_proctored', 'online_unproctored' => $this->center_type === 'online',
            'hybrid' => $this->computer_seats > 0 && $this->paper_seats > 0,
            default => true
        };
    }

    /**
     * Activate the center.
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Deactivate the center.
     */
    public function deactivate(): bool
    {
        // Check if there are upcoming sessions
        $upcomingSessions = $this->sessions()
            ->where('session_date', '>', now())
            ->whereIn('status', ['scheduled', 'registration_open', 'registration_closed'])
            ->count();
        
        if ($upcomingSessions > 0) {
            return false; // Cannot deactivate with upcoming sessions
        }
        
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Generate center summary.
     */
    public function generateSummary(): array
    {
        return [
            'center_code' => $this->center_code,
            'center_name' => $this->center_name,
            'type' => $this->center_type,
            'location' => [
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'country' => $this->country,
                'postal_code' => $this->postal_code,
                'coordinates' => [
                    'latitude' => $this->latitude,
                    'longitude' => $this->longitude
                ]
            ],
            'capacity' => [
                'total' => $this->total_capacity,
                'computer_seats' => $this->computer_seats,
                'paper_seats' => $this->paper_seats
            ],
            'facilities' => $this->getAvailableFacilities(),
            'contact' => [
                'person' => $this->contact_person,
                'phone' => $this->contact_phone,
                'email' => $this->contact_email
            ],
            'status' => $this->is_active ? 'Active' : 'Inactive',
            'statistics' => $this->getStatistics()
        ];
    }
}