<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->configureRoutePatterns();
        $this->configureRouteModelBindings();

        $this->routes(function () {
            // ============================================================
            // API ROUTES - Stateless, token-based authentication
            // ============================================================
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // ============================================================
            // PUBLIC WEB ROUTES - No authentication required
            // ============================================================
            if (file_exists(base_path('routes/public.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/public.php'));
            }

            // ============================================================
            // AUTHENTICATION ROUTES - Login, registration, password reset
            // ============================================================
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));

            // ============================================================
            // MAIN WEB APPLICATION ROUTES - Base authenticated routes
            // ============================================================
            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/web.php'));

            // ============================================================
            // STUDENT PORTAL ROUTES
            // ============================================================
            if (file_exists(base_path('routes/student.php'))) {
                Route::middleware(['web', 'auth', 'verified'])
                    ->prefix('student')
                    ->name('student.')
                    ->group(base_path('routes/student.php'));
            }

            // ============================================================
            // FACULTY PORTAL ROUTES
            // ============================================================
            if (file_exists(base_path('routes/faculty.php'))) {
                Route::middleware(['web', 'auth', 'role:faculty,department-head,dean,admin'])
                    ->prefix('faculty')
                    ->name('faculty.')
                    ->group(base_path('routes/faculty.php'));
            }

            // ============================================================
            // ACADEMIC MANAGEMENT ROUTES
            // Courses, Registration, Grades, Transcripts
            // ============================================================
            if (file_exists(base_path('routes/academic.php'))) {
                Route::middleware(['web', 'auth'])
                    ->group(base_path('routes/academic.php'));
            }

            // ============================================================
            // ADMISSIONS & ENROLLMENT ROUTES
            // Mixed public/authenticated routes
            // ============================================================
            if (file_exists(base_path('routes/admissions.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/admissions.php'));
            }

            // ============================================================
            // FINANCIAL MANAGEMENT ROUTES
            // ============================================================
            if (file_exists(base_path('routes/financial.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('financial')
                    ->name('financial.')
                    ->group(base_path('routes/financial.php'));
            }

            // ============================================================
            // LEARNING MANAGEMENT SYSTEM (LMS) ROUTES
            // ============================================================
            if (config('app.intellicampus.modules.lms.enabled', true) && file_exists(base_path('routes/lms.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('lms')
                    ->name('lms.')
                    ->group(base_path('routes/lms.php'));
            }

            // ============================================================
            // ORGANIZATIONAL STRUCTURE ROUTES
            // Colleges, Schools, Departments, Divisions
            // ============================================================
            if (file_exists(base_path('routes/organizational.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('organization')
                    ->name('organization.')
                    ->group(base_path('routes/organizational.php'));
            }

            // ============================================================
            // ADMINISTRATIVE ROUTES
            // System administration, configuration, user management
            // ============================================================
            if (file_exists(base_path('routes/admin.php'))) {
                Route::middleware(['web', 'auth', 'role:admin,super-administrator,system-administrator'])
                    ->prefix('admin')
                    ->name('admin.')
                    ->group(base_path('routes/admin.php'));
            }

            // ============================================================
            // REGISTRAR OFFICE ROUTES
            // ============================================================
            if (file_exists(base_path('routes/registrar.php'))) {
                Route::middleware(['web', 'auth', 'role:registrar,academic-administrator,admin'])
                    ->prefix('registrar')
                    ->name('registrar.')
                    ->group(base_path('routes/registrar.php'));
            }

            // ============================================================
            // ADVISOR PORTAL ROUTES
            // ============================================================
            if (file_exists(base_path('routes/advisor.php'))) {
                Route::middleware(['web', 'auth', 'role:advisor,academic-advisor,faculty'])
                    ->prefix('advisor')
                    ->name('advisor.')
                    ->group(base_path('routes/advisor.php'));
            }

            // ============================================================
            // DEPARTMENT HEAD ROUTES
            // ============================================================
            if (file_exists(base_path('routes/department.php'))) {
                Route::middleware(['web', 'auth', 'role:department-head,department-chair,dean,admin'])
                    ->prefix('department')
                    ->name('department.')
                    ->group(base_path('routes/department.php'));
            }

            // ============================================================
            // EXAMINATION MANAGEMENT ROUTES
            // ============================================================
            if (config('app.intellicampus.modules.examinations.enabled', true) && file_exists(base_path('routes/exams.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('exams')
                    ->name('exams.')
                    ->group(base_path('routes/exams.php'));
            }

            // ============================================================
            // SYSTEM CONFIGURATION ROUTES
            // ============================================================
            if (file_exists(base_path('routes/system.php'))) {
                Route::middleware(['web', 'auth', 'role:admin,system-administrator'])
                    ->prefix('system')
                    ->name('system.')
                    ->group(base_path('routes/system.php'));
            }

            // ============================================================
            // REPORTING & ANALYTICS ROUTES
            // ============================================================
            if (file_exists(base_path('routes/reports.php'))) {
                Route::middleware(['web', 'auth', 'permission:reports.view'])
                    ->prefix('reports')
                    ->name('reports.')
                    ->group(base_path('routes/reports.php'));
            }

            // ============================================================
            // COMMUNICATION & NOTIFICATIONS ROUTES
            // ============================================================
            if (config('app.intellicampus.modules.communications.enabled', true) && file_exists(base_path('routes/communications.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('communications')
                    ->name('communications.')
                    ->group(base_path('routes/communications.php'));
            }

            // ============================================================
            // ATTENDANCE MANAGEMENT ROUTES
            // ============================================================
            if (config('app.intellicampus.modules.attendance.enabled', true) && file_exists(base_path('routes/attendance.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('attendance')
                    ->name('attendance.')
                    ->group(base_path('routes/attendance.php'));
            }

            // ============================================================
            // SCHEDULING & TIMETABLING ROUTES
            // ============================================================
            if (config('app.intellicampus.modules.scheduling.enabled', true) && file_exists(base_path('routes/scheduling.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('scheduling')
                    ->name('scheduling.')
                    ->group(base_path('routes/scheduling.php'));
            }

            // ============================================================
            // DEGREE AUDIT ROUTES
            // ============================================================
            if (config('app.intellicampus.modules.degree_audit.enabled', true) && file_exists(base_path('routes/degree-audit.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('degree-audit')
                    ->name('degree-audit.')
                    ->group(base_path('routes/degree-audit.php'));
            }

            // ============================================================
            // HOUSING & CAMPUS SERVICES ROUTES (Future Module)
            // ============================================================
            if (config('app.intellicampus.modules.housing.enabled', false) && file_exists(base_path('routes/housing.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('housing')
                    ->name('housing.')
                    ->group(base_path('routes/housing.php'));
            }

            // ============================================================
            // LIBRARY MANAGEMENT ROUTES (Future Module)
            // ============================================================
            if (config('app.intellicampus.modules.library.enabled', false) && file_exists(base_path('routes/library.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('library')
                    ->name('library.')
                    ->group(base_path('routes/library.php'));
            }

            // ============================================================
            // ALUMNI MANAGEMENT ROUTES (Future Module)
            // ============================================================
            if (config('app.intellicampus.modules.alumni.enabled', false) && file_exists(base_path('routes/alumni.php'))) {
                Route::middleware(['web'])
                    ->prefix('alumni')
                    ->name('alumni.')
                    ->group(base_path('routes/alumni.php'));
            }

            // ============================================================
            // HEALTHCARE SERVICES ROUTES (Future Module)
            // ============================================================
            if (config('app.intellicampus.modules.healthcare.enabled', false) && file_exists(base_path('routes/healthcare.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('healthcare')
                    ->name('healthcare.')
                    ->group(base_path('routes/healthcare.php'));
            }

            // ============================================================
            // ATHLETICS & SPORTS ROUTES (Future Module)
            // ============================================================
            if (config('app.intellicampus.modules.athletics.enabled', false) && file_exists(base_path('routes/athletics.php'))) {
                Route::middleware(['web'])
                    ->prefix('athletics')
                    ->name('athletics.')
                    ->group(base_path('routes/athletics.php'));
            }

            // ============================================================
            // TRANSPORTATION ROUTES (Future Module)
            // ============================================================
            if (config('app.intellicampus.modules.transportation.enabled', false) && file_exists(base_path('routes/transportation.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('transportation')
                    ->name('transportation.')
                    ->group(base_path('routes/transportation.php'));
            }

            // ============================================================
            // CAFETERIA/DINING ROUTES (Future Module)
            // ============================================================
            if (config('app.intellicampus.modules.cafeteria.enabled', false) && file_exists(base_path('routes/cafeteria.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('cafeteria')
                    ->name('cafeteria.')
                    ->group(base_path('routes/cafeteria.php'));
            }

            // ============================================================
            // RESEARCH MANAGEMENT ROUTES (Future Module)
            // ============================================================
            if (config('app.intellicampus.modules.research.enabled', false) && file_exists(base_path('routes/research.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('research')
                    ->name('research.')
                    ->group(base_path('routes/research.php'));
            }

            // ============================================================
            // DEVELOPMENT & TESTING ROUTES (Only in non-production)
            // ============================================================
            if (app()->environment(['local', 'development', 'staging']) && file_exists(base_path('routes/development.php'))) {
                Route::middleware('web')
                    ->prefix('dev')
                    ->name('dev.')
                    ->group(base_path('routes/development.php'));
            }
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Standard API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Strict rate limiting for authentication attempts
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Rate limiting for file uploads
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limiting for report generation
        RateLimiter::for('reports', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limiting for bulk operations
        RateLimiter::for('bulk', function (Request $request) {
            return Limit::perMinute(2)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limiting for payment processing
        RateLimiter::for('payments', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limiting for email sending
        RateLimiter::for('emails', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limiting for grade submissions
        RateLimiter::for('grades', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limiting for attendance marking
        RateLimiter::for('attendance', function (Request $request) {
            return Limit::perHour(100)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limiting for exam submissions
        RateLimiter::for('exams', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Configure route patterns for common parameters
     */
    protected function configureRoutePatterns(): void
    {
        // Numeric ID patterns
        Route::pattern('id', '[0-9]+');
        Route::pattern('student', '[0-9]+');
        Route::pattern('faculty', '[0-9]+');
        Route::pattern('course', '[0-9]+');
        Route::pattern('section', '[0-9]+');
        Route::pattern('term', '[0-9]+');
        Route::pattern('enrollment', '[0-9]+');
        Route::pattern('grade', '[0-9]+');
        Route::pattern('payment', '[0-9]+');
        Route::pattern('application', '[0-9]+');
        Route::pattern('building', '[0-9]+');
        Route::pattern('room', '[0-9]+');
        Route::pattern('exam', '[0-9]+');
        Route::pattern('assignment', '[0-9]+');
        Route::pattern('quiz', '[0-9]+');
        Route::pattern('forum', '[0-9]+');
        Route::pattern('post', '[0-9]+');
        Route::pattern('college', '[0-9]+');
        Route::pattern('school', '[0-9]+');
        Route::pattern('department', '[0-9]+');
        Route::pattern('division', '[0-9]+');
        Route::pattern('program', '[0-9]+');
        
        // UUID patterns
        Route::pattern('uuid', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');
        
        // Slug patterns
        Route::pattern('slug', '[a-z0-9-]+');
        
        // Code patterns
        Route::pattern('code', '[A-Z0-9-]+');
        Route::pattern('course_code', '[A-Z]{2,4}[0-9]{3,4}');
        Route::pattern('student_id', '[A-Z]{2}[0-9]{6,8}');
        
        // Academic term pattern (e.g., "2024-fall")
        Route::pattern('term_code', '[0-9]{4}-(spring|summer|fall|winter)');
        
        // Status patterns
        Route::pattern('status', '(active|inactive|pending|approved|rejected|draft|submitted|cancelled)');
        
        // Academic patterns
        Route::pattern('semester', '(fall|spring|summer|winter)');
        Route::pattern('year', '[0-9]{4}');
        Route::pattern('level', '(undergraduate|graduate|doctoral|certificate)');
        
        // Grade patterns
        Route::pattern('grade_letter', '[A-F][+-]?');
        
        // Time patterns
        Route::pattern('time_slot', '[0-9]{2}:[0-9]{2}-[0-9]{2}:[0-9]{2}');
        Route::pattern('day', '(monday|tuesday|wednesday|thursday|friday|saturday|sunday)');
    }

    /**
     * Configure implicit route model bindings
     */
    protected function configureRouteModelBindings(): void
    {
        // Custom resolution logic for models
        Route::bind('student_by_sid', function ($value) {
            return \App\Models\Student::where('student_id', $value)->firstOrFail();
        });

        Route::bind('course_by_code', function ($value) {
            return \App\Models\Course::where('code', $value)->firstOrFail();
        });

        Route::bind('term_by_code', function ($value) {
            return \App\Models\AcademicTerm::where('code', $value)->firstOrFail();
        });

        Route::bind('user_by_username', function ($value) {
            return \App\Models\User::where('username', $value)->firstOrFail();
        });

        Route::bind('section_by_crn', function ($value) {
            return \App\Models\CourseSection::where('crn', $value)->firstOrFail();
        });

        Route::bind('program_by_code', function ($value) {
            return \App\Models\AcademicProgram::where('code', $value)->firstOrFail();
        });

        Route::bind('building_by_code', function ($value) {
            return \App\Models\Building::where('code', $value)->firstOrFail();
        });

        Route::bind('room_by_number', function ($value) {
            return \App\Models\Room::where('room_number', $value)->firstOrFail();
        });
    }
}