<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ExamEvaluationService;
use App\Services\ApplicationNotificationService;
use App\Models\EntranceExam;
use App\Models\ExamQuestionPaper;
use App\Models\ExamResponse;
use App\Models\ExamResponseDetail;
use App\Models\EntranceExamResult;
use App\Models\ExamAnswerKey;
use App\Models\AnswerKeyChallenge;
use App\Models\ExamQuestion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExamResultsExport;
use App\Exports\EvaluationReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;

class ExamEvaluationController extends Controller
{
    protected $evaluationService;
    protected $notificationService;

    /**
     * Items per page for pagination
     */
    private const ITEMS_PER_PAGE = 25;

    /**
     * Evaluation statuses
     */
    private const EVALUATION_STATUSES = [
        'pending' => 'Pending Evaluation',
        'in_progress' => 'Evaluation In Progress',
        'completed' => 'Evaluation Completed',
        'reviewed' => 'Results Reviewed',
        'published' => 'Results Published',
    ];

    /**
     * Question types requiring manual evaluation
     */
    private const MANUAL_EVALUATION_TYPES = [
        'essay',
        'short_answer',
        'fill_blanks',
    ];

    /**
     * Result statuses
     */
    private const RESULT_STATUSES = [
        'pass' => 'Passed',
        'fail' => 'Failed',
        'absent' => 'Absent',
        'disqualified' => 'Disqualified',
        'withheld' => 'Withheld',
        'under_review' => 'Under Review',
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(
        ExamEvaluationService $evaluationService,
        ApplicationNotificationService $notificationService
    ) {
        $this->evaluationService = $evaluationService;
        $this->notificationService = $notificationService;
        
        // Middleware for evaluation authority
        $this->middleware(['auth', 'role:admin,exam_coordinator,evaluator,registrar']);
    }

    /**
     * Display evaluation dashboard for an exam.
     *
     * @param int $examId
     * @return \Illuminate\View\View
     */
    public function evaluationDashboard($examId)
    {
        try {
            $exam = EntranceExam::with([
                'sessions',
                'questions',
                'responses',
                'results'
            ])->findOrFail($examId);

            // Check if exam is ready for evaluation
            if (!in_array($exam->status, ['completed', 'results_pending', 'results_published'])) {
                return redirect()->route('exams.admin.show', $examId)
                    ->with('warning', 'Exam is not ready for evaluation.');
            }

            // Get evaluation statistics
            $statistics = $this->getEvaluationStatistics($exam);

            // Get evaluation progress
            $progress = $this->getEvaluationProgress($exam);

            // Get evaluators assigned
            $evaluators = $this->getAssignedEvaluators($exam);

            // Get pending evaluations
            $pendingEvaluations = $this->getPendingEvaluations($exam);

            return view('exams.evaluation.dashboard', compact(
                'exam',
                'statistics',
                'progress',
                'evaluators',
                'pendingEvaluations'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load evaluation dashboard', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('exams.admin.index')
                ->with('error', 'Failed to load evaluation dashboard.');
        }
    }

    /**
     * Start automatic evaluation for objective questions.
     *
     * @param int $examId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function startAutoEvaluation($examId)
    {
        DB::beginTransaction();

        try {
            $exam = EntranceExam::findOrFail($examId);

            // Check if answer key exists
            $answerKey = ExamAnswerKey::where('exam_id', $examId)
                ->where('key_type', 'final')
                ->first();

            if (!$answerKey) {
                return redirect()->back()
                    ->with('error', 'Final answer key not found. Please upload answer key first.');
            }

            // Get all question papers for the exam
            $questionPapers = ExamQuestionPaper::where('exam_id', $examId)->get();

            $evaluatedCount = 0;
            foreach ($questionPapers as $paper) {
                // Evaluate objective questions
                $results = $this->evaluationService->evaluateObjectiveQuestions($paper->id);
                $evaluatedCount += $results['evaluated_count'] ?? 0;
            }

            // Update exam status
            $exam->evaluation_status = 'in_progress';
            $exam->auto_evaluation_completed_at = now();
            $exam->save();

            DB::commit();

            // Clear cache
            Cache::forget("exam_evaluation_{$examId}");

            return redirect()->route('exams.evaluation.dashboard', $examId)
                ->with('success', "Automatic evaluation completed. {$evaluatedCount} responses evaluated.");

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to start auto evaluation', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to start automatic evaluation: ' . $e->getMessage());
        }
    }

    /**
     * Assign evaluators for manual evaluation.
     *
     * @param Request $request
     * @param int $examId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function assignEvaluators(Request $request, $examId)
    {
        try {
            $exam = EntranceExam::with(['questions'])->findOrFail($examId);

            if ($request->isMethod('post')) {
                return $this->processEvaluatorAssignment($request, $exam);
            }

            // Get questions requiring manual evaluation
            $manualQuestions = $exam->questions()
                ->whereIn('question_type', self::MANUAL_EVALUATION_TYPES)
                ->get();

            // Get available evaluators
            $evaluators = User::role(['evaluator', 'faculty', 'admin'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            // Get current assignments
            $currentAssignments = $this->getCurrentEvaluatorAssignments($exam);

            return view('exams.evaluation.assign-evaluators', compact(
                'exam',
                'manualQuestions',
                'evaluators',
                'currentAssignments'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load evaluator assignment', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('exams.evaluation.dashboard', $examId)
                ->with('error', 'Failed to load evaluator assignment.');
        }
    }

    /**
     * Process evaluator assignment.
     */
    private function processEvaluatorAssignment(Request $request, EntranceExam $exam)
    {
        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.evaluator_id' => 'required|exists:users,id',
            'assignments.*.question_ids' => 'required|array',
            'assignments.*.question_ids.*' => 'exists:exam_questions,id',
            'assignments.*.paper_ids' => 'nullable|array',
            'assignments.*.paper_ids.*' => 'exists:exam_question_papers,id',
            'distribution_method' => 'required|in:equal,random,manual',
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['assignments'] as $assignment) {
                // Assign evaluator to papers and questions
                foreach ($assignment['paper_ids'] ?? [] as $paperId) {
                    $this->evaluationService->assignEvaluator(
                        $paperId,
                        $assignment['evaluator_id'],
                        $assignment['question_ids']
                    );
                }
            }

            // Send notifications to evaluators
            $this->notifyEvaluators($validated['assignments'], $exam);

            DB::commit();

            return redirect()->route('exams.evaluation.dashboard', $exam->id)
                ->with('success', 'Evaluators assigned successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to assign evaluators', [
                'exam_id' => $exam->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to assign evaluators.')
                ->withInput();
        }
    }

    /**
     * Evaluate answers manually.
     *
     * @param Request $request
     * @param int $paperId
     * @return \Illuminate\View\View
     */
    public function evaluateAnswers(Request $request, $paperId)
    {
        try {
            $paper = ExamQuestionPaper::with([
                'exam',
                'response.registration',
                'response.responseDetails.question'
            ])->findOrFail($paperId);

            // Check if user is assigned to evaluate this paper
            if (!$this->userCanEvaluatePaper($paper)) {
                return redirect()->route('exams.evaluation.dashboard', $paper->exam_id)
                    ->with('error', 'You are not assigned to evaluate this paper.');
            }

            if ($request->isMethod('post')) {
                return $this->processEvaluation($request, $paper);
            }

            // Get questions to evaluate
            $questionsToEvaluate = $this->getQuestionsToEvaluate($paper);

            // Get evaluation progress
            $evaluationProgress = $this->getPaperEvaluationProgress($paper);

            return view('exams.evaluation.evaluate-answers', compact(
                'paper',
                'questionsToEvaluate',
                'evaluationProgress'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load answer evaluation', [
                'paper_id' => $paperId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to load answer evaluation.');
        }
    }

    /**
     * Process manual evaluation.
     */
    private function processEvaluation(Request $request, ExamQuestionPaper $paper)
    {
        $validated = $request->validate([
            'evaluations' => 'required|array',
            'evaluations.*.response_detail_id' => 'required|exists:exam_response_details,id',
            'evaluations.*.marks' => 'required|numeric|min:0',
            'evaluations.*.comments' => 'nullable|string|max:1000',
            'save_draft' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['evaluations'] as $evaluation) {
                $responseDetail = ExamResponseDetail::findOrFail($evaluation['response_detail_id']);
                
                // Evaluate the answer
                $this->evaluationService->evaluateSubjectiveAnswer(
                    $responseDetail->id,
                    $evaluation['marks'],
                    $evaluation['comments'] ?? null
                );

                // Update evaluation status
                $responseDetail->evaluation_status = $validated['save_draft'] ? 'draft' : 'completed';
                $responseDetail->evaluated_by = Auth::id();
                $responseDetail->evaluated_at = now();
                $responseDetail->save();
            }

            // Check if all questions are evaluated
            if (!$validated['save_draft']) {
                $this->checkAndUpdatePaperStatus($paper);
            }

            DB::commit();

            $message = $validated['save_draft'] 
                ? 'Evaluation saved as draft.'
                : 'Evaluation completed successfully.';

            return redirect()->route('exams.evaluation.evaluate', $paper->id)
                ->with('success', $message);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to process evaluation', [
                'paper_id' => $paper->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to process evaluation.')
                ->withInput();
        }
    }

    /**
     * Review evaluated papers.
     *
     * @param Request $request
     * @param int $paperId
     * @return \Illuminate\View\View
     */
    public function reviewEvaluation(Request $request, $paperId)
    {
        try {
            $paper = ExamQuestionPaper::with([
                'exam',
                'response.registration',
                'response.responseDetails.question'
            ])->findOrFail($paperId);

            if ($request->isMethod('post')) {
                return $this->processReview($request, $paper);
            }

            // Get evaluation details
            $evaluationDetails = $this->getEvaluationDetails($paper);

            // Get evaluator information
            $evaluators = $this->getPaperEvaluators($paper);

            return view('exams.evaluation.review', compact(
                'paper',
                'evaluationDetails',
                'evaluators'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load evaluation review', [
                'paper_id' => $paperId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to load evaluation review.');
        }
    }

    /**
     * Process evaluation review.
     */
    private function processReview(Request $request, ExamQuestionPaper $paper)
    {
        $validated = $request->validate([
            'review_status' => 'required|in:approved,needs_revision,rejected',
            'review_comments' => 'nullable|string',
            'mark_adjustments' => 'nullable|array',
            'mark_adjustments.*.response_detail_id' => 'required|exists:exam_response_details,id',
            'mark_adjustments.*.new_marks' => 'required|numeric|min:0',
            'mark_adjustments.*.reason' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            // Process mark adjustments if any
            if (!empty($validated['mark_adjustments'])) {
                foreach ($validated['mark_adjustments'] as $adjustment) {
                    $this->evaluationService->adjustMarks(
                        $adjustment['response_detail_id'],
                        $adjustment['new_marks'],
                        $adjustment['reason']
                    );
                }
            }

            // Update paper review status
            $paper->review_status = $validated['review_status'];
            $paper->reviewed_by = Auth::id();
            $paper->reviewed_at = now();
            $paper->review_comments = $validated['review_comments'];
            $paper->save();

            // If approved, update response status
            if ($validated['review_status'] === 'approved') {
                $this->updateResponseEvaluationStatus($paper->response);
            }

            DB::commit();

            return redirect()->route('exams.evaluation.dashboard', $paper->exam_id)
                ->with('success', 'Review completed successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to process review', [
                'paper_id' => $paper->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to process review.')
                ->withInput();
        }
    }

    /**
     * Calculate and finalize results.
     *
     * @param int $examId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function calculateResults($examId)
    {
        DB::beginTransaction();

        try {
            $exam = EntranceExam::with(['registrations'])->findOrFail($examId);

            // Check if evaluation is complete
            $incompleteCount = $this->getIncompleteEvaluationsCount($exam);
            if ($incompleteCount > 0) {
                return redirect()->back()
                    ->with('warning', "Cannot calculate results. {$incompleteCount} evaluations are incomplete.");
            }

            $processedCount = 0;
            foreach ($exam->registrations as $registration) {
                // Calculate results for each registration
                $result = $this->evaluationService->calculateResults($registration->id);
                
                if ($result) {
                    $processedCount++;
                }
            }

            // Apply normalization if configured
            if ($exam->apply_normalization) {
                $this->evaluationService->normalizeScores($examId);
            }

            // Calculate percentiles
            $this->evaluationService->calculatePercentile($examId);

            // Generate rank list
            $this->evaluationService->generateRankList($examId);

            // Update exam status
            $exam->evaluation_status = 'completed';
            $exam->results_calculated_at = now();
            $exam->save();

            DB::commit();

            // Clear cache
            Cache::forget("exam_results_{$examId}");
            Cache::tags(['exam_results'])->flush();

            return redirect()->route('exams.evaluation.dashboard', $examId)
                ->with('success', "Results calculated successfully for {$processedCount} candidates.");

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to calculate results', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to calculate results: ' . $e->getMessage());
        }
    }

    /**
     * View calculated results.
     *
     * @param Request $request
     * @param int $examId
     * @return \Illuminate\View\View
     */
    public function viewResults(Request $request, $examId)
    {
        try {
            $exam = EntranceExam::findOrFail($examId);

            $query = EntranceExamResult::with([
                'registration.application',
                'registration.session.center'
            ])->where('exam_id', $examId);

            // Apply filters
            if ($request->filled('result_status')) {
                $query->where('result_status', $request->result_status);
            }

            if ($request->filled('percentile_min')) {
                $query->where('percentile', '>=', $request->percentile_min);
            }

            if ($request->filled('percentile_max')) {
                $query->where('percentile', '<=', $request->percentile_max);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('registration', function ($q) use ($search) {
                    $q->where('registration_number', 'like', "%{$search}%")
                      ->orWhere('hall_ticket_number', 'like', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'overall_rank');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Get result statistics
            $statistics = $this->getResultStatistics($exam);

            // Paginate results
            $results = $query->paginate(self::ITEMS_PER_PAGE)
                ->appends($request->all());

            return view('exams.evaluation.results', compact(
                'exam',
                'results',
                'statistics'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load results', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('exams.evaluation.dashboard', $examId)
                ->with('error', 'Failed to load results.');
        }
    }

    /**
     * Publish exam results.
     *
     * @param Request $request
     * @param int $examId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function publishResults(Request $request, $examId)
    {
        $validated = $request->validate([
            'publish_date' => 'required|date',
            'notify_candidates' => 'boolean',
            'publish_rank_list' => 'boolean',
            'publish_answer_key' => 'boolean',
            'allow_scorecard_download' => 'boolean',
            'confirmation' => 'required|in:PUBLISH',
        ]);

        DB::beginTransaction();

        try {
            $exam = EntranceExam::findOrFail($examId);

            // Publish results
            $this->evaluationService->publishResults($examId);

            // Update exam status
            $exam->status = 'results_published';
            $exam->result_publish_date = $validated['publish_date'];
            $exam->results_published_at = now();
            $exam->published_by = Auth::id();
            $exam->save();

            // Update result records
            EntranceExamResult::where('exam_id', $examId)
                ->update([
                    'is_published' => true,
                    'published_at' => now(),
                ]);

            // Send notifications if requested
            if ($validated['notify_candidates']) {
                $this->notifyResultPublication($exam);
            }

            // Publish answer key if requested
            if ($validated['publish_answer_key']) {
                $this->publishAnswerKey($exam);
            }

            DB::commit();

            // Clear cache
            Cache::forget("exam_results_{$examId}");
            Cache::tags(['exam_results'])->flush();

            return redirect()->route('exams.evaluation.dashboard', $examId)
                ->with('success', 'Results published successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to publish results', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to publish results: ' . $e->getMessage());
        }
    }

    /**
     * Manage answer key.
     *
     * @param Request $request
     * @param int $examId
     * @return \Illuminate\View\View
     */
    public function manageAnswerKey(Request $request, $examId)
    {
        try {
            $exam = EntranceExam::with(['questionPapers', 'questions'])->findOrFail($examId);

            if ($request->isMethod('post')) {
                return $this->processAnswerKey($request, $exam);
            }

            // Get existing answer keys
            $answerKeys = ExamAnswerKey::where('exam_id', $examId)
                ->orderBy('created_at', 'desc')
                ->get();

            // Get question papers
            $questionPapers = $exam->questionPapers;

            return view('exams.evaluation.answer-key', compact(
                'exam',
                'answerKeys',
                'questionPapers'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load answer key management', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('exams.evaluation.dashboard', $examId)
                ->with('error', 'Failed to load answer key management.');
        }
    }

    /**
     * Process answer key upload/update.
     */
    private function processAnswerKey(Request $request, EntranceExam $exam)
    {
        $validated = $request->validate([
            'paper_id' => 'required|exists:exam_question_papers,id',
            'key_type' => 'required|in:provisional,final',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:exam_questions,id',
            'answers.*.correct_answer' => 'required',
            'publish_immediately' => 'boolean',
        ]);

        DB::beginTransaction();

        try {
            // Create or update answer key
            $answerKey = ExamAnswerKey::updateOrCreate(
                [
                    'exam_id' => $exam->id,
                    'paper_id' => $validated['paper_id'],
                    'key_type' => $validated['key_type'],
                ],
                [
                    'answers' => collect($validated['answers'])->pluck('correct_answer', 'question_id'),
                    'created_by' => Auth::id(),
                    'is_published' => $validated['publish_immediately'] ?? false,
                    'published_at' => $validated['publish_immediately'] ? now() : null,
                ]
            );

            // If final answer key, trigger re-evaluation if needed
            if ($validated['key_type'] === 'final') {
                $this->triggerReEvaluation($exam, $answerKey);
            }

            DB::commit();

            return redirect()->route('exams.evaluation.answer-key', $exam->id)
                ->with('success', 'Answer key saved successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to process answer key', [
                'exam_id' => $exam->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to process answer key.')
                ->withInput();
        }
    }

    /**
     * View answer key challenges.
     *
     * @param Request $request
     * @param int $examId
     * @return \Illuminate\View\View
     */
    public function viewChallenges(Request $request, $examId)
    {
        try {
            $exam = EntranceExam::findOrFail($examId);

            $query = AnswerKeyChallenge::with([
                'registration.application',
                'question',
                'answerKey'
            ])->whereHas('answerKey', function ($q) use ($examId) {
                $q->where('exam_id', $examId);
            });

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('question_id')) {
                $query->where('question_id', $request->question_id);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Get challenge statistics
            $statistics = $this->getChallengeStatistics($examId);

            // Paginate results
            $challenges = $query->paginate(self::ITEMS_PER_PAGE)
                ->appends($request->all());

            return view('exams.evaluation.challenges', compact(
                'exam',
                'challenges',
                'statistics'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load answer key challenges', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('exams.evaluation.dashboard', $examId)
                ->with('error', 'Failed to load challenges.');
        }
    }

    /**
     * Export evaluation report.
     *
     * @param Request $request
     * @param int $examId
     * @return mixed
     */
    public function exportReport(Request $request, $examId)
    {
        try {
            $exam = EntranceExam::findOrFail($examId);
            
            $format = $request->get('format', 'xlsx');
            $type = $request->get('type', 'summary');
            
            $filename = "evaluation_report_{$exam->exam_code}_" . date('Y-m-d');
            
            switch ($format) {
                case 'pdf':
                    return $this->exportPdfReport($exam, $type);
                    
                case 'xlsx':
                default:
                    return Excel::download(
                        new EvaluationReportExport($exam, $type),
                        "{$filename}.xlsx"
                    );
            }

        } catch (Exception $e) {
            Log::error('Failed to export evaluation report', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to export report.');
        }
    }

    /**
     * Private helper methods
     */

    /**
     * Get evaluation statistics.
     */
    private function getEvaluationStatistics(EntranceExam $exam): array
    {
        $responses = ExamResponse::whereHas('registration', function ($q) use ($exam) {
            $q->where('exam_id', $exam->id);
        });

        return [
            'total_responses' => $responses->count(),
            'evaluated' => $responses->where('evaluation_status', 'completed')->count(),
            'pending_evaluation' => $responses->where('evaluation_status', 'pending')->count(),
            'in_progress' => $responses->where('evaluation_status', 'in_progress')->count(),
            'results_generated' => EntranceExamResult::where('exam_id', $exam->id)->count(),
            'results_published' => EntranceExamResult::where('exam_id', $exam->id)
                ->where('is_published', true)->count(),
        ];
    }

    /**
     * Get evaluation progress.
     */
    private function getEvaluationProgress(EntranceExam $exam): array
    {
        $totalQuestions = $exam->questions()->count();
        $manualQuestions = $exam->questions()
            ->whereIn('question_type', self::MANUAL_EVALUATION_TYPES)
            ->count();
        
        $evaluatedManual = ExamResponseDetail::whereHas('response.registration', function ($q) use ($exam) {
                $q->where('exam_id', $exam->id);
            })
            ->whereHas('question', function ($q) {
                $q->whereIn('question_type', self::MANUAL_EVALUATION_TYPES);
            })
            ->whereNotNull('marks_obtained')
            ->count();
        
        return [
            'total_questions' => $totalQuestions,
            'manual_questions' => $manualQuestions,
            'auto_evaluated' => $totalQuestions - $manualQuestions,
            'manual_evaluated' => $evaluatedManual,
            'progress_percentage' => $manualQuestions > 0 
                ? round(($evaluatedManual / $manualQuestions) * 100, 2)
                : 100,
        ];
    }

    /**
     * Get assigned evaluators.
     */
    private function getAssignedEvaluators(EntranceExam $exam): Collection
    {
        return Cache::remember("exam_evaluators_{$exam->id}", 300, function () use ($exam) {
            $evaluatorIds = DB::table('exam_evaluator_assignments')
                ->where('exam_id', $exam->id)
                ->pluck('evaluator_id')
                ->unique();
            
            return User::whereIn('id', $evaluatorIds)->get();
        });
    }

    /**
     * Get pending evaluations.
     */
    private function getPendingEvaluations(EntranceExam $exam): Collection
    {
        return ExamResponseDetail::whereHas('response.registration', function ($q) use ($exam) {
                $q->where('exam_id', $exam->id);
            })
            ->whereHas('question', function ($q) {
                $q->whereIn('question_type', self::MANUAL_EVALUATION_TYPES);
            })
            ->whereNull('marks_obtained')
            ->with(['response.registration', 'question'])
            ->limit(10)
            ->get();
    }
}