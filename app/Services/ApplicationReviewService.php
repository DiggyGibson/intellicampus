<?php

namespace App\Services;

use App\Models\AdmissionApplication;
use App\Models\ApplicationReview;
use App\Models\ApplicationNote;
use App\Models\ApplicationStatusHistory;
use App\Models\ApplicationCommunication;
use App\Models\User;
use App\Models\AcademicProgram;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Exception;

class ApplicationReviewService
{
    /**
     * Review stages and their order
     */
    private const REVIEW_STAGES = [
        'initial_review' => 1,
        'academic_review' => 2,
        'department_review' => 3,
        'committee_review' => 4,
        'final_review' => 5,
    ];

    /**
     * Minimum reviewers required per stage
     */
    private const MIN_REVIEWERS_PER_STAGE = [
        'initial_review' => 1,
        'academic_review' => 2,
        'department_review' => 1,
        'committee_review' => 3,
        'final_review' => 1,
    ];

    /**
     * Review workload limits
     */
    private const MAX_ACTIVE_REVIEWS_PER_REVIEWER = 20;
    private const MAX_DAILY_ASSIGNMENTS = 10;

    /**
     * Assign a reviewer to an application
     *
     * @param int $applicationId
     * @param int $reviewerId
     * @param string $stage
     * @return ApplicationReview
     * @throws Exception
     */
    public function assignReviewer(int $applicationId, int $reviewerId, string $stage): ApplicationReview
    {
        DB::beginTransaction();

        try {
            // Validate application exists and is in reviewable status
            $application = AdmissionApplication::findOrFail($applicationId);
            
            if (!in_array($application->status, ['submitted', 'under_review', 'committee_review'])) {
                throw new Exception("Application is not in a reviewable status: {$application->status}");
            }

            // Validate reviewer
            $reviewer = User::findOrFail($reviewerId);
            
            if (!$this->canReviewApplications($reviewer)) {
                throw new Exception("User {$reviewer->name} does not have permission to review applications");
            }

            // Check if reviewer is already assigned to this application at this stage
            $existingReview = ApplicationReview::where('application_id', $applicationId)
                ->where('reviewer_id', $reviewerId)
                ->where('review_stage', $stage)
                ->first();

            if ($existingReview) {
                throw new Exception("Reviewer is already assigned to this application for {$stage}");
            }

            // Check reviewer workload
            if (!$this->checkReviewerCapacity($reviewerId)) {
                throw new Exception("Reviewer has reached maximum active review capacity");
            }

            // Check for conflicts of interest
            if ($this->hasConflictOfInterest($application, $reviewer)) {
                throw new Exception("Conflict of interest detected for this reviewer");
            }

            // Create review assignment
            $review = new ApplicationReview();
            $review->application_id = $applicationId;
            $review->reviewer_id = $reviewerId;
            $review->review_stage = $stage;
            $review->status = 'pending';
            $review->assigned_at = now();
            $review->save();

            // Update application status if needed
            if ($application->status === 'submitted') {
                $application->status = 'under_review';
                $application->save();
                
                $this->logStatusChange($application, 'submitted', 'under_review', 'Review process started');
            }

            // Send notification to reviewer
            $this->notifyReviewerOfAssignment($review);

            // Add note to application
            $this->addReviewNote($application, "Assigned to {$reviewer->name} for {$stage}");

            // Clear reviewer cache
            Cache::forget("reviewer_queue_{$reviewerId}");

            DB::commit();

            Log::info('Reviewer assigned', [
                'application_id' => $applicationId,
                'reviewer_id' => $reviewerId,
                'stage' => $stage,
            ]);

            return $review;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign reviewer', [
                'application_id' => $applicationId,
                'reviewer_id' => $reviewerId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Submit a review for an application
     *
     * @param int $reviewId
     * @param array $reviewData
     * @return ApplicationReview
     * @throws Exception
     */
    public function submitReview(int $reviewId, array $reviewData): ApplicationReview
    {
        DB::beginTransaction();

        try {
            $review = ApplicationReview::with('application')->findOrFail($reviewId);

            // Validate review can be submitted
            if ($review->status === 'completed') {
                throw new Exception("Review has already been submitted");
            }

            // Validate review data
            $this->validateReviewData($reviewData);

            // Update review with submitted data
            $review->fill($reviewData);
            $review->status = 'completed';
            $review->completed_at = now();
            
            // Calculate review duration
            if ($review->started_at) {
                $review->review_duration_minutes = $review->started_at->diffInMinutes(now());
            }

            // Calculate overall rating if individual ratings provided
            if (!isset($review->overall_rating)) {
                $review->overall_rating = $this->calculateOverallRating($reviewData);
            }

            $review->save();

            // Check if all reviews for this stage are complete
            $stageComplete = $this->checkStageCompletion($review->application_id, $review->review_stage);

            if ($stageComplete) {
                // Move to next stage or complete review process
                $this->progressToNextStage($review->application_id, $review->review_stage);
            }

            // Update application review statistics
            $this->updateApplicationReviewStats($review->application_id);

            // Add review summary note
            $this->addReviewNote(
                $review->application,
                "Review completed by {$review->reviewer->name} - Rating: {$review->overall_rating}/5 - Recommendation: {$review->recommendation}"
            );

            DB::commit();

            Log::info('Review submitted', [
                'review_id' => $reviewId,
                'application_id' => $review->application_id,
                'overall_rating' => $review->overall_rating,
                'recommendation' => $review->recommendation,
            ]);

            return $review;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit review', [
                'review_id' => $reviewId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate average rating for an application
     *
     * @param int $applicationId
     * @return array
     */
    public function calculateAverageRating(int $applicationId): array
    {
        $reviews = ApplicationReview::where('application_id', $applicationId)
            ->where('status', 'completed')
            ->get();

        if ($reviews->isEmpty()) {
            return [
                'average_overall' => 0,
                'average_academic' => 0,
                'average_extracurricular' => 0,
                'average_essay' => 0,
                'average_recommendation' => 0,
                'average_interview' => 0,
                'total_reviews' => 0,
                'reviews_by_stage' => [],
            ];
        }

        $averages = [
            'average_overall' => round($reviews->avg('overall_rating'), 2),
            'average_academic' => round($reviews->avg('academic_rating'), 2),
            'average_extracurricular' => round($reviews->avg('extracurricular_rating'), 2),
            'average_essay' => round($reviews->avg('essay_rating'), 2),
            'average_recommendation' => round($reviews->avg('recommendation_rating'), 2),
            'average_interview' => round($reviews->avg('interview_rating'), 2),
            'total_reviews' => $reviews->count(),
            'reviews_by_stage' => [],
        ];

        // Calculate averages by stage
        $stages = $reviews->groupBy('review_stage');
        foreach ($stages as $stage => $stageReviews) {
            $averages['reviews_by_stage'][$stage] = [
                'count' => $stageReviews->count(),
                'average_rating' => round($stageReviews->avg('overall_rating'), 2),
                'recommendations' => $stageReviews->pluck('recommendation')->countBy()->toArray(),
            ];
        }

        return $averages;
    }

    /**
     * Escalate a review to higher authority
     *
     * @param int $applicationId
     * @param string $reason
     * @param string $escalateTo
     * @return void
     * @throws Exception
     */
    public function escalateReview(int $applicationId, string $reason, string $escalateTo = 'committee'): void
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);

            // Determine escalation stage
            $newStage = $escalateTo === 'committee' ? 'committee_review' : 'final_review';

            // Update application status
            $previousStatus = $application->status;
            $application->status = $newStage;
            $application->save();

            // Log escalation
            $this->logStatusChange($application, $previousStatus, $newStage, "Escalated: {$reason}");

            // Cancel pending reviews at current stage
            ApplicationReview::where('application_id', $applicationId)
                ->where('status', 'pending')
                ->update(['status' => 'skipped']);

            // Assign committee members
            if ($escalateTo === 'committee') {
                $this->assignCommitteeReviewers($applicationId);
            } else {
                // Assign to senior reviewer
                $this->assignSeniorReviewer($applicationId);
            }

            // Add escalation note
            $this->addReviewNote($application, "Review escalated to {$escalateTo}: {$reason}");

            // Send escalation notifications
            $this->sendEscalationNotifications($application, $reason);

            DB::commit();

            Log::info('Review escalated', [
                'application_id' => $applicationId,
                'escalated_to' => $escalateTo,
                'reason' => $reason,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to escalate review', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get review queue for a reviewer
     *
     * @param int $reviewerId
     * @param array $filters
     * @return Collection
     */
    public function getReviewQueue(int $reviewerId, array $filters = []): Collection
    {
        $cacheKey = "reviewer_queue_{$reviewerId}_" . md5(json_encode($filters));
        
        return Cache::remember($cacheKey, 300, function () use ($reviewerId, $filters) {
            $query = ApplicationReview::with(['application.program', 'application.term'])
                ->where('reviewer_id', $reviewerId)
                ->where('status', 'pending');

            // Apply filters
            if (isset($filters['stage'])) {
                $query->where('review_stage', $filters['stage']);
            }

            if (isset($filters['priority'])) {
                $query->whereHas('application', function ($q) use ($filters) {
                    $q->where('priority', $filters['priority']);
                });
            }

            if (isset($filters['program_id'])) {
                $query->whereHas('application', function ($q) use ($filters) {
                    $q->where('program_id', $filters['program_id']);
                });
            }

            // Sort by priority and age
            $query->orderByDesc(function ($q) {
                $q->select('priority')
                    ->from('admission_applications')
                    ->whereColumn('admission_applications.id', 'application_reviews.application_id');
            })->orderBy('assigned_at');

            return $query->get()->map(function ($review) {
                $application = $review->application;
                return [
                    'review_id' => $review->id,
                    'application_id' => $application->id,
                    'application_number' => $application->application_number,
                    'applicant_name' => $application->first_name . ' ' . $application->last_name,
                    'program' => $application->program->name ?? 'N/A',
                    'review_stage' => $review->review_stage,
                    'assigned_at' => $review->assigned_at,
                    'days_pending' => $review->assigned_at->diffInDays(now()),
                    'priority' => $application->priority ?? 'normal',
                    'completion_percentage' => $application->completionPercentage(),
                ];
            });
        });
    }

    /**
     * Bulk assign reviews to multiple reviewers
     *
     * @param array $applicationIds
     * @param array $reviewerIds
     * @param string $stage
     * @param string $method
     * @return array
     */
    public function bulkAssignReviews(
        array $applicationIds, 
        array $reviewerIds, 
        string $stage,
        string $method = 'round_robin'
    ): array {
        $results = [
            'successful' => [],
            'failed' => [],
        ];

        DB::beginTransaction();

        try {
            // Validate all reviewers first
            $reviewers = User::whereIn('id', $reviewerIds)->get();
            
            if ($reviewers->count() !== count($reviewerIds)) {
                throw new Exception("One or more reviewers not found");
            }

            // Check reviewer permissions
            foreach ($reviewers as $reviewer) {
                if (!$this->canReviewApplications($reviewer)) {
                    throw new Exception("User {$reviewer->name} cannot review applications");
                }
            }

            // Get applications
            $applications = AdmissionApplication::whereIn('id', $applicationIds)
                ->where('status', '!=', 'withdrawn')
                ->get();

            // Assign based on method
            $reviewerIndex = 0;
            foreach ($applications as $application) {
                try {
                    $reviewerId = $this->selectReviewer($reviewerIds, $method, $reviewerIndex, $application);
                    
                    $review = $this->assignReviewer($application->id, $reviewerId, $stage);
                    
                    $results['successful'][] = [
                        'application_id' => $application->id,
                        'reviewer_id' => $reviewerId,
                        'review_id' => $review->id,
                    ];
                    
                    if ($method === 'round_robin') {
                        $reviewerIndex = ($reviewerIndex + 1) % count($reviewerIds);
                    }
                    
                } catch (Exception $e) {
                    $results['failed'][] = [
                        'application_id' => $application->id,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            Log::info('Bulk review assignment completed', [
                'successful' => count($results['successful']),
                'failed' => count($results['failed']),
            ]);

            return $results;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Bulk assignment failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate review report for an application
     *
     * @param int $applicationId
     * @return array
     */
    public function generateReviewReport(int $applicationId): array
    {
        $application = AdmissionApplication::with([
            'reviews.reviewer',
            'program',
            'term'
        ])->findOrFail($applicationId);

        $reviews = $application->reviews->where('status', 'completed');

        // Calculate statistics
        $stats = $this->calculateAverageRating($applicationId);

        // Get review timeline
        $timeline = $this->getReviewTimeline($applicationId);

        // Compile strengths and weaknesses
        $strengths = [];
        $weaknesses = [];
        $recommendations = [];

        foreach ($reviews as $review) {
            if ($review->strengths) {
                $strengths[] = $review->strengths;
            }
            if ($review->weaknesses) {
                $weaknesses[] = $review->weaknesses;
            }
            if ($review->recommendation) {
                $recommendations[$review->recommendation] = 
                    ($recommendations[$review->recommendation] ?? 0) + 1;
            }
        }

        // Determine consensus
        $consensus = $this->determineConsensus($recommendations);

        return [
            'application' => [
                'id' => $application->id,
                'number' => $application->application_number,
                'applicant' => $application->first_name . ' ' . $application->last_name,
                'program' => $application->program->name ?? 'N/A',
                'term' => $application->term->name ?? 'N/A',
                'status' => $application->status,
            ],
            'statistics' => $stats,
            'timeline' => $timeline,
            'reviews' => $reviews->map(function ($review) {
                return [
                    'reviewer' => $review->reviewer->name,
                    'stage' => $review->review_stage,
                    'date' => $review->completed_at?->format('Y-m-d'),
                    'overall_rating' => $review->overall_rating,
                    'recommendation' => $review->recommendation,
                    'academic_rating' => $review->academic_rating,
                    'extracurricular_rating' => $review->extracurricular_rating,
                    'essay_rating' => $review->essay_rating,
                    'comments' => $review->additional_comments,
                ];
            }),
            'strengths' => array_unique($strengths),
            'weaknesses' => array_unique($weaknesses),
            'recommendation_distribution' => $recommendations,
            'consensus' => $consensus,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Check review consensus
     *
     * @param int $applicationId
     * @return array
     */
    public function checkReviewConsensus(int $applicationId): array
    {
        $reviews = ApplicationReview::where('application_id', $applicationId)
            ->where('status', 'completed')
            ->get();

        if ($reviews->isEmpty()) {
            return [
                'has_consensus' => false,
                'consensus_level' => 'none',
                'recommendation' => null,
                'confidence' => 0,
            ];
        }

        // Get recommendation distribution
        $recommendations = $reviews->pluck('recommendation')->countBy();
        $totalReviews = $reviews->count();
        
        // Calculate rating variance
        $ratings = $reviews->pluck('overall_rating');
        $avgRating = $ratings->avg();
        $variance = $ratings->map(function ($rating) use ($avgRating) {
            return pow($rating - $avgRating, 2);
        })->avg();

        // Determine consensus based on recommendations
        $maxRecommendation = $recommendations->max();
        $consensusRecommendation = $recommendations->search($maxRecommendation);
        $consensusPercentage = ($maxRecommendation / $totalReviews) * 100;

        // Determine consensus level
        $consensusLevel = match(true) {
            $consensusPercentage >= 80 && $variance < 0.5 => 'strong',
            $consensusPercentage >= 60 && $variance < 1 => 'moderate',
            $consensusPercentage >= 40 => 'weak',
            default => 'none',
        };

        return [
            'has_consensus' => $consensusLevel !== 'none',
            'consensus_level' => $consensusLevel,
            'recommendation' => $consensusRecommendation,
            'confidence' => round($consensusPercentage, 2),
            'rating_variance' => round($variance, 2),
            'total_reviews' => $totalReviews,
            'recommendation_distribution' => $recommendations->toArray(),
        ];
    }

    /**
     * Request additional review for an application
     *
     * @param int $applicationId
     * @param string $reason
     * @param string $specificArea
     * @return ApplicationReview
     * @throws Exception
     */
    public function requestAdditionalReview(
        int $applicationId, 
        string $reason, 
        string $specificArea = null
    ): ApplicationReview {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::findOrFail($applicationId);

            // Check current consensus
            $consensus = $this->checkReviewConsensus($applicationId);
            
            if ($consensus['consensus_level'] === 'strong') {
                throw new Exception("Strong consensus already exists. Additional review may not be necessary.");
            }

            // Find appropriate reviewer based on specific area
            $reviewer = $this->findSpecialistReviewer($application, $specificArea);
            
            if (!$reviewer) {
                throw new Exception("No specialist reviewer available for {$specificArea}");
            }

            // Create additional review request
            $review = new ApplicationReview();
            $review->application_id = $applicationId;
            $review->reviewer_id = $reviewer->id;
            $review->review_stage = 'additional_review';
            $review->status = 'pending';
            $review->assigned_at = now();
            $review->notes = "Additional review requested: {$reason}. Focus area: {$specificArea}";
            $review->save();

            // Add note to application
            $this->addReviewNote(
                $application, 
                "Additional review requested by " . auth()->user()->name . ". Reason: {$reason}"
            );

            // Notify the specialist reviewer
            $this->notifyReviewerOfUrgentAssignment($review, $reason);

            DB::commit();

            Log::info('Additional review requested', [
                'application_id' => $applicationId,
                'reviewer_id' => $reviewer->id,
                'reason' => $reason,
                'area' => $specificArea,
            ]);

            return $review;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to request additional review', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Private helper methods
     */

    /**
     * Check if user can review applications
     */
    private function canReviewApplications(User $user): bool
    {
        // Check user roles/permissions
        $allowedRoles = [
            'admissions_officer',
            'faculty',
            'department_head',
            'academic_administrator',
            'dean',
            'admissions_committee',
        ];

        return $user->hasAnyRole($allowedRoles);
    }

    /**
     * Check reviewer capacity
     */
    private function checkReviewerCapacity(int $reviewerId): bool
    {
        $activeReviews = ApplicationReview::where('reviewer_id', $reviewerId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        return $activeReviews < self::MAX_ACTIVE_REVIEWS_PER_REVIEWER;
    }

    /**
     * Check for conflicts of interest
     */
    private function hasConflictOfInterest(AdmissionApplication $application, User $reviewer): bool
    {
        // Check if reviewer has same last name (possible family)
        if (strtolower($application->last_name) === strtolower($reviewer->last_name)) {
            return true;
        }

        // Check if reviewer is from same previous institution (for faculty)
        if ($reviewer->institution && 
            $application->previous_institution === $reviewer->institution) {
            return true;
        }

        // Check custom conflict rules
        // This could be expanded based on your requirements

        return false;
    }

    /**
     * Validate review data
     */
    private function validateReviewData(array $data): void
    {
        $requiredFields = ['recommendation'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Required field '{$field}' is missing");
            }
        }

        // Validate ratings are within range
        $ratingFields = [
            'academic_rating',
            'extracurricular_rating',
            'essay_rating',
            'recommendation_rating',
            'interview_rating',
            'overall_rating',
        ];

        foreach ($ratingFields as $field) {
            if (isset($data[$field]) && ($data[$field] < 1 || $data[$field] > 5)) {
                throw new Exception("Rating '{$field}' must be between 1 and 5");
            }
        }

        // Validate recommendation value
        $validRecommendations = [
            'strongly_recommend',
            'recommend',
            'recommend_with_reservations',
            'do_not_recommend',
            'defer_decision',
        ];

        if (!in_array($data['recommendation'], $validRecommendations)) {
            throw new Exception("Invalid recommendation value");
        }
    }

    /**
     * Calculate overall rating from individual ratings
     */
    private function calculateOverallRating(array $reviewData): int
    {
        $weights = [
            'academic_rating' => 0.35,
            'extracurricular_rating' => 0.15,
            'essay_rating' => 0.25,
            'recommendation_rating' => 0.15,
            'interview_rating' => 0.10,
        ];

        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($weights as $field => $weight) {
            if (isset($reviewData[$field]) && $reviewData[$field] > 0) {
                $weightedSum += $reviewData[$field] * $weight;
                $totalWeight += $weight;
            }
        }

        if ($totalWeight > 0) {
            return round($weightedSum / $totalWeight);
        }

        return 3; // Default middle rating
    }

    /**
     * Check if all reviews for a stage are complete
     */
    private function checkStageCompletion(int $applicationId, string $stage): bool
    {
        $requiredReviews = self::MIN_REVIEWERS_PER_STAGE[$stage] ?? 1;
        
        $completedReviews = ApplicationReview::where('application_id', $applicationId)
            ->where('review_stage', $stage)
            ->where('status', 'completed')
            ->count();

        return $completedReviews >= $requiredReviews;
    }

    /**
     * Progress application to next review stage
     */
    private function progressToNextStage(int $applicationId, string $currentStage): void
    {
        $application = AdmissionApplication::findOrFail($applicationId);
        
        // Determine next stage
        $currentStageOrder = self::REVIEW_STAGES[$currentStage] ?? 0;
        $nextStage = null;
        
        foreach (self::REVIEW_STAGES as $stage => $order) {
            if ($order === $currentStageOrder + 1) {
                $nextStage = $stage;
                break;
            }
        }

        if ($nextStage) {
            // Auto-assign reviewers for next stage
            $this->autoAssignReviewers($applicationId, $nextStage);
            
            // Update application status if needed
            if ($nextStage === 'committee_review') {
                $application->status = 'committee_review';
                $application->save();
            }
        } else {
            // All review stages complete
            $application->status = 'decision_pending';
            $application->save();
            
            // Trigger decision process
            $this->triggerDecisionProcess($applicationId);
        }
    }

    /**
     * Auto-assign reviewers for a stage
     */
    private function autoAssignReviewers(int $applicationId, string $stage): void
    {
        $requiredReviewers = self::MIN_REVIEWERS_PER_STAGE[$stage] ?? 1;
        
        // Find available reviewers
        $availableReviewers = $this->findAvailableReviewers($applicationId, $stage, $requiredReviewers);
        
        foreach ($availableReviewers as $reviewer) {
            try {
                $this->assignReviewer($applicationId, $reviewer->id, $stage);
            } catch (Exception $e) {
                Log::warning('Failed to auto-assign reviewer', [
                    'application_id' => $applicationId,
                    'reviewer_id' => $reviewer->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Find available reviewers
     */
    private function findAvailableReviewers(int $applicationId, string $stage, int $count): Collection
    {
        $application = AdmissionApplication::with('program')->findOrFail($applicationId);
        
        // Get reviewers based on stage
        $query = User::whereHas('roles', function ($q) use ($stage) {
            $roleMapping = [
                'initial_review' => 'admissions_officer',
                'academic_review' => 'faculty',
                'department_review' => 'department_head',
                'committee_review' => 'admissions_committee',
                'final_review' => 'dean',
            ];
            
            $q->where('name', $roleMapping[$stage] ?? 'admissions_officer');
        });

        // Filter by department if applicable
        if ($application->program && $application->program->department_id) {
            $query->where('department_id', $application->program->department_id);
        }

        // Exclude reviewers with conflicts
        $query->whereNotIn('id', function ($q) use ($applicationId) {
            $q->select('reviewer_id')
                ->from('application_reviews')
                ->where('application_id', $applicationId);
        });

        // Order by workload (least busy first)
        $query->withCount(['applicationReviews' => function ($q) {
            $q->whereIn('status', ['pending', 'in_progress']);
        }])->orderBy('application_reviews_count');

        return $query->limit($count)->get();
    }

    /**
     * Update application review statistics
     */
    private function updateApplicationReviewStats(int $applicationId): void
    {
        $stats = $this->calculateAverageRating($applicationId);
        
        $application = AdmissionApplication::find($applicationId);
        if ($application) {
            $application->review_stats = $stats;
            $application->save();
        }
    }

    /**
     * Get review timeline
     */
    private function getReviewTimeline(int $applicationId): array
    {
        $reviews = ApplicationReview::where('application_id', $applicationId)
            ->orderBy('assigned_at')
            ->get();

        return $reviews->map(function ($review) {
            return [
                'stage' => $review->review_stage,
                'reviewer' => $review->reviewer->name ?? 'N/A',
                'assigned' => $review->assigned_at?->format('Y-m-d H:i'),
                'started' => $review->started_at?->format('Y-m-d H:i'),
                'completed' => $review->completed_at?->format('Y-m-d H:i'),
                'duration_minutes' => $review->review_duration_minutes,
                'status' => $review->status,
            ];
        })->toArray();
    }

    /**
     * Determine consensus from recommendations
     */
    private function determineConsensus(array $recommendations): string
    {
        if (empty($recommendations)) {
            return 'no_consensus';
        }

        $maxCount = max($recommendations);
        $maxRecommendation = array_search($maxCount, $recommendations);
        $totalCount = array_sum($recommendations);
        
        $percentage = ($maxCount / $totalCount) * 100;
        
        if ($percentage >= 75) {
            return $maxRecommendation;
        } elseif ($percentage >= 50) {
            return 'majority_' . $maxRecommendation;
        } else {
            return 'no_clear_consensus';
        }
    }

    /**
     * Select reviewer based on assignment method
     */
    private function selectReviewer(
        array $reviewerIds, 
        string $method, 
        int &$index, 
        AdmissionApplication $application
    ): int {
        switch ($method) {
            case 'round_robin':
                return $reviewerIds[$index];
                
            case 'random':
                return $reviewerIds[array_rand($reviewerIds)];
                
            case 'workload':
                // Get reviewer with least active reviews
                $workloads = [];
                foreach ($reviewerIds as $id) {
                    $count = ApplicationReview::where('reviewer_id', $id)
                        ->whereIn('status', ['pending', 'in_progress'])
                        ->count();
                    $workloads[$id] = $count;
                }
                asort($workloads);
                return array_key_first($workloads);
                
            case 'expertise':
                // Match based on program/department expertise
                return $this->matchByExpertise($reviewerIds, $application);
                
            default:
                return $reviewerIds[0];
        }
    }

    /**
     * Match reviewer by expertise
     */
    private function matchByExpertise(array $reviewerIds, AdmissionApplication $application): int
    {
        // This would check reviewer expertise against application program
        // For now, return first reviewer
        return $reviewerIds[0];
    }

    /**
     * Find specialist reviewer
     */
    private function findSpecialistReviewer(AdmissionApplication $application, ?string $area): ?User
    {
        $query = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['faculty', 'academic_administrator']);
        });

        if ($area) {
            // Add expertise matching logic here
            $query->where('expertise', 'like', "%{$area}%");
        }

        return $query->first();
    }

    /**
     * Assign committee reviewers
     */
    private function assignCommitteeReviewers(int $applicationId): void
    {
        $committeeMembers = User::whereHas('roles', function ($q) {
            $q->where('name', 'admissions_committee');
        })->limit(3)->get();

        foreach ($committeeMembers as $member) {
            try {
                $this->assignReviewer($applicationId, $member->id, 'committee_review');
            } catch (Exception $e) {
                Log::warning('Failed to assign committee member', [
                    'application_id' => $applicationId,
                    'member_id' => $member->id,
                ]);
            }
        }
    }

    /**
     * Assign senior reviewer
     */
    private function assignSeniorReviewer(int $applicationId): void
    {
        $seniorReviewer = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['dean', 'academic_administrator']);
        })->first();

        if ($seniorReviewer) {
            $this->assignReviewer($applicationId, $seniorReviewer->id, 'final_review');
        }
    }

    /**
     * Add review note to application
     */
    private function addReviewNote(AdmissionApplication $application, string $note): void
    {
        if (class_exists(ApplicationNote::class)) {
            ApplicationNote::create([
                'application_id' => $application->id,
                'note' => $note,
                'created_by' => auth()->id(),
                'type' => 'review',
            ]);
        }
    }

    /**
     * Log status change
     */
    private function logStatusChange(
        AdmissionApplication $application,
        string $fromStatus,
        string $toStatus,
        string $notes
    ): void {
        if (class_exists(ApplicationStatusHistory::class)) {
            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'changed_by' => auth()->id(),
                'notes' => $notes,
            ]);
        }
    }

    /**
     * Send notifications
     */
    private function notifyReviewerOfAssignment(ApplicationReview $review): void
    {
        // Implementation would send actual email/notification
        Log::info('Reviewer notification sent', [
            'reviewer_id' => $review->reviewer_id,
            'application_id' => $review->application_id,
        ]);
    }

    private function notifyReviewerOfUrgentAssignment(ApplicationReview $review, string $reason): void
    {
        // Implementation would send urgent notification
        Log::info('Urgent reviewer notification sent', [
            'reviewer_id' => $review->reviewer_id,
            'application_id' => $review->application_id,
            'reason' => $reason,
        ]);
    }

    private function sendEscalationNotifications(AdmissionApplication $application, string $reason): void
    {
        // Implementation would send escalation notifications
        Log::info('Escalation notifications sent', [
            'application_id' => $application->id,
            'reason' => $reason,
        ]);
    }

    private function triggerDecisionProcess(int $applicationId): void
    {
        // This would trigger the decision-making process
        Log::info('Decision process triggered', [
            'application_id' => $applicationId,
        ]);
    }
}