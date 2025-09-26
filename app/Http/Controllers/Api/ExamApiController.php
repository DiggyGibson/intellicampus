<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EntranceExamService;
use App\Services\OnlineExamService;
use App\Services\ExamProctoringService;
use App\Services\ExamEvaluationService;
use App\Models\EntranceExam;
use App\Models\EntranceExamRegistration;
use App\Models\ExamSession;
use App\Models\ExamResponse;
use App\Models\ExamResponseDetail;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionPaper;
use App\Models\EntranceExamResult;
use App\Http\Resources\ExamResource;
use App\Http\Resources\ExamRegistrationResource;
use App\Http\Resources\ExamResultResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class ExamApiController extends Controller
{
    protected $examService;
    protected $onlineExamService;
    protected $proctoringService;
    protected $evaluationService;

    /**
     * API response codes
     */
    private const RESPONSE_CODES = [
        'SUCCESS' => 200,
        'CREATED' => 201,
        'BAD_REQUEST' => 400,
        'UNAUTHORIZED' => 401,
        'FORBIDDEN' => 403,
        'NOT_FOUND' => 404,
        'CONFLICT' => 409,
        'VALIDATION_ERROR' => 422,
        'SERVER_ERROR' => 500,
    ];

    /**
     * Exam session timeout (minutes)
     */
    private const EXAM_SESSION_TIMEOUT = 180;

    /**
     * Maximum question navigation attempts
     */
    private const MAX_NAVIGATION_ATTEMPTS = 1000;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        EntranceExamService $examService,
        OnlineExamService $onlineExamService,
        ExamProctoringService $proctoringService,
        ExamEvaluationService $evaluationService
    ) {
        $this->examService = $examService;
        $this->onlineExamService = $onlineExamService;
        $this->proctoringService = $proctoringService;
        $this->evaluationService = $evaluationService;
        
        // API authentication middleware
        $this->middleware('auth:sanctum')->except(['getAvailableExams']);
        
        // Rate limiting
        $this->middleware('throttle:api');
    }

    /**
     * Get available exams.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAvailableExams(Request $request): JsonResponse
    {
        try {
            $validated = Validator::make($request->all(), [
                'program_id' => 'nullable|exists:academic_programs,id',
                'exam_type' => 'nullable|string',
                'delivery_mode' => 'nullable|string',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after:from_date',
            ])->validate();

            $query = EntranceExam::where('status', 'registration_open')
                ->where('registration_end_date', '>=', now());

            if ($validated['program_id'] ?? null) {
                $query->whereJsonContains('applicable_programs', $validated['program_id']);
            }

            if ($validated['exam_type'] ?? null) {
                $query->where('exam_type', $validated['exam_type']);
            }

            if ($validated['delivery_mode'] ?? null) {
                $query->where('delivery_mode', $validated['delivery_mode']);
            }

            if ($validated['from_date'] ?? null) {
                $query->where('exam_date', '>=', $validated['from_date']);
            }

            if ($validated['to_date'] ?? null) {
                $query->where('exam_date', '<=', $validated['to_date']);
            }

            $exams = $query->with(['term', 'sessions.center'])
                ->orderBy('exam_date')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => ExamResource::collection($exams),
                'meta' => [
                    'total' => $exams->total(),
                    'per_page' => $exams->perPage(),
                    'current_page' => $exams->currentPage(),
                    'last_page' => $exams->lastPage(),
                ],
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to fetch available exams', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch available exams',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error',
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Register for an exam.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function registerForExam(Request $request): JsonResponse
    {
        try {
            $validated = Validator::make($request->all(), [
                'exam_id' => 'required|exists:entrance_exams,id',
                'session_id' => 'required|exists:exam_sessions,id',
                'center_id' => 'required|exists:exam_centers,id',
                'application_id' => 'nullable|exists:admission_applications,id',
                'candidate_name' => 'required_without:application_id|string|max:200',
                'candidate_email' => 'required_without:application_id|email',
                'candidate_phone' => 'required_without:application_id|string|max:20',
                'date_of_birth' => 'required_without:application_id|date',
                'requires_accommodation' => 'nullable|boolean',
                'accommodation_details' => 'nullable|required_if:requires_accommodation,true|array',
                'payment_method' => 'required|string',
                'payment_reference' => 'nullable|string',
            ])->validate();

            DB::beginTransaction();

            // Register for exam
            $registration = $this->examService->registerCandidate(
                $validated['exam_id'],
                $validated
            );

            // Allocate seat
            $this->examService->allocateSeat(
                $registration->id,
                $validated['session_id']
            );

            // Process payment if reference provided
            if ($validated['payment_reference'] ?? null) {
                $registration->fee_paid = true;
                $registration->payment_reference = $validated['payment_reference'];
                $registration->payment_date = now();
                $registration->registration_status = 'confirmed';
                $registration->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Successfully registered for exam',
                'data' => new ExamRegistrationResource($registration),
            ], self::RESPONSE_CODES['CREATED']);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to register for exam', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register for exam',
                'error' => $e->getMessage(),
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Start online exam.
     *
     * @param Request $request
     * @param int $registrationId
     * @return JsonResponse
     */
    public function startExam(Request $request, int $registrationId): JsonResponse
    {
        try {
            $registration = EntranceExamRegistration::findOrFail($registrationId);
            
            // Verify ownership
            if (Auth::check() && $registration->candidate_email !== Auth::user()->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to exam',
                ], self::RESPONSE_CODES['FORBIDDEN']);
            }

            // Validate exam can be started
            $session = $registration->seatAllocation->session;
            $now = Carbon::now();
            $examStart = Carbon::parse($session->session_date . ' ' . $session->start_time);
            $examEnd = Carbon::parse($session->session_date . ' ' . $session->end_time);

            if ($now < $examStart->subMinutes(15)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exam has not started yet',
                    'data' => [
                        'exam_starts_at' => $examStart->toIso8601String(),
                        'can_start_at' => $examStart->subMinutes(15)->toIso8601String(),
                    ],
                ], self::RESPONSE_CODES['BAD_REQUEST']);
            }

            if ($now > $examEnd) {
                return response()->json([
                    'success' => false,
                    'message' => 'Exam has already ended',
                ], self::RESPONSE_CODES['BAD_REQUEST']);
            }

            // Initialize exam
            $examData = $this->onlineExamService->initializeOnlineExam($registrationId);

            // Start proctoring if enabled
            if ($session->proctoring_type !== 'honor_code') {
                $proctoringSession = $this->proctoringService->startProctoring($examData['response_id']);
                $examData['proctoring'] = $proctoringSession;
            }

            // Create session token
            $token = $this->generateExamToken($registration, $examData['response_id']);

            return response()->json([
                'success' => true,
                'message' => 'Exam started successfully',
                'data' => [
                    'exam_token' => $token,
                    'response_id' => $examData['response_id'],
                    'total_questions' => $examData['total_questions'],
                    'duration_minutes' => $examData['duration_minutes'],
                    'sections' => $examData['sections'],
                    'instructions' => $examData['instructions'],
                    'proctoring_enabled' => isset($examData['proctoring']),
                    'expires_at' => Carbon::now()->addMinutes($examData['duration_minutes'])->toIso8601String(),
                ],
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to start exam', [
                'error' => $e->getMessage(),
                'registration_id' => $registrationId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start exam',
                'error' => $e->getMessage(),
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Save answer for a question.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveAnswer(Request $request): JsonResponse
    {
        try {
            $validated = Validator::make($request->all(), [
                'response_id' => 'required|exists:exam_responses,id',
                'question_id' => 'required|exists:exam_questions,id',
                'answer' => 'required',
                'time_spent' => 'nullable|integer|min:0',
                'mark_for_review' => 'nullable|boolean',
            ])->validate();

            // Verify exam token
            if (!$this->verifyExamToken($request, $validated['response_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired exam session',
                ], self::RESPONSE_CODES['UNAUTHORIZED']);
            }

            // Save answer
            $saved = $this->onlineExamService->saveResponse(
                $validated['response_id'],
                $validated['question_id'],
                $validated['answer']
            );

            // Update response detail
            if ($saved && isset($validated['mark_for_review'])) {
                $detail = ExamResponseDetail::where('response_id', $validated['response_id'])
                    ->where('question_id', $validated['question_id'])
                    ->first();
                    
                if ($detail) {
                    $detail->status = $validated['mark_for_review'] 
                        ? 'answered_marked_review' 
                        : 'answered';
                    
                    if ($validated['time_spent'] ?? null) {
                        $detail->time_spent_seconds += $validated['time_spent'];
                    }
                    
                    $detail->save();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Answer saved successfully',
                'data' => [
                    'question_id' => $validated['question_id'],
                    'saved' => $saved,
                    'timestamp' => now()->toIso8601String(),
                ],
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to save answer', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save answer',
                'error' => $e->getMessage(),
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Navigate between questions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function navigateQuestion(Request $request): JsonResponse
    {
        try {
            $validated = Validator::make($request->all(), [
                'response_id' => 'required|exists:exam_responses,id',
                'direction' => 'required|in:next,previous,jump',
                'question_number' => 'required_if:direction,jump|integer|min:1',
            ])->validate();

            // Verify exam token
            if (!$this->verifyExamToken($request, $validated['response_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired exam session',
                ], self::RESPONSE_CODES['UNAUTHORIZED']);
            }

            // Navigate question
            $question = $this->onlineExamService->navigateQuestion(
                $validated['response_id'],
                $validated['direction'],
                $validated['question_number'] ?? null
            );

            // Get question details
            $response = ExamResponse::find($validated['response_id']);
            $paper = $response->paper;
            $questionData = ExamQuestion::find($question['question_id']);
            
            // Get saved answer if exists
            $savedAnswer = ExamResponseDetail::where('response_id', $validated['response_id'])
                ->where('question_id', $question['question_id'])
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'question_number' => $question['question_number'],
                    'question_id' => $question['question_id'],
                    'question_text' => $questionData->question_text,
                    'question_type' => $questionData->question_type,
                    'options' => $questionData->options,
                    'marks' => $questionData->marks,
                    'negative_marks' => $questionData->negative_marks,
                    'saved_answer' => $savedAnswer->answer ?? null,
                    'status' => $savedAnswer->status ?? 'not_visited',
                    'time_spent' => $savedAnswer->time_spent_seconds ?? 0,
                ],
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to navigate question', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to navigate question',
                'error' => $e->getMessage(),
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Submit exam.
     *
     * @param Request $request
     * @param int $registrationId
     * @return JsonResponse
     */
    public function finishExam(Request $request, int $registrationId): JsonResponse
    {
        try {
            $validated = Validator::make($request->all(), [
                'response_id' => 'required|exists:exam_responses,id',
                'force_submit' => 'nullable|boolean',
            ])->validate();

            // Verify exam token
            if (!$this->verifyExamToken($request, $validated['response_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired exam session',
                ], self::RESPONSE_CODES['UNAUTHORIZED']);
            }

            DB::beginTransaction();

            // Submit exam
            $result = $this->onlineExamService->submitExam($validated['response_id']);

            // Stop proctoring if active
            $response = ExamResponse::find($validated['response_id']);
            if ($response->session->proctoring_type !== 'honor_code') {
                $this->proctoringService->stopProctoring($validated['response_id']);
            }

            // Calculate preliminary results for objective questions
            if ($response->paper->exam->delivery_mode === 'computer_based') {
                $this->evaluationService->evaluateObjectiveQuestions($response->paper_id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Exam submitted successfully',
                'data' => [
                    'submission_time' => now()->toIso8601String(),
                    'total_attempted' => $result['questions_attempted'],
                    'total_questions' => $result['total_questions'],
                    'time_taken' => $result['time_spent_seconds'],
                    'result_available_date' => $response->paper->exam->result_publish_date,
                ],
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to submit exam', [
                'error' => $e->getMessage(),
                'registration_id' => $registrationId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit exam',
                'error' => $e->getMessage(),
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Get exam results.
     *
     * @param Request $request
     * @param int $registrationId
     * @return JsonResponse
     */
    public function getResults(Request $request, int $registrationId): JsonResponse
    {
        try {
            $registration = EntranceExamRegistration::findOrFail($registrationId);
            
            // Verify ownership
            if (Auth::check() && $registration->candidate_email !== Auth::user()->email) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to results',
                ], self::RESPONSE_CODES['FORBIDDEN']);
            }

            // Check if results are published
            $result = EntranceExamResult::where('registration_id', $registrationId)
                ->where('is_published', true)
                ->first();

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Results not yet published',
                    'data' => [
                        'expected_date' => $registration->exam->result_publish_date,
                    ],
                ], self::RESPONSE_CODES['NOT_FOUND']);
            }

            return response()->json([
                'success' => true,
                'data' => new ExamResultResource($result),
            ], self::RESPONSE_CODES['SUCCESS']);

        } catch (Exception $e) {
            Log::error('Failed to fetch results', [
                'error' => $e->getMessage(),
                'registration_id' => $registrationId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch results',
                'error' => $e->getMessage(),
            ], self::RESPONSE_CODES['SERVER_ERROR']);
        }
    }

    /**
     * Generate exam session token.
     *
     * @param EntranceExamRegistration $registration
     * @param int $responseId
     * @return string
     */
    private function generateExamToken(EntranceExamRegistration $registration, int $responseId): string
    {
        $payload = [
            'registration_id' => $registration->id,
            'response_id' => $responseId,
            'expires_at' => Carbon::now()->addMinutes(self::EXAM_SESSION_TIMEOUT)->timestamp,
        ];

        $token = base64_encode(json_encode($payload));
        
        // Cache the token
        Cache::put(
            "exam_token_{$responseId}",
            $token,
            Carbon::now()->addMinutes(self::EXAM_SESSION_TIMEOUT)
        );

        return $token;
    }

    /**
     * Verify exam session token.
     *
     * @param Request $request
     * @param int $responseId
     * @return bool
     */
    private function verifyExamToken(Request $request, int $responseId): bool
    {
        $token = $request->header('X-Exam-Token') ?? $request->input('exam_token');
        
        if (!$token) {
            return false;
        }

        $cachedToken = Cache::get("exam_token_{$responseId}");
        
        if ($cachedToken !== $token) {
            return false;
        }

        try {
            $payload = json_decode(base64_decode($token), true);
            
            if ($payload['response_id'] !== $responseId) {
                return false;
            }
            
            if ($payload['expires_at'] < time()) {
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}