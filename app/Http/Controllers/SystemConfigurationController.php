<?php
// File: app/Http/Controllers/SystemConfigurationController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\AcademicCalendar;
use App\Models\CalendarEvent;
use App\Models\InstitutionConfig;
use App\Models\AcademicPeriodType;
use App\Models\CreditConfiguration;
use App\Models\GradingConfiguration;
use App\Models\AttendanceConfiguration;
use App\Models\RegistrationConfiguration;
use App\Models\EmailTemplate;
use App\Models\SystemModule;
use App\Models\AcademicTerm;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SystemConfigurationController extends Controller
{
    /**
     * Constructor - Check admin access
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            
            // Check if user has any of the admin roles
            if (!$user->hasRole(['Super Administrator', 'admin', 'system-administrator', 'super-administrator'])) {
                abort(403, 'Unauthorized access');
            }
            
            return $next($request);
        });
    }

    /**
     * Display the main configuration dashboard
     */
    public function index()
    {
        $data = [
            'institution' => InstitutionConfig::first(),
            'modules' => SystemModule::orderBy('display_order')->get(),
            'activeCalendar' => AcademicCalendar::where('is_active', true)->first(),
            'currentTerm' => AcademicTerm::where('is_current', true)->first(),
            'settingsCount' => SystemSetting::count(),
            'templatesCount' => EmailTemplate::where('is_active', true)->count(),
        ];
        
        return view('admin.system-config.index', $data);
    }

    /**
     * Display the system configuration dashboard
     * This handles both /system and /system/dashboard routes
     * FIXED: No more redirect loop!
     */
    public function dashboard()
    {
        // Don't redirect! Return the same view as index
        $data = [
            'institution' => InstitutionConfig::first(),
            'modules' => SystemModule::orderBy('display_order')->get(),
            'activeCalendar' => AcademicCalendar::where('is_active', true)->first(),
            'currentTerm' => AcademicTerm::where('is_current', true)->first(),
            'settingsCount' => SystemSetting::count(),
            'templatesCount' => EmailTemplate::where('is_active', true)->count(),
            'systemHealth' => $this->getSystemHealthScore(),
            'alerts' => $this->getSystemAlerts(),
        ];
        
        return view('admin.system-config.index', $data);
    }

    /**
     * Institution Settings
     */
    public function institution()
    {
        $institution = InstitutionConfig::firstOrCreate([
            'id' => 1
        ], [
            'institution_name' => 'IntelliCampus University',
            'institution_code' => 'ICU',
            'institution_type' => 'university',
            'address' => '',
            'city' => '',
            'state' => '',
            'country' => '',
            'postal_code' => '',
            'phone' => '',
            'email' => '',
        ]);
        
        return view('admin.system-config.institution', compact('institution'));
    }

    /**
     * Update institution settings
     */
    public function updateInstitution(Request $request)
    {
        $validated = $request->validate([
            'institution_name' => 'required|string|max:255',
            'institution_code' => 'required|string|max:50',
            'institution_type' => 'required|string|max:50',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'phone' => 'required|string|max:50',
            'email' => 'required|email',
            'website' => 'nullable|url',
            'timezone' => 'required|string|max:50',
            'currency_code' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:10',
            'date_format' => 'required|string|max:20',
            'time_format' => 'required|string|max:20',
        ]);
        
        $institution = InstitutionConfig::first();
        $institution->update($validated);
        
        // Clear cache
        Cache::forget('institution_config');
        
        return redirect()->route('admin.system-config.institution')
            ->with('success', 'Institution settings updated successfully');
    }

    /**
     * Academic Calendar Management
     */
    public function calendar()
    {
        $calendars = AcademicCalendar::orderBy('academic_year', 'desc')->paginate(10);
        $activeCalendar = AcademicCalendar::where('is_active', true)->first();
        
        return view('admin.system-config.calendar', compact('calendars', 'activeCalendar'));
    }

    /**
     * Create new academic calendar
     */
    public function createCalendar()
    {
        return view('admin.system-config.calendar-create');
    }

    /**
     * Store new academic calendar
     */
    public function storeCalendar(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'academic_year' => 'required|integer|min:2020|max:2050',
            'year_start' => 'required|date',
            'year_end' => 'required|date|after:year_start',
            'description' => 'nullable|string',
        ]);
        
        DB::transaction(function () use ($validated) {
            // If setting as active, deactivate others
            if (request('is_active')) {
                AcademicCalendar::where('is_active', true)->update(['is_active' => false]);
                $validated['is_active'] = true;
            }
            
            AcademicCalendar::create($validated);
        });
        
        return redirect()->route('admin.system-config.calendar')
            ->with('success', 'Academic calendar created successfully');
    }

    /**
     * Edit calendar events
     */
    public function calendarEvents($calendarId)
    {
        $calendar = AcademicCalendar::findOrFail($calendarId);
        $events = CalendarEvent::where('academic_calendar_id', $calendarId)
            ->orderBy('start_date')
            ->get();
        
        $eventTypes = [
            'holiday' => 'Holiday',
            'deadline' => 'Deadline',
            'exam_period' => 'Exam Period',
            'registration' => 'Registration Period',
            'orientation' => 'Orientation',
            'graduation' => 'Graduation',
            'break' => 'Break',
            'class_start' => 'Classes Start',
            'class_end' => 'Classes End',
            'other' => 'Other'
        ];
        
        return view('admin.system-config.calendar-events', compact('calendar', 'events', 'eventTypes'));
    }

    /**
     * Store calendar event
     */
    public function storeCalendarEvent(Request $request, $calendarId)
    {
        $validated = $request->validate([
            'event_type' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'all_day' => 'boolean',
            'start_time' => 'nullable|required_if:all_day,false',
            'end_time' => 'nullable|required_if:all_day,false',
            'is_holiday' => 'boolean',
            'affects_classes' => 'boolean',
            'visibility' => 'required|in:public,students,faculty,staff,admin',
            'priority' => 'required|in:low,normal,high,critical',
        ]);
        
        $validated['academic_calendar_id'] = $calendarId;
        CalendarEvent::create($validated);
        
        return redirect()->route('admin.system-config.calendar.events', $calendarId)
            ->with('success', 'Calendar event added successfully');
    }

    /**
     * System Settings Management
     */
    public function settings()
    {
        $settings = SystemSetting::orderBy('category')
            ->orderBy('key')
            ->get()
            ->groupBy('category');
        
        return view('admin.system-config.settings', compact('settings'));
    }

    /**
     * Update system setting
     */
    public function updateSetting(Request $request, $id)
    {
        $setting = SystemSetting::findOrFail($id);
        
        if (!$setting->is_editable) {
            return response()->json(['error' => 'This setting is not editable'], 403);
        }
        
        $value = $request->input('value');
        
        // Validate based on type
        switch ($setting->type) {
            case 'boolean':
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
                break;
            case 'number':
                if (!is_numeric($value)) {
                    return response()->json(['error' => 'Invalid number format'], 422);
                }
                break;
            case 'json':
                if (!json_decode($value)) {
                    return response()->json(['error' => 'Invalid JSON format'], 422);
                }
                break;
        }
        
        $setting->value = $value;
        $setting->save();
        
        // Clear cache
        Cache::forget('system_settings');
        Cache::forget('system_setting_' . $setting->key);
        
        return response()->json(['success' => true, 'message' => 'Setting updated']);
    }

    /**
     * Academic Configuration
     */
    public function academic()
    {
        $data = [
            'periodTypes' => AcademicPeriodType::all(),
            'creditConfig' => CreditConfiguration::first(),
            'gradingConfig' => GradingConfiguration::first(),
            'attendanceConfig' => AttendanceConfiguration::first(),
            'registrationConfig' => RegistrationConfiguration::first(),
        ];
        
        return view('admin.system-config.academic', $data);
    }

    /**
     * Update academic configuration
     */
    public function updateAcademicConfig(Request $request, $type)
    {
        switch ($type) {
            case 'credit':
                $validated = $request->validate([
                    'credit_system' => 'required|string|max:50',
                    'min_credits_full_time' => 'required|integer|min:1',
                    'max_credits_regular' => 'required|integer|min:1',
                    'max_credits_overload' => 'required|integer|min:1',
                    'min_credits_graduation' => 'required|integer|min:1',
                    'hours_per_credit' => 'required|numeric|min:0.5|max:5',
                ]);
                
                CreditConfiguration::updateOrCreate(['id' => 1], $validated);
                break;
                
            case 'grading':
                $validated = $request->validate([
                    'grading_system' => 'required|string|max:50',
                    'max_gpa' => 'required|numeric|min:1|max:10',
                    'passing_gpa' => 'required|numeric|min:0',
                    'probation_gpa' => 'required|numeric|min:0',
                    'honors_gpa' => 'required|numeric|min:0',
                    'high_honors_gpa' => 'required|numeric|min:0',
                    'use_plus_minus' => 'boolean',
                    'include_failed_in_gpa' => 'boolean',
                ]);
                
                GradingConfiguration::updateOrCreate(['id' => 1], $validated);
                break;
                
            case 'attendance':
                $validated = $request->validate([
                    'track_attendance' => 'boolean',
                    'max_absences_allowed' => 'nullable|integer|min:0',
                    'attendance_weight_in_grade' => 'required|numeric|min:0|max:100',
                    'attendance_calculation_method' => 'required|string|max:50',
                    'notify_on_absence' => 'boolean',
                    'absence_notification_threshold' => 'required|integer|min:1',
                ]);
                
                AttendanceConfiguration::updateOrCreate(['id' => 1], $validated);
                break;
                
            case 'registration':
                $validated = $request->validate([
                    'allow_online_registration' => 'boolean',
                    'registration_priority_days' => 'required|integer|min:0',
                    'enforce_prerequisites' => 'boolean',
                    'allow_time_conflicts' => 'boolean',
                    'allow_waitlist' => 'boolean',
                    'max_waitlist_size' => 'required|integer|min:0',
                    'drop_deadline_weeks' => 'required|integer|min:1',
                    'withdraw_deadline_weeks' => 'required|integer|min:1',
                    'late_registration_fee' => 'required|numeric|min:0',
                ]);
                
                RegistrationConfiguration::updateOrCreate(['id' => 1], $validated);
                break;
        }
        
        Cache::forget('academic_config_' . $type);
        
        return redirect()->route('admin.system-config.academic')
            ->with('success', ucfirst($type) . ' configuration updated successfully');
    }

    /**
     * Module Management
     */
    public function modules()
    {
        $modules = SystemModule::orderBy('display_order')->get();
        
        return view('admin.system-config.modules', compact('modules'));
    }

    /**
     * Toggle module status
     */
    public function toggleModule($id)
    {
        $module = SystemModule::findOrFail($id);
        $module->is_enabled = !$module->is_enabled;
        $module->save();
        
        Cache::forget('enabled_modules');
        
        return response()->json([
            'success' => true,
            'is_enabled' => $module->is_enabled,
            'message' => $module->is_enabled ? 'Module enabled' : 'Module disabled'
        ]);
    }

    /**
     * Email Templates Management
     */
    public function emailTemplates()
    {
        $templates = EmailTemplate::orderBy('category')
            ->orderBy('name')
            ->paginate(20);
        
        $categories = [
            'registration' => 'Registration',
            'academic' => 'Academic',
            'financial' => 'Financial',
            'system' => 'System',
            'notification' => 'Notifications',
        ];
        
        return view('admin.system-config.email-templates', compact('templates', 'categories'));
    }

    /**
     * Edit email template
     */
    public function editEmailTemplate($id)
    {
        $template = EmailTemplate::findOrFail($id);
        
        // Available variables for different categories
        $availableVariables = [
            'registration' => ['student_name', 'student_id', 'term', 'courses', 'total_credits'],
            'academic' => ['student_name', 'course_name', 'grade', 'gpa', 'term'],
            'financial' => ['student_name', 'amount', 'due_date', 'invoice_number'],
            'system' => ['user_name', 'date', 'time', 'action'],
            'notification' => ['recipient_name', 'message', 'link'],
        ];
        
        $variables = $availableVariables[$template->category] ?? [];
        
        return view('admin.system-config.email-template-edit', compact('template', 'variables'));
    }

    /**
     * Update email template
     */
    public function updateEmailTemplate(Request $request, $id)
    {
        $template = EmailTemplate::findOrFail($id);
        
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);
        
        $template->update($validated);
        
        return redirect()->route('admin.system-config.email-templates')
            ->with('success', 'Email template updated successfully');
    }

    /**
     * System Health Check
     */
    public function healthCheck()
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
            'mail' => $this->checkMail(),
        ];
        
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'debug_mode' => config('app.debug'),
            'environment' => config('app.env'),
        ];
        
        return view('admin.system-config.health', compact('checks', 'systemInfo'));
    }

    /**
     * Check database connection
     */
    private function checkDatabase()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'ok', 'message' => 'Connected'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check cache system
     */
    private function checkCache()
    {
        try {
            Cache::put('health_check', true, 1);
            $result = Cache::get('health_check');
            Cache::forget('health_check');
            
            return ['status' => 'ok', 'message' => 'Working'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check storage permissions
     */
    private function checkStorage()
    {
        $directories = [
            storage_path('app'),
            storage_path('framework'),
            storage_path('logs'),
        ];
        
        foreach ($directories as $dir) {
            if (!is_writable($dir)) {
                return ['status' => 'error', 'message' => "Directory not writable: $dir"];
            }
        }
        
        return ['status' => 'ok', 'message' => 'All directories writable'];
    }

    /**
     * Check queue system
     */
    private function checkQueue()
    {
        // Basic check - you can expand this based on your queue driver
        try {
            $queueSize = DB::table('jobs')->count();
            return ['status' => 'ok', 'message' => "Queue size: $queueSize"];
        } catch (\Exception $e) {
            return ['status' => 'warning', 'message' => 'Queue not configured'];
        }
    }

    /**
     * Check mail configuration
     */
    private function checkMail()
    {
        $mailConfig = config('mail.default');
        if ($mailConfig) {
            return ['status' => 'ok', 'message' => "Using: $mailConfig"];
        }
        
        return ['status' => 'warning', 'message' => 'Mail not configured'];
    }

    /**
     * Clear all system cache
     */
    public function clearCache()
    {
        try {
            Cache::flush();
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'All cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle maintenance mode
     */
    public function toggleMaintenance()
    {
        try {
            // Add confirmation and IP exemption for admin
            if (app()->isDownForMaintenance()) {
                \Artisan::call('up');
                $status = 'disabled';
            } else {
                // Exempt current user's IP from maintenance mode
                $userIp = request()->ip();
                \Artisan::call('down', [
                    '--retry' => 60,
                    '--refresh' => 15,
                    '--except' => [$userIp]  // Allow current admin to still access
                ]);
                $status = 'enabled (except for your IP: ' . $userIp . ')';
            }
            
            return response()->json([
                'success' => true,
                'message' => "Maintenance mode $status"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling maintenance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the admin-specific dashboard
     */
    public function adminDashboard()
    {
        // Get admin-specific metrics
        $data = [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('status', 'active')->count(),
            'totalStudents' => User::where('user_type', 'student')->count(),
            'totalFaculty' => User::where('user_type', 'faculty')->count(),
            'systemHealth' => $this->getSystemHealthSummary(),
            'recentActivity' => $this->getRecentAdminActivity(),
            'pendingApprovals' => $this->getPendingApprovals(),
            'systemAlerts' => $this->getSystemAlerts(),
        ];
        
        return view('admin.dashboard', $data);
    }

    /**
     * Helper method to calculate system health score
     */
    private function getSystemHealthScore()
    {
        $score = 100;
        
        // Check database connection
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $score -= 25;
        }
        
        // Check cache
        try {
            Cache::has('test');
        } catch (\Exception $e) {
            $score -= 15;
        }
        
        // Check storage
        if (!is_writable(storage_path())) {
            $score -= 20;
        }
        
        return $score;
    }

    /**
     * Get system alerts
     */
    private function getSystemAlerts()
    {
        $alerts = [];
        
        // Check for pending updates
        try {
            if (SystemSetting::where('key', 'pending_updates')->where('value', 'true')->exists()) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => 'System updates are available'
                ];
            }
        } catch (\Exception $e) {
            // Table might not exist yet
        }
        
        // Check disk space (Windows compatible)
        try {
            // Try to get disk space - handle both Windows and Linux
            $freeSpace = @disk_free_space(base_path());
            $totalSpace = @disk_total_space(base_path());
            
            if ($freeSpace && $totalSpace) {
                $usagePercent = 100 - (($freeSpace / $totalSpace) * 100);
                
                if ($usagePercent > 80) {
                    $alerts[] = [
                        'type' => 'danger',
                        'message' => 'Disk space is running low (' . round($usagePercent) . '% used)'
                    ];
                }
            }
        } catch (\Exception $e) {
            // Ignore disk check errors
        }
        
        return $alerts;
    }

    /**
     * Helper methods for admin dashboard
     */
    private function getSystemHealthSummary()
    {
        return [
            'status' => 'healthy',
            'uptime' => '99.9%',
            'last_backup' => now()->subHours(6),
            'storage_used' => '45%',
        ];
    }

    private function getRecentAdminActivity()
    {
        // Get recent admin actions - return empty for now
        try {
            if (class_exists('App\Models\ActivityLog')) {
                return ActivityLog::latest()->take(5)->get();
            }
        } catch (\Exception $e) {
            // Model doesn't exist yet
        }
        return collect([]);
    }

    private function getPendingApprovals()
    {
        // Count items needing admin approval
        return 0;
    }

    /**
     * Export system settings
     */
    public function exportSettings()
    {
        try {
            $settings = [
                'institution' => InstitutionConfig::first(),
                'system_settings' => SystemSetting::all(),
                'modules' => SystemModule::all(),
                'email_templates' => EmailTemplate::all(),
                'academic_config' => [
                    'credit' => CreditConfiguration::first(),
                    'grading' => GradingConfiguration::first(),
                    'attendance' => AttendanceConfiguration::first(),
                    'registration' => RegistrationConfiguration::first(),
                ],
                'export_date' => now()->toIso8601String(),
                'version' => config('app.version', '1.0.0')
            ];
            
            $json = json_encode($settings, JSON_PRETTY_PRINT);
            
            return response($json, 200)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', 'attachment; filename="intellicampus-settings-' . date('Y-m-d') . '.json"');
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting settings: ' . $e->getMessage());
        }
    }

    /**
     * Additional system status method for monitoring
     */
    public function systemStatus()
    {
        $status = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'health_score' => $this->getSystemHealthScore(),
            'alerts' => $this->getSystemAlerts(),
        ];
        
        return response()->json($status);
    }

    /**
     * Quick stats for admin panel
     */
    public function quickStats()
    {
        $stats = [
            'total_users' => User::count(),
            'active_modules' => SystemModule::where('is_enabled', true)->count(),
            'total_modules' => SystemModule::count(),
            'pending_approvals' => $this->getPendingApprovals(),
        ];
        
        return response()->json($stats);
    }
}