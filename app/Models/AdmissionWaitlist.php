<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionWaitlist extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'admission_waitlists';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'application_id',
        'term_id',
        'program_id',
        'rank',
        'original_rank',
        'status',
        'offer_date',
        'offer_expiry_date',
        'response_date',
        'notes',
        'removal_reason'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'rank' => 'integer',
        'original_rank' => 'integer',
        'offer_date' => 'date',
        'offer_expiry_date' => 'date',
        'response_date' => 'date'
    ];

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Set original rank when creating
        static::creating(function ($waitlist) {
            if (!$waitlist->original_rank && $waitlist->rank) {
                $waitlist->original_rank = $waitlist->rank;
            }
        });

        // Update application status when waitlist status changes
        static::updated(function ($waitlist) {
            if ($waitlist->isDirty('status')) {
                $waitlist->updateApplicationStatus();
            }
        });
    }

    /**
     * Relationships
     */

    /**
     * Get the application.
     */
    public function application()
    {
        return $this->belongsTo(AdmissionApplication::class, 'application_id');
    }

    /**
     * Get the academic term.
     */
    public function term()
    {
        return $this->belongsTo(AcademicTerm::class, 'term_id');
    }

    /**
     * Get the academic program.
     */
    public function program()
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    /**
     * Scopes
     */

    /**
     * Scope for active waitlist entries.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for offered waitlist entries.
     */
    public function scopeOffered($query)
    {
        return $query->where('status', 'offered');
    }

    /**
     * Scope for pending offers.
     */
    public function scopePendingOffers($query)
    {
        return $query->where('status', 'offered')
            ->where('offer_expiry_date', '>=', now());
    }

    /**
     * Scope for expired offers.
     */
    public function scopeExpiredOffers($query)
    {
        return $query->where('status', 'offered')
            ->where('offer_expiry_date', '<', now());
    }

    /**
     * Scope for specific term and program.
     */
    public function scopeForProgram($query, $termId, $programId)
    {
        return $query->where('term_id', $termId)
            ->where('program_id', $programId);
    }

    /**
     * Scope ordered by rank.
     */
    public function scopeRanked($query)
    {
        return $query->orderBy('rank');
    }

    /**
     * Helper Methods
     */

    /**
     * Make an offer to the waitlisted candidate.
     */
    public function makeOffer($expiryDays = 7): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $this->status = 'offered';
        $this->offer_date = now();
        $this->offer_expiry_date = now()->addDays($expiryDays);
        
        $saved = $this->save();
        
        if ($saved) {
            // Send notification to applicant
            $this->sendOfferNotification();
        }
        
        return $saved;
    }

    /**
     * Accept the waitlist offer.
     */
    public function acceptOffer(): bool
    {
        if ($this->status !== 'offered') {
            return false;
        }

        if ($this->offer_expiry_date < now()) {
            return false; // Offer has expired
        }

        $this->status = 'accepted';
        $this->response_date = now();
        
        return $this->save();
    }

    /**
     * Decline the waitlist offer.
     */
    public function declineOffer(): bool
    {
        if ($this->status !== 'offered') {
            return false;
        }

        $this->status = 'declined';
        $this->response_date = now();
        
        $saved = $this->save();
        
        if ($saved) {
            // Move next candidate up
            $this->processNextCandidate();
        }
        
        return $saved;
    }

    /**
     * Remove from waitlist.
     */
    public function remove($reason = null): bool
    {
        if (in_array($this->status, ['accepted', 'removed'])) {
            return false;
        }

        $this->status = 'removed';
        $this->removal_reason = $reason;
        
        $saved = $this->save();
        
        if ($saved && $this->status === 'active') {
            // Rerank remaining candidates
            $this->rerankWaitlist();
        }
        
        return $saved;
    }

    /**
     * Check and expire offer if past deadline.
     */
    public function checkExpiry(): bool
    {
        if ($this->status === 'offered' && 
            $this->offer_expiry_date && 
            $this->offer_expiry_date < now()) {
            
            $this->status = 'expired';
            $saved = $this->save();
            
            if ($saved) {
                // Process next candidate
                $this->processNextCandidate();
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * Update rank.
     */
    public function updateRank($newRank): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $oldRank = $this->rank;
        $this->rank = $newRank;
        
        $saved = $this->save();
        
        if ($saved && $oldRank != $newRank) {
            // Adjust other ranks accordingly
            $this->adjustOtherRanks($oldRank, $newRank);
        }
        
        return $saved;
    }

    /**
     * Process next candidate in waitlist.
     */
    protected function processNextCandidate(): void
    {
        $nextCandidate = self::forProgram($this->term_id, $this->program_id)
            ->active()
            ->ranked()
            ->first();
        
        if ($nextCandidate) {
            $nextCandidate->makeOffer();
        }
    }

    /**
     * Rerank waitlist after removal.
     */
    protected function rerankWaitlist(): void
    {
        $waitlistEntries = self::forProgram($this->term_id, $this->program_id)
            ->active()
            ->where('rank', '>', $this->rank)
            ->orderBy('rank')
            ->get();
        
        foreach ($waitlistEntries as $entry) {
            $entry->rank = $entry->rank - 1;
            $entry->saveQuietly();
        }
    }

    /**
     * Adjust other ranks when rank changes.
     */
    protected function adjustOtherRanks($oldRank, $newRank): void
    {
        if ($oldRank < $newRank) {
            // Moving down - adjust entries between old and new rank
            self::forProgram($this->term_id, $this->program_id)
                ->active()
                ->where('id', '!=', $this->id)
                ->where('rank', '>', $oldRank)
                ->where('rank', '<=', $newRank)
                ->decrement('rank');
        } else {
            // Moving up - adjust entries between new and old rank
            self::forProgram($this->term_id, $this->program_id)
                ->active()
                ->where('id', '!=', $this->id)
                ->where('rank', '>=', $newRank)
                ->where('rank', '<', $oldRank)
                ->increment('rank');
        }
    }

    /**
     * Update application status based on waitlist status.
     */
    protected function updateApplicationStatus(): void
    {
        $statusMap = [
            'active' => 'waitlisted',
            'offered' => 'waitlist_offered',
            'accepted' => 'admitted',
            'declined' => 'waitlist_declined',
            'expired' => 'waitlist_expired',
            'removed' => 'waitlist_removed'
        ];

        if (isset($statusMap[$this->status])) {
            $this->application->update([
                'status' => $statusMap[$this->status]
            ]);
            
            if ($this->status === 'accepted') {
                $this->application->update([
                    'decision' => 'admit',
                    'decision_date' => now()
                ]);
            }
        }
    }

    /**
     * Send offer notification.
     */
    protected function sendOfferNotification(): void
    {
        // Send email/SMS notification to applicant
        // Implementation depends on notification system
    }

    /**
     * Get position change from original.
     */
    public function getPositionChange(): int
    {
        if (!$this->original_rank) {
            return 0;
        }
        
        return $this->original_rank - $this->rank;
    }

    /**
     * Get days until offer expires.
     */
    public function getDaysUntilExpiry(): ?int
    {
        if ($this->status !== 'offered' || !$this->offer_expiry_date) {
            return null;
        }
        
        if ($this->offer_expiry_date < now()) {
            return 0;
        }
        
        return now()->diffInDays($this->offer_expiry_date);
    }

    /**
     * Get status label.
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            'active' => 'On Waitlist',
            'offered' => 'Offer Extended',
            'accepted' => 'Offer Accepted',
            'declined' => 'Offer Declined',
            'expired' => 'Offer Expired',
            'removed' => 'Removed from Waitlist',
            default => 'Unknown'
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'active' => 'yellow',
            'offered' => 'blue',
            'accepted' => 'green',
            'declined' => 'red',
            'expired' => 'gray',
            'removed' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get waitlist statistics for a program.
     */
    public static function getStatistics($termId, $programId): array
    {
        $query = self::forProgram($termId, $programId);
        
        return [
            'total' => $query->count(),
            'active' => $query->active()->count(),
            'offered' => $query->offered()->count(),
            'accepted' => $query->where('status', 'accepted')->count(),
            'declined' => $query->where('status', 'declined')->count(),
            'expired' => $query->where('status', 'expired')->count(),
            'removed' => $query->where('status', 'removed')->count(),
            'conversion_rate' => $query->where('status', 'accepted')->count() / 
                max($query->whereIn('status', ['accepted', 'declined', 'expired'])->count(), 1) * 100
        ];
    }

    /**
     * Process all waitlists for available spots.
     */
    public static function processWaitlist($termId, $programId): int
    {
        $processed = 0;
        
        // Get target enrollment from settings
        $setting = AdmissionSetting::where('term_id', $termId)
            ->where('program_id', $programId)
            ->first();
        
        if (!$setting || !$setting->target_enrollment) {
            return 0;
        }
        
        // Count current confirmed enrollments
        $confirmedCount = AdmissionApplication::where('term_id', $termId)
            ->where('program_id', $programId)
            ->where('decision', 'admit')
            ->where('enrollment_confirmed', true)
            ->count();
        
        // Calculate available spots
        $availableSpots = max(0, $setting->target_enrollment - $confirmedCount);
        
        if ($availableSpots > 0) {
            // Get active waitlist candidates in rank order
            $candidates = self::forProgram($termId, $programId)
                ->active()
                ->ranked()
                ->limit($availableSpots)
                ->get();
            
            foreach ($candidates as $candidate) {
                if ($candidate->makeOffer()) {
                    $processed++;
                }
            }
        }
        
        return $processed;
    }

    /**
     * Check all expired offers.
     */
    public static function checkAllExpiredOffers(): int
    {
        $expired = 0;
        $expiredOffers = self::expiredOffers()->get();
        
        foreach ($expiredOffers as $offer) {
            if ($offer->checkExpiry()) {
                $expired++;
            }
        }
        
        return $expired;
    }
}