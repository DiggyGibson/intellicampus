<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Admin Student Helper Controller
 * Allows authorized staff to process transactions on behalf of students
 * Save as: app/Http/Controllers/Admin/AdminStudentHelperController.php
 */
class AdminStudentHelperController extends Controller
{
    /**
     * Show the student lookup interface
     */
    public function index()
    {
        // Check permission
        if (!auth()->user()->hasRole(['Super Administrator', 'Registrar', 'Financial Administrator', 'Student Services'])) {
            abort(403, 'Unauthorized to assist students');
        }

        // Get any currently impersonated student
        $impersonatingStudent = null;
        if (Session::has('impersonating_student_id')) {
            $impersonatingStudent = Student::find(Session::get('impersonating_student_id'));
        }

        return view('admin.student-helper', compact('impersonatingStudent'));
    }

    /**
     * Search for a student
     */
    public function searchStudent(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:2'
        ]);

        $search = $request->search;

        $students = Student::with('user')
            ->where('student_id', 'LIKE', "%{$search}%")
            ->orWhereHas('user', function($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get();

        return response()->json($students);
    }

    /**
     * Start assisting a student (impersonation with audit trail)
     */
    public function startAssisting(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'reason' => 'required|string|max:500'
        ]);

        // Check permission
        if (!auth()->user()->hasRole(['Super Administrator', 'Registrar', 'Financial Administrator', 'Student Services'])) {
            abort(403);
        }

        $student = Student::findOrFail($request->student_id);

        // Log the assistance session
        \DB::table('student_assistance_logs')->insert([
            'staff_id' => auth()->id(),
            'student_id' => $student->id,
            'reason' => $request->reason,
            'started_at' => now(),
            'ip_address' => $request->ip()
        ]);

        // Store in session
        Session::put('impersonating_student_id', $student->id);
        Session::put('impersonating_started_at', now());
        Session::put('original_user_id', auth()->id());
        Session::put('assistance_reason', $request->reason);

        return redirect()->route('admin.student-helper.dashboard')
                        ->with('success', "Now assisting {$student->user->name} ({$student->student_id})");
    }

    /**
     * Stop assisting student
     */
    public function stopAssisting()
    {
        if (Session::has('impersonating_student_id')) {
            // Log the end of assistance
            \DB::table('student_assistance_logs')
                ->where('staff_id', Session::get('original_user_id'))
                ->where('student_id', Session::get('impersonating_student_id'))
                ->whereNull('ended_at')
                ->update(['ended_at' => now()]);

            // Clear session
            Session::forget(['impersonating_student_id', 'impersonating_started_at', 'original_user_id', 'assistance_reason']);
        }

        return redirect()->route('admin.student-helper')
                        ->with('success', 'Stopped assisting student');
    }

    /**
     * Dashboard for assisting student
     */
    public function dashboard()
    {
        if (!Session::has('impersonating_student_id')) {
            return redirect()->route('admin.student-helper')
                            ->with('error', 'No student selected');
        }

        $student = Student::with(['user', 'program'])->findOrFail(Session::get('impersonating_student_id'));
        
        // Available actions for the staff member
        $availableActions = $this->getAvailableActions();

        return view('admin.student-helper-dashboard', compact('student', 'availableActions'));
    }

    /**
     * Process payment on behalf of student
     */
    public function processPayment(Request $request)
    {
        if (!Session::has('impersonating_student_id')) {
            abort(403);
        }

        $student = Student::findOrFail(Session::get('impersonating_student_id'));

        // Log the action
        $this->logAction('process_payment', [
            'amount' => $request->amount,
            'method' => $request->payment_method
        ]);

        // Process the payment (reuse existing logic)
        $payment = \App\Models\Payment::create([
            'student_id' => $student->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'reference_number' => $request->reference_number,
            'payment_date' => now(),
            'status' => 'completed',
            'processed_by' => Session::get('original_user_id'),
            'notes' => 'Processed by staff: ' . auth()->user()->name . '. Reason: ' . Session::get('assistance_reason')
        ]);

        return back()->with('success', 'Payment processed successfully');
    }

    /**
     * Register courses on behalf of student
     */
    public function registerCourses(Request $request)
    {
        if (!Session::has('impersonating_student_id')) {
            abort(403);
        }

        $student = Student::findOrFail(Session::get('impersonating_student_id'));

        // Log the action
        $this->logAction('register_courses', [
            'sections' => $request->section_ids
        ]);

        // Process registration (reuse existing logic)
        foreach ($request->section_ids as $sectionId) {
            \App\Models\Enrollment::create([
                'student_id' => $student->id,
                'section_id' => $sectionId,
                'enrollment_status' => 'enrolled',
                'enrolled_by' => Session::get('original_user_id'),
                'notes' => 'Enrolled by staff: ' . auth()->user()->name
            ]);
        }

        return back()->with('success', 'Courses registered successfully');
    }

    /**
     * Get available actions based on user role
     */
    private function getAvailableActions()
    {
        $user = auth()->user();
        $actions = [];

        if ($user->hasRole(['Super Administrator', 'Financial Administrator'])) {
            $actions[] = ['name' => 'Process Payment', 'route' => 'admin.student-helper.payment', 'icon' => 'fas fa-credit-card'];
            $actions[] = ['name' => 'View Financial Account', 'route' => 'financial.student-dashboard', 'icon' => 'fas fa-wallet'];
            $actions[] = ['name' => 'Generate Invoice', 'route' => 'admin.student-helper.invoice', 'icon' => 'fas fa-file-invoice'];
        }

        if ($user->hasRole(['Super Administrator', 'Registrar'])) {
            $actions[] = ['name' => 'Register Courses', 'route' => 'admin.student-helper.register', 'icon' => 'fas fa-book'];
            $actions[] = ['name' => 'Drop/Add Courses', 'route' => 'admin.student-helper.drop-add', 'icon' => 'fas fa-exchange-alt'];
            $actions[] = ['name' => 'View Schedule', 'route' => 'registration.schedule', 'icon' => 'fas fa-calendar'];
        }

        if ($user->hasRole(['Super Administrator', 'Academic Administrator'])) {
            $actions[] = ['name' => 'View Grades', 'route' => 'student.grades', 'icon' => 'fas fa-graduation-cap'];
            $actions[] = ['name' => 'Generate Transcript', 'route' => 'transcripts.show', 'icon' => 'fas fa-file-alt'];
        }

        return $actions;
    }

    /**
     * Log staff action on behalf of student
     */
    private function logAction($action, $details = [])
    {
        \DB::table('student_assistance_actions')->insert([
            'staff_id' => Session::get('original_user_id'),
            'student_id' => Session::get('impersonating_student_id'),
            'action' => $action,
            'details' => json_encode($details),
            'created_at' => now()
        ]);
    }
}