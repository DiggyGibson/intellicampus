<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ApplicationReviewService;
use App\Services\ApplicationService;
use App\Services\DocumentVerificationService;
use App\Services\ApplicationNotificationService;
use App\Services\RecommendationService;
use App\Models\AdmissionApplication;
use App\Models\ApplicationReview;
use App\Models\ApplicationDocument;
use App\Models\ApplicationNote;
use App\Models\RecommendationLetter;
use App\Models\User;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

class ApplicationReviewController extends Controller
{
    protected $reviewService;
    protected $applicationService;
    protected $documentService;
    protected $notificationService;
    protected $recommendationService;

    /**
     * Review stages
     */
    private const REVIEW_STAGES = [
        'initial_review' => 'Initial Review',
        'academic_review' => 'Academic Review',
        'department_review' => 'Department Review',
        'committee_review' => 'Committee Review',
        'final_review' => 'Final Review',
    ];

    /**
     * Rating scale
     */
    private const RATING_SCALE = [
        1 => 'Poor',
        2 => 'Below Average',
        3 => 'Average',
        4 => 'Above Average',
        5 => 'Excellent',
    ];

    /**
     * Recommendation options
     */
    private const RECOMMENDATIONS = [
        'strongly_recommend' => 'Strongly Recommend',
        'recommend' => 'Recommend',
        'recommend_with_reservations' => 'Recommend with Reservations',
        'do_not_recommend' => 'Do Not Recommend',
        'defer_decision' => 'Defer Decision',
    ];

    /**
     * Review priorities
     */
    private const PRIORITIES = [
        'urgent' => ['label' => 'Urgent', 'days' => 2, 'color' => 'red'],
        'high' => ['label' => 'High Priority', 'days' => 5, 'color' => 'orange'],
        'normal' => ['label' => 'Normal', 'days' => 7, 'color' => 'blue'],
        'low' => ['label' => 'Low Priority', 'days' => 14, 'color' => 'gray'],
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(
        ApplicationReviewService $reviewService,
        ApplicationService $applicationService,
        DocumentVerificationService $documentService,
        ApplicationNotificationService $notificationService,
        RecommendationService $recommendationService
    ) {
        $this->reviewService = $reviewService;
        $this->applicationService = $applicationService;
        $this->documentService = $documentService;
        $this->notificationService = $notificationService;
        $this->recommendationService = $recommendationService;
        
        // Middleware for reviewer access
        $this->middleware(['auth', 'role:reviewer,faculty,admissions_officer,admin']);
    }

    /**
     * Display reviewer dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function myReviews()
    {
        try {
            $reviewerId = Auth::id();
            
            // Get review statistics
            $statistics = $this->getReviewerStatistics($reviewerId);
            
            // Get pending reviews
            $pendingReviews = ApplicationReview::where('reviewer_id', $reviewerId)
                ->whereIn('status', ['pending', 'in_progress'])
                ->with(['application.program', 'application.term'])
                ->orderBy('assigned_at')
                ->get();
            
            // Categorize by priority
            $prioritizedReviews = $this->prioritizeReviews($pendingReviews);
            
            // Get completed reviews (recent)
            $completedReviews = ApplicationReview::where('reviewer_id', $reviewerId)
                ->where('status', 'completed')
                ->with(['application.program', 'application.term'])
                ->orderBy('completed_at', 'desc')
                ->limit(10)
                ->get();
            
            // Get review queue if committee member
            $committeeQueue = [];
            if (Auth::user()->hasRole('committee_member')) {
                $committeeQueue = $this->getCommitteeQueue();
            }

            return view('admissions.reviews.dashboard', compact(
                'statistics',
                'prioritizedReviews',
                'completedReviews',
                'committeeQueue'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load reviewer dashboard', [
                'reviewer_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Failed to load review dashboard.');
        }
    }

    /**
     * Display review form for an application.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function reviewApplication($id)
    {
        try {
            // Get the review assignment
            $review = ApplicationReview::where('application_id', $id)
                ->where('reviewer_id', Auth::id())
                ->with(['application' => function($query) {
                    $query->with([
                        'program',
                        'term',
                        'documents',
                        'checklistItems',
                        'recommendations'
                    ]);
                }])
                ->firstOrFail();

            // Check if review can be edited
            if ($review->status === 'completed' && !$this->canEditCompletedReview($review)) {
                return redirect()->route('reviews.show', $review->id)
                    ->with('warning', 'This review has been completed and cannot be edited.');
            }

            // Mark as in progress if pending
            if ($review->status === 'pending') {
                $review->status = 'in_progress';
                $review->started_at = now();
                $review->save();
            }

            // Get application details
            $application = $review->application;
            
            // Get other reviews for comparison (if allowed)
            $otherReviews = [];
            if ($this->canViewOtherReviews($review)) {
                $otherReviews = ApplicationReview::where('application_id', $id)
                    ->where('id', '!=', $review->id)
                    ->where('status', 'completed')
                    ->with('reviewer')
                    ->get();
            }

            // Get recommendation letters
            $recommendations = RecommendationLetter::where('application_id', $id)
                ->where('status', 'submitted')
                ->get();

            // Get review guidelines
            $guidelines = $this->getReviewGuidelines($review->review_stage, $application->application_type);

            // Get previous notes
            $previousNotes = ApplicationNote::where('application_id', $id)
                ->where('created_by', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();

            // Track review start time for duration calculation
            session(['review_start_time_' . $review->id => now()]);

            return view('admissions.reviews.review-form', compact(
                'review',
                'application',
                'otherReviews',
                'recommendations',
                'guidelines',
                'previousNotes'
            ));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('reviews.my-reviews')
                ->with('error', 'Review assignment not found.');
        } catch (Exception $e) {
            Log::error('Failed to load review form', [
                'application_id' => $id,
                'reviewer_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('reviews.my-reviews')
                ->with('error', 'Failed to load review form.');
        }
    }

    /**
     * Submit review for an application.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitReview(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // Get the review
            $review = ApplicationReview::where('id', $id)
                ->where('reviewer_id', Auth::id())
                ->firstOrFail();

            // Validate review data
            $validated = $request->validate([
                // Ratings
                'academic_rating' => 'required|integer|between:1,5',
                'extracurricular_rating' => 'nullable|integer|between:1,5',
                'essay_rating' => 'nullable|integer|between:1,5',
                'recommendation_rating' => 'nullable|integer|between:1,5',
                'interview_rating' => 'nullable|integer|between:1,5',
                'overall_rating' => 'required|integer|between:1,5',
                
                // Comments
                'academic_comments' => 'required|string|max:2000',
                'extracurricular_comments' => 'nullable|string|max:2000',
                'essay_comments' => 'nullable|string|max:2000',
                'strengths' => 'required|string|max:2000',
                'weaknesses' => 'required|string|max:2000',
                'additional_comments' => 'nullable|string|max:2000',
                
                // Recommendation
                'recommendation' => 'required|in:' . implode(',', array_keys(self::RECOMMENDATIONS)),
                
                // Flags
                'flag_for_interview' => 'boolean',
                'flag_for_committee' => 'boolean',
                'request_additional_info' => 'boolean',
                'additional_info_needed' => 'required_if:request_additional_info,true|nullable|string|max:500',
            ]);

            // Calculate review duration
            $startTime = session('review_start_time_' . $review->id);
            $duration = $startTime ? now()->diffInMinutes($startTime) : null;

            // Update review
            $review->fill($validated);
            $review->status = 'completed';
            $review->completed_at = now();
            $review->review_duration_minutes = $duration;
            $review->save();

            // Submit review using service
            $this->reviewService->submitReview($review->id, $validated);

            // Add review note
            ApplicationNote::create([
                'application_id' => $review->application_id,
                'note' => "Review completed: {$validated['recommendation']}",
                'type' => 'review',
                'created_by' => Auth::id(),
            ]);

            // Handle special flags
            if ($request->boolean('flag_for_interview')) {
                $this->flagForInterview($review->application_id);
            }

            if ($request->boolean('flag_for_committee')) {
                $this->flagForCommittee($review->application_id);
            }

            if ($request->boolean('request_additional_info')) {
                $this->requestAdditionalInfo($review->application_id, $validated['additional_info_needed']);
            }

            // Clear session
            session()->forget('review_start_time_' . $review->id);

            // Check if all reviews are complete for this stage
            $this->checkStageCompletion($review->application_id, $review->review_stage);

            DB::commit();

            return redirect()->route('reviews.my-reviews')
                ->with('success', 'Review submitted successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to submit review', [
                'review_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to submit review. Please try again.')
                ->withInput();
        }
    }

    /**
     * Compare multiple reviews for an application.
     *
     * @param int $applicationId
     * @return \Illuminate\View\View
     */
    public function compareReviews($applicationId)
    {
        try {
            // Check permissions
            if (!Auth::user()->hasAnyRole(['admin', 'admissions_director', 'committee_chair'])) {
                abort(403, 'Unauthorized to view review comparisons.');
            }

            $application = AdmissionApplication::with(['program', 'term'])->findOrFail($applicationId);
            
            // Get all completed reviews
            $reviews = ApplicationReview::where('application_id', $applicationId)
                ->where('status', 'completed')
                ->with('reviewer')
                ->orderBy('review_stage')
                ->orderBy('completed_at')
                ->get();

            if ($reviews->isEmpty()) {
                return redirect()->back()
                    ->with('warning', 'No completed reviews available for comparison.');
            }

            // Calculate consensus metrics
            $consensus = $this->reviewService->checkReviewConsensus($applicationId);
            
            // Get average ratings
            $averageRatings = $this->calculateAverageRatings($reviews);
            
            // Get recommendation distribution
            $recommendationDistribution = $reviews->groupBy('recommendation')
                ->map(function ($group) {
                    return $group->count();
                })->toArray();

            // Generate comparison chart data
            $chartData = $this->generateComparisonChartData($reviews);

            return view('admissions.reviews.compare', compact(
                'application',
                'reviews',
                'consensus',
                'averageRatings',
                'recommendationDistribution',
                'chartData'
            ));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.applications.index')
                ->with('error', 'Application not found.');
        } catch (Exception $e) {
            Log::error('Failed to load review comparison', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to load review comparison.');
        }
    }

    /**
     * Display review queue.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function reviewQueue(Request $request)
    {
        try {
            $reviewerId = Auth::id();
            
            // Get filter parameters
            $filters = [
                'stage' => $request->get('stage'),
                'program' => $request->get('program_id'),
                'priority' => $request->get('priority'),
                'deadline' => $request->get('deadline'),
            ];

            // Get reviews based on filters
            $queue = $this->reviewService->getReviewQueue($reviewerId, $filters);
            
            // Paginate results
            $reviews = $queue['reviews']->paginate(20)->appends($request->all());
            
            // Get filter options
            $programs = AcademicProgram::where('is_active', true)->pluck('name', 'id');
            $stages = self::REVIEW_STAGES;
            $priorities = self::PRIORITIES;

            return view('admissions.reviews.queue', compact(
                'reviews',
                'programs',
                'stages',
                'priorities',
                'filters'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load review queue', [
                'reviewer_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('reviews.my-reviews')
                ->with('error', 'Failed to load review queue.');
        }
    }

    /**
     * Display review statistics.
     *
     * @return \Illuminate\View\View
     */
    public function reviewStatistics()
    {
        try {
            $reviewerId = Auth::id();
            
            // Get comprehensive statistics
            $stats = [
                'personal' => $this->getReviewerStatistics($reviewerId),
                'comparison' => $this->getReviewerComparison($reviewerId),
                'trends' => $this->getReviewTrends($reviewerId),
                'distribution' => $this->getReviewDistribution($reviewerId),
            ];

            // Get time-based analytics
            $timeAnalytics = $this->getTimeBasedAnalytics($reviewerId);
            
            // Get quality metrics
            $qualityMetrics = $this->getQualityMetrics($reviewerId);

            return view('admissions.reviews.statistics', compact(
                'stats',
                'timeAnalytics',
                'qualityMetrics'
            ));

        } catch (Exception $e) {
            Log::error('Failed to load review statistics', [
                'reviewer_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('reviews.my-reviews')
                ->with('error', 'Failed to load statistics.');
        }
    }

    /**
     * Save review draft (AJAX).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveDraft(Request $request, $id)
    {
        try {
            $review = ApplicationReview::where('id', $id)
                ->where('reviewer_id', Auth::id())
                ->firstOrFail();

            // Save draft data
            $review->fill($request->all());
            $review->save();

            return response()->json([
                'success' => true,
                'message' => 'Draft saved',
                'saved_at' => now()->format('g:i A'),
            ]);

        } catch (Exception $e) {
            Log::error('Failed to save review draft', [
                'review_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save draft',
            ], 500);
        }
    }

    /**
     * Request review deadline extension.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function requestExtension(Request $request, $id)
    {
        try {
            $review = ApplicationReview::where('id', $id)
                ->where('reviewer_id', Auth::id())
                ->firstOrFail();

            $validated = $request->validate([
                'requested_deadline' => 'required|date|after:today',
                'reason' => 'required|string|max:500',
            ]);

            // Create extension request
            ApplicationNote::create([
                'application_id' => $review->application_id,
                'note' => "Extension requested until {$validated['requested_deadline']}: {$validated['reason']}",
                'type' => 'extension_request',
                'created_by' => Auth::id(),
            ]);

            // Notify admissions office
            $this->notificationService->sendExtensionRequest($review->id, $validated);

            return redirect()->back()
                ->with('success', 'Extension request submitted.');

        } catch (Exception $e) {
            Log::error('Failed to request extension', [
                'review_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to submit extension request.');
        }
    }

    /**
     * Recuse from review.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function recuseFromReview(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $review = ApplicationReview::where('id', $id)
                ->where('reviewer_id', Auth::id())
                ->firstOrFail();

            $validated = $request->validate([
                'reason' => 'required|string|max:500',
                'conflict_type' => 'required|in:personal,professional,other',
            ]);

            // Update review status
            $review->status = 'recused';
            $review->recusal_reason = $validated['reason'];
            $review->recusal_type = $validated['conflict_type'];
            $review->recused_at = now();
            $review->save();

            // Log recusal
            ApplicationNote::create([
                'application_id' => $review->application_id,
                'note' => "Reviewer recused: {$validated['reason']}",
                'type' => 'recusal',
                'created_by' => Auth::id(),
            ]);

            // Request new reviewer assignment
            $this->reviewService->requestReviewerReplacement($review->id);

            DB::commit();

            return redirect()->route('reviews.my-reviews')
                ->with('success', 'Successfully recused from review. A new reviewer will be assigned.');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to recuse from review', [
                'review_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to process recusal request.');
        }
    }

    /**
     * Helper Methods
     */

    /**
     * Get reviewer statistics.
     */
    private function getReviewerStatistics($reviewerId): array
    {
        $cacheKey = "reviewer_stats_{$reviewerId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($reviewerId) {
            $reviews = ApplicationReview::where('reviewer_id', $reviewerId);
            
            return [
                'total_assigned' => (clone $reviews)->count(),
                'completed' => (clone $reviews)->where('status', 'completed')->count(),
                'in_progress' => (clone $reviews)->where('status', 'in_progress')->count(),
                'pending' => (clone $reviews)->where('status', 'pending')->count(),
                'overdue' => (clone $reviews)->where('status', 'pending')
                    ->where('deadline', '<', now())
                    ->count(),
                'average_time' => (clone $reviews)->where('status', 'completed')
                    ->avg('review_duration_minutes'),
                'this_week' => (clone $reviews)->where('status', 'completed')
                    ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'completion_rate' => $this->calculateCompletionRate($reviewerId),
            ];
        });
    }

    /**
     * Prioritize reviews based on deadlines and importance.
     */
    private function prioritizeReviews($reviews): array
    {
        $prioritized = [
            'urgent' => [],
            'high' => [],
            'normal' => [],
            'low' => [],
        ];

        foreach ($reviews as $review) {
            $daysUntilDeadline = $review->deadline ? 
                now()->diffInDays($review->deadline, false) : 
                999;

            if ($daysUntilDeadline <= 2) {
                $prioritized['urgent'][] = $review;
            } elseif ($daysUntilDeadline <= 5) {
                $prioritized['high'][] = $review;
            } elseif ($daysUntilDeadline <= 7) {
                $prioritized['normal'][] = $review;
            } else {
                $prioritized['low'][] = $review;
            }
        }

        return $prioritized;
    }

    /**
     * Get committee review queue.
     */
    private function getCommitteeQueue(): array
    {
        return ApplicationReview::where('review_stage', 'committee_review')
            ->where('status', 'pending')
            ->with(['application.program', 'application.term'])
            ->orderBy('assigned_at')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Check if reviewer can edit completed review.
     */
    private function canEditCompletedReview($review): bool
    {
        // Allow editing within 24 hours of completion
        return $review->completed_at && 
               $review->completed_at->diffInHours(now()) <= 24;
    }

    /**
     * Check if reviewer can view other reviews.
     */
    private function canViewOtherReviews($review): bool
    {
        return Auth::user()->hasAnyRole(['admin', 'committee_member']) ||
               $review->review_stage === 'committee_review';
    }

    /**
     * Get review guidelines.
     */
    private function getReviewGuidelines($stage, $applicationType): array
    {
        // This would typically come from a database or config file
        return [
            'focus_areas' => $this->getFocusAreas($stage),
            'evaluation_criteria' => $this->getEvaluationCriteria($applicationType),
            'red_flags' => $this->getRedFlags(),
            'best_practices' => $this->getBestPractices(),
        ];
    }

    /**
     * Get focus areas for review stage.
     */
    private function getFocusAreas($stage): array
    {
        return match($stage) {
            'initial_review' => [
                'Completeness of application',
                'Minimum requirements met',
                'Document authenticity',
            ],
            'academic_review' => [
                'Academic performance',
                'Test scores',
                'Course rigor',
                'Academic potential',
            ],
            'department_review' => [
                'Fit with program',
                'Research interests',
                'Career goals alignment',
            ],
            'committee_review' => [
                'Overall merit',
                'Diversity contribution',
                'Special circumstances',
            ],
            default => ['General assessment'],
        };
    }

    /**
     * Calculate completion rate.
     */
    private function calculateCompletionRate($reviewerId): float
    {
        $total = ApplicationReview::where('reviewer_id', $reviewerId)->count();
        $completed = ApplicationReview::where('reviewer_id', $reviewerId)
            ->where('status', 'completed')
            ->count();
        
        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    // Additional helper methods would continue here...
}