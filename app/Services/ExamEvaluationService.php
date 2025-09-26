<?php

namespace App\Services;

use App\Models\EntranceExam;
use App\Models\ExamQuestionPaper;
use App\Models\ExamResponse;
use App\Models\ExamResponseDetail;
use App\Models\EntranceExamResult;
use App\Models\EntranceExamRegistration;
use App\Models\ExamAnswerKey;
use App\Models\ExamQuestion;
use App\Models\User;
use App\Models\ApplicationCommunication;
use App\Models\ExamCertificate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Exception;

class ExamEvaluationService
{
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
     * Grading scales
     */
    private const GRADING_SCALES = [
        'percentile' => ['top_10', 'top_25', 'top_50', 'bottom_50'],
        'grade' => ['A+', 'A', 'B+', 'B', 'C+', 'C', 'D', 'F'],
        'qualification' => ['highly_qualified', 'qualified', 'marginally_qualified', 'not_qualified'],
    ];

    /**
     * Evaluate objective questions automatically
     *
     * @param int $paperId
     * @return array
     * @throws Exception
     */
    public function evaluateObjectiveQuestions(int $paperId): array
    {
        DB::beginTransaction();

        try {
            $paper = ExamQuestionPaper::with(['exam', 'responses.responseDetails'])->findOrFail($paperId);
            
            // Get answer key
            $answerKey = ExamAnswerKey::where('paper_id', $paperId)
                ->where('key_type', 'final')
                ->first();
            
            if (!$answerKey) {
                throw new Exception("Final answer key not found for paper");
            }

            $evaluationResults = [];
            $totalEvaluated = 0;

            foreach ($paper->responses as $response) {
                if ($response->status !== 'submitted' && $response->status !== 'auto_submitted') {
                    continue;
                }

                $result = $this->evaluateResponse($response, $answerKey);
                $evaluationResults[] = $result;
                $totalEvaluated++;

                // Update response status
                $response->evaluation_status = 'completed';
                $response->evaluated_at = now();
                $response->save();
            }

            // Mark paper as evaluated
            $paper->evaluation_status = 'objective_evaluated';
            $paper->objective_evaluated_at = now();
            $paper->save();

            DB::commit();

            Log::info('Objective questions evaluated', [
                'paper_id' => $paperId,
                'total_evaluated' => $totalEvaluated,
            ]);

            return [
                'status' => 'success',
                'paper_id' => $paperId,
                'total_responses' => count($paper->responses),
                'total_evaluated' => $totalEvaluated,
                'results' => $evaluationResults,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to evaluate objective questions', [
                'paper_id' => $paperId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Assign evaluator to subjective questions
     *
     * @param int $paperId
     * @param int $evaluatorId
     * @param array $questions
     * @return array
     * @throws Exception
     */
    public function assignEvaluator(int $paperId, int $evaluatorId, array $questions = []): array
    {
        DB::beginTransaction();

        try {
            $paper = ExamQuestionPaper::findOrFail($paperId);
            $evaluator = User::findOrFail($evaluatorId);

            // Validate evaluator permissions
            if (!$this->canEvaluate($evaluator)) {
                throw new Exception("User does not have evaluation permissions");
            }

            // Get subjective questions if not specified
            if (empty($questions)) {
                $questions = ExamQuestion::whereIn('id', $paper->questions_order)
                    ->whereIn('question_type', ['short_answer', 'essay'])
                    ->pluck('id')
                    ->toArray();
            }

            // Create evaluation assignments
            $assignments = [];
            foreach ($questions as $questionId) {
                $assignments[] = [
                    'paper_id' => $paperId,
                    'evaluator_id' => $evaluatorId,
                    'question_id' => $questionId,
                    'assigned_at' => now(),
                    'status' => 'pending',
                ];
            }

            DB::table('exam_evaluation_assignments')->insert($assignments);

            // Notify evaluator
            $this->notifyEvaluator($evaluator, $paper, count($questions));

            DB::commit();

            Log::info('Evaluator assigned', [
                'paper_id' => $paperId,
                'evaluator_id' => $evaluatorId,
                'questions_count' => count($questions),
            ]);

            return [
                'status' => 'success',
                'assignments' => count($assignments),
                'evaluator' => $evaluator->name,
                'estimated_time' => count($questions) * 5 . ' minutes',
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign evaluator', [
                'paper_id' => $paperId,
                'evaluator_id' => $evaluatorId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Evaluate subjective answer
     *
     * @param int $responseDetailId
     * @param float $marks
     * @param string|null $comments
     * @return ExamResponseDetail
     * @throws Exception
     */
    public function evaluateSubjectiveAnswer(
        int $responseDetailId, 
        float $marks, 
        ?string $comments = null
    ): ExamResponseDetail {
        DB::beginTransaction();

        try {
            $responseDetail = ExamResponseDetail::with(['response', 'question'])->findOrFail($responseDetailId);
            
            // Validate marks
            if ($marks < 0 || $marks > $responseDetail->question->marks) {
                throw new Exception("Invalid marks. Must be between 0 and {$responseDetail->question->marks}");
            }

            // Update evaluation
            $responseDetail->marks_obtained = $marks;
            $responseDetail->is_correct = ($marks >= $responseDetail->question->marks * 0.5); // 50% threshold
            $responseDetail->evaluator_comments = $comments;
            $responseDetail->evaluated_by = auth()->id();
            $responseDetail->evaluated_at = now();
            $responseDetail->save();

            // Check if all subjective questions are evaluated
            $this->checkResponseCompletion($responseDetail->response_id);

            DB::commit();

            Log::info('Subjective answer evaluated', [
                'response_detail_id' => $responseDetailId,
                'marks' => $marks,
                'evaluator' => auth()->id(),
            ]);

            return $responseDetail;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to evaluate subjective answer', [
                'response_detail_id' => $responseDetailId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate results for a registration
     *
     * @param int $registrationId
     * @return EntranceExamResult
     * @throws Exception
     */
    public function calculateResults(int $registrationId): EntranceExamResult
    {
        DB::beginTransaction();

        try {
            $registration = EntranceExamRegistration::with([
                'exam',
                'response.responseDetails.question'
            ])->findOrFail($registrationId);

            $response = $registration->response;
            if (!$response) {
                throw new Exception("No exam response found for registration");
            }

            // Calculate scores
            $scores = $this->calculateScores($response);

            // Get or create result record
            $result = EntranceExamResult::firstOrNew([
                'registration_id' => $registrationId,
                'exam_id' => $registration->exam_id,
                'response_id' => $response->id,
            ]);

            // Update result data
            $result->fill($scores);
            
            // Determine result status
            $result->result_status = $this->determineResultStatus($result, $registration->exam);
            $result->is_qualified = ($result->result_status === 'pass');
            
            // Add evaluation metadata
            $result->evaluated_by = auth()->id();
            $result->evaluated_at = now();
            
            $result->save();

            // Calculate section-wise scores
            $this->calculateSectionScores($result, $response);

            DB::commit();

            Log::info('Results calculated', [
                'registration_id' => $registrationId,
                'final_score' => $result->final_score,
                'status' => $result->result_status,
            ]);

            return $result;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to calculate results', [
                'registration_id' => $registrationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Apply negative marking to responses
     *
     * @param int $responseId
     * @return array
     * @throws Exception
     */
    public function applyNegativeMarking(int $responseId): array
    {
        $response = ExamResponse::with(['paper.exam', 'responseDetails'])->findOrFail($responseId);
        $exam = $response->paper->exam;

        if (!$exam->negative_marking) {
            return [
                'applied' => false,
                'message' => 'Negative marking not applicable for this exam',
            ];
        }

        $negativeMarks = 0;
        $wrongAnswers = 0;

        foreach ($response->responseDetails as $detail) {
            if ($detail->status === 'answered' && !$detail->is_correct) {
                $negativeMarks += $exam->negative_mark_value;
                $wrongAnswers++;
                
                // Update detail with negative marks
                $detail->negative_marks = $exam->negative_mark_value;
                $detail->save();
            }
        }

        // Update response with total negative marks
        $response->negative_marks = $negativeMarks;
        $response->save();

        Log::info('Negative marking applied', [
            'response_id' => $responseId,
            'wrong_answers' => $wrongAnswers,
            'negative_marks' => $negativeMarks,
        ]);

        return [
            'applied' => true,
            'wrong_answers' => $wrongAnswers,
            'negative_marks' => $negativeMarks,
            'negative_mark_per_question' => $exam->negative_mark_value,
        ];
    }

    /**
     * Normalize scores across all candidates
     *
     * @param int $examId
     * @return array
     * @throws Exception
     */
    public function normalizeScores(int $examId): array
    {
        DB::beginTransaction();

        try {
            $exam = EntranceExam::findOrFail($examId);
            
            // Get all results for normalization
            $results = EntranceExamResult::where('exam_id', $examId)
                ->whereIn('result_status', ['pass', 'fail'])
                ->get();

            if ($results->isEmpty()) {
                throw new Exception("No results available for normalization");
            }

            // Calculate statistics
            $stats = [
                'mean' => $results->avg('final_score'),
                'std_dev' => $this->calculateStandardDeviation($results->pluck('final_score')),
                'min' => $results->min('final_score'),
                'max' => $results->max('final_score'),
            ];

            // Apply normalization formula
            foreach ($results as $result) {
                $normalizedScore = $this->normalizeScore(
                    $result->final_score,
                    $stats['mean'],
                    $stats['std_dev']
                );
                
                $result->normalized_score = $normalizedScore;
                $result->save();
            }

            DB::commit();

            Log::info('Scores normalized', [
                'exam_id' => $examId,
                'total_normalized' => $results->count(),
                'statistics' => $stats,
            ]);

            return [
                'status' => 'success',
                'total_normalized' => $results->count(),
                'statistics' => $stats,
                'normalization_method' => 'z-score',
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to normalize scores', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate percentile for all candidates
     *
     * @param int $examId
     * @return array
     * @throws Exception
     */
    public function calculatePercentile(int $examId): array
    {
        DB::beginTransaction();

        try {
            $exam = EntranceExam::findOrFail($examId);
            
            // Get all results sorted by score
            $results = EntranceExamResult::where('exam_id', $examId)
                ->whereIn('result_status', ['pass', 'fail'])
                ->orderBy('final_score', 'desc')
                ->get();

            $totalCandidates = $results->count();
            
            if ($totalCandidates === 0) {
                throw new Exception("No results available for percentile calculation");
            }

            // Calculate percentile for each candidate
            $rank = 1;
            foreach ($results as $result) {
                $percentile = (($totalCandidates - $rank + 0.5) / $totalCandidates) * 100;
                
                $result->percentile = round($percentile, 2);
                $result->save();
                
                $rank++;
            }

            // Cache percentile distribution
            $this->cachePercentileDistribution($examId, $results);

            DB::commit();

            Log::info('Percentiles calculated', [
                'exam_id' => $examId,
                'total_candidates' => $totalCandidates,
            ]);

            return [
                'status' => 'success',
                'total_candidates' => $totalCandidates,
                'percentile_distribution' => [
                    '90th' => $results->where('percentile', '>=', 90)->count(),
                    '75th' => $results->where('percentile', '>=', 75)->count(),
                    '50th' => $results->where('percentile', '>=', 50)->count(),
                    '25th' => $results->where('percentile', '>=', 25)->count(),
                ],
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to calculate percentiles', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate rank list for exam
     *
     * @param int $examId
     * @param array $filters
     * @return Collection
     * @throws Exception
     */
    public function generateRankList(int $examId, array $filters = []): Collection
    {
        DB::beginTransaction();

        try {
            $exam = EntranceExam::findOrFail($examId);
            
            $query = EntranceExamResult::with(['registration.application'])
                ->where('exam_id', $examId)
                ->whereIn('result_status', ['pass', 'fail']);

            // Apply filters
            if (isset($filters['category'])) {
                $query->whereHas('registration.application', function ($q) use ($filters) {
                    $q->where('category', $filters['category']);
                });
            }

            if (isset($filters['program_id'])) {
                $query->whereHas('registration.application', function ($q) use ($filters) {
                    $q->where('program_id', $filters['program_id']);
                });
            }

            // Get results ordered by score
            $results = $query->orderBy('final_score', 'desc')
                ->orderBy('percentile', 'desc')
                ->get();

            // Assign ranks
            $rank = 1;
            $previousScore = null;
            $sameRankCount = 0;

            foreach ($results as $result) {
                if ($previousScore !== null && $result->final_score < $previousScore) {
                    $rank += $sameRankCount + 1;
                    $sameRankCount = 0;
                } elseif ($previousScore !== null && $result->final_score === $previousScore) {
                    $sameRankCount++;
                }

                $result->overall_rank = $rank;
                $result->save();
                
                $previousScore = $result->final_score;
            }

            // Generate category-wise ranks
            $this->generateCategoryRanks($examId);

            // Generate center-wise ranks
            $this->generateCenterRanks($examId);

            DB::commit();

            Log::info('Rank list generated', [
                'exam_id' => $examId,
                'total_ranked' => $results->count(),
            ]);

            return $results;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate rank list', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Publish exam results
     *
     * @param int $examId
     * @return array
     * @throws Exception
     */
    public function publishResults(int $examId): array
    {
        DB::beginTransaction();

        try {
            $exam = EntranceExam::findOrFail($examId);
            
            // Validate all results are ready
            $pendingCount = EntranceExamResult::where('exam_id', $examId)
                ->whereNull('evaluated_at')
                ->count();
            
            if ($pendingCount > 0) {
                throw new Exception("Cannot publish results. {$pendingCount} results pending evaluation");
            }

            // Update all results to published
            $publishedCount = EntranceExamResult::where('exam_id', $examId)
                ->update([
                    'is_published' => true,
                    'published_at' => now(),
                ]);

            // Update exam status
            $exam->result_publish_date = now();
            $exam->status = 'results_published';
            $exam->save();

            // Send result notifications
            $this->sendResultNotifications($examId);

            // Generate result statistics
            $statistics = $this->generateResultStatistics($examId);

            // Cache public results
            $this->cachePublicResults($examId);

            DB::commit();

            Log::info('Results published', [
                'exam_id' => $examId,
                'published_count' => $publishedCount,
            ]);

            return [
                'status' => 'success',
                'exam_id' => $examId,
                'published_count' => $publishedCount,
                'published_at' => now()->format('Y-m-d H:i:s'),
                'statistics' => $statistics,
                'notification_status' => 'Notifications queued for delivery',
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to publish results', [
                'exam_id' => $examId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate scorecard for a registration
     *
     * @param int $registrationId
     * @return string
     * @throws Exception
     */
    public function generateScorecard(int $registrationId): string
    {
        $registration = EntranceExamRegistration::with([
            'exam',
            'result',
            'application',
            'seatAllocation.session.center'
        ])->findOrFail($registrationId);

        $result = $registration->result;
        if (!$result || !$result->is_published) {
            throw new Exception("Results not available or not published");
        }

        // Prepare scorecard data
        $data = [
            'registration' => $registration,
            'result' => $result,
            'candidate_name' => $this->getCandidateName($registration),
            'exam' => $registration->exam,
            'center' => $registration->seatAllocation?->session?->center,
            'section_scores' => json_decode($result->section_scores, true),
            'percentile' => $result->percentile,
            'rank' => $result->overall_rank,
            'qualification_status' => $result->is_qualified ? 'Qualified' : 'Not Qualified',
            'generated_at' => now(),
            'verification_code' => $this->generateVerificationCode($result),
        ];

        // Generate QR code for verification
        $qrCode = QrCode::size(100)
            ->generate(route('exam.verify-scorecard', $data['verification_code']));
        $data['qr_code'] = base64_encode($qrCode);

        // Generate PDF
        $pdf = PDF::loadView('exams.scorecard', $data);
        
        $filename = "scorecard_{$registration->registration_number}_{$registration->id}.pdf";
        $path = "exams/scorecards/{$registration->exam_id}/{$filename}";
        
        Storage::put($path, $pdf->output());

        // Save certificate record
        $this->createCertificate($registration, $result, $path, $data['verification_code']);

        Log::info('Scorecard generated', [
            'registration_id' => $registrationId,
            'path' => $path,
        ]);

        return $path;
    }

    /**
     * Private helper methods
     */

    /**
     * Evaluate a single response against answer key
     */
    private function evaluateResponse(ExamResponse $response, ExamAnswerKey $answerKey): array
    {
        $correctAnswers = 0;
        $wrongAnswers = 0;
        $unanswered = 0;
        $marksObtained = 0;
        $negativeMarks = 0;

        $answers = $answerKey->answers;

        foreach ($response->responseDetails as $detail) {
            $questionId = $detail->question_id;
            $correctAnswer = $answers[$questionId] ?? null;

            if (!$correctAnswer) {
                continue;
            }

            if ($detail->status === 'not_answered' || $detail->status === 'not_visited') {
                $unanswered++;
            } elseif ($detail->status === 'answered' || $detail->status === 'answered_marked_review') {
                if ($this->isAnswerCorrect($detail->answer, $correctAnswer)) {
                    $correctAnswers++;
                    $marksObtained += $detail->question->marks;
                    $detail->is_correct = true;
                    $detail->marks_obtained = $detail->question->marks;
                } else {
                    $wrongAnswers++;
                    $detail->is_correct = false;
                    $detail->marks_obtained = 0;
                    
                    // Apply negative marking if applicable
                    if ($response->paper->exam->negative_marking) {
                        $negativeMarks += $response->paper->exam->negative_mark_value;
                    }
                }
                
                $detail->save();
            }
        }

        return [
            'response_id' => $response->id,
            'correct_answers' => $correctAnswers,
            'wrong_answers' => $wrongAnswers,
            'unanswered' => $unanswered,
            'marks_obtained' => $marksObtained,
            'negative_marks' => $negativeMarks,
            'final_score' => $marksObtained - $negativeMarks,
        ];
    }

    /**
     * Check if answer is correct
     */
    private function isAnswerCorrect($studentAnswer, $correctAnswer): bool
    {
        // Handle different answer formats
        if (is_array($correctAnswer)) {
            // Multiple correct answers
            return in_array($studentAnswer, $correctAnswer);
        }
        
        return strtolower(trim($studentAnswer)) === strtolower(trim($correctAnswer));
    }

    /**
     * Check if user can evaluate
     */
    private function canEvaluate(User $user): bool
    {
        $allowedRoles = ['examiner', 'evaluator', 'faculty', 'academic_administrator'];
        return $user->hasAnyRole($allowedRoles);
    }

    /**
     * Check if all questions in response are evaluated
     */
    private function checkResponseCompletion(int $responseId): void
    {
        $response = ExamResponse::with('responseDetails')->find($responseId);
        
        if (!$response) {
            return;
        }

        $unevaluated = $response->responseDetails
            ->whereNull('marks_obtained')
            ->where('status', 'answered')
            ->count();

        if ($unevaluated === 0) {
            $response->evaluation_status = 'completed';
            $response->evaluated_at = now();
            $response->save();
            
            // Trigger result calculation
            $this->calculateResults($response->registration_id);
        }
    }

    /**
     * Calculate scores from response
     */
    private function calculateScores(ExamResponse $response): array
    {
        $details = $response->responseDetails;
        
        $scores = [
            'total_questions_attempted' => 0,
            'correct_answers' => 0,
            'wrong_answers' => 0,
            'unanswered' => 0,
            'marks_obtained' => 0,
            'negative_marks' => 0,
            'final_score' => 0,
        ];

        foreach ($details as $detail) {
            if (in_array($detail->status, ['answered', 'answered_marked_review'])) {
                $scores['total_questions_attempted']++;
                
                if ($detail->is_correct) {
                    $scores['correct_answers']++;
                    $scores['marks_obtained'] += $detail->marks_obtained ?? 0;
                } else {
                    $scores['wrong_answers']++;
                    $scores['negative_marks'] += $detail->negative_marks ?? 0;
                }
            } else {
                $scores['unanswered']++;
            }
        }

        $scores['final_score'] = $scores['marks_obtained'] - $scores['negative_marks'];
        $scores['percentage'] = ($response->paper->exam->total_marks > 0) 
            ? round(($scores['final_score'] / $response->paper->exam->total_marks) * 100, 2)
            : 0;

        return $scores;
    }

    /**
     * Calculate section-wise scores
     */
    private function calculateSectionScores(EntranceExamResult $result, ExamResponse $response): void
    {
        $sections = [];
        $exam = $response->paper->exam;
        
        if ($exam->sections) {
            foreach ($exam->sections as $section) {
                $sectionDetails = $response->responseDetails
                    ->whereIn('question_id', $section['question_ids'] ?? []);
                
                $sections[$section['name']] = [
                    'attempted' => $sectionDetails->where('status', 'answered')->count(),
                    'correct' => $sectionDetails->where('is_correct', true)->count(),
                    'marks' => $sectionDetails->sum('marks_obtained'),
                    'percentage' => 0,
                ];
                
                if ($section['marks'] > 0) {
                    $sections[$section['name']]['percentage'] = 
                        round(($sections[$section['name']]['marks'] / $section['marks']) * 100, 2);
                }
            }
        }
        
        $result->section_scores = json_encode($sections);
        $result->save();
    }

    /**
     * Determine result status based on scores
     */
    private function determineResultStatus(EntranceExamResult $result, EntranceExam $exam): string
    {
        if ($result->total_questions_attempted === 0) {
            return 'absent';
        }
        
        if ($result->final_score >= $exam->passing_marks) {
            return 'pass';
        }
        
        return 'fail';
    }

    /**
     * Calculate standard deviation
     */
    private function calculateStandardDeviation(Collection $scores): float
    {
        $mean = $scores->avg();
        $variance = $scores->map(function ($score) use ($mean) {
            return pow($score - $mean, 2);
        })->avg();
        
        return sqrt($variance);
    }

    /**
     * Normalize score using z-score method
     */
    private function normalizeScore(float $score, float $mean, float $stdDev): float
    {
        if ($stdDev == 0) {
            return $score;
        }
        
        // Z-score normalization with scaling
        $zScore = ($score - $mean) / $stdDev;
        
        // Scale to 0-100 range
        $normalized = 50 + ($zScore * 10);
        
        return max(0, min(100, $normalized));
    }

    /**
     * Generate category-wise ranks
     */
    private function generateCategoryRanks(int $examId): void
    {
        $categories = ['general', 'obc', 'sc', 'st', 'ews'];
        
        foreach ($categories as $category) {
            $results = EntranceExamResult::where('exam_id', $examId)
                ->whereHas('registration.application', function ($q) use ($category) {
                    $q->where('category', $category);
                })
                ->orderBy('final_score', 'desc')
                ->get();
            
            $rank = 1;
            foreach ($results as $result) {
                $result->category_rank = $rank++;
                $result->save();
            }
        }
    }

    /**
     * Generate center-wise ranks
     */
    private function generateCenterRanks(int $examId): void
    {
        $centers = DB::table('exam_seat_allocations')
            ->join('entrance_exam_registrations', 'entrance_exam_registrations.id', '=', 'exam_seat_allocations.registration_id')
            ->where('entrance_exam_registrations.exam_id', $examId)
            ->distinct()
            ->pluck('exam_seat_allocations.center_id');
        
        foreach ($centers as $centerId) {
            $results = EntranceExamResult::where('exam_id', $examId)
                ->whereHas('registration.seatAllocation', function ($q) use ($centerId) {
                    $q->where('center_id', $centerId);
                })
                ->orderBy('final_score', 'desc')
                ->get();
            
            $rank = 1;
            foreach ($results as $result) {
                $result->center_rank = $rank++;
                $result->save();
            }
        }
    }

    /**
     * Cache percentile distribution
     */
    private function cachePercentileDistribution(int $examId, Collection $results): void
    {
        $distribution = [];
        
        for ($i = 10; $i <= 100; $i += 10) {
            $distribution[$i] = $results->where('percentile', '>=', $i)->count();
        }
        
        Cache::put("exam_percentile_distribution_{$examId}", $distribution, now()->addDays(30));
    }

    /**
     * Generate result statistics
     */
    private function generateResultStatistics(int $examId): array
    {
        $results = EntranceExamResult::where('exam_id', $examId)->get();
        
        return [
            'total_appeared' => $results->whereIn('result_status', ['pass', 'fail'])->count(),
            'passed' => $results->where('result_status', 'pass')->count(),
            'failed' => $results->where('result_status', 'fail')->count(),
            'absent' => $results->where('result_status', 'absent')->count(),
            'average_score' => round($results->avg('final_score'), 2),
            'highest_score' => $results->max('final_score'),
            'lowest_score' => $results->min('final_score'),
            'pass_percentage' => $results->count() > 0 
                ? round(($results->where('result_status', 'pass')->count() / $results->count()) * 100, 2)
                : 0,
        ];
    }

    /**
     * Cache public results for display
     */
    private function cachePublicResults(int $examId): void
    {
        $results = EntranceExamResult::with(['registration'])
            ->where('exam_id', $examId)
            ->where('is_published', true)
            ->orderBy('overall_rank')
            ->get()
            ->map(function ($result) {
                return [
                    'rank' => $result->overall_rank,
                    'registration_number' => $result->registration->registration_number,
                    'score' => $result->final_score,
                    'percentile' => $result->percentile,
                    'status' => $result->result_status,
                ];
            });
        
        Cache::put("exam_public_results_{$examId}", $results, now()->addDays(7));
    }

    /**
     * Send result notifications
     */
    private function sendResultNotifications(int $examId): void
    {
        $results = EntranceExamResult::with(['registration'])
            ->where('exam_id', $examId)
            ->where('is_published', true)
            ->whereNull('candidate_notified')
            ->get();
        
        foreach ($results as $result) {
            try {
                // Queue notification job
                dispatch(new \App\Jobs\SendResultNotification($result));
                
                $result->candidate_notified = true;
                $result->save();
            } catch (Exception $e) {
                Log::error('Failed to queue result notification', [
                    'result_id' => $result->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get candidate name
     */
    private function getCandidateName(EntranceExamRegistration $registration): string
    {
        if ($registration->application) {
            return $registration->application->first_name . ' ' . $registration->application->last_name;
        }
        
        return $registration->candidate_name ?? 'N/A';
    }

    /**
     * Generate verification code for scorecard
     */
    private function generateVerificationCode(EntranceExamResult $result): string
    {
        $data = $result->id . '-' . $result->registration_id . '-' . $result->exam_id;
        return strtoupper(substr(md5($data), 0, 12));
    }

    /**
     * Create certificate record
     */
    private function createCertificate(
        EntranceExamRegistration $registration,
        EntranceExamResult $result,
        string $filePath,
        string $verificationCode
    ): void {
        ExamCertificate::create([
            'result_id' => $result->id,
            'registration_id' => $registration->id,
            'certificate_number' => 'CERT-' . $registration->exam->exam_code . '-' . str_pad($result->overall_rank, 5, '0', STR_PAD_LEFT),
            'certificate_type' => $result->is_qualified ? 'qualification' : 'participation',
            'file_path' => $filePath,
            'verification_code' => $verificationCode,
            'issued_at' => now(),
            'issued_by' => auth()->id(),
        ]);
    }

    /**
     * Notify evaluator of assignment
     */
    private function notifyEvaluator(User $evaluator, ExamQuestionPaper $paper, int $questionCount): void
    {
        // Send notification to evaluator
        // This would use your notification service
        Log::info('Evaluator notified', [
            'evaluator_id' => $evaluator->id,
            'paper_id' => $paper->id,
            'questions' => $questionCount,
        ]);
    }
}