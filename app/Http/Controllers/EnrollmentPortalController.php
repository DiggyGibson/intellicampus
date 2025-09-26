<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\EnrollmentConfirmationService;
use App\Services\FinancialIntegrationService;
use App\Services\DocumentVerificationService;
use App\Services\ApplicationNotificationService;
use App\Models\AdmissionApplication;
use App\Models\EnrollmentConfirmation;
use App\Models\ApplicationFee;
use App\Models\ApplicationDocument;
use App\Models\Student;
use App\Models\AcademicTerm;
use App\Models\Course;
use App\Models\Housing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

class EnrollmentPortalController extends Controller
{
    protected $enrollmentService;
    protected $financialService;
    protected $documentService;
    protected $notificationService;

    /**
     * Enrollment steps
     */
    private const ENROLLMENT_STEPS = [
        'decision' => 'Review Admission Decision',
        'accept' => 'Accept/Decline Offer',
        'deposit' => 'Pay Enrollment Deposit',
        'documents' => 'Submit Final Documents',
        'housing' => 'Apply for Housing',
        'orientation' => 'Register for Orientation',
        'courses' => 'Course Registration',
        'complete' => 'Enrollment Complete',
    ];

    /**
     * Required enrollment documents
     */
    private const ENROLLMENT_DOCUMENTS = [
        'final_transcript' => [
            'name' => 'Final Official Transcript',
            'required' => true,
            'deadline_days' => 60,
        ],
        'health_form' => [
            'name' => 'Health Information Form',
            'required' => true,
            'deadline_days' => 30,
        ],
        'immunization_records' => [
            'name' => 'Immunization Records',
            'required' => true,
            'deadline_days' => 30,
        ],
        'emergency_contact' => [
            'name' => 'Emergency Contact Form',
            'required' => true,
            'deadline_days' => 14,
        ],
        'photo_id' => [
            'name' => 'Photo for Student ID',
            'required' => true,
            'deadline_days' => 14,
        ],
        'i20_documents' => [
            'name' => 'I-20 Documents (International Students)',
            'required' => false,
            'deadline_days' => 30,
        ],
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(
        EnrollmentConfirmationService $enrollmentService,
        FinancialIntegrationService $financialService,
        DocumentVerificationService $documentService,
        ApplicationNotificationService $notificationService
    ) {
        $this->enrollmentService = $enrollmentService;
        $this->financialService = $financialService;
        $this->documentService = $documentService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display enrollment dashboard.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        try {
            // Get application from session or request
            $application = $this->getAdmittedApplication($request);
            
            if (!$application) {
                return redirect()->route('admissions.portal.index')
                    ->with('error', 'No admission offer found. Please check your application status.');
            }

            // Get or create enrollment confirmation record
            $enrollment = EnrollmentConfirmation::firstOrCreate(
                ['application_id' => $application->id],
                [
                    'decision' => 'pending',
                    'enrollment_deadline' => $application->enrollment_deadline ?? Carbon::now()->addDays(30),
                    'deposit_amount' => $this->enrollmentService->calculateDepositAmount($application),
                ]
            );

            // Get enrollment progress
            $progress = $this->getEnrollmentProgress($enrollment);
            
            // Get important dates
            $importantDates = $this->getImportantDates($application, $enrollment);
            
            // Get pending tasks
            $pendingTasks = $this->getPendingTasks($enrollment);
            
            // Check if enrollment is expired
            $isExpired = $enrollment->enrollment_deadline < now() && $enrollment->decision === 'pending';

            return view('enrollment.dashboard', compact(
                'application',
                'enrollment',
                'progress',
                'importantDates',
                'pendingTasks',
                'isExpired'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load enrollment dashboard', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('admissions.portal.index')
                ->with('error', 'Unable to access enrollment portal. Please contact admissions office.');
        }
    }

    /**
     * Accept admission offer.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function acceptOffer(Request $request)
    {
        DB::beginTransaction();

        try {
            $application = $this->getAdmittedApplication($request);
            
            if (!$application) {
                throw new Exception('No admission offer found');
            }

            // Validate request
            $request->validate([
                'confirm_acceptance' => 'required|accepted',
                'understand_deposit' => 'required|accepted',
                'agree_terms' => 'required|accepted',
            ]);

            // Get enrollment record
            $enrollment = EnrollmentConfirmation::where('application_id', $application->id)
                ->firstOrFail();

            // Check if already decided
            if ($enrollment->decision !== 'pending') {
                return redirect()->route('enrollment.dashboard')
                    ->with('warning', 'You have already responded to the admission offer.');
            }

            // Check deadline
            if ($enrollment->enrollment_deadline < now()) {
                return redirect()->route('enrollment.dashboard')
                    ->with('error', 'The enrollment deadline has passed. Please contact admissions office.');
            }

            // Update enrollment decision
            $enrollment->decision = 'accept';
            $enrollment->decision_date = now();
            $enrollment->save();

            // Update application
            $application->enrollment_confirmed = true;
            $application->enrollment_confirmation_date = now();
            $application->save();

            // Send confirmation email
            $this->notificationService->sendEnrollmentAcceptance($application->id);

            // Log activity
            Log::info('Enrollment offer accepted', [
                'application_id' => $application->id,
                'enrollment_id' => $enrollment->id,
            ]);

            DB::commit();

            return redirect()->route('enrollment.deposit')
                ->with('success', 'Congratulations! You have accepted the admission offer. Please proceed to pay the enrollment deposit.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to accept enrollment offer', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to process your acceptance. Please try again.');
        }
    }

    /**
     * Decline admission offer.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function declineOffer(Request $request)
    {
        DB::beginTransaction();

        try {
            $application = $this->getAdmittedApplication($request);
            
            if (!$application) {
                throw new Exception('No admission offer found');
            }

            // Validate request
            $request->validate([
                'decline_reason' => 'required|string|max:500',
                'confirm_decline' => 'required|accepted',
            ]);

            // Get enrollment record
            $enrollment = EnrollmentConfirmation::where('application_id', $application->id)
                ->firstOrFail();

            // Check if already decided
            if ($enrollment->decision !== 'pending') {
                return redirect()->route('enrollment.dashboard')
                    ->with('warning', 'You have already responded to the admission offer.');
            }

            // Decline enrollment
            $confirmedEnrollment = $this->enrollmentService->declineEnrollment(
                $application->id,
                $request->decline_reason
            );

            // Send confirmation email
            $this->notificationService->sendEnrollmentDeclined($application->id);

            // Clear session
            Session::forget(['enrollment_application_id', 'enrollment_application_uuid']);

            DB::commit();

            return redirect()->route('admissions.portal.index')
                ->with('info', 'Your decision has been recorded. We wish you the best in your academic journey.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to decline enrollment offer', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to process your decision. Please try again.');
        }
    }

    /**
     * Display enrollment deposit payment page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function payDeposit(Request $request)
    {
        try {
            $application = $this->getAdmittedApplication($request);
            $enrollment = EnrollmentConfirmation::where('application_id', $application->id)
                ->firstOrFail();

            // Check if deposit already paid
            if ($enrollment->deposit_paid) {
                return redirect()->route('enrollment.dashboard')
                    ->with('info', 'Enrollment deposit has already been paid.');
            }

            // Check if offer was accepted
            if ($enrollment->decision !== 'accept') {
                return redirect()->route('enrollment.dashboard')
                    ->with('warning', 'Please accept the admission offer before paying the deposit.');
            }

            // Get payment options
            $paymentMethods = $this->financialService->getAvailablePaymentMethods();
            
            // Check for existing payment plans
            $paymentPlan = $this->financialService->checkPaymentPlan($application->id);

            return view('enrollment.deposit', compact(
                'application',
                'enrollment',
                'paymentMethods',
                'paymentPlan'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load deposit page', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('enrollment.dashboard')
                ->with('error', 'Unable to process deposit payment.');
        }
    }

    /**
     * Process enrollment deposit payment.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processDeposit(Request $request)
    {
        DB::beginTransaction();

        try {
            $application = $this->getAdmittedApplication($request);
            $enrollment = EnrollmentConfirmation::where('application_id', $application->id)
                ->firstOrFail();

            // Validate payment data
            $request->validate([
                'payment_method' => 'required|string',
                'amount' => 'required|numeric|min:' . $enrollment->deposit_amount,
            ]);

            // Process payment
            $payment = $this->financialService->processEnrollmentDeposit(
                $application->id,
                [
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'card_number' => $request->card_number ?? null,
                    'mobile_number' => $request->mobile_number ?? null,
                ]
            );

            // Update enrollment record
            $enrollment->deposit_paid = true;
            $enrollment->deposit_paid_date = now();
            $enrollment->deposit_transaction_id = $payment['transaction_id'];
            $enrollment->save();

            // Send receipt
            $this->notificationService->sendDepositReceipt($application->id, $payment);

            DB::commit();

            return redirect()->route('enrollment.documents')
                ->with('success', 'Enrollment deposit paid successfully! Receipt has been sent to your email.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Deposit payment failed', [
                'error' => $e->getMessage(),
                'application_id' => $application->id ?? null,
            ]);

            return redirect()->back()
                ->with('error', 'Payment processing failed. Please try again or contact support.');
        }
    }

    /**
     * Display enrollment documents upload page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function uploadDocuments(Request $request)
    {
        try {
            $application = $this->getAdmittedApplication($request);
            $enrollment = EnrollmentConfirmation::where('application_id', $application->id)
                ->firstOrFail();

            // Get required documents checklist
            $documentChecklist = $this->getEnrollmentDocumentChecklist($application, $enrollment);
            
            // Get uploaded documents
            $uploadedDocuments = ApplicationDocument::where('application_id', $application->id)
                ->whereIn('document_type', array_keys(self::ENROLLMENT_DOCUMENTS))
                ->get();

            return view('enrollment.documents', compact(
                'application',
                'enrollment',
                'documentChecklist',
                'uploadedDocuments'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load documents page', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('enrollment.dashboard')
                ->with('error', 'Unable to access documents page.');
        }
    }

    /**
     * Complete enrollment checklist.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function completeChecklist(Request $request)
    {
        DB::beginTransaction();

        try {
            $application = $this->getAdmittedApplication($request);
            $enrollment = EnrollmentConfirmation::where('application_id', $application->id)
                ->firstOrFail();

            // Validate checklist items
            $request->validate([
                'health_form_submitted' => 'required|accepted',
                'immunization_submitted' => 'required|accepted',
                'housing_applied' => 'nullable|boolean',
                'orientation_registered' => 'required|accepted',
                'emergency_contact_submitted' => 'required|accepted',
            ]);

            // Update enrollment checklist
            $enrollment->health_form_submitted = true;
            $enrollment->immunization_submitted = true;
            $enrollment->housing_applied = $request->housing_applied ?? false;
            $enrollment->orientation_registered = true;
            
            // Check if all requirements are met
            if ($this->checkEnrollmentComplete($enrollment)) {
                // Create student account
                $student = $this->enrollmentService->generateStudentAccount($enrollment->id);
                
                $enrollment->student_account_created = true;
                $enrollment->student_record_id = $student->id;
                $enrollment->student_id = $student->student_id;
            }
            
            $enrollment->save();

            DB::commit();

            if ($enrollment->student_account_created) {
                return redirect()->route('enrollment.complete')
                    ->with('success', 'Enrollment completed successfully! Your student ID is: ' . $enrollment->student_id);
            }

            return redirect()->route('enrollment.dashboard')
                ->with('success', 'Checklist updated successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to complete checklist', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update checklist. Please try again.');
        }
    }

    /**
     * Display housing application page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function applyForHousing(Request $request)
    {
        try {
            $application = $this->getAdmittedApplication($request);
            $enrollment = EnrollmentConfirmation::where('application_id', $application->id)
                ->firstOrFail();

            // Check if housing already applied
            if ($enrollment->housing_applied) {
                return redirect()->route('enrollment.dashboard')
                    ->with('info', 'You have already applied for housing.');
            }

            // Get available housing options
            $housingOptions = Cache::remember('housing_options_' . $application->term_id, 3600, function () use ($application) {
                return Housing::where('is_available', true)
                    ->where('term_id', $application->term_id)
                    ->get();
            });

            return view('enrollment.housing', compact(
                'application',
                'enrollment',
                'housingOptions'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load housing page', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('enrollment.dashboard')
                ->with('error', 'Unable to access housing application.');
        }
    }

    /**
     * Register for orientation.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function registerOrientation(Request $request)
    {
        try {
            $application = $this->getAdmittedApplication($request);
            $enrollment = EnrollmentConfirmation::where('application_id', $application->id)
                ->firstOrFail();

            if ($request->isMethod('post')) {
                $request->validate([
                    'orientation_session' => 'required|string',
                    'dietary_restrictions' => 'nullable|string|max:500',
                    'accessibility_needs' => 'nullable|string|max:500',
                    'emergency_contact_name' => 'required|string|max:200',
                    'emergency_contact_phone' => 'required|string|max:20',
                ]);

                // Register for orientation
                $enrollment->orientation_registered = true;
                $enrollment->orientation_date = $request->orientation_session;
                $enrollment->save();

                // Send confirmation
                $this->notificationService->sendOrientationConfirmation($application->id, $request->all());

                return redirect()->route('enrollment.dashboard')
                    ->with('success', 'Successfully registered for orientation!');
            }

            // Get available orientation sessions
            $orientationSessions = $this->getOrientationSessions($application->term_id);

            return view('enrollment.orientation', compact(
                'application',
                'enrollment',
                'orientationSessions'
            ));

        } catch (Exception $e) {
            Log::error('Failed to process orientation registration', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('enrollment.dashboard')
                ->with('error', 'Failed to register for orientation.');
        }
    }

    /**
     * Display enrollment completion page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function complete(Request $request)
    {
        try {
            $application = $this->getAdmittedApplication($request);
            $enrollment = EnrollmentConfirmation::where('application_id', $application->id)
                ->firstOrFail();

            // Check if enrollment is actually complete
            if (!$enrollment->student_account_created) {
                return redirect()->route('enrollment.dashboard')
                    ->with('warning', 'Please complete all enrollment requirements first.');
            }

            // Get student record
            $student = Student::find($enrollment->student_record_id);

            // Get next steps
            $nextSteps = [
                'Access student portal with your new student ID',
                'Register for courses during your registration window',
                'Set up your university email account',
                'Order textbooks and supplies',
                'Review financial aid package if applicable',
                'Complete any remaining health requirements',
                'Plan your arrival on campus',
            ];

            // Clear enrollment session
            Session::forget(['enrollment_application_id', 'enrollment_application_uuid']);

            return view('enrollment.complete', compact(
                'application',
                'enrollment',
                'student',
                'nextSteps'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load completion page', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('enrollment.dashboard')
                ->with('error', 'Unable to load completion page.');
        }
    }

    /**
     * Helper Methods
     */

    /**
     * Get admitted application.
     */
    private function getAdmittedApplication(Request $request)
    {
        // Try to get from request
        if ($request->has('application_uuid')) {
            $application = AdmissionApplication::where('application_uuid', $request->application_uuid)
                ->where('decision', 'admit')
                ->first();
                
            if ($application) {
                Session::put('enrollment_application_uuid', $application->application_uuid);
                return $application;
            }
        }

        // Try to get from session
        if (Session::has('enrollment_application_uuid')) {
            return AdmissionApplication::where('application_uuid', Session::get('enrollment_application_uuid'))
                ->where('decision', 'admit')
                ->first();
        }

        // Try to get by authenticated user
        if (Auth::check()) {
            return AdmissionApplication::where('user_id', Auth::id())
                ->where('decision', 'admit')
                ->orderBy('decision_date', 'desc')
                ->first();
        }

        return null;
    }

    /**
     * Get enrollment progress.
     */
    private function getEnrollmentProgress(EnrollmentConfirmation $enrollment): array
    {
        $steps = [];
        
        foreach (self::ENROLLMENT_STEPS as $key => $label) {
            $completed = false;
            
            switch ($key) {
                case 'decision':
                    $completed = true; // Always true if they're here
                    break;
                case 'accept':
                    $completed = $enrollment->decision === 'accept';
                    break;
                case 'deposit':
                    $completed = $enrollment->deposit_paid;
                    break;
                case 'documents':
                    $completed = $enrollment->health_form_submitted && $enrollment->immunization_submitted;
                    break;
                case 'housing':
                    $completed = $enrollment->housing_applied;
                    break;
                case 'orientation':
                    $completed = $enrollment->orientation_registered;
                    break;
                case 'courses':
                    $completed = false; // Implement course registration check
                    break;
                case 'complete':
                    $completed = $enrollment->student_account_created;
                    break;
            }
            
            $steps[] = [
                'key' => $key,
                'label' => $label,
                'completed' => $completed,
            ];
        }

        return $steps;
    }

    /**
     * Get important dates.
     */
    private function getImportantDates($application, $enrollment): array
    {
        return [
            'Enrollment Deadline' => $enrollment->enrollment_deadline->format('F d, Y'),
            'Deposit Deadline' => $enrollment->enrollment_deadline->subDays(14)->format('F d, Y'),
            'Document Submission' => $enrollment->enrollment_deadline->addDays(30)->format('F d, Y'),
            'Orientation Date' => $enrollment->orientation_date ?? 'TBD',
            'Move-in Date' => $enrollment->move_in_date ?? 'TBD',
            'Classes Begin' => $application->term->start_date->format('F d, Y'),
        ];
    }

    /**
     * Get pending tasks.
     */
    private function getPendingTasks(EnrollmentConfirmation $enrollment): array
    {
        $tasks = [];

        if ($enrollment->decision === 'pending') {
            $tasks[] = ['task' => 'Accept or decline admission offer', 'priority' => 'high'];
        }

        if ($enrollment->decision === 'accept' && !$enrollment->deposit_paid) {
            $tasks[] = ['task' => 'Pay enrollment deposit', 'priority' => 'high'];
        }

        if (!$enrollment->health_form_submitted) {
            $tasks[] = ['task' => 'Submit health information form', 'priority' => 'medium'];
        }

        if (!$enrollment->immunization_submitted) {
            $tasks[] = ['task' => 'Submit immunization records', 'priority' => 'medium'];
        }

        if (!$enrollment->orientation_registered) {
            $tasks[] = ['task' => 'Register for orientation', 'priority' => 'medium'];
        }

        return $tasks;
    }

    /**
     * Get enrollment document checklist.
     */
    private function getEnrollmentDocumentChecklist($application, $enrollment): array
    {
        $checklist = [];
        
        foreach (self::ENROLLMENT_DOCUMENTS as $type => $config) {
            // Skip international documents for non-international students
            if ($type === 'i20_documents' && $application->application_type !== 'international') {
                continue;
            }

            $uploaded = ApplicationDocument::where('application_id', $application->id)
                ->where('document_type', $type)
                ->whereIn('status', ['uploaded', 'verified'])
                ->exists();

            $deadline = $enrollment->enrollment_deadline->addDays($config['deadline_days']);

            $checklist[] = [
                'type' => $type,
                'name' => $config['name'],
                'required' => $config['required'],
                'uploaded' => $uploaded,
                'deadline' => $deadline->format('F d, Y'),
                'overdue' => !$uploaded && $deadline->isPast(),
            ];
        }

        return $checklist;
    }

    /**
     * Check if enrollment is complete.
     */
    private function checkEnrollmentComplete(EnrollmentConfirmation $enrollment): bool
    {
        return $enrollment->deposit_paid &&
               $enrollment->health_form_submitted &&
               $enrollment->immunization_submitted &&
               $enrollment->orientation_registered;
    }

    /**
     * Get orientation sessions.
     */
    private function getOrientationSessions($termId): array
    {
        // This would typically come from a database table
        return [
            '2025-08-15' => 'August 15, 2025 - Session A (9:00 AM - 4:00 PM)',
            '2025-08-16' => 'August 16, 2025 - Session B (9:00 AM - 4:00 PM)',
            '2025-08-20' => 'August 20, 2025 - Session C (9:00 AM - 4:00 PM)',
            '2025-08-21' => 'August 21, 2025 - International Students (8:00 AM - 5:00 PM)',
        ];
    }
}