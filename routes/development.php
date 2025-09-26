<?php
/**
 * IntelliCampus Development & Testing Routes
 * 
 * Routes for development, testing, and debugging purposes.
 * These routes are ONLY available in non-production environments.
 * These routes are automatically prefixed with 'dev' and named with 'dev.'
 * Applied middleware: 'web'
 * 
 * WARNING: These routes should NEVER be accessible in production!
 */

use App\Http\Controllers\SystemConfigurationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

// Ensure these routes are only available in non-production environments
if (!app()->environment(['local', 'development', 'staging'])) {
    abort(404);
}

// ============================================================
// DEVELOPMENT DASHBOARD
// ============================================================
Route::get('/', function() {
    return view('dev.dashboard', [
        'environment' => app()->environment(),
        'debug_mode' => config('app.debug'),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'database_connection' => config('database.default'),
        'cache_driver' => config('cache.default'),
        'queue_driver' => config('queue.default'),
        'mail_driver' => config('mail.default')
    ]);
})->name('dashboard');

// ============================================================
// DATABASE TOOLS
// ============================================================
Route::prefix('database')->name('database.')->group(function () {
    // Database Status
    Route::get('/', function() {
        try {
            $tables = DB::select('SELECT table_name, table_rows, data_length, index_length 
                                 FROM information_schema.tables 
                                 WHERE table_schema = ?', [config('database.connections.mysql.database')]);
            
            return response()->json([
                'status' => 'connected',
                'database' => config('database.connections.' . config('database.default') . '.database'),
                'tables_count' => count($tables),
                'tables' => $tables
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    })->name('status');
    
    // Run Migrations
    Route::post('/migrate', function() {
        try {
            Artisan::call('migrate');
            return response()->json([
                'status' => 'success',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    })->name('migrate');
    
    // Rollback Migrations
    Route::post('/rollback', function() {
        try {
            Artisan::call('migrate:rollback');
            return response()->json([
                'status' => 'success',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    })->name('rollback');
    
    // Fresh Migration (Drop all tables and re-run)
    Route::post('/fresh', function() {
        if (app()->environment('local')) {
            try {
                Artisan::call('migrate:fresh');
                return response()->json([
                    'status' => 'success',
                    'output' => Artisan::output()
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 500);
            }
        }
        return response()->json(['error' => 'Only available in local environment'], 403);
    })->name('fresh');
    
    // Seed Database
    Route::post('/seed', function() {
        try {
            Artisan::call('db:seed');
            return response()->json([
                'status' => 'success',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    })->name('seed');
    
    // Run specific seeder
    Route::post('/seed/{seeder}', function($seeder) {
        try {
            Artisan::call('db:seed', ['--class' => $seeder]);
            return response()->json([
                'status' => 'success',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    })->name('seed.specific');
    
    // Query Builder
    Route::get('/query', function() {
        return view('dev.query-builder');
    })->name('query');
    
    Route::post('/query/execute', function() {
        if (!app()->environment('local')) {
            return response()->json(['error' => 'Query execution only available in local environment'], 403);
        }
        
        $query = request('query');
        $type = request('type', 'select');
        
        try {
            if ($type === 'select') {
                $results = DB::select($query);
            } else {
                $results = DB::statement($query);
            }
            
            return response()->json([
                'status' => 'success',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    })->name('query.execute');
});

// ============================================================
// CACHE MANAGEMENT
// ============================================================
Route::prefix('cache')->name('cache.')->group(function () {
    // Cache Status
    Route::get('/', function() {
        return response()->json([
            'driver' => config('cache.default'),
            'stores' => config('cache.stores'),
            'prefix' => config('cache.prefix')
        ]);
    })->name('status');
    
    // Clear All Cache
    Route::post('/clear', function() {
        Cache::flush();
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        
        return response()->json([
            'status' => 'success',
            'message' => 'All cache cleared'
        ]);
    })->name('clear');
    
    // Clear Specific Cache
    Route::post('/clear/{type}', function($type) {
        switch($type) {
            case 'application':
                Cache::flush();
                break;
            case 'config':
                Artisan::call('config:clear');
                break;
            case 'route':
                Artisan::call('route:clear');
                break;
            case 'view':
                Artisan::call('view:clear');
                break;
            default:
                return response()->json(['error' => 'Invalid cache type'], 400);
        }
        
        return response()->json([
            'status' => 'success',
            'message' => "{$type} cache cleared"
        ]);
    })->name('clear.specific');
    
    // Cache Keys
    Route::get('/keys', function() {
        // This is implementation-specific and may not work with all cache drivers
        return response()->json([
            'message' => 'Cache key listing depends on cache driver implementation'
        ]);
    })->name('keys');
    
    // Test Cache
    Route::post('/test', function() {
        $key = 'test_' . time();
        $value = 'Test value at ' . now();
        
        Cache::put($key, $value, 60);
        $retrieved = Cache::get($key);
        Cache::forget($key);
        
        return response()->json([
            'status' => 'success',
            'stored' => $value,
            'retrieved' => $retrieved,
            'match' => $value === $retrieved
        ]);
    })->name('test');
});

// ============================================================
// SESSION MANAGEMENT
// ============================================================
Route::prefix('session')->name('session.')->group(function () {
    // View Session Data
    Route::get('/', function() {
        return response()->json([
            'id' => session()->getId(),
            'data' => session()->all(),
            'driver' => config('session.driver'),
            'lifetime' => config('session.lifetime')
        ]);
    })->name('view');
    
    // Clear Session
    Route::post('/clear', function() {
        session()->flush();
        return response()->json([
            'status' => 'success',
            'message' => 'Session cleared'
        ]);
    })->name('clear');
    
    // Regenerate Session
    Route::post('/regenerate', function() {
        session()->regenerate();
        return response()->json([
            'status' => 'success',
            'new_id' => session()->getId()
        ]);
    })->name('regenerate');
});

// ============================================================
// TEST DATA GENERATION
// ============================================================
Route::prefix('data')->name('data.')->group(function () {
    // Generate Test Students
    Route::post('/students/{count}', function($count) {
        $students = [];
        for ($i = 0; $i < min($count, 100); $i++) {
            $user = \App\Models\User::factory()->create();
            $student = \App\Models\Student::factory()->create([
                'user_id' => $user->id
            ]);
            $students[] = $student;
        }
        
        return response()->json([
            'status' => 'success',
            'created' => count($students),
            'students' => $students
        ]);
    })->name('students');
    
    // Generate Test Faculty
    Route::post('/faculty/{count}', function($count) {
        $faculty = [];
        for ($i = 0; $i < min($count, 50); $i++) {
            $user = \App\Models\User::factory()->create();
            $user->assignRole('faculty');
            $faculty[] = $user;
        }
        
        return response()->json([
            'status' => 'success',
            'created' => count($faculty),
            'faculty' => $faculty
        ]);
    })->name('faculty');
    
    // Generate Test Courses
    Route::post('/courses/{count}', function($count) {
        $courses = \App\Models\Course::factory()->count(min($count, 100))->create();
        
        return response()->json([
            'status' => 'success',
            'created' => $courses->count(),
            'courses' => $courses
        ]);
    })->name('courses');
    
    // Generate Test Enrollments
    Route::post('/enrollments', function() {
        $students = \App\Models\Student::limit(50)->get();
        $sections = \App\Models\CourseSection::limit(20)->get();
        $enrollments = [];
        
        foreach($students as $student) {
            $randomSections = $sections->random(rand(3, 5));
            foreach($randomSections as $section) {
                $enrollment = \App\Models\Enrollment::create([
                    'student_id' => $student->id,
                    'section_id' => $section->id,
                    'term_id' => 1,
                    'status' => 'enrolled',
                    'enrolled_at' => now()
                ]);
                $enrollments[] = $enrollment;
            }
        }
        
        return response()->json([
            'status' => 'success',
            'created' => count($enrollments)
        ]);
    })->name('enrollments');
    
    // Generate All Test Data
    Route::post('/all', function() {
        Artisan::call('db:seed', ['--class' => 'DevelopmentSeeder']);
        
        return response()->json([
            'status' => 'success',
            'message' => 'All test data generated',
            'output' => Artisan::output()
        ]);
    })->name('all');
    
    // Clear All Test Data
    Route::post('/clear', function() {
        if (app()->environment('local')) {
            Artisan::call('migrate:fresh');
            return response()->json([
                'status' => 'success',
                'message' => 'All data cleared'
            ]);
        }
        return response()->json(['error' => 'Only available in local environment'], 403);
    })->name('clear');
});

// ============================================================
// EMAIL TESTING
// ============================================================
Route::prefix('mail')->name('mail.')->group(function () {
    // Test Email Configuration
    Route::get('/config', function() {
        return response()->json([
            'driver' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'from' => config('mail.from'),
            'encryption' => config('mail.mailers.smtp.encryption')
        ]);
    })->name('config');
    
    // Send Test Email
    Route::post('/send', function() {
        $to = request('to', 'test@example.com');
        $subject = request('subject', 'Test Email from IntelliCampus');
        $body = request('body', 'This is a test email sent from the development environment.');
        
        try {
            Mail::raw($body, function($message) use ($to, $subject) {
                $message->to($to)
                       ->subject($subject);
            });
            
            return response()->json([
                'status' => 'success',
                'message' => 'Email sent successfully to ' . $to
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    })->name('send');
    
    // View Mail Log
    Route::get('/log', function() {
        if (config('mail.default') === 'log') {
            $log = Storage::get('logs/laravel.log');
            return response()->json([
                'status' => 'success',
                'log' => $log
            ]);
        }
        return response()->json([
            'message' => 'Mail driver is not set to log'
        ]);
    })->name('log');
});

// ============================================================
// QUEUE TESTING
// ============================================================
Route::prefix('queue')->name('queue.')->group(function () {
    // Queue Status
    Route::get('/', function() {
        return response()->json([
            'connection' => config('queue.default'),
            'driver' => config("queue.connections." . config('queue.default') . ".driver"),
            'queue' => config("queue.connections." . config('queue.default') . ".queue")
        ]);
    })->name('status');
    
    // Dispatch Test Job
    Route::post('/dispatch', function() {
        dispatch(function() {
            Log::info('Test job executed at ' . now());
        });
        
        return response()->json([
            'status' => 'success',
            'message' => 'Test job dispatched'
        ]);
    })->name('dispatch');
    
    // Process Queue
    Route::post('/work', function() {
        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--tries' => 1
        ]);
        
        return response()->json([
            'status' => 'success',
            'output' => Artisan::output()
        ]);
    })->name('work');
    
    // View Failed Jobs
    Route::get('/failed', function() {
        $failed = DB::table('failed_jobs')->get();
        return response()->json($failed);
    })->name('failed');
    
    // Retry Failed Jobs
    Route::post('/retry', function() {
        Artisan::call('queue:retry', ['id' => 'all']);
        
        return response()->json([
            'status' => 'success',
            'output' => Artisan::output()
        ]);
    })->name('retry');
});

// ============================================================
// ROUTE INFORMATION
// ============================================================
Route::prefix('routes')->name('routes.')->group(function () {
    // List All Routes
    Route::get('/', function() {
        $routes = collect(Route::getRoutes())->map(function ($route) {
            return [
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
                'methods' => $route->methods(),
                'middleware' => $route->middleware()
            ];
        });
        
        return response()->json($routes);
    })->name('list');
    
    // Search Routes
    Route::get('/search', function() {
        $query = request('q');
        $routes = collect(Route::getRoutes())->filter(function ($route) use ($query) {
            return str_contains($route->uri(), $query) || 
                   str_contains($route->getName() ?? '', $query);
        })->map(function ($route) {
            return [
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'methods' => $route->methods()
            ];
        });
        
        return response()->json($routes);
    })->name('search');
});

// ============================================================
// AUTHENTICATION TESTING
// ============================================================
Route::prefix('auth')->name('auth.')->group(function () {
    // Login as User
    Route::post('/login/{user}', function($userId) {
        if (app()->environment('local')) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                Auth::login($user);
                return response()->json([
                    'status' => 'success',
                    'user' => $user
                ]);
            }
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json(['error' => 'Only available in local environment'], 403);
    })->name('login');
    
    // Create Test User with Role
    Route::post('/create-user', function() {
        $role = request('role', 'student');
        $user = \App\Models\User::factory()->create([
            'email' => $role . '_' . time() . '@test.com',
            'password' => bcrypt('password')
        ]);
        $user->assignRole($role);
        
        if ($role === 'student') {
            \App\Models\Student::factory()->create(['user_id' => $user->id]);
        }
        
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'login_credentials' => [
                'email' => $user->email,
                'password' => 'password'
            ]
        ]);
    })->name('create-user');
});

// ============================================================
// LOGS VIEWER
// ============================================================
Route::prefix('logs')->name('logs.')->group(function () {
    // View Laravel Log
    Route::get('/', function() {
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $log = file_get_contents($logFile);
            return response($log, 200)->header('Content-Type', 'text/plain');
        }
        return response('Log file not found', 404);
    })->name('view');
    
    // Clear Logs
    Route::post('/clear', function() {
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
            return response()->json([
                'status' => 'success',
                'message' => 'Log file cleared'
            ]);
        }
        return response()->json(['error' => 'Log file not found'], 404);
    })->name('clear');
    
    // Download Logs
    Route::get('/download', function() {
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            return response()->download($logFile);
        }
        return response('Log file not found', 404);
    })->name('download');
});

// ============================================================
// PERFORMANCE TESTING
// ============================================================
Route::prefix('performance')->name('performance.')->group(function () {
    // Database Query Performance
    Route::get('/db', function() {
        $start = microtime(true);
        $count = \App\Models\User::count();
        $queryTime = microtime(true) - $start;
        
        return response()->json([
            'query_time' => $queryTime,
            'record_count' => $count,
            'queries_per_second' => 1 / $queryTime
        ]);
    })->name('db');
    
    // Cache Performance
    Route::get('/cache', function() {
        $iterations = 1000;
        $key = 'perf_test';
        
        // Write test
        $writeStart = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            Cache::put($key . $i, 'value' . $i, 60);
        }
        $writeTime = microtime(true) - $writeStart;
        
        // Read test
        $readStart = microtime(true);
        for ($i = 0; $i < $iterations; $i++) {
            Cache::get($key . $i);
        }
        $readTime = microtime(true) - $readStart;
        
        // Cleanup
        for ($i = 0; $i < $iterations; $i++) {
            Cache::forget($key . $i);
        }
        
        return response()->json([
            'iterations' => $iterations,
            'write_time' => $writeTime,
            'read_time' => $readTime,
            'writes_per_second' => $iterations / $writeTime,
            'reads_per_second' => $iterations / $readTime
        ]);
    })->name('cache');
    
    // Memory Usage
    Route::get('/memory', function() {
        return response()->json([
            'current_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB',
            'peak_usage' => memory_get_peak_usage(true) / 1024 / 1024 . ' MB',
            'limit' => ini_get('memory_limit')
        ]);
    })->name('memory');
});

// ============================================================
// API DOCUMENTATION
// ============================================================
Route::get('/api-docs', function() {
    return view('dev.api-docs');
})->name('api-docs');

// ============================================================
// PHPINFO
// ============================================================
Route::get('/phpinfo', function() {
    if (app()->environment('local')) {
        phpinfo();
    } else {
        return response('Only available in local environment', 403);
    }
})->name('phpinfo');

// ============================================================
// ARTISAN COMMAND RUNNER
// ============================================================
Route::prefix('artisan')->name('artisan.')->group(function () {
    Route::get('/', function() {
        return view('dev.artisan');
    })->name('index');
    
    Route::post('/run', function() {
        if (!app()->environment('local')) {
            return response()->json(['error' => 'Only available in local environment'], 403);
        }
        
        $command = request('command');
        $params = request('params', []);
        
        try {
            Artisan::call($command, $params);
            return response()->json([
                'status' => 'success',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    })->name('run');
});

// ============================================================
// TELESCOPE & HORIZON SHORTCUTS (if installed)
// ============================================================
Route::get('/telescope', function() {
    if (class_exists('Laravel\Telescope\Telescope')) {
        return redirect('/telescope');
    }
    return response('Laravel Telescope is not installed', 404);
})->name('telescope');

Route::get('/horizon', function() {
    if (class_exists('Laravel\Horizon\Horizon')) {
        return redirect('/horizon');
    }
    return response('Laravel Horizon is not installed', 404);
})->name('horizon');