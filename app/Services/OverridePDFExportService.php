<?php
// ============================================
// app/Services/OverridePDFExportService.php
// ============================================

namespace App\Services;

use App\Models\RegistrationOverrideRequest;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class OverridePDFExportService
{
    /**
     * Export single override request as PDF
     */
    public function exportRequest(RegistrationOverrideRequest $request)
    {
        $data = [
            'request' => $request,
            'student' => $request->student,
            'approver' => $request->approver,
            'generated_at' => Carbon::now()
        ];
        
        $pdf = PDF::loadView('pdf.override-request', $data);
        
        return $pdf->download('override-request-' . $request->id . '.pdf');
    }
    
    /**
     * Export student's override history as PDF
     */
    public function exportStudentHistory(Student $student, $termId = null)
    {
        $query = RegistrationOverrideRequest::where('student_id', $student->id)
            ->with(['course', 'section.course', 'term', 'approver']);
            
        if ($termId) {
            $query->where('term_id', $termId);
        }
        
        $requests = $query->orderBy('created_at', 'desc')->get();
        
        $data = [
            'student' => $student,
            'requests' => $requests,
            'generated_at' => Carbon::now(),
            'summary' => $this->generateSummaryStats($requests)
        ];
        
        $pdf = PDF::loadView('pdf.override-history', $data);
        
        return $pdf->download('override-history-' . $student->student_id . '.pdf');
    }
    
    /**
     * Generate override report for administrators
     */
    public function generateOverrideReport($startDate, $endDate, $filters = [])
    {
        $query = RegistrationOverrideRequest::with(['student.user', 'course', 'approver'])
            ->whereBetween('created_at', [$startDate, $endDate]);
            
        if (isset($filters['type'])) {
            $query->where('request_type', $filters['type']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        $requests = $query->get();
        
        $statistics = [
            'total_requests' => $requests->count(),
            'approved' => $requests->where('status', 'approved')->count(),
            'denied' => $requests->where('status', 'denied')->count(),
            'pending' => $requests->where('status', 'pending')->count(),
            'by_type' => $requests->groupBy('request_type')->map->count(),
            'average_response_time' => $this->calculateAverageResponseTime($requests),
            'approval_rate' => $this->calculateApprovalRate($requests)
        ];
        
        $data = [
            'requests' => $requests,
            'statistics' => $statistics,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'generated_at' => Carbon::now()
        ];
        
        $pdf = PDF::loadView('pdf.override-report', $data);
        $pdf->setPaper('a4', 'landscape');
        
        return $pdf->download('override-report-' . Carbon::now()->format('Y-m-d') . '.pdf');
    }
    
    private function generateSummaryStats($requests)
    {
        return [
            'total' => $requests->count(),
            'approved' => $requests->where('status', 'approved')->count(),
            'denied' => $requests->where('status', 'denied')->count(),
            'pending' => $requests->where('status', 'pending')->count(),
            'by_type' => $requests->groupBy('request_type')->map->count()
        ];
    }
    
    private function calculateAverageResponseTime($requests)
    {
        $processed = $requests->whereNotNull('approval_date');
        
        if ($processed->isEmpty()) {
            return 'N/A';
        }
        
        $totalHours = 0;
        foreach ($processed as $request) {
            $totalHours += Carbon::parse($request->created_at)
                ->diffInHours(Carbon::parse($request->approval_date));
        }
        
        $avgHours = $totalHours / $processed->count();
        
        if ($avgHours < 24) {
            return round($avgHours) . ' hours';
        } else {
            return round($avgHours / 24, 1) . ' days';
        }
    }
    
    private function calculateApprovalRate($requests)
    {
        $decided = $requests->whereIn('status', ['approved', 'denied']);
        
        if ($decided->isEmpty()) {
            return 'N/A';
        }
        
        $approved = $decided->where('status', 'approved')->count();
        
        return round(($approved / $decided->count()) * 100, 1) . '%';
    }
}