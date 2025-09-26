<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class TimeConflictService
{
    /**
     * Check for time conflicts between sections
     *
     * @param int $studentId
     * @param Collection $newSections Sections being added
     * @param int|null $termId
     * @return array Array of conflicts found
     */
    public function checkTimeConflicts(int $studentId, Collection $newSections, ?int $termId = null): array
    {
        // Get current term if not provided
        if (!$termId) {
            $currentTerm = DB::table('academic_terms')->where('is_current', true)->first();
            $termId = $currentTerm ? $currentTerm->id : null;
        }
        
        if (!$termId) {
            return [];
        }
        
        // Get student's existing enrollments
        $existingEnrollments = $this->getStudentSchedule($studentId, $termId);
        
        // Build time slots for existing enrollments
        $existingSlots = $this->buildTimeSlots($existingEnrollments);
        
        // Build time slots for new sections
        $newSlots = $this->buildTimeSlots($newSections);
        
        // Check for conflicts
        $conflicts = [];
        
        foreach ($newSlots as $newSlot) {
            foreach ($existingSlots as $existingSlot) {
                if ($this->slotsConflict($newSlot, $existingSlot)) {
                    $conflicts[] = [
                        'new_section' => [
                            'code' => $newSlot['course_code'],
                            'title' => $newSlot['course_title'],
                            'section' => $newSlot['section_number'],
                            'days' => $newSlot['days'],
                            'time' => $this->formatTimeRange($newSlot['start_time'], $newSlot['end_time'])
                        ],
                        'existing_section' => [
                            'code' => $existingSlot['course_code'],
                            'title' => $existingSlot['course_title'],
                            'section' => $existingSlot['section_number'],
                            'days' => $existingSlot['days'],
                            'time' => $this->formatTimeRange($existingSlot['start_time'], $existingSlot['end_time'])
                        ],
                        'conflict_days' => $this->getConflictingDays($newSlot['days'], $existingSlot['days']),
                        'message' => "Time conflict between {$newSlot['course_code']} and {$existingSlot['course_code']} on {$this->getConflictingDays($newSlot['days'], $existingSlot['days'])}"
                    ];
                }
            }
            
            // Also check conflicts within new sections themselves
            foreach ($newSlots as $otherNewSlot) {
                if ($newSlot === $otherNewSlot) continue;
                
                if ($this->slotsConflict($newSlot, $otherNewSlot)) {
                    $conflicts[] = [
                        'new_section' => [
                            'code' => $newSlot['course_code'],
                            'title' => $newSlot['course_title'],
                            'section' => $newSlot['section_number'],
                            'days' => $newSlot['days'],
                            'time' => $this->formatTimeRange($newSlot['start_time'], $newSlot['end_time'])
                        ],
                        'existing_section' => [
                            'code' => $otherNewSlot['course_code'],
                            'title' => $otherNewSlot['course_title'],
                            'section' => $otherNewSlot['section_number'],
                            'days' => $otherNewSlot['days'],
                            'time' => $this->formatTimeRange($otherNewSlot['start_time'], $otherNewSlot['end_time'])
                        ],
                        'conflict_days' => $this->getConflictingDays($newSlot['days'], $otherNewSlot['days']),
                        'message' => "Time conflict between selected courses {$newSlot['course_code']} and {$otherNewSlot['course_code']}"
                    ];
                }
            }
        }
        
        // Remove duplicate conflicts
        return $this->removeDuplicateConflicts($conflicts);
    }
    
    /**
     * Get student's current schedule
     */
    private function getStudentSchedule(int $studentId, int $termId): Collection
    {
        return DB::table('enrollments as e')
            ->join('course_sections as cs', 'e.section_id', '=', 'cs.id')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->where('e.student_id', $studentId)
            ->where('e.term_id', $termId)
            ->whereIn('e.enrollment_status', ['enrolled', 'pending'])
            ->select(
                'cs.*',
                'c.code as course_code',
                'c.title as course_title'
            )
            ->get();
    }
    
    /**
     * Build time slots from sections
     */
    private function buildTimeSlots(Collection $sections): array
    {
        $slots = [];
        
        foreach ($sections as $section) {
            if (!$section->days_of_week || !$section->start_time || !$section->end_time) {
                continue; // Skip online/asynchronous sections
            }
            
            $slots[] = [
                'section_id' => $section->id,
                'course_code' => $section->course_code ?? ($section->course->code ?? ''),
                'course_title' => $section->course_title ?? ($section->course->title ?? ''),
                'section_number' => $section->section_number,
                'days' => $section->days_of_week,
                'start_time' => $section->start_time,
                'end_time' => $section->end_time,
                'room' => $section->room ?? '',
                'building' => $section->building ?? ''
            ];
        }
        
        return $slots;
    }
    
    /**
     * Check if two time slots conflict
     */
    private function slotsConflict(array $slot1, array $slot2): bool
    {
        // Check if they share any days
        $days1 = str_split($slot1['days']);
        $days2 = str_split($slot2['days']);
        $commonDays = array_intersect($days1, $days2);
        
        if (empty($commonDays)) {
            return false; // No common days, no conflict
        }
        
        // Check if times overlap
        $start1 = Carbon::parse($slot1['start_time']);
        $end1 = Carbon::parse($slot1['end_time']);
        $start2 = Carbon::parse($slot2['start_time']);
        $end2 = Carbon::parse($slot2['end_time']);
        
        // Times conflict if:
        // 1. Slot1 starts during slot2
        // 2. Slot2 starts during slot1
        // 3. One completely contains the other
        return !($end1->lte($start2) || $end2->lte($start1));
    }
    
    /**
     * Get conflicting days between two schedules
     */
    private function getConflictingDays(string $days1, string $days2): string
    {
        $dayMap = [
            'M' => 'Monday',
            'T' => 'Tuesday',
            'W' => 'Wednesday',
            'R' => 'Thursday',
            'F' => 'Friday',
            'S' => 'Saturday',
            'U' => 'Sunday'
        ];
        
        $days1Array = str_split($days1);
        $days2Array = str_split($days2);
        $commonDays = array_intersect($days1Array, $days2Array);
        
        $conflictDays = [];
        foreach ($commonDays as $day) {
            $conflictDays[] = $dayMap[$day] ?? $day;
        }
        
        return implode(', ', $conflictDays);
    }
    
    /**
     * Format time range for display
     */
    private function formatTimeRange(string $startTime, string $endTime): string
    {
        $start = Carbon::parse($startTime)->format('g:i A');
        $end = Carbon::parse($endTime)->format('g:i A');
        return "{$start} - {$end}";
    }
    
    /**
     * Remove duplicate conflicts from the list
     */
    private function removeDuplicateConflicts(array $conflicts): array
    {
        $unique = [];
        $seen = [];
        
        foreach ($conflicts as $conflict) {
            // Create a unique key for this conflict
            $key1 = $conflict['new_section']['code'] . '-' . $conflict['existing_section']['code'];
            $key2 = $conflict['existing_section']['code'] . '-' . $conflict['new_section']['code'];
            
            if (!isset($seen[$key1]) && !isset($seen[$key2])) {
                $unique[] = $conflict;
                $seen[$key1] = true;
                $seen[$key2] = true;
            }
        }
        
        return $unique;
    }
    
    /**
     * Check if adding a section would exceed daily time limits
     */
    public function checkDailyTimeLimit(int $studentId, Collection $newSections, int $termId, int $maxHoursPerDay = 8): array
    {
        $violations = [];
        $schedule = $this->getStudentSchedule($studentId, $termId);
        $allSections = $schedule->merge($newSections);
        
        // Group by day
        $daySchedule = [];
        foreach ($allSections as $section) {
            if (!$section->days_of_week || !$section->start_time || !$section->end_time) {
                continue;
            }
            
            $days = str_split($section->days_of_week);
            foreach ($days as $day) {
                if (!isset($daySchedule[$day])) {
                    $daySchedule[$day] = [];
                }
                
                $daySchedule[$day][] = [
                    'course' => $section->course_code ?? ($section->course->code ?? ''),
                    'start' => Carbon::parse($section->start_time),
                    'end' => Carbon::parse($section->end_time)
                ];
            }
        }
        
        // Check each day's total hours
        $dayNames = [
            'M' => 'Monday',
            'T' => 'Tuesday',
            'W' => 'Wednesday',
            'R' => 'Thursday',
            'F' => 'Friday',
            'S' => 'Saturday',
            'U' => 'Sunday'
        ];
        
        foreach ($daySchedule as $day => $sections) {
            $totalMinutes = 0;
            foreach ($sections as $section) {
                $totalMinutes += $section['start']->diffInMinutes($section['end']);
            }
            
            $totalHours = $totalMinutes / 60;
            if ($totalHours > $maxHoursPerDay) {
                $violations[] = [
                    'day' => $dayNames[$day] ?? $day,
                    'total_hours' => round($totalHours, 1),
                    'max_hours' => $maxHoursPerDay,
                    'message' => "Schedule on {$dayNames[$day]} exceeds {$maxHoursPerDay} hours limit ({$totalHours} hours scheduled)"
                ];
            }
        }
        
        return $violations;
    }
}