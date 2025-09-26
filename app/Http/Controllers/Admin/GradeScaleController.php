<?php

// ============================================================
// Save this as: app/Http/Controllers/Admin/GradeScaleController.php
// ============================================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GradeScaleController extends Controller
{
    /**
     * Display all grade scales
     */
    public function index()
    {
        if (!auth()->user()->hasRole(['admin', 'registrar', 'academic-administrator'])) {
            abort(403, 'Unauthorized access');
        }

        $gradeScales = DB::table('grade_scales')
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($scale) {
                $scale->scale_values = json_decode($scale->scale_values, true);
                return $scale;
            });

        foreach ($gradeScales as $scale) {
            $scale->usage_count = DB::table('course_sections')
                ->where('grade_scale_id', $scale->id)
                ->count();
        }

        return view('admin.grades.scales', compact('gradeScales'));
    }

    /**
     * Store new grade scale
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasRole(['admin', 'registrar'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:grade_scales,name',
            'description' => 'nullable|string|max:500',
            'scale_type' => 'required|in:letter,percentage,points,custom',
            'scale_values' => 'required|array',
            'is_active' => 'boolean'
        ]);

        $validated['scale_values'] = json_encode($validated['scale_values']);
        $validated['created_by'] = auth()->id();
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        DB::table('grade_scales')->insert($validated);

        Cache::forget('grade_scales');

        return redirect()->route('admin.grades.scales')
            ->with('success', 'Grade scale created successfully');
    }

    /**
     * Update grade scale
     */
    public function update(Request $request, $scaleId)
    {
        if (!auth()->user()->hasRole(['admin', 'registrar'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'scale_values' => 'required|array',
            'is_active' => 'boolean'
        ]);

        $validated['scale_values'] = json_encode($validated['scale_values']);
        $validated['updated_at'] = now();

        DB::table('grade_scales')
            ->where('id', $scaleId)
            ->update($validated);

        Cache::forget('grade_scales');

        return redirect()->route('admin.grades.scales')
            ->with('success', 'Grade scale updated successfully');
    }
}