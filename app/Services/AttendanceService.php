<?php
// File: app/Services/AttendanceService.php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\AttendancePolicy;
use App\Models\AttendanceStatistic;
use App\Models\AttendanceAlert;
use App\Models\AttendanceExcuse;
use App\Models\SectionAttendancePolicy;
use App\Models\CourseSection;
use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Get section attendance policy
     */
    public function getSectionPolicy($sectionId)
    {
        $sectionPolicy = SectionAttendancePolicy::where('section_id', $sectionId)->first();
        
        if ($sectionPolicy && $sectionPolicy->policy_id) {
            return AttendancePolicy::find($sectionPolicy->policy_id);
        }
        
        return AttendancePolicy::where('is_default', true)->first();
    }

    /**
     * Update attendance statistics for a section
     */
    public function updateStatistics($sectionId)
    {
        $enrollments = Enrollment::where('section_id', $sectionId)
            ->where('status', 'enrolled')
            ->get();
        
        foreach ($enrollments as $enrollment) {
            $this->updateStudentStatistics($enrollment->student_id, $sectionId);
        }
    }

    /**
     * Update individual student statistics
     */
    public function updateStudentStatistics($studentId, $sectionId)
    {
        $sessions = AttendanceSession::where('section_id', $sectionId)
            ->where('attendance_taken', true)
            ->where('is_cancelled', false)
            ->count();
        
        if ($sessions == 0) {
            return;
        }
        
        $records = AttendanceRecord::whereHas('session', function($query) use ($sectionId) {
            $query->where('section_id', $sectionId)
                  ->where('attendance_taken', true)
                  ->where('is_cancelled', false);
        })
        ->where('student_id', $studentId)
        ->get();
        
        $stats = [
            'present' => $records->where('status', 'present')->count(),
            'absent' => $records->where('status', 'absent')->count(),
            'late' => $records->where('status', 'late')->count(),
            'excused' => $records->whereIn('status', ['excused', 'sick'])->count(),
        ];
        
        $attendancePercentage = (($stats['present'] + $stats['late'] * 0.5 + $stats['excused']) / $sessions) * 100;
        
        AttendanceStatistic::updateOrCreate(
            [
                'student_id' => $studentId,
                'section_id' => $sectionId
            ],
            [
                'term_id' => CourseSection::find($sectionId)->term_id,
                'total_sessions' => $sessions,
                'sessions_present' => $stats['present'],
                'sessions_absent' => $stats['absent'],
                'sessions_late' => $stats['late'],
                'sessions_excused' => $stats['excused'],
                'attendance_percentage' => round($attendancePercentage, 2),
                'last_calculated' => now()
            ]
        );
    }

    /**
     * Check and create attendance alerts
     */
    public function checkAndCreateAlerts($sectionId)
    {
        $policy = $this->getSectionPolicy($sectionId);
        
        if (!$policy) {
            return;
        }
        
        $statistics = AttendanceStatistic::where('section_id', $sectionId)->get();
        
        foreach ($statistics as $stat) {
            // Check for excessive absences
            if ($policy->max_absences && $stat->sessions_absent > $policy->max_absences) {
                $this->createAlert(
                    $stat->student_id,
                    $sectionId,
                    'excessive_absence',
                    "You have exceeded the maximum allowed absences ({$stat->sessions_absent}/{$policy->max_absences})",
                    $stat->sessions_absent,
                    $stat->attendance_percentage
                );
            }
            
            // Check for at-risk (below 75%)
            if ($stat->attendance_percentage < 75) {
                $this->createAlert(
                    $stat->student_id,
                    $sectionId,
                    'at_risk',
                    "Your attendance is below 75% ({$stat->attendance_percentage}%)",
                    $stat->sessions_absent,
                    $stat->attendance_percentage
                );
            }
            
            // Check for auto-fail threshold
            if ($policy->auto_fail_on_excess_absence && 
                $policy->auto_fail_threshold && 
                $stat->sessions_absent >= $policy->auto_fail_threshold) {
                $this->createAlert(
                    $stat->student_id,
                    $sectionId,
                    'auto_fail_risk',
                    "WARNING: You are at risk of automatic failure due to excessive absences",
                    $stat->sessions_absent,
                    $stat->attendance_percentage
                );
            }
        }
    }

    /**
     * Create an attendance alert
     */
    private function createAlert($studentId, $sectionId, $type, $message, $absenceCount, $percentage)
    {
        // Check if similar alert exists recently (within 7 days)
        $existingAlert = AttendanceAlert::where('student_id', $studentId)
            ->where('section_id', $sectionId)
            ->where('alert_type', $type)
            ->where('created_at', '>', now()->subDays(7))
            ->first();
        
        if (!$existingAlert) {
            AttendanceAlert::create([
                'student_id' => $studentId,
                'section_id' => $sectionId,
                'alert_type' => $type,
                'message' => $message,
                'absence_count' => $absenceCount,
                'attendance_percentage' => $percentage
            ]);
            
            // TODO: Send notifications (email/SMS)
        }
    }

    /**
     * Apply approved excuse to attendance records
     */
    public function applyExcuse(AttendanceExcuse $excuse)
    {
        $startDate = Carbon::parse($excuse->start_date);
        $endDate = Carbon::parse($excuse->end_date);
        
        // Get applicable sections
        $sectionIds = [];
        if ($excuse->apply_to_all_courses) {
            $sectionIds = Enrollment::where('student_id', $excuse->student_id)
                ->where('status', 'enrolled')
                ->pluck('section_id');
        } else {
            $sectionIds = json_decode($excuse->applicable_sections, true) ?? [];
        }
        
        // Update attendance records
        AttendanceRecord::whereHas('session', function($query) use ($sectionIds, $startDate, $endDate) {
            $query->whereIn('section_id', $sectionIds)
                  ->whereBetween('session_date', [$startDate, $endDate]);
        })
        ->where('student_id', $excuse->student_id)
        ->whereIn('status', ['absent', 'late'])
        ->update([
            'status' => 'excused',
            'excuse_verified' => true,
            'verified_by' => $excuse->reviewed_by,
            'verified_at' => now()
        ]);
        
        // Update statistics
        foreach ($sectionIds as $sectionId) {
            $this->updateStudentStatistics($excuse->student_id, $sectionId);
        }
    }

    /**
     * Get section attendance statistics
     */
    public function getSectionStatistics($sectionId)
    {
        $stats = AttendanceStatistic::where('section_id', $sectionId)->get();
        
        return [
            'enrolled' => $stats->count(),
            'average_attendance' => $stats->avg('attendance_percentage'),
            'perfect_attendance' => $stats->where('attendance_percentage', 100)->count(),
            'at_risk' => $stats->where('attendance_percentage', '<', 75)->count(),
            'critical' => $stats->where('attendance_percentage', '<', 50)->count(),
        ];
    }

    /**
     * Get overall attendance rate for a term
     */
    public function getOverallAttendanceRate($termId)
    {
        return AttendanceStatistic::where('term_id', $termId)
            ->avg('attendance_percentage');
    }

    /**
     * Get attendance by department
     */
    public function getAttendanceByDepartment($termId)
    {
        return DB::table('attendance_statistics as a')
            ->join('course_sections as cs', 'a.section_id', '=', 'cs.id')
            ->join('courses as c', 'cs.course_id', '=', 'c.id')
            ->join('departments as d', 'c.department_id', '=', 'd.id')
            ->where('a.term_id', $termId)
            ->select('d.name', DB::raw('AVG(a.attendance_percentage) as avg_attendance'))
            ->groupBy('d.id', 'd.name')
            ->orderBy('avg_attendance', 'desc')
            ->get();
    }

    /**
     * Get attendance trends
     */
    public function getAttendanceTrends($termId, $weeks = 8)
    {
        return DB::table('attendance_records as ar')
            ->join('attendance_sessions as as', 'ar.session_id', '=', 'as.id')
            ->join('course_sections as cs', 'as.section_id', '=', 'cs.id')
            ->where('cs.term_id', $termId)
            ->where('as.session_date', '>=', now()->subWeeks($weeks))
            ->select(
                DB::raw('WEEK(as.session_date) as week'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN ar.status = "present" THEN 1 ELSE 0 END) as present'),
                DB::raw('SUM(CASE WHEN ar.status = "absent" THEN 1 ELSE 0 END) as absent')
            )
            ->groupBy('week')
            ->orderBy('week')
            ->get();
    }

    /**
     * Get at-risk students
     */
    public function getAtRiskStudents($termId, $threshold = 75)
    {
        return AttendanceStatistic::where('term_id', $termId)
            ->where('attendance_percentage', '<', $threshold)
            ->with(['student', 'section.course'])
            ->orderBy('attendance_percentage', 'asc')
            ->limit(50)
            ->get();
    }
}