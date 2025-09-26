<?php

// ============================================================
// File: app/Services/ConflictDetectionService.php
// ============================================================

namespace App\Services;

use App\Models\ClassSchedule;
use App\Models\Enrollment;

class ConflictDetectionService
{
    /**
     * Check for conflicts when creating/updating a schedule
     */
    public function checkScheduleConflicts($scheduleData)
    {
        $conflicts = [];
        
        // Check room conflicts
        $roomConflict = $this->checkRoomConflict($scheduleData);
        if ($roomConflict) {
            $conflicts[] = $roomConflict;
        }
        
        // Check instructor conflicts
        $instructorConflict = $this->checkInstructorConflict($scheduleData);
        if ($instructorConflict) {
            $conflicts[] = $instructorConflict;
        }
        
        // Check student conflicts
        $studentConflicts = $this->checkStudentConflicts($scheduleData);
        $conflicts = array_merge($conflicts, $studentConflicts);
        
        return $conflicts;
    }

    /**
     * Check if room is already booked
     */
    private function checkRoomConflict($data)
    {
        $conflict = ClassSchedule::where('room_id', $data['room_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('is_active', true)
            ->where(function($q) use ($data) {
                $q->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                  ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                  ->orWhere(function($q2) use ($data) {
                      $q2->where('start_time', '<=', $data['start_time'])
                         ->where('end_time', '>=', $data['end_time']);
                  });
            })
            ->first();
        
        if ($conflict) {
            return [
                'type' => 'room',
                'severity' => 'high',
                'conflicting_schedule_id' => $conflict->id,
                'description' => "Room is already booked for {$conflict->section->course->course_code} at this time"
            ];
        }
        
        return null;
    }

    /**
     * Check if instructor has another class
     */
    private function checkInstructorConflict($data)
    {
        if (!isset($data['instructor_id'])) {
            return null;
        }
        
        $conflict = ClassSchedule::where('instructor_id', $data['instructor_id'])
            ->where('day_of_week', $data['day_of_week'])
            ->where('is_active', true)
            ->where(function($q) use ($data) {
                $q->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                  ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                  ->orWhere(function($q2) use ($data) {
                      $q2->where('start_time', '<=', $data['start_time'])
                         ->where('end_time', '>=', $data['end_time']);
                  });
            })
            ->first();
        
        if ($conflict) {
            return [
                'type' => 'instructor',
                'severity' => 'critical',
                'conflicting_schedule_id' => $conflict->id,
                'description' => "Instructor is teaching {$conflict->section->course->course_code} at this time"
            ];
        }
        
        return null;
    }

    /**
     * Check for student time conflicts
     */
    private function checkStudentConflicts($data)
    {
        $conflicts = [];
        
        // Get students enrolled in this section
        $studentIds = Enrollment::where('section_id', $data['section_id'])
            ->where('status', 'enrolled')
            ->pluck('student_id');
        
        // Check if any of these students have classes at the same time
        $conflictingSections = DB::table('enrollments as e')
            ->join('class_schedules as cs', 'e.section_id', '=', 'cs.section_id')
            ->join('course_sections as s', 'cs.section_id', '=', 's.id')
            ->join('courses as c', 's.course_id', '=', 'c.id')
            ->whereIn('e.student_id', $studentIds)
            ->where('e.status', 'enrolled')
            ->where('cs.day_of_week', $data['day_of_week'])
            ->where('cs.is_active', true)
            ->where(function($q) use ($data) {
                $q->whereBetween('cs.start_time', [$data['start_time'], $data['end_time']])
                  ->orWhereBetween('cs.end_time', [$data['start_time'], $data['end_time']])
                  ->orWhere(function($q2) use ($data) {
                      $q2->where('cs.start_time', '<=', $data['start_time'])
                         ->where('cs.end_time', '>=', $data['end_time']);
                  });
            })
            ->select('cs.id', 'c.course_code', DB::raw('COUNT(DISTINCT e.student_id) as affected_students'))
            ->groupBy('cs.id', 'c.course_code')
            ->get();
        
        foreach ($conflictingSections as $conflict) {
            $conflicts[] = [
                'type' => 'student',
                'severity' => $conflict->affected_students > 5 ? 'high' : 'medium',
                'conflicting_schedule_id' => $conflict->id,
                'description' => "{$conflict->affected_students} students have {$conflict->course_code} at this time"
            ];
        }
        
        return $conflicts;
    }
}