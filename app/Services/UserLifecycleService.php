<?php
# app/Services/UserLifecycleService.php

namespace App\Services;

use App\Models\User;
use App\Models\Student;
use App\Models\AdmissionApplication;
use App\Services\EnrollmentConfirmationService;
use App\Services\ApplicationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserLifecycleService
{
    protected EnrollmentConfirmationService $enrollmentService;
    protected ApplicationService $applicationService;

    /**
     * Valid user type transitions
     */
    private const VALID_TRANSITIONS = [
        'applicant' => ['student', 'guest'],
        'student' => ['alumni', 'inactive'],
        'faculty' => ['inactive', 'emeritus'],
        'staff' => ['inactive'],
        'alumni' => ['guest'],
        'guest' => ['applicant'],
    ];

    public function __construct(
        EnrollmentConfirmationService $enrollmentService,
        ApplicationService $applicationService
    ) {
        $this->enrollmentService = $enrollmentService;
        $this->applicationService = $applicationService;
    }

    /**
     * Create applicant account from registration or application
     */
    public function createApplicant(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Check for existing user
            $existingUser = User::where('email', $data['email'])->first();
            if ($existingUser) {
                if ($existingUser->user_type !== 'applicant' && 
                    !$this->canTransition($existingUser, 'applicant')) {
                    throw new \Exception('An account with this email already exists with type: ' . $existingUser->user_type);
                }
                return $existingUser;
            }

            // Normalize gender to match our standard values
            $gender = null;
            if (isset($data['gender'])) {
                $genderLower = strtolower($data['gender']);
                $genderMap = [
                    'male' => 'male',
                    'm' => 'male',
                    'female' => 'female',
                    'f' => 'female',
                    'other' => 'other',
                    'o' => 'other',
                    'prefer_not_to_say' => 'prefer_not_to_say',
                    'prefer not to say' => 'prefer_not_to_say',
                ];
                $gender = $genderMap[$genderLower] ?? 'other';
            }

            // Create new user
            $user = User::create([
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?? Str::random(16)),
                'username' => $this->generateUsername($data['email']),
                'user_type' => 'applicant',
                'status' => 'active',
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $gender,
                'nationality' => $data['nationality'] ?? null,
                'email_verified_at' => $data['auto_verify'] ?? false ? now() : null,
            ]);

            // Assign applicant role
            $user->assignRole('applicant');

            // Add to user type history
            $this->recordTypeHistory($user, null, 'applicant', 'Account created as applicant');

            Log::info('Applicant account created', [
                'user_id' => $user->id,
                'email' => $user->email,
                'created_by' => auth()->id() ?? 'self-registration',
            ]);

            return $user;
        });
    }

    /**
     * Link application to user account
     */
    public function linkApplicationToUser(AdmissionApplication $application, User $user): void
    {
        // Ensure user is an applicant
        if ($user->user_type !== 'applicant') {
            throw new \Exception('Only applicant users can be linked to applications');
        }

        // Check if application is already linked
        if ($application->user_id && $application->user_id !== $user->id) {
            throw new \Exception('Application is already linked to a different user');
        }

        $application->update(['user_id' => $user->id]);

        Log::info('Application linked to user', [
            'application_id' => $application->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Transition applicant to student upon enrollment confirmation
     */
    public function transitionToStudent(User $user, AdmissionApplication $application): Student
    {
        if (!$this->canTransition($user, 'student')) {
            throw new \Exception("User {$user->id} cannot transition from {$user->user_type} to student");
        }

        if ($application->decision !== 'admit' && $application->decision !== 'conditional_admit') {
            throw new \Exception("Application must be admitted to transition to student");
        }

        return DB::transaction(function () use ($user, $application) {
            // Use your existing EnrollmentConfirmationService
            $enrollment = $this->enrollmentService->confirmEnrollment($application->id, [
                'user_id' => $user->id,
                'confirmation_date' => now(),
            ]);

            // The EnrollmentConfirmationService already creates the student record
            // We just need to update the user type
            $this->recordTypeHistory($user, 'applicant', 'student', 'Enrollment confirmed');

            $user->update([
                'user_type' => 'student',
                'status' => 'active',
            ]);

            // Sync roles
            $user->syncRoles(['student']);

            // Get the student record created by EnrollmentConfirmationService
            $student = Student::where('user_id', $user->id)->firstOrFail();

            Log::info('Applicant transitioned to student', [
                'user_id' => $user->id,
                'student_id' => $student->student_id,
                'application_id' => $application->id,
            ]);

            return $student;
        });
    }

    /**
     * Transition student to alumni upon graduation
     */
    public function transitionToAlumni(User $user, Student $student): User
    {
        if (!$this->canTransition($user, 'alumni')) {
            throw new \Exception("User {$user->id} cannot transition from {$user->user_type} to alumni");
        }

        return DB::transaction(function () use ($user, $student) {
            $this->recordTypeHistory($user, 'student', 'alumni', 'Graduated');

            $user->update([
                'user_type' => 'alumni',
                'status' => 'active',
            ]);

            // Update student record
            $student->update([
                'is_alumni' => true,
                'enrollment_status' => 'graduated',
                'graduation_date' => now(),
            ]);

            // Sync roles
            $user->syncRoles(['alumni']);

            Log::info('Student transitioned to alumni', [
                'user_id' => $user->id,
                'student_id' => $student->student_id,
            ]);

            return $user;
        });
    }

    /**
     * Check if user can transition to new type
     */
    public function canTransition(User $user, string $toType): bool
    {
        $validTransitions = self::VALID_TRANSITIONS[$user->user_type] ?? [];
        return in_array($toType, $validTransitions);
    }

    /**
     * Get valid transitions for user
     */
    public function getValidTransitions(User $user): array
    {
        return self::VALID_TRANSITIONS[$user->user_type] ?? [];
    }

    /**
     * Record user type history
     */
    private function recordTypeHistory(User $user, ?string $from, string $to, string $reason): void
    {
        // metadata is already cast to array in User model, no need to decode
        $history = $user->metadata ?? [];
        
        if (!isset($history['type_history'])) {
            $history['type_history'] = [];
        }

        $history['type_history'][] = [
            'from' => $from,
            'to' => $to,
            'changed_at' => now()->toIso8601String(),
            'changed_by' => auth()->id() ?? 'system',
            'reason' => $reason,
            'ip_address' => request()->ip(),
        ];

        $user->metadata = $history;
        $user->save();
    }

    /**
     * Generate unique username
     */
    private function generateUsername(string $email): string
    {
        $base = explode('@', $email)[0];
        $username = Str::slug($base, '.');
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . '.' . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Get users by type with optional filters
     */
    public function getUsersByType(string $type, array $filters = [])
    {
        $query = User::where('user_type', $type);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['created_after'])) {
            $query->where('created_at', '>=', $filters['created_after']);
        }

        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        return $query;
    }

    /**
     * Get transition statistics
     */
    public function getTransitionStatistics(string $period = 'month'): array
    {
        $startDate = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };

        // This would need to be implemented based on how you store history
        // For now, return basic counts
        return [
            'applicants_created' => User::where('user_type', 'applicant')
                ->where('created_at', '>=', $startDate)
                ->count(),
            'students_enrolled' => User::where('user_type', 'student')
                ->where('created_at', '>=', $startDate)
                ->count(),
            'alumni_graduated' => User::where('user_type', 'alumni')
                ->where('updated_at', '>=', $startDate)
                ->count(),
        ];
    }
}