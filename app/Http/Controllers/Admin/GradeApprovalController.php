<?php
// Save this as: app/Http/Controllers/Admin/GradeApprovalController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\CourseSection;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GradeApprovalController extends Controller
{
    /**
     * Display grade approval dashboard
     */
    public function index()
    {
        // Check permission
        if (!auth()->user()->hasRole(['admin', 'registrar', 'department-head'])) {
            abort(403, 'Unauthorized access');
        }

        // Get sections with pending grade submissions
        $pendingSections = CourseSection::with(['course', 'instructor', 'term'])
            ->whereHas('grades', function($query) {
                $query->where('is_final', false)
                      ->where('status', 'submitted');
            })
            ->paginate(10);

        // Get recent approvals
        $recentApprovals = DB::table('grade_submissions')
            ->join('course_sections', 'grade_submissions.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->where('grade_submissions.status', 'approved')
            ->orderBy('grade_submissions.approved_at', 'desc')
            ->limit(5)
            ->select(
                'courses.code',
                'courses.name',
                'grade_submissions.approved_at',
                'grade_submissions.approved_by'
            )
            ->get();

        return view('admin.grades.approval', compact('pendingSections', 'recentApprovals'));
    }

    /**
     * Approve grade submission
     */
    public function approve($submissionId)
    {
        if (!auth()->user()->hasRole(['admin', 'registrar'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            // Update submission status
            DB::table('grade_submissions')
                ->where('id', $submissionId)
                ->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'updated_at' => now()
                ]);

            // Mark grades as final
            $submission = DB::table('grade_submissions')->find($submissionId);
            Grade::where('section_id', $submission->section_id)
                ->update([
                    'is_final' => true,
                    'status' => 'approved',
                    'updated_at' => now()
                ]);

            DB::commit();
            return redirect()->route('admin.grades.approval')
                ->with('success', 'Grades approved successfully');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Grade approval failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to approve grades');
        }
    }

    /**
     * Reject grade submission
     */
    public function reject(Request $request, $submissionId)
    {
        if (!auth()->user()->hasRole(['admin', 'registrar'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            DB::table('grade_submissions')
                ->where('id', $submissionId)
                ->update([
                    'status' => 'rejected',
                    'rejection_reason' => $request->reason,
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                    'updated_at' => now()
                ]);

            DB::commit();
            return redirect()->route('admin.grades.approval')
                ->with('success', 'Grade submission rejected');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to reject submission');
        }
    }

    /**
     * Display grade change requests
     */
    public function changeRequests()
    {
        $changeRequests = DB::table('grade_change_requests')
            ->join('enrollments', 'grade_change_requests.enrollment_id', '=', 'enrollments.id')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('course_sections', 'enrollments.section_id', '=', 'course_sections.id')
            ->join('courses', 'course_sections.course_id', '=', 'courses.id')
            ->where('grade_change_requests.status', 'pending')
            ->select(
                'grade_change_requests.*',
                'students.first_name',
                'students.last_name',
                'students.student_id',
                'courses.code',
                'courses.name as course_name'
            )
            ->orderBy('grade_change_requests.created_at', 'desc')
            ->paginate(10);

        return view('admin.grades.changes', compact('changeRequests'));
    }

    /**
     * Approve grade change request
     */
    public function approveChange($requestId)
    {
        if (!auth()->user()->hasRole(['admin', 'registrar'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            $changeRequest = DB::table('grade_change_requests')->find($requestId);
            
            // Update the grade
            Grade::where('enrollment_id', $changeRequest->enrollment_id)
                ->update([
                    'letter_grade' => $changeRequest->new_grade,
                    'grade_points' => $this->calculateGradePoints($changeRequest->new_grade),
                    'updated_at' => now()
                ]);

            // Update request status
            DB::table('grade_change_requests')
                ->where('id', $requestId)
                ->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'updated_at' => now()
                ]);

            DB::commit();
            return redirect()->route('admin.grades.changes')
                ->with('success', 'Grade change approved');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to approve grade change');
        }
    }

    /**
     * Reject grade change request
     */
    public function rejectChange(Request $request, $requestId)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        DB::table('grade_change_requests')
            ->where('id', $requestId)
            ->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'updated_at' => now()
            ]);

        return redirect()->route('admin.grades.changes')
            ->with('success', 'Grade change request rejected');
    }

    /**
     * Calculate grade points from letter grade
     */
    private function calculateGradePoints($letterGrade)
    {
        $gradePointMap = [
            'A' => 4.0, 'A-' => 3.7,
            'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
            'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
            'D+' => 1.3, 'D' => 1.0,
            'F' => 0.0
        ];

        return $gradePointMap[$letterGrade] ?? 0.0;
    }
}






