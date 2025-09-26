<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\EntranceExamService;
use App\Services\OnlineExamService;
use App\Services\ExamProctoringService;
use App\Services\ApplicationNotificationService;
use App\Models\EntranceExam;
use App\Models\EntranceExamRegistration;
use App\Models\ExamSession;
use App\Models\ExamCenter;
use App\Models\ExamResponse;
use App\Models\ExamResponseDetail;
use App\Models\EntranceExamResult;
use App\Models\ExamCertificate;
use App\Models\AdmissionApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Exception;

class ExamPortalController extends Controller
{
    protected $examService;
    protected $onlineExamService;
    protected $proctoringService;
    protected $notificationService;

    /**
     * Exam status messages
     */
    private const EXAM_STATUS_MESSAGES = [
        'scheduled' => 'Your exam is scheduled. Please download your hall ticket.',
        'in_progress' => 'Exam is currently in progress.',
        'completed' => 'You have completed this exam.',
        'absent' => 'You were marked absent for this exam.',
        'cancelled' => 'This exam has been cancelled.',
        'results_pending' => 'Your exam has been evaluated. Results will be published soon.',
        'results_published' => 'Your exam results are available.',
    ];

    /**
     * Minimum days before exam to register
     */
    private const MIN_REGISTRATION_DAYS = 7;

    /**
     * Hall ticket availability (days before exam)
     */
    private const HALL_TICKET_AVAILABLE_DAYS = 3;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        EntranceExamService $examService,
        OnlineExamService $onlineExamService,
        ExamProctoringService $proctoringService,
        ApplicationNotificationService $notificationService
    ) {
        $this->examService = $examService;
        $this->onlineExamService = $onlineExamService;
        $this->proctoringService = $proctoringService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display available exams.
     *
     * @return \Illuminate\View\View
     */
    public function availableExams()
    {
        try {
            // Get candidate information
            $candidate = $this->getCandidateInfo();
            
            // Get available exams based on candidate's application
            $availableExams = EntranceExam::where('status', 'registration_open')
                ->where('registration_end_date', '>=', now())
                ->when($candidate['application'], function ($query) use ($candidate) {
                    return $query->where(function ($q) use ($candidate) {
                        $q->whereJsonContains('applicable_programs', $candidate['application']->program_id)
                          ->orWhereJsonContains('applicable_application_types', $candidate['application']->application_type)
                          ->orWhereNull('applicable_programs');
                    });
                })
                ->with(['term', 'sessions.center'])
                ->orderBy('exam_date')
                ->get();

            // Get candidate's existing registrations
            $myRegistrations = EntranceExamRegistration::where(function ($query) use ($candidate) {
                    if ($candidate['application']) {
                        $query->where('application_id', $candidate['application']->id);
                    }
                    if ($candidate['user']) {
                        $query->orWhere('candidate_email', $candidate['user']->email);
                    }
                })
                ->with('exam')
                ->get();

            // Filter out already registered exams
            $availableExams = $availableExams->filter(function ($exam) use ($myRegistrations) {
                return !$myRegistrations->contains('exam_id', $exam->id);
            });

            return view('exams.portal.available', compact(
                'availableExams',
                'myRegistrations',
                'candidate'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load available exams', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('admissions.portal.index')
                ->with('error', 'Unable to load available exams. Please try again later.');
        }
    }

    /**
     * Register for an exam.
     *
     * @param Request $request
     * @param int $examId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function register(Request $request, $examId)
    {
        try {
            $exam = EntranceExam::with(['sessions.center'])->findOrFail($examId);
            
            // Check if registration is open
            if ($exam->status !== 'registration_open' || 
                $exam->registration_end_date < now() ||
                $exam->registration_start_date > now()) {
                return redirect()->route('exams.available')
                    ->with('error', 'Registration is not open for this exam.');
            }

            if ($request->isMethod('post')) {
                DB::beginTransaction();

                try {
                    $validated = $request->validate([
                        'session_id' => 'required|exists:exam_sessions,id',
                        'center_preference_1' => 'required|exists:exam_centers,id',
                        'center_preference_2' => 'nullable|exists:exam_centers,id|different:center_preference_1',
                        'center_preference_3' => 'nullable|exists:exam_centers,id|different:center_preference_1,center_preference_2',
                        'requires_accommodation' => 'boolean',
                        'accommodation_type' => 'required_if:requires_accommodation,true|nullable|string',
                        'accommodation_details' => 'required_if:requires_accommodation,true|nullable|string|max:500',
                        'agree_terms' => 'required|accepted',
                    ]);

                    // Get candidate info
                    $candidate = $this->getCandidateInfo();
                    
                    // Prepare registration data
                    $registrationData = [
                        'application_id' => $candidate['application']->id ?? null,
                        'student_id' => $candidate['student']->id ?? null,
                        'email' => $candidate['user']->email ?? $validated['email'] ?? null,
                        'session_id' => $validated['session_id'],
                        'center_preferences' => [
                            $validated['center_preference_1'],
                            $validated['center_preference_2'] ?? null,
                            $validated['center_preference_3'] ?? null,
                        ],
                        'requires_accommodation' => $validated['requires_accommodation'] ?? false,
                        'accommodation_details' => $validated['requires_accommodation'] ? [
                            'type' => $validated['accommodation_type'],
                            'details' => $validated['accommodation_details'],
                        ] : null,
                    ];

                    // Register for exam
                    $registration = $this->examService->registerCandidate($examId, $registrationData);

                    // Process payment if required
                    if ($exam->fee_amount > 0) {
                        Session::put('pending_exam_payment', [
                            'registration_id' => $registration->id,
                            'amount' => $exam->fee_amount,
                        ]);
                        
                        DB::commit();
                        
                        return redirect()->route('exams.payment', ['registration' => $registration->id])
                            ->with('info', 'Registration successful! Please complete the payment to confirm.');
                    }

                    DB::commit();

                    // Send confirmation
                    $this->notificationService->sendExamRegistrationConfirmation($registration->id);

                    return redirect()->route('exams.my-registrations')
                        ->with('success', 'Successfully registered for the exam! Check your email for confirmation.');

                } catch (Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            // Get available sessions and centers
            $sessions = $exam->sessions()
                ->where('status', 'registration_open')
                ->where('capacity', '>', DB::raw('registered_count'))
                ->get();

            $centers = ExamCenter::where('is_active', true)
                ->whereIn('center_type', ['internal', 'external'])
                ->orderBy('city')
                ->get();

            // Get candidate info for pre-filling
            $candidate = $this->getCandidateInfo();

            return view('exams.portal.register', compact(
                'exam',
                'sessions',
                'centers',
                'candidate'
            ));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('exams.available')
                ->with('error', 'Exam not found.');
        } catch (Exception $e) {
            Log::error('Exam registration failed', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Registration failed. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display my exam registrations.
     *
     * @return \Illuminate\View\View
     */
    public function myRegistrations()
    {
        try {
            $candidate = $this->getCandidateInfo();
            
            // Get all registrations
            $registrations = EntranceExamRegistration::where(function ($query) use ($candidate) {
                    if ($candidate['application']) {
                        $query->where('application_id', $candidate['application']->id);
                    }
                    if ($candidate['user']) {
                        $query->orWhere('candidate_email', $candidate['user']->email);
                    }
                    if ($candidate['student']) {
                        $query->orWhere('student_id', $candidate['student']->id);
                    }
                })
                ->with([
                    'exam',
                    'session.center',
                    'seatAllocation',
                    'examResponse',
                    'result'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            // Categorize registrations
            $upcoming = $registrations->filter(function ($reg) {
                return $reg->exam->exam_date > now() && $reg->registration_status === 'confirmed';
            });

            $completed = $registrations->filter(function ($reg) {
                return $reg->examResponse && $reg->examResponse->status === 'submitted';
            });

            $pending = $registrations->filter(function ($reg) {
                return $reg->registration_status === 'pending' || !$reg->fee_paid;
            });

            return view('exams.portal.my-registrations', compact(
                'registrations',
                'upcoming',
                'completed',
                'pending'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load registrations', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('exams.available')
                ->with('error', 'Unable to load your registrations.');
        }
    }

    /**
     * Download hall ticket.
     *
     * @param int $registrationId
     * @return \Illuminate\Http\Response
     */
    public function downloadHallTicket($registrationId)
    {
        try {
            $registration = EntranceExamRegistration::with([
                'exam',
                'session.center',
                'seatAllocation',
                'application'
            ])->findOrFail($registrationId);

            // Verify ownership
            if (!$this->verifyRegistrationOwnership($registration)) {
                abort(403, 'Unauthorized');
            }

            // Check if hall ticket is available
            $exam = $registration->exam;
            $daysUntilExam = Carbon::parse($exam->exam_date)->diffInDays(now());
            
            if ($daysUntilExam > self::HALL_TICKET_AVAILABLE_DAYS) {
                return redirect()->back()
                    ->with('warning', 'Hall ticket will be available ' . self::HALL_TICKET_AVAILABLE_DAYS . ' days before the exam.');
            }

            // Check if registration is confirmed
            if ($registration->registration_status !== 'confirmed') {
                return redirect()->back()
                    ->with('error', 'Please complete your registration first.');
            }

            // Check if seat is allocated
            if (!$registration->seatAllocation) {
                return redirect()->back()
                    ->with('warning', 'Seat allocation pending. Please check back later.');
            }

            // Generate hall ticket
            $hallTicket = $this->generateHallTicket($registration);

            return $hallTicket->download('hall_ticket_' . $registration->registration_number . '.pdf');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('exams.my-registrations')
                ->with('error', 'Registration not found.');
        } catch (Exception $e) {
            Log::error('Failed to generate hall ticket', [
                'registration_id' => $registrationId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to generate hall ticket. Please try again.');
        }
    }

    /**
     * Start online exam.
     *
     * @param int $registrationId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function startOnlineExam($registrationId)
    {
        try {
            $registration = EntranceExamRegistration::with([
                'exam',
                'session',
                'examResponse'
            ])->findOrFail($registrationId);

            // Verify ownership
            if (!$this->verifyRegistrationOwnership($registration)) {
                abort(403, 'Unauthorized');
            }

            // Check if exam is online
            if (!in_array($registration->exam->delivery_mode, ['online_proctored', 'online_unproctored'])) {
                return redirect()->route('exams.my-registrations')
                    ->with('error', 'This is not an online exam.');
            }

            // Check if exam can be started
            $session = $registration->session;
            $now = Carbon::now();
            $examStart = Carbon::parse($session->session_date . ' ' . $session->start_time);
            $examEnd = Carbon::parse($session->session_date . ' ' . $session->end_time);

            if ($now < $examStart->subMinutes(15)) {
                return redirect()->route('exams.my-registrations')
                    ->with('warning', 'Exam has not started yet. You can start 15 minutes before scheduled time.');
            }

            if ($now > $examEnd) {
                return redirect()->route('exams.my-registrations')
                    ->with('error', 'Exam time has ended.');
            }

            // Check if already started
            if ($registration->examResponse && $registration->examResponse->status === 'submitted') {
                return redirect()->route('exams.my-registrations')
                    ->with('info', 'You have already completed this exam.');
            }

            // Initialize or resume exam
            if (!$registration->examResponse || $registration->examResponse->status === 'not_started') {
                $examData = $this->onlineExamService->initializeOnlineExam($registrationId);
            } else {
                $examData = $this->onlineExamService->resumeExam($registration->examResponse->id);
            }

            // Start proctoring if required
            if ($registration->exam->delivery_mode === 'online_proctored') {
                $this->proctoringService->startProctoring($examData['response_id']);
            }

            return view('exams.portal.online-exam', compact(
                'registration',
                'examData'
            ));

        } catch (Exception $e) {
            Log::error('Failed to start online exam', [
                'registration_id' => $registrationId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('exams.my-registrations')
                ->with('error', 'Failed to start exam. Please contact support.');
        }
    }

    /**
     * View exam results.
     *
     * @param int $registrationId
     * @return \Illuminate\View\View
     */
    public function viewResults($registrationId)
    {
        try {
            $registration = EntranceExamRegistration::with([
                'exam',
                'result',
                'certificate'
            ])->findOrFail($registrationId);

            // Verify ownership
            if (!$this->verifyRegistrationOwnership($registration)) {
                abort(403, 'Unauthorized');
            }

            // Check if results are published
            $result = $registration->result;
            
            if (!$result || !$result->is_published) {
                return redirect()->route('exams.my-registrations')
                    ->with('info', 'Results have not been published yet.');
            }

            // Get detailed scores if available
            $detailedScores = null;
            if ($registration->exam->show_detailed_results) {
                $detailedScores = $this->getDetailedScores($registration);
            }

            // Check for certificate
            $certificate = ExamCertificate::where('registration_id', $registrationId)
                ->where('result_id', $result->id)
                ->first();

            return view('exams.portal.results', compact(
                'registration',
                'result',
                'detailedScores',
                'certificate'
            ));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('exams.my-registrations')
                ->with('error', 'Registration not found.');
        } catch (Exception $e) {
            Log::error('Failed to load results', [
                'registration_id' => $registrationId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('exams.my-registrations')
                ->with('error', 'Unable to load results.');
        }
    }

    /**
     * Submit exam answer (AJAX).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitAnswer(Request $request)
    {
        try {
            $validated = $request->validate([
                'response_id' => 'required|exists:exam_responses,id',
                'question_id' => 'required|exists:exam_questions,id',
                'answer' => 'required',
                'time_spent' => 'nullable|integer|min:0',
            ]);

            // Verify ownership of response
            $response = ExamResponse::findOrFail($validated['response_id']);
            $registration = $response->registration;
            
            if (!$this->verifyRegistrationOwnership($registration)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Save answer
            $result = $this->onlineExamService->saveResponse(
                $validated['response_id'],
                $validated['question_id'],
                $validated['answer'],
                $validated['time_spent'] ?? 0
            );

            return response()->json([
                'success' => true,
                'message' => 'Answer saved',
                'data' => $result,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to save answer', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save answer',
            ], 500);
        }
    }

    /**
     * Navigate between questions (AJAX).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function navigateQuestion(Request $request)
    {
        try {
            $validated = $request->validate([
                'response_id' => 'required|exists:exam_responses,id',
                'direction' => 'required|in:next,previous,jump',
                'question_number' => 'required_if:direction,jump|integer|min:1',
            ]);

            $response = ExamResponse::findOrFail($validated['response_id']);
            
            if (!$this->verifyRegistrationOwnership($response->registration)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $question = $this->onlineExamService->navigateQuestion(
                $validated['response_id'],
                $validated['direction'],
                $validated['question_number'] ?? null
            );

            return response()->json([
                'success' => true,
                'question' => $question,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to navigate question', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Navigation failed',
            ], 500);
        }
    }

    /**
     * Mark question for review (AJAX).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markForReview(Request $request)
    {
        try {
            $validated = $request->validate([
                'response_id' => 'required|exists:exam_responses,id',
                'question_id' => 'required|exists:exam_questions,id',
                'mark' => 'required|boolean',
            ]);

            $response = ExamResponse::findOrFail($validated['response_id']);
            
            if (!$this->verifyRegistrationOwnership($response->registration)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $this->onlineExamService->markForReview(
                $validated['response_id'],
                $validated['question_id'],
                $validated['mark']
            );

            return response()->json([
                'success' => true,
                'message' => $validated['mark'] ? 'Marked for review' : 'Review mark removed',
            ]);

        } catch (Exception $e) {
            Log::error('Failed to mark for review', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark for review',
            ], 500);
        }
    }

    /**
     * Finish exam.
     *
     * @param Request $request
     * @param int $registrationId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function finishExam(Request $request, $registrationId)
    {
        DB::beginTransaction();

        try {
            $registration = EntranceExamRegistration::with(['examResponse'])->findOrFail($registrationId);
            
            if (!$this->verifyRegistrationOwnership($registration)) {
                abort(403, 'Unauthorized');
            }

            $request->validate([
                'confirm_submit' => 'required|accepted',
            ]);

            // Submit exam
            $this->onlineExamService->submitExam($registration->examResponse->id);

            // Stop proctoring if active
            if ($registration->exam->delivery_mode === 'online_proctored') {
                $this->proctoringService->stopProctoring($registration->examResponse->id);
            }

            DB::commit();

            return redirect()->route('exams.my-registrations')
                ->with('success', 'Exam submitted successfully! Results will be available soon.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to finish exam', [
                'registration_id' => $registrationId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to submit exam. Please try again.');
        }
    }

    /**
     * Download certificate.
     *
     * @param int $certificateId
     * @return \Illuminate\Http\Response
     */
    public function downloadCertificate($certificateId)
    {
        try {
            $certificate = ExamCertificate::with(['registration', 'result'])->findOrFail($certificateId);
            
            // Verify ownership
            if (!$this->verifyRegistrationOwnership($certificate->registration)) {
                abort(403, 'Unauthorized');
            }

            // Generate or retrieve certificate PDF
            if (!$certificate->file_path || !Storage::exists($certificate->file_path)) {
                $pdf = $this->generateCertificate($certificate);
                $path = 'certificates/exams/' . $certificate->certificate_number . '.pdf';
                Storage::put($path, $pdf->output());
                
                $certificate->file_path = $path;
                $certificate->save();
            }

            // Update download count
            $certificate->increment('download_count');
            
            if (!$certificate->first_downloaded_at) {
                $certificate->first_downloaded_at = now();
                $certificate->save();
            }

            return Storage::download($certificate->file_path, 'certificate_' . $certificate->certificate_number . '.pdf');

        } catch (Exception $e) {
            Log::error('Failed to download certificate', [
                'certificate_id' => $certificateId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to download certificate.');
        }
    }

    /**
     * Helper Methods
     */

    /**
     * Get candidate information.
     */
    private function getCandidateInfo(): array
    {
        $info = [
            'user' => null,
            'application' => null,
            'student' => null,
        ];

        if (Auth::check()) {
            $info['user'] = Auth::user();
            
            // Check for application
            $info['application'] = AdmissionApplication::where('user_id', Auth::id())
                ->orWhere('email', Auth::user()->email)
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Check for student record
            $info['student'] = Student::where('user_id', Auth::id())
                ->orWhere('email', Auth::user()->email)
                ->first();
        }

        return $info;
    }

    /**
     * Verify registration ownership.
     */
    private function verifyRegistrationOwnership($registration): bool
    {
        $candidate = $this->getCandidateInfo();
        
        if ($candidate['application'] && $registration->application_id === $candidate['application']->id) {
            return true;
        }
        
        if ($candidate['student'] && $registration->student_id === $candidate['student']->id) {
            return true;
        }
        
        if ($candidate['user'] && $registration->candidate_email === $candidate['user']->email) {
            return true;
        }
        
        return false;
    }

    /**
     * Generate hall ticket PDF.
     */
    private function generateHallTicket($registration)
    {
        $data = [
            'registration' => $registration,
            'exam' => $registration->exam,
            'session' => $registration->session,
            'center' => $registration->session->center,
            'seat' => $registration->seatAllocation,
            'candidate_name' => $this->getCandidateName($registration),
            'photo' => $this->getCandidatePhoto($registration),
            'qr_code' => $this->generateQRCode($registration),
            'instructions' => $registration->exam->general_instructions,
            'rules' => $registration->exam->exam_rules,
        ];

        return PDF::loadView('exams.hall-ticket', $data);
    }

    /**
     * Get candidate name.
     */
    private function getCandidateName($registration): string
    {
        if ($registration->application) {
            return $registration->application->first_name . ' ' . $registration->application->last_name;
        }
        
        if ($registration->student) {
            return $registration->student->full_name;
        }
        
        return $registration->candidate_name ?? 'N/A';
    }

    /**
     * Get candidate photo.
     */
    private function getCandidatePhoto($registration): ?string
    {
        if ($registration->application) {
            $photo = ApplicationDocument::where('application_id', $registration->application->id)
                ->where('document_type', 'photo')
                ->first();
            
            return $photo ? Storage::url($photo->file_path) : null;
        }
        
        return null;
    }

    /**
     * Generate QR code for hall ticket.
     */
    private function generateQRCode($registration): string
    {
        $data = [
            'reg_no' => $registration->registration_number,
            'hall_ticket' => $registration->hall_ticket_number,
            'exam_code' => $registration->exam->exam_code,
        ];
        
        return base64_encode(QrCode::format('png')
            ->size(150)
            ->generate(json_encode($data)));
    }

    /**
     * Get detailed scores.
     */
    private function getDetailedScores($registration): array
    {
        $responseDetails = ExamResponseDetail::where('response_id', $registration->examResponse->id)
            ->with('question')
            ->get();
        
        $sections = [];
        
        foreach ($responseDetails as $detail) {
            $section = $detail->question->subject ?? 'General';
            
            if (!isset($sections[$section])) {
                $sections[$section] = [
                    'total' => 0,
                    'attempted' => 0,
                    'correct' => 0,
                    'wrong' => 0,
                    'marks_obtained' => 0,
                ];
            }
            
            $sections[$section]['total']++;
            
            if ($detail->status !== 'not_visited') {
                $sections[$section]['attempted']++;
            }
            
            if ($detail->is_correct) {
                $sections[$section]['correct']++;
                $sections[$section]['marks_obtained'] += $detail->marks_obtained;
            } elseif ($detail->is_correct === false) {
                $sections[$section]['wrong']++;
            }
        }
        
        return $sections;
    }

    /**
     * Generate certificate PDF.
     */
    private function generateCertificate($certificate)
    {
        $data = [
            'certificate' => $certificate,
            'registration' => $certificate->registration,
            'result' => $certificate->result,
            'exam' => $certificate->registration->exam,
            'candidate_name' => $this->getCandidateName($certificate->registration),
            'issued_date' => $certificate->issued_at->format('F d, Y'),
            'verification_url' => route('exams.verify-certificate', $certificate->verification_code),
            'qr_code' => base64_encode(QrCode::format('png')
                ->size(100)
                ->generate(route('exams.verify-certificate', $certificate->verification_code))),
        ];

        return PDF::loadView('exams.certificate', $data);
    }
}