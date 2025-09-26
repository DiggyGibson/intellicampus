<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GradeScaleController extends Controller
{
    /**
     * Display all grade scales
     */
    public function index()
    {
        // Check admin permission
        if (!auth()->user()->hasRole(['admin', 'registrar', 'academic_admin'])) {
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

        // Get usage statistics for each scale
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
        // Check admin permission
        if (!auth()->user()->hasRole(['admin', 'registrar', 'academic_admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:grade_scales,name',
            'description' => 'nullable|string|max:500',
            'scale_type' => 'required|in:letter,percentage,points,custom',
            'scale_values' => 'required|array',
            'scale_values.*.grade' => 'required|string|max:5',
            'scale_values.*.min' => 'required|numeric|min:0|max:100',
            'scale_values.*.max' => 'required|numeric|min:0|max:100',
            'scale_values.*.points' => 'required|numeric|min:0|max:4',
            'is_active' => 'boolean',
            'is_default' => 'boolean'
        ]);

        // Validate scale values logic
        $validationError = $this->validateScaleValues($validated['scale_values']);
        if ($validationError) {
            return back()->withErrors(['scale_values' => $validationError])->withInput();
        }

        DB::beginTransaction();
        try {
            // If setting as default, unset other defaults
            if ($request->input('is_default', false)) {
                DB::table('grade_scales')->update(['is_default' => false]);
            }

            // Create the grade scale
            $scaleId = DB::table('grade_scales')->insertGetId([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'scale_type' => $validated['scale_type'],
                'scale_values' => json_encode($this->formatScaleValues($validated['scale_values'])),
                'is_active' => $validated['is_active'] ?? true,
                'is_default' => $validated['is_default'] ?? false,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Clear cache
            Cache::forget('grade_scales');
            Cache::forget('default_grade_scale');

            // Log the action
            DB::table('audit_logs')->insert([
                'user_id' => auth()->id(),
                'action' => 'create_grade_scale',
                'model_type' => 'GradeScale',
                'model_id' => $scaleId,
                'changes' => json_encode($validated),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);

            DB::commit();

            return back()->with('success', 'Grade scale created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Grade scale creation error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'Failed to create grade scale. Please try again.');
        }
    }

    /**
     * Update existing grade scale
     */
    public function update(Request $request, $id)
    {
        // Check admin permission
        if (!auth()->user()->hasRole(['admin', 'registrar', 'academic_admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $scale = DB::table('grade_scales')->find($id);
        if (!$scale) {
            return back()->with('error', 'Grade scale not found.');
        }

        // Check if scale is in use
        $inUse = DB::table('course_sections')->where('grade_scale_id', $id)->exists();
        if ($inUse && $request->has('scale_values')) {
            return back()->with('error', 'Cannot modify scale values while in use by active courses.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:grade_scales,name,' . $id,
            'description' => 'nullable|string|max:500',
            'scale_values' => 'sometimes|required|array',
            'scale_values.*.grade' => 'required_with:scale_values|string|max:5',
            'scale_values.*.min' => 'required_with:scale_values|numeric|min:0|max:100',
            'scale_values.*.max' => 'required_with:scale_values|numeric|min:0|max:100',
            'scale_values.*.points' => 'required_with:scale_values|numeric|min:0|max:4',
            'is_active' => 'boolean',
            'is_default' => 'boolean'
        ]);

        // Validate scale values if provided
        if (isset($validated['scale_values'])) {
            $validationError = $this->validateScaleValues($validated['scale_values']);
            if ($validationError) {
                return back()->withErrors(['scale_values' => $validationError])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // Store old values for audit
            $oldValues = (array) $scale;

            // If setting as default, unset other defaults
            if ($request->input('is_default', false)) {
                DB::table('grade_scales')->where('id', '!=', $id)->update(['is_default' => false]);
            }

            // Prepare update data
            $updateData = [
                'name' => $validated['name'],
                'description' => $validated['description'],
                'is_active' => $validated['is_active'] ?? $scale->is_active,
                'is_default' => $validated['is_default'] ?? $scale->is_default,
                'updated_by' => auth()->id(),
                'updated_at' => now()
            ];

            // Only update scale values if provided and not in use
            if (isset($validated['scale_values']) && !$inUse) {
                $updateData['scale_values'] = json_encode($this->formatScaleValues($validated['scale_values']));
            }

            // Update the grade scale
            DB::table('grade_scales')->where('id', $id)->update($updateData);

            // Clear cache
            Cache::forget('grade_scales');
            Cache::forget('default_grade_scale');
            Cache::forget("grade_scale_{$id}");

            // Log the action
            DB::table('audit_logs')->insert([
                'user_id' => auth()->id(),
                'action' => 'update_grade_scale',
                'model_type' => 'GradeScale',
                'model_id' => $id,
                'old_values' => json_encode($oldValues),
                'new_values' => json_encode($updateData),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now()
            ]);

            DB::commit();

            return back()->with('success', 'Grade scale updated successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Grade scale update error', [
                'error' => $e->getMessage(),
                'scale_id' => $id,
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'Failed to update grade scale.');
        }
    }

    /**
     * Delete grade scale
     */
    public function destroy($id)
    {
        // Check admin permission
        if (!auth()->user()->hasRole(['admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $scale = DB::table('grade_scales')->find($id);
        if (!$scale) {
            return back()->with('error', 'Grade scale not found.');
        }

        // Check if scale is in use
        $inUse = DB::table('course_sections')->where('grade_scale_id', $id)->exists();
        if ($inUse) {
            return back()->with('error', 'Cannot delete grade scale that is currently in use.');
        }

        // Check if it's the default scale
        if ($scale->is_default) {
            return back()->with('error', 'Cannot delete the default grade scale.');
        }

        DB::beginTransaction();
        try {
            // Delete the grade scale
            DB::table('grade_scales')->where('id', $id)->delete();

            // Clear cache
            Cache::forget('grade_scales');
            Cache::forget("grade_scale_{$id}");

            // Log the action
            DB::table('audit_logs')->insert([
                'user_id' => auth()->id(),
                'action' => 'delete_grade_scale',
                'model_type' => 'GradeScale',
                'model_id' => $id,
                'old_values' => json_encode($scale),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);

            DB::commit();

            return back()->with('success', 'Grade scale deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Grade scale deletion error', [
                'error' => $e->getMessage(),
                'scale_id' => $id,
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'Failed to delete grade scale.');
        }
    }

    /**
     * Get grade scale details (AJAX)
     */
    public function show($id)
    {
        $scale = Cache::remember("grade_scale_{$id}", 3600, function () use ($id) {
            $scale = DB::table('grade_scales')->find($id);
            if ($scale) {
                $scale->scale_values = json_decode($scale->scale_values, true);
            }
            return $scale;
        });

        if (!$scale) {
            return response()->json(['error' => 'Grade scale not found'], 404);
        }

        return response()->json($scale);
    }

    /**
     * Convert grades from one scale to another
     */
    public function convert(Request $request)
    {
        $validated = $request->validate([
            'from_scale_id' => 'required|exists:grade_scales,id',
            'to_scale_id' => 'required|exists:grade_scales,id',
            'grade' => 'required|string',
            'percentage' => 'nullable|numeric|min:0|max:100'
        ]);

        $fromScale = $this->getScale($validated['from_scale_id']);
        $toScale = $this->getScale($validated['to_scale_id']);

        // Convert grade to percentage first
        $percentage = $validated['percentage'] ?? $this->gradeToPercentage($validated['grade'], $fromScale);
        
        // Convert percentage to new scale
        $newGrade = $this->percentageToGrade($percentage, $toScale);

        return response()->json([
            'original_grade' => $validated['grade'],
            'percentage' => $percentage,
            'converted_grade' => $newGrade,
            'from_scale' => $fromScale->name,
            'to_scale' => $toScale->name
        ]);
    }

    /**
     * Validate scale values
     */
    private function validateScaleValues($scaleValues)
    {
        // Sort by minimum value
        usort($scaleValues, function ($a, $b) {
            return $b['min'] <=> $a['min'];
        });

        $previousMin = 101;
        $grades = [];

        foreach ($scaleValues as $value) {
            // Check for duplicate grades
            if (in_array($value['grade'], $grades)) {
                return "Duplicate grade: {$value['grade']}";
            }
            $grades[] = $value['grade'];

            // Check min/max relationship
            if ($value['min'] > $value['max']) {
                return "Minimum value cannot be greater than maximum for grade {$value['grade']}";
            }

            // Check for gaps or overlaps
            if ($value['max'] >= $previousMin) {
                return "Grade ranges cannot overlap. Check grade {$value['grade']}";
            }

            $previousMin = $value['min'];
        }

        // Check that scale covers 0-100 (or appropriate range)
        $minValue = min(array_column($scaleValues, 'min'));
        $maxValue = max(array_column($scaleValues, 'max'));

        if ($minValue > 0) {
            return "Grade scale must include grades for scores starting from 0";
        }

        return null; // No errors
    }

    /**
     * Format scale values for storage
     */
    private function formatScaleValues($scaleValues)
    {
        // Sort by minimum value descending
        usort($scaleValues, function ($a, $b) {
            return $b['min'] <=> $a['min'];
        });

        $formatted = [];
        foreach ($scaleValues as $value) {
            $formatted[$value['grade']] = [
                'min' => floatval($value['min']),
                'max' => floatval($value['max']),
                'points' => floatval($value['points']),
                'special' => $value['special'] ?? false
            ];
        }

        return $formatted;
    }

    /**
     * Get grade scale from cache or database
     */
    private function getScale($id)
    {
        return Cache::remember("grade_scale_{$id}", 3600, function () use ($id) {
            $scale = DB::table('grade_scales')->find($id);
            if ($scale) {
                $scale->scale_values = json_decode($scale->scale_values, true);
            }
            return $scale;
        });
    }

    /**
     * Convert grade to percentage
     */
    private function gradeToPercentage($grade, $scale)
    {
        $scaleValues = is_string($scale->scale_values) 
            ? json_decode($scale->scale_values, true) 
            : $scale->scale_values;

        if (isset($scaleValues[$grade])) {
            // Return the midpoint of the range
            return ($scaleValues[$grade]['min'] + $scaleValues[$grade]['max']) / 2;
        }

        return 0;
    }

    /**
     * Convert percentage to grade
     */
    private function percentageToGrade($percentage, $scale)
    {
        $scaleValues = is_string($scale->scale_values) 
            ? json_decode($scale->scale_values, true) 
            : $scale->scale_values;

        foreach ($scaleValues as $grade => $range) {
            if ($percentage >= $range['min'] && $percentage <= $range['max']) {
                return $grade;
            }
        }

        return 'F'; // Default to F if no match
    }

    /**
     * Apply grade scale to section
     */
    public function applyToSection(Request $request)
    {
        $validated = $request->validate([
            'scale_id' => 'required|exists:grade_scales,id',
            'section_id' => 'required|exists:course_sections,id'
        ]);

        // Check permission
        $section = DB::table('course_sections')->find($validated['section_id']);
        if (!auth()->user()->hasRole(['admin', 'registrar']) && 
            $section->instructor_id != auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::table('course_sections')
            ->where('id', $validated['section_id'])
            ->update([
                'grade_scale_id' => $validated['scale_id'],
                'updated_at' => now()
            ]);

        return response()->json(['success' => true, 'message' => 'Grade scale applied successfully']);
    }
}