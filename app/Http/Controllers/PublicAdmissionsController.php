<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Models\AdmissionSetting;
use App\Models\AdmissionApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class PublicAdmissionsController extends Controller
{
    /**
     * Cache duration in minutes
     */
    private const CACHE_DURATION = 60;

    /**
     * Application types
     */
    private const APPLICATION_TYPES = [
        'freshman' => 'First-Year Student',
        'transfer' => 'Transfer Student',
        'graduate' => 'Graduate Student',
        'international' => 'International Student',
        'readmission' => 'Returning Student',
        'non_degree' => 'Non-Degree Seeking',
        'exchange' => 'Exchange Student',
    ];

    /**
     * Display admission requirements page.
     */
    public function requirements(Request $request)
    {
        try {
            $programId = $request->input('program_id');
            $applicationType = $request->input('type', 'freshman');

            // Get general requirements
            $generalRequirements = Cache::remember(
                "admission_requirements_general_{$applicationType}",
                self::CACHE_DURATION,
                function () use ($applicationType) {
                    return $this->getGeneralRequirements($applicationType);
                }
            );

            // Get program-specific requirements if program selected
            $programRequirements = null;
            $program = null;
            if ($programId) {
                $program = AcademicProgram::find($programId);
                if ($program) {
                    $programRequirements = Cache::remember(
                        "admission_requirements_program_{$programId}_{$applicationType}",
                        self::CACHE_DURATION,
                        function () use ($program, $applicationType) {
                            return $this->getProgramRequirements($program, $applicationType);
                        }
                    );
                }
            }

            // Get all active programs for dropdown
            $programs = AcademicProgram::where('is_active', true)
                ->where('accepts_applications', true)
                ->orderBy('name')
                ->get();

            // Get current admission cycle
            $currentTerm = AcademicTerm::where('is_admission_open', true)
                ->where('admission_deadline', '>', now())
                ->first();

            return view('admissions.public.requirements', compact(
                'generalRequirements',
                'programRequirements',
                'programs',
                'program',
                'applicationType',
                'currentTerm'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load admission requirements', [
                'error' => $e->getMessage(),
            ]);

            return view('admissions.public.requirements', [
                'error' => 'Unable to load admission requirements at this time.',
            ]);
        }
    }

    /**
     * Display available programs page.
     */
    public function programs(Request $request)
    {
        try {
            $degreeLevel = $request->input('level');
            $departmentFilter = $request->input('department');
            $search = $request->input('search');

            // Build query without eager loading non-existent relationships
            $query = AcademicProgram::where('is_active', true)
                ->where('accepts_applications', true);

            // Filter by level (using the 'level' column which has values like 'bachelor')
            if ($degreeLevel) {
                $query->where('level', $degreeLevel);
            }

            // Filter by department (using the string 'department' field)
            if ($departmentFilter) {
                $query->where('department', $departmentFilter);
            }

            // Search functionality
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $programs = $query->orderBy('name')->paginate(12);

            // Get unique departments from academic_programs for filter
            $departments = DB::table('academic_programs')
                ->where('is_active', true)
                ->whereNotNull('department')
                ->distinct()
                ->orderBy('department')
                ->pluck('department', 'department');

            // Get real statistics from database
            $stats = Cache::remember('program_statistics', self::CACHE_DURATION, function () {
                $activePrograms = AcademicProgram::where('is_active', true);
                
                return [
                    'total_programs' => $activePrograms->count(),
                    'undergraduate' => (clone $activePrograms)->where('program_type', 'undergraduate')->count(),
                    'graduate' => (clone $activePrograms)->where('program_type', 'graduate')->count(),
                    'certificate' => (clone $activePrograms)->where('program_type', 'certificate')->count(),
                    'diploma' => (clone $activePrograms)->where('program_type', 'diploma')->count(),
                ];
            });

            return view('admissions.public.programs', compact(
                'programs',
                'departments',
                'degreeLevel',
                'departmentFilter',
                'search',
                'stats'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load programs', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('admissions.public.programs', [
                'error' => 'Unable to load programs at this time.',
                'programs' => collect(),
                'departments' => collect(),
                'stats' => [
                    'total_programs' => 0,
                    'undergraduate' => 0,
                    'graduate' => 0,
                    'certificate' => 0,
                    'diploma' => 0,
                ]
            ]);
        }
    }

    /**
     * Display admission calendar with important dates.
     */
    public function calendar(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));

            // Get admission-related dates
            $admissionDates = Cache::remember(
                "admission_calendar_{$year}",
                self::CACHE_DURATION,
                function () use ($year) {
                    return $this->getAdmissionDates($year);
                }
            );

            // Get terms for the year
            $terms = AcademicTerm::whereYear('start_date', $year)
                ->orWhereYear('end_date', $year)
                ->orderBy('start_date')
                ->get();

            // Get exam schedules if table exists
            $examSchedules = collect();
            if (Schema::hasTable('entrance_exams')) {
                $examSchedules = DB::table('entrance_exams')
                    ->whereYear('exam_date', $year)
                    ->where('status', '!=', 'cancelled')
                    ->orderBy('exam_date')
                    ->get();
            }

            // Organize dates by month
            $calendar = $this->organizeCalendarByMonth($admissionDates, $terms, $examSchedules);

            // Get available years
            $availableYears = range(date('Y') - 1, date('Y') + 2);

            return view('admissions.public.calendar', compact(
                'calendar',
                'year',
                'availableYears',
                'terms',
                'examSchedules'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load admission calendar', [
                'error' => $e->getMessage(),
            ]);

            return view('admissions.public.calendar', [
                'error' => 'Unable to load calendar at this time.',
                'calendar' => [],
                'year' => $year ?? date('Y'),
            ]);
        }
    }

    /**
     * Display frequently asked questions.
     */
    public function faq(Request $request)
    {
        try {
            $category = $request->input('category');
            $search = $request->input('search');

            // Get FAQs from cache or database
            $faqs = Cache::remember(
                "admission_faqs_{$category}_{$search}",
                self::CACHE_DURATION,
                function () use ($category, $search) {
                    return $this->getFAQs($category, $search);
                }
            );

            // Get FAQ categories
            $categories = [
                'general' => 'General Admissions',
                'requirements' => 'Admission Requirements',
                'application' => 'Application Process',
                'documents' => 'Required Documents',
                'international' => 'International Students',
                'transfer' => 'Transfer Students',
                'financial' => 'Fees & Financial Aid',
                'deadlines' => 'Deadlines & Dates',
                'exams' => 'Entrance Exams',
                'after_admission' => 'After Admission',
            ];

            // Get popular FAQs
            $popularFAQs = Cache::remember('popular_admission_faqs', self::CACHE_DURATION * 24, function () {
                return $this->getPopularFAQs();
            });

            return view('admissions.public.faq', compact(
                'faqs',
                'categories',
                'category',
                'search',
                'popularFAQs'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load FAQs', [
                'error' => $e->getMessage(),
            ]);

            return view('admissions.public.faq', [
                'error' => 'Unable to load FAQs at this time.',
                'faqs' => [],
                'categories' => [],
            ]);
        }
    }

    /**
     * Display contact information for admissions office.
     */
    public function contact(Request $request)
    {
        try {
            // Get admissions office contact information
            $contactInfo = Cache::remember('admission_contact_info', self::CACHE_DURATION * 24, function () {
                return [
                    'office_name' => config('university.admissions.office_name', 'Office of Admissions'),
                    'address' => config('university.admissions.address', '123 University Ave'),
                    'phone' => config('university.admissions.phone', '(555) 123-4567'),
                    'email' => config('university.admissions.email', 'admissions@intellicampus.edu'),
                    'fax' => config('university.admissions.fax'),
                    'hours' => config('university.admissions.hours', [
                        'monday_friday' => '8:00 AM - 5:00 PM',
                        'saturday' => '9:00 AM - 1:00 PM',
                        'sunday' => 'Closed',
                    ]),
                    'social_media' => [
                        'facebook' => config('university.admissions.facebook'),
                        'twitter' => config('university.admissions.twitter'),
                        'instagram' => config('university.admissions.instagram'),
                        'linkedin' => config('university.admissions.linkedin'),
                    ],
                ];
            });

            // Get key contacts with default values
            $keyContacts = Cache::remember('admission_key_contacts', self::CACHE_DURATION * 24, function () {
                return [
                    [
                        'title' => 'Director of Admissions',
                        'name' => config('university.admissions.director_name', 'Dr. Jane Smith'),
                        'email' => config('university.admissions.director_email', 'director@intellicampus.edu'),
                        'phone' => config('university.admissions.director_phone', '(555) 123-4568'),
                    ],
                    [
                        'title' => 'International Admissions',
                        'name' => config('university.admissions.international_name', 'Mr. John Doe'),
                        'email' => config('university.admissions.international_email', 'international@intellicampus.edu'),
                        'phone' => config('university.admissions.international_phone', '(555) 123-4569'),
                    ],
                    [
                        'title' => 'Graduate Admissions',
                        'name' => config('university.admissions.graduate_name', 'Dr. Mary Johnson'),
                        'email' => config('university.admissions.graduate_email', 'graduate@intellicampus.edu'),
                        'phone' => config('university.admissions.graduate_phone', '(555) 123-4570'),
                    ],
                    [
                        'title' => 'Transfer Credit Evaluation',
                        'name' => config('university.admissions.transfer_name', 'Ms. Sarah Williams'),
                        'email' => config('university.admissions.transfer_email', 'transfer@intellicampus.edu'),
                        'phone' => config('university.admissions.transfer_phone', '(555) 123-4571'),
                    ],
                ];
            });

            // Get regional representatives if table exists
            $regionalReps = collect();
            if (Schema::hasTable('admission_representatives')) {
                $regionalReps = Cache::remember('admission_regional_reps', self::CACHE_DURATION * 24, function () {
                    return DB::table('admission_representatives')
                        ->where('is_active', true)
                        ->orderBy('region')
                        ->get();
                });
            }

            return view('admissions.public.contact', compact(
                'contactInfo',
                'keyContacts',
                'regionalReps'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load contact information', [
                'error' => $e->getMessage(),
            ]);

            return view('admissions.public.contact', [
                'error' => 'Unable to load contact information at this time.',
                'contactInfo' => [],
                'keyContacts' => [],
                'regionalReps' => collect(),
            ]);
        }
    }

    /**
     * Display admission statistics and facts.
     */
    public function statistics(Request $request)
    {
        try {
            $year = $request->input('year', date('Y'));

            // Get admission statistics
            $stats = Cache::remember("admission_statistics_{$year}", self::CACHE_DURATION, function () use ($year) {
                // Check if we have any terms for the year
                $termIds = AcademicTerm::whereYear('start_date', $year)->pluck('id');
                
                if ($termIds->isEmpty()) {
                    // Return empty stats if no terms found
                    return [
                        'total_applications' => 0,
                        'total_admitted' => 0,
                        'total_enrolled' => 0,
                        'acceptance_rate' => 0,
                        'yield_rate' => 0,
                        'average_gpa' => 0,
                        'by_type' => [],
                        'by_program' => [],
                        'geographic_distribution' => [],
                    ];
                }
                
                // Get applications for the year
                $applications = AdmissionApplication::whereIn('term_id', $termIds)
                    ->where('status', '!=', 'draft');

                return [
                    'total_applications' => $applications->count(),
                    'total_admitted' => (clone $applications)->where('decision', 'admit')->count(),
                    'total_enrolled' => (clone $applications)->where('enrollment_confirmed', true)->count(),
                    'acceptance_rate' => $this->calculateAcceptanceRate($applications),
                    'yield_rate' => $this->calculateYieldRate($applications),
                    'average_gpa' => (clone $applications)->where('decision', 'admit')->avg('previous_gpa') ?? 0,
                    'by_type' => $this->getStatsByType($applications),
                    'by_program' => $this->getStatsByProgram($applications),
                    'geographic_distribution' => $this->getGeographicDistribution($applications),
                ];
            });

            // Get historical trends
            $trends = Cache::remember('admission_trends', self::CACHE_DURATION * 24, function () {
                return $this->getHistoricalTrends();
            });

            // Get diversity statistics
            $diversity = Cache::remember("admission_diversity_{$year}", self::CACHE_DURATION, function () use ($year) {
                return $this->getDiversityStatistics($year);
            });

            return view('admissions.public.statistics', compact(
                'stats',
                'trends',
                'diversity',
                'year'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load admission statistics', [
                'error' => $e->getMessage(),
            ]);

            return view('admissions.public.statistics', [
                'error' => 'Unable to load statistics at this time.',
                'stats' => [],
                'trends' => [],
                'diversity' => [],
                'year' => $year ?? date('Y'),
            ]);
        }
    }

    /**
     * Display virtual tour and campus information.
     */
    public function virtualTour()
    {
        try {
            // Get campus facilities
            $facilities = Cache::remember('campus_facilities', self::CACHE_DURATION * 24, function () {
                return [
                    'academic' => $this->getAcademicFacilities(),
                    'residential' => $this->getResidentialFacilities(),
                    'recreational' => $this->getRecreationalFacilities(),
                    'dining' => $this->getDiningFacilities(),
                    'support' => $this->getSupportFacilities(),
                ];
            });

            // Get virtual tour links/media
            $tourMedia = [
                'video_url' => config('university.virtual_tour.video_url'),
                '360_tour_url' => config('university.virtual_tour.360_url'),
                'photo_gallery' => Storage::exists('public/campus-photos') ? Storage::files('public/campus-photos') : [],
                'campus_map' => config('university.virtual_tour.map_url'),
            ];

            return view('admissions.public.virtual-tour', compact(
                'facilities',
                'tourMedia'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load virtual tour', [
                'error' => $e->getMessage(),
            ]);

            return view('admissions.public.virtual-tour', [
                'error' => 'Unable to load virtual tour at this time.',
            ]);
        }
    }

    public function index()
    {
        $currentTerm = \App\Models\AcademicTerm::where('is_admission_open', true)->first();
        
        $stats = [
            'total_programs' => \App\Models\AcademicProgram::where('is_active', true)->count(),
            'undergraduate' => \App\Models\AcademicProgram::where('program_type', 'undergraduate')->count(),
            'graduate' => \App\Models\AcademicProgram::where('program_type', 'graduate')->count(),
            'acceptance_rate' => 65,
        ];

        $programs = \App\Models\AcademicProgram::where('is_active', true)
            ->where('accepts_applications', true)
            ->limit(6)
            ->get();

        return view('admissions.public.index', compact('stats', 'programs', 'currentTerm'));
    }

    /**
     * Helper: Get general admission requirements.
     */
    private function getGeneralRequirements(string $applicationType): array
    {
        $requirements = [
            'freshman' => [
                'education' => [
                    'High School Diploma or equivalent',
                    'Minimum GPA of 2.5 on a 4.0 scale',
                    'Completion of college preparatory curriculum',
                ],
                'test_scores' => [
                    'SAT (minimum 1000) or ACT (minimum 21)',
                    'TOEFL (minimum 80) or IELTS (minimum 6.5) for international students',
                ],
                'documents' => [
                    'Official high school transcript',
                    'Personal statement/essay (500-650 words)',
                    'Two letters of recommendation',
                    'Application fee payment',
                ],
            ],
            'transfer' => [
                'education' => [
                    'Minimum 24 transferable credit hours',
                    'Minimum GPA of 2.5 from previous institution',
                    'Good standing at previous institution',
                ],
                'documents' => [
                    'Official transcripts from all colleges attended',
                    'Course descriptions for transfer credit evaluation',
                    'Personal statement',
                    'One letter of recommendation',
                ],
            ],
            'graduate' => [
                'education' => [
                    "Bachelor's degree from accredited institution",
                    'Minimum GPA of 3.0 on a 4.0 scale',
                    'Prerequisite courses as required by program',
                ],
                'test_scores' => [
                    'GRE General Test (varies by program)',
                    'GMAT for business programs',
                    'TOEFL (minimum 90) or IELTS (minimum 7.0) for international students',
                ],
                'documents' => [
                    'Official transcripts from all institutions',
                    'Statement of purpose',
                    'Three letters of recommendation',
                    'Resume/CV',
                    'Writing sample (if required by program)',
                ],
            ],
            'international' => [
                'education' => [
                    'Equivalent of U.S. high school diploma or bachelor\'s degree',
                    'Credential evaluation from approved agency',
                    'Meet program-specific requirements',
                ],
                'test_scores' => [
                    'TOEFL (minimum 80) or IELTS (minimum 6.5)',
                    'SAT/ACT for undergraduate',
                    'GRE/GMAT for graduate programs',
                ],
                'documents' => [
                    'Translated and certified academic records',
                    'Passport copy',
                    'Financial support documentation',
                    'Visa documentation support',
                    'All standard application documents',
                ],
            ],
        ];

        return $requirements[$applicationType] ?? $requirements['freshman'];
    }

    /**
     * Helper: Get program-specific requirements.
     */
    private function getProgramRequirements(AcademicProgram $program, string $applicationType): array
    {
        return [
            'additional_requirements' => $program->admission_requirements ?? [],
            'minimum_gpa' => $program->min_gpa ?? 2.5,
            'total_credits' => $program->total_credits ?? 120,
            'duration_years' => $program->duration_years ?? 4,
            'application_fee' => $program->application_fee ?? 50,
        ];
    }

    /**
     * Helper: Get admission-related dates.
     */
    private function getAdmissionDates(int $year): array
    {
        $dates = [];
        
        // Check if admission_settings table exists
        if (!Schema::hasTable('admission_settings')) {
            return $dates;
        }
        
        // Get from admission settings
        $settings = AdmissionSetting::whereYear('application_open_date', $year)
            ->orWhereYear('application_close_date', $year)
            ->get();

        foreach ($settings as $setting) {
            if ($setting->application_open_date) {
                $dates[] = [
                    'date' => $setting->application_open_date,
                    'title' => 'Applications Open',
                    'type' => 'application',
                    'description' => $setting->term->name ?? 'Admission cycle opens',
                ];
            }
            
            if ($setting->application_close_date) {
                $dates[] = [
                    'date' => $setting->application_close_date,
                    'title' => 'Application Deadline',
                    'type' => 'deadline',
                    'description' => $setting->term->name ?? 'Final deadline for applications',
                ];
            }
            
            if ($setting->decision_release_date) {
                $dates[] = [
                    'date' => $setting->decision_release_date,
                    'title' => 'Decision Release',
                    'type' => 'decision',
                    'description' => 'Admission decisions will be released',
                ];
            }
            
            if ($setting->enrollment_deadline) {
                $dates[] = [
                    'date' => $setting->enrollment_deadline,
                    'title' => 'Enrollment Deadline',
                    'type' => 'enrollment',
                    'description' => 'Deadline to confirm enrollment',
                ];
            }
        }

        return $dates;
    }

    /**
     * Helper: Organize calendar by month.
     */
    private function organizeCalendarByMonth($admissionDates, $terms, $examSchedules): array
    {
        $calendar = [];
        
        // Group dates by month
        foreach ($admissionDates as $date) {
            $month = Carbon::parse($date['date'])->format('F Y');
            $calendar[$month][] = $date;
        }
        
        // Add term dates
        foreach ($terms as $term) {
            if ($term->start_date) {
                $month = Carbon::parse($term->start_date)->format('F Y');
                $calendar[$month][] = [
                    'date' => $term->start_date,
                    'title' => $term->name . ' Begins',
                    'type' => 'term',
                    'description' => 'Academic term starts',
                ];
            }
        }
        
        // Add exam dates
        foreach ($examSchedules as $exam) {
            if ($exam->exam_date) {
                $month = Carbon::parse($exam->exam_date)->format('F Y');
                $calendar[$month][] = [
                    'date' => $exam->exam_date,
                    'title' => $exam->exam_name ?? 'Entrance Exam',
                    'type' => 'exam',
                    'description' => 'Entrance examination',
                ];
            }
        }
        
        // Sort by date within each month
        foreach ($calendar as $month => $events) {
            usort($events, function ($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });
            $calendar[$month] = $events;
        }
        
        return $calendar;
    }

    /**
     * Helper: Get FAQs.
     */
    private function getFAQs($category, $search): array
    {
        // This would normally fetch from database
        // For now, return sample FAQs
        $faqs = [
            [
                'question' => 'What are the application deadlines?',
                'answer' => 'Application deadlines vary by program and term. Generally, Fall applications are due by March 15, and Spring applications by October 15.',
                'category' => 'deadlines',
            ],
            [
                'question' => 'What documents are required for admission?',
                'answer' => 'Required documents include official transcripts, test scores (SAT/ACT for undergraduate, GRE/GMAT for graduate), letters of recommendation, and a personal statement.',
                'category' => 'documents',
            ],
            [
                'question' => 'What is the minimum GPA requirement?',
                'answer' => 'The minimum GPA requirement varies by program. Generally, undergraduate programs require a 2.5 GPA, while graduate programs require a 3.0 GPA.',
                'category' => 'requirements',
            ],
        ];
        
        // Filter by category if provided
        if ($category) {
            $faqs = array_filter($faqs, function ($faq) use ($category) {
                return $faq['category'] === $category;
            });
        }
        
        // Filter by search if provided
        if ($search) {
            $faqs = array_filter($faqs, function ($faq) use ($search) {
                return stripos($faq['question'], $search) !== false || 
                       stripos($faq['answer'], $search) !== false;
            });
        }
        
        return array_values($faqs);
    }

    /**
     * Helper: Get popular FAQs.
     */
    private function getPopularFAQs(): array
    {
        return [
            [
                'question' => 'How do I apply?',
                'answer' => 'You can apply online through our admissions portal. Create an account, complete the application form, upload required documents, and submit your application fee.',
            ],
            [
                'question' => 'What is the application fee?',
                'answer' => 'The application fee is $50 for undergraduate programs and $75 for graduate programs. Fee waivers are available for eligible students.',
            ],
            [
                'question' => 'When will I receive an admission decision?',
                'answer' => 'Admission decisions are typically released 4-6 weeks after the application deadline. You will receive an email notification when your decision is available.',
            ],
        ];
    }

    /**
     * Helper: Calculate acceptance rate.
     */
    private function calculateAcceptanceRate($applications): float
    {
        $total = $applications->count();
        if ($total === 0) return 0;
        
        $admitted = (clone $applications)->where('decision', 'admit')->count();
        return round(($admitted / $total) * 100, 1);
    }

    /**
     * Helper: Calculate yield rate.
     */
    private function calculateYieldRate($applications): float
    {
        $admitted = (clone $applications)->where('decision', 'admit')->count();
        if ($admitted === 0) return 0;
        
        $enrolled = (clone $applications)->where('enrollment_confirmed', true)->count();
        return round(($enrolled / $admitted) * 100, 1);
    }

    /**
     * Helper: Get stats by application type.
     */
    private function getStatsByType($applications): array
    {
        $stats = [];
        foreach (self::APPLICATION_TYPES as $type => $label) {
            $typeApplications = (clone $applications)->where('application_type', $type);
            $stats[$type] = [
                'label' => $label,
                'count' => $typeApplications->count(),
                'admitted' => $typeApplications->where('decision', 'admit')->count(),
            ];
        }
        return $stats;
    }

    /**
     * Helper: Get stats by program.
     */
    private function getStatsByProgram($applications): array
    {
        $programStats = [];
        $programs = AcademicProgram::where('is_active', true)->get();
        
        foreach ($programs as $program) {
            $programApps = (clone $applications)->where('program_id', $program->id);
            if ($programApps->count() > 0) {
                $programStats[$program->code] = [
                    'name' => $program->name,
                    'applications' => $programApps->count(),
                    'admitted' => $programApps->where('decision', 'admit')->count(),
                    'enrolled' => $programApps->where('enrollment_confirmed', true)->count(),
                ];
            }
        }
        
        return $programStats;
    }

    /**
     * Helper: Get geographic distribution.
     */
    private function getGeographicDistribution($applications): array
    {
        return $applications->groupBy('country')
            ->map(function ($group) {
                return $group->count();
            })
            ->toArray();
    }

    /**
     * Helper: Get historical trends.
     */
    private function getHistoricalTrends(): array
    {
        $trends = [];
        $currentYear = date('Y');
        
        for ($year = $currentYear - 4; $year <= $currentYear; $year++) {
            $termIds = AcademicTerm::whereYear('start_date', $year)->pluck('id');
            if (!$termIds->isEmpty()) {
                $yearApps = AdmissionApplication::whereIn('term_id', $termIds)
                    ->where('status', '!=', 'draft');
                    
                $trends[$year] = [
                    'applications' => $yearApps->count(),
                    'admitted' => (clone $yearApps)->where('decision', 'admit')->count(),
                    'enrolled' => (clone $yearApps)->where('enrollment_confirmed', true)->count(),
                ];
            }
        }
        
        return $trends;
    }

    /**
     * Helper: Get diversity statistics.
     */
    private function getDiversityStatistics($year): array
    {
        $termIds = AcademicTerm::whereYear('start_date', $year)->pluck('id');
        if ($termIds->isEmpty()) {
            return [];
        }
        
        $applications = AdmissionApplication::whereIn('term_id', $termIds)
            ->where('status', '!=', 'draft')
            ->where('decision', 'admit');
        
        return [
            'gender' => $applications->groupBy('gender')->map->count()->toArray(),
            'nationality' => $applications->groupBy('nationality')->map->count()->take(10)->toArray(),
            'age_groups' => $this->calculateAgeGroups($applications),
        ];
    }

    /**
     * Helper: Calculate age groups.
     */
    private function calculateAgeGroups($applications): array
    {
        $groups = [
            'under_18' => 0,
            '18_22' => 0,
            '23_30' => 0,
            '31_40' => 0,
            'over_40' => 0,
        ];
        
        foreach ($applications->get() as $app) {
            if ($app->date_of_birth) {
                $age = Carbon::parse($app->date_of_birth)->age;
                if ($age < 18) $groups['under_18']++;
                elseif ($age <= 22) $groups['18_22']++;
                elseif ($age <= 30) $groups['23_30']++;
                elseif ($age <= 40) $groups['31_40']++;
                else $groups['over_40']++;
            }
        }
        
        return $groups;
    }

    /**
     * Helper: Get academic facilities.
     */
    private function getAcademicFacilities(): array
    {
        return [
            'Libraries' => '3 modern libraries with over 500,000 volumes',
            'Laboratories' => '50+ state-of-the-art research and teaching labs',
            'Lecture Halls' => '30 smart classrooms with multimedia capabilities',
            'Study Spaces' => '200+ individual and group study rooms',
        ];
    }

    /**
     * Helper: Get residential facilities.
     */
    private function getResidentialFacilities(): array
    {
        return [
            'Residence Halls' => '10 on-campus residence halls',
            'Dining Halls' => '5 dining facilities with diverse menu options',
            'Student Lounges' => 'Common areas in each residence hall',
            'Laundry Facilities' => '24/7 laundry rooms in each hall',
        ];
    }

    /**
     * Helper: Get recreational facilities.
     */
    private function getRecreationalFacilities(): array
    {
        return [
            'Fitness Center' => 'Full-service gym with modern equipment',
            'Swimming Pool' => 'Olympic-size pool and diving facilities',
            'Sports Fields' => 'Soccer, football, and baseball fields',
            'Tennis Courts' => '8 outdoor and 4 indoor courts',
        ];
    }

    /**
     * Helper: Get dining facilities.
     */
    private function getDiningFacilities(): array
    {
        return [
            'Main Cafeteria' => 'All-you-can-eat dining with multiple stations',
            'Food Court' => 'Various fast-food and international options',
            'Coffee Shops' => '5 campus coffee shops and cafes',
            'Convenience Stores' => '24/7 stores for snacks and essentials',
        ];
    }

    /**
     * Helper: Get support facilities.
     */
    private function getSupportFacilities(): array
    {
        return [
            'Health Center' => 'Full-service medical and counseling services',
            'Career Center' => 'Job placement and career counseling',
            'Writing Center' => 'Academic writing support and tutoring',
            'IT Help Desk' => '24/7 technical support for students',
        ];
    }
}