<?php
// File: app/Http/Controllers/SchedulingController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\Room;
use App\Models\Building;
use App\Models\TimeSlot;
use App\Models\CourseSection;
use App\Models\ScheduleConflict;
use App\Models\ScheduleChange;
use App\Models\FacultyAvailability;
use App\Models\TeachingLoad;
use App\Models\RoomBooking;
use App\Models\Department;
use App\Models\AcademicTerm;
use App\Services\SchedulingService;
use App\Services\ConflictDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SchedulingController extends Controller
{
    protected $schedulingService;
    protected $conflictService;
    
    public function __construct(
        SchedulingService $schedulingService,
        ConflictDetectionService $conflictService
    ) {
        $this->schedulingService = $schedulingService;
        $this->conflictService = $conflictService;
    }

    /**
     * Display scheduling dashboard
     */
    public function index(Request $request)
    {
        $termId = $request->get('term_id', $this->getCurrentTermId());
        
        $stats = [
            'total_sections' => CourseSection::where('term_id', $termId)->count(),
            'scheduled_sections' => ClassSchedule::whereHas('section', function($q) use ($termId) {
                $q->where('term_id', $termId);
            })->distinct('section_id')->count('section_id'),
            'total_rooms' => Room::where('is_active', true)->count(),
            'conflicts' => ScheduleConflict::where('is_resolved', false)->count(),
            'pending_changes' => ScheduleChange::where('status', 'pending')->count(),
        ];
        
        $recentChanges = ScheduleChange::with(['originalSchedule.section.course', 'requestedBy'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        $terms = AcademicTerm::orderBy('start_date', 'desc')->get();
        
        return view('scheduling.index', compact('stats', 'recentChanges', 'termId', 'terms'));
    }

    /**
     * Master timetable view
     */
    public function timetable(Request $request)
    {
        $filters = [
            'term_id' => $request->get('term_id', $this->getCurrentTermId()),
            'department_id' => $request->get('department_id'),
            'building_id' => $request->get('building_id'),
            'day' => $request->get('day'),
            'view_type' => $request->get('view', 'week') // week, day, room, instructor
        ];
        
        $schedules = $this->schedulingService->getFilteredSchedules($filters);
        $timeSlots = TimeSlot::where('is_active', true)->orderBy('start_time')->get();
        $buildings = Building::where('is_active', true)->get();
        $departments = Department::all();
        
        return view('scheduling.timetable', compact('schedules', 'timeSlots', 'buildings', 'departments', 'filters'));
    }

    /**
     * Create/Edit schedule for a section
     */
    public function createSchedule($sectionId)
    {
        $section = CourseSection::with(['course', 'primaryInstructor'])->findOrFail($sectionId);
        $existingSchedules = ClassSchedule::where('section_id', $sectionId)->get();
        
        // Get available rooms
        $rooms = Room::where('is_active', true)
            ->where('capacity', '>=', $section->enrollment_capacity)
            ->orderBy('building_id')
            ->orderBy('room_code')
            ->get();
        
        // Get instructor availability
        $instructorAvailability = FacultyAvailability::where('faculty_id', $section->primary_instructor_id)
            ->where('term_id', $section->term_id)
            ->get();
        
        // Get time slots
        $timeSlots = TimeSlot::where('is_active', true)->orderBy('start_time')->get();
        
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        return view('scheduling.create', compact('section', 'existingSchedules', 'rooms', 'instructorAvailability', 'timeSlots', 'days'));
    }

    /**
     * Store new schedule
     */
    public function storeSchedule(Request $request, $sectionId)
    {
        $section = CourseSection::findOrFail($sectionId);
        
        $validated = $request->validate([
            'schedules' => 'required|array',
            'schedules.*.day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'schedules.*.start_time' => 'required|date_format:H:i',
            'schedules.*.end_time' => 'required|date_format:H:i|after:schedules.*.start_time',
            'schedules.*.room_id' => 'required|exists:rooms,id',
            'schedules.*.schedule_type' => 'required|in:lecture,lab,tutorial,seminar',
            'schedules.*.is_online' => 'boolean',
            'schedules.*.online_link' => 'nullable|url|required_if:schedules.*.is_online,true',
        ]);
        
        DB::transaction(function() use ($validated, $section) {
            foreach ($validated['schedules'] as $scheduleData) {
                $scheduleData['section_id'] = $section->id;
                $scheduleData['instructor_id'] = $section->primary_instructor_id;
                $scheduleData['effective_from'] = $section->term->start_date;
                $scheduleData['effective_until'] = $section->term->end_date;
                
                // Check for conflicts before creating
                $conflicts = $this->conflictService->checkScheduleConflicts($scheduleData);
                
                if (!empty($conflicts)) {
                    // Create schedule with conflicts (admin can override)
                    $schedule = ClassSchedule::create($scheduleData);
                    
                    // Record conflicts
                    foreach ($conflicts as $conflict) {
                        ScheduleConflict::create([
                            'conflict_type' => $conflict['type'],
                            'schedule_1_id' => $schedule->id,
                            'schedule_2_id' => $conflict['conflicting_schedule_id'],
                            'description' => $conflict['description'],
                            'severity' => $conflict['severity']
                        ]);
                    }
                } else {
                    ClassSchedule::create($scheduleData);
                }
            }
            
            // Update teaching load
            $this->schedulingService->updateTeachingLoad($section->primary_instructor_id, $section->term_id);
        });
        
        return redirect()->route('scheduling.section', $sectionId)
            ->with('success', 'Schedule created successfully');
    }

    /**
     * View section schedule
     */
    public function sectionSchedule($sectionId)
    {
        $section = CourseSection::with(['course', 'primaryInstructor', 'term'])->findOrFail($sectionId);
        $schedules = ClassSchedule::where('section_id', $sectionId)
            ->with(['room.building', 'instructor'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        
        $conflicts = ScheduleConflict::where('is_resolved', false)
            ->where(function($query) use ($schedules) {
                $scheduleIds = $schedules->pluck('id');
                $query->whereIn('schedule_1_id', $scheduleIds)
                      ->orWhereIn('schedule_2_id', $scheduleIds);
            })
            ->get();
        
        return view('scheduling.section', compact('section', 'schedules', 'conflicts'));
    }

    /**
     * Room management
     */
    public function rooms()
    {
        $buildings = Building::with(['rooms' => function($query) {
            $query->orderBy('room_code');
        }])->get();
        
        return view('scheduling.rooms', compact('buildings'));
    }

    /**
     * Create/Edit room
     */
    public function editRoom($id = null)
    {
        $room = $id ? Room::findOrFail($id) : new Room();
        $buildings = Building::where('is_active', true)->get();
        
        $roomTypes = [
            'classroom' => 'Classroom',
            'lab' => 'Laboratory',
            'auditorium' => 'Auditorium',
            'seminar' => 'Seminar Room',
            'computer_lab' => 'Computer Lab',
            'conference' => 'Conference Room'
        ];
        
        $equipment = [
            'projector' => 'Projector',
            'whiteboard' => 'Whiteboard',
            'smartboard' => 'Smart Board',
            'computers' => 'Computers',
            'ac' => 'Air Conditioning',
            'audio_system' => 'Audio System',
            'video_conferencing' => 'Video Conferencing'
        ];
        
        return view('scheduling.room-edit', compact('room', 'buildings', 'roomTypes', 'equipment'));
    }

    /**
     * Save room
     */
    public function saveRoom(Request $request, $id = null)
    {
        $validated = $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'room_code' => 'required|string|max:20|unique:rooms,room_code,' . $id,
            'room_name' => 'required|string|max:255',
            'room_type' => 'required|string|max:30',
            'capacity' => 'required|integer|min:1',
            'exam_capacity' => 'nullable|integer|min:1',
            'equipment' => 'nullable|array',
            'is_accessible' => 'boolean',
            'has_ac' => 'boolean',
            'has_projector' => 'boolean',
            'has_computers' => 'boolean',
            'is_active' => 'boolean',
            'notes' => 'nullable|string'
        ]);
        
        if ($id) {
            $room = Room::findOrFail($id);
            $room->update($validated);
        } else {
            Room::create($validated);
        }
        
        return redirect()->route('scheduling.rooms')
            ->with('success', 'Room saved successfully');
    }

    /**
     * Faculty availability management
     */
    public function facultyAvailability($facultyId = null)
    {
        $facultyId = $facultyId ?: Auth::id();
        $termId = $this->getCurrentTermId();
        
        $availability = FacultyAvailability::where('faculty_id', $facultyId)
            ->where('term_id', $termId)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
        
        $teachingLoad = TeachingLoad::firstOrCreate(
            ['faculty_id' => $facultyId, 'term_id' => $termId],
            [
                'min_credit_hours' => 9,
                'max_credit_hours' => 15,
                'max_courses' => 4,
                'max_preparations' => 3
            ]
        );
        
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $timeSlots = TimeSlot::where('is_active', true)->orderBy('start_time')->get();
        
        return view('scheduling.faculty-availability', compact('availability', 'teachingLoad', 'days', 'timeSlots', 'facultyId'));
    }

    /**
     * Save faculty availability
     */
    public function saveFacultyAvailability(Request $request, $facultyId)
    {
        $validated = $request->validate([
            'availability' => 'array',
            'availability.*.day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'availability.*.start_time' => 'required|date_format:H:i',
            'availability.*.end_time' => 'required|date_format:H:i|after:availability.*.start_time',
            'availability.*.is_available' => 'boolean',
            'availability.*.preference_level' => 'in:preferred,neutral,avoid',
            'teaching_load.min_credit_hours' => 'required|integer|min:0',
            'teaching_load.max_credit_hours' => 'required|integer|min:0',
            'teaching_load.max_courses' => 'required|integer|min:0',
            'teaching_load.can_teach_evening' => 'boolean',
            'teaching_load.can_teach_weekend' => 'boolean',
            'teaching_load.can_teach_online' => 'boolean',
        ]);
        
        $termId = $this->getCurrentTermId();
        
        DB::transaction(function() use ($validated, $facultyId, $termId) {
            // Clear existing availability
            FacultyAvailability::where('faculty_id', $facultyId)
                ->where('term_id', $termId)
                ->delete();
            
            // Add new availability
            if (isset($validated['availability'])) {
                foreach ($validated['availability'] as $slot) {
                    $slot['faculty_id'] = $facultyId;
                    $slot['term_id'] = $termId;
                    FacultyAvailability::create($slot);
                }
            }
            
            // Update teaching load
            if (isset($validated['teaching_load'])) {
                TeachingLoad::updateOrCreate(
                    ['faculty_id' => $facultyId, 'term_id' => $termId],
                    $validated['teaching_load']
                );
            }
        });
        
        return redirect()->route('scheduling.faculty-availability', $facultyId)
            ->with('success', 'Faculty availability saved successfully');
    }

    /**
     * Room booking calendar
     */
    public function roomBookings(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $roomId = $request->get('room_id');
        
        $rooms = Room::where('is_active', true)
            ->orderBy('building_id')
            ->orderBy('room_code')
            ->get();
        
        $bookingsQuery = RoomBooking::whereDate('booking_date', $date);
        
        if ($roomId) {
            $bookingsQuery->where('room_id', $roomId);
        }
        
        $bookings = $bookingsQuery->with(['room', 'bookedBy'])->get();
        
        return view('scheduling.room-bookings', compact('rooms', 'bookings', 'date', 'roomId'));
    }

    /**
     * Create room booking
     */
    public function createBooking(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'booking_type' => 'required|in:event,meeting,exam,maintenance',
            'event_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'expected_attendees' => 'nullable|integer|min:1',
            'requirements' => 'nullable|array'
        ]);
        
        // Check for conflicts
        $hasConflict = RoomBooking::where('room_id', $validated['room_id'])
            ->where('booking_date', $validated['booking_date'])
            ->where('status', 'approved')
            ->where(function($query) use ($validated) {
                $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                      ->orWhere(function($q) use ($validated) {
                          $q->where('start_time', '<=', $validated['start_time'])
                            ->where('end_time', '>=', $validated['end_time']);
                      });
            })
            ->exists();
        
        if ($hasConflict) {
            return back()->withErrors(['time' => 'This time slot is already booked'])->withInput();
        }
        
        $validated['booked_by'] = Auth::id();
        $validated['status'] = Auth::user()->hasRole('admin') ? 'approved' : 'pending';
        
        RoomBooking::create($validated);
        
        return redirect()->route('scheduling.room-bookings')
            ->with('success', 'Room booking created successfully');
    }

    /**
     * Conflict resolution center
     */
    public function conflicts()
    {
        $conflicts = ScheduleConflict::with([
            'schedule1.section.course',
            'schedule2.section.course',
            'resolvedBy'
        ])
        ->orderBy('is_resolved')
        ->orderBy('severity', 'desc')
        ->orderBy('created_at', 'desc')
        ->paginate(20);
        
        return view('scheduling.conflicts', compact('conflicts'));
    }

    /**
     * Resolve conflict
     */
    public function resolveConflict(Request $request, $conflictId)
    {
        $conflict = ScheduleConflict::findOrFail($conflictId);
        
        $validated = $request->validate([
            'resolution_notes' => 'required|string',
            'action' => 'required|in:resolve,ignore,reschedule'
        ]);
        
        if ($validated['action'] === 'resolve' || $validated['action'] === 'ignore') {
            $conflict->is_resolved = true;
            $conflict->resolution_notes = $validated['resolution_notes'];
            $conflict->resolved_by = Auth::id();
            $conflict->resolved_at = now();
            $conflict->save();
        } elseif ($validated['action'] === 'reschedule') {
            // Redirect to reschedule page
            return redirect()->route('scheduling.create', $conflict->schedule1->section_id);
        }
        
        return redirect()->route('scheduling.conflicts')
            ->with('success', 'Conflict resolved successfully');
    }

    /**
     * Get current term ID
     */
    private function getCurrentTermId()
    {
        $term = AcademicTerm::where('is_current', true)->first();
        return $term ? $term->id : null;
    }
}