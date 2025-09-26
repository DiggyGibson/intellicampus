<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EntranceExam;
use App\Models\ExamCenter;
use App\Models\ExamSession;
use App\Models\EntranceExamResult;
use App\Models\ExamAnswerKey;
use App\Models\ExamCertificate;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Exception;

class PublicExamController extends Controller
{
    /**
     * Cache duration in minutes
     */
    private const CACHE_DURATION = 60;

    /**
     * Results display limit without authentication
     */
    private const PUBLIC_RESULTS_LIMIT = 100;

    /**
     * Exam types
     */
    private const EXAM_TYPES = [
        'entrance' => 'Entrance Examination',
        'placement' => 'Placement Test',
        'diagnostic' => 'Diagnostic Assessment',
        'scholarship' => 'Scholarship Examination',
        'transfer_credit' => 'Transfer Credit Exam',
        'exemption' => 'Course Exemption Test',
    ];

    /**
     * Display entrance exam information page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function information(Request $request)
    {
        try {
            $examType = $request->input('type', 'entrance');

            // Get current and upcoming exams
            $currentExams = Cache::remember(
                "public_exams_current_{$examType}",
                self::CACHE_DURATION,
                function () use ($examType) {
                    return EntranceExam::where('exam_type', $examType)
                        ->whereIn('status', ['published', 'registration_open', 'registration_closed'])
                        ->where(function ($query) {
                            $query->where('exam_date', '>=', now())
                                  ->orWhere('exam_window_end', '>=', now());
                        })
                        ->orderBy('exam_date')
                        ->get();
                }
            );

            // Get exam information content
            $examInfo = Cache::remember(
                "public_exam_info_{$examType}",
                self::CACHE_DURATION * 24,
                function () use ($examType) {
                    return $this->getExamInformation($examType);
                }
            );

            // Get exam centers
            $examCenters = Cache::remember('public_exam_centers', self::CACHE_DURATION * 24, function () {
                return ExamCenter::where('is_active', true)
                    ->where('center_type', '!=', 'online')
                    ->orderBy('city')
                    ->get();
            });

            // Get FAQs specific to exams
            $examFAQs = Cache::remember(
                "public_exam_faqs_{$examType}",
                self::CACHE_DURATION * 24,
                function () use ($examType) {
                    return $this->getExamFAQs($examType);
                }
            );

            return view('exams.public.information', compact(
                'currentExams',
                'examInfo',
                'examCenters',
                'examFAQs',
                'examType'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load exam information', [
                'error' => $e->getMessage(),
            ]);

            return view('exams.public.information', [
                'error' => 'Unable to load exam information at this time.',
                'currentExams' => collect(),
            ]);
        }
    }

    /**
     * Download exam syllabus.
     *
     * @param int $examId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function syllabus($examId)
    {
        try {
            $exam = EntranceExam::findOrFail($examId);

            // Check if syllabus is available
            $syllabusPath = "exams/syllabus/{$exam->exam_code}_syllabus.pdf";
            
            if (!Storage::exists($syllabusPath)) {
                // Generate default syllabus if not exists
                $syllabusPath = $this->generateDefaultSyllabus($exam);
            }

            $fileName = "{$exam->exam_code}_Syllabus.pdf";
            
            // Log download
            $this->logSyllabusDownload($exam);

            return Storage::download($syllabusPath, $fileName);

        } catch (Exception $e) {
            Log::error('Failed to download syllabus', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Syllabus not available for download.');
        }
    }

    /**
     * Display sample papers and practice questions.
     *
     * @param int $examId
     * @return \Illuminate\View\View
     */
    public function samplePapers($examId)
    {
        try {
            $exam = EntranceExam::findOrFail($examId);

            // Get sample papers
            $samplePapers = Cache::remember(
                "public_sample_papers_{$examId}",
                self::CACHE_DURATION * 24,
                function () use ($exam) {
                    return $this->getSamplePapers($exam);
                }
            );

            // Get practice questions
            $practiceQuestions = Cache::remember(
                "public_practice_questions_{$examId}",
                self::CACHE_DURATION * 24,
                function () use ($exam) {
                    return $this->getPracticeQuestions($exam);
                }
            );

            // Get preparation tips
            $preparationTips = Cache::remember(
                "public_preparation_tips_{$exam->exam_type}",
                self::CACHE_DURATION * 24,
                function () use ($exam) {
                    return $this->getPreparationTips($exam->exam_type);
                }
            );

            // Get recommended study materials
            $studyMaterials = Cache::remember(
                "public_study_materials_{$exam->exam_type}",
                self::CACHE_DURATION * 24,
                function () use ($exam) {
                    return $this->getStudyMaterials($exam->exam_type);
                }
            );

            return view('exams.public.sample-papers', compact(
                'exam',
                'samplePapers',
                'practiceQuestions',
                'preparationTips',
                'studyMaterials'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load sample papers', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('exams.public.information')
                ->with('error', 'Sample papers not available.');
        }
    }

    /**
     * Display public exam results.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function results(Request $request)
    {
        try {
            $examId = $request->input('exam_id');
            $registrationNumber = $request->input('registration_number');
            $searchResult = null;

            // Get published exam results
            $publishedExams = EntranceExam::where('status', 'results_published')
                ->orderBy('result_publish_date', 'desc')
                ->limit(10)
                ->get();

            // Search for specific result if registration number provided
            if ($registrationNumber && $examId) {
                $searchResult = $this->searchResult($examId, $registrationNumber);
            }

            // Get selected exam results for display
            $examResults = null;
            $statistics = null;
            if ($examId) {
                $exam = EntranceExam::find($examId);
                if ($exam && $exam->status === 'results_published') {
                    // Get top performers only (for privacy)
                    $examResults = Cache::remember(
                        "public_exam_results_{$examId}",
                        self::CACHE_DURATION,
                        function () use ($examId) {
                            return EntranceExamResult::with(['registration:id,registration_number,candidate_name'])
                                ->where('exam_id', $examId)
                                ->where('is_published', true)
                                ->orderBy('overall_rank')
                                ->limit(self::PUBLIC_RESULTS_LIMIT)
                                ->get(['id', 'registration_id', 'overall_rank', 'final_score', 'percentile', 'result_status']);
                        }
                    );

                    // Get statistics
                    $statistics = Cache::remember(
                        "public_exam_statistics_{$examId}",
                        self::CACHE_DURATION,
                        function () use ($examId) {
                            return $this->getExamStatistics($examId);
                        }
                    );
                }
            }

            return view('exams.public.results', compact(
                'publishedExams',
                'examResults',
                'statistics',
                'searchResult',
                'examId',
                'registrationNumber'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load exam results', [
                'error' => $e->getMessage(),
            ]);

            return view('exams.public.results', [
                'error' => 'Unable to load exam results at this time.',
                'publishedExams' => collect(),
            ]);
        }
    }

    /**
     * Display exam statistics and analytics.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function statistics(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $examType = $request->input('type');

            // Get overall exam statistics
            $overallStats = Cache::remember(
                "public_exam_stats_{$year}_{$examType}",
                self::CACHE_DURATION,
                function () use ($year, $examType) {
                    return $this->getOverallStatistics($year, $examType);
                }
            );

            // Get performance trends
            $trends = Cache::remember(
                "public_exam_trends_{$examType}",
                self::CACHE_DURATION * 24,
                function () use ($examType) {
                    return $this->getPerformanceTrends($examType);
                }
            );

            // Get center-wise performance
            $centerPerformance = Cache::remember(
                "public_center_performance_{$year}",
                self::CACHE_DURATION,
                function () use ($year) {
                    return $this->getCenterWisePerformance($year);
                }
            );

            // Get subject-wise analysis
            $subjectAnalysis = Cache::remember(
                "public_subject_analysis_{$year}_{$examType}",
                self::CACHE_DURATION,
                function () use ($year, $examType) {
                    return $this->getSubjectWiseAnalysis($year, $examType);
                }
            );

            // Get available years for filter
            $availableYears = DB::table('entrance_exams')
                ->selectRaw('DISTINCT YEAR(exam_date) as year')
                ->whereNotNull('exam_date')
                ->orderBy('year', 'desc')
                ->pluck('year');

            return view('exams.public.statistics', compact(
                'overallStats',
                'trends',
                'centerPerformance',
                'subjectAnalysis',
                'year',
                'examType',
                'availableYears'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load exam statistics', [
                'error' => $e->getMessage(),
            ]);

            return view('exams.public.statistics', [
                'error' => 'Unable to load statistics at this time.',
                'overallStats' => [],
            ]);
        }
    }

    /**
     * Verify exam certificate.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function verifyCertificate(Request $request)
    {
        try {
            $verificationCode = $request->input('code');
            $certificateNumber = $request->input('certificate_number');
            $certificate = null;
            $isValid = false;

            if ($verificationCode || $certificateNumber) {
                // Search for certificate
                $query = ExamCertificate::with(['result.registration', 'result.exam']);
                
                if ($verificationCode) {
                    $query->where('verification_code', $verificationCode);
                } elseif ($certificateNumber) {
                    $query->where('certificate_number', $certificateNumber);
                }
                
                $certificate = $query->first();
                
                if ($certificate) {
                    $isValid = true;
                    
                    // Log verification attempt
                    $this->logCertificateVerification($certificate, true);
                } else {
                    // Log failed verification
                    $this->logCertificateVerification(null, false);
                }
            }

            return view('exams.public.verify-certificate', compact(
                'certificate',
                'isValid',
                'verificationCode',
                'certificateNumber'
            ));

        } catch (Exception $e) {
            Log::error('Failed to verify certificate', [
                'error' => $e->getMessage(),
            ]);

            return view('exams.public.verify-certificate', [
                'error' => 'Unable to verify certificate at this time.',
                'isValid' => false,
            ]);
        }
    }

    /**
     * Display answer keys for published exams.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function answerKeys(Request $request)
    {
        try {
            // Get exams with published answer keys
            $examsWithKeys = Cache::remember('public_exams_with_keys', self::CACHE_DURATION, function () {
                return EntranceExam::whereHas('answerKeys', function ($query) {
                    $query->where('is_published', true);
                })
                ->where('status', 'results_published')
                ->orderBy('exam_date', 'desc')
                ->get();
            });

            $selectedExam = null;
            $answerKey = null;
            $challenges = null;

            if ($request->has('exam_id')) {
                $selectedExam = EntranceExam::find($request->input('exam_id'));
                
                if ($selectedExam) {
                    // Get published answer key
                    $answerKey = ExamAnswerKey::where('exam_id', $selectedExam->id)
                        ->where('is_published', true)
                        ->where('key_type', 'final')
                        ->first();
                    
                    // Get challenge statistics
                    if ($answerKey) {
                        $challenges = DB::table('answer_key_challenges')
                            ->where('answer_key_id', $answerKey->id)
                            ->selectRaw('status, COUNT(*) as count')
                            ->groupBy('status')
                            ->pluck('count', 'status');
                    }
                }
            }

            return view('exams.public.answer-keys', compact(
                'examsWithKeys',
                'selectedExam',
                'answerKey',
                'challenges'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load answer keys', [
                'error' => $e->getMessage(),
            ]);

            return view('exams.public.answer-keys', [
                'error' => 'Unable to load answer keys at this time.',
                'examsWithKeys' => collect(),
            ]);
        }
    }

    /**
     * Display exam preparation resources.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function resources(Request $request)
    {
        try {
            $examType = $request->input('type', 'entrance');

            // Get preparation resources
            $resources = Cache::remember(
                "public_exam_resources_{$examType}",
                self::CACHE_DURATION * 24,
                function () use ($examType) {
                    return [
                        'study_guides' => $this->getStudyGuides($examType),
                        'video_tutorials' => $this->getVideoTutorials($examType),
                        'practice_tests' => $this->getPracticeTests($examType),
                        'recommended_books' => $this->getRecommendedBooks($examType),
                        'online_courses' => $this->getOnlineCourses($examType),
                        'tips_strategies' => $this->getTipsAndStrategies($examType),
                    ];
                }
            );

            // Get upcoming workshops/webinars
            $workshops = Cache::remember('public_exam_workshops', self::CACHE_DURATION, function () {
                return DB::table('exam_workshops')
                    ->where('date', '>=', now())
                    ->where('is_public', true)
                    ->orderBy('date')
                    ->limit(5)
                    ->get();
            });

            // Get success stories/testimonials
            $testimonials = Cache::remember('public_exam_testimonials', self::CACHE_DURATION * 24, function () {
                return DB::table('exam_testimonials')
                    ->where('is_published', true)
                    ->orderBy('created_at', 'desc')
                    ->limit(6)
                    ->get();
            });

            return view('exams.public.resources', compact(
                'resources',
                'workshops',
                'testimonials',
                'examType'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load exam resources', [
                'error' => $e->getMessage(),
            ]);

            return view('exams.public.resources', [
                'error' => 'Unable to load resources at this time.',
                'resources' => [],
            ]);
        }
    }

    /**
     * Display exam calendar.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function calendar(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', date('m'));

            // Get exam schedule for the period
            $examSchedule = Cache::remember(
                "public_exam_calendar_{$year}_{$month}",
                self::CACHE_DURATION,
                function () use ($year, $month) {
                    $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                    $endDate = Carbon::create($year, $month, 1)->endOfMonth();

                    return EntranceExam::whereBetween('exam_date', [$startDate, $endDate])
                        ->orWhere(function ($query) use ($startDate, $endDate) {
                            $query->whereBetween('exam_window_start', [$startDate, $endDate])
                                  ->orWhereBetween('exam_window_end', [$startDate, $endDate]);
                        })
                        ->where('status', '!=', 'cancelled')
                        ->orderBy('exam_date')
                        ->get();
                }
            );

            // Get important dates
            $importantDates = [
                'registration_deadlines' => $this->getRegistrationDeadlines($year, $month),
                'result_dates' => $this->getResultDates($year, $month),
                'hall_ticket_dates' => $this->getHallTicketDates($year, $month),
            ];

            // Format for calendar display
            $calendarEvents = $this->formatCalendarEvents($examSchedule, $importantDates);

            return view('exams.public.calendar', compact(
                'calendarEvents',
                'year',
                'month',
                'examSchedule',
                'importantDates'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load exam calendar', [
                'error' => $e->getMessage(),
            ]);

            return view('exams.public.calendar', [
                'error' => 'Unable to load calendar at this time.',
                'calendarEvents' => [],
            ]);
        }
    }

    /**
     * Helper: Get exam information content.
     */
    private function getExamInformation(string $examType): array
    {
        return [
            'overview' => $this->getExamOverview($examType),
            'eligibility' => $this->getEligibilityCriteria($examType),
            'pattern' => $this->getExamPattern($examType),
            'syllabus_outline' => $this->getSyllabusOutline($examType),
            'important_dates' => $this->getImportantDates($examType),
            'fee_structure' => $this->getFeeStructure($examType),
            'how_to_apply' => $this->getApplicationProcess($examType),
        ];
    }

    /**
     * Helper: Search for specific result.
     */
    private function searchResult($examId, $registrationNumber)
    {
        try {
            $registration = DB::table('entrance_exam_registrations')
                ->where('exam_id', $examId)
                ->where('registration_number', $registrationNumber)
                ->first();

            if (!$registration) {
                return null;
            }

            $result = EntranceExamResult::where('registration_id', $registration->id)
                ->where('is_published', true)
                ->first();

            if ($result) {
                return [
                    'registration_number' => $registrationNumber,
                    'candidate_name' => substr($registration->candidate_name, 0, 3) . '***',
                    'rank' => $result->overall_rank,
                    'score' => $result->final_score,
                    'percentile' => $result->percentile,
                    'status' => $result->result_status,
                    'qualified' => $result->is_qualified,
                ];
            }

            return null;
        } catch (Exception $e) {
            Log::error('Failed to search result', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Helper: Get exam statistics.
     */
    private function getExamStatistics($examId): array
    {
        $results = EntranceExamResult::where('exam_id', $examId)
            ->where('is_published', true);

        return [
            'total_appeared' => $results->count(),
            'total_qualified' => (clone $results)->where('is_qualified', true)->count(),
            'highest_score' => (clone $results)->max('final_score'),
            'average_score' => round((clone $results)->avg('final_score'), 2),
            'pass_percentage' => $this->calculatePassPercentage($results),
            'score_distribution' => $this->getScoreDistribution($results),
        ];
    }

    /**
     * Helper: Other helper methods...
     */
    private function generateDefaultSyllabus($exam): string
    {
        // Implementation details...
        return '';
    }

    private function logSyllabusDownload($exam): void
    {
        // Implementation details...
    }

    private function logCertificateVerification($certificate, $success): void
    {
        // Implementation details...
    }

    // Additional helper methods would follow...
}