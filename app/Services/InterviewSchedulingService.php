<?php

namespace App\Services;

use App\Models\AdmissionApplication;
use App\Models\AdmissionInterview;
use App\Models\User;
use App\Models\ApplicationCommunication;
use App\Models\ApplicationNote;
use App\Models\AcademicProgram;
use App\Models\CalendarEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;

class InterviewSchedulingService
{
    /**
     * Interview configuration
     */
    private const INTERVIEW_DURATION_MINUTES = 30;
    private const BUFFER_MINUTES_BETWEEN_INTERVIEWS = 15;
    private const MAX_INTERVIEWS_PER_DAY = 8;
    private const MIN_DAYS_ADVANCE_BOOKING = 2;
    private const MAX_DAYS_ADVANCE_BOOKING = 30;

    /**
     * Interview types
     */
    private const INTERVIEW_TYPES = [
        'in_person' => 'In-Person Interview',
        'phone' => 'Phone Interview',
        'video' => 'Video Interview',
        'group' => 'Group Interview',
    ];

    /**
     * Interview statuses
     */
    private const INTERVIEW_STATUSES = [
        'scheduled' => 'Scheduled',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'no_show' => 'No Show',
        'rescheduled' => 'Rescheduled',
    ];

    /**
     * Working hours configuration
     */
    private const WORKING_HOURS = [
        'monday' => ['09:00', '17:00'],
        'tuesday' => ['09:00', '17:00'],
        'wednesday' => ['09:00', '17:00'],
        'thursday' => ['09:00', '17:00'],
        'friday' => ['09:00', '16:00'],
        'saturday' => null, // Closed
        'sunday' => null,   // Closed
    ];

    /**
     * Interview time slots
     */
    private const TIME_SLOTS = [
        '09:00', '09:45', '10:30', '11:15',
        '13:00', '13:45', '14:30', '15:15', '16:00'
    ];

    /**
     * Schedule an interview for an application
     *
     * @param int $applicationId
     * @param array $interviewData
     * @return AdmissionInterview
     * @throws Exception
     */
    public function scheduleInterview(int $applicationId, array $interviewData): AdmissionInterview
    {
        DB::beginTransaction();

        try {
            $application = AdmissionApplication::with(['program'])->findOrFail($applicationId);

            // Validate application status
            if (!$this->canScheduleInterview($application)) {
                throw new Exception("Interview cannot be scheduled for application in status: {$application->status}");
            }

            // Check for existing interview
            $existingInterview = AdmissionInterview::where('application_id', $applicationId)
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->first();

            if ($existingInterview) {
                throw new Exception("An interview is already scheduled for this application");
            }

            // Validate interview date and time
            $scheduledAt = Carbon::parse($interviewData['scheduled_at']);
            $this->validateInterviewDateTime($scheduledAt);

            // Check interviewer availability
            if (isset($interviewData['interviewer_id'])) {
                $this->checkInterviewerAvailability($interviewData['interviewer_id'], $scheduledAt);
            }

            // Create interview record
            $interview = new AdmissionInterview();
            $interview->application_id = $applicationId;
            $interview->interviewer_id = $interviewData['interviewer_id'] ?? null;
            $interview->scheduled_at = $scheduledAt;
            $interview->duration_minutes = $interviewData['duration'] ?? self::INTERVIEW_DURATION_MINUTES;
            $interview->interview_type = $interviewData['type'] ?? 'video';
            $interview->status = 'scheduled';
            
            // Set location or meeting details
            if ($interview->interview_type === 'in_person') {
                $interview->location = $interviewData['location'] ?? 'Admissions Office';
            } elseif ($interview->interview_type === 'video') {
                $meetingDetails = $this->generateVideoMeetingDetails();
                $interview->meeting_link = $meetingDetails['link'];
                $interview->meeting_id = $meetingDetails['id'];
            } elseif ($interview->interview_type === 'phone') {
                $interview->location = $interviewData['phone_number'] ?? null;
            }

            $interview->notes = $interviewData['notes'] ?? null;
            $interview->save();

            // Create calendar event
            $this->createCalendarEvent($interview, $application);

            // Send interview invitation
            $this->sendInterviewInvitation($interview, $application);

            // Update application status if needed
            if ($application->status === 'under_review') {
                $application->status = 'interview_scheduled';
                $application->save();
            }

            // Add note to application
            $this->addInterviewNote($application, "Interview scheduled for {$scheduledAt->format('F d, Y g:i A')}");

            DB::commit();

            Log::info('Interview scheduled', [
                'interview_id' => $interview->id,
                'application_id' => $applicationId,
                'scheduled_at' => $scheduledAt,
            ]);

            return $interview;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to schedule interview', [
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Assign an interviewer to an interview
     *
     * @param int $interviewId
     * @param int $interviewerId
     * @return AdmissionInterview
     * @throws Exception
     */
    public function assignInterviewer(int $interviewId, int $interviewerId): AdmissionInterview
    {
        DB::beginTransaction();

        try {
            $interview = AdmissionInterview::with(['application'])->findOrFail($interviewId);
            $interviewer = User::findOrFail($interviewerId);

            // Validate interviewer can conduct interviews
            if (!$this->canConductInterviews($interviewer)) {
                throw new Exception("User is not authorized to conduct interviews");
            }

            // Check availability
            $this->checkInterviewerAvailability($interviewerId, $interview->scheduled_at);

            // Check for conflicts of interest
            if ($this->hasConflictOfInterest($interview->application, $interviewer)) {
                throw new Exception("Conflict of interest detected for this interviewer");
            }

            // Assign interviewer
            $previousInterviewer = $interview->interviewer_id;
            $interview->interviewer_id = $interviewerId;
            $interview->save();

            // Update calendar event
            $this->updateCalendarEvent($interview);

            // Notify new interviewer
            $this->notifyInterviewerAssignment($interview, $interviewer);

            // Notify previous interviewer if changed
            if ($previousInterviewer && $previousInterviewer !== $interviewerId) {
                $this->notifyInterviewerRemoval($interview, $previousInterviewer);
            }

            DB::commit();

            Log::info('Interviewer assigned', [
                'interview_id' => $interviewId,
                'interviewer_id' => $interviewerId,
            ]);

            return $interview;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign interviewer', [
                'interview_id' => $interviewId,
                'interviewer_id' => $interviewerId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reschedule an interview
     *
     * @param int $interviewId
     * @param Carbon $newDateTime
     * @param string $reason
     * @return AdmissionInterview
     * @throws Exception
     */
    public function rescheduleInterview(int $interviewId, Carbon $newDateTime, string $reason = ''): AdmissionInterview
    {
        DB::beginTransaction();

        try {
            $interview = AdmissionInterview::with(['application', 'interviewer'])->findOrFail($interviewId);

            // Check if interview can be rescheduled
            if (!in_array($interview->status, ['scheduled', 'confirmed'])) {
                throw new Exception("Interview cannot be rescheduled in current status: {$interview->status}");
            }

            // Validate new date and time
            $this->validateInterviewDateTime($newDateTime);

            // Check interviewer availability for new time
            if ($interview->interviewer_id) {
                $this->checkInterviewerAvailability($interview->interviewer_id, $newDateTime);
            }

            // Store old datetime
            $oldDateTime = $interview->scheduled_at;

            // Update interview
            $interview->scheduled_at = $newDateTime;
            $interview->status = 'rescheduled';
            $interview->rescheduled_from = $oldDateTime;
            $interview->reschedule_reason = $reason;
            $interview->save();

            // Update calendar event
            $this->updateCalendarEvent($interview);

            // Send reschedule notifications
            $this->sendRescheduleNotifications($interview, $oldDateTime, $reason);

            // Add note
            $this->addInterviewNote(
                $interview->application,
                "Interview rescheduled from {$oldDateTime->format('F d, Y g:i A')} to {$newDateTime->format('F d, Y g:i A')}. Reason: {$reason}"
            );

            // Reset status to scheduled after notifications
            $interview->status = 'scheduled';
            $interview->save();

            DB::commit();

            Log::info('Interview rescheduled', [
                'interview_id' => $interviewId,
                'old_time' => $oldDateTime,
                'new_time' => $newDateTime,
                'reason' => $reason,
            ]);

            return $interview;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to reschedule interview', [
                'interview_id' => $interviewId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel an interview
     *
     * @param int $interviewId
     * @param string $reason
     * @return AdmissionInterview
     * @throws Exception
     */
    public function cancelInterview(int $interviewId, string $reason): AdmissionInterview
    {
        DB::beginTransaction();

        try {
            $interview = AdmissionInterview::with(['application', 'interviewer'])->findOrFail($interviewId);

            // Check if interview can be cancelled
            if (in_array($interview->status, ['completed', 'cancelled'])) {
                throw new Exception("Interview is already {$interview->status}");
            }

            // Update interview status
            $interview->status = 'cancelled';
            $interview->cancelled_at = now();
            $interview->cancellation_reason = $reason;
            $interview->cancelled_by = auth()->id();
            $interview->save();

            // Cancel calendar event
            $this->cancelCalendarEvent($interview);

            // Send cancellation notifications
            $this->sendCancellationNotifications($interview, $reason);

            // Update application status if needed
            if ($interview->application->status === 'interview_scheduled') {
                $interview->application->status = 'under_review';
                $interview->application->save();
            }

            // Add note
            $this->addInterviewNote(
                $interview->application,
                "Interview cancelled. Reason: {$reason}"
            );

            DB::commit();

            Log::info('Interview cancelled', [
                'interview_id' => $interviewId,
                'reason' => $reason,
            ]);

            return $interview;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel interview', [
                'interview_id' => $interviewId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send interview invitation
     *
     * @param int $interviewId
     * @return array
     * @throws Exception
     */
    public function sendInterviewInvite(int $interviewId): array
    {
        try {
            $interview = AdmissionInterview::with(['application', 'interviewer'])->findOrFail($interviewId);

            // Generate invitation content
            $invitationContent = $this->generateInvitationContent($interview);

            // Send to applicant
            $this->sendApplicantInvitation($interview, $invitationContent);

            // Send to interviewer if assigned
            if ($interview->interviewer) {
                $this->sendInterviewerInvitation($interview, $invitationContent);
            }

            // Update interview status to confirmed
            $interview->status = 'confirmed';
            $interview->confirmed_at = now();
            $interview->save();

            Log::info('Interview invitation sent', [
                'interview_id' => $interviewId,
            ]);

            return [
                'success' => true,
                'message' => 'Interview invitation sent successfully',
                'interview_id' => $interviewId,
                'scheduled_at' => $interview->scheduled_at->format('Y-m-d H:i:s'),
            ];

        } catch (Exception $e) {
            Log::error('Failed to send interview invitation', [
                'interview_id' => $interviewId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Record interview feedback
     *
     * @param int $interviewId
     * @param array $feedback
     * @return AdmissionInterview
     * @throws Exception
     */
    public function recordInterviewFeedback(int $interviewId, array $feedback): AdmissionInterview
    {
        DB::beginTransaction();

        try {
            $interview = AdmissionInterview::with(['application'])->findOrFail($interviewId);

            // Validate interview has occurred
            if ($interview->status !== 'confirmed' && $interview->status !== 'completed') {
                throw new Exception("Cannot record feedback for interview with status: {$interview->status}");
            }

            // Validate feedback data
            $this->validateFeedback($feedback);

            // Update interview with feedback
            $interview->interview_score = $feedback['score'] ?? null;
            $interview->feedback = $feedback['comments'] ?? null;
            $interview->strengths = $feedback['strengths'] ?? null;
            $interview->concerns = $feedback['concerns'] ?? null;
            $interview->recommendation = $feedback['recommendation'] ?? null;
            $interview->status = 'completed';
            $interview->completed_at = now();
            $interview->feedback_by = auth()->id();
            $interview->save();

            // Add feedback to application review
            $this->addFeedbackToApplicationReview($interview);

            // Update application status if needed
            $this->updateApplicationStatusAfterInterview($interview);

            // Add note
            $this->addInterviewNote(
                $interview->application,
                "Interview completed. Score: {$interview->interview_score}/10. Recommendation: {$interview->recommendation}"
            );

            DB::commit();

            Log::info('Interview feedback recorded', [
                'interview_id' => $interviewId,
                'score' => $interview->interview_score,
                'recommendation' => $interview->recommendation,
            ]);

            return $interview;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to record interview feedback', [
                'interview_id' => $interviewId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate interview calendar for an interviewer
     *
     * @param int $interviewerId
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function generateInterviewCalendar(int $interviewerId, Carbon $startDate = null, Carbon $endDate = null): array
    {
        try {
            $interviewer = User::findOrFail($interviewerId);

            // Default to next 30 days if dates not provided
            $startDate = $startDate ?? Carbon::now();
            $endDate = $endDate ?? Carbon::now()->addDays(30);

            // Get all interviews for the period
            $interviews = AdmissionInterview::where('interviewer_id', $interviewerId)
                ->whereBetween('scheduled_at', [$startDate, $endDate])
                ->whereNotIn('status', ['cancelled'])
                ->orderBy('scheduled_at')
                ->get();

            // Get available slots
            $availableSlots = $this->getAvailableSlots($interviewerId, $startDate, $endDate);

            // Generate calendar data
            $calendar = [];
            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                $dayOfWeek = strtolower($date->format('l'));
                $workingHours = self::WORKING_HOURS[$dayOfWeek];

                if ($workingHours === null) {
                    // Weekend or holiday
                    $calendar[$date->format('Y-m-d')] = [
                        'date' => $date->format('Y-m-d'),
                        'day' => $date->format('l'),
                        'is_working_day' => false,
                        'interviews' => [],
                        'available_slots' => [],
                    ];
                    continue;
                }

                // Get interviews for this day
                $dayInterviews = $interviews->filter(function ($interview) use ($date) {
                    return $interview->scheduled_at->isSameDay($date);
                });

                // Get available slots for this day
                $daySlots = $availableSlots->filter(function ($slot) use ($date) {
                    return Carbon::parse($slot['datetime'])->isSameDay($date);
                });

                $calendar[$date->format('Y-m-d')] = [
                    'date' => $date->format('Y-m-d'),
                    'day' => $date->format('l'),
                    'is_working_day' => true,
                    'working_hours' => $workingHours,
                    'interviews' => $dayInterviews->map(function ($interview) {
                        return [
                            'id' => $interview->id,
                            'time' => $interview->scheduled_at->format('H:i'),
                            'duration' => $interview->duration_minutes,
                            'type' => $interview->interview_type,
                            'applicant' => $interview->application->first_name . ' ' . $interview->application->last_name,
                            'status' => $interview->status,
                        ];
                    })->toArray(),
                    'available_slots' => $daySlots->values()->toArray(),
                    'total_interviews' => $dayInterviews->count(),
                    'slots_available' => $daySlots->count(),
                ];
            }

            // Calculate statistics
            $stats = [
                'total_interviews' => $interviews->count(),
                'total_available_slots' => $availableSlots->count(),
                'busiest_day' => $this->getBusiestDay($calendar),
                'average_interviews_per_day' => $interviews->count() / $period->count(),
            ];

            return [
                'interviewer' => [
                    'id' => $interviewer->id,
                    'name' => $interviewer->name,
                ],
                'period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ],
                'calendar' => $calendar,
                'statistics' => $stats,
            ];

        } catch (Exception $e) {
            Log::error('Failed to generate interview calendar', [
                'interviewer_id' => $interviewerId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Private helper methods
     */

    /**
     * Check if interview can be scheduled for application
     */
    private function canScheduleInterview(AdmissionApplication $application): bool
    {
        $allowedStatuses = ['under_review', 'committee_review'];
        return in_array($application->status, $allowedStatuses);
    }

    /**
     * Validate interview date and time
     */
    private function validateInterviewDateTime(Carbon $dateTime): void
    {
        // Check minimum advance booking
        $minDate = Carbon::now()->addDays(self::MIN_DAYS_ADVANCE_BOOKING);
        if ($dateTime->lt($minDate)) {
            throw new Exception("Interview must be scheduled at least " . self::MIN_DAYS_ADVANCE_BOOKING . " days in advance");
        }

        // Check maximum advance booking
        $maxDate = Carbon::now()->addDays(self::MAX_DAYS_ADVANCE_BOOKING);
        if ($dateTime->gt($maxDate)) {
            throw new Exception("Interview cannot be scheduled more than " . self::MAX_DAYS_ADVANCE_BOOKING . " days in advance");
        }

        // Check working hours
        $dayOfWeek = strtolower($dateTime->format('l'));
        $workingHours = self::WORKING_HOURS[$dayOfWeek];

        if ($workingHours === null) {
            throw new Exception("Interviews cannot be scheduled on {$dateTime->format('l')}");
        }

        $startTime = Carbon::parse($dateTime->format('Y-m-d') . ' ' . $workingHours[0]);
        $endTime = Carbon::parse($dateTime->format('Y-m-d') . ' ' . $workingHours[1]);

        if ($dateTime->lt($startTime) || $dateTime->gt($endTime->subMinutes(self::INTERVIEW_DURATION_MINUTES))) {
            throw new Exception("Interview time must be between {$workingHours[0]} and {$workingHours[1]}");
        }
    }

    /**
     * Check interviewer availability
     */
    private function checkInterviewerAvailability(int $interviewerId, Carbon $dateTime): void
    {
        // Check for existing interviews at the same time
        $conflictingInterview = AdmissionInterview::where('interviewer_id', $interviewerId)
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($query) use ($dateTime) {
                $endTime = $dateTime->copy()->addMinutes(self::INTERVIEW_DURATION_MINUTES + self::BUFFER_MINUTES_BETWEEN_INTERVIEWS);
                $query->whereBetween('scheduled_at', [$dateTime, $endTime])
                    ->orWhere(function ($q) use ($dateTime) {
                        $q->where('scheduled_at', '<=', $dateTime)
                            ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$dateTime]);
                    });
            })
            ->first();

        if ($conflictingInterview) {
            throw new Exception("Interviewer is not available at the selected time");
        }

        // Check daily interview limit
        $dailyCount = AdmissionInterview::where('interviewer_id', $interviewerId)
            ->whereDate('scheduled_at', $dateTime->format('Y-m-d'))
            ->whereNotIn('status', ['cancelled'])
            ->count();

        if ($dailyCount >= self::MAX_INTERVIEWS_PER_DAY) {
            throw new Exception("Interviewer has reached the maximum number of interviews for this day");
        }
    }

    /**
     * Check if user can conduct interviews
     */
    private function canConductInterviews(User $user): bool
    {
        $allowedRoles = [
            'admissions_officer',
            'faculty',
            'department_head',
            'dean',
            'admissions_committee',
        ];

        return $user->hasAnyRole($allowedRoles);
    }

    /**
     * Check for conflicts of interest
     */
    private function hasConflictOfInterest(AdmissionApplication $application, User $interviewer): bool
    {
        // Check if interviewer has same last name
        if (strtolower($application->last_name) === strtolower($interviewer->last_name)) {
            return true;
        }

        // Add more conflict checks as needed

        return false;
    }

    /**
     * Generate video meeting details
     */
    private function generateVideoMeetingDetails(): array
    {
        // This would integrate with video conferencing services (Zoom, Teams, etc.)
        // For now, generate mock details
        
        $meetingId = 'MTG-' . Str::random(10);
        $meetingLink = config('app.url') . '/interviews/video/' . $meetingId;

        return [
            'id' => $meetingId,
            'link' => $meetingLink,
            'password' => Str::random(6),
        ];
    }

    /**
     * Get available time slots
     */
    private function getAvailableSlots(int $interviewerId, Carbon $startDate, Carbon $endDate): Collection
    {
        $slots = collect();
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            // Skip if before minimum booking date
            if ($date->lt(Carbon::now()->addDays(self::MIN_DAYS_ADVANCE_BOOKING))) {
                continue;
            }

            $dayOfWeek = strtolower($date->format('l'));
            $workingHours = self::WORKING_HOURS[$dayOfWeek];

            if ($workingHours === null) {
                continue;
            }

            // Get existing interviews for this day
            $existingInterviews = AdmissionInterview::where('interviewer_id', $interviewerId)
                ->whereDate('scheduled_at', $date)
                ->whereNotIn('status', ['cancelled'])
                ->pluck('scheduled_at')
                ->map(function ($dt) {
                    return Carbon::parse($dt)->format('H:i');
                })
                ->toArray();

            // Check each time slot
            foreach (self::TIME_SLOTS as $slot) {
                if (!in_array($slot, $existingInterviews)) {
                    $slotTime = Carbon::parse($date->format('Y-m-d') . ' ' . $slot);
                    
                    // Make sure slot is within working hours
                    $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $workingHours[0]);
                    $endTime = Carbon::parse($date->format('Y-m-d') . ' ' . $workingHours[1]);
                    
                    if ($slotTime->gte($startTime) && $slotTime->lte($endTime->subMinutes(self::INTERVIEW_DURATION_MINUTES))) {
                        $slots->push([
                            'datetime' => $slotTime->format('Y-m-d H:i:s'),
                            'date' => $date->format('Y-m-d'),
                            'time' => $slot,
                            'available' => true,
                        ]);
                    }
                }
            }
        }

        return $slots;
    }

    // Additional helper methods for notifications, calendar management, etc.
}