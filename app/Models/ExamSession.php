<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ExamSession extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'exam_sessions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'exam_id',
        'center_id',
        'session_code',
        'session_date',
        'start_time',
        'end_time',
        'session_type',
        'capacity',
        'registered_count',
        'proctoring_type',
        'proctor_assignments',
        'candidates_per_proctor',
        'status',
        'special_instructions'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'capacity' => 'integer',
        'registered_count' => 'integer',
        'candidates_per_proctor' => 'integer',
        'proctor_assignments' => 'array'
    ];

    /**
     * Session duration presets in minutes.
     */
    protected static $sessionDurations = [
        'morning' => ['start' => '09:00', 'end' => '12:00'],
        'afternoon' => ['start' => '14:00', 'end' => '17:00'],
        'evening' => ['start' => '18:00', 'end' => '21:00'],
        'full_day' => ['start' => '09:00', 'end' => '17:00']
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate session code on creation
        static::creating(function ($session) {
            if (!$session->session_code) {
                $session->session_code = self::generateSessionCode($session);
            }
            
            // Set default status
            if (!$session->status) {
                $session->status = 'scheduled';
            }
            
            // Set default registered count
            if (is_null($session->registered_count)) {
                $session->registered_count = 0;
            }
            
            // Set times based on session type if not provided
            if ($session->session_type && !$session->start_time && !$session->end_time) {
                $times = self::$sessionDurations[$session->session_type] ?? null;
                if ($times) {
                    $session->start_time = $times['start'];
                    $session->end_time = $times['end'];
                }
            }
        });

        // Update status based on registration
        static::updating(function ($session) {
            // Auto-update status when capacity is reached
            if ($session->isDirty('registered_count')) {
                if ($session->registered_count >= $session->capacity && 
                    $session->status === 'registration_open') {
                    $session->status = 'registration_closed';
                }
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the exam for this session.
     */
    public function exam()
    {
        return $this->belongsTo(EntranceExam::class, 'exam_id');
    }

    /**
     * Get the center for this session.
     */
    public function center()
    {
        return $this->belongsTo(ExamCenter::class, 'center_id');
    }

    /**
     * Get seat allocations for this session.
     */
    public function seatAllocations()
    {
        return $this->hasMany(ExamSeatAllocation::class, 'session_id');
    }

    /**
     * Get exam responses for this session.
     */
    public function examResponses()
    {
        return $this->hasMany(ExamResponse::class, 'session_id');
    }

    /**
     * Get question papers for this session.
     */
    public function questionPapers()
    {
        return $this->hasMany(ExamQuestionPaper::class, 'session_id');
    }

    /**
     * Get proctors assigned to this session.
     */
    public function proctors()
    {
        if ($this->proctor_assignments) {
            return User::whereIn('id', $this->proctor_assignments)->get();
        }
        return collect();
    }

    /**
     * Scopes
     */

    /**
     * Scope for scheduled sessions.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope for sessions open for registration.
     */
    public function scopeRegistrationOpen($query)
    {
        return $query->where('status', 'registration_open');
    }

    /**
     * Scope for upcoming sessions.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('session_date', '>', now()->toDateString());
    }

    /**
     * Scope for today's sessions.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('session_date', now()->toDateString());
    }

    /**
     * Scope for completed sessions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for sessions with available seats.
     */
    public function scopeWithAvailableSeats($query)
    {
        return $query->whereColumn('registered_count', '<', 'capacity');
    }

    /**
     * Scope for sessions by date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('session_date', [$startDate, $endDate]);
    }

    /**
     * Helper Methods
     */

    /**
     * Generate unique session code.
     */
    public static function generateSessionCode($session): string
    {
        $exam = EntranceExam::find($session->exam_id);
        $examCode = $exam ? substr($exam->exam_code, -3) : '000';
        $dateCode = Carbon::parse($session->session_date)->format('md');
        
        $typeCode = match($session->session_type) {
            'morning' => 'M',
            'afternoon' => 'A',
            'evening' => 'E',
            'full_day' => 'F',
            default => 'X'
        };
        
        $count = self::where('exam_id', $session->exam_id)
            ->where('session_date', $session->session_date)
            ->where('session_type', $session->session_type)
            ->count();
        
        $number = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
        
        return "SESS-{$examCode}-{$dateCode}{$typeCode}{$number}";
    }

    /**
     * Open registration for the session.
     */
    public function openRegistration(): bool
    {
        if (!in_array($this->status, ['scheduled', 'registration_closed'])) {
            return false;
        }
        
        if ($this->registered_count >= $this->capacity) {
            return false;
        }
        
        $this->status = 'registration_open';
        return $this->save();
    }

    /**
     * Close registration for the session.
     */
    public function closeRegistration(): bool
    {
        if ($this->status !== 'registration_open') {
            return false;
        }
        
        $this->status = 'registration_closed';
        return $this->save();
    }

    /**
     * Start the session.
     */
    public function start(): bool
    {
        if (!$this->canStart()) {
            return false;
        }
        
        $this->status = 'in_progress';
        return $this->save();
    }

    /**
     * Complete the session.
     */
    public function complete(): bool
    {
        if ($this->status !== 'in_progress') {
            return false;
        }
        
        $this->status = 'completed';
        return $this->save();
    }

    /**
     * Cancel the session.
     */
    public function cancel($reason = null): bool
    {
        if (in_array($this->status, ['completed', 'cancelled'])) {
            return false;
        }
        
        $this->status = 'cancelled';
        
        if ($reason) {
            $this->special_instructions = ($this->special_instructions ?? '') . ' | Cancelled: ' . $reason;
        }
        
        // TODO: Notify all registered candidates
        
        return $this->save();
    }

    /**
     * Postpone the session.
     */
    public function postpone($newDate, $reason = null): bool
    {
        if (in_array($this->status, ['completed', 'cancelled'])) {
            return false;
        }
        
        $this->status = 'postponed';
        
        if ($reason) {
            $this->special_instructions = ($this->special_instructions ?? '') . 
                ' | Postponed to ' . $newDate->format('Y-m-d') . ': ' . $reason;
        }
        
        // TODO: Create new session for the new date
        // TODO: Transfer all registrations to new session
        // TODO: Notify all registered candidates
        
        return $this->save();
    }

    /**
     * Check if session can start.
     */
    public function canStart(): bool
    {
        // Check if it's the session date
        if (!$this->session_date->isToday()) {
            return false;
        }
        
        // Check if it's within start time window (allow 15 minutes early)
        $startTime = Carbon::parse($this->session_date->format('Y-m-d') . ' ' . $this->start_time);
        $now = now();
        
        if ($now < $startTime->subMinutes(15)) {
            return false;
        }
        
        return in_array($this->status, ['scheduled', 'registration_closed']);
    }

    /**
     * Check if registration is available.
     */
    public function isRegistrationAvailable(): bool
    {
        return $this->status === 'registration_open' && 
               $this->registered_count < $this->capacity &&
               $this->session_date > now();
    }

    /**
     * Get available seats count.
     */
    public function getAvailableSeats(): int
    {
        return max(0, $this->capacity - $this->registered_count);
    }

    /**
     * Get occupancy percentage.
     */
    public function getOccupancyPercentage(): float
    {
        if ($this->capacity == 0) {
            return 0;
        }
        
        return round(($this->registered_count / $this->capacity) * 100, 2);
    }

    /**
     * Register a candidate for the session.
     */
    public function registerCandidate($registrationId): bool
    {
        if (!$this->isRegistrationAvailable()) {
            return false;
        }
        
        // Create seat allocation
        $allocation = ExamSeatAllocation::create([
            'registration_id' => $registrationId,
            'session_id' => $this->id,
            'center_id' => $this->center_id
        ]);
        
        if ($allocation) {
            $this->registered_count++;
            
            // Close registration if capacity reached
            if ($this->registered_count >= $this->capacity) {
                $this->status = 'registration_closed';
            }
            
            return $this->save();
        }
        
        return false;
    }

    /**
     * Unregister a candidate from the session.
     */
    public function unregisterCandidate($registrationId): bool
    {
        $allocation = ExamSeatAllocation::where('registration_id', $registrationId)
            ->where('session_id', $this->id)
            ->first();
        
        if ($allocation) {
            $allocation->delete();
            
            $this->registered_count = max(0, $this->registered_count - 1);
            
            // Reopen registration if seats available
            if ($this->registered_count < $this->capacity && 
                $this->status === 'registration_closed') {
                $this->status = 'registration_open';
            }
            
            return $this->save();
        }
        
        return false;
    }

    /**
     * Assign proctors to the session.
     */
    public function assignProctors(array $proctorIds): bool
    {
        $this->proctor_assignments = $proctorIds;
        return $this->save();
    }

    /**
     * Add a proctor to the session.
     */
    public function addProctor($proctorId): bool
    {
        $proctors = $this->proctor_assignments ?? [];
        
        if (!in_array($proctorId, $proctors)) {
            $proctors[] = $proctorId;
            $this->proctor_assignments = $proctors;
            return $this->save();
        }
        
        return false;
    }

    /**
     * Remove a proctor from the session.
     */
    public function removeProctor($proctorId): bool
    {
        $proctors = $this->proctor_assignments ?? [];
        
        if (($key = array_search($proctorId, $proctors)) !== false) {
            unset($proctors[$key]);
            $this->proctor_assignments = array_values($proctors);
            return $this->save();
        }
        
        return false;
    }

    /**
     * Calculate required proctors.
     */
    public function getRequiredProctors(): int
    {
        if ($this->candidates_per_proctor == 0) {
            return 1;
        }
        
        return (int) ceil($this->registered_count / $this->candidates_per_proctor);
    }

    /**
     * Check if session has enough proctors.
     */
    public function hasEnoughProctors(): bool
    {
        $assignedProctors = count($this->proctor_assignments ?? []);
        return $assignedProctors >= $this->getRequiredProctors();
    }

    /**
     * Get session duration in minutes.
     */
    public function getDurationMinutes(): int
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        
        return $start->diffInMinutes($end);
    }

    /**
     * Check if session is ongoing.
     */
    public function isOngoing(): bool
    {
        if (!$this->session_date->isToday()) {
            return false;
        }
        
        $now = now();
        $start = Carbon::parse($this->session_date->format('Y-m-d') . ' ' . $this->start_time);
        $end = Carbon::parse($this->session_date->format('Y-m-d') . ' ' . $this->end_time);
        
        return $now->between($start, $end);
    }

    /**
     * Get attendance statistics.
     */
    public function getAttendanceStats(): array
    {
        $allocations = $this->seatAllocations;
        
        return [
            'total_registered' => $this->registered_count,
            'present' => $allocations->where('attendance_marked', true)->count(),
            'absent' => $allocations->where('attendance_marked', false)->count(),
            'attendance_rate' => $this->registered_count > 0 
                ? round(($allocations->where('attendance_marked', true)->count() / $this->registered_count) * 100, 2)
                : 0
        ];
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'scheduled' => 'blue',
            'registration_open' => 'green',
            'registration_closed' => 'yellow',
            'in_progress' => 'purple',
            'completed' => 'gray',
            'cancelled' => 'red',
            'postponed' => 'orange',
            default => 'gray'
        };
    }

    /**
     * Generate session summary.
     */
    public function generateSummary(): array
    {
        return [
            'session_code' => $this->session_code,
            'exam' => [
                'code' => $this->exam->exam_code ?? null,
                'name' => $this->exam->exam_name ?? null
            ],
            'center' => [
                'code' => $this->center->center_code ?? null,
                'name' => $this->center->center_name ?? null,
                'city' => $this->center->city ?? null
            ],
            'schedule' => [
                'date' => $this->session_date->format('Y-m-d'),
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'duration_minutes' => $this->getDurationMinutes(),
                'session_type' => $this->session_type
            ],
            'capacity' => [
                'total' => $this->capacity,
                'registered' => $this->registered_count,
                'available' => $this->getAvailableSeats(),
                'occupancy_percentage' => $this->getOccupancyPercentage()
            ],
            'proctoring' => [
                'type' => $this->proctoring_type,
                'required_proctors' => $this->getRequiredProctors(),
                'assigned_proctors' => count($this->proctor_assignments ?? []),
                'candidates_per_proctor' => $this->candidates_per_proctor
            ],
            'status' => $this->status,
            'attendance' => $this->getAttendanceStats()
        ];
    }
}