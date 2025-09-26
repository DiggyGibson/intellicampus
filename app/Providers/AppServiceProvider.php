<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Services\ScopeManagementService;
use App\Models\Course;
use App\Models\Student;
use App\Models\Department;
use App\Models\Grade;
use App\Models\CourseSection;
use App\Policies\CoursePolicy;
use App\Policies\StudentPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\GradePolicy;
use App\Policies\CourseSectionPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the scope service as a singleton
        $this->app->singleton(ScopeManagementService::class, function ($app) {
            return new ScopeManagementService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Grade::class, GradePolicy::class);
        Gate::policy(CourseSection::class, CourseSectionPolicy::class);

        // Define super admin gate
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });

        // Define organizational gates
        Gate::define('manage-college', function ($user, $college) {
            return $college->hasAdministrator($user);
        });

        Gate::define('manage-school', function ($user, $school) {
            return $school->director_id === $user->id;
        });

        Gate::define('manage-department', function ($user, $department) {
            return $user->canManageDepartment($department);
        });

        Gate::define('access-department', function ($user, $department) {
            return $user->canAccessDepartment($department);
        });
    }
}