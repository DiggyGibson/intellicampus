<?php

// ============================================================
// File: app/Services/SchedulingService.php
// ============================================================

namespace App\Services;

use App\Models\ClassSchedule;
use App\Models\TeachingLoad;
use App\Models\CourseSection;
use App\Models\FacultyAvailability;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class SchedulingService
{
    /**
     * Get filtered schedules for timetable view
     */
    public function getFilteredSchedules($filters)
    {
        $query = ClassSchedule::with(['section.course', 'room.building', 'instructor']);
        
        if (isset($filters['term_id'])) {
            $query->whereHas('section', function($q) use ($filters) {
                $q->where('term_id', $filters['term_id']);
            });
        }
        
        if (isset($filters['department_id'])) {
            $query->whereHas('section.course', function($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }
        
        if (isset($filters['building_id'])) {
            $query->whereHas('room', function($q) use ($filters) {
                $q->where('building_id', $filters['building_id']);
            });
        }
        
        if (isset($filters['day'])) {
            $query->where('day_of_week', $filters['day']);
        }
        
        return $query->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Update faculty teaching load
     */
    public function updateTeachingLoad($facultyId, $termId)
    {
        $sections = CourseSection::where('primary_instructor_id', $facultyId)
            ->where('term_id', $termId)
            ->with('course')
            ->get();
        
        $totalCredits = $sections->sum('course.credit_hours');
        $courseCount = $sections->count();
        $uniqueCourses = $sections->pluck('course_id')->unique()->count();
        
        TeachingLoad::updateOrCreate(
            ['faculty_id' => $facultyId, 'term_id' => $termId],
            [
                'current_credit_hours' => $totalCredits,
                'current_courses' => $courseCount,
            ]
        );
    }

    /**
     * Find available rooms for a time slot
     */
    public function findAvailableRooms($day, $startTime, $endTime, $capacity = null)
    {
        $query = Room::where('is_active', true);
        
        if ($capacity) {
            $query->where('capacity', '>=', $capacity);
        }
        
        // Get rooms that don't have schedules at this time
        $busyRoomIds = ClassSchedule::where('day_of_week', $day)
            ->where(function($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            })
            ->pluck('room_id');
        
        return $query->whereNotIn('id', $busyRoomIds)->get();
    }

    /**
     * Get faculty schedule
     */
    public function getFacultySchedule($facultyId, $termId)
    {
        return ClassSchedule::where('instructor_id', $facultyId)
            ->whereHas('section', function($q) use ($termId) {
                $q->where('term_id', $termId);
            })
            ->with(['section.course', 'room'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Generate optimal schedule (basic algorithm)
     */
    public function generateOptimalSchedule($termId)
    {
        // This is a simplified version - a real implementation would use
        // more sophisticated algorithms like genetic algorithms or constraint programming
        
        $sections = CourseSection::where('term_id', $termId)
            ->whereDoesntHave('schedules')
            ->with(['course', 'primaryInstructor'])
            ->get();
        
        $suggestions = [];
        
        foreach ($sections as $section) {
            // Get faculty availability
            $availability = FacultyAvailability::where('faculty_id', $section->primary_instructor_id)
                ->where('term_id', $termId)
                ->where('is_available', true)
                ->get();
            
            // Find suitable time slots and rooms
            foreach ($availability as $slot) {
                $rooms = $this->findAvailableRooms(
                    $slot->day_of_week,
                    $slot->start_time,
                    $slot->end_time,
                    $section->enrollment_capacity
                );
                
                if ($rooms->isNotEmpty()) {
                    $suggestions[] = [
                        'section' => $section,
                        'day' => $slot->day_of_week,
                        'start_time' => $slot->start_time,
                        'end_time' => $slot->end_time,
                        'room' => $rooms->first(),
                        'preference' => $slot->preference_level
                    ];
                    break; // Found a slot, move to next section
                }
            }
        }
        
        return $suggestions;
    }
}
