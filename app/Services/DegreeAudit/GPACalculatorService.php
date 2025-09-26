<?php
// Save as: backend/app/Services/DegreeAudit/GPACalculatorService.php

namespace App\Services\DegreeAudit;

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\AcademicTerm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class GPACalculatorService
{
    /**
     * Grade point values
     */
    private $gradePoints = [
        'A' => 4.0, 'A-' => 3.7,
        'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
        'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
        'D+' => 1.3, 'D' => 1.0, 'D-' => 0.7,
        'F' => 0.0
    ];

    /**
     * Calculate cumulative GPA for a student
     */
    public function calculateCumulativeGPA(Student $student): float
    {
        $enrollments = $student->enrollments()
            ->where('enrollment_status', 'completed')
            ->whereNotNull('final_grade')
            ->whereNotIn('grade_option', ['pass_fail', 'audit']) // Exclude pass/fail and audit courses
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->select('enrollments.*', 'courses.credits', 'courses.code')
            ->get();

        return $this->calculateGPA($enrollments);
    }

    /**
     * Calculate GPA for a specific term
     */
    public function calculateTermGPA(Student $student, int $termId): float
    {
        $enrollments = $student->enrollments()
            ->where('term_id', $termId)
            ->where('enrollment_status', 'completed')
            ->whereNotNull('final_grade')
            ->whereNotIn('grade_option', ['pass_fail', 'audit'])
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->select('enrollments.*', 'courses.credits', 'courses.code')
            ->get();

        return $this->calculateGPA($enrollments);
    }

    /**
     * Calculate major GPA
     */
    public function calculateMajorGPA(Student $student): float
    {
        $program = $student->program;
        if (!$program) {
            return 0.0;
        }

        // Get all courses that count toward major
        $enrollments = $student->enrollments()
            ->where('enrollment_status', 'completed')
            ->whereNotNull('final_grade')
            ->whereNotIn('grade_option', ['pass_fail', 'audit'])
            ->whereHas('section.course', function ($query) use ($program) {
                // Courses from the major's department
                $query->where('department_id', $program->department_id)
                    // Or courses with major prefix (e.g., CS for Computer Science)
                    ->orWhere('course_code', 'like', substr($program->code, 0, 2) . '%');
            })
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->select('enrollments.*', 'courses.credits', 'courses.course_code')
            ->get();

        return $this->calculateGPA($enrollments);
    }

    /**
     * Calculate minor GPA
     */
    public function calculateMinorGPA(Student $student, int $minorProgramId = null): float
    {
        if (!$minorProgramId && !$student->minor_program_id) {
            return 0.0;
        }

        $minorId = $minorProgramId ?? $student->minor_program_id;
        
        // Get minor program details
        $minorProgram = \App\Models\AcademicProgram::find($minorId);
        if (!$minorProgram) {
            return 0.0;
        }

        $enrollments = $student->enrollments()
            ->where('enrollment_status', 'completed')
            ->whereNotNull('final_grade')
            ->whereNotIn('grade_option', ['pass_fail', 'audit'])
            ->whereHas('section.course', function ($query) use ($minorProgram) {
                $query->where('department_id', $minorProgram->department_id)
                    ->orWhere('course_code', 'like', substr($minorProgram->code, 0, 2) . '%');
            })
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->select('enrollments.*', 'courses.credits', 'courses.code')
            ->get();

        return $this->calculateGPA($enrollments);
    }

    /**
     * Calculate GPA for last X credits
     */
    public function calculateLastXCreditsGPA(Student $student, int $creditCount = 60): float
    {
        $enrollments = $student->enrollments()
            ->where('enrollment_status', 'completed')
            ->whereNotNull('final_grade')
            ->whereNotIn('grade_option', ['pass_fail', 'audit'])
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->orderBy('enrollments.created_at', 'desc')
            ->select('enrollments.*', 'courses.credits', 'courses.code')
            ->get();

        // Take only the last X credits
        $totalCredits = 0;
        $selectedEnrollments = new Collection();
        
        foreach ($enrollments as $enrollment) {
            if ($totalCredits >= $creditCount) {
                break;
            }
            $selectedEnrollments->push($enrollment);
            $totalCredits += $enrollment->credits;
        }

        return $this->calculateGPA($selectedEnrollments);
    }

    /**
     * Calculate GPA excluding certain grades (for academic renewal)
     */
    public function calculateGPAWithExclusions(Student $student, array $excludedEnrollmentIds = []): float
    {
        $enrollments = $student->enrollments()
            ->where('enrollment_status', 'completed')
            ->whereNotNull('final_grade')
            ->whereNotIn('grade_option', ['pass_fail', 'audit'])
            ->whereNotIn('id', $excludedEnrollmentIds)
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->select('enrollments.*', 'courses.credits', 'courses.code')
            ->get();

        return $this->calculateGPA($enrollments);
    }

    /**
     * Core GPA calculation logic
     */
    private function calculateGPA($enrollments): float
    {
        if ($enrollments->isEmpty()) {
            return 0.0;
        }

        $totalQualityPoints = 0;
        $totalGPACredits = 0;

        foreach ($enrollments as $enrollment) {
            $grade = $enrollment->final_grade ?? $enrollment->grade;
            
            // Skip if no valid grade
            if (!$grade || !isset($this->gradePoints[$grade])) {
                continue;
            }

            // Handle repeated courses (take the best grade)
            if ($enrollment->is_repeat) {
                // Logic to handle repeated courses would go here
                // For now, we'll include all attempts
            }

            $credits = $enrollment->credits;
            $gradePoint = $this->gradePoints[$grade];
            
            $totalQualityPoints += ($gradePoint * $credits);
            $totalGPACredits += $credits;
        }

        if ($totalGPACredits == 0) {
            return 0.0;
        }

        return round($totalQualityPoints / $totalGPACredits, 2);
    }

    /**
     * Calculate statistics for GPA
     */
    public function calculateGPAStatistics(Student $student): array
    {
        $cumulative = $this->calculateCumulativeGPA($student);
        $major = $this->calculateMajorGPA($student);
        
        // Get current term
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        $termGPA = $currentTerm ? $this->calculateTermGPA($student, $currentTerm->id) : 0.0;
        
        // Calculate trend (last 3 terms)
        $trend = $this->calculateGPATrend($student, 3);
        
        return [
            'cumulative_gpa' => $cumulative,
            'major_gpa' => $major,
            'current_term_gpa' => $termGPA,
            'last_60_credits_gpa' => $this->calculateLastXCreditsGPA($student, 60),
            'trend' => $trend,
            'total_gpa_credits' => $this->getTotalGPACredits($student),
            'total_quality_points' => $this->getTotalQualityPoints($student)
        ];
    }

    /**
     * Calculate GPA trend over last N terms
     */
    private function calculateGPATrend(Student $student, int $termCount = 3): array
    {
        $terms = AcademicTerm::orderBy('start_date', 'desc')
            ->limit($termCount)
            ->get();

        $trend = [];
        foreach ($terms as $term) {
            $gpa = $this->calculateTermGPA($student, $term->id);
            $trend[] = [
                'term' => $term->name,
                'gpa' => $gpa
            ];
        }

        return array_reverse($trend);
    }

    /**
     * Get total GPA credits
     */
    private function getTotalGPACredits(Student $student): float
    {
        return $student->enrollments()
            ->where('enrollment_status', 'completed')
            ->whereNotNull('final_grade')
            ->whereNotIn('grade_option', ['pass_fail', 'audit'])
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->sum('courses.credits');
    }

    /**
     * Get total quality points
     */
    private function getTotalQualityPoints(Student $student): float
    {
        $enrollments = $student->enrollments()
            ->where('enrollment_status', 'completed')
            ->whereNotNull('final_grade')
            ->whereNotIn('grade_option', ['pass_fail', 'audit'])
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->select('enrollments.*', 'courses.credits')
            ->get();

        $totalPoints = 0;
        foreach ($enrollments as $enrollment) {
            $grade = $enrollment->final_grade ?? $enrollment->grade;
            if ($grade && isset($this->gradePoints[$grade])) {
                $totalPoints += $this->gradePoints[$grade] * $enrollment->credits;
            }
        }

        return $totalPoints;
    }

    /**
     * Check if student meets GPA requirement
     */
    public function meetsGPARequirement(Student $student, float $requiredGPA, string $type = 'cumulative'): bool
    {
        $actualGPA = match($type) {
            'cumulative' => $this->calculateCumulativeGPA($student),
            'major' => $this->calculateMajorGPA($student),
            'term' => $this->calculateTermGPA($student, AcademicTerm::where('is_current', true)->first()?->id ?? 0),
            default => 0.0
        };

        return $actualGPA >= $requiredGPA;
    }

    /**
     * Calculate GPA if certain grades were different (what-if analysis)
     */
    public function calculateWhatIfGPA(Student $student, array $gradeChanges): float
    {
        $enrollments = $student->enrollments()
            ->where('enrollment_status', 'completed')
            ->whereNotNull('final_grade')
            ->whereNotIn('grade_option', ['pass_fail', 'audit'])
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->select('enrollments.*', 'courses.credits', 'courses.code')
            ->get();

        $totalQualityPoints = 0;
        $totalGPACredits = 0;

        foreach ($enrollments as $enrollment) {
            // Use changed grade if provided, otherwise use actual grade
            $grade = $gradeChanges[$enrollment->id] ?? $enrollment->final_grade ?? $enrollment->grade;
            
            if (!$grade || !isset($this->gradePoints[$grade])) {
                continue;
            }

            $credits = $enrollment->credits;
            $gradePoint = $this->gradePoints[$grade];
            
            $totalQualityPoints += ($gradePoint * $credits);
            $totalGPACredits += $credits;
        }

        return $totalGPACredits > 0 ? round($totalQualityPoints / $totalGPACredits, 2) : 0.0;
    }
}