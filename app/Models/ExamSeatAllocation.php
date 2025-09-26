<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ExamSeatAllocation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'exam_seat_allocations';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'registration_id',
        'session_id',
        'center_id',
        'seat_number',
        'room_number',
        'floor',
        'building',
        'computer_number',
        'login_id',
        'password',
        'attendance_marked',
        'check_in_time',
        'check_out_time',
        'marked_by'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'attendance_marked' => 'boolean',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime'
    ];

    /**
     * The attributes that should be hidden.
     */
    protected $hidden = [
        'password'
    ];

    /**
     * Room layouts and capacities.
     */
    protected static $roomLayouts = [
        'classroom' => ['rows' => 10, 'columns' => 5, 'capacity' => 50],
        'computer_lab' => ['rows' => 8, 'columns' => 4, 'capacity' => 32],
        'lecture_hall' => ['rows' => 15, 'columns' => 10, 'capacity' => 150],
        'seminar_room' => ['rows' => 5, 'columns' => 4, 'capacity' => 20]
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate seat details on creation
        static::creating(function ($allocation) {
            // Auto-generate seat number if not provided
            if (!$allocation->seat_number) {
                $allocation->seat_number = self::generateSeatNumber($allocation);
            }
            
            // Generate login credentials for CBT
            if (!$allocation->login_id && $allocation->isComputerBased()) {
                $allocation->login_id = self::generateLoginId($allocation);
                $allocation->password = bcrypt(self::generatePassword());
            }
        });

        // Clear sensitive data after exam
        static::updated(function ($allocation) {
            if ($allocation->check_out_time && $allocation->isDirty('check_out_time')) {
                // Clear login credentials after checkout
                $allocation->password = null;
                $allocation->saveQuietly();
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the registration for this allocation.
     */
    public function registration()
    {
        return $this->belongsTo(EntranceExamRegistration::class, 'registration_id');
    }

    /**
     * Get the session for this allocation.
     */
    public function session()
    {
        return $this->belongsTo(ExamSession::class, 'session_id');
    }

    /**
     * Get the center for this allocation.
     */
    public function center()
    {
        return $this->belongsTo(ExamCenter::class, 'center_id');
    }

    /**
     * Get the user who marked attendance.
     */
    public function attendanceMarker()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    /**
     * Scopes
     */

    /**
     * Scope for allocations with attendance marked.
     */
    public function scopePresent($query)
    {
        return $query->where('attendance_marked', true);
    }

    /**
     * Scope for allocations without attendance marked.
     */
    public function scopeAbsent($query)
    {
        return $query->where('attendance_marked', false)
            ->whereHas('session', function ($q) {
                $q->where('status', 'completed');
            });
    }

    /**
     * Scope for allocations in a specific room.
     */
    public function scopeInRoom($query, $roomNumber)
    {
        return $query->where('room_number', $roomNumber);
    }

    /**
     * Scope for allocations on a specific floor.
     */
    public function scopeOnFloor($query, $floor)
    {
        return $query->where('floor', $floor);
    }

    /**
     * Scope for allocations in a specific building.
     */
    public function scopeInBuilding($query, $building)
    {
        return $query->where('building', $building);
    }

    /**
     * Scope for checked-in allocations.
     */
    public function scopeCheckedIn($query)
    {
        return $query->whereNotNull('check_in_time');
    }

    /**
     * Scope for checked-out allocations.
     */
    public function scopeCheckedOut($query)
    {
        return $query->whereNotNull('check_out_time');
    }

    /**
     * Helper Methods
     */

    /**
     * Generate unique seat number.
     */
    public static function generateSeatNumber($allocation): string
    {
        $session = ExamSession::find($allocation->session_id);
        $center = ExamCenter::find($allocation->center_id);
        
        // Get next available seat number for this session
        $lastSeat = self::where('session_id', $allocation->session_id)
            ->where('center_id', $allocation->center_id)
            ->orderBy('seat_number', 'desc')
            ->first();
        
        if ($lastSeat && preg_match('/\d+$/', $lastSeat->seat_number, $matches)) {
            $nextNumber = intval($matches[0]) + 1;
        } else {
            $nextNumber = 1;
        }
        
        $roomPrefix = $allocation->room_number ? substr($allocation->room_number, 0, 3) : 'R01';
        
        return $roomPrefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate login ID for CBT.
     */
    public static function generateLoginId($allocation): string
    {
        $registration = EntranceExamRegistration::find($allocation->registration_id);
        
        if ($registration && $registration->hall_ticket_number) {
            return $registration->hall_ticket_number;
        }
        
        return 'CBT' . date('md') . str_pad($allocation->id, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate random password.
     */
    public static function generatePassword($length = 8): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $password;
    }

    /**
     * Check if allocation is for computer-based test.
     */
    public function isComputerBased(): bool
    {
        if ($this->session && $this->session->exam) {
            return in_array($this->session->exam->delivery_mode, [
                'computer_based',
                'online_proctored',
                'online_unproctored'
            ]);
        }
        
        return !is_null($this->computer_number);
    }

    /**
     * Mark attendance for check-in.
     */
    public function checkIn($markedBy = null): bool
    {
        if ($this->check_in_time) {
            return false; // Already checked in
        }
        
        $this->attendance_marked = true;
        $this->check_in_time = now();
        $this->marked_by = $markedBy ?? auth()->id();
        
        return $this->save();
    }

    /**
     * Mark attendance for check-out.
     */
    public function checkOut(): bool
    {
        if (!$this->check_in_time) {
            return false; // Not checked in yet
        }
        
        if ($this->check_out_time) {
            return false; // Already checked out
        }
        
        $this->check_out_time = now();
        
        return $this->save();
    }

    /**
     * Mark as absent.
     */
    public function markAbsent($markedBy = null): bool
    {
        $this->attendance_marked = false;
        $this->marked_by = $markedBy ?? auth()->id();
        $this->check_in_time = null;
        $this->check_out_time = null;
        
        return $this->save();
    }

    /**
     * Get exam duration for this candidate.
     */
    public function getExamDuration(): ?int
    {
        if ($this->check_in_time && $this->check_out_time) {
            return $this->check_in_time->diffInMinutes($this->check_out_time);
        }
        
        return null;
    }

    /**
     * Check if candidate is late.
     */
    public function isLate($allowedMinutes = 30): bool
    {
        if (!$this->check_in_time || !$this->session) {
            return false;
        }
        
        $sessionStart = Carbon::parse(
            $this->session->session_date->format('Y-m-d') . ' ' . $this->session->start_time
        );
        
        return $this->check_in_time > $sessionStart->addMinutes($allowedMinutes);
    }

    /**
     * Get late arrival minutes.
     */
    public function getLateMinutes(): ?int
    {
        if (!$this->check_in_time || !$this->session) {
            return null;
        }
        
        $sessionStart = Carbon::parse(
            $this->session->session_date->format('Y-m-d') . ' ' . $this->session->start_time
        );
        
        if ($this->check_in_time > $sessionStart) {
            return $sessionStart->diffInMinutes($this->check_in_time);
        }
        
        return 0;
    }

    /**
     * Check if candidate left early.
     */
    public function leftEarly(): bool
    {
        if (!$this->check_out_time || !$this->session) {
            return false;
        }
        
        $sessionEnd = Carbon::parse(
            $this->session->session_date->format('Y-m-d') . ' ' . $this->session->end_time
        );
        
        return $this->check_out_time < $sessionEnd;
    }

    /**
     * Get early departure minutes.
     */
    public function getEarlyDepartureMinutes(): ?int
    {
        if (!$this->check_out_time || !$this->session) {
            return null;
        }
        
        $sessionEnd = Carbon::parse(
            $this->session->session_date->format('Y-m-d') . ' ' . $this->session->end_time
        );
        
        if ($this->check_out_time < $sessionEnd) {
            return $this->check_out_time->diffInMinutes($sessionEnd);
        }
        
        return 0;
    }

    /**
     * Allocate to a specific room.
     */
    public function allocateToRoom($roomNumber, $floor = null, $building = null): bool
    {
        $this->room_number = $roomNumber;
        $this->floor = $floor;
        $this->building = $building;
        
        // Regenerate seat number for new room
        $this->seat_number = self::generateSeatNumber($this);
        
        return $this->save();
    }

    /**
     * Allocate to a specific computer.
     */
    public function allocateToComputer($computerNumber): bool
    {
        if (!$this->isComputerBased()) {
            return false;
        }
        
        $this->computer_number = $computerNumber;
        
        // Generate new login credentials
        if (!$this->login_id) {
            $this->login_id = self::generateLoginId($this);
            $this->password = bcrypt(self::generatePassword());
        }
        
        return $this->save();
    }

    /**
     * Get seat location description.
     */
    public function getSeatLocation(): string
    {
        $location = [];
        
        if ($this->building) {
            $location[] = "Building: {$this->building}";
        }
        
        if ($this->floor) {
            $location[] = "Floor: {$this->floor}";
        }
        
        if ($this->room_number) {
            $location[] = "Room: {$this->room_number}";
        }
        
        if ($this->seat_number) {
            $location[] = "Seat: {$this->seat_number}";
        }
        
        if ($this->computer_number) {
            $location[] = "Computer: {$this->computer_number}";
        }
        
        return implode(', ', $location);
    }

    /**
     * Generate seat map position.
     */
    public function getSeatMapPosition($layout = 'classroom'): array
    {
        $config = self::$roomLayouts[$layout] ?? self::$roomLayouts['classroom'];
        
        // Extract seat number
        if (preg_match('/(\d+)$/', $this->seat_number, $matches)) {
            $seatNum = intval($matches[1]);
            
            $row = ceil($seatNum / $config['columns']);
            $column = (($seatNum - 1) % $config['columns']) + 1;
            
            return [
                'row' => $row,
                'column' => $column,
                'label' => chr(64 + $row) . $column // e.g., A1, B2, etc.
            ];
        }
        
        return ['row' => 1, 'column' => 1, 'label' => 'A1'];
    }

    /**
     * Check if seat is window seat.
     */
    public function isWindowSeat($layout = 'classroom'): bool
    {
        $position = $this->getSeatMapPosition($layout);
        $config = self::$roomLayouts[$layout] ?? self::$roomLayouts['classroom'];
        
        return $position['column'] == 1 || $position['column'] == $config['columns'];
    }

    /**
     * Check if seat is aisle seat.
     */
    public function isAisleSeat($layout = 'classroom'): bool
    {
        $position = $this->getSeatMapPosition($layout);
        
        // Assuming aisles are after every 2 columns
        return $position['column'] % 2 == 0 || $position['column'] % 2 == 1;
    }

    /**
     * Get neighboring seats.
     */
    public function getNeighboringSeats(): array
    {
        $neighbors = [];
        
        // Get seats in same room and session
        $sameRoom = self::where('session_id', $this->session_id)
            ->where('room_number', $this->room_number)
            ->where('id', '!=', $this->id)
            ->get();
        
        foreach ($sameRoom as $seat) {
            $thisPosition = $this->getSeatMapPosition();
            $seatPosition = $seat->getSeatMapPosition();
            
            // Check if adjacent (row or column differs by 1)
            $rowDiff = abs($thisPosition['row'] - $seatPosition['row']);
            $colDiff = abs($thisPosition['column'] - $seatPosition['column']);
            
            if (($rowDiff == 1 && $colDiff == 0) || ($rowDiff == 0 && $colDiff == 1)) {
                $neighbors[] = $seat;
            }
        }
        
        return $neighbors;
    }

    /**
     * Generate hall ticket seat details.
     */
    public function getHallTicketDetails(): array
    {
        return [
            'center' => [
                'name' => $this->center->center_name ?? 'N/A',
                'address' => $this->center->address ?? 'N/A',
                'city' => $this->center->city ?? 'N/A'
            ],
            'session' => [
                'date' => $this->session->session_date->format('l, F j, Y'),
                'time' => $this->session->start_time . ' - ' . $this->session->end_time,
                'reporting_time' => Carbon::parse($this->session->start_time)->subMinutes(30)->format('H:i')
            ],
            'seat' => [
                'building' => $this->building,
                'floor' => $this->floor,
                'room' => $this->room_number,
                'seat_number' => $this->seat_number,
                'computer' => $this->computer_number
            ],
            'instructions' => $this->getExamInstructions()
        ];
    }

    /**
     * Get exam day instructions.
     */
    protected function getExamInstructions(): array
    {
        $instructions = [
            'Report 30 minutes before exam start time',
            'Carry valid photo ID and hall ticket',
            'Mobile phones and electronic devices are not allowed',
            'Rough sheets will be provided at the center'
        ];
        
        if ($this->isComputerBased()) {
            $instructions[] = 'Login credentials will be provided at the center';
            $instructions[] = 'System check will be done before exam starts';
        }
        
        return $instructions;
    }

    /**
     * Generate allocation summary.
     */
    public function generateSummary(): array
    {
        return [
            'registration' => [
                'number' => $this->registration->registration_number ?? null,
                'candidate_name' => $this->registration->candidate_name ?? null
            ],
            'session' => [
                'code' => $this->session->session_code ?? null,
                'date' => $this->session->session_date->format('Y-m-d'),
                'time' => $this->session->start_time . ' - ' . $this->session->end_time
            ],
            'location' => $this->getSeatLocation(),
            'seat_details' => [
                'seat_number' => $this->seat_number,
                'room' => $this->room_number,
                'floor' => $this->floor,
                'building' => $this->building,
                'computer' => $this->computer_number,
                'position' => $this->getSeatMapPosition()
            ],
            'attendance' => [
                'marked' => $this->attendance_marked,
                'check_in' => $this->check_in_time?->format('H:i:s'),
                'check_out' => $this->check_out_time?->format('H:i:s'),
                'duration_minutes' => $this->getExamDuration(),
                'late_by_minutes' => $this->getLateMinutes(),
                'left_early_by_minutes' => $this->getEarlyDepartureMinutes()
            ],
            'is_cbt' => $this->isComputerBased()
        ];
    }
}