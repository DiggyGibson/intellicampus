<?php

// app/Policies/GradePolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Grade;
use App\Services\ScopeManagementService;
use Illuminate\Auth\Access\HandlesAuthorization;

class GradePolicy
{
    use HandlesAuthorization;

    protected $scopeService;

    public function __construct(ScopeManagementService $scopeService)
    {
        $this->scopeService = $scopeService;
    }

    /**
     * Determine if the user can view the grade
     */
    public function view(User $user, Grade $grade): bool
    {
        // Student can view their own grades
        if ($grade->enrollment->student->user_id === $user->id) {
            return true;
        }

        return $this->scopeService->canPerformAction($user, 'view', 'grade', $grade);
    }

    /**
     * Determine if the user can create grades
     */
    public function create(User $user): bool
    {
        // Faculty can create grades for their sections
        return $user->hasRole(['faculty', 'admin']);
    }

    /**
     * Determine if the user can update the grade
     */
    public function update(User $user, Grade $grade): bool
    {
        return $this->scopeService->canPerformAction($user, 'update', 'grade', $grade);
    }

    /**
     * Determine if the user can delete the grade
     */
    public function delete(User $user, Grade $grade): bool
    {
        // Only the instructor who created it or admin can delete
        if ($user->hasRole('admin')) {
            return true;
        }

        // Check if user is the instructor for the section
        $section = $grade->enrollment->section;
        if ($section->instructor_id === $user->id) {
            // Check if grade is not yet finalized
            return !$grade->is_final;
        }

        return false;
    }

    /**
     * Determine if the user can approve the grade
     */
    public function approve(User $user, Grade $grade): bool
    {
        return $this->scopeService->canPerformAction($user, 'approve', 'grade', $grade);
    }

    /**
     * Determine if the user can finalize grades
     */
    public function finalize(User $user, Grade $grade): bool
    {
        // Registrar can finalize any grade
        if ($user->hasRole('registrar')) {
            return true;
        }

        // Department head can finalize grades in their department
        $course = $grade->enrollment->section->course;
        if ($user->hasRole('department-head') && 
            $course->department_id === $user->department_id) {
            return true;
        }

        return false;
    }
}
