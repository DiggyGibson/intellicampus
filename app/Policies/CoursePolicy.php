<?php

// app/Policies/CoursePolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Course;
use App\Services\ScopeManagementService;
use Illuminate\Auth\Access\HandlesAuthorization;

class CoursePolicy
{
    use HandlesAuthorization;

    protected $scopeService;

    public function __construct(ScopeManagementService $scopeService)
    {
        $this->scopeService = $scopeService;
    }

    /**
     * Determine if the user can view any courses
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole([
            'admin', 'faculty', 'department-head', 'dean', 
            'registrar', 'academic-admin'
        ]);
    }

    /**
     * Determine if the user can view the course
     */
    public function view(User $user, Course $course): bool
    {
        return $this->scopeService->canPerformAction($user, 'view', 'course', $course);
    }

    /**
     * Determine if the user can create courses
     */
    public function create(User $user): bool
    {
        // Admin and academic admin can create courses
        if ($user->hasRole(['admin', 'academic-admin'])) {
            return true;
        }

        // Department heads can create courses for their department
        if ($user->hasRole('department-head') && $user->department_id) {
            return true;
        }

        // Deans can create courses in their college
        if ($user->hasRole('dean') && $user->college_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can update the course
     */
    public function update(User $user, Course $course): bool
    {
        return $this->scopeService->canPerformAction($user, 'edit', 'course', $course);
    }

    /**
     * Determine if the user can delete the course
     */
    public function delete(User $user, Course $course): bool
    {
        return $this->scopeService->canPerformAction($user, 'delete', 'course', $course);
    }

    /**
     * Determine if the user can restore the course
     */
    public function restore(User $user, Course $course): bool
    {
        // Same as delete permission
        return $this->delete($user, $course);
    }

    /**
     * Determine if the user can permanently delete the course
     */
    public function forceDelete(User $user, Course $course): bool
    {
        // Only super admin can permanently delete
        return $user->hasRole('super-admin');
    }

    /**
     * Determine if the user can manage course sections
     */
    public function manageSections(User $user, Course $course): bool
    {
        return $this->scopeService->canPerformAction($user, 'edit', 'course', $course);
    }

    /**
     * Determine if the user can assign faculty to the course
     */
    public function assignFaculty(User $user, Course $course): bool
    {
        return $this->scopeService->canPerformAction($user, 'assign_faculty', 'course', $course);
    }

    /**
     * Determine if the user can manage prerequisites
     */
    public function managePrerequisites(User $user, Course $course): bool
    {
        // Admin and registrar can manage prerequisites
        if ($user->hasRole(['admin', 'registrar', 'academic-admin'])) {
            return true;
        }

        // Department head can manage for their department's courses
        if ($user->hasRole('department-head') && 
            $course->department_id === $user->department_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can approve the course
     */
    public function approve(User $user, Course $course): bool
    {
        // Admin can approve any course
        if ($user->hasRole(['admin', 'academic-admin'])) {
            return true;
        }

        // Dean can approve courses in their college
        if ($user->hasRole('dean') && $course->department && 
            $course->department->college_id === $user->college_id) {
            return true;
        }

        // Department head can approve courses in their department
        if ($user->hasRole('department-head') && 
            $course->department_id === $user->department_id) {
            return true;
        }

        return false;
    }
}