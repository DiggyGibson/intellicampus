<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RegistrarController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes - Enhanced with Role-Based Routing
|--------------------------------------------------------------------------
*/

// ============================================================
// INCLUDE MODULE ROUTE FILES
// ============================================================
// Check if module route files exist and include them
$moduleRoutes = [
    'admissions.php',
    'academic.php',
    'faculty.php',
    'student.php',
    'finance.php',
    'registrar.php',
    'admin.php',
    'exams.php',
    'department.php',
    'advisor.php',
    'library.php',
    'reports.php',
    'api.php',
    'public.php',
];

foreach ($moduleRoutes as $routeFile) {
    $filePath = __DIR__ . '/' . $routeFile;
    if (file_exists($filePath)) {
        require $filePath;
    }
}

// ============================================================
// SYSTEM ANALYSIS & DEBUG ROUTES (Keep your existing ones)
// ============================================================
Route::get('/system-analysis', function() {
    if (!auth()->check() || !auth()->user()->hasRole(['super-administrator', 'system-administrator'])) {
        abort(403, 'Unauthorized');
    }
    
    // Your existing system analysis code...
    $routes = Route::getRoutes();
    $routesByPrefix = [];
    $namedRoutes = [];
    $routesByMiddleware = [];
    
    foreach ($routes as $route) {
        $uri = $route->uri();
        $name = $route->getName();
        $action = $route->getActionName();
        $methods = implode('|', $route->methods());
        $middleware = $route->middleware();
        
        if (str_starts_with($uri, '_') || str_starts_with($uri, 'sanctum/') || str_starts_with($uri, 'livewire/')) {
            continue;
        }
        
        $prefix = explode('/', $uri)[0] ?? 'root';
        if (!isset($routesByPrefix[$prefix])) {
            $routesByPrefix[$prefix] = [];
        }
        
        $routeInfo = [
            'uri' => $uri,
            'name' => $name,
            'methods' => $methods,
            'action' => $action,
            'middleware' => $middleware
        ];
        
        $routesByPrefix[$prefix][] = $routeInfo;
        
        if ($name) {
            $namedRoutes[$name] = $routeInfo;
        }
        
        $middlewareKey = implode(',', $middleware);
        if (!isset($routesByMiddleware[$middlewareKey])) {
            $routesByMiddleware[$middlewareKey] = [];
        }
        $routesByMiddleware[$middlewareKey][] = $routeInfo;
    }
    
    $expectedRoutes = [
        'dashboard',
        'student.dashboard',
        'faculty.dashboard',
        'registrar.dashboard',
        'admin.dashboard',
        'department.dashboard',
        'advisor.dashboard',
        'parent.dashboard',
        'audit.dashboard',
        'alumni.dashboard',
        'staff.dashboard',
        'financial.admin.dashboard',
        'admissions.portal.index',
        'admissions.portal.start',
    ];
    
    $missingRoutes = [];
    foreach ($expectedRoutes as $expectedRoute) {
        if (!Route::has($expectedRoute)) {
            $missingRoutes[] = $expectedRoute;
        }
    }
    
    $unusedNamedRoutes = array_diff(array_keys($namedRoutes), $expectedRoutes);
    
    $user = auth()->user();
    $userAnalysis = [
        'id' => $user->id,
        'email' => $user->email,
        'user_type' => $user->user_type ?? 'N/A',
        'roles' => $user->roles->pluck('name'),
        'primary_role' => $user->roles->first()?->slug ?? 'none',
        'landing_page' => getUserLandingPage($user),
        'permissions' => method_exists($user, 'getDirectPermissions') ? 
            $user->getDirectPermissions()->pluck('name') : [],
    ];
    
    // Add module routes analysis
    $moduleRoutesAnalysis = [];
    foreach ($moduleRoutes as $routeFile) {
        $filePath = __DIR__ . '/' . $routeFile;
        $moduleRoutesAnalysis[$routeFile] = [
            'exists' => file_exists($filePath),
            'path' => $filePath,
            'size' => file_exists($filePath) ? filesize($filePath) : 0,
        ];
    }
    
    return view('system-analysis', compact(
        'routesByPrefix',
        'namedRoutes',
        'routesByMiddleware',
        'expectedRoutes',
        'missingRoutes',
        'unusedNamedRoutes',
        'userAnalysis',
        'moduleRoutesAnalysis'
    ));
})->middleware(['auth'])->name('system.analysis');

// Cache debugging routes
Route::get('/nav-debug', function () {
    if (!auth()->check()) {
        abort(403);
    }
    
    $user = auth()->user();
    $navigationConfig = config('navigation');
    
    return response()->json([
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name'),
        ],
        'config_exists' => !is_null($navigationConfig),
        'config_sections' => $navigationConfig ? array_keys($navigationConfig) : [],
        'sidebar_items' => $navigationConfig['sidebar'] ?? [],
        'landing_pages' => $navigationConfig['landing_pages'] ?? [],
    ]);
})->middleware('auth')->name('nav.debug');

Route::get('/nav-clear-cache', function () {
    if (!auth()->check()) {
        abort(403);
    }
    
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    
    return redirect()->back()->with('success', 'Navigation cache cleared');
})->middleware('auth')->name('nav.clear-cache');

route::get('/test-save-section/{uuid}', function($uuid) {
    $controller = new \App\Http\Controllers\ApplicationFormController();
    $request = new \Illuminate\Http\Request([
        'section' => 'academic',
        'previous_institution' => 'Debug Test University',
        'previous_gpa' => '3.75',
        'gpa_scale' => '4.0'
    ]);
    
    return $controller->saveSection($request, $uuid);
});


// ============================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================
Route::get('/', function () {
    // Check if public home view exists, otherwise show welcome
    if (view()->exists('public.home')) {
        return view('public.home');
    }
    return view('welcome');
})->name('public.home');

Route::get('/about', function () {
    if (view()->exists('public.about')) {
        return view('public.about');
    }
    return view('about');
})->name('public.about');

Route::get('/contact', function () {
    if (view()->exists('public.contact')) {
        return view('public.contact');
    }
    return view('contact');
})->name('public.contact');

Route::get('/programs', function () {
    if (view()->exists('public.programs')) {
        return view('public.programs');
    }
    return view('programs');
})->name('public.programs');

// ============================================================
// AUTHENTICATION ROUTES
// ============================================================
require __DIR__.'/auth.php';

// ============================================================
// AUTHENTICATED ROUTES - ENHANCED WITH ROLE-BASED LOGIC
// ============================================================
Route::middleware(['auth', 'verified'])->group(function () {
    
    // ============================================================
    // UNIVERSAL DASHBOARD ROUTE WITH SMART REDIRECTS
    // ============================================================
    Route::get('/dashboard', function() {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Get landing page for user's role
        $landingPage = getUserLandingPage($user);
        
        // If user's landing page is different from /dashboard, redirect them
        if ($landingPage !== '/dashboard' && $landingPage !== url('/dashboard')) {
            return redirect($landingPage);
        }
        
        // Only show main dashboard to authorized users
        if ($user->hasAnyRole(['super-administrator', 'system-administrator', 'academic-administrator'])) {
            if (class_exists(DashboardController::class)) {
                return app(DashboardController::class)->index();
            }
            return view('dashboard');
        }
        
        // Everyone else goes to their role-specific dashboard
        return redirect($landingPage);
    })->name('dashboard');
    
    // ============================================================
    // MY PORTAL - Smart multi-role redirect
    // ============================================================
    Route::get('/my-portal', function() {
        $user = Auth::user();
        $landingPage = getUserLandingPage($user);
        return redirect($landingPage);
    })->name('my-portal');
    
    // ============================================================
    // PROFILE MANAGEMENT (Universal)
    // ============================================================
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
        Route::get('/photo', [ProfileController::class, 'photo'])->name('photo');
        Route::post('/photo', [ProfileController::class, 'updatePhoto'])->name('photo.update');
        Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
        Route::patch('/settings', [ProfileController::class, 'updateSettings'])->name('settings.update');
    });
    
    // ============================================================
    // UNIVERSAL FEATURES (Available to all authenticated users)
    // ============================================================
    
    // Search
    Route::get('/search', function () {
        $query = request('q');
        if (!$query) {
            return back()->with('info', 'Please enter a search term.');
        }
        
        // Check if SearchController exists
        if (class_exists(\App\Http\Controllers\SearchController::class)) {
            return app(\App\Http\Controllers\SearchController::class)->search($query);
        }
        
        return back()->with('info', "Search results for '{$query}' coming soon.");
    })->name('search');
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', function() {
            return view('notifications.index', [
                'notifications' => Auth::user()->notifications()->paginate(20)
            ]);
        })->name('index');
        
        Route::post('/{id}/mark-read', function($id) {
            Auth::user()->notifications()->findOrFail($id)->markAsRead();
            return back()->with('success', 'Notification marked as read.');
        })->name('mark-read');
        
        Route::post('/mark-all-read', function() {
            Auth::user()->unreadNotifications->markAsRead();
            return back()->with('success', 'All notifications marked as read.');
        })->name('mark-all-read');
        
        Route::delete('/{id}', function($id) {
            Auth::user()->notifications()->findOrFail($id)->delete();
            return back()->with('success', 'Notification deleted.');
        })->name('delete');
    });
    
    // Announcements
    Route::get('/announcements', function() {
        if (class_exists(\App\Http\Controllers\AnnouncementController::class)) {
            return app(\App\Http\Controllers\AnnouncementController::class)->index();
        }
        return view('announcements.index');
    })->name('announcements.index');
    
    Route::get('/announcements/{id}', function($id) {
        if (class_exists(\App\Http\Controllers\AnnouncementController::class)) {
            return app(\App\Http\Controllers\AnnouncementController::class)->show($id);
        }
        return view('announcements.show', ['id' => $id]);
    })->name('announcements.show');
    
    // Messages/Mail
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', function() {
            if (class_exists(\App\Http\Controllers\MessageController::class)) {
                return app(\App\Http\Controllers\MessageController::class)->index();
            }
            return view('messages.index');
        })->name('index');
        
        Route::get('/compose', function() {
            return view('messages.compose');
        })->name('compose');
        
        Route::post('/send', function() {
            return back()->with('success', 'Message sent successfully.');
        })->name('send');
    });
    
    // Calendar
    Route::get('/calendar', function() {
        if (class_exists(\App\Http\Controllers\CalendarController::class)) {
            return app(\App\Http\Controllers\CalendarController::class)->index();
        }
        return view('calendar.index');
    })->name('calendar.index');
    
    // Help & Support
    Route::prefix('help')->name('help.')->group(function () {
        Route::get('/', function() {
            return view('help.index');
        })->name('index');
        
        Route::get('/faq', function() {
            return view('help.faq');
        })->name('faq');
        
        Route::get('/guides', function() {
            return view('help.guides');
        })->name('guides');
        
        Route::get('/contact-support', function() {
            return view('help.contact');
        })->name('contact');
        
        Route::post('/submit-ticket', function() {
            return back()->with('success', 'Support ticket submitted successfully.');
        })->name('submit-ticket');
    });
    
    // ============================================================
    // ROLE SWITCHING (for users with multiple roles)
    // ============================================================
    Route::post('/switch-role/{role}', function($roleId) {
        $user = Auth::user();
        $role = $user->roles()->where('id', $roleId)->first();
        
        if (!$role) {
            return back()->with('error', 'Invalid role selected.');
        }
        
        session(['active_role' => $role->slug]);
        
        // Redirect to the new role's landing page
        $landingPages = config('navigation.landing_pages', []);
        $landingPage = $landingPages[$role->slug] ?? '/dashboard';
        
        return redirect($landingPage)
            ->with('success', "Switched to {$role->name} role.");
    })->name('switch-role');
    
    // ============================================================
    // PLACEHOLDER ROUTES (Remove as modules are completed)
    // ============================================================
    
    // Library Placeholder (if library.php doesn't exist)
    if (!file_exists(__DIR__ . '/library.php')) {
        Route::get('/library', function() {
            return view('errors.under-construction', [
                'title' => 'Library Services',
                'message' => 'The library module is currently under construction.'
            ]);
        })->name('library.index');
    }
    
    // Parent Portal Placeholder
    Route::get('/parent/dashboard', function() {
        if (!Auth::user()->hasRole('parent-guardian')) {
            return redirect('/dashboard')->with('error', 'Access denied.');
        }
        return view('errors.under-construction', [
            'title' => 'Parent Portal',
            'message' => 'Parent portal is coming soon.'
        ]);
    })->name('parent.dashboard');
    
    // Audit Dashboard Placeholder
    Route::get('/audit/dashboard', function() {
        if (!Auth::user()->hasRole(['auditor', 'super-administrator'])) {
            return redirect('/dashboard')->with('error', 'Access denied.');
        }
        return view('errors.under-construction', [
            'title' => 'Audit Dashboard',
            'message' => 'Audit module is under development.'
        ]);
    })->name('audit.dashboard');
    
    // Alumni Dashboard Placeholder
    Route::get('/alumni/dashboard', function() {
        if (!Auth::user()->hasRole('alumni')) {
            return redirect('/dashboard')->with('error', 'Access denied.');
        }
        return view('errors.under-construction', [
            'title' => 'Alumni Portal',
            'message' => 'Alumni services are coming soon.'
        ]);
    })->name('alumni.dashboard');
    
    // Staff Dashboard Placeholder (if staff routes don't exist)
    if (!file_exists(__DIR__ . '/staff.php')) {
        Route::get('/staff/dashboard', function() {
            if (!Auth::user()->hasRole('staff')) {
                return redirect('/dashboard')->with('error', 'Access denied.');
            }
            return view('errors.under-construction', [
                'title' => 'Staff Portal',
                'message' => 'Staff dashboard is being developed.'
            ]);
        })->name('staff.dashboard');
    }
});

// ============================================================
// LOGOUT ROUTE
// ============================================================
Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ============================================================
// HELPER FUNCTION: Get user's landing page based on role
// ============================================================
if (!function_exists('getUserLandingPage')) {
    function getUserLandingPage($user) {
        // Check for active role in session
        if (session()->has('active_role')) {
            $activeRole = session('active_role');
            $landingPages = config('navigation.landing_pages', []);
            
            if (isset($landingPages[$activeRole])) {
                return $landingPages[$activeRole];
            }
        }
        
        // Check for primary role
        if ($user->roles && $user->roles->count() > 0) {
            $primaryRole = $user->roles->first();
            $landingPages = config('navigation.landing_pages', []);
            
            if (isset($landingPages[$primaryRole->slug])) {
                return $landingPages[$primaryRole->slug];
            }
        }
        
        // Fallback based on user_type
        return match($user->user_type) {
            'student' => '/student/dashboard',
            'faculty' => '/faculty/dashboard',
            'staff' => '/staff/dashboard',
            'admin' => '/admin/dashboard',
            default => '/dashboard'
        };
    }
}

// ============================================================
// FALLBACK ROUTE (Must be last)
// ============================================================
Route::fallback(function () {
    return view('errors.404');
});