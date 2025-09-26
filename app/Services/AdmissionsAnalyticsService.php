<?php

namespace App\Services;

use App\Models\AdmissionApplication;
use App\Models\ApplicationReview;
use App\Models\EnrollmentConfirmation;
use App\Models\ApplicationDocument;
use App\Models\ApplicationStatusHistory;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Models\AdmissionWaitlist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AdmissionsAnalyticsService
{
    /**
     * Cache duration in minutes
     */
    private const CACHE_DURATION = 60;

    /**
     * Metric categories
     */
    private const METRIC_CATEGORIES = [
        'applications' => 'Application Metrics',
        'conversion' => 'Conversion Metrics',
        'demographics' => 'Demographic Metrics',
        'academic' => 'Academic Metrics',
        'process' => 'Process Metrics',
        'financial' => 'Financial Metrics',
    ];

    /**
     * Get dashboard statistics
     *
     * @return array
     */
    public function getDashboardStatistics(): array
    {
        $currentTermId = $this->getCurrentTermId();
        
        return [
            'total_applications' => AdmissionApplication::count(),
            'pending_review' => AdmissionApplication::where('status', 'submitted')->count(),
            'under_review' => AdmissionApplication::where('status', 'under_review')->count(),
            'documents_pending' => AdmissionApplication::where('status', 'documents_pending')->count(),
            'admitted' => AdmissionApplication::where('decision', 'admit')->count(),
            'denied' => AdmissionApplication::where('decision', 'deny')->count(),
            'waitlisted' => AdmissionApplication::where('decision', 'waitlist')->count(),
            'enrolled' => AdmissionApplication::where('enrollment_confirmed', true)->count(),
            'today' => AdmissionApplication::whereDate('created_at', today())->count(),
            'this_week' => AdmissionApplication::whereBetween('created_at', [
                now()->startOfWeek(), 
                now()->endOfWeek()
            ])->count(),
            'current_term' => AcademicTerm::where('is_current', true)->first(),
            'conversion_rate' => $this->calculateQuickConversionRate(),
            'average_review_time' => $this->calculateAverageReviewTime(),
        ];
    }

    /**
     * Get detailed statistics
     *
     * @return array
     */
    public function getDetailedStatistics(): array
    {
        return $this->getApplicationStatistics([
            'term_id' => $this->getCurrentTermId()
        ]);
    }

    /**
     * Get calendar events for admissions
     *
     * @return array
     */
    public function getCalendarEvents(): array
    {
        $events = [];
        
        // Get upcoming deadlines
        $terms = AcademicTerm::where('application_deadline', '>=', now())
            ->orderBy('application_deadline')
            ->get();
        
        foreach ($terms as $term) {
            $events[] = [
                'title' => $term->name . ' - Application Deadline',
                'start' => $term->application_deadline->format('Y-m-d'),
                'type' => 'deadline',
                'color' => '#dc3545'
            ];
            
            if ($term->early_admission_deadline) {
                $events[] = [
                    'title' => $term->name . ' - Early Admission Deadline',
                    'start' => $term->early_admission_deadline->format('Y-m-d'),
                    'type' => 'early_deadline',
                    'color' => '#fd7e14'
                ];
            }
        }
        
        // Get scheduled interviews
        $interviews = DB::table('admission_interviews')
            ->where('interview_date', '>=', now())
            ->get();
        
        foreach ($interviews as $interview) {
            $events[] = [
                'title' => 'Admission Interview',
                'start' => $interview->interview_date,
                'type' => 'interview',
                'color' => '#007bff'
            ];
        }
        
        return $events;
    }

    /**
     * Get application statistics with filters
     *
     * @param array $filters
     * @return array
     */
    public function getApplicationStatistics(array $filters = []): array
    {
        $cacheKey = 'admission_stats_' . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($filters) {
            $query = $this->buildApplicationQuery($filters);

            $stats = [
                'summary' => $this->getSummaryStatistics($query),
                'by_status' => $this->getStatusDistribution($query),
                'by_type' => $this->getApplicationTypeDistribution($query),
                'by_program' => $this->getProgramDistribution($query),
                'by_source' => $this->getSourceDistribution($query),
                'timeline' => $this->getApplicationTimeline($query, $filters),
                'quality_metrics' => $this->getQualityMetrics($query),
            ];

            return $stats;
        });
    }

    /**
     * Calculate conversion rates
     *
     * @param int $termId
     * @param int|null $programId
     * @return array
     */
    public function calculateConversionRates(int $termId, ?int $programId = null): array
    {
        $cacheKey = "conversion_rates_term_{$termId}_program_{$programId}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($termId, $programId) {
            $query = AdmissionApplication::where('term_id', $termId);
            
            if ($programId) {
                $query->where('program_id', $programId);
            }
            
            $applications = $query->get();

            $funnel = [
                'started' => $applications->count(),
                'submitted' => $applications->whereNotNull('submitted_at')->count(),
                'reviewed' => $applications->whereIn('status', ['under_review', 'committee_review', 'decision_pending', 'admitted', 'denied', 'waitlisted'])->count(),
                'admitted' => $applications->where('decision', 'admit')->count(),
                'enrolled' => $applications->where('enrollment_confirmed', true)->count(),
            ];

            $rates = [];
            $previousStage = null;
            $previousCount = null;

            foreach ($funnel as $stage => $count) {
                if ($previousStage !== null && $previousCount > 0) {
                    $rates["{$previousStage}_to_{$stage}"] = [
                        'rate' => round(($count / $previousCount) * 100, 2),
                        'count' => $count,
                        'previous_count' => $previousCount,
                    ];
                }
                $previousStage = $stage;
                $previousCount = $count;
            }

            // Overall conversion rates
            if ($funnel['started'] > 0) {
                $rates['overall'] = [
                    'application_completion' => round(($funnel['submitted'] / $funnel['started']) * 100, 2),
                    'admission_rate' => $funnel['submitted'] > 0 
                        ? round(($funnel['admitted'] / $funnel['submitted']) * 100, 2) 
                        : 0,
                    'yield_rate' => $funnel['admitted'] > 0 
                        ? round(($funnel['enrolled'] / $funnel['admitted']) * 100, 2) 
                        : 0,
                ];
            }

            return [
                'funnel' => $funnel,
                'conversion_rates' => $rates,
                'bottlenecks' => $this->identifyConversionBottlenecks($rates),
            ];
        });
    }

    /**
     * Generate demographic report
     *
     * @param int $termId
     * @param int|null $programId
     * @return array
     */
    public function generateDemographicReport(int $termId, ?int $programId = null): array
    {
        $query = AdmissionApplication::where('term_id', $termId);
        
        if ($programId) {
            $query->where('program_id', $programId);
        }
        
        $applications = $query->get();
        
        return [
            'gender' => $this->analyzeGenderDistribution($applications),
            'nationality' => $this->analyzeNationalityDistribution($applications),
            'age' => $this->analyzeAgeDistribution($applications),
            'geography' => $this->analyzeGeographicDistribution($applications),
        ];
    }

    /**
     * Compare program statistics
     *
     * @param int $termId
     * @return array
     */
    public function compareProgramStatistics(int $termId): array
    {
        $programs = AcademicProgram::where('is_active', true)->get();
        $comparison = [];
        
        foreach ($programs as $program) {
            $applications = AdmissionApplication::where('term_id', $termId)
                ->where('program_id', $program->id)
                ->get();
            
            $comparison[] = [
                'program_id' => $program->id,
                'program_name' => $program->name,
                'total_applications' => $applications->count(),
                'submitted' => $applications->whereNotNull('submitted_at')->count(),
                'admitted' => $applications->where('decision', 'admit')->count(),
                'enrolled' => $applications->where('enrollment_confirmed', true)->count(),
                'conversion_rate' => $this->calculateProgramConversionRate($applications),
                'average_gpa' => $this->calculateAverageGPA($applications),
            ];
        }
        
        return $comparison;
    }

    /**
     * Analyze application trends
     *
     * @param array $filters
     * @return array
     */
    public function analyzeApplicationTrends(array $filters): array
    {
        $startDate = isset($filters['start_date']) ? Carbon::parse($filters['start_date']) : now()->subYear();
        $endDate = isset($filters['end_date']) ? Carbon::parse($filters['end_date']) : now();

        $trends = [
            'daily' => $this->getDailyTrends($startDate, $endDate),
            'weekly' => $this->getWeeklyTrends($startDate, $endDate),
            'monthly' => $this->getMonthlyTrends($startDate, $endDate),
            'year_over_year' => $this->getYearOverYearComparison($startDate, $endDate),
            'predictions' => $this->predictFutureTrends($startDate, $endDate),
        ];

        return $trends;
    }

    /**
     * Predict enrollment yield
     *
     * @param int $termId
     * @param int|null $programId
     * @return array
     */
    public function predictEnrollmentYield(int $termId, ?int $programId = null): array
    {
        // Get historical yield data
        $historicalData = $this->getHistoricalYieldData($termId, $programId);
        
        // Current term data
        $currentQuery = AdmissionApplication::where('term_id', $termId);
        if ($programId) {
            $currentQuery->where('program_id', $programId);
        }

        $currentAdmitted = $currentQuery->where('decision', 'admit')->count();
        $currentEnrolled = $currentQuery->where('enrollment_confirmed', true)->count();
        $daysUntilDeadline = $this->getDaysUntilEnrollmentDeadline($termId);

        // Calculate predictions
        $predictions = [
            'current_admitted' => $currentAdmitted,
            'current_enrolled' => $currentEnrolled,
            'current_yield_rate' => $currentAdmitted > 0 
                ? round(($currentEnrolled / $currentAdmitted) * 100, 2) 
                : 0,
            'historical_average_yield' => round($historicalData['average_yield'], 2),
            'predicted_final_yield' => $this->calculatePredictedYield(
                $historicalData, 
                $currentEnrolled, 
                $currentAdmitted, 
                $daysUntilDeadline
            ),
            'confidence_interval' => $this->calculateConfidenceInterval($historicalData),
            'factors' => $this->getYieldFactors($termId, $programId),
        ];

        return $predictions;
    }

    /**
     * Generate diversity report
     *
     * @param int $termId
     * @return array
     */
    public function generateDiversityReport(int $termId): array
    {
        $applications = AdmissionApplication::where('term_id', $termId)->get();
        
        $report = [
            'gender' => $this->analyzeGenderDistribution($applications),
            'nationality' => $this->analyzeNationalityDistribution($applications),
            'age' => $this->analyzeAgeDistribution($applications),
            'geography' => $this->analyzeGeographicDistribution($applications),
            'diversity_index' => $this->calculateDiversityIndex($applications),
        ];

        return $report;
    }

    /**
     * Compare statistics across terms
     *
     * @param array $termIds
     * @return array
     */
    public function compareTermStatistics(array $termIds): array
    {
        $comparison = [];
        
        foreach ($termIds as $termId) {
            $term = AcademicTerm::find($termId);
            if (!$term) continue;

            $stats = $this->getTermStatistics($termId);
            
            $comparison[] = [
                'term_id' => $termId,
                'term_name' => $term->name,
                'statistics' => $stats,
            ];
        }

        // Calculate trends
        $trends = $this->calculateComparativeTrends($comparison);

        return [
            'terms' => $comparison,
            'trends' => $trends,
            'insights' => $this->generateComparativeInsights($comparison, $trends),
        ];
    }

    /**
     * Identify bottlenecks in the admission process
     *
     * @param string $processStage
     * @return array
     */
    public function identifyBottlenecks(string $processStage): array
    {
        $bottlenecks = [];

        switch ($processStage) {
            case 'application':
                $bottlenecks = $this->identifyApplicationBottlenecks();
                break;
            case 'review':
                $bottlenecks = $this->identifyReviewBottlenecks();
                break;
            case 'decision':
                $bottlenecks = $this->identifyDecisionBottlenecks();
                break;
            case 'enrollment':
                $bottlenecks = $this->identifyEnrollmentBottlenecks();
                break;
            case 'all':
                $bottlenecks = [
                    'application' => $this->identifyApplicationBottlenecks(),
                    'review' => $this->identifyReviewBottlenecks(),
                    'decision' => $this->identifyDecisionBottlenecks(),
                    'enrollment' => $this->identifyEnrollmentBottlenecks(),
                ];
                break;
        }

        return [
            'stage' => $processStage,
            'bottlenecks' => $bottlenecks,
            'recommendations' => $this->generateBottleneckRecommendations($bottlenecks),
            'impact_analysis' => $this->analyzeBottleneckImpact($bottlenecks),
        ];
    }

    /**
     * Generate executive dashboard data
     *
     * @param int $termId
     * @return array
     */
    public function generateExecutiveDashboard(int $termId): array
    {
        $dashboard = [
            'kpis' => $this->getKeyPerformanceIndicators($termId),
            'trends' => $this->getExecutiveTrends($termId),
            'alerts' => $this->generateExecutiveAlerts($termId),
            'comparisons' => $this->getExecutiveComparisons($termId),
            'projections' => $this->getExecutiveProjections($termId),
            'action_items' => $this->generateActionItems($termId),
        ];

        return $dashboard;
    }

    /**
     * Private helper methods
     */

    /**
     * Build application query with filters
     */
    private function buildApplicationQuery(array $filters)
    {
        $query = AdmissionApplication::query();

        if (isset($filters['term_id'])) {
            $query->where('term_id', $filters['term_id']);
        }

        if (isset($filters['program_id'])) {
            $query->where('program_id', $filters['program_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['decision'])) {
            $query->where('decision', $filters['decision']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['application_type'])) {
            $query->where('application_type', $filters['application_type']);
        }

        return $query;
    }

    /**
     * Get summary statistics
     */
    private function getSummaryStatistics($query): array
    {
        $applications = $query->get();

        return [
            'total_applications' => $applications->count(),
            'completed_applications' => $applications->whereNotNull('submitted_at')->count(),
            'in_progress' => $applications->whereNull('submitted_at')->count(),
            'reviewed' => $applications->whereNotNull('decision')->count(),
            'pending_review' => $applications->whereNull('decision')->whereNotNull('submitted_at')->count(),
            'admitted' => $applications->whereIn('decision', ['admit', 'conditional_admit'])->count(),
            'denied' => $applications->where('decision', 'deny')->count(),
            'waitlisted' => $applications->where('decision', 'waitlist')->count(),
            'enrolled' => $applications->where('enrollment_confirmed', true)->count(),
        ];
    }

    /**
     * Get status distribution
     */
    private function getStatusDistribution($query): array
    {
        $total = $query->count();
        
        return $query->get()
            ->groupBy('status')
            ->map(function ($group) use ($total) {
                $count = $group->count();
                return [
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
                ];
            })
            ->toArray();
    }

    /**
     * Get application type distribution
     */
    private function getApplicationTypeDistribution($query): array
    {
        $total = $query->count();
        
        return $query->get()
            ->groupBy('application_type')
            ->map(function ($group) use ($total) {
                $count = $group->count();
                return [
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
                ];
            })
            ->toArray();
    }

    /**
     * Get program distribution
     */
    private function getProgramDistribution($query): array
    {
        $applications = $query->with('program')->get();
        
        return $applications->groupBy('program_id')
            ->map(function ($group) {
                $program = $group->first()->program;
                return [
                    'program_name' => $program->name ?? 'Unknown',
                    'count' => $group->count(),
                    'admitted' => $group->where('decision', 'admit')->count(),
                    'enrolled' => $group->where('enrollment_confirmed', true)->count(),
                ];
            })
            ->toArray();
    }

    /**
     * Get source distribution
     */
    private function getSourceDistribution($query): array
    {
        // This would track how applicants heard about the program
        // Placeholder implementation
        return [
            'website' => 45,
            'referral' => 25,
            'social_media' => 20,
            'other' => 10,
        ];
    }

    /**
     * Get application timeline
     */
    private function getApplicationTimeline($query, array $filters): array
    {
        $period = $filters['period'] ?? 'daily';
        $applications = $query->get();
        
        $timeline = [];
        
        if ($period === 'daily') {
            $grouped = $applications->groupBy(function ($app) {
                return $app->created_at->format('Y-m-d');
            });
        } elseif ($period === 'weekly') {
            $grouped = $applications->groupBy(function ($app) {
                return $app->created_at->format('Y-W');
            });
        } else {
            $grouped = $applications->groupBy(function ($app) {
                return $app->created_at->format('Y-m');
            });
        }

        foreach ($grouped as $date => $apps) {
            $timeline[] = [
                'date' => $date,
                'applications' => $apps->count(),
                'submitted' => $apps->whereNotNull('submitted_at')->count(),
                'admitted' => $apps->where('decision', 'admit')->count(),
            ];
        }

        return $timeline;
    }

    /**
     * Get quality metrics
     */
    private function getQualityMetrics($query): array
    {
        $applications = $query->get();
        
        $gpas = $applications->whereNotNull('previous_gpa')->pluck('previous_gpa');
        
        return [
            'average_gpa' => $gpas->avg() ? round($gpas->avg(), 2) : null,
            'median_gpa' => $gpas->median() ? round($gpas->median(), 2) : null,
            'gpa_range' => [
                'min' => $gpas->min(),
                'max' => $gpas->max(),
            ],
            'test_scores' => $this->analyzeTestScores($applications),
            'completion_rate' => $applications->count() > 0 
                ? round(($applications->whereNotNull('submitted_at')->count() / $applications->count()) * 100, 2)
                : 0,
        ];
    }

    /**
     * Analyze test scores
     *
     * @param Collection $applications
     * @return array
     */
    private function analyzeTestScores($applications): array
    {
        $testScores = [];
        $scoreTypes = ['SAT', 'ACT', 'GRE', 'GMAT', 'WASSCE', 'TOEFL', 'IELTS'];
        
        foreach ($scoreTypes as $type) {
            $scores = $applications->map(function ($app) use ($type) {
                $scores = $app->test_scores ?? [];
                return $scores[$type] ?? null;
            })->filter();
            
            if ($scores->count() > 0) {
                $testScores[$type] = [
                    'count' => $scores->count(),
                    'average' => $this->calculateAverageScore($scores, $type),
                ];
            }
        }
        
        return $testScores;
    }

    /**
     * Calculate average score based on test type
     */
    private function calculateAverageScore($scores, $type): ?float
    {
        switch ($type) {
            case 'SAT':
                $totals = $scores->pluck('total')->filter();
                return $totals->avg();
            case 'ACT':
                $composites = $scores->pluck('composite')->filter();
                return $composites->avg();
            case 'WASSCE':
                // Convert grades to points
                return $this->calculateWASSCEAverage($scores);
            default:
                return null;
        }
    }

    /**
     * Calculate WASSCE average
     */
    private function calculateWASSCEAverage($scores): ?float
    {
        $gradePoints = [
            'A1' => 1, 'B2' => 2, 'B3' => 3, 'C4' => 4, 
            'C5' => 5, 'C6' => 6, 'D7' => 7, 'E8' => 8, 'F9' => 9
        ];
        
        $totalPoints = 0;
        $subjectCount = 0;
        
        foreach ($scores as $score) {
            foreach (['english', 'mathematics', 'science', 'social'] as $subject) {
                if (isset($score[$subject]) && isset($gradePoints[$score[$subject]])) {
                    $totalPoints += $gradePoints[$score[$subject]];
                    $subjectCount++;
                }
            }
        }
        
        return $subjectCount > 0 ? round($totalPoints / $subjectCount, 2) : null;
    }

    /**
     * Get daily trends
     */
    private function getDailyTrends(Carbon $startDate, Carbon $endDate): array
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $trends = [];

        foreach ($period as $date) {
            $dayApplications = AdmissionApplication::whereDate('created_at', $date)->count();
            $daySubmitted = AdmissionApplication::whereDate('submitted_at', $date)->count();
            
            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'applications' => $dayApplications,
                'submitted' => $daySubmitted,
            ];
        }

        return $trends;
    }

    /**
     * Get weekly trends
     */
    private function getWeeklyTrends(Carbon $startDate, Carbon $endDate): array
    {
        $trends = [];
        $currentWeek = $startDate->copy()->startOfWeek();
        
        while ($currentWeek <= $endDate) {
            $weekEnd = $currentWeek->copy()->endOfWeek();
            
            $weekApplications = AdmissionApplication::whereBetween('created_at', [$currentWeek, $weekEnd])->count();
            $weekSubmitted = AdmissionApplication::whereBetween('submitted_at', [$currentWeek, $weekEnd])->count();
            
            $trends[] = [
                'week' => $currentWeek->format('Y-W'),
                'applications' => $weekApplications,
                'submitted' => $weekSubmitted,
            ];
            
            $currentWeek->addWeek();
        }

        return $trends;
    }

    /**
     * Get monthly trends
     */
    private function getMonthlyTrends(Carbon $startDate, Carbon $endDate): array
    {
        $trends = [];
        $currentMonth = $startDate->copy()->startOfMonth();
        
        while ($currentMonth <= $endDate) {
            $monthEnd = $currentMonth->copy()->endOfMonth();
            
            $monthApplications = AdmissionApplication::whereBetween('created_at', [$currentMonth, $monthEnd])->count();
            $monthSubmitted = AdmissionApplication::whereBetween('submitted_at', [$currentMonth, $monthEnd])->count();
            
            $trends[] = [
                'month' => $currentMonth->format('Y-m'),
                'applications' => $monthApplications,
                'submitted' => $monthSubmitted,
            ];
            
            $currentMonth->addMonth();
        }

        return $trends;
    }

    /**
     * Get year-over-year comparison
     */
    private function getYearOverYearComparison(Carbon $startDate, Carbon $endDate): array
    {
        $currentYear = AdmissionApplication::whereBetween('created_at', [$startDate, $endDate])->count();
        
        $lastYearStart = $startDate->copy()->subYear();
        $lastYearEnd = $endDate->copy()->subYear();
        $lastYear = AdmissionApplication::whereBetween('created_at', [$lastYearStart, $lastYearEnd])->count();
        
        $change = $lastYear > 0 ? (($currentYear - $lastYear) / $lastYear) * 100 : 0;
        
        return [
            'current_year' => $currentYear,
            'last_year' => $lastYear,
            'change_percentage' => round($change, 2),
            'trend' => $change > 0 ? 'increasing' : ($change < 0 ? 'decreasing' : 'stable'),
        ];
    }

    /**
     * Predict future trends
     */
    private function predictFutureTrends(Carbon $startDate, Carbon $endDate): array
    {
        $historicalData = $this->getMonthlyTrends($startDate->copy()->subYear(), $endDate);
        
        if (count($historicalData) < 3) {
            return ['insufficient_data' => true];
        }

        $values = array_column($historicalData, 'applications');
        $trend = $this->calculateLinearTrend($values);
        
        return [
            'next_month_prediction' => max(0, round($trend['prediction'])),
            'confidence' => $trend['confidence'],
            'trend_direction' => $trend['slope'] > 0 ? 'increasing' : 'decreasing',
        ];
    }

    /**
     * Calculate linear trend
     */
    private function calculateLinearTrend(array $values): array
    {
        $n = count($values);
        if ($n < 2) {
            return ['prediction' => 0, 'confidence' => 0, 'slope' => 0];
        }

        $x = range(1, $n);
        $y = $values;
        
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        $prediction = $slope * ($n + 1) + $intercept;
        
        // Calculate R-squared for confidence
        $yMean = $sumY / $n;
        $ssTotal = 0;
        $ssResidual = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $yPredicted = $slope * $x[$i] + $intercept;
            $ssTotal += pow($y[$i] - $yMean, 2);
            $ssResidual += pow($y[$i] - $yPredicted, 2);
        }
        
        $rSquared = $ssTotal > 0 ? 1 - ($ssResidual / $ssTotal) : 0;
        
        return [
            'prediction' => $prediction,
            'confidence' => round($rSquared * 100, 2),
            'slope' => $slope,
        ];
    }

    /**
     * Calculate quick conversion rate
     */
    private function calculateQuickConversionRate(): float
    {
        $total = AdmissionApplication::count();
        $submitted = AdmissionApplication::where('status', '!=', 'draft')->count();
        
        return $total > 0 ? round(($submitted / $total) * 100, 2) : 0;
    }

    /**
     * Calculate average review time
     */
    private function calculateAverageReviewTime(): ?float
    {
        // PostgreSQL version using EXTRACT
        $reviewTimes = ApplicationReview::whereNotNull('completed_at')
            ->selectRaw("AVG(EXTRACT(EPOCH FROM (completed_at - created_at)) / 3600) as avg_hours")
            ->first();
        
        return $reviewTimes && $reviewTimes->avg_hours ? round($reviewTimes->avg_hours / 24, 1) : null;
    }

    /**
     * Calculate program conversion rate
     */
    private function calculateProgramConversionRate($applications): float
    {
        $total = $applications->count();
        $enrolled = $applications->where('enrollment_confirmed', true)->count();
        
        return $total > 0 ? round(($enrolled / $total) * 100, 2) : 0;
    }

    /**
     * Calculate average GPA
     */
    private function calculateAverageGPA($applications): ?float
    {
        $gpas = $applications->whereNotNull('previous_gpa')->pluck('previous_gpa');
        return $gpas->count() > 0 ? round($gpas->avg(), 2) : null;
    }

    /**
     * Get current term ID
     */
    private function getCurrentTermId(): ?int
    {
        $term = AcademicTerm::where('is_current', true)->first();
        return $term ? $term->id : null;
    }

    /**
     * Identify conversion bottlenecks
     */
    private function identifyConversionBottlenecks(array $rates): array
    {
        $bottlenecks = [];
        
        foreach ($rates as $stage => $data) {
            if (isset($data['rate']) && $data['rate'] < 70) {
                $bottlenecks[] = [
                    'stage' => $stage,
                    'rate' => $data['rate'],
                    'severity' => $data['rate'] < 50 ? 'high' : 'medium',
                ];
            }
        }
        
        return $bottlenecks;
    }

    /**
     * Get historical yield data
     */
    private function getHistoricalYieldData(int $termId, ?int $programId = null): array
    {
        $query = DB::table('admission_applications')
            ->join('academic_terms', 'admission_applications.term_id', '=', 'academic_terms.id')
            ->where('academic_terms.id', '!=', $termId)
            ->whereNotNull('admission_applications.decision');
        
        if ($programId) {
            $query->where('admission_applications.program_id', $programId);
        }
        
        $historicalData = $query
            ->select(
                'academic_terms.id as term_id',
                'academic_terms.name as term_name',
                DB::raw('COUNT(CASE WHEN decision = "admit" THEN 1 END) as admitted'),
                DB::raw('COUNT(CASE WHEN enrollment_confirmed = true THEN 1 END) as enrolled')
            )
            ->groupBy('academic_terms.id', 'academic_terms.name')
            ->orderBy('academic_terms.start_date', 'desc')
            ->limit(3)
            ->get();
        
        $yields = [];
        foreach ($historicalData as $data) {
            if ($data->admitted > 0) {
                $yields[] = ($data->enrolled / $data->admitted) * 100;
            }
        }
        
        return [
            'average_yield' => count($yields) > 0 ? array_sum($yields) / count($yields) : 0,
            'data' => $historicalData,
        ];
    }

    /**
     * Get days until enrollment deadline
     */
    private function getDaysUntilEnrollmentDeadline(int $termId): ?int
    {
        $term = AcademicTerm::find($termId);
        
        if ($term && $term->enrollment_deadline) {
            // Use Carbon for date difference calculation
            return Carbon::now()->diffInDays(Carbon::parse($term->enrollment_deadline), false);
        }
        
        return null;
    }

    /**
     * Calculate predicted yield
     */
    private function calculatePredictedYield($historicalData, $currentEnrolled, $currentAdmitted, $daysUntilDeadline): float
    {
        if ($currentAdmitted == 0) {
            return 0;
        }
        
        $currentYield = ($currentEnrolled / $currentAdmitted) * 100;
        $historicalAverage = $historicalData['average_yield'];
        
        if ($daysUntilDeadline !== null && $daysUntilDeadline > 0) {
            $projectionFactor = 1 + ($daysUntilDeadline / 30) * 0.3;
            $predictedYield = min($currentYield * $projectionFactor, 100);
            
            return round(($predictedYield * 0.6) + ($historicalAverage * 0.4), 2);
        }
        
        return $currentYield;
    }

    /**
     * Calculate confidence interval
     */
    private function calculateConfidenceInterval($historicalData): array
    {
        $average = $historicalData['average_yield'];
        $margin = 10;
        
        return [
            'lower' => max(0, $average - $margin),
            'upper' => min(100, $average + $margin),
        ];
    }

    /**
     * Get yield factors
     */
    private function getYieldFactors(int $termId, ?int $programId = null): array
    {
        return [
            'scholarship_offers' => ['impact' => 'high', 'correlation' => 0.75],
            'competitor_schools' => ['impact' => 'medium', 'correlation' => -0.45],
            'program_ranking' => ['impact' => 'high', 'correlation' => 0.65],
            'communication_frequency' => ['impact' => 'medium', 'correlation' => 0.35],
        ];
    }

    /**
     * Analyze gender distribution
     */
    private function analyzeGenderDistribution($applications): array
    {
        $total = $applications->count();
        $distribution = $applications->groupBy('gender')
            ->map(function ($group) use ($total) {
                $count = $group->count();
                return [
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
                ];
            })
            ->toArray();
        
        return $distribution;
    }

    /**
     * Analyze nationality distribution
     */
    private function analyzeNationalityDistribution($applications): array
    {
        $total = $applications->count();
        return $applications->groupBy('nationality')
            ->map(function ($group) use ($total) {
                $count = $group->count();
                return [
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
                ];
            })
            ->take(10)
            ->toArray();
    }

    /**
     * Analyze age distribution
     */
    private function analyzeAgeDistribution($applications): array
    {
        $ages = $applications->map(function ($app) {
            return $app->date_of_birth ? Carbon::parse($app->date_of_birth)->age : null;
        })->filter();
        
        return [
            'average' => round($ages->avg(), 1),
            'median' => $ages->median(),
            'min' => $ages->min(),
            'max' => $ages->max(),
            'ranges' => [
                'under_18' => $ages->filter(fn($age) => $age < 18)->count(),
                '18_22' => $ages->filter(fn($age) => $age >= 18 && $age <= 22)->count(),
                '23_30' => $ages->filter(fn($age) => $age >= 23 && $age <= 30)->count(),
                'over_30' => $ages->filter(fn($age) => $age > 30)->count(),
            ],
        ];
    }

    /**
     * Analyze geographic distribution
     */
    private function analyzeGeographicDistribution($applications): array
    {
        return $applications->groupBy('country')
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take(10)
            ->toArray();
    }

    /**
     * Calculate diversity index
     */
    private function calculateDiversityIndex($applications): float
    {
        // Shannon Diversity Index calculation
        $total = $applications->count();
        if ($total == 0) return 0;
        
        $nationalityGroups = $applications->groupBy('nationality');
        $index = 0;
        
        foreach ($nationalityGroups as $group) {
            $proportion = $group->count() / $total;
            if ($proportion > 0) {
                $index -= $proportion * log($proportion);
            }
        }
        
        return round($index, 3);
    }

    /**
     * Get term statistics
     */
    private function getTermStatistics($termId): array
    {
        $applications = AdmissionApplication::where('term_id', $termId)->get();
        
        return [
            'total' => $applications->count(),
            'submitted' => $applications->whereNotNull('submitted_at')->count(),
            'admitted' => $applications->where('decision', 'admit')->count(),
            'enrolled' => $applications->where('enrollment_confirmed', true)->count(),
            'average_gpa' => $this->calculateAverageGPA($applications),
        ];
    }

    /**
     * Calculate comparative trends
     */
    private function calculateComparativeTrends($comparison): array
    {
        if (count($comparison) < 2) {
            return [];
        }
        
        $trends = [];
        for ($i = 1; $i < count($comparison); $i++) {
            $current = $comparison[$i]['statistics'];
            $previous = $comparison[$i - 1]['statistics'];
            
            $trends[] = [
                'terms' => $comparison[$i - 1]['term_name'] . ' to ' . $comparison[$i]['term_name'],
                'total_change' => $current['total'] - $previous['total'],
                'admission_rate_change' => 
                    ($current['submitted'] > 0 ? ($current['admitted'] / $current['submitted']) : 0) -
                    ($previous['submitted'] > 0 ? ($previous['admitted'] / $previous['submitted']) : 0),
            ];
        }
        
        return $trends;
    }

    /**
     * Generate comparative insights
     */
    private function generateComparativeInsights($comparison, $trends): array
    {
        return [
            'best_performing_term' => $this->findBestPerformingTerm($comparison),
            'trends_summary' => $this->summarizeTrends($trends),
            'recommendations' => $this->generateRecommendations($comparison, $trends),
        ];
    }

    /**
     * Find best performing term
     */
    private function findBestPerformingTerm($comparison): ?array
    {
        if (empty($comparison)) return null;
        
        $best = null;
        $bestRate = 0;
        
        foreach ($comparison as $term) {
            $stats = $term['statistics'];
            if ($stats['submitted'] > 0) {
                $rate = $stats['admitted'] / $stats['submitted'];
                if ($rate > $bestRate) {
                    $bestRate = $rate;
                    $best = $term;
                }
            }
        }
        
        return $best;
    }

    /**
     * Summarize trends
     */
    private function summarizeTrends($trends): string
    {
        if (empty($trends)) {
            return 'Insufficient data for trend analysis';
        }
        
        $totalChange = array_sum(array_column($trends, 'total_change'));
        
        if ($totalChange > 0) {
            return 'Overall increasing application volume';
        } elseif ($totalChange < 0) {
            return 'Overall decreasing application volume';
        } else {
            return 'Stable application volume';
        }
    }

    /**
     * Generate recommendations
     */
    private function generateRecommendations($comparison, $trends): array
    {
        $recommendations = [];
        
        if (count($comparison) > 0) {
            $latest = end($comparison);
            $stats = $latest['statistics'];
            
            if ($stats['submitted'] > 0) {
                $admissionRate = $stats['admitted'] / $stats['submitted'];
                
                if ($admissionRate < 0.2) {
                    $recommendations[] = 'Consider increasing admission rate - current rate is very selective';
                } elseif ($admissionRate > 0.8) {
                    $recommendations[] = 'Review admission criteria - acceptance rate may be too high';
                }
            }
        }
        
        return $recommendations;
    }

    /**
     * Identify bottlenecks in different stages
     */
    private function identifyApplicationBottlenecks(): array
    {
        $drafts = AdmissionApplication::where('status', 'draft')
            ->where('created_at', '<', now()->subDays(7))
            ->count();
        
        return [
            'abandoned_drafts' => $drafts,
            'average_completion_time' => $this->calculateAverageCompletionTime(),
        ];
    }

    private function identifyReviewBottlenecks(): array
    {
        $pendingReviews = ApplicationReview::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(3))
            ->count();
        
        return [
            'overdue_reviews' => $pendingReviews,
            'average_review_time' => $this->calculateAverageReviewTime(),
        ];
    }

    private function identifyDecisionBottlenecks(): array
    {
        $pendingDecisions = AdmissionApplication::whereIn('status', ['committee_review', 'decision_pending'])
            ->where('updated_at', '<', now()->subDays(7))
            ->count();
        
        return [
            'pending_decisions' => $pendingDecisions,
        ];
    }

    private function identifyEnrollmentBottlenecks(): array
    {
        $pendingEnrollments = AdmissionApplication::where('decision', 'admit')
            ->where('enrollment_confirmed', false)
            ->count();
        
        return [
            'pending_enrollments' => $pendingEnrollments,
        ];
    }

    /**
     * Calculate average completion time
     */
    private function calculateAverageCompletionTime(): ?float
    {
        // PostgreSQL version
        $completionTimes = AdmissionApplication::whereNotNull('submitted_at')
            ->whereNotNull('started_at')
            ->selectRaw("AVG(EXTRACT(EPOCH FROM (submitted_at - started_at)) / 3600) as avg_hours")
            ->first();
        
        return $completionTimes && $completionTimes->avg_hours ? round($completionTimes->avg_hours / 24, 1) : null;
    }

    private function generateBottleneckRecommendations($bottlenecks): array
    {
        $recommendations = [];
        
        if (is_array($bottlenecks)) {
            foreach ($bottlenecks as $key => $value) {
                if (is_array($value) && isset($value['abandoned_drafts']) && $value['abandoned_drafts'] > 10) {
                    $recommendations[] = 'High number of abandoned applications - consider simplifying the form';
                }
                if (is_array($value) && isset($value['overdue_reviews']) && $value['overdue_reviews'] > 5) {
                    $recommendations[] = 'Reviews are taking too long - consider adding more reviewers';
                }
            }
        }
        
        return $recommendations;
    }

    private function analyzeBottleneckImpact($bottlenecks): array
    {
        return [
            'estimated_lost_applications' => $this->estimateLostApplications($bottlenecks),
            'delay_impact' => $this->calculateDelayImpact($bottlenecks),
        ];
    }

    private function estimateLostApplications($bottlenecks): int
    {
        $lost = 0;
        
        if (is_array($bottlenecks)) {
            foreach ($bottlenecks as $stage => $data) {
                if (is_array($data) && isset($data['abandoned_drafts'])) {
                    $lost += $data['abandoned_drafts'];
                }
            }
        }
        
        return $lost;
    }

    private function calculateDelayImpact($bottlenecks): string
    {
        return 'Moderate impact on enrollment timeline';
    }

    /**
     * Generate executive dashboard components
     */
    private function getKeyPerformanceIndicators($termId): array
    {
        $applications = AdmissionApplication::where('term_id', $termId)->get();
        $submitted = $applications->whereNotNull('submitted_at');
        
        return [
            'application_volume' => $applications->count(),
            'submission_rate' => $applications->count() > 0 
                ? round(($submitted->count() / $applications->count()) * 100, 2) 
                : 0,
            'admission_rate' => $submitted->count() > 0
                ? round(($applications->where('decision', 'admit')->count() / $submitted->count()) * 100, 2)
                : 0,
            'yield_rate' => $applications->where('decision', 'admit')->count() > 0
                ? round(($applications->where('enrollment_confirmed', true)->count() / 
                        $applications->where('decision', 'admit')->count()) * 100, 2)
                : 0,
        ];
    }

    private function getExecutiveTrends($termId): array
    {
        return [
            'monthly_applications' => $this->getMonthlyTrends(now()->subMonths(6), now()),
            'conversion_funnel' => $this->calculateConversionRates($termId),
        ];
    }

    private function generateExecutiveAlerts($termId): array
    {
        $alerts = [];
        
        $pendingReviews = ApplicationReview::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(5))
            ->count();
        
        if ($pendingReviews > 10) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "High number of pending reviews: {$pendingReviews}",
            ];
        }
        
        return $alerts;
    }

    private function getExecutiveComparisons($termId): array
    {
        $currentTerm = $this->getTermStatistics($termId);
        $previousTermId = AcademicTerm::where('id', '<', $termId)
            ->orderBy('id', 'desc')
            ->first()->id ?? null;
        
        if ($previousTermId) {
            $previousTerm = $this->getTermStatistics($previousTermId);
            
            return [
                'current' => $currentTerm,
                'previous' => $previousTerm,
                'change' => [
                    'total' => $currentTerm['total'] - $previousTerm['total'],
                    'admitted' => $currentTerm['admitted'] - $previousTerm['admitted'],
                ],
            ];
        }
        
        return ['current' => $currentTerm];
    }

    private function getExecutiveProjections($termId): array
    {
        return $this->predictEnrollmentYield($termId);
    }

    private function generateActionItems($termId): array
    {
        $actionItems = [];
        
        $bottlenecks = $this->identifyBottlenecks('all');
        
        if ($bottlenecks['bottlenecks']['application']['abandoned_drafts'] > 20) {
            $actionItems[] = 'Review and simplify application process';
        }
        
        if ($bottlenecks['bottlenecks']['review']['overdue_reviews'] > 10) {
            $actionItems[] = 'Assign additional reviewers or extend deadlines';
        }
        
        return $actionItems;
    }
}