<?php

// app/Policies/DepartmentPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Department;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any departments
     */
    public function viewAny(User $user): bool
    {
        // Most users can view department list
        return true;
    }

    /**
     * Determine if the user can view the department
     */
    public function view(User $user, Department $department): bool
    {
        // Anyone can view basic department info
        return true;
    }

    /**
     * Determine if the user can create departments
     */
    public function create(User $user): bool
    {
        // Only admin can create departments
        return $user->hasRole(['admin', 'super-admin']);
    }

    /**
     * Determine if the user can update the department
     */
    public function update(User $user, Department $department): bool
    {
        // Admin can update any department
        if ($user->hasRole(['admin', 'super-admin'])) {
            return true;
        }

        // Department head can update their department
        if ($department->head_id === $user->id) {
            return true;
        }

        // Dean can update departments in their college
        if ($user->hasRole('dean') && $department->college_id === $user->college_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can delete the department
     */
    public function delete(User $user, Department $department): bool
    {
        // Only super admin can delete departments
        return $user->hasRole('super-admin');
    }

    /**
     * Determine if the user can manage department faculty
     */
    public function manageFaculty(User $user, Department $department): bool
    {
        // Admin can manage any department's faculty
        if ($user->hasRole(['admin', 'hr'])) {
            return true;
        }

        // Department head can manage their faculty
        if ($department->head_id === $user->id || 
            $department->deputy_head_id === $user->id) {
            return true;
        }

        // Dean can manage faculty in their college
        if ($user->hasRole('dean') && $department->college_id === $user->college_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can manage department courses
     */
    public function manageCourses(User $user, Department $department): bool
    {
        // Admin and academic admin can manage courses
        if ($user->hasRole(['admin', 'academic-admin'])) {
            return true;
        }

        // Department head can manage their courses
        if ($department->head_id === $user->id || 
            $department->deputy_head_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can view department statistics
     */
    public function viewStatistics(User $user, Department $department): bool
    {
        // Admin can view all statistics
        if ($user->hasRole(['admin', 'super-admin'])) {
            return true;
        }

        // Department leadership can view their statistics
        if ($department->hasAdministrator($user)) {
            return true;
        }

        // Dean can view statistics for departments in their college
        if ($user->hasRole('dean') && $department->college_id === $user->college_id) {
            return true;
        }

        // Faculty in department can view basic statistics
        if ($user->department_id === $department->id) {
            return true;
        }

        return false;
    }
}
