<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdmissionApplication;
use App\Models\ApplicationDocument;
use App\Models\ApplicationChecklistItem;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Models\ProgramRequirement;
use App\Services\ApplicationService;
use App\Services\DocumentVerificationService;
use App\Services\ApplicationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDF;
use Exception;

class ApplicationFormController extends Controller
{
    protected $applicationService;
    protected $documentService;
    protected $notificationService;

    /**
     * Application type requirements configuration
     */
    private const TYPE_REQUIREMENTS = [
        'freshman' => [
            'sections' => ['academic', 'test-scores', 'essays', 'activities', 'documents', 'recommendations'],
            'required_tests' => ['WASSCE'], // Only WASSCE for local freshmen
            'required_documents' => ['high_school_transcript', 'recommendation_letter'],
            'min_recommendations' => 2,
            'essay_prompts' => ['personal_statement'],
        ],
        'transfer' => [
            'sections' => ['academic', 'college-courses', 'essays', 'documents', 'recommendations'],
            'required_tests' => [], // No tests required for transfers
            'required_documents' => ['college_transcript', 'high_school_transcript'],
            'min_recommendations' => 1,
            'essay_prompts' => ['transfer_essay', 'personal_statement'],
        ],
        'graduate' => [
            'sections' => ['academic', 'research', 'test-scores', 'essays', 'documents', 'recommendations', 'publications'],
            'required_tests' => ['GRE'], // Only GRE, not GMAT for all
            'required_documents' => ['undergraduate_transcript', 'degree_certificate', 'resume', 'recommendation_letter'],
            'min_recommendations' => 3,
            'essay_prompts' => ['statement_of_purpose', 'research_interests'],
        ],
        'international' => [
            'sections' => ['academic', 'test-scores', 'english-proficiency', 'essays', 'documents', 'recommendations', 'financial'],
            'required_tests' => ['SAT', 'ACT', 'TOEFL'], // SAT OR ACT plus TOEFL
            'required_documents' => ['transcript', 'passport', 'financial_statement', 'recommendation_letter'],
            'min_recommendations' => 2,
            'essay_prompts' => ['personal_statement', 'why_international'],
        ],
        'readmission' => [
            'sections' => ['previous-enrollment', 'reason-for-leaving', 'essays', 'documents'],
            'required_tests' => [],
            'required_documents' => ['transcript', 'readmission_form'],
            'min_recommendations' => 1,
            'essay_prompts' => ['readmission_statement'],
        ],
    ];

    /**
     * Program-specific additional requirements
     */
    private const PROGRAM_SPECIFIC_REQUIREMENTS = [
        'medicine' => ['mcat_scores', 'clinical_experience', 'medical_ethics_essay'],
        'law' => ['lsat_scores', 'writing_sample', 'legal_interest_essay'],
        'business' => ['gmat_scores', 'work_experience', 'leadership_essay'],
        'engineering' => ['math_placement', 'technical_project', 'engineering_essay'],
        'arts' => ['portfolio', 'artistic_statement', 'creative_samples'],
        'music' => ['audition_video', 'repertoire_list', 'music_theory_test'],
        'architecture' => ['design_portfolio', 'spatial_test', 'creative_essay'],
    ];

    public function __construct(
        ApplicationService $applicationService = null,
        DocumentVerificationService $documentService = null,
        ApplicationNotificationService $notificationService = null
    ) {
        $this->applicationService = $applicationService ?: new ApplicationService();
        $this->documentService = $documentService ?: new DocumentVerificationService();
        $this->notificationService = $notificationService ?: new ApplicationNotificationService();
    }

    /**
     * Main entry point - determines which form to show based on application type and program
     */
    public function showApplicationForm($uuid, $section = null)
    {
        $application = $this->getApplicationByUuid($uuid);
        
        // Ensure Phase 1 is complete
        if (empty($application->first_name) || empty($application->email) || empty($application->program_id)) {
            return redirect()->route('admissions.portal.start')
                ->with('error', 'Please complete basic information first.');
        }
        
        // Determine application requirements based on type and program
        $requirements = $this->determineRequirements($application);
        
        // Determine which view to use
        $viewName = $this->determineView($application);
        
        // Get program-specific data
        $programRequirements = $this->getProgramSpecificRequirements($application->program_id);
        
        // Set default section if not specified
        if (!$section) {
            $section = $requirements['sections'][0] ?? 'academic';
        }
        
        // Get additional data based on application type
        $additionalData = $this->getAdditionalDataForType($application);
        
        return view($viewName, array_merge([
            'application' => $application,
            'requirements' => $requirements,
            'programRequirements' => $programRequirements,
            'currentSection' => $section,
        ], $additionalData));
    }

    /**
     * Personal info route - redirect to consolidated form
     * This handles the redirect from start application
     */
    public function personalInfo($uuid)
    {
        return $this->showApplicationForm($uuid, 'academic');
    }
    
    /**
     * Contact info route - redirect to consolidated form
     */
    public function contactInfo($uuid)
    {
        return $this->showApplicationForm($uuid, 'academic');
    }
    
    /**
     * All section routes redirect to main form with dynamic requirements
     */
    public function academicInfo($uuid)
    {
        return $this->showApplicationForm($uuid, 'academic');
    }
    
    public function testScores($uuid)
    {
        return $this->showApplicationForm($uuid, 'test-scores');
    }
    
    public function essays($uuid)
    {
        return $this->showApplicationForm($uuid, 'essays');
    }
    
    public function documents($uuid)
    {
        return $this->showApplicationForm($uuid, 'documents');
    }
    
    public function recommendations($uuid)
    {
        return $this->showApplicationForm($uuid, 'recommendations');
    }

    /**
     * Determine requirements based on application type and program
     */
    private function determineRequirements($application)
    {
        // Get base requirements for application type
        $baseRequirements = self::TYPE_REQUIREMENTS[$application->application_type] ?? self::TYPE_REQUIREMENTS['freshman'];
        
        // Get program-specific additions
        $program = AcademicProgram::find($application->program_id);
        if ($program) {
            // Check if program has specific requirement category
            $programCategory = $this->getProgramCategory($program);
            if (isset(self::PROGRAM_SPECIFIC_REQUIREMENTS[$programCategory])) {
                $baseRequirements['additional_requirements'] = self::PROGRAM_SPECIFIC_REQUIREMENTS[$programCategory];
            }
            
            // Check for custom program requirements from database
            $customRequirements = ProgramRequirement::where('program_id', $program->id)
                ->where('is_active', true)
                ->get();
            
            if ($customRequirements->isNotEmpty()) {
                $baseRequirements['custom_requirements'] = $customRequirements;
            }
        }
        
        // Adjust for special cases
        $baseRequirements = $this->adjustRequirementsForSpecialCases($application, $baseRequirements);
        
        return $baseRequirements;
    }

    /**
     * Determine which view template to use
     */
    private function determineView($application)
    {
        // Check for type-specific views
        $typeSpecificView = "admissions.portal.forms.{$application->application_type}-application";
        if (view()->exists($typeSpecificView)) {
            return $typeSpecificView;
        }
        
        // Check for program-specific views
        $program = AcademicProgram::find($application->program_id);
        if ($program) {
            $programSlug = Str::slug($program->name);
            $programSpecificView = "admissions.portal.forms.programs.{$programSlug}";
            if (view()->exists($programSpecificView)) {
                return $programSpecificView;
            }
        }
        
        // Default consolidated view
        return 'admissions.portal.forms.consolidated-application';
    }

    /**
     * Get program category for requirement mapping
     */
    private function getProgramCategory($program)
    {
        $programName = strtolower($program->name);
        
        // Map program names to categories
        $categoryMappings = [
            'medicine' => ['medicine', 'medical', 'mbbs', 'md', 'pre-med'],
            'law' => ['law', 'legal', 'llb', 'jd', 'juris'],
            'business' => ['business', 'mba', 'commerce', 'accounting', 'finance'],
            'engineering' => ['engineering', 'computer science', 'it', 'technology'],
            'arts' => ['arts', 'fine arts', 'visual arts', 'design'],
            'music' => ['music', 'performance', 'composition'],
            'architecture' => ['architecture', 'urban planning', 'landscape'],
        ];
        
        foreach ($categoryMappings as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($programName, $keyword)) {
                    return $category;
                }
            }
        }
        
        return 'general';
    }

    /**
     * Get additional data based on application type
     */
    private function getAdditionalDataForType($application)
    {
        $data = [];
        
        switch ($application->application_type) {
            case 'transfer':
                // Get list of colleges for transfer students
                $data['colleges'] = DB::table('colleges')->orderBy('name')->get();
                $data['creditSystems'] = ['semester', 'quarter', 'trimester'];
                break;
                
            case 'graduate':
                // Get research areas for graduate students
                $data['researchAreas'] = DB::table('research_areas')
                    ->where('program_id', $application->program_id)
                    ->get();
                $data['facultyAdvisors'] = DB::table('users')
                    ->join('faculty_profiles', 'users.id', '=', 'faculty_profiles.user_id')
                    ->where('faculty_profiles.department_id', $application->program->department_id ?? null)
                    ->where('faculty_profiles.accepts_graduate_students', true)
                    ->select('users.id', 'users.name', 'faculty_profiles.research_interests')
                    ->get();
                break;
                
            case 'international':
                // Get country-specific requirements
                $data['countries'] = DB::table('countries')->orderBy('name')->get();
                $data['visaTypes'] = ['F-1', 'J-1', 'M-1'];
                $data['sponsorTypes'] = ['self', 'family', 'government', 'organization'];
                break;
        }
        
        return $data;
    }

    /**
     * Submit the application
     */
    public function submit(Request $request, $uuid)
    {
        try {
            $application = $this->getApplicationByUuid($uuid);
            
            // Check if already submitted - provide better feedback
            if ($application->status !== 'draft') {
                // If already submitted, redirect to confirmation page
                return response()->json([
                    'success' => true,
                    'already_submitted' => true,
                    'message' => 'Your application has already been submitted successfully.',
                    'submitted_at' => $application->submitted_at ? $application->submitted_at->format('F d, Y at g:i A') : 'Previously',
                    'redirect' => route('admissions.portal.application.confirmation', ['uuid' => $uuid])
                ]);
            }
            
            // Validate that all required sections are complete
            $requirements = $this->determineRequirements($application);
            $completion = $this->calculateCompletion($application, $requirements);
            
            // Get detailed incompletion info
            $incompleteSections = [];
            $incompleteDetails = [];
            
            foreach ($requirements['sections'] as $section) {
                if (!$this->isSectionComplete($application, $section, $requirements)) {
                    $incompleteSections[] = $section;
                    
                    // Get specific missing items for each section
                    switch($section) {
                        case 'academic':
                            if (empty($application->previous_institution)) {
                                $incompleteDetails[$section][] = 'Previous institution is required';
                            }
                            if (empty($application->previous_gpa)) {
                                $incompleteDetails[$section][] = 'GPA is required';
                            }
                            if ($application->application_type === 'freshman' && empty($application->high_school_name)) {
                                $incompleteDetails[$section][] = 'High school name is required';
                            }
                            break;
                            
                        case 'test-scores':
                            if (!empty($requirements['required_tests'])) {
                                $scores = $application->test_scores ?? [];
                                foreach ($requirements['required_tests'] as $test) {
                                    if (!isset($scores[$test])) {
                                        $incompleteDetails[$section][] = "$test scores are required";
                                    }
                                }
                            }
                            break;
                            
                        case 'essays':
                            foreach ($requirements['essay_prompts'] ?? [] as $prompt) {
                                if (empty($application->$prompt)) {
                                    $incompleteDetails[$section][] = ucwords(str_replace('_', ' ', $prompt)) . ' is required';
                                }
                            }
                            break;
                            
                        case 'activities':
                            $activities = $application->extracurricular_activities ?? [];
                            if (empty($activities)) {
                                $incompleteDetails[$section][] = 'At least one activity is required';
                            }
                            break;
                            
                        case 'recommendations':
                            $minRequired = $requirements['min_recommendations'] ?? 2;
                            $references = $application->references ?? [];
                            if (count($references) < $minRequired) {
                                $incompleteDetails[$section][] = "Minimum $minRequired recommendations required (currently have " . count($references) . ")";
                            }
                            break;
                    }
                }
            }
            
            if ($completion['percentage'] < 100 || !empty($incompleteSections)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please complete all required sections before submitting',
                    'incomplete_sections' => $incompleteSections,
                    'details' => $incompleteDetails,
                    'completion' => $completion
                ], 400);
            }
            
            // Update application status
            $application->status = 'submitted';
            $application->submitted_at = now();
            $application->save();
            
            // Send confirmation email if service is available
            if ($this->notificationService) {
                try {
                    $this->notificationService->sendSubmissionConfirmation($application);
                } catch (Exception $e) {
                    Log::warning('Could not send submission email', ['error' => $e->getMessage()]);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully',
                'redirect' => route('admissions.portal.application.confirmation', ['uuid' => $uuid])
            ]);
            
        } catch (Exception $e) {
            Log::error('Application submission failed', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit application. Please try again.'
            ], 500);
        }
    }

    /**
     * Get review summary for AJAX request
     */
    public function getReviewSummary(Request $request, $uuid)
    {
        try {
            $application = $this->getApplicationByUuid($uuid);
            $requirements = $this->determineRequirements($application);
            $completion = $this->calculateCompletion($application, $requirements);
            
            // Create summary data instead of calling non-existent method
            $summary = [
                'application_type' => $application->application_type,
                'program' => $application->program ? $application->program->name : 'Not specified',
                'term' => $application->term ? $application->term->name : 'Not specified',
                'name' => $application->first_name . ' ' . $application->last_name,
                'email' => $application->email,
                'status' => $application->status,
                'sections_completed' => $completion['completed'] . ' of ' . $completion['total'],
                'completion_percentage' => $completion['percentage']
            ];
            
            return response()->json([
                'success' => true,
                'completion' => $completion,
                'summary' => $summary
            ]);
        } catch (Exception $e) {
            Log::error('Error getting review summary', [
                'uuid' => $uuid,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Could not load application summary'
            ], 500);
        }
    }

    /**
     * Show confirmation page after submission
     */
    public function confirmation($uuid)
    {
        $application = $this->getApplicationByUuid($uuid);
        
        if ($application->status === 'draft') {
            return redirect()->route('admissions.portal.application.academic', ['uuid' => $uuid])
                ->with('warning', 'Application has not been submitted yet.');
        }
        
        return view('admissions.portal.forms.confirmation', compact('application'));
    }

    /**
     * Preview application (read-only view)
     */
    public function preview($uuid)
    {
        $application = $this->getApplicationByUuid($uuid);
        $requirements = $this->determineRequirements($application);
        
        return view('admissions.portal.forms.preview', compact('application', 'requirements'));
    }

    /**
     * Adjust requirements for special cases
     */
    private function adjustRequirementsForSpecialCases($application, $requirements)
    {
        // Remove test scores for certain cases
        if ($application->application_type === 'transfer' && $application->previous_gpa >= 3.5) {
            $requirements['required_tests'] = []; // Waive test requirements for high GPA transfers
        }
        
        // Add interview for competitive programs
        $competitivePrograms = ['medicine', 'law', 'mba'];
        $program = AcademicProgram::find($application->program_id);
        if ($program && in_array($this->getProgramCategory($program), $competitivePrograms)) {
            if (!in_array('interview', $requirements['sections'])) {
                $requirements['sections'][] = 'interview';
            }
        }
        
        // Adjust for mature students (over 25)
        if ($application->date_of_birth && $application->date_of_birth->age >= 25) {
            $requirements['additional_requirements'][] = 'work_experience';
            $requirements['additional_requirements'][] = 'mature_student_essay';
        }
        
        return $requirements;
    }

    /**
     * Get program-specific requirements from database
     */
    private function getProgramSpecificRequirements($programId)
    {
        return DB::table('program_requirements')
            ->where('program_id', $programId)
            ->where('is_active', true)
            ->get()
            ->groupBy('requirement_type');
    }

    /**
     * AJAX endpoint to save section data - handles all types
     */
    public function saveSection(Request $request, $uuid)
    {
        try {
            $application = $this->getApplicationByUuid($uuid);
            $section = $request->input('section');
            $requirements = $this->determineRequirements($application);
            
            // Enhanced logging with all request data
            Log::info('SaveSection called', [
                'uuid' => $uuid,
                'section' => $section,
                'application_type' => $application->application_type,
                'request_data' => $request->except(['_token'])
            ]);
            
            // Validate section is required for this application type
            if (!in_array($section, $requirements['sections'])) {
                Log::warning('Section not required', [
                    'section' => $section,
                    'required_sections' => $requirements['sections']
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'This section is not required for your application type'
                ], 400);
            }
            
            $data = $request->except(['section', '_token']);
            
            // Store initial state for comparison
            $initialData = $application->toArray();
            
            // Handle section based on type
            switch($section) {
                case 'academic':
                    $this->saveAcademicSection($application, $request, $requirements);
                    break;
                    
                case 'test-scores':
                    $this->saveTestScoresSection($application, $request, $requirements);
                    break;
                    
                case 'essays':
                    $this->saveEssaysSection($application, $request, $requirements);
                    break;
                    
                case 'activities':
                    $this->saveActivitiesSection($application, $request);
                    break;
                    
                case 'documents':
                    $this->saveDocumentsSection($application, $request);
                    break;
                    
                case 'recommendations':
                    $this->saveRecommendationsSection($application, $request);
                    break;
                    
                case 'college-courses':
                    $this->saveCollegeCoursesSection($application, $request);
                    break;
                    
                case 'research':
                    $this->saveResearchSection($application, $request);
                    break;
                    
                case 'english-proficiency':
                    $this->saveEnglishProficiencySection($application, $request);
                    break;
                    
                case 'financial':
                    $this->saveFinancialSection($application, $request);
                    break;
                    
                case 'portfolio':
                    $this->savePortfolioSection($application, $request);
                    break;
                    
                default:
                    // Generic save for custom sections
                    $this->saveCustomSection($application, $section, $data);
            }
            
            $application->last_updated_at = now();
            $saved = $application->save();
            
            // Log what changed
            $changes = [];
            foreach ($application->getAttributes() as $key => $value) {
                if (isset($initialData[$key]) && $initialData[$key] != $value) {
                    $changes[$key] = [
                        'old' => $initialData[$key],
                        'new' => $value
                    ];
                }
            }
            
            Log::info('Section save result', [
                'section' => $section,
                'saved' => $saved,
                'changes_made' => !empty($changes),
                'changed_fields' => array_keys($changes)
            ]);
            
            // Update checklist
            $this->updateChecklistItem($application, $section, true);
            
            // Calculate completion
            $completion = $this->calculateCompletion($application, $requirements);
            
            return response()->json([
                'success' => true,
                'message' => 'Section saved successfully',
                'completion' => $completion,
                'debug' => [
                    'changes_made' => !empty($changes),
                    'fields_changed' => array_keys($changes)
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error in saveSection', [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error saving section', [
                'section' => $section ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Section-specific save methods
     */
    private function saveAcademicSection($application, $request, $requirements)
    {
        $data = $request->all();
        
        // Handle freshman-specific fields
        if ($application->application_type === 'freshman') {
            $application->high_school_name = $data['high_school_name'] ?? $application->high_school_name;
            $application->high_school_country = $data['high_school_country'] ?? $application->high_school_country;
            $application->high_school_graduation_date = $data['high_school_graduation_date'] ?? $application->high_school_graduation_date;
            $application->high_school_diploma_type = $data['high_school_diploma_type'] ?? $application->high_school_diploma_type;
        }
        
        // Common academic fields
        $application->previous_institution = $data['previous_institution'] ?? $application->previous_institution;
        $application->previous_institution_country = $data['previous_institution_country'] ?? $application->previous_institution_country;
        $application->previous_degree = $data['previous_degree'] ?? $application->previous_degree;
        $application->previous_major = $data['previous_major'] ?? $application->previous_major;
        $application->previous_gpa = $data['previous_gpa'] ?? $application->previous_gpa;
        $application->gpa_scale = $data['gpa_scale'] ?? $application->gpa_scale;
        $application->class_rank = $data['class_rank'] ?? $application->class_rank;
        $application->class_size = $data['class_size'] ?? $application->class_size;
        
        // Handle graduate-specific fields
        if ($application->application_type === 'graduate') {
            $application->thesis_title = $data['thesis_title'] ?? $application->thesis_title;
            $application->thesis_advisor = $data['thesis_advisor'] ?? $application->thesis_advisor;
        }
        
        // Save the application
        $application->save();
    }

    private function saveTestScoresSection($application, $request, $requirements)
    {
        // Get existing scores - with model cast, this will be an array
        $testScores = $application->test_scores ?? [];
        
        // Only process tests required for this application type
        foreach ($requirements['required_tests'] ?? [] as $test) {
            switch($test) {
                case 'WASSCE':
                    // Check if any WASSCE field is being submitted
                    if ($request->has('wassce_english') || $request->has('wassce_mathematics')) {
                        $testScores['WASSCE'] = [
                            'english' => $request->input('wassce_english'),
                            'mathematics' => $request->input('wassce_mathematics'),
                            'science' => $request->input('wassce_science'),
                            'social' => $request->input('wassce_social'),
                            'year' => $request->input('wassce_year'),
                            'additional' => $request->input('wassce_additional')
                        ];
                    }
                    break;
                    
                case 'SAT':
                    if ($request->has('sat_math') || $request->has('sat_verbal')) {
                        $testScores['SAT'] = [
                            'math' => $request->input('sat_math'),
                            'verbal' => $request->input('sat_verbal'),
                            'total' => $request->input('sat_total'),
                            'test_date' => $request->input('sat_date')
                        ];
                    }
                    break;
                    
                case 'ACT':
                    // Check for either composite or individual scores
                    if ($request->has('act_composite') || $request->has('act_english') || 
                        $request->has('act_math') || $request->has('act_reading') || 
                        $request->has('act_science')) {
                        $testScores['ACT'] = [
                            'english' => $request->input('act_english'),
                            'math' => $request->input('act_math'),
                            'reading' => $request->input('act_reading'),
                            'science' => $request->input('act_science'),
                            'composite' => $request->input('act_composite'),
                            'test_date' => $request->input('act_date')
                        ];
                    }
                    break;
                    
                case 'GRE':
                    if ($request->has('gre_verbal') || $request->has('gre_quantitative')) {
                        $testScores['GRE'] = [
                            'verbal' => $request->input('gre_verbal'),
                            'quantitative' => $request->input('gre_quantitative'),
                            'analytical' => $request->input('gre_analytical'),
                            'test_date' => $request->input('gre_date')
                        ];
                    }
                    break;
                    
                case 'GMAT':
                    if ($request->has('gmat_verbal') || $request->has('gmat_quantitative')) {
                        $testScores['GMAT'] = [
                            'verbal' => $request->input('gmat_verbal'),
                            'quantitative' => $request->input('gmat_quantitative'),
                            'analytical' => $request->input('gmat_analytical'),
                            'integrated' => $request->input('gmat_integrated'),
                            'total' => $request->input('gmat_total'),
                            'test_date' => $request->input('gmat_date')
                        ];
                    }
                    break;
                    
                case 'TOEFL':
                    if ($request->has('toefl_reading') || $request->has('toefl_listening')) {
                        $testScores['TOEFL'] = [
                            'reading' => $request->input('toefl_reading'),
                            'listening' => $request->input('toefl_listening'),
                            'speaking' => $request->input('toefl_speaking'),
                            'writing' => $request->input('toefl_writing'),
                            'total' => $request->input('toefl_total'),
                            'test_date' => $request->input('toefl_date')
                        ];
                    }
                    break;
                    
                case 'IELTS':
                    if ($request->has('ielts_listening') || $request->has('ielts_reading')) {
                        $testScores['IELTS'] = [
                            'listening' => $request->input('ielts_listening'),
                            'reading' => $request->input('ielts_reading'),
                            'writing' => $request->input('ielts_writing'),
                            'speaking' => $request->input('ielts_speaking'),
                            'overall' => $request->input('ielts_overall'),
                            'test_date' => $request->input('ielts_date')
                        ];
                    }
                    break;
            }
        }
        
        // Model will handle JSON encoding due to 'array' cast
        $application->test_scores = $testScores;
        $application->save();
        
        // Log what was saved for debugging
        Log::info('Test scores saved', [
            'application_id' => $application->id,
            'tests_saved' => array_keys($testScores),
            'data' => $testScores
        ]);
    }

    private function saveEssaysSection($application, $request, $requirements)
    {
        $data = $request->all();
        
        // Save each essay prompt
        foreach ($requirements['essay_prompts'] ?? [] as $prompt) {
            if (isset($data[$prompt])) {
                $application->$prompt = $data[$prompt];
            }
        }
        
        // Handle additional essays
        if (isset($data['additional_essay_1'])) {
            $application->additional_essay_1 = $data['additional_essay_1'];
        }
        if (isset($data['additional_essay_2'])) {
            $application->additional_essay_2 = $data['additional_essay_2'];
        }
        
        $application->save();
    }

    private function saveActivitiesSection($application, $request)
    {
        $data = $request->all();
        $activities = [];
        $awards = [];
        
        // Log incoming data
        Log::info('Activities section data received', [
            'data_keys' => array_keys($data),
            'sample_data' => array_slice($data, 0, 5)
        ]);
        
        // Process activities
        for ($i = 1; $i <= 5; $i++) {
            if (!empty($data["activity_{$i}_name"])) {
                $activities[] = [
                    'name' => $data["activity_{$i}_name"],
                    'position' => $data["activity_{$i}_position"] ?? '',
                    'years' => $data["activity_{$i}_years"] ?? '',
                    'hours' => intval($data["activity_{$i}_hours"] ?? 0),
                    'description' => $data["activity_{$i}_description"] ?? ''
                ];
            }
        }
        
        // Process awards
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($data["award_{$i}_name"])) {
                $awards[] = [
                    'name' => $data["award_{$i}_name"],
                    'year' => $data["award_{$i}_year"] ?? '',
                    'level' => $data["award_{$i}_level"] ?? ''
                ];
            }
        }
        
        // Since the model has 'array' cast, just assign arrays directly
        $application->extracurricular_activities = $activities;
        $application->awards_honors = $awards;
        
        Log::info('Activities section processed', [
            'activities_count' => count($activities),
            'awards_count' => count($awards),
            'activities_data' => $activities,
            'awards_data' => $awards
        ]);
        
        $saved = $application->save();
        
        Log::info('Activities save result', [
            'saved' => $saved,
            'db_activities' => $application->fresh()->extracurricular_activities,
            'db_awards' => $application->fresh()->awards_honors
        ]);
    }

    private function saveDocumentsSection($application, $request)
    {
        // Documents are handled separately through the upload endpoint
        // This just marks the section as attempted
        return true;
    }

    private function saveRecommendationsSection($application, $request)
    {
        $data = $request->all();
        $recommendations = [];
        
        // Process recommenders
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($data["recommender_{$i}_name"])) {
                $recommendations[] = [
                    'name' => $data["recommender_{$i}_name"],
                    'title' => $data["recommender_{$i}_title"] ?? '',
                    'email' => $data["recommender_{$i}_email"] ?? '',
                    'relationship' => $data["recommender_{$i}_relationship"] ?? '',
                    'institution' => $data["recommender_{$i}_institution"] ?? '',
                    'phone' => $data["recommender_{$i}_phone"] ?? '',
                    'status' => 'pending'
                ];
            }
        }
        
        // Model will handle JSON encoding due to 'array' cast
        $application->references = $recommendations;
        $application->save();
    }

    private function saveCollegeCoursesSection($application, $request)
    {
        $data = $request->all();
        $courses = [];
        
        for ($i = 1; $i <= 20; $i++) {
            if (!empty($data["course_{$i}_name"])) {
                $courses[] = [
                    'name' => $data["course_{$i}_name"],
                    'code' => $data["course_{$i}_code"] ?? '',
                    'credits' => $data["course_{$i}_credits"] ?? 0,
                    'grade' => $data["course_{$i}_grade"] ?? '',
                    'institution' => $data["course_{$i}_institution"] ?? '',
                ];
            }
        }
        
        // Store in custom_requirements since there's no dedicated field
        $customData = $application->custom_requirements ?? [];
        $customData['college_courses'] = $courses;
        $application->custom_requirements = $customData;
        $application->save();
    }

    private function saveResearchSection($application, $request)
    {
        $data = $request->all();
        
        // Store research data in custom_requirements
        $customData = $application->custom_requirements ?? [];
        $customData['research'] = [
            'research_area' => $data['research_area'] ?? '',
            'research_interests' => $data['research_interests'] ?? '',
            'preferred_advisor' => $data['preferred_advisor'] ?? null,
            'publications' => $data['publications'] ?? [],
            'research_experience' => $data['research_experience'] ?? ''
        ];
        $application->custom_requirements = $customData;
        $application->save();
    }

    private function saveEnglishProficiencySection($application, $request)
    {
        $data = $request->all();
        
        // Store in custom_requirements
        $customData = $application->custom_requirements ?? [];
        $customData['english_proficiency'] = [
            'test_type' => $data['english_test_type'] ?? '',
            'test_score' => $data['english_test_score'] ?? '',
            'test_date' => $data['english_test_date'] ?? null,
            'waiver_requested' => $data['english_waiver_requested'] ?? false,
            'waiver_reason' => $data['english_waiver_reason'] ?? ''
        ];
        $application->custom_requirements = $customData;
        $application->save();
    }

    private function saveFinancialSection($application, $request)
    {
        $data = $request->all();
        
        // Store financial info in custom_requirements
        $customData = $application->custom_requirements ?? [];
        $customData['financial'] = [
            'sponsor_type' => $data['sponsor_type'] ?? '',
            'sponsor_name' => $data['sponsor_name'] ?? '',
            'sponsor_relationship' => $data['sponsor_relationship'] ?? '',
            'annual_income' => $data['annual_income'] ?? 0,
            'available_funds' => $data['available_funds'] ?? 0,
            'bank_statement_date' => $data['bank_statement_date'] ?? null
        ];
        $application->custom_requirements = $customData;
        $application->save();
    }

    private function savePortfolioSection($application, $request)
    {
        $portfolio = [];
        
        for ($i = 1; $i <= 10; $i++) {
            if ($request->hasFile("portfolio_item_{$i}")) {
                $file = $request->file("portfolio_item_{$i}");
                $path = $file->store("portfolios/{$application->application_uuid}", 'public');
                
                $portfolio[] = [
                    'title' => $request->input("portfolio_title_{$i}"),
                    'description' => $request->input("portfolio_description_{$i}"),
                    'medium' => $request->input("portfolio_medium_{$i}"),
                    'year' => $request->input("portfolio_year_{$i}"),
                    'file_path' => $path,
                ];
            }
        }
        
        // Store portfolio in custom_requirements
        $customData = $application->custom_requirements ?? [];
        $customData['portfolio'] = $portfolio;
        $application->custom_requirements = $customData;
        $application->save();
    }

    private function saveCustomSection($application, $section, $data)
    {
        // Save any other custom/additional requirements
        $customData = $application->custom_requirements ?? [];
        $customData[$section] = $data;
        $application->custom_requirements = $customData;
        $application->save();
    }

    /**
     * Calculate completion percentage based on requirements
     */
    private function calculateCompletion($application, $requirements)
    {
        $totalSections = count($requirements['sections']);
        $completedSections = 0;
        $sectionStatuses = [];
        
        foreach ($requirements['sections'] as $section) {
            $isComplete = $this->isSectionComplete($application, $section, $requirements);
            if ($isComplete) {
                $completedSections++;
            }
            $sectionStatuses[$section] = $isComplete;
        }
        
        $percentage = $totalSections > 0 ? round(($completedSections / $totalSections) * 100) : 0;
        
        return [
            'percentage' => $percentage,
            'completed' => $completedSections,
            'total' => $totalSections,
            'sections' => $sectionStatuses,
            'can_submit' => $percentage === 100
        ];
    }

    /**
     * Check if a section is complete
     */
    private function isSectionComplete($application, $section, $requirements)
    {
        switch($section) {
            case 'academic':
                if ($application->application_type === 'freshman') {
                    return !empty($application->high_school_name) && 
                        !empty($application->previous_gpa) &&
                        !empty($application->gpa_scale);
                }
                return !empty($application->previous_institution) && 
                    !empty($application->previous_gpa) &&
                    !empty($application->gpa_scale);
                
            case 'test-scores':
                if (empty($requirements['required_tests'])) {
                    return true;
                }
                
                // Safely get test scores
                $scores = $application->test_scores;
                if (!is_array($scores)) {
                    return false;
                }
                
                // For freshman, check WASSCE
                if ($application->application_type === 'freshman' && 
                    in_array('WASSCE', $requirements['required_tests'])) {
                    return isset($scores['WASSCE']) && 
                        !empty($scores['WASSCE']['english']) && 
                        !empty($scores['WASSCE']['mathematics']);
                }
                
                // For international students
                if ($application->application_type === 'international') {
                    $hasSAT = isset($scores['SAT']) && !empty($scores['SAT']['total']);
                    $hasACT = isset($scores['ACT']) && !empty($scores['ACT']['composite']);
                    return $hasSAT || $hasACT;
                }
                
                return true;
                
            case 'essays':
                foreach ($requirements['essay_prompts'] ?? [] as $prompt) {
                    if (empty($application->$prompt)) {
                        return false;
                    }
                }
                return true;
                
            case 'activities':
                $activities = $application->extracurricular_activities;
                return is_array($activities) && count($activities) > 0;
                
            case 'documents':
                $checklistItem = ApplicationChecklistItem::where('application_id', $application->id)
                    ->where('item_name', 'documents')
                    ->first();
                return $checklistItem ? $checklistItem->is_completed : false;
                
            case 'recommendations':
                $minRequired = $requirements['min_recommendations'] ?? 2;
                $references = $application->references;
                return is_array($references) && count($references) >= $minRequired;
                
            default:
                $checklistItem = ApplicationChecklistItem::where('application_id', $application->id)
                    ->where('item_name', $section)
                    ->first();
                    
                if ($checklistItem) {
                    return $checklistItem->is_completed;
                }
                
                $customData = $application->custom_requirements;
                return is_array($customData) && isset($customData[$section]) && !empty($customData[$section]);
        }
    }

    /**
     * Helper method to get application by UUID
     */
    private function getApplicationByUuid($uuid)
    {
        $application = AdmissionApplication::where('application_uuid', $uuid)
            ->with(['documents', 'checklistItems', 'program', 'term'])
            ->firstOrFail();
            
        if ($application->expires_at && $application->expires_at < now()) {
            abort(403, 'This application has expired. Please start a new application.');
        }
        
        return $application;
    }

    /**
     * Update checklist item
     */
    private function updateChecklistItem($application, $section, $completed)
    {
        ApplicationChecklistItem::updateOrCreate(
            [
                'application_id' => $application->id,
                'item_name' => $section
            ],
            [
                'is_completed' => $completed,
                'completed_at' => $completed ? now() : null,
                'item_type' => 'form',
                'is_required' => true
            ]
        );
    }
}