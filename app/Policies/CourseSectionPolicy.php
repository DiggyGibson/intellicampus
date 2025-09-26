<?php

// app/Policies/CourseSectionPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\CourseSection;
use App\Services\ScopeManagementService;
use Illuminate\Auth\Access\HandlesAuthorization;

class CourseSectionPolicy
{
    use HandlesAuthorization;

    protected $scopeService;

    public function __construct(ScopeManagementService $scopeService)
    {
        $this->scopeService = $scopeService;
    }

    /**
     * Determine if the user can view any sections
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole([
            'admin', 'faculty', 'department-head', 'dean', 
            'registrar', 'academic-admin', 'student'
        ]);
    }

    /**
     * Determine if the user can view the section
     */
    public function view(User $user, CourseSection $section): bool
    {
        // Students can view sections they're enrolled in
        if ($user->hasRole('student')) {
            return $user->student->enrollments()
                ->where('section_id', $section->id)
                ->exists();
        }

        return $this->scopeService->canPerformAction($user, 'view', 'section', $section);
    }

    /**
     * Determine if the user can create sections
     */
    public function create(User $user): bool
    {
        return $user->hasRole([
            'admin', 'registrar', 'department-head', 'academic-admin'
        ]);
    }

    /**
     * Determine if the user can update the section
     */
    public function update(User $user, CourseSection $section): bool
    {
        return $this->scopeService->canPerformAction($user, 'update', 'section', $section);
    }

    /**
     * Determine if the user can delete the section
     */
    public function delete(User $user, CourseSection $section): bool
    {
        // Only admin and registrar can delete sections
        if ($user->hasRole(['admin', 'registrar'])) {
            return true;
        }

        // Department head can delete if no enrollments
        if ($user->hasRole('department-head') && 
            $section->course->department_id === $user->department_id &&
            $section->current_enrollment === 0) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can manage section enrollments
     */
    public function manageEnrollments(User $user, CourseSection $section): bool
    {
        return $this->scopeService->canPerformAction($user, 'manage_enrollments', 'section', $section);
    }

    /**
     * Determine if the user can view section roster
     */
    public function viewRoster(User $user, CourseSection $section): bool
    {
        // Instructor can view their roster
        if ($section->instructor_id === $user->id) {
            return true;
        }

        // Admin and registrar can view any roster
        if ($user->hasRole(['admin', 'registrar'])) {
            return true;
        }

        // Department head can view rosters in their department
        if ($user->hasRole('department-head') && 
            $section->course->department_id === $user->department_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can assign instructor
     */
    public function assignInstructor(User $user, CourseSection $section): bool
    {
        // Admin and registrar can assign any instructor
        if ($user->hasRole(['admin', 'registrar'])) {
            return true;
        }

        // Department head can assign instructors for their department's sections
        if ($user->hasRole('department-head') && 
            $section->course->department_id === $user->department_id) {
            return true;
        }

        return false;
    }
}