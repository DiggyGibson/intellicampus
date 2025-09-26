<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ApplicationService;
use App\Services\DocumentVerificationService;
use App\Services\ApplicationNotificationService;
use App\Services\PaymentGatewayService;
use App\Models\AdmissionApplication;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Models\Country;
use App\Models\AdmissionSetting;
use App\Models\Department;
use App\Models\SystemSetting;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Exception;

class AdmissionsPortalController extends Controller
{
    protected $applicationService;
    protected $documentService;
    protected $notificationService;
    protected $paymentService;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        ApplicationService $applicationService = null,
        DocumentVerificationService $documentService = null,
        ApplicationNotificationService $notificationService = null,
        PaymentGatewayService $paymentService = null
    ) {
        // Initialize services with null checks for optional dependencies
        $this->applicationService = $applicationService ?: new ApplicationService();
        $this->documentService = $documentService ?: new DocumentVerificationService();
        $this->notificationService = $notificationService ?: new ApplicationNotificationService();
        $this->paymentService = $paymentService ?: new PaymentGatewayService();
    }

    /**
     * Display the admissions portal landing page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get current admission cycle information
        $currentTerm = AcademicTerm::where('is_admission_open', true)
            ->where('admission_deadline', '>', now())
            ->first();

        // Get available programs
        $programs = AcademicProgram::where('is_active', true)
            ->where('accepts_applications', true)
            ->orderBy('name')
            ->get();

        // Get admission statistics for display
        $stats = Cache::remember('admission_portal_stats', 3600, function () use ($currentTerm) {
            if (!$currentTerm) {
                return null;
            }
            
            return [
                'total_applications' => AdmissionApplication::where('term_id', $currentTerm->id)
                    ->where('status', '!=', 'draft')
                    ->count(),
                'programs_available' => AcademicProgram::where('is_active', true)
                    ->where('accepts_applications', true)
                    ->count(),
                'deadline' => $currentTerm->admission_deadline ?? null,
                'spots_available' => $currentTerm->total_spots ?? 0,
            ];
        });

        // Check if user has existing applications
        $existingApplications = [];
        if (Auth::check()) {
            $existingApplications = AdmissionApplication::where('user_id', Auth::id())
                ->orWhere('email', Auth::user()->email)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        return view('admissions.portal.index', compact(
            'currentTerm',
            'programs',
            'stats',
            'existingApplications'
        ));
    }

    /**
     * Start a new application - Shows form and processes submission
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function startApplication(Request $request)
    {
        try {
            // Check if admissions are open
            $currentTerm = AcademicTerm::where('is_admission_open', true)
                ->where('admission_deadline', '>', now())
                ->first();

            if (!$currentTerm) {
                return redirect()->route('admissions.portal.index')
                    ->with('error', 'Admissions are currently closed. Please check back later.');
            }

            // Get all available terms
            $availableTerms = AcademicTerm::where('is_admission_open', true)
                ->where('admission_deadline', '>', now())
                ->orderBy('admission_deadline')
                ->get();

            // Get application types
            $applicationTypes = [
                'freshman' => 'First-Year Student',
                'transfer' => 'Transfer Student', 
                'graduate' => 'Graduate Student',
                'international' => 'International Student',
                'readmission' => 'Readmission',
            ];

            // Get available programs that accept applications
            $programs = AcademicProgram::where('is_active', true)
                ->where('accepts_applications', true)
                ->orderBy('name')
                ->get()
                ->map(function ($program) {
                    // Add department name for display
                    $program->department = DB::table('departments')
                        ->where('id', $program->department_id)
                        ->value('name') ?? 'General Studies';
                    return $program;
                });

            // Get departments
            $departments = collect();
            if (class_exists(\App\Models\Department::class)) {
                $departments = Department::where('is_active', true)
                    ->orderBy('name')
                    ->get();
            } else {
                // Fallback: Get departments from database
                $departments = DB::table('departments')
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
            }

            // Get fee configuration from system settings or use defaults
            $applicationFee = $this->getSystemSetting('admissions', 'application_fee', 50.00);
            $processingFee = $this->getSystemSetting('admissions', 'processing_fee', 0.00);
            
            // Check payment gateway availability
            $stripeEnabled = !empty(config('services.stripe.secret'));
            $paymentMethods = $this->getAvailablePaymentMethods();

            // Handle POST request (form submission)
            if ($request->isMethod('post')) {
                Log::info('Processing application submission', [
                    'method' => $request->method(),
                    'data' => $request->except(['password', '_token'])
                ]);

                // Validate initial application data with ALL required fields
                $validated = $request->validate([
                    // Application type and program
                    'application_type' => 'required|in:' . implode(',', array_keys($applicationTypes)),
                    'program_id' => 'required|exists:academic_programs,id',
                    'entry_term' => 'required|in:fall,spring,summer',
                    'entry_year' => 'required|integer|min:' . date('Y'),
                    'term_id' => 'required|exists:academic_terms,id',
                    
                    // Personal information
                    'first_name' => 'required|string|max:100',
                    'middle_name' => 'nullable|string|max:100',
                    'last_name' => 'required|string|max:100',
                    'date_of_birth' => 'required|date|before:today',
                    'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
                    'nationality' => 'required|string|max:100',
                    'national_id' => 'nullable|string|max:50',
                    'passport_number' => 'nullable|string|max:50',
                    
                    // Contact information
                    'email' => 'required|email|max:255',
                    'phone_primary' => 'required|string|max:20',
                    
                    // Address information
                    'current_address' => 'required|string',
                    'city' => 'required|string|max:100',
                    'state_province' => 'nullable|string|max:100',
                    'country' => 'required|string|max:100',
                    
                    // Optional fields
                    'alternate_program_id' => 'nullable|exists:academic_programs,id',
                    
                    // Agreement
                    'agree_terms' => 'accepted',
                ]);

                // Check for duplicate application
                $existingApplication = AdmissionApplication::where('email', $validated['email'])
                    ->where('term_id', $validated['term_id'])
                    ->where('program_id', $validated['program_id'])
                    ->whereNotIn('status', ['withdrawn', 'denied', 'expired'])
                    ->first();

                if ($existingApplication) {
                    Log::info('Found existing application', [
                        'application_id' => $existingApplication->id,
                        'uuid' => $existingApplication->application_uuid
                    ]);

                    // If application exists, continue it
                    Session::put('current_application_id', $existingApplication->id);
                    Session::put('current_application_uuid', $existingApplication->application_uuid);
                    
                    // Update the existing application with the new personal info if provided
                    $existingApplication->update([
                        'first_name' => $validated['first_name'],
                        'middle_name' => $validated['middle_name'] ?? null,
                        'last_name' => $validated['last_name'],
                        'date_of_birth' => $validated['date_of_birth'],
                        'gender' => $validated['gender'] ?? null,
                        'nationality' => $validated['nationality'],
                        'national_id' => $validated['national_id'] ?? null,
                        'passport_number' => $validated['passport_number'] ?? null,
                        'phone_primary' => $validated['phone_primary'],
                        'current_address' => $validated['current_address'],
                        'permanent_address' => $validated['current_address'],
                        'city' => $validated['city'],
                        'state_province' => $validated['state_province'] ?? null,
                        'country' => $validated['country'],
                        'updated_at' => now(),
                    ]);
                    
                    // Determine the next step based on what's already filled
                    $nextStep = $this->determineNextSection($existingApplication);
                    
                    return redirect()->route("admissions.portal.application.{$nextStep}", [
                        'uuid' => $existingApplication->application_uuid
                    ])->with('info', 'You already have an application in progress. You can continue where you left off.')
                       ->with('application_uuid', $existingApplication->application_uuid)
                       ->with('application_number', $existingApplication->application_number);
                }

                // Generate application number and UUID
                $applicationNumber = $this->generateApplicationNumber();
                $applicationUuid = (string) Str::uuid();

                Log::info('Creating new application', [
                    'application_number' => $applicationNumber,
                    'uuid' => $applicationUuid,
                    'email' => $validated['email']
                ]);

                // Create new application
                DB::beginTransaction();
                try {
                    // Create the main application record
                    $application = AdmissionApplication::create([
                        // Application identifiers
                        'application_number' => $applicationNumber,
                        'application_uuid' => $applicationUuid,
                        
                        // Personal information
                        'first_name' => $validated['first_name'],
                        'middle_name' => $validated['middle_name'] ?? null,
                        'last_name' => $validated['last_name'],
                        'date_of_birth' => $validated['date_of_birth'],
                        'gender' => $validated['gender'] ?? null,
                        'nationality' => $validated['nationality'],
                        'national_id' => $validated['national_id'] ?? null,
                        'passport_number' => $validated['passport_number'] ?? null,
                        
                        // Contact information
                        'email' => $validated['email'],
                        'phone_primary' => $validated['phone_primary'],
                        
                        // Address information
                        'current_address' => $validated['current_address'],
                        'city' => $validated['city'],
                        'state_province' => $validated['state_province'] ?? null,
                        'country' => $validated['country'],
                        
                        // Also set permanent address same as current for now
                        'permanent_address' => $validated['current_address'],
                        
                        // Application details
                        'application_type' => $validated['application_type'],
                        'program_id' => $validated['program_id'],
                        'alternate_program_id' => $validated['alternate_program_id'] ?? null,
                        'term_id' => $validated['term_id'],
                        'entry_type' => $validated['entry_term'] ?? null,
                        'entry_year' => $validated['entry_year'],
                        
                        // Status and tracking
                        'user_id' => Auth::id(),
                        'status' => 'draft',
                        'started_at' => now(),
                        'expires_at' => now()->addDays(90), // 90 days to complete
                        
                        // Fee information (set directly here)
                        'application_fee_amount' => $applicationFee,
                        'application_fee_paid' => false,
                        
                        // IP and user agent
                        'ip_address' => $request->ip(),
                        'user_agent' => substr($request->userAgent(), 0, 500), // Limit length
                        
                        // Activity log
                        'activity_log' => json_encode([
                            [
                                'timestamp' => now()->toIso8601String(),
                                'action' => 'application_started',
                                'ip' => $request->ip()
                            ]
                        ]),
                        
                        // Timestamps
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Commit the main application first
                    DB::commit();
                    
                    Log::info('Application created successfully', [
                        'application_id' => $application->id,
                        'uuid' => $application->application_uuid
                    ]);

                    // Now handle non-critical operations outside the main transaction
                    // These won't rollback the main application if they fail
                    
                    // Create checklist items (non-critical)
                    try {
                        $this->createApplicationChecklist($application);
                    } catch (Exception $e) {
                        Log::error('Failed to create checklist items', [
                            'application_id' => $application->id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    // Create fee record (non-critical)
                    if ($applicationFee > 0) {
                        try {
                            // Simple fee record update
                            $application->update([
                                'application_fee_amount' => $applicationFee,
                                'fee_waiver_requested' => false
                            ]);
                        } catch (Exception $e) {
                            Log::warning('Failed to update fee information', [
                                'application_id' => $application->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    // Send notification email (non-critical)
                    try {
                        if ($this->notificationService) {
                            $this->notificationService->sendApplicationStartedEmail($application);
                        }
                    } catch (Exception $e) {
                        Log::warning('Could not send application started email', [
                            'application_id' => $application->id,
                            'error' => $e->getMessage()
                        ]);
                    }

                    // Store application ID in session for tracking
                    Session::put('current_application_id', $application->id);
                    Session::put('current_application_uuid', $application->application_uuid);
                    Session::flash('application_uuid', $application->application_uuid);
                    Session::flash('application_number', $application->application_number);

                    // Check if save and exit was requested
                    if ($request->input('save_and_exit') === 'true') {
                        return view('admissions.portal.application-created', compact('application'))
                            ->with('save_and_exit', true);
                    }

                    // Otherwise show the application created page with continue option
                    return view('admissions.portal.application-created', compact('application'))
                        ->with('save_and_exit', false);

                } catch (Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to create application in database', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }           

            // For GET request, show the start form with all required data
            return view('admissions.portal.start-application', compact(
                'applicationTypes',
                'programs',
                'currentTerm',
                'availableTerms',
                'departments',
                'applicationFee',
                'processingFee',
                'stripeEnabled',
                'paymentMethods'
            ));

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed', [
                'errors' => $e->errors()
            ]);
            // Handle validation errors
            return back()->withErrors($e->validator)->withInput();
            
        } catch (Exception $e) {
            Log::error('Failed to start application', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => $request->except(['password', 'password_confirmation']),
            ]);

            return back()->with('error', 'An error occurred while starting your application. Please try again. Error: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Create application (alias for POST to startApplication)
     */
    public function createApplication(Request $request)
    {
        return $this->startApplication($request);
    }

    /**
     * Show status check form
     */
    public function statusForm()
    {
        return view('admissions.portal.check-status');
    }
    
    /**
     * Alias for statusForm for route compatibility
     */
    public function checkStatusForm()
    {
        return $this->statusForm();
    }

    /**
     * Check application status (process form submission)
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reference' => 'required|string',
        ]);

        // Determine if reference is a UUID or application number
        $isUuid = preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $request->reference);
        
        // Build the query
        $query = AdmissionApplication::where('email', $request->email);
        
        if ($isUuid) {
            $query->where('application_uuid', $request->reference);
        } else {
            $query->where('application_number', $request->reference);
        }
        
        $application = $query->first();

        if (!$application) {
            return back()->with('error', 'Application not found. Please check your email and application reference.')
                        ->withInput();
        }

        return redirect()->route('admissions.portal.status.view', ['uuid' => $application->application_uuid]);
    }


    /**
     * Display application status
     */
    public function viewStatus($uuid)
    {
        try {
            $application = AdmissionApplication::where('application_uuid', $uuid)
                ->with(['program', 'term', 'documents', 'checklistItems'])
                ->firstOrFail();
            
            // Build timeline
            $timeline = $this->getApplicationTimeline($application);
            
            // Get next steps
            $nextSteps = $this->getNextSteps($application);
            
            // Get pending actions
            $pendingActions = $this->getPendingActions($application);
            
            return view('admissions.portal.application-status', compact(
                'application', 
                'timeline',
                'nextSteps',
                'pendingActions'
            ));
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admissions.portal.status')
                ->with('error', 'Application not found.');
        } catch (Exception $e) {
            Log::error('Failed to view application status', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('admissions.portal.index')
                ->with('error', 'An error occurred while retrieving your application status.');
        }
    }

    /**
     * Show continue application form
     */
    public function continueForm()
    {
        // Check if there's a form view, otherwise use status form
        $viewName = 'admissions.portal.continue-form';
        if (!view()->exists($viewName)) {
            return $this->statusForm();
        }
        
        return view($viewName);
    }

    /**
     * Find and continue application
     */
    public function findApplication(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reference' => 'required|string',
        ]);

        // Determine if reference is a UUID or application number
        $isUuid = preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $request->reference);
        
        // Build the query
        $query = AdmissionApplication::where('email', $request->email)
            ->whereIn('status', ['draft', 'documents_pending']);
        
        if ($isUuid) {
            $query->where('application_uuid', $request->reference);
        } else {
            $query->where('application_number', $request->reference);
        }
        
        $application = $query->first();

        if (!$application) {
            // Check if the application exists but is already submitted
            $submittedQuery = AdmissionApplication::where('email', $request->email);
            
            if ($isUuid) {
                $submittedQuery->where('application_uuid', $request->reference);
            } else {
                $submittedQuery->where('application_number', $request->reference);
            }
            
            $submittedApp = $submittedQuery->first();
            
            if ($submittedApp) {
                return redirect()->route('admissions.portal.status.view', ['uuid' => $submittedApp->application_uuid])
                    ->with('info', 'This application has already been submitted. You can view its status below.');
            }
            
            return back()->with('error', 'No application found with these details. Please check your email and application reference.')
                        ->withInput();
        }

        // Store in session
        Session::put('current_application_id', $application->id);
        Session::put('current_application_uuid', $application->application_uuid);

        // Determine next step
        $nextStep = $this->determineNextSection($application);
        
        return redirect()->route("admissions.portal.application.{$nextStep}", ['uuid' => $application->application_uuid])
            ->with('success', 'Welcome back! You can continue your application where you left off.')
            ->with('application_number', $application->application_number);
    }

    /**
     * Continue application handler - handles both query param and direct UUID
     * This method handles: /admissions/portal/continue?uuid={uuid} OR /admissions/portal/continue/{uuid}
     */
    public function continueApplication($uuid = null)
    {
        // Check if UUID is in query parameters (for backward compatibility)
        if (!$uuid) {
            $uuid = request()->query('uuid');
        }
        
        if (!$uuid) {
            return redirect()->route('admissions.portal.index')
                ->with('error', 'No application UUID provided. Please use your application link from the email.');
        }

        try {
            // Find application by UUID
            $application = AdmissionApplication::where('application_uuid', $uuid)
                ->firstOrFail();

            // Check if application is still editable
            if (!in_array($application->status, ['draft', 'documents_pending'])) {
                // If already submitted, redirect to status view
                return redirect()->route('admissions.portal.status.view', ['uuid' => $uuid])
                    ->with('info', 'This application has already been submitted. You can view its status below.');
            }

            // Check if application has expired
            if ($application->expires_at && $application->expires_at < now()) {
                return redirect()->route('admissions.portal.index')
                    ->with('error', 'This application has expired. Please start a new application.');
            }

            // Store in session
            Session::put('current_application_id', $application->id);
            Session::put('current_application_uuid', $application->application_uuid);

            // Log the continuation
            $activityLog = json_decode($application->activity_log, true) ?? [];
            $activityLog[] = [
                'timestamp' => now()->toIso8601String(),
                'action' => 'application_continued',
                'ip' => request()->ip()
            ];
            $application->activity_log = json_encode($activityLog);
            $application->save();

            // Determine next incomplete section
            $nextSection = $this->determineNextSection($application);

            // Redirect to the appropriate form section
            return redirect()->route("admissions.portal.application.{$nextSection}", ['uuid' => $uuid])
                ->with('success', 'Welcome back! Continue filling out your application.')
                ->with('application_number', $application->application_number);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admissions.portal.index')
                ->with('error', 'Application not found. Please check your application link.');
        } catch (Exception $e) {
            Log::error('Failed to continue application', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admissions.portal.index')
                ->with('error', 'An error occurred while loading your application. Please try again.');
        }
    }


    /**
     * Resume an application
     */
    public function resumeApplication($uuid)
    {
        try {
            $application = AdmissionApplication::where('application_uuid', $uuid)
                ->with(['program', 'term', 'documents', 'checklistItems'])
                ->firstOrFail();

            // Check if application is still editable
            if (!in_array($application->status, ['draft', 'documents_pending'])) {
                return redirect()->route('admissions.portal.status.view', ['uuid' => $uuid])
                    ->with('info', 'This application has already been submitted and cannot be edited.');
            }

            // Check if application has expired
            if ($application->expires_at && $application->expires_at < now()) {
                return redirect()->route('admissions.portal.index')
                    ->with('error', 'This application has expired. Please start a new application.');
            }

            // Store in session
            Session::put('current_application_id', $application->id);
            Session::put('current_application_uuid', $application->application_uuid);

            // Determine next incomplete section
            $nextSection = $this->determineNextSection($application);

            // Redirect to next section
            return redirect()->route("admissions.portal.application.{$nextSection}", ['uuid' => $uuid])
                ->with('success', 'Welcome back! Continue filling out your application.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admissions.portal.index')
                ->with('error', 'Application not found.');
        } catch (Exception $e) {
            Log::error('Failed to continue application', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('admissions.portal.index')
                ->with('error', 'An error occurred while loading your application.');
        }
    }

    /**
     *  checkApplicationStatus (alias method)
     */
    public function checkApplicationStatus(Request $request)
    {
        return $this->checkStatus($request);
    }

    /**
     * Download application as PDF
     */
    public function downloadApplication($uuid)
    {
        try {
            $application = AdmissionApplication::where('application_uuid', $uuid)
                ->with(['program', 'term', 'documents'])
                ->firstOrFail();

            // Verify ownership if user is authenticated
            if (Auth::check() && !$this->verifyApplicationOwnership($application)) {
                abort(403, 'Unauthorized');
            }

            // Generate PDF
            if ($this->applicationService) {
                $pdfPath = $this->applicationService->generateApplicationPDF($application->id);
                return response()->download(storage_path('app/' . $pdfPath));
            } else {
                return back()->with('error', 'PDF generation service is not available.');
            }

        } catch (Exception $e) {
            Log::error('Failed to download application', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to generate application PDF.');
        }
    }

    /**
     * Withdraw application
     */
    public function withdrawApplication(Request $request, $uuid)
    {
        try {
            $application = AdmissionApplication::where('application_uuid', $uuid)->firstOrFail();

            // Validate withdrawal is allowed
            if (!in_array($application->status, ['draft', 'submitted', 'under_review'])) {
                return back()->with('error', 'This application cannot be withdrawn at this stage.');
            }

            $validated = $request->validate([
                'reason' => 'required|string|max:500',
                'confirm' => 'required|accepted',
            ]);

            // Withdraw application
            $application->status = 'withdrawn';
            $application->withdrawal_reason = $validated['reason'];
            $application->withdrawn_at = now();
            $application->save();

            // Clear session
            Session::forget(['current_application_id', 'current_application_uuid']);

            return redirect()->route('admissions.portal.index')
                ->with('success', 'Your application has been withdrawn successfully.');

        } catch (Exception $e) {
            Log::error('Failed to withdraw application', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to withdraw application.');
        }
    }

    /**
     * Process application payment
     */
    public function processApplicationPayment(Request $request, $uuid)
    {
        try {
            $application = AdmissionApplication::where('application_uuid', $uuid)->firstOrFail();
            
            $validated = $request->validate([
                'payment_method' => 'required|in:online,bank_transfer,cashier'
            ]);
            
            $applicationFee = $this->getSystemSetting('admissions', 'application_fee', 50.00);
            $processingFee = $this->getSystemSetting('admissions', 'processing_fee', 0.00);
            $totalFee = $applicationFee + $processingFee;
            
            if ($validated['payment_method'] === 'online' && $this->paymentService) {
                // Use existing Stripe integration
                $paymentIntent = $this->paymentService->createPaymentIntent(
                    $totalFee,
                    $application->id,
                    'Application fee for ' . $application->application_number
                );
                
                if ($paymentIntent['success']) {
                    return response()->json([
                        'success' => true,
                        'client_secret' => $paymentIntent['client_secret'],
                        'stripe_public_key' => config('services.stripe.key')
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'Failed to create payment intent'
                    ], 400);
                }
            } else {
                // For manual payment methods, record the intent
                $application->payment_method = $validated['payment_method'];
                $application->payment_status = 'pending';
                $application->save();
                
                return redirect()->route('admissions.portal.application.academic', ['uuid' => $application->application_uuid])
                    ->with('success', 'Application created successfully! Please complete the remaining sections.');
            }
        } catch (Exception $e) {
            Log::error('Failed to process application payment', [
                'uuid' => $uuid,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to process payment. Please try again.');
        }
    }

    /**
     * Applicant dashboard (for authenticated users)
     */
    public function applicantDashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('info', 'Please login to view your dashboard.');
        }

        $applications = AdmissionApplication::where('user_id', Auth::id())
            ->orWhere('email', Auth::user()->email)
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => $applications->count(),
            'draft' => $applications->where('status', 'draft')->count(),
            'submitted' => $applications->where('status', 'submitted')->count(),
            'under_review' => $applications->where('status', 'under_review')->count(),
            'admitted' => $applications->where('decision', 'admit')->count(),
        ];

        return view('admissions.portal.dashboard', compact('applications', 'stats'));
    }

    /**
     * My applications list
     */
    public function myApplications()
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('info', 'Please login to view your applications.');
        }

        $applications = AdmissionApplication::where('user_id', Auth::id())
            ->orWhere('email', Auth::user()->email)
            ->with(['program', 'term'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admissions.portal.my-applications', compact('applications'));
    }

    /**
     * Helper Methods
     */

    /**
     * Get system setting value
     */
    private function getSystemSetting($category, $key, $default = null)
    {
        $value = DB::table('system_settings')
            ->where('category', $category)
            ->where('key', $key)
            ->value('value');
            
        return $value !== null ? floatval($value) : $default;
    }

    /**
     * Get available payment methods
     */
    private function getAvailablePaymentMethods(): array
    {
        $methods = [];
        
        // Check what's configured
        if (!empty(config('services.stripe.secret'))) {
            $methods['online'] = 'Pay Online (Credit/Debit Card)';
        }
        
        // Always allow manual payment options
        $methods['bank_transfer'] = 'Bank Transfer';
        $methods['cashier'] = 'Pay at Cashier Office';
        
        // Check if mobile money is configured (for Liberia)
        if ($this->getSystemSetting('payments', 'enable_mobile_money', false)) {
            $methods['mobile_money'] = 'Mobile Money (Orange/MTN)';
        }
        
        return $methods;
    }

    /**
     * Create fee record in financial system
     */
    private function createApplicationFeeRecord($application, $applicationFee, $processingFee): void
    {
        try {
            $totalFee = $applicationFee + $processingFee;
            
            // Check if financial tables exist
            $tableExists = DB::getSchemaBuilder()->hasTable('fee_assessments');
            
            if ($tableExists) {
                DB::table('fee_assessments')->insert([
                    'reference_type' => 'application',
                    'reference_id' => $application->id,
                    'fee_type' => 'application_fee',
                    'amount' => $totalFee,
                    'due_date' => now()->addDays(7),
                    'status' => 'pending',
                    'description' => 'Application fee for ' . $application->application_number,
                    'breakdown' => json_encode([
                        'application_fee' => $applicationFee,
                        'processing_fee' => $processingFee
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                // Fallback: Store fee information in activity_log (not application_data)
                $activityLog = json_decode($application->activity_log, true) ?? [];
                $activityLog[] = [
                    'timestamp' => now()->toIso8601String(),
                    'action' => 'fee_record_created',
                    'fees' => [
                        'application_fee' => $applicationFee,
                        'processing_fee' => $processingFee,
                        'total' => $totalFee,
                        'status' => 'pending'
                    ]
                ];
                
                // Update using the correct column name
                DB::table('admission_applications')
                    ->where('id', $application->id)
                    ->update([
                        'activity_log' => json_encode($activityLog),
                        'application_fee_amount' => $totalFee,
                        'application_fee_paid' => false,
                        'updated_at' => now()
                    ]);
            }
            
            Log::info('Application fee record created', [
                'application_id' => $application->id,
                'amount' => $totalFee
            ]);
            
        } catch (Exception $e) {
            // Don't fail the application if fee creation fails
            Log::error('Failed to create application fee record', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
            
            // Try a simpler update that should definitely work
            try {
                DB::table('admission_applications')
                    ->where('id', $application->id)
                    ->update([
                        'application_fee_amount' => $applicationFee + $processingFee,
                        'application_fee_paid' => false,
                        'updated_at' => now()
                    ]);
            } catch (Exception $e2) {
                Log::error('Could not even update fee amount', [
                    'error' => $e2->getMessage()
                ]);
            }
        }
    }

    /**
     * Generate unique application number
     */
    private function generateApplicationNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        $lastApp = DB::table('admission_applications')
            ->where('application_number', 'LIKE', "APP{$year}{$month}%")
            ->orderByDesc('application_number')
            ->first();

        if ($lastApp) {
            $lastNumber = intval(substr($lastApp->application_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf("APP%s%s%04d", $year, $month, $newNumber);
    }

    /**
     * Create application checklist items
     */
    private function createApplicationChecklist($application): void
    {
        try {
            // First, check what item_types are allowed in the database
            $allowedTypes = ['form', 'document', 'test', 'verification', 'other'];
            
            $items = [
                'Personal Information' => ['required' => true, 'order' => 1, 'type' => 'form'],
                'Contact Information' => ['required' => true, 'order' => 2, 'type' => 'form'],
                'Educational Background' => ['required' => true, 'order' => 3, 'type' => 'form'],
                'Test Scores' => ['required' => false, 'order' => 4, 'type' => 'test'],
                'Personal Essay' => ['required' => true, 'order' => 5, 'type' => 'document'],
                'Transcripts' => ['required' => true, 'order' => 6, 'type' => 'document'],
                'Recommendation Letters' => ['required' => false, 'order' => 7, 'type' => 'document'],
                'Application Fee' => ['required' => true, 'order' => 8, 'type' => 'other'], // Changed from 'payment' to 'other'
            ];

            // Adjust requirements based on application type
            if ($application->application_type === 'graduate') {
                $items['Recommendation Letters']['required'] = true;
                $items['Resume/CV'] = ['required' => true, 'order' => 9, 'type' => 'document'];
                $items['Statement of Purpose'] = ['required' => true, 'order' => 10, 'type' => 'document'];
            }

            if ($application->application_type === 'international') {
                $items['TOEFL/IELTS Score'] = ['required' => true, 'order' => 11, 'type' => 'test'];
                $items['Financial Documentation'] = ['required' => true, 'order' => 12, 'type' => 'document'];
                $items['Passport Copy'] = ['required' => true, 'order' => 13, 'type' => 'document'];
            }

            foreach ($items as $itemName => $config) {
                // Ensure the type is allowed
                $itemType = in_array($config['type'], $allowedTypes) ? $config['type'] : 'other';
                
                DB::table('application_checklist_items')->insert([
                    'application_id' => $application->id,
                    'item_name' => $itemName,
                    'item_type' => $itemType,
                    'is_required' => $config['required'],
                    'is_completed' => false,
                    'sort_order' => $config['order'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            Log::info('Application checklist created successfully', [
                'application_id' => $application->id,
                'items_count' => count($items)
            ]);
            
        } catch (Exception $e) {
            // Log the error but don't fail the entire application
            Log::error('Failed to create complete checklist', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
            
            // Create at least a basic checklist entry so the application isn't broken
            try {
                DB::table('application_checklist_items')->insert([
                    'application_id' => $application->id,
                    'item_name' => 'Application Form',
                    'item_type' => 'form',
                    'is_required' => true,
                    'is_completed' => false,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } catch (Exception $e2) {
                Log::error('Could not create even basic checklist', [
                    'error' => $e2->getMessage()
                ]);
            }
        }
    }

    /**
     * Verify application ownership
     */
    private function verifyApplicationOwnership(AdmissionApplication $application): bool
    {
        if (Auth::check()) {
            return $application->user_id === Auth::id() || 
                   $application->email === Auth::user()->email;
        }

        return false;
    }

    /**
     * Determine next incomplete section
     */
    private function determineNextSection(AdmissionApplication $application): string
    {
        // Check which fields are filled to determine the next step
        if (empty($application->first_name) || empty($application->last_name)) {
            return 'personal';
        }

        if (empty($application->current_address) || empty($application->phone_primary)) {
            return 'contact';
        }

        if (empty($application->previous_institution)) {
            return 'academic';
        }

        if ($application->application_type !== 'transfer' && empty($application->test_scores)) {
            return 'test-scores';
        }

        if (empty($application->personal_statement)) {
            return 'essays';
        }

        $documentCount = DB::table('application_documents')
            ->where('application_id', $application->id)
            ->count();
            
        if ($documentCount == 0) {
            return 'documents';
        }

        return 'review';
    }

    /**
     * Get application timeline
     */
    private function getApplicationTimeline(AdmissionApplication $application): array
    {
        $timeline = [];

        // Application started
        $timeline[] = [
            'date' => $application->created_at ?? now(),
            'event' => 'Application Started',
            'status' => 'completed',
            'icon' => 'fa-play-circle',
            'color' => 'primary'
        ];

        // Application submitted
        if ($application->submitted_at) {
            $timeline[] = [
                'date' => $application->submitted_at,
                'event' => 'Application Submitted',
                'status' => 'completed',
                'icon' => 'fa-check-circle',
                'color' => 'success'
            ];
        } else {
            $timeline[] = [
                'date' => null,
                'event' => 'Application Submitted',
                'status' => 'pending',
                'icon' => 'fa-circle',
                'color' => 'secondary'
            ];
        }

        // Under review
        if (in_array($application->status, ['under_review', 'committee_review', 'decision_pending'])) {
            $timeline[] = [
                'date' => $application->reviewed_at ?? $application->updated_at,
                'event' => 'Under Review',
                'status' => 'current',
                'icon' => 'fa-search',
                'color' => 'info'
            ];
        } else if ($application->reviewed_at) {
            $timeline[] = [
                'date' => $application->reviewed_at,
                'event' => 'Review Completed',
                'status' => 'completed',
                'icon' => 'fa-check-circle',
                'color' => 'success'
            ];
        } else {
            $timeline[] = [
                'date' => null,
                'event' => 'Under Review',
                'status' => 'pending',
                'icon' => 'fa-circle',
                'color' => 'secondary'
            ];
        }

        // Decision made
        if ($application->decision_date) {
            $eventText = 'Decision: ' . ucfirst($application->decision ?? 'Pending');
            $color = match($application->decision) {
                'admit' => 'success',
                'deny' => 'danger',
                'waitlist' => 'warning',
                default => 'secondary'
            };
            
            $timeline[] = [
                'date' => $application->decision_date,
                'event' => $eventText,
                'status' => 'completed',
                'icon' => 'fa-gavel',
                'color' => $color
            ];
        } else {
            $timeline[] = [
                'date' => null,
                'event' => 'Decision Made',
                'status' => 'pending',
                'icon' => 'fa-circle',
                'color' => 'secondary'
            ];
        }

        return $timeline;
    }

    /**
     * Get next steps for application
     */
    private function getNextSteps(AdmissionApplication $application): array
    {
        $steps = [];

        switch ($application->status) {
            case 'draft':
                $steps[] = [
                    'action' => 'Complete all required sections of the application',
                    'priority' => 'high',
                    'icon' => 'fa-edit'
                ];
                $steps[] = [
                    'action' => 'Upload all required documents',
                    'priority' => 'high',
                    'icon' => 'fa-file-upload'
                ];
                $steps[] = [
                    'action' => 'Submit your application before the deadline',
                    'priority' => 'high',
                    'icon' => 'fa-paper-plane'
                ];
                break;

            case 'submitted':
            case 'under_review':
                $steps[] = [
                    'action' => 'Your application is being reviewed',
                    'priority' => 'info',
                    'icon' => 'fa-hourglass-half'
                ];
                $steps[] = [
                    'action' => 'Check your email regularly for updates',
                    'priority' => 'medium',
                    'icon' => 'fa-envelope'
                ];
                $steps[] = [
                    'action' => 'You will be notified once a decision is made',
                    'priority' => 'low',
                    'icon' => 'fa-bell'
                ];
                break;

            case 'admitted':
                if (!$application->enrollment_confirmed) {
                    $steps[] = [
                        'action' => 'Accept or decline your admission offer',
                        'priority' => 'urgent',
                        'icon' => 'fa-check-square'
                    ];
                    $steps[] = [
                        'action' => 'Pay enrollment deposit if accepting',
                        'priority' => 'high',
                        'icon' => 'fa-dollar-sign'
                    ];
                    $steps[] = [
                        'action' => 'Complete enrollment requirements',
                        'priority' => 'medium',
                        'icon' => 'fa-tasks'
                    ];
                }
                break;

            case 'waitlisted':
                $steps[] = [
                    'action' => 'You have been placed on the waitlist',
                    'priority' => 'info',
                    'icon' => 'fa-list'
                ];
                $steps[] = [
                    'action' => 'We will notify you if a spot becomes available',
                    'priority' => 'low',
                    'icon' => 'fa-clock'
                ];
                $steps[] = [
                    'action' => 'Consider submitting additional materials if applicable',
                    'priority' => 'medium',
                    'icon' => 'fa-plus-circle'
                ];
                break;
        }

        return $steps;
    }

    /**
     * Get pending actions for application
     */
    private function getPendingActions(AdmissionApplication $application): array
    {
        $actions = [];

        // Check for missing documents
        $checklistItems = DB::table('application_checklist_items')
            ->where('application_id', $application->id)
            ->where('is_required', true)
            ->where('is_completed', false)
            ->get();

        foreach ($checklistItems as $item) {
            $actions[] = [
                'type' => 'checklist',
                'description' => 'Complete: ' . $item->item_name,
                'priority' => 'high',
                'icon' => 'fa-check-circle'
            ];
        }

        // Check for enrollment confirmation
        if ($application->decision === 'admit' && !$application->enrollment_confirmed) {
            $actions[] = [
                'type' => 'enrollment',
                'description' => 'Confirm your enrollment',
                'priority' => 'urgent',
                'deadline' => $application->enrollment_deadline,
                'icon' => 'fa-user-graduate'
            ];
        }

        // Check for payment
        if ($application->payment_status === 'pending') {
            $actions[] = [
                'type' => 'payment',
                'description' => 'Complete application fee payment',
                'priority' => 'high',
                'icon' => 'fa-credit-card'
            ];
        }

        return $actions;
    }
}