<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GradeDeadlineController extends Controller
{
    /**
     * Display grade deadlines for all terms
     */
    public function index()
    {
        // Check admin permission
        if (!auth()->user()->hasRole(['admin', 'registrar'])) {
            abort(403, 'Unauthorized access');
        }

        $deadlines = DB::table('grade_deadlines as gd')
            ->join('academic_terms as at', 'gd.term_id', '=', 'at.id')
            ->select(
                'gd.*',
                'at.name as term_name',
                'at.code as term_code',
                'at.start_date',
                'at.end_date',
                'at.is_current'
            )
            ->orderBy('at.is_current', 'desc')
            ->orderBy('at.start_date', 'desc')
            ->get();

        $terms = AcademicTerm::orderBy('start_date', 'desc')->get();

        return view('admin.grades.deadlines', compact('deadlines', 'terms'));
    }

    /**
     * Store new grade deadline
     */
    public function store(Request $request)
    {
        // Check admin permission
        if (!auth()->user()->hasRole(['admin', 'registrar'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'term_id' => 'required|exists:academic_terms,id',
            'midterm_grade_deadline' => 'required|date',
            'final_grade_deadline' => 'required|date|after:midterm_grade_deadline',
            'grade_change_deadline' => 'required|date|after:final_grade_deadline',
            'incomplete_deadline' => 'required|date|after:final_grade_deadline',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            // Check if deadline already exists for this term
            $exists = DB::table('grade_deadlines')
                ->where('term_id', $validated['term_id'])
                ->exists();

            if ($exists) {
                return back()->with('error', 'Grade deadlines already exist for this term. Please update instead.');
            }

            // Create new deadline
            $deadlineId = DB::table('grade_deadlines')->insertGetId([
                'term_id' => $validated['term_id'],
                'midterm_grade_deadline' => $validated['midterm_grade_deadline'],
                'final_grade_deadline' => $validated['final_grade_deadline'],
                'grade_change_deadline' => $validated['grade_change_deadline'],
                'incomplete_deadline' => $validated['incomplete_deadline'],
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Log the action
            DB::table('audit_logs')->insert([
                'user_id' => auth()->id(),
                'action' => 'create_grade_deadline',
                'model_type' => 'GradeDeadline',
                'model_id' => $deadlineId,
                'changes' => json_encode($validated),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);

            DB::commit();

            return back()->with('success', 'Grade deadlines created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Grade deadline creation error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'Failed to create grade deadlines. Please try again.');
        }
    }

    /**
     * Update existing grade deadline
     */
    public function update(Request $request, $id)
    {
        // Check admin permission
        if (!auth()->user()->hasRole(['admin', 'registrar'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $deadline = DB::table('grade_deadlines')->find($id);
        if (!$deadline) {
            return back()->with('error', 'Grade deadline not found.');
        }

        $validated = $request->validate([
            'midterm_grade_deadline' => 'required|date',
            'final_grade_deadline' => 'required|date|after:midterm_grade_deadline',
            'grade_change_deadline' => 'required|date|after:final_grade_deadline',
            'incomplete_deadline' => 'required|date|after:final_grade_deadline',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            // Store old values for audit
            $oldValues = (array) $deadline;

            // Update deadline
            DB::table('grade_deadlines')
                ->where('id', $id)
                ->update([
                    'midterm_grade_deadline' => $validated['midterm_grade_deadline'],
                    'final_grade_deadline' => $validated['final_grade_deadline'],
                    'grade_change_deadline' => $validated['grade_change_deadline'],
                    'incomplete_deadline' => $validated['incomplete_deadline'],
                    'notes' => $validated['notes'],
                    'updated_by' => auth()->id(),
                    'updated_at' => now()
                ]);

            // Log the action
            DB::table('audit_logs')->insert([
                'user_id' => auth()->id(),
                'action' => 'update_grade_deadline',
                'model_type' => 'GradeDeadline',
                'model_id' => $id,
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode($validated),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);

            // Send notifications to faculty about deadline changes
            $this->notifyFacultyOfDeadlineChange($deadline->term_id, $validated);

            DB::commit();

            return back()->with('success', 'Grade deadlines updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Grade deadline update error', [
                'error' => $e->getMessage(),
                'deadline_id' => $id,
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'Failed to update grade deadlines.');
        }
    }

    /**
     * Check and enforce grade deadlines
     */
    public function checkDeadlines($termId = null)
    {
        if (!$termId) {
            $term = AcademicTerm::where('is_current', true)->first();
            $termId = $term ? $term->id : null;
        }

        if (!$termId) {
            return response()->json(['error' => 'No active term found'], 404);
        }

        $deadline = DB::table('grade_deadlines')->where('term_id', $termId)->first();
        
        if (!$deadline) {
            return response()->json(['error' => 'No deadlines set for this term'], 404);
        }

        $now = Carbon::now();
        $status = [];

        // Check midterm deadline
        $midtermDeadline = Carbon::parse($deadline->midterm_grade_deadline);
        $status['midterm'] = [
            'deadline' => $midtermDeadline->format('Y-m-d H:i:s'),
            'passed' => $now->gt($midtermDeadline),
            'days_remaining' => $now->lt($midtermDeadline) ? $now->diffInDays($midtermDeadline) : 0,
            'status' => $this->getDeadlineStatus($now, $midtermDeadline)
        ];

        // Check final grade deadline
        $finalDeadline = Carbon::parse($deadline->final_grade_deadline);
        $status['final'] = [
            'deadline' => $finalDeadline->format('Y-m-d H:i:s'),
            'passed' => $now->gt($finalDeadline),
            'days_remaining' => $now->lt($finalDeadline) ? $now->diffInDays($finalDeadline) : 0,
            'status' => $this->getDeadlineStatus($now, $finalDeadline)
        ];

        // Check grade change deadline
        $changeDeadline = Carbon::parse($deadline->grade_change_deadline);
        $status['change'] = [
            'deadline' => $changeDeadline->format('Y-m-d H:i:s'),
            'passed' => $now->gt($changeDeadline),
            'days_remaining' => $now->lt($changeDeadline) ? $now->diffInDays($changeDeadline) : 0,
            'status' => $this->getDeadlineStatus($now, $changeDeadline)
        ];

        // Get submission statistics
        $stats = $this->getSubmissionStatistics($termId);
        
        return response()->json([
            'deadlines' => $status,
            'statistics' => $stats
        ]);
    }

    /**
     * Get deadline status
     */
    private function getDeadlineStatus($now, $deadline)
    {
        if ($now->gt($deadline)) {
            return 'passed';
        } elseif ($now->diffInDays($deadline) <= 3) {
            return 'urgent';
        } elseif ($now->diffInDays($deadline) <= 7) {
            return 'approaching';
        } else {
            return 'upcoming';
        }
    }

    /**
     * Get grade submission statistics
     */
    private function getSubmissionStatistics($termId)
    {
        $totalSections = DB::table('course_sections')
            ->where('term_id', $termId)
            ->count();

        $midtermSubmitted = DB::table('grade_submissions')
            ->where('term_id', $termId)
            ->where('submission_type', 'midterm')
            ->where('status', 'approved')
            ->count();

        $finalSubmitted = DB::table('grade_submissions')
            ->where('term_id', $termId)
            ->where('submission_type', 'final')
            ->where('status', 'approved')
            ->count();

        return [
            'total_sections' => $totalSections,
            'midterm_submitted' => $midtermSubmitted,
            'midterm_percentage' => $totalSections > 0 ? round(($midtermSubmitted / $totalSections) * 100, 2) : 0,
            'final_submitted' => $finalSubmitted,
            'final_percentage' => $totalSections > 0 ? round(($finalSubmitted / $totalSections) * 100, 2) : 0
        ];
    }

    /**
     * Send reminder emails to faculty about upcoming deadlines
     */
    public function sendReminders(Request $request)
    {
        // Check admin permission
        if (!auth()->user()->hasRole(['admin', 'registrar'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'term_id' => 'required|exists:academic_terms,id',
            'deadline_type' => 'required|in:midterm,final,change'
        ]);

        $deadline = DB::table('grade_deadlines')->where('term_id', $validated['term_id'])->first();
        
        if (!$deadline) {
            return back()->with('error', 'No deadlines set for this term.');
        }

        // Get faculty with pending grades
        $pendingFaculty = $this->getFacultyWithPendingGrades($validated['term_id'], $validated['deadline_type']);

        $remindersSent = 0;
        foreach ($pendingFaculty as $faculty) {
            // Queue reminder email
            $this->sendReminderEmail($faculty, $deadline, $validated['deadline_type']);
            $remindersSent++;
        }

        return back()->with('success', "Sent {$remindersSent} reminder emails successfully.");
    }

    /**
     * Get faculty with pending grade submissions
     */
    private function getFacultyWithPendingGrades($termId, $deadlineType)
    {
        $query = DB::table('course_sections as cs')
            ->join('users as u', 'cs.instructor_id', '=', 'u.id')
            ->where('cs.term_id', $termId)
            ->select('u.id', 'u.email', 'u.name', DB::raw('COUNT(cs.id) as pending_sections'));

        if ($deadlineType === 'midterm') {
            $query->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('grade_submissions')
                    ->whereColumn('grade_submissions.section_id', 'cs.id')
                    ->where('submission_type', 'midterm')
                    ->where('status', 'approved');
            });
        } else {
            $query->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('grade_submissions')
                    ->whereColumn('grade_submissions.section_id', 'cs.id')
                    ->where('submission_type', 'final')
                    ->where('status', 'approved');
            });
        }

        return $query->groupBy('u.id', 'u.email', 'u.name')->get();
    }

    /**
     * Send reminder email to faculty
     */
    private function sendReminderEmail($faculty, $deadline, $deadlineType)
    {
        // This would integrate with your email service
        // For now, we'll just log it
        Log::info('Grade deadline reminder sent', [
            'faculty_id' => $faculty->id,
            'email' => $faculty->email,
            'deadline_type' => $deadlineType,
            'pending_sections' => $faculty->pending_sections
        ]);
    }

    /**
     * Notify faculty of deadline changes
     */
    private function notifyFacultyOfDeadlineChange($termId, $newDeadlines)
    {
        // Get all faculty teaching in this term
        $faculty = DB::table('course_sections')
            ->join('users', 'course_sections.instructor_id', '=', 'users.id')
            ->where('course_sections.term_id', $termId)
            ->select('users.id', 'users.email', 'users.name')
            ->distinct()
            ->get();

        foreach ($faculty as $member) {
            // Queue notification
            Log::info('Grade deadline change notification', [
                'faculty_id' => $member->id,
                'term_id' => $termId,
                'new_deadlines' => $newDeadlines
            ]);
        }
    }
}