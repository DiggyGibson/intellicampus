<?php
# app/Http/Controllers/Admissions/ExamConductController.php

namespace App\Http\Controllers\Admissions;

use App\Http\Controllers\Controller;
use App\Services\ExamConductService;
use App\Services\EntranceExamService;
use App\Models\EntranceExam;
use App\Models\ExamSession;
use App\Models\ExamCenter;
use App\Models\AdmissionApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ExamConductController extends Controller
{
    protected ExamConductService $examConductService;
    protected EntranceExamService $entranceExamService;

    public function __construct(
        ExamConductService $examConductService,
        EntranceExamService $entranceExamService
    ) {
        $this->examConductService = $examConductService;
        $this->entranceExamService = $entranceExamService;
    }

    /**
     * Display exam management dashboard
     */
    public function index()
    {
        try {
            // Use your existing service
            $data = $this->examConductService->getExamDashboardData();
            
            return view('admissions.exam-conduct.index', $data);
        } catch (\Exception $e) {
            Log::error('Failed to load exam conduct dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Failed to load exam dashboard');
        }
    }

    /**
     * Schedule a new exam session
     */
    public function schedule(Request $request)
    {
        $validated = $request->validate([
            'exam_name' => 'required|string|max:255',
            'exam_date' => 'required|date|after:today',
            'exam_type' => 'required|in:entrance,placement,scholarship',
            'duration_minutes' => 'required|integer|min:30|max:480',
            'venue_id' => 'required|exists:exam_centers,id',
            'max_capacity' => 'required|integer|min:1',
            'instructions' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Use your existing service methods
            $exam = $this->entranceExamService->createExam($validated);
            
            // Schedule the session
            $session = $this->examConductService->scheduleExamSession([
                'entrance_exam_id' => $exam->id,
                'venue_id' => $validated['venue_id'],
                'session_date' => $validated['exam_date'],
                'max_capacity' => $validated['max_capacity'],
                'instructions' => $validated['instructions'],
            ]);

            DB::commit();
            
            return redirect()
                ->route('admissions.exam-conduct.index')
                ->with('success', 'Exam session scheduled successfully');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to schedule exam', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Failed to schedule exam: ' . $e->getMessage());
        }
    }

    /**
     * Manage exam venues
     */
    public function venues()
    {
        $venues = ExamCenter::with(['sessions' => function ($query) {
            $query->where('session_date', '>=', now())
                  ->orderBy('session_date');
        }])
        ->orderBy('name')
        ->paginate(20);

        return view('admissions.exam-conduct.venues', compact('venues'));
    }

    /**
     * Generate admit cards
     */
    public function admitCards(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:entrance_exams,id',
            'application_ids' => 'nullable|array',
            'application_ids.*' => 'exists:admission_applications,id',
        ]);

        try {
            $admitCards = $this->examConductService->generateAdmitCards(
                $request->exam_id,
                $request->application_ids ?? []
            );

            return view('admissions.exam-conduct.admit-cards', compact('admitCards'));
            
        } catch (\Exception $e) {
            Log::error('Failed to generate admit cards', [
                'exam_id' => $request->exam_id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Failed to generate admit cards');
        }
    }
}