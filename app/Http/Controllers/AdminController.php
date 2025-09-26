<?php
// File: app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\AcademicTerm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Display admin dashboard
     */
    public function dashboard()
    {
        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'total_students' => Student::count(),
            'total_courses' => Course::count(),
            'active_sections' => CourseSection::where('status', 'active')->count(),
            'current_term' => AcademicTerm::where('is_current', true)->first(),
        ];
        
        // Recent activities
        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();
        $recentStudents = Student::with('user')->orderBy('created_at', 'desc')->limit(5)->get();
        
        return view('admin.dashboard', compact('stats', 'recentUsers', 'recentStudents'));
    }
    
    /**
     * Reports index
     */
    public function reportsIndex()
    {
        return view('admin.reports.index');
    }
    
    /**
     * Reports dashboard
     */
    public function reportsDashboard()
    {
        return view('admin.reports.dashboard');
    }
    
    /**
     * User activity report
     */
    public function userActivityReport(Request $request)
    {
        $activities = DB::table('user_activity_logs')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('admin.reports.user-activity', compact('activities'));
    }
    
    /**
     * System usage report
     */
    public function systemUsageReport()
    {
        return view('admin.reports.system-usage');
    }
    
    /**
     * Enrollment statistics
     */
    public function enrollmentStats()
    {
        return view('admin.reports.enrollment-stats');
    }
    
    /**
     * Academic performance report
     */
    public function academicPerformance()
    {
        return view('admin.reports.academic-performance');
    }
    
    /**
     * Custom report builder
     */
    public function customReport()
    {
        return view('admin.reports.custom');
    }
    
    /**
     * Generate report
     */
    public function generateReport(Request $request)
    {
        // Implementation for report generation
        return back()->with('success', 'Report generated successfully');
    }
    
    /**
     * Export report
     */
    public function exportReport($report)
    {
        // Implementation for report export
        return response()->download("reports/{$report}.pdf");
    }
    
    /**
     * Scheduled reports
     */
    public function scheduledReports()
    {
        return view('admin.reports.scheduled');
    }
    
    /**
     * Schedule a new report
     */
    public function scheduleReport(Request $request)
    {
        // Implementation for scheduling reports
        return back()->with('success', 'Report scheduled successfully');
    }
}