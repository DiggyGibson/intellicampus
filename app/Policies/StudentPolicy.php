<?php

// app/Policies/StudentPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Student;
use App\Services\ScopeManagementService;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPolicy
{
    use HandlesAuthorization;

    protected $scopeService;

    public function __construct(ScopeManagementService $scopeService)
    {
        $this->scopeService = $scopeService;
    }

    /**
     * Determine if the user can view any students
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole([
            'admin', 'registrar', 'faculty', 'department-head', 
            'dean', 'advisor', 'academic-admin'
        ]);
    }

    /**
     * Determine if the user can view the student
     */
    public function view(User $user, Student $student): bool
    {
        // Student can view their own record
        if ($user->id === $student->user_id) {
            return true;
        }

        return $this->scopeService->canPerformAction($user, 'view', 'student', $student);
    }

    /**
     * Determine if the user can create students
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'registrar', 'admissions']);
    }

    /**
     * Determine if the user can update the student
     */
    public function update(User $user, Student $student): bool
    {
        // Student can update some of their own information
        if ($user->id === $student->user_id) {
            // Note: You might want to limit what students can update
            return true;
        }

        return $this->scopeService->canPerformAction($user, 'edit', 'student', $student);
    }

    /**
     * Determine if the user can delete the student
     */
    public function delete(User $user, Student $student): bool
    {
        // Only admin and registrar can delete student records
        return $user->hasRole(['admin', 'registrar']);
    }

    /**
     * Determine if the user can view student grades
     */
    public function viewGrades(User $user, Student $student): bool
    {
        // Student can view their own grades
        if ($user->id === $student->user_id) {
            return true;
        }

        return $this->scopeService->canPerformAction($user, 'view_grades', 'student', $student);
    }

    /**
     * Determine if the user can update student grades
     */
    public function updateGrades(User $user, Student $student): bool
    {
        return $this->scopeService->canPerformAction($user, 'update_grades', 'student', $student);
    }

    /**
     * Determine if the user can view student financial information
     */
    public function viewFinancial(User $user, Student $student): bool
    {
        // Student can view their own financial info
        if ($user->id === $student->user_id) {
            return true;
        }

        // Financial admin and registrar can view
        if ($user->hasRole(['admin', 'financial-admin', 'registrar'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can manage student enrollment
     */
    public function manageEnrollment(User $user, Student $student): bool
    {
        // Registrar can manage all enrollments
        if ($user->hasRole(['admin', 'registrar'])) {
            return true;
        }

        // Advisor can manage their students' enrollment
        if ($user->hasRole('advisor') && $student->advisor_id === $user->id) {
            return true;
        }

        return false;
    }
}