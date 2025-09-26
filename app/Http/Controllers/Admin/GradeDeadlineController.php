<?php

// ============================================================
// Save this as: app/Http/Controllers/Admin/GradeDeadlineController.php
// ============================================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GradeDeadlineController extends Controller
{
    /**
     * Display grade deadlines
     */
    public function index()
    {
        if (!auth()->user()->hasRole(['admin', 'registrar', 'academic-administrator'])) {
            abort(403, 'Unauthorized access');
        }

        $deadlines = DB::table('grade_deadlines')
            ->join('academic_terms', 'grade_deadlines.term_id', '=', 'academic_terms.id')
            ->select(
                'grade_deadlines.*',
                'academic_terms.name as term_name',
                'academic_terms.code as term_code'
            )
            ->orderBy('grade_deadlines.deadline_date', 'desc')
            ->get();

        $terms = DB::table('academic_terms')
            ->where('end_date', '>=', now())
            ->orderBy('start_date')
            ->get();

        return view('admin.grades.deadlines', compact('deadlines', 'terms'));
    }

    /**
     * Store new deadline
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole(['admin', 'registrar'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'term_id' => 'required|exists:academic_terms,id',
            'deadline_type' => 'required|in:midterm,final,grade_change,incomplete',
            'deadline_date' => 'required|date|after:today',
            'description' => 'nullable|string|max:500',
            'send_reminder' => 'boolean',
            'reminder_days_before' => 'nullable|integer|min:1|max:30'
        ]);

        $validated['created_by'] = auth()->id();
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        DB::table('grade_deadlines')->insert($validated);

        return redirect()->route('admin.grades.deadlines')
            ->with('success', 'Deadline created successfully');
    }

    /**
     * Update deadline
     */
    public function update(Request $request, $deadlineId)
    {
        if (!auth()->user()->hasRole(['admin', 'registrar'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'deadline_date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'send_reminder' => 'boolean',
            'reminder_days_before' => 'nullable|integer|min:1|max:30'
        ]);

        $validated['updated_at'] = now();

        DB::table('grade_deadlines')
            ->where('id', $deadlineId)
            ->update($validated);

        return redirect()->route('admin.grades.deadlines')
            ->with('success', 'Deadline updated successfully');
    }
}
