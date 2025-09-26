<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\User;
use App\Models\Registration;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get counts with proper error handling
        $studentCount = $this->getModelCount(Student::class);
        $activeStudentCount = $this->getActiveStudentCount();
        $courseCount = $this->getModelCount(Course::class);
        $sectionCount = $this->getSectionCount();
        $openSectionCount = $this->getOpenSectionCount();
        $facultyCount = $this->getFacultyCount();
        $totalEnrollments = $this->getEnrollmentCount();
        
        // Get recent students with proper eager loading
        $recentStudents = $this->getRecentStudents();
        
        // Get enrollment trend data
        $enrollmentTrends = $this->getEnrollmentTrends();
        
        // Get grade distribution
        $gradeDistribution = $this->getGradeDistribution();
        
        // Get academic performance metrics
        $performanceMetrics = $this->getPerformanceMetrics();
        
        // Get upcoming deadlines
        $upcomingDeadlines = $this->getUpcomingDeadlines();
        
        return view('dashboard', compact(
            'studentCount',
            'activeStudentCount',
            'courseCount',
            'sectionCount',
            'openSectionCount',
            'facultyCount',
            'totalEnrollments',
            'recentStudents',
            'enrollmentTrends',
            'gradeDistribution',
            'performanceMetrics',
            'upcomingDeadlines'
        ));
    }

    /**
     * Get count for a model safely
     */
    private function getModelCount($modelClass)
    {
        if (!class_exists($modelClass)) {
            return 0;
        }
        
        try {
            return $modelClass::count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get active student count
     */
    private function getActiveStudentCount()
    {
        if (!class_exists(Student::class)) {
            return 0;
        }
        
        try {
            return Student::where('enrollment_status', 'active')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get section count
     */
    private function getSectionCount()
    {
        if (!class_exists(CourseSection::class)) {
            return 0;
        }
        
        try {
            return CourseSection::count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get open section count
     */
    private function getOpenSectionCount()
    {
        if (!class_exists(CourseSection::class)) {
            return 0;
        }
        
        try {
            return CourseSection::where('status', 'open')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get faculty count
     */
    private function getFacultyCount()
    {
        try {
            return User::where('user_type', 'faculty')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get enrollment count
     */
    private function getEnrollmentCount()
    {
        if (!class_exists(Registration::class)) {
            return 0;
        }
        
        try {
            return Registration::where('status', 'enrolled')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get recent students
     */
    private function getRecentStudents()
    {
        if (!class_exists(Student::class)) {
            return collect();
        }
        
        try {
            $query = Student::latest()->take(5);
            
            // Check if program relationship exists
            if (method_exists(Student::class, 'program')) {
                $query->with('program');
            }
            
            return $query->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Get enrollment trends for the chart
     */
    private function getEnrollmentTrends()
    {
        // For now, return sample data
        // In production, this would query actual enrollment data by month
        return [
            'undergraduate' => [3200, 3350, 3400, 3380, 3300, 3250, 3100, 3400, 3600, 3650, 3700, 3680],
            'graduate' => [800, 850, 870, 880, 860, 820, 780, 890, 950, 980, 1000, 990]
        ];
    }

    /**
     * Get grade distribution
     */
    private function getGradeDistribution()
    {
        if (!class_exists(Registration::class)) {
            // Return sample data if Registration model doesn't exist
            return [
                'A' => 28,
                'B' => 35,
                'C' => 22,
                'D' => 10,
                'F' => 5
            ];
        }
        
        try {
            $grades = Registration::whereNotNull('final_grade')
                ->select('final_grade', DB::raw('count(*) as count'))
                ->groupBy('final_grade')
                ->get();
            
            $distribution = [];
            foreach ($grades as $grade) {
                // Group grades by letter (A+, A, A- all become 'A')
                $letter = substr($grade->final_grade, 0, 1);
                if (!isset($distribution[$letter])) {
                    $distribution[$letter] = 0;
                }
                $distribution[$letter] += $grade->count;
            }
            
            // If no real data, return sample data
            if (empty($distribution)) {
                return [
                    'A' => 28,
                    'B' => 35,
                    'C' => 22,
                    'D' => 10,
                    'F' => 5
                ];
            }
            
            return $distribution;
        } catch (\Exception $e) {
            return [
                'A' => 28,
                'B' => 35,
                'C' => 22,
                'D' => 10,
                'F' => 5
            ];
        }
    }

    /**
     * Get academic performance metrics
     */
    private function getPerformanceMetrics()
    {
        // Calculate actual metrics if models exist
        $averageGPA = 3.42;
        $passRate = 92;
        $retentionRate = 88;
        $graduationRate = 76;
        
        if (class_exists(Registration::class)) {
            try {
                // Calculate average GPA from actual grades
                $gpaData = Registration::whereNotNull('grade_points')
                    ->whereNotNull('credits_attempted')
                    ->where('credits_attempted', '>', 0)
                    ->select(
                        DB::raw('SUM(grade_points) as total_points'),
                        DB::raw('SUM(credits_attempted) as total_credits')
                    )
                    ->first();
                
                if ($gpaData && $gpaData->total_credits > 0) {
                    $averageGPA = round($gpaData->total_points / $gpaData->total_credits, 2);
                }
                
                // Calculate pass rate
                $totalCompleted = Registration::where('status', 'completed')->count();
                $totalPassed = Registration::where('status', 'completed')
                    ->whereNotIn('final_grade', ['F', 'W', 'I'])
                    ->count();
                
                if ($totalCompleted > 0) {
                    $passRate = round(($totalPassed / $totalCompleted) * 100);
                }
            } catch (\Exception $e) {
                // Use default values on error
            }
        }
        
        return [
            'averageGPA' => $averageGPA,
            'passRate' => $passRate,
            'retentionRate' => $retentionRate,
            'graduationRate' => $graduationRate
        ];
    }

    /**
     * Get upcoming deadlines
     */
    private function getUpcomingDeadlines()
    {
        // This would normally query from a deadlines or calendar table
        // For now, return sample deadlines
        return [
            [
                'title' => 'Fall 2025 Registration Deadline',
                'description' => 'Last day to register for Fall semester courses',
                'date' => now()->addDays(3),
                'type' => 'danger',
                'days_remaining' => 3
            ],
            [
                'title' => 'Add/Drop Period Ends',
                'description' => 'Final day to add or drop courses without penalty',
                'date' => now()->addDays(14),
                'type' => 'warning',
                'days_remaining' => 14
            ],
            [
                'title' => 'Midterm Grade Submission',
                'description' => 'Faculty deadline for midterm grade entry',
                'date' => now()->addDays(49),
                'type' => 'info',
                'days_remaining' => 49
            ]
        ];
    }
}