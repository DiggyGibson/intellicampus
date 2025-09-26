<?php

// ===============================================
// app/Http/Controllers/AdvisorOverrideController.php
// ===============================================

namespace App\Http\Controllers;

use App\Models\RegistrationOverrideRequest;
use App\Services\RegistrationOverrideService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdvisorOverrideController extends Controller
{
    protected $overrideService;
    
    public function __construct(RegistrationOverrideService $overrideService)
    {
        $this->overrideService = $overrideService;
    }
    
    /**
     * Display dashboard with pending requests
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get pending requests for this approver
        $requests = $this->overrideService->getPendingRequestsForApprover($user);
        
        // Get statistics
        $stats = $this->getApproverStatistics($user);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'requests' => $requests,
                'stats' => $stats
            ]);
        }
        
        return view('advisor.override-dashboard', compact('requests', 'stats'));
    }
    
    /**
     * Approve an override request
     */
    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
            'conditions' => 'nullable|array'
        ]);
        
        $overrideRequest = RegistrationOverrideRequest::findOrFail($id);
        $user = Auth::user();
        
        try {
            $result = $this->overrideService->processOverrideRequest(
                $overrideRequest,
                $user,
                RegistrationOverrideRequest::STATUS_APPROVED,
                $validated['notes'] ?? null,
                $validated['conditions'] ?? []
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Request approved successfully',
                'override_code' => $result->override_code
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Deny an override request
     */
    public function deny(Request $request, $id)
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:1000'
        ]);
        
        $overrideRequest = RegistrationOverrideRequest::findOrFail($id);
        $user = Auth::user();
        
        try {
            $this->overrideService->processOverrideRequest(
                $overrideRequest,
                $user,
                RegistrationOverrideRequest::STATUS_DENIED,
                $validated['notes']
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Request denied'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    /**
     * Bulk process requests
     */
    public function bulkProcess(Request $request)
    {
        $validated = $request->validate([
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:registration_override_requests,id',
            'action' => 'required|in:approve,deny',
            'notes' => 'nullable|string|max:1000'
        ]);
        
        $user = Auth::user();
        $processedCount = 0;
        $errors = [];
        
        foreach ($validated['request_ids'] as $requestId) {
            try {
                $overrideRequest = RegistrationOverrideRequest::find($requestId);
                
                if ($overrideRequest && $overrideRequest->status === 'pending') {
                    $this->overrideService->processOverrideRequest(
                        $overrideRequest,
                        $user,
                        $validated['action'] === 'approve' ? 
                            RegistrationOverrideRequest::STATUS_APPROVED : 
                            RegistrationOverrideRequest::STATUS_DENIED,
                        $validated['notes'] ?? 'Bulk processed'
                    );
                    
                    $processedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "Request $requestId: " . $e->getMessage();
            }
        }
        
        return response()->json([
            'success' => true,
            'processed' => $processedCount,
            'errors' => $errors,
            'message' => "$processedCount requests processed successfully"
        ]);
    }
    
    /**
     * Get statistics for approver dashboard
     */
    private function getApproverStatistics($user)
    {
        $baseQuery = RegistrationOverrideRequest::query();
        
        // Filter based on user role
        if ($user->hasRole('advisor')) {
            $studentIds = DB::table('advisor_assignments')
                ->where('advisor_id', $user->id)
                ->pluck('student_id');
            $baseQuery->whereIn('student_id', $studentIds);
        } elseif ($user->hasRole('department-head')) {
            $courseIds = DB::table('courses')
                ->where('department_id', $user->department_id)
                ->pluck('id');
            $baseQuery->whereIn('course_id', $courseIds);
        }
        
        return [
            'pending' => (clone $baseQuery)->pending()->count(),
            'approved_this_week' => (clone $baseQuery)
                ->where('approver_id', $user->id)
                ->where('status', 'approved')
                ->where('approval_date', '>=', now()->startOfWeek())
                ->count(),
            'denied_this_week' => (clone $baseQuery)
                ->where('approver_id', $user->id)
                ->where('status', 'denied')
                ->where('approval_date', '>=', now()->startOfWeek())
                ->count(),
            'average_response_time' => $this->calculateAverageResponseTime($user)
        ];
    }
    
    /**
     * Calculate average response time for approver
     */
    private function calculateAverageResponseTime($user)
    {
        $avgHours = RegistrationOverrideRequest::where('approver_id', $user->id)
            ->whereNotNull('approval_date')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, approval_date)) as avg_hours')
            ->value('avg_hours');
            
        if ($avgHours < 24) {
            return round($avgHours) . ' hours';
        } else {
            return round($avgHours / 24, 1) . ' days';
        }
    }
}