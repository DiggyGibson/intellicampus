<?php

namespace App\Services;

use App\Models\AdmissionApplication;
use App\Models\AdmissionWaitlist;
use App\Models\EnrollmentConfirmation;
use App\Models\ApplicationCommunication;
use App\Models\ApplicationStatusHistory;
use App\Models\ApplicationNote;
use App\Models\AcademicProgram;
use App\Models\AcademicTerm;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Exception;

class WaitlistManagementService
{
    /**
     * Waitlist configuration
     */
    private const DEFAULT_OFFER_VALIDITY_DAYS = 7;
    private const MAX_WAITLIST_PERCENTAGE = 20; // Max 20% of program capacity
    private const WAITLIST_REVIEW_FREQUENCY_DAYS = 3;
    private const AUTO_RELEASE_INACTIVE_DAYS = 30;
    
    /**
     * Waitlist movement strategies
     */
    private const MOVEMENT_STRATEGIES = [
        'rank_based' => 'Move based on waitlist rank',
        'score_based' => 'Move based on application score',
        'priority_based' => 'Move based on priority factors',
        'manual' => 'Manual selection only',
    ];

    /**
     * Add application to waitlist
     *
     * @param int $applicationId
     * @param int|null $rank
     * @param array $options
     * @return AdmissionWaitlist
     * @throws Exception
     */
    public function addToWaitlist(int $applicationId, ?int $rank = null, array $options = []): AdmissionWaitlist
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::with(['program', 'term'])->findOrFail($applicationId);

            // Validate application can be waitlisted
            if (!$this->canBeWaitlisted($application)) {
                throw new Exception("Application cannot be added to waitlist in current status: {$application->status}");
            }

            // Check if already on waitlist
            $existingWaitlist = AdmissionWaitlist::where('application_id', $applicationId)
                ->where('status', 'active')
                ->first();

            if ($existingWaitlist) {
                throw new Exception("Application is already on the waitlist");
            }

            // Check waitlist capacity
            if (!$this->hasWaitlistCapacity($application->program_id, $application->term_id)) {
                throw new Exception("Waitlist is at maximum capacity for this program");
            }

            // Calculate rank if not provided
            if ($rank === null) {
                $rank = $this->calculateOptimalRank($application);
            }

            // Create waitlist entry
            $waitlist = new AdmissionWaitlist();
            $waitlist->application_id = $applicationId;
            $waitlist->term_id = $application->term_id;
            $waitlist->program_id = $application->program_id;
            $waitlist->rank = $rank;
            $waitlist->original_rank = $rank;
            $waitlist->status = 'active';
            $waitlist->notes = $options['notes'] ?? null;
            $waitlist->priority_factors = $this->calculatePriorityFactors($application);
            $waitlist->save();

            // Adjust other ranks if inserting in middle
            $this->adjustWaitlistRanks($application->program_id, $application->term_id, $waitlist->id, $rank);

            // Update application status
            $previousStatus = $application->status;
            $application->status = 'waitlisted';
            $application->decision = 'waitlist';
            $application->decision_date = now();
            $application->save();

            // Log status change
            $this->logStatusChange($application, $previousStatus, 'waitlisted', 
                "Added to waitlist at position {$rank}");

            // Send waitlist notification
            $this->sendWaitlistNotification($application, $waitlist);

            // Cache waitlist statistics
            $this->updateWaitlistCache($application->program_id, $application->term_id);

            DB::commit();

            Log::info('Application added to waitlist', [
                'application_id' => $applicationId,
                'waitlist_id' => $waitlist->id,
                'rank' => $rank,
            ]);

            return $waitlist;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to add to waitlist', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update waitlist rank
     *
     * @param int $waitlistId
     * @param int $newRank
     * @param string $reason
     * @return AdmissionWaitlist
     * @throws Exception
     */
    public function updateWaitlistRank(int $waitlistId, int $newRank, string $reason): AdmissionWaitlist
    {
        DB::beginTransaction();

        try {
            $waitlist = AdmissionWaitlist::with('application')->findOrFail($waitlistId);

            if ($waitlist->status !== 'active') {
                throw new Exception("Cannot update rank for inactive waitlist entry");
            }

            $oldRank = $waitlist->rank;
            
            if ($oldRank === $newRank) {
                return $waitlist; // No change needed
            }

            // Get all waitlist entries for this program/term
            $allWaitlist = AdmissionWaitlist::where('program_id', $waitlist->program_id)
                ->where('term_id', $waitlist->term_id)
                ->where('status', 'active')
                ->where('id', '!=', $waitlistId)
                ->orderBy('rank')
                ->get();

            // Reorder ranks
            if ($newRank < $oldRank) {
                // Moving up - shift others down
                $allWaitlist->where('rank', '>=', $newRank)
                    ->where('rank', '<', $oldRank)
                    ->each(function ($item) {
                        $item->increment('rank');
                    });
            } else {
                // Moving down - shift others up
                $allWaitlist->where('rank', '>', $oldRank)
                    ->where('rank', '<=', $newRank)
                    ->each(function ($item) {
                        $item->decrement('rank');
                    });
            }

            // Update the target rank
            $waitlist->rank = $newRank;
            $waitlist->rank_change_history = array_merge($waitlist->rank_change_history ?? [], [[
                'from' => $oldRank,
                'to' => $newRank,
                'reason' => $reason,
                'changed_by' => auth()->id(),
                'changed_at' => now()->toIso8601String(),
            ]]);
            $waitlist->save();

            // Add note to application
            $this->addNote($waitlist->application, 
                "Waitlist rank changed from {$oldRank} to {$newRank}. Reason: {$reason}");

            // Notify if moved to top positions
            if ($newRank <= 5 && $oldRank > 5) {
                $this->sendHighPriorityWaitlistNotification($waitlist);
            }

            // Update cache
            $this->updateWaitlistCache($waitlist->program_id, $waitlist->term_id);

            DB::commit();

            Log::info('Waitlist rank updated', [
                'waitlist_id' => $waitlistId,
                'old_rank' => $oldRank,
                'new_rank' => $newRank,
                'reason' => $reason,
            ]);

            return $waitlist->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update waitlist rank', [
                'waitlist_id' => $waitlistId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Release a waitlist spot (offer admission)
     *
     * @param int $waitlistId
     * @param array $offerDetails
     * @return array
     * @throws Exception
     */
    public function releaseWaitlistSpot(int $waitlistId, array $offerDetails = []): array
    {
        DB::beginTransaction();

        try {
            $waitlist = AdmissionWaitlist::with(['application.program', 'application.term'])
                ->findOrFail($waitlistId);

            if ($waitlist->status !== 'active') {
                throw new Exception("Waitlist entry is not active");
            }

            // Check if there's available capacity
            if (!$this->hasAvailableCapacity($waitlist->program_id, $waitlist->term_id)) {
                throw new Exception("No available capacity in the program");
            }

            // Update waitlist status
            $waitlist->status = 'offered';
            $waitlist->offer_date = now();
            $waitlist->offer_expiry_date = now()->addDays(
                $offerDetails['validity_days'] ?? self::DEFAULT_OFFER_VALIDITY_DAYS
            );
            $waitlist->save();

            // Update application status
            $application = $waitlist->application;
            $previousStatus = $application->status;
            $application->status = 'admitted';
            $application->decision = 'admit';
            $application->decision_date = now();
            $application->decision_reason = 'Admitted from waitlist';
            $application->save();

            // Create enrollment confirmation record
            $this->createEnrollmentConfirmation($application, $offerDetails);

            // Move up remaining waitlist
            $this->promoteWaitlistRanks($waitlist->program_id, $waitlist->term_id, $waitlist->rank);

            // Send offer notification
            $this->sendWaitlistOfferNotification($application, $waitlist);

            // Log the change
            $this->logStatusChange($application, $previousStatus, 'admitted', 
                "Admitted from waitlist position {$waitlist->rank}");

            // Update statistics
            $this->updateWaitlistStatistics($waitlist->program_id, $waitlist->term_id);

            DB::commit();

            Log::info('Waitlist spot released', [
                'waitlist_id' => $waitlistId,
                'application_id' => $application->id,
                'original_rank' => $waitlist->rank,
            ]);

            return [
                'status' => 'success',
                'application_id' => $application->id,
                'offer_expires' => $waitlist->offer_expiry_date->format('Y-m-d'),
                'enrollment_deadline' => $waitlist->offer_expiry_date->format('Y-m-d'),
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to release waitlist spot', [
                'waitlist_id' => $waitlistId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send waitlist offer to next eligible candidate
     *
     * @param int $programId
     * @param int $termId
     * @param array $criteria
     * @return AdmissionWaitlist|null
     * @throws Exception
     */
    public function sendWaitlistOffer(int $programId, int $termId, array $criteria = []): ?AdmissionWaitlist
    {
        DB::beginTransaction();

        try {
            // Find next eligible candidate
            $nextCandidate = $this->findNextEligibleCandidate($programId, $termId, $criteria);

            if (!$nextCandidate) {
                Log::info('No eligible waitlist candidates found', [
                    'program_id' => $programId,
                    'term_id' => $termId,
                ]);
                return null;
            }

            // Release the spot
            $result = $this->releaseWaitlistSpot($nextCandidate->id);

            DB::commit();

            return $nextCandidate->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to send waitlist offer', [
                'program_id' => $programId,
                'term_id' => $termId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Track waitlist response (accept/decline)
     *
     * @param int $waitlistId
     * @param string $response
     * @param string|null $reason
     * @return AdmissionWaitlist
     * @throws Exception
     */
    public function trackWaitlistResponse(int $waitlistId, string $response, ?string $reason = null): AdmissionWaitlist
    {
        DB::beginTransaction();

        try {
            $waitlist = AdmissionWaitlist::with('application')->findOrFail($waitlistId);

            if ($waitlist->status !== 'offered') {
                throw new Exception("No active offer for this waitlist entry");
            }

            // Check if offer has expired
            if ($waitlist->offer_expiry_date < now()) {
                $waitlist->status = 'expired';
                $waitlist->save();
                throw new Exception("Waitlist offer has expired");
            }

            // Update based on response
            $waitlist->response_date = now();
            
            if ($response === 'accept') {
                $waitlist->status = 'accepted';
                
                // Confirm enrollment
                $application = $waitlist->application;
                $application->enrollment_confirmed = true;
                $application->enrollment_confirmation_date = now();
                $application->save();
                
                // Update enrollment confirmation record
                $this->confirmEnrollmentFromWaitlist($application);
                
                // Send confirmation
                $this->sendAcceptanceConfirmation($waitlist);
                
            } elseif ($response === 'decline') {
                $waitlist->status = 'declined';
                $waitlist->decline_reason = $reason;
                
                // Update application
                $application = $waitlist->application;
                $application->status = 'declined';
                $application->enrollment_declined = true;
                $application->enrollment_declined_date = now();
                $application->enrollment_declined_reason = $reason;
                $application->save();
                
                // Make spot available for next candidate
                $this->releaseDeclinedSpot($waitlist);
                
                // Send acknowledgment
                $this->sendDeclineAcknowledgment($waitlist);
            } else {
                throw new Exception("Invalid response type: {$response}");
            }

            $waitlist->save();

            // Update statistics
            $this->updateWaitlistStatistics($waitlist->program_id, $waitlist->term_id);

            DB::commit();

            Log::info('Waitlist response tracked', [
                'waitlist_id' => $waitlistId,
                'response' => $response,
            ]);

            return $waitlist;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to track waitlist response', [
                'waitlist_id' => $waitlistId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate waitlist movement predictions
     *
     * @param int $programId
     * @param int $termId
     * @return array
     */
    public function calculateWaitlistMovement(int $programId, int $termId): array
    {
        $program = AcademicProgram::findOrFail($programId);
        $term = AcademicTerm::findOrFail($termId);

        // Get current waitlist
        $waitlist = AdmissionWaitlist::where('program_id', $programId)
            ->where('term_id', $termId)
            ->where('status', 'active')
            ->orderBy('rank')
            ->get();

        // Get historical data
        $historicalData = $this->getHistoricalWaitlistData($programId);

        // Calculate metrics
        $currentAdmitted = AdmissionApplication::where('program_id', $programId)
            ->where('term_id', $termId)
            ->where('decision', 'admit')
            ->count();

        $enrollmentConfirmed = AdmissionApplication::where('program_id', $programId)
            ->where('term_id', $termId)
            ->where('decision', 'admit')
            ->where('enrollment_confirmed', true)
            ->count();

        $capacity = $program->capacity ?? 100;
        $availableSpots = max(0, $capacity - $enrollmentConfirmed);
        
        // Historical yield rate (percentage of admitted who enroll)
        $historicalYield = $historicalData['average_yield'] ?? 0.65;
        
        // Predict how many admitted students will not enroll
        $expectedDeclines = (int)(($currentAdmitted - $enrollmentConfirmed) * (1 - $historicalYield));
        
        // Predict waitlist movement
        $expectedMovement = min($availableSpots + $expectedDeclines, $waitlist->count());
        
        // Calculate probability for each rank
        $predictions = [];
        foreach ($waitlist as $index => $entry) {
            $rank = $entry->rank;
            $probability = $this->calculateAdmissionProbability($rank, $expectedMovement, $waitlist->count());
            
            $predictions[] = [
                'rank' => $rank,
                'application_id' => $entry->application_id,
                'probability' => round($probability * 100, 2),
                'expected_decision_date' => $this->predictDecisionDate($rank, $historicalData),
                'factors' => $this->getMovementFactors($entry),
            ];
        }

        return [
            'program' => $program->name,
            'term' => $term->name,
            'statistics' => [
                'total_capacity' => $capacity,
                'current_enrolled' => $enrollmentConfirmed,
                'available_spots' => $availableSpots,
                'waitlist_size' => $waitlist->count(),
                'expected_movement' => $expectedMovement,
                'historical_yield_rate' => round($historicalYield * 100, 2) . '%',
            ],
            'predictions' => $predictions,
            'confidence_level' => $this->calculateConfidenceLevel($historicalData),
            'last_updated' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Predict waitlist clearance
     *
     * @param int $programId
     * @param int $termId
     * @return array
     */
    public function predictWaitlistClearance(int $programId, int $termId = null): array
    {
        if (!$termId) {
            $termId = AcademicTerm::current()->first()->id ?? null;
        }

        $waitlist = AdmissionWaitlist::where('program_id', $programId)
            ->where('term_id', $termId)
            ->where('status', 'active')
            ->count();

        if ($waitlist === 0) {
            return [
                'clearance_predicted' => true,
                'clearance_date' => now()->format('Y-m-d'),
                'confidence' => 100,
                'factors' => ['No active waitlist'],
            ];
        }

        // Analyze historical patterns
        $historicalAnalysis = $this->analyzeHistoricalClearance($programId);
        
        // Current state analysis
        $currentState = $this->analyzeCurrentState($programId, $termId);
        
        // Predict clearance
        $clearanceProbability = $this->calculateClearanceProbability(
            $waitlist,
            $currentState,
            $historicalAnalysis
        );

        $prediction = [
            'clearance_predicted' => $clearanceProbability > 0.7,
            'clearance_probability' => round($clearanceProbability * 100, 2),
            'estimated_clearance_date' => $this->estimateClearanceDate(
                $waitlist,
                $historicalAnalysis['average_daily_movement'] ?? 0
            ),
            'confidence' => $this->calculatePredictionConfidence($historicalAnalysis),
            'factors' => $this->getClearanceFactors($currentState, $historicalAnalysis),
            'recommendations' => $this->generateClearanceRecommendations(
                $clearanceProbability,
                $waitlist,
                $currentState
            ),
        ];

        return $prediction;
    }

    /**
     * Process automatic waitlist updates
     *
     * @return array
     */
    public function processAutomaticUpdates(): array
    {
        $results = [
            'expired_offers' => 0,
            'auto_released' => 0,
            'rank_adjustments' => 0,
            'notifications_sent' => 0,
        ];

        DB::beginTransaction();

        try {
            // Process expired offers
            $expiredOffers = AdmissionWaitlist::where('status', 'offered')
                ->where('offer_expiry_date', '<', now())
                ->get();

            foreach ($expiredOffers as $offer) {
                $offer->status = 'expired';
                $offer->save();
                $results['expired_offers']++;
                
                // Automatically offer to next candidate
                $this->sendWaitlistOffer($offer->program_id, $offer->term_id);
                $results['auto_released']++;
            }

            // Clean up inactive waitlist entries
            $inactiveDate = now()->subDays(self::AUTO_RELEASE_INACTIVE_DAYS);
            $inactiveEntries = AdmissionWaitlist::where('status', 'active')
                ->whereHas('application', function ($query) use ($inactiveDate) {
                    $query->where('last_activity_at', '<', $inactiveDate)
                        ->orWhereNull('last_activity_at');
                })
                ->get();

            foreach ($inactiveEntries as $entry) {
                $entry->status = 'removed';
                $entry->save();
                
                // Adjust ranks
                $this->promoteWaitlistRanks($entry->program_id, $entry->term_id, $entry->rank);
                $results['rank_adjustments']++;
            }

            // Send periodic updates to active waitlist
            $this->sendPeriodicUpdates();
            $results['notifications_sent'] = $this->getNotificationCount();

            DB::commit();

            Log::info('Automatic waitlist updates processed', $results);

            return $results;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to process automatic updates', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate waitlist report
     *
     * @param int $programId
     * @param int $termId
     * @return array
     */
    public function generateWaitlistReport(int $programId, int $termId): array
    {
        $program = AcademicProgram::find($programId);
        $term = AcademicTerm::find($termId);

        // Get all waitlist entries
        $waitlist = AdmissionWaitlist::with(['application'])
            ->where('program_id', $programId)
            ->where('term_id', $termId)
            ->get();

        // Group by status
        $byStatus = $waitlist->groupBy('status')->map->count();

        // Movement analysis
        $movementData = $this->analyzeMovement($waitlist);

        // Demographics
        $demographics = $this->analyzeWaitlistDemographics($waitlist);

        // Time analysis
        $timeAnalysis = $this->analyzeWaitlistTiming($waitlist);

        return [
            'program' => $program?->name,
            'term' => $term?->name,
            'summary' => [
                'total_waitlisted' => $waitlist->count(),
                'currently_active' => $byStatus['active'] ?? 0,
                'offers_made' => $byStatus['offered'] ?? 0,
                'offers_accepted' => $byStatus['accepted'] ?? 0,
                'offers_declined' => $byStatus['declined'] ?? 0,
                'offers_expired' => $byStatus['expired'] ?? 0,
            ],
            'movement' => $movementData,
            'demographics' => $demographics,
            'timing' => $timeAnalysis,
            'conversion_rate' => $this->calculateConversionRate($waitlist),
            'average_wait_time' => $this->calculateAverageWaitTime($waitlist),
            'recommendations' => $this->generateRecommendations($waitlist, $program),
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Private helper methods
     */

    /**
     * Check if application can be waitlisted
     */
    private function canBeWaitlisted(AdmissionApplication $application): bool
    {
        $allowedStatuses = ['submitted', 'under_review', 'committee_review', 'decision_pending'];
        return in_array($application->status, $allowedStatuses);
    }

    /**
     * Check if waitlist has capacity
     */
    private function hasWaitlistCapacity(int $programId, int $termId): bool
    {
        $program = AcademicProgram::find($programId);
        if (!$program || !$program->capacity) {
            return true; // No limit if capacity not set
        }

        $maxWaitlist = ceil($program->capacity * (self::MAX_WAITLIST_PERCENTAGE / 100));
        
        $currentWaitlist = AdmissionWaitlist::where('program_id', $programId)
            ->where('term_id', $termId)
            ->where('status', 'active')
            ->count();

        return $currentWaitlist < $maxWaitlist;
    }

    /**
     * Calculate optimal rank for new waitlist entry
     */
    private function calculateOptimalRank(AdmissionApplication $application): int
    {
        // Get current waitlist
        $currentWaitlist = AdmissionWaitlist::where('program_id', $application->program_id)
            ->where('term_id', $application->term_id)
            ->where('status', 'active')
            ->orderBy('rank')
            ->get();

        if ($currentWaitlist->isEmpty()) {
            return 1;
        }

        // Calculate application score
        $applicationScore = $this->calculateApplicationScore($application);

        // Find appropriate position based on score
        $position = $currentWaitlist->count() + 1;
        
        foreach ($currentWaitlist as $index => $entry) {
            $entryScore = $this->calculateApplicationScore($entry->application);
            if ($applicationScore > $entryScore) {
                $position = $entry->rank;
                break;
            }
        }

        return $position;
    }

    /**
     * Calculate application score for ranking
     */
    private function calculateApplicationScore(AdmissionApplication $application): float
    {
        $score = 0;

        // GPA component (40%)
        if ($application->previous_gpa) {
            $score += ($application->previous_gpa / 4.0) * 40;
        }

        // Test scores (30%)
        if ($application->test_scores) {
            $testScore = $this->normalizeTestScores($application->test_scores);
            $score += $testScore * 30;
        }

        // Review ratings (20%)
        $avgRating = $application->reviews()
            ->where('status', 'completed')
            ->avg('overall_rating');
        if ($avgRating) {
            $score += ($avgRating / 5) * 20;
        }

        // Priority factors (10%)
        $priorityScore = $this->calculatePriorityScore($application);
        $score += $priorityScore * 10;

        return $score;
    }

    /**
     * Calculate priority factors
     */
    private function calculatePriorityFactors(AdmissionApplication $application): array
    {
        $factors = [];

        // Check if first-generation student
        if ($application->is_first_generation ?? false) {
            $factors['first_generation'] = true;
        }

        // Check if in-state/local
        if ($application->state === 'local_state') {
            $factors['in_state'] = true;
        }

        // Check if underrepresented minority
        if ($this->isUnderrepresentedMinority($application)) {
            $factors['diversity'] = true;
        }

        // Check if legacy
        if ($application->has_legacy ?? false) {
            $factors['legacy'] = true;
        }

        // Financial need
        if ($application->financial_need_level ?? null) {
            $factors['financial_need'] = $application->financial_need_level;
        }

        return $factors;
    }

    /**
     * Adjust waitlist ranks after insertion
     */
    private function adjustWaitlistRanks(int $programId, int $termId, int $excludeId, int $insertedRank): void
    {
        AdmissionWaitlist::where('program_id', $programId)
            ->where('term_id', $termId)
            ->where('id', '!=', $excludeId)
            ->where('rank', '>=', $insertedRank)
            ->where('status', 'active')
            ->increment('rank');
    }

    /**
     * Promote waitlist ranks after removal
     */
    private function promoteWaitlistRanks(int $programId, int $termId, int $removedRank): void
    {
        AdmissionWaitlist::where('program_id', $programId)
            ->where('term_id', $termId)
            ->where('rank', '>', $removedRank)
            ->where('status', 'active')
            ->decrement('rank');
    }

    /**
     * Check available capacity
     */
    private function hasAvailableCapacity(int $programId, int $termId): bool
    {
        $program = AcademicProgram::find($programId);
        if (!$program || !$program->capacity) {
            return true;
        }

        $enrolled = AdmissionApplication::where('program_id', $programId)
            ->where('term_id', $termId)
            ->where('enrollment_confirmed', true)
            ->count();

        return $enrolled < $program->capacity;
    }

    /**
     * Find next eligible candidate
     */
    private function findNextEligibleCandidate(int $programId, int $termId, array $criteria = []): ?AdmissionWaitlist
    {
        $query = AdmissionWaitlist::with('application')
            ->where('program_id', $programId)
            ->where('term_id', $termId)
            ->where('status', 'active')
            ->orderBy('rank');

        // Apply additional criteria if provided
        if (!empty($criteria['min_gpa'])) {
            $query->whereHas('application', function ($q) use ($criteria) {
                $q->where('previous_gpa', '>=', $criteria['min_gpa']);
            });
        }

        if (!empty($criteria['priority_factors'])) {
            $query->whereJsonContains('priority_factors', $criteria['priority_factors']);
        }

        return $query->first();
    }

    /**
     * Calculate admission probability for a rank
     */
    private function calculateAdmissionProbability(int $rank, int $expectedMovement, int $totalWaitlist): float
    {
        if ($rank <= $expectedMovement) {
            // High probability for ranks within expected movement
            return max(0.7, 1 - ($rank / $expectedMovement) * 0.3);
        } else {
            // Lower probability for ranks beyond expected movement
            $beyondExpected = $rank - $expectedMovement;
            return max(0, 0.5 * exp(-$beyondExpected / 10));
        }
    }

    /**
     * Additional helper methods would continue here...
     * Including notification methods, statistics calculations, etc.
     */
}