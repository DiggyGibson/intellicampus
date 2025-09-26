<?php
// ============================================================
// File 1: bootstrap/app.php (Enhanced version based on your current)
// ============================================================

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // ============================================================
            // CRITICAL: Load auth.php FIRST with highest priority
            // ============================================================
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));
            
            // ============================================================
            // PROTECTED ROUTES WITH ROLE-BASED ACCESS
            // ============================================================
            
            // Student portal routes
            if (file_exists(base_path('routes/student.php'))) {
                Route::middleware(['web', 'auth', 'verified'])
                    ->prefix('student')
                    ->name('student.')
                    ->group(base_path('routes/student.php'));
            }
            
            // Faculty portal routes
            if (file_exists(base_path('routes/faculty.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('faculty')
                    ->name('faculty.')
                    ->group(base_path('routes/faculty.php'));
            }
            
            // Admin routes - Enhanced security
            if (file_exists(base_path('routes/admin.php'))) {
                Route::middleware(['web', 'auth', 'verified', 'role:admin,super-administrator,system-administrator,academic-administrator'])
                    ->prefix('admin')
                    ->name('admin.')
                    ->group(base_path('routes/admin.php'));
            }
            
            // Registrar routes
            if (file_exists(base_path('routes/registrar.php'))) {
                Route::middleware(['web', 'auth', 'role:registrar,academic-administrator,super-administrator'])
                    ->prefix('registrar')
                    ->name('registrar.')
                    ->group(base_path('routes/registrar.php'));
            }
            
            // Advisor routes
            if (file_exists(base_path('routes/advisor.php'))) {
                Route::middleware(['web', 'auth', 'role:advisor,faculty,academic-administrator,super-administrator'])
                    ->prefix('advisor')
                    ->name('advisor.')
                    ->group(base_path('routes/advisor.php'));
            }
            
            // Department routes
            if (file_exists(base_path('routes/department.php'))) {
                Route::middleware(['web', 'auth', 'role:department-head,department-chair,dean,academic-administrator,super-administrator'])
                    ->prefix('department')
                    ->name('department.')
                    ->group(base_path('routes/department.php'));
            }
            
            // Financial routes - Role-based access
            if (file_exists(base_path('routes/financial.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('financial')
                    ->name('financial.')
                    ->group(base_path('routes/financial.php'));
            }
            
            // Parent portal routes (NEW)
            if (file_exists(base_path('routes/parent.php'))) {
                Route::middleware(['web', 'auth', 'role:parent-guardian'])
                    ->prefix('parent')
                    ->name('parent.')
                    ->group(base_path('routes/parent.php'));
            }
            
            // Audit routes (NEW)
            if (file_exists(base_path('routes/audit.php'))) {
                Route::middleware(['web', 'auth', 'role:auditor,super-administrator'])
                    ->prefix('audit')
                    ->name('audit.')
                    ->group(base_path('routes/audit.php'));
            }
            
            // Staff routes (NEW)
            if (file_exists(base_path('routes/staff.php'))) {
                Route::middleware(['web', 'auth', 'role:staff'])
                    ->prefix('staff')
                    ->name('staff.')
                    ->group(base_path('routes/staff.php'));
            }
            
            // Alumni routes - WITH PREFIX to avoid conflicts
            if (file_exists(base_path('routes/alumni.php'))) {
                Route::middleware('web')
                    ->prefix('alumni')
                    ->name('alumni.')
                    ->group(base_path('routes/alumni.php'));
            }
            
            // LMS routes
            if (file_exists(base_path('routes/lms.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('lms')
                    ->name('lms.')
                    ->group(base_path('routes/lms.php'));
            }
            
            // Housing routes
            if (file_exists(base_path('routes/housing.php'))) {
                Route::middleware('web')
                    ->prefix('housing')
                    ->name('housing.')
                    ->group(base_path('routes/housing.php'));
            }
            
            // Library routes
            if (file_exists(base_path('routes/library.php'))) {
                Route::middleware('web')
                    ->prefix('library')
                    ->name('library.')
                    ->group(base_path('routes/library.php'));
            }
            
            // Communications routes
            if (file_exists(base_path('routes/communications.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('communications')
                    ->name('communications.')
                    ->group(base_path('routes/communications.php'));
            }
            
            // Exams routes
            if (file_exists(base_path('routes/exams.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('exams')
                    ->name('exams.')
                    ->group(base_path('routes/exams.php'));
            }
            
            // Reports routes
            if (file_exists(base_path('routes/reports.php'))) {
                Route::middleware(['web', 'auth', 'permission:reports.view'])
                    ->prefix('reports')
                    ->name('reports.')
                    ->group(base_path('routes/reports.php'));
            }
            
            // Organizational routes
            if (file_exists(base_path('routes/organizational.php'))) {
                Route::middleware(['web', 'auth'])
                    ->prefix('organization')
                    ->name('organization.')
                    ->group(base_path('routes/organizational.php'));
            }
            
            // System routes
            if (file_exists(base_path('routes/system.php'))) {
                Route::middleware(['web', 'auth', 'role:super-administrator,system-administrator'])
                    ->prefix('system')
                    ->name('system.')
                    ->group(base_path('routes/system.php'));
            }
            
            // Development routes (only in local environment)
            if (app()->environment(['local', 'development']) && file_exists(base_path('routes/development.php'))) {
                Route::middleware('web')
                    ->prefix('dev')
                    ->name('dev.')
                    ->group(base_path('routes/development.php'));
            }
            
            // ============================================================
            // Load routes without prefixes AFTER prefixed routes
            // ============================================================
            
            // Public routes (no auth required)
            if (file_exists(base_path('routes/public.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/public.php'));
            }
            
            // Academic routes (authenticated but no prefix)
            if (file_exists(base_path('routes/academic.php'))) {
                Route::middleware(['web', 'auth'])
                    ->group(base_path('routes/academic.php'));
            }
            
            // Admissions routes (mixed public/auth, no prefix)
            if (file_exists(base_path('routes/admissions.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/admissions.php'));
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ============================================================
        // REGISTER ALL CUSTOM MIDDLEWARE ALIASES
        // ============================================================
        $middleware->alias([
            // Authentication & Authorization
            'auth' => \App\Http\Middleware\Authenticate::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            
            // Role & Permission Checking
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'user.type' => \App\Http\Middleware\CheckUserType::class,
            
            // Organizational Scope Management
            'scope' => \App\Http\Middleware\ApplyScopeFilter::class,
            'department.access' => \App\Http\Middleware\CheckDepartmentAccess::class,
            'org.scope' => \App\Http\Middleware\CheckOrganizationalScope::class,
            'enforce.scope' => \App\Http\Middleware\EnforceScopePolicy::class,
            
            // Module Access Control
            'module.access' => \App\Http\Middleware\ModuleAccessControl::class,
            
            // Student Specific
            'student' => \App\Http\Middleware\StudentMiddleware::class,
            
            // Utility Middleware
            'signed' => \App\Http\Middleware\ValidateSignature::class,
        ]);
        
        // ============================================================
        // GLOBAL MIDDLEWARE
        // ============================================================
        $middleware->append(\App\Http\Middleware\TrimStrings::class);
        $middleware->append(\Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class);
        
        // ============================================================
        // MIDDLEWARE GROUPS
        // ============================================================
        $middleware->group('web', [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->group('api', [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
        
        // ============================================================
        // MIDDLEWARE PRIORITY
        // ============================================================
        $middleware->priority([
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\Authenticate::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Auth\Middleware\Authorize::class,
            \App\Http\Middleware\CheckRole::class,
            \App\Http\Middleware\CheckPermission::class,
            \App\Http\Middleware\CheckUserType::class,
            \App\Http\Middleware\CheckDepartmentAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Your existing exception handling
        $exceptions->report(function (\Throwable $e) {
            if (app()->environment('production')) {
                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    \Log::warning('Authorization failure', [
                        'user' => auth()->user()?->id,
                        'message' => $e->getMessage(),
                        'url' => request()->fullUrl(),
                    ]);
                }
            }
        });
        
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthorized access',
                    'error' => $e->getMessage()
                ], 403);
            }
            
            // Enhanced: Redirect to user's appropriate dashboard
            if (auth()->check()) {
                $user = auth()->user();
                $primaryRole = $user->roles->first();
                
                if ($primaryRole) {
                    $landingPages = config('navigation.landing_pages', []);
                    $landingPage = $landingPages[$primaryRole->slug] ?? '/dashboard';
                    return redirect($landingPage)->with('error', 'You are not authorized to access that resource.');
                }
            }
            
            return redirect()->route('dashboard')
                ->with('error', 'You are not authorized to access that resource.');
        });
        
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Resource not found',
                    'error' => 'The requested resource does not exist'
                ], 404);
            }
            
            return null;
        });
    })->create();