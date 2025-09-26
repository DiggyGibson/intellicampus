<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\AcademicTerm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class GradeCalculationService
{
    /**
     * Grade scale configuration
     */
    protected $gradeScale = [
        'A'  => ['min' => 93, 'max' => 100, 'points' => 4.0],
        'A-' => ['min' => 90, 'max' => 92.99, 'points' => 3.7],
        'B+' => ['min' => 87, 'max' => 89.99, 'points' => 3.3],
        'B'  => ['min' => 83, 'max' => 86.99, 'points' => 3.0],
        'B-' => ['min' => 80, 'max' => 82.99, 'points' => 2.7],
        'C+' => ['min' => 77, 'max' => 79.99, 'points' => 2.3],
        'C'  => ['min' => 73, 'max' => 76.99, 'points' => 2.0],
        'C-' => ['min' => 70, 'max' => 72.99, 'points' => 1.7],
        'D+' => ['min' => 67, 'max' => 69.99, 'points' => 1.3],
        'D'  => ['min' => 63, 'max' => 66.99, 'points' => 1.0],
        'F'  => ['min' => 0, 'max' => 62.99, 'points' => 0.0]
    ];

    /**
     * Special grades that don't affect GPA
     */
    protected $specialGrades = ['W', 'I', 'P', 'NP', 'AU', 'IP'];

    /**
     * Calculate overall grade for an enrollment
     *
     * @param int $enrollmentId
     * @return array
     */
    public function calculateGrade($enrollmentId)
    {
        $enrollment = Enrollment::with(['section.course'])->findOrFail($enrollmentId);
        
        // Get all grade components for the section
        $componentsQuery = GradeComponent::where('section_id', $enrollment->section_id);
        
        // Only filter by is_extra_credit if the column exists
        if (Schema::hasColumn('grade_components', 'is_extra_credit')) {
            $componentsQuery->where('is_extra_credit', false);
        }
        
        $components = $componentsQuery->get();
        
        // Get all grades for this enrollment
        $grades = Grade::where('enrollment_id', $enrollmentId)
            ->whereNotNull('component_id')
            ->get()
            ->keyBy('component_id');
        
        $totalWeightedScore = 0;
        $totalWeight = 0;
        $componentBreakdown = [];
        
        // Calculate weighted scores for each component
        foreach ($components as $component) {
            if (isset($grades[$component->id])) {
                $grade = $grades[$component->id];
                
                // Calculate percentage for this component
                $componentPercentage = $component->max_points > 0 
                    ? ($grade->points_earned / $component->max_points) * 100 
                    : 0;
                
                // Calculate weighted contribution
                $weightedScore = ($componentPercentage * $component->weight) / 100;
                
                $totalWeightedScore += $weightedScore;
                $totalWeight += $component->weight;
                
                $componentBreakdown[] = [
                    'component' => $component->name,
                    'type' => $component->type,
                    'weight' => $component->weight,
                    'points_earned' => $grade->points_earned,
                    'max_points' => $component->max_points,
                    'percentage' => round($componentPercentage, 2),
                    'weighted_score' => round($weightedScore, 2)
                ];
            } else {
                // Component not graded yet
                $componentBreakdown[] = [
                    'component' => $component->name,
                    'type' => $component->type,
                    'weight' => $component->weight,
                    'points_earned' => null,
                    'max_points' => $component->max_points,
                    'percentage' => 0,
                    'weighted_score' => 0
                ];
            }
        }
        
        // Handle extra credit if column exists
        $extraCredit = 0;
        if (Schema::hasColumn('grade_components', 'is_extra_credit')) {
            $extraCredit = $this->calculateExtraCredit($enrollmentId);
            $totalWeightedScore += $extraCredit;
        }
        
        // Calculate final percentage
        // If not all components are graded, scale up the percentage
        $finalPercentage = $totalWeight > 0 
            ? ($totalWeightedScore / $totalWeight) * 100 
            : 0;
        
        $finalPercentage = min(100, $finalPercentage); // Cap at 100%
        
        // Get letter grade
        $letterGrade = $this->getLetterGrade($finalPercentage);
        
        return [
            'enrollment_id' => $enrollmentId,
            'percentage' => round($finalPercentage, 2),
            'letter_grade' => $letterGrade,
            'grade_points' => $this->getGradePoints($letterGrade),
            'components' => $componentBreakdown,
            'extra_credit' => round($extraCredit, 2),
            'total_weight_graded' => $totalWeight,
            'credits' => $enrollment->section->course->credits
        ];
    }

    /**
     * Calculate extra credit points
     *
     * @param int $enrollmentId
     * @return float
     */
    protected function calculateExtraCredit($enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        
        // Only calculate if is_extra_credit column exists
        if (!Schema::hasColumn('grade_components', 'is_extra_credit')) {
            return 0;
        }
        
        $extraCreditComponents = GradeComponent::where('section_id', $enrollment->section_id)
            ->where('is_extra_credit', true)
            ->get();
        
        $extraCreditPoints = 0;
        
        foreach ($extraCreditComponents as $component) {
            $grade = Grade::where('enrollment_id', $enrollmentId)
                ->where('component_id', $component->id)
                ->first();
            
            if ($grade && $component->max_points > 0) {
                // Extra credit typically adds directly to final percentage
                // You can adjust this calculation based on your institution's policy
                $extraCreditPoints += ($grade->points_earned / $component->max_points) * $component->weight;
            }
        }
        
        return $extraCreditPoints;
    }

    /**
     * Get letter grade from percentage
     *
     * @param float $percentage
     * @return string
     */
    public function getLetterGrade($percentage)
    {
        foreach ($this->gradeScale as $letter => $range) {
            if ($percentage >= $range['min'] && $percentage <= $range['max']) {
                return $letter;
            }
        }
        
        return 'F';
    }

    /**
     * Get grade points from letter grade
     *
     * @param string $letterGrade
     * @return float
     */
    public function getGradePoints($letterGrade)
    {
        if (in_array($letterGrade, $this->specialGrades)) {
            return 0.0;
        }
        
        return $this->gradeScale[$letterGrade]['points'] ?? 0.0;
    }

    /**
     * Calculate GPA for a set of enrollments
     *
     * @param array $enrollmentData Array of ['grade_points' => x, 'credits' => y]
     * @return float
     */
    public function calculateGPA($enrollmentData)
    {
        $totalQualityPoints = 0;
        $totalCredits = 0;
        
        foreach ($enrollmentData as $data) {
            if (!in_array($data['letter_grade'] ?? '', $this->specialGrades)) {
                $qualityPoints = $data['grade_points'] * $data['credits'];
                $totalQualityPoints += $qualityPoints;
                $totalCredits += $data['credits'];
            }
        }
        
        if ($totalCredits == 0) {
            return 0.0;
        }
        
        return round($totalQualityPoints / $totalCredits, 2);
    }

    /**
     * Calculate semester GPA for a student
     *
     * @param int $studentId
     * @param int $termId
     * @return array
     */
    public function calculateSemesterGPA($studentId, $termId)
    {
        $enrollments = Enrollment::with('section.course')
            ->where('student_id', $studentId)
            ->whereHas('section', function($query) use ($termId) {
                $query->where('term_id', $termId);
            })
            ->whereIn('enrollment_status', ['completed', 'enrolled'])
            ->get();
        
        $enrollmentData = [];
        $totalCreditsAttempted = 0;
        $totalCreditsEarned = 0;
        
        foreach ($enrollments as $enrollment) {
            if ($enrollment->grade && !in_array($enrollment->grade, $this->specialGrades)) {
                $enrollmentData[] = [
                    'letter_grade' => $enrollment->grade,
                    'grade_points' => $enrollment->grade_points ?? $this->getGradePoints($enrollment->grade),
                    'credits' => $enrollment->section->course->credits
                ];
                
                $totalCreditsAttempted += $enrollment->section->course->credits;
                
                if (!in_array($enrollment->grade, ['F', 'W'])) {
                    $totalCreditsEarned += $enrollment->section->course->credits;
                }
            }
        }
        
        $gpa = $this->calculateGPA($enrollmentData);
        
        return [
            'term_id' => $termId,
            'gpa' => $gpa,
            'credits_attempted' => $totalCreditsAttempted,
            'credits_earned' => $totalCreditsEarned,
            'enrollments' => count($enrollments)
        ];
    }

    /**
     * Calculate term GPA for a student (alias for calculateSemesterGPA)
     * This method was missing and causing errors
     *
     * @param int $studentId
     * @param int|null $termId
     * @return float
     */
    public function calculateTermGPA($studentId, $termId = null)
    {
        if (!$termId) {
            // Get current term if not specified
            $currentTerm = AcademicTerm::where('is_current', true)->first();
            $termId = $currentTerm ? $currentTerm->id : null;
        }
        
        if (!$termId) {
            return 0.0;
        }
        
        $result = $this->calculateSemesterGPA($studentId, $termId);
        return $result['gpa'];
    }

    /**
     * Calculate cumulative GPA for a student
     *
     * @param int $studentId
     * @return float (returns just the GPA value for compatibility)
     */
    public function calculateCumulativeGPA($studentId)
    {
        $result = $this->calculateCumulativeGPADetailed($studentId);
        return $result['gpa'];
    }

    /**
     * Calculate cumulative GPA for a student with detailed information
     *
     * @param int $studentId
     * @return array
     */
    public function calculateCumulativeGPADetailed($studentId)
    {
        $enrollments = Enrollment::with('section.course')
            ->where('student_id', $studentId)
            ->whereIn('enrollment_status', ['completed'])
            ->whereNotNull('grade')
            ->get();
        
        $enrollmentData = [];
        $totalCreditsAttempted = 0;
        $totalCreditsEarned = 0;
        $totalQualityPoints = 0;
        
        // Handle grade replacement policy
        $processedCourses = [];
        
        foreach ($enrollments as $enrollment) {
            $courseId = $enrollment->section->course_id;
            $grade = $enrollment->grade;
            
            if (in_array($grade, $this->specialGrades)) {
                continue;
            }
            
            $gradePoints = $enrollment->grade_points ?? $this->getGradePoints($grade);
            $credits = $enrollment->section->course->credits;
            
            // Check if this is a repeated course
            if (isset($processedCourses[$courseId])) {
                // Apply grade replacement policy (keep highest grade)
                $existingGradePoints = $processedCourses[$courseId]['grade_points'];
                
                if ($gradePoints > $existingGradePoints) {
                    // Replace with better grade
                    $totalQualityPoints -= $processedCourses[$courseId]['quality_points'];
                    $totalQualityPoints += ($gradePoints * $credits);
                    
                    $processedCourses[$courseId] = [
                        'grade_points' => $gradePoints,
                        'quality_points' => $gradePoints * $credits,
                        'credits' => $credits
                    ];
                }
            } else {
                // First attempt at this course
                $qualityPoints = $gradePoints * $credits;
                $totalQualityPoints += $qualityPoints;
                $totalCreditsAttempted += $credits;
                
                if ($grade !== 'F') {
                    $totalCreditsEarned += $credits;
                }
                
                $processedCourses[$courseId] = [
                    'grade_points' => $gradePoints,
                    'quality_points' => $qualityPoints,
                    'credits' => $credits
                ];
            }
        }
        
        $cumulativeGPA = $totalCreditsAttempted > 0 
            ? round($totalQualityPoints / $totalCreditsAttempted, 2)
            : 0.0;
        
        return [
            'gpa' => $cumulativeGPA,
            'credits_attempted' => $totalCreditsAttempted,
            'credits_earned' => $totalCreditsEarned,
            'quality_points' => round($totalQualityPoints, 2)
        ];
    }

    /**
     * Calculate major GPA for a student
     *
     * @param int $studentId
     * @return float (returns just the GPA value for compatibility)
     */
    public function calculateMajorGPA($studentId)
    {
        $result = $this->calculateMajorGPADetailed($studentId);
        return $result['gpa'];
    }

    /**
     * Calculate major GPA for a student with detailed information
     *
     * @param int $studentId
     * @return array
     */
    public function calculateMajorGPADetailed($studentId)
    {
        $student = Student::findOrFail($studentId);
        
        // If no major is declared or no program_id field exists, return cumulative GPA
        if (!isset($student->major_id) && !isset($student->program_id)) {
            return [
                'gpa' => $this->calculateCumulativeGPA($studentId),
                'credits' => 0,
                'message' => 'No major declared'
            ];
        }
        
        $majorId = $student->major_id ?? $student->program_id ?? null;
        
        if (!$majorId) {
            return [
                'gpa' => $this->calculateCumulativeGPA($studentId),
                'credits' => 0,
                'message' => 'No major declared'
            ];
        }
        
        // Get courses required for the major
        $majorCourseIds = DB::table('program_courses')
            ->where('program_id', $majorId)
            ->pluck('course_id');
        
        if ($majorCourseIds->isEmpty()) {
            // If no specific major courses defined, return cumulative GPA
            return [
                'gpa' => $this->calculateCumulativeGPA($studentId),
                'credits' => 0,
                'courses_counted' => 0,
                'message' => 'No major courses defined'
            ];
        }
        
        $enrollments = Enrollment::with('section.course')
            ->where('student_id', $studentId)
            ->whereIn('enrollment_status', ['completed'])
            ->whereNotNull('grade')
            ->whereHas('section.course', function ($query) use ($majorCourseIds) {
                $query->whereIn('id', $majorCourseIds);
            })
            ->get();
        
        $enrollmentData = [];
        $totalCredits = 0;
        
        foreach ($enrollments as $enrollment) {
            if (!in_array($enrollment->grade, $this->specialGrades)) {
                $enrollmentData[] = [
                    'letter_grade' => $enrollment->grade,
                    'grade_points' => $enrollment->grade_points ?? $this->getGradePoints($enrollment->grade),
                    'credits' => $enrollment->section->course->credits
                ];
                $totalCredits += $enrollment->section->course->credits;
            }
        }
        
        $majorGPA = $this->calculateGPA($enrollmentData);
        
        return [
            'gpa' => $majorGPA,
            'credits' => $totalCredits,
            'courses_counted' => count($enrollmentData)
        ];
    }

    /**
     * Update student's GPA fields
     *
     * @param int $studentId
     * @return void
     */
    public function updateStudentGPA($studentId)
    {
        $student = Student::findOrFail($studentId);
        
        // Calculate cumulative GPA
        $cumulativeData = $this->calculateCumulativeGPADetailed($studentId);
        
        // Calculate current semester GPA
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        $semesterData = $currentTerm 
            ? $this->calculateSemesterGPA($studentId, $currentTerm->id)
            : ['gpa' => 0];
        
        // Calculate major GPA
        $majorData = $this->calculateMajorGPADetailed($studentId);
        
        // Build update array based on existing columns
        $updateData = [];
        
        // Check which columns exist and update only those
        if (Schema::hasColumn('students', 'cumulative_gpa')) {
            $updateData['cumulative_gpa'] = $cumulativeData['gpa'];
        }
        if (Schema::hasColumn('students', 'gpa')) {
            $updateData['gpa'] = $cumulativeData['gpa'];
        }
        if (Schema::hasColumn('students', 'semester_gpa')) {
            $updateData['semester_gpa'] = $semesterData['gpa'];
        }
        if (Schema::hasColumn('students', 'major_gpa')) {
            $updateData['major_gpa'] = $majorData['gpa'];
        }
        if (Schema::hasColumn('students', 'total_credits_earned')) {
            $updateData['total_credits_earned'] = $cumulativeData['credits_earned'];
        }
        if (Schema::hasColumn('students', 'total_credits_attempted')) {
            $updateData['total_credits_attempted'] = $cumulativeData['credits_attempted'];
        }
        
        // Update student record if there are fields to update
        if (!empty($updateData)) {
            $student->update($updateData);
        }
        
        // Update academic standing
        $this->updateAcademicStanding($student);
        
        // Check for Dean's List
        $this->checkDeansListEligibility($student, $semesterData['gpa']);
    }

    /**
     * Update academic standing based on GPA
     *
     * @param Student $student
     * @return void
     */
    protected function updateAcademicStanding($student)
    {
        // Get the GPA from whichever field exists
        $gpa = $student->cumulative_gpa ?? $student->gpa ?? 0;
        
        $standing = 'good';
        
        if ($gpa < 2.0) {
            if ($gpa < 1.5) {
                $standing = 'suspension';
            } else {
                $standing = 'probation';
            }
        } elseif ($gpa >= 3.5) {
            $standing = 'excellent';
        }
        
        // Only update if the column exists
        if (Schema::hasColumn('students', 'academic_standing')) {
            $student->update(['academic_standing' => $standing]);
            
            // Log standing change if it changed
            if ($student->wasChanged('academic_standing')) {
                DB::table('academic_standing_changes')->insert([
                    'student_id' => $student->id,
                    'previous_standing' => $student->getOriginal('academic_standing'),
                    'new_standing' => $standing,
                    'gpa' => $gpa,
                    'changed_at' => now(),
                    'term_id' => AcademicTerm::where('is_current', true)->first()->id ?? null
                ]);
            }
        }
    }

    /**
     * Check if student qualifies for Dean's List
     *
     * @param Student $student
     * @param float $semesterGPA
     * @return void
     */
    protected function checkDeansListEligibility($student, $semesterGPA)
    {
        // Dean's List criteria: 3.5+ GPA with 12+ credits
        $currentTerm = AcademicTerm::where('is_current', true)->first();
        
        if (!$currentTerm) {
            return;
        }
        
        $semesterCredits = Enrollment::where('student_id', $student->id)
            ->whereHas('section', function($query) use ($currentTerm) {
                $query->where('term_id', $currentTerm->id);
            })
            ->whereIn('enrollment_status', ['completed', 'enrolled'])
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->sum('courses.credits');
        
        if ($semesterGPA >= 3.5 && $semesterCredits >= 12) {
            // Add to Dean's List
            DB::table('deans_list')->insertOrIgnore([
                'student_id' => $student->id,
                'term_id' => $currentTerm->id,
                'gpa' => $semesterGPA,
                'credits' => $semesterCredits,
                'created_at' => now()
            ]);
        }
    }

    /**
     * Update enrollment grade based on component grades
     *
     * @param int $enrollmentId
     * @return array
     */
    public function updateEnrollmentGrade($enrollmentId)
    {
        $calculation = $this->calculateGrade($enrollmentId);
        
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $enrollment->update([
            'grade' => $calculation['letter_grade'],
            'grade_points' => $calculation['grade_points']
        ]);
        
        return $calculation;
    }

    /**
     * Get section grade statistics
     *
     * @param int $sectionId
     * @return array
     */
    public function getSectionStatistics($sectionId)
    {
        $enrollments = Enrollment::where('section_id', $sectionId)
            ->whereIn('enrollment_status', ['enrolled', 'completed'])
            ->get();
        
        $gradeDistribution = [];
        $percentages = [];
        
        foreach ($enrollments as $enrollment) {
            $calculation = $this->calculateGrade($enrollment->id);
            $letterGrade = $calculation['letter_grade'];
            $percentage = $calculation['percentage'];
            
            if (!isset($gradeDistribution[$letterGrade])) {
                $gradeDistribution[$letterGrade] = 0;
            }
            $gradeDistribution[$letterGrade]++;
            
            $percentages[] = $percentage;
        }
        
        // Calculate statistics
        $count = count($percentages);
        $average = $count > 0 ? array_sum($percentages) / $count : 0;
        $median = $count > 0 ? $this->calculateMedian($percentages) : 0;
        
        // Standard deviation
        $standardDeviation = 0;
        if ($count > 1) {
            $variance = 0;
            foreach ($percentages as $percentage) {
                $variance += pow($percentage - $average, 2);
            }
            $variance /= ($count - 1);
            $standardDeviation = sqrt($variance);
        }
        
        return [
            'total_students' => $count,
            'grade_distribution' => $gradeDistribution,
            'average' => round($average, 2),
            'median' => round($median, 2),
            'standard_deviation' => round($standardDeviation, 2),
            'highest' => $count > 0 ? max($percentages) : 0,
            'lowest' => $count > 0 ? min($percentages) : 0
        ];
    }

    /**
     * Calculate letter grade for a section
     *
     * @param float $percentage
     * @param int $sectionId
     * @return string
     */
    public function calculateLetterGrade($percentage, $sectionId = null)
    {
        // For now, use the default grade scale
        // In future, could check for section-specific grade scale
        return $this->getLetterGrade($percentage);
    }

    /**
     * Calculate median of an array
     *
     * @param array $values
     * @return float
     */
    protected function calculateMedian($values)
    {
        sort($values);
        $count = count($values);
        
        if ($count == 0) {
            return 0;
        }
        
        $middle = floor($count / 2);
        
        if ($count % 2 == 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        } else {
            return $values[$middle];
        }
    }
}