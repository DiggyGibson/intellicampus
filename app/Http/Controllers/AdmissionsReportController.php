<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AdmissionsAnalyticsService;
use App\Services\ApplicationService;
use App\Services\FinancialIntegrationService;
use App\Models\AdmissionApplication;
use App\Models\ApplicationReview;
use App\Models\EnrollmentConfirmation;
use App\Models\ApplicationFee;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Models\EntranceExam;
use App\Models\EntranceExamResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdmissionsReportExport;
use App\Exports\ConversionFunnelExport;
use App\Exports\DemographicReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;

class AdmissionsReportController extends Controller
{
    protected $analyticsService;
    protected $applicationService;
    protected $financialService;

    /**
     * Report types available
     */
    private const REPORT_TYPES = [
        'executive_summary' => 'Executive Summary',
        'application_statistics' => 'Application Statistics',
        'conversion_funnel' => 'Conversion Funnel Analysis',
        'demographic_analysis' => 'Demographic Analysis',
        'program_wise' => 'Program-wise Report',
        'comparative_analysis' => 'Comparative Analysis',
        'financial_summary' => 'Financial Summary',
        'yield_analysis' => 'Enrollment Yield Analysis',
        'geographic_distribution' => 'Geographic Distribution',
        'test_score_analysis' => 'Test Score Analysis',
    ];

    /**
     * Export formats
     */
    private const EXPORT_FORMATS = [
        'pdf' => 'PDF Document',
        'excel' => 'Excel Spreadsheet',
        'csv' => 'CSV File',
        'json' => 'JSON Data',
    ];

    /**
     * Date range presets
     */
    private const DATE_RANGES = [
        'current_term' => 'Current Term',
        'last_term' => 'Last Term',
        'current_year' => 'Current Year',
        'last_year' => 'Last Year',
        'last_3_years' => 'Last 3 Years',
        'last_5_years' => 'Last 5 Years',
        'custom' => 'Custom Range',
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(
        AdmissionsAnalyticsService $analyticsService,
        ApplicationService $applicationService,
        FinancialIntegrationService $financialService
    ) {
        $this->analyticsService = $analyticsService;
        $this->applicationService = $applicationService;
        $this->financialService = $financialService;
        
        // Middleware for report access
        $this->middleware(['auth', 'role:admin,admissions_director,registrar,dean,data_analyst']);
    }

    /**
     * Display reports dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        try {
            // Get current term
            $currentTerm = AcademicTerm::current()->first();
            
            // Get quick stats
            $quickStats = Cache::remember('admissions_quick_stats', 300, function () use ($currentTerm) {
                return $this->getQuickStats($currentTerm);
            });
            
            // Get recent reports
            $recentReports = $this->getRecentReports();
            
            // Get available terms for filtering
            $terms = AcademicTerm::orderBy('start_date', 'desc')
                ->limit(10)
                ->get();
            
            // Get programs for filtering
            $programs = AcademicProgram::where('is_active', true)
                ->orderBy('name')
                ->get();
            
            return view('admissions.reports.dashboard', compact(
                'quickStats',
                'recentReports',
                'terms',
                'programs'
            ));
            
        } catch (Exception $e) {
            Log::error('Failed to load reports dashboard', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            
            return redirect()->route('admissions.admin.dashboard')
                ->with('error', 'Unable to load reports dashboard.');
        }
    }

    /**
     * Generate application statistics report.
     *
     * @param Request $request
     * @return mixed
     */
    public function applicationStatistics(Request $request)
    {
        try {
            $validated = $request->validate([
                'term_id' => 'nullable|exists:academic_terms,id',
                'program_id' => 'nullable|exists:academic_programs,id',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after:date_from',
                'group_by' => 'nullable|in:day,week,month,program,status',
                'export_format' => 'nullable|in:pdf,excel,csv,json',
            ]);
            
            // Get statistics
            $filters = $this->buildFilters($validated);
            $statistics = $this->analyticsService->getApplicationStatistics($filters);
            
            // Add trend analysis
            $statistics['trends'] = $this->analyticsService->analyzeApplicationTrends([
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
            ]);
            
            // Add comparisons
            if ($validated['term_id'] ?? null) {
                $previousTerm = AcademicTerm::where('id', '<', $validated['term_id'])
                    ->orderBy('id', 'desc')
                    ->first();
                    
                if ($previousTerm) {
                    $previousFilters = array_merge($filters, ['term_id' => $previousTerm->id]);
                    $previousStats = $this->analyticsService->getApplicationStatistics($previousFilters);
                    $statistics['comparison'] = $this->compareStatistics($statistics, $previousStats);
                }
            }
            
            // Export if requested
            if ($validated['export_format'] ?? null) {
                return $this->exportReport(
                    'application_statistics',
                    $statistics,
                    $validated['export_format']
                );
            }
            
            return view('admissions.reports.application-statistics', compact('statistics', 'filters'));
            
        } catch (Exception $e) {
            Log::error('Failed to generate application statistics', [
                'error' => $e->getMessage(),
                'filters' => $request->all(),
            ]);
            
            return redirect()->back()
                ->with('error', 'Unable to generate statistics report.');
        }
    }

    /**
     * Generate conversion funnel analysis.
     *
     * @param Request $request
     * @return mixed
     */
    public function conversionFunnel(Request $request)
    {
        try {
            $validated = $request->validate([
                'term_id' => 'required|exists:academic_terms,id',
                'program_id' => 'nullable|exists:academic_programs,id',
                'export_format' => 'nullable|in:pdf,excel,csv',
            ]);
            
            $termId = $validated['term_id'];
            $programId = $validated['program_id'] ?? null;
            
            // Calculate conversion rates
            $conversionRates = $this->analyticsService->calculateConversionRates($termId, $programId);
            
            // Build funnel data
            $funnelData = [
                'stages' => [
                    [
                        'name' => 'Applications Started',
                        'count' => $conversionRates['started'],
                        'percentage' => 100,
                    ],
                    [
                        'name' => 'Applications Submitted',
                        'count' => $conversionRates['submitted'],
                        'percentage' => $this->calculatePercentage($conversionRates['submitted'], $conversionRates['started']),
                    ],
                    [
                        'name' => 'Documents Complete',
                        'count' => $conversionRates['complete'],
                        'percentage' => $this->calculatePercentage($conversionRates['complete'], $conversionRates['started']),
                    ],
                    [
                        'name' => 'Reviews Completed',
                        'count' => $conversionRates['reviewed'],
                        'percentage' => $this->calculatePercentage($conversionRates['reviewed'], $conversionRates['started']),
                    ],
                    [
                        'name' => 'Decisions Made',
                        'count' => $conversionRates['decided'],
                        'percentage' => $this->calculatePercentage($conversionRates['decided'], $conversionRates['started']),
                    ],
                    [
                        'name' => 'Admitted',
                        'count' => $conversionRates['admitted'],
                        'percentage' => $this->calculatePercentage($conversionRates['admitted'], $conversionRates['started']),
                    ],
                    [
                        'name' => 'Enrolled',
                        'count' => $conversionRates['enrolled'],
                        'percentage' => $this->calculatePercentage($conversionRates['enrolled'], $conversionRates['started']),
                    ],
                ],
                'dropout_points' => $this->identifyDropoutPoints($conversionRates),
                'bottlenecks' => $this->analyticsService->identifyBottlenecks('application'),
                'recommendations' => $this->generateFunnelRecommendations($conversionRates),
            ];
            
            // Export if requested
            if ($validated['export_format'] ?? null) {
                return $this->exportReport(
                    'conversion_funnel',
                    $funnelData,
                    $validated['export_format']
                );
            }
            
            return view('admissions.reports.conversion-funnel', compact('funnelData', 'termId', 'programId'));
            
        } catch (Exception $e) {
            Log::error('Failed to generate conversion funnel', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            
            return redirect()->back()
                ->with('error', 'Unable to generate conversion funnel analysis.');
        }
    }

    /**
     * Generate demographic analysis report.
     *
     * @param Request $request
     * @return mixed
     */
    public function demographicAnalysis(Request $request)
    {
        try {
            $validated = $request->validate([
                'term_id' => 'required|exists:academic_terms,id',
                'program_id' => 'nullable|exists:academic_programs,id',
                'analysis_type' => 'nullable|in:gender,nationality,age,geography,income',
                'export_format' => 'nullable|in:pdf,excel,csv',
            ]);
            
            $termId = $validated['term_id'];
            
            // Generate diversity report
            $diversityReport = $this->analyticsService->generateDiversityReport($termId);
            
            // Build demographic analysis
            $demographics = [
                'gender_distribution' => $this->analyzeGenderDistribution($termId, $validated['program_id'] ?? null),
                'nationality_distribution' => $this->analyzeNationalityDistribution($termId, $validated['program_id'] ?? null),
                'age_distribution' => $this->analyzeAgeDistribution($termId, $validated['program_id'] ?? null),
                'geographic_distribution' => $this->analyzeGeographicDistribution($termId, $validated['program_id'] ?? null),
                'socioeconomic_analysis' => $this->analyzeSocioeconomicFactors($termId, $validated['program_id'] ?? null),
                'diversity_metrics' => $diversityReport,
            ];
            
            // Export if requested
            if ($validated['export_format'] ?? null) {
                return $this->exportReport(
                    'demographic_analysis',
                    $demographics,
                    $validated['export_format']
                );
            }
            
            return view('admissions.reports.demographic-analysis', compact('demographics', 'termId'));
            
        } catch (Exception $e) {
            Log::error('Failed to generate demographic analysis', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            
            return redirect()->back()
                ->with('error', 'Unable to generate demographic analysis.');
        }
    }

    /**
     * Generate program-wise report.
     *
     * @param Request $request
     * @return mixed
     */
    public function programWiseReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'term_id' => 'required|exists:academic_terms,id',
                'export_format' => 'nullable|in:pdf,excel,csv',
            ]);
            
            $termId = $validated['term_id'];
            
            // Get all active programs
            $programs = AcademicProgram::where('is_active', true)->get();
            
            $programReports = [];
            foreach ($programs as $program) {
                $programReports[] = [
                    'program' => $program,
                    'statistics' => $this->analyticsService->getApplicationStatistics([
                        'term_id' => $termId,
                        'program_id' => $program->id,
                    ]),
                    'conversion_rate' => $this->analyticsService->calculateConversionRates($termId, $program->id),
                    'yield_rate' => $this->analyticsService->predictEnrollmentYield($termId, $program->id),
                    'average_gpa' => $this->calculateAverageGPA($termId, $program->id),
                    'average_test_scores' => $this->calculateAverageTestScores($termId, $program->id),
                    'financial_summary' => $this->getProgramFinancialSummary($termId, $program->id),
                ];
            }
            
            // Sort by total applications
            usort($programReports, function ($a, $b) {
                return $b['statistics']['total_applications'] <=> $a['statistics']['total_applications'];
            });
            
            // Export if requested
            if ($validated['export_format'] ?? null) {
                return $this->exportReport(
                    'program_wise',
                    $programReports,
                    $validated['export_format']
                );
            }
            
            return view('admissions.reports.program-wise', compact('programReports', 'termId'));
            
        } catch (Exception $e) {
            Log::error('Failed to generate program-wise report', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            
            return redirect()->back()
                ->with('error', 'Unable to generate program-wise report.');
        }
    }

    /**
     * Generate comparative analysis report.
     *
     * @param Request $request
     * @return mixed
     */
    public function comparativeAnalysis(Request $request)
    {
        try {
            $validated = $request->validate([
                'term_ids' => 'required|array|min:2|max:5',
                'term_ids.*' => 'exists:academic_terms,id',
                'program_id' => 'nullable|exists:academic_programs,id',
                'comparison_metrics' => 'nullable|array',
                'export_format' => 'nullable|in:pdf,excel,csv',
            ]);
            
            $termIds = $validated['term_ids'];
            $programId = $validated['program_id'] ?? null;
            
            // Compare term statistics
            $comparison = $this->analyticsService->compareTermStatistics($termIds, $programId);
            
            // Build comparison data
            $comparisonData = [
                'terms' => AcademicTerm::whereIn('id', $termIds)->get(),
                'metrics' => $comparison,
                'trends' => $this->calculateTrends($comparison),
                'insights' => $this->generateComparativeInsights($comparison),
                'charts' => [
                    'applications_trend' => $this->prepareChartData($comparison, 'total_applications'),
                    'conversion_trend' => $this->prepareChartData($comparison, 'conversion_rate'),
                    'yield_trend' => $this->prepareChartData($comparison, 'yield_rate'),
                    'revenue_trend' => $this->prepareChartData($comparison, 'total_revenue'),
                ],
            ];
            
            // Export if requested
            if ($validated['export_format'] ?? null) {
                return $this->exportReport(
                    'comparative_analysis',
                    $comparisonData,
                    $validated['export_format']
                );
            }
            
            return view('admissions.reports.comparative-analysis', compact('comparisonData'));
            
        } catch (Exception $e) {
            Log::error('Failed to generate comparative analysis', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            
            return redirect()->back()
                ->with('error', 'Unable to generate comparative analysis.');
        }
    }

    /**
     * Export data in various formats.
     *
     * @param Request $request
     * @return mixed
     */
    public function exportData(Request $request)
    {
        try {
            $validated = $request->validate([
                'report_type' => 'required|string',
                'format' => 'required|in:pdf,excel,csv,json',
                'filters' => 'nullable|array',
            ]);
            
            // Get the data based on report type
            $data = $this->getReportData($validated['report_type'], $validated['filters'] ?? []);
            
            // Export in requested format
            return $this->exportReport(
                $validated['report_type'],
                $data,
                $validated['format']
            );
            
        } catch (Exception $e) {
            Log::error('Failed to export data', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);
            
            return redirect()->back()
                ->with('error', 'Unable to export data.');
        }
    }

    /**
     * Get quick statistics for dashboard.
     *
     * @param AcademicTerm|null $term
     * @return array
     */
    private function getQuickStats($term): array
    {
        if (!$term) {
            return [
                'total_applications' => 0,
                'pending_review' => 0,
                'admitted' => 0,
                'enrolled' => 0,
                'conversion_rate' => 0,
                'yield_rate' => 0,
            ];
        }
        
        $stats = AdmissionApplication::where('term_id', $term->id)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status IN ("submitted", "under_review") THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN decision = "admit" THEN 1 ELSE 0 END) as admitted,
                SUM(CASE WHEN enrollment_confirmed = 1 THEN 1 ELSE 0 END) as enrolled
            ')
            ->first();
        
        return [
            'total_applications' => $stats->total ?? 0,
            'pending_review' => $stats->pending ?? 0,
            'admitted' => $stats->admitted ?? 0,
            'enrolled' => $stats->enrolled ?? 0,
            'conversion_rate' => $stats->total > 0 ? round(($stats->admitted / $stats->total) * 100, 1) : 0,
            'yield_rate' => $stats->admitted > 0 ? round(($stats->enrolled / $stats->admitted) * 100, 1) : 0,
        ];
    }

    /**
     * Get recent reports generated.
     *
     * @return Collection
     */
    private function getRecentReports()
    {
        // This would fetch from a reports history table if implemented
        return collect([]);
    }

    /**
     * Build filters from request.
     *
     * @param array $validated
     * @return array
     */
    private function buildFilters(array $validated): array
    {
        $filters = [];
        
        if ($validated['term_id'] ?? null) {
            $filters['term_id'] = $validated['term_id'];
        }
        
        if ($validated['program_id'] ?? null) {
            $filters['program_id'] = $validated['program_id'];
        }
        
        if ($validated['date_from'] ?? null) {
            $filters['date_from'] = $validated['date_from'];
        }
        
        if ($validated['date_to'] ?? null) {
            $filters['date_to'] = $validated['date_to'];
        }
        
        return $filters;
    }

    /**
     * Export report in specified format.
     *
     * @param string $reportType
     * @param array $data
     * @param string $format
     * @return mixed
     */
    private function exportReport(string $reportType, array $data, string $format)
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "{$reportType}_{$timestamp}";
        
        switch ($format) {
            case 'pdf':
                $pdf = PDF::loadView("admissions.reports.exports.{$reportType}", compact('data'));
                return $pdf->download("{$filename}.pdf");
                
            case 'excel':
                return Excel::download(
                    new AdmissionsReportExport($data, $reportType),
                    "{$filename}.xlsx"
                );
                
            case 'csv':
                return $this->exportAsCSV($data, $filename);
                
            case 'json':
                return response()->json($data)
                    ->header('Content-Disposition', "attachment; filename={$filename}.json");
                
            default:
                throw new Exception("Unsupported export format: {$format}");
        }
    }

    /**
     * Calculate percentage.
     *
     * @param int $value
     * @param int $total
     * @return float
     */
    private function calculatePercentage(int $value, int $total): float
    {
        return $total > 0 ? round(($value / $total) * 100, 2) : 0;
    }

    /**
     * Compare statistics between periods.
     *
     * @param array $current
     * @param array $previous
     * @return array
     */
    private function compareStatistics(array $current, array $previous): array
    {
        $comparison = [];
        
        foreach ($current as $key => $value) {
            if (is_numeric($value) && isset($previous[$key])) {
                $previousValue = $previous[$key];
                $difference = $value - $previousValue;
                $percentChange = $previousValue > 0 ? round(($difference / $previousValue) * 100, 2) : 0;
                
                $comparison[$key] = [
                    'current' => $value,
                    'previous' => $previousValue,
                    'difference' => $difference,
                    'percent_change' => $percentChange,
                    'trend' => $difference > 0 ? 'up' : ($difference < 0 ? 'down' : 'stable'),
                ];
            }
        }
        
        return $comparison;
    }
}