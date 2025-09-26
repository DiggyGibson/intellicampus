{{-- resources/views/degree-audit/student/detailed-report.blade.php --}}
@extends('layouts.app')

@section('title', 'Detailed Degree Audit Report')

@section('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        .page-break { page-break-before: always; }
        body { font-size: 12pt; }
        .card { border: 1px solid #ddd !important; }
    }
    
    .report-header {
        border-bottom: 3px solid #667eea;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
    }
    
    .requirement-section {
        margin-bottom: 2rem;
        border-left: 4px solid #e5e7eb;
        padding-left: 1rem;
    }
    
    .requirement-section.completed {
        border-left-color: #10b981;
    }
    
    .requirement-section.in-progress {
        border-left-color: #f59e0b;
    }
    
    .course-list {
        background: #f8fafc;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 0.5rem;
    }
    
    .print-watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        font-size: 120px;
        color: rgba(0,0,0,0.05);
        z-index: -1;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <!-- Print Watermark -->
    <div class="print-watermark d-none d-print-block">UNOFFICIAL</div>
    
    <!-- Action Buttons -->
    <div class="row mb-4 no-print">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Detailed Degree Audit Report</h2>
                <div>
                    <button class="btn btn-outline-primary me-2" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                    <button class="btn btn-outline-success me-2" onclick="exportPDF()">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                    <a href="{{ route('degree-audit.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Header -->
    <div class="report-header">
        <div class="row">
            <div class="col-md-6">
                <h3>{{ $student->user->name ?? 'Student Name' }}</h3>
                <p class="mb-1"><strong>Student ID:</strong> {{ $student->student_id }}</p>
                <p class="mb-1"><strong>Email:</strong> {{ $student->user->email }}</p>
                <p class="mb-1"><strong>Phone:</strong> {{ $student->phone ?? 'Not provided' }}</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-1"><strong>Program:</strong> {{ $student->program->name ?? 'Undeclared' }}</p>
                <p class="mb-1"><strong>Catalog Year:</strong> {{ $auditReport->catalog_year ?? date('Y') }}</p>
                <p class="mb-1"><strong>Report Date:</strong> {{ now()->format('F j, Y g:i A') }}</p>
                <p class="mb-1"><strong>Expected Graduation:</strong> {{ $auditReport->expected_graduation_date ?? 'TBD' }}</p>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Summary Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="text-primary">{{ number_format($auditReport->overall_completion_percentage ?? 0, 1) }}%</h3>
                                <p class="text-muted mb-0">Overall Progress</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3>{{ $auditReport->total_credits_completed ?? 0 }}/{{ $auditReport->total_credits_required ?? 120 }}</h3>
                                <p class="text-muted mb-0">Credits Completed</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="{{ ($auditReport->cumulative_gpa ?? 0) >= 2.0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($auditReport->cumulative_gpa ?? 0, 2) }}
                                </h3>
                                <p class="text-muted mb-0">Cumulative GPA</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h3 class="{{ ($auditReport->major_gpa ?? 0) >= 2.0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($auditReport->major_gpa ?? 0, 2) }}
                                </h3>
                                <p class="text-muted mb-0">Major GPA</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Requirements -->
    @foreach($auditReport->requirements_summary ?? [] as $categoryId => $category)
    <div class="requirement-section {{ $category['is_satisfied'] ? 'completed' : ($category['completion_percentage'] > 0 ? 'in-progress' : '') }}">
        <h4 class="mb-3">
            {{ $category['category_name'] }}
            <span class="badge {{ $category['is_satisfied'] ? 'bg-success' : 'bg-warning' }} ms-2">
                {{ number_format($category['completion_percentage'] ?? 0, 0) }}% Complete
            </span>
        </h4>
        
        @foreach($category['requirements'] ?? [] as $requirement)
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="card-title">
                            {{ $requirement['requirement_name'] }}
                            @if($requirement['is_satisfied'])
                                <i class="fas fa-check-circle text-success ms-2"></i>
                            @endif
                        </h6>
                        
                        @if($requirement['requirement_type'] === 'credit_hours')
                            <p class="mb-2">
                                <strong>Credits:</strong> 
                                {{ $requirement['credits_completed'] ?? 0 }} of {{ $requirement['credits_required'] ?? 0 }} completed
                            </p>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar" 
                                     style="width: {{ $requirement['progress_percentage'] ?? 0 }}%">
                                    {{ number_format($requirement['progress_percentage'] ?? 0, 0) }}%
                                </div>
                            </div>
                        @elseif($requirement['requirement_type'] === 'specific_courses')
                            <p class="mb-2"><strong>Required Courses:</strong></p>
                            <div class="course-list">
                                @foreach($requirement['required_courses'] ?? [] as $course)
                                    <span class="badge {{ in_array($course, $requirement['completed_courses'] ?? []) ? 'bg-success' : 'bg-secondary' }} me-2 mb-2">
                                        {{ $course }}
                                    </span>
                                @endforeach
                            </div>
                        @elseif($requirement['requirement_type'] === 'gpa')
                            <p class="mb-2">
                                <strong>GPA Requirement:</strong> 
                                Current: {{ number_format($requirement['current_gpa'] ?? 0, 2) }} / 
                                Required: {{ number_format($requirement['required_gpa'] ?? 2.0, 2) }}
                            </p>
                        @endif
                        
                        @if(!empty($requirement['notes']))
                            <p class="text-muted small mb-0">
                                <i class="fas fa-info-circle"></i> {{ $requirement['notes'] }}
                            </p>
                        @endif
                    </div>
                    <div class="col-md-4 text-end">
                        @if(!empty($requirement['remaining_courses']))
                            <div class="alert alert-warning small">
                                <strong>Still Need:</strong><br>
                                {{ implode(', ', array_slice($requirement['remaining_courses'], 0, 3)) }}
                                @if(count($requirement['remaining_courses']) > 3)
                                    <br>...and {{ count($requirement['remaining_courses']) - 3 }} more
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach

    <!-- Completed Courses -->
    <div class="page-break"></div>
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Completed Courses</h5>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Term</th>
                        <th>Course</th>
                        <th>Title</th>
                        <th>Credits</th>
                        <th>Grade</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($student->enrollments()->with('section.course')->whereNotNull('final_grade')->get() as $enrollment)
                    <tr>
                        <td>{{ $enrollment->section->term->name ?? 'N/A' }}</td>
                        <td>{{ $enrollment->section->course->code }}</td>
                        <td>{{ $enrollment->section->course->name }}</td>
                        <td>{{ $enrollment->section->course->credits }}</td>
                        <td>{{ $enrollment->final_grade }}</td>
                        <td>{{ number_format($enrollment->grade_points ?? 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- In Progress Courses -->
    <div class="card mb-4">
        <div class="card-header bg-warning">
            <h5 class="mb-0">Currently Enrolled</h5>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Title</th>
                        <th>Credits</th>
                        <th>Instructor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($student->enrollments()->with('section.course')->whereNull('final_grade')->where('enrollment_status', 'enrolled')->get() as $enrollment)
                    <tr>
                        <td>{{ $enrollment->section->course->code }}</td>
                        <td>{{ $enrollment->section->course->name }}</td>
                        <td>{{ $enrollment->section->course->credits }}</td>
                        <td>{{ $enrollment->section->instructor->name ?? 'TBA' }}</td>
                        <td><span class="badge bg-info">In Progress</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center text-muted mt-5">
        <p>This is an unofficial degree audit report. Please consult with your academic advisor for official information.</p>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
function exportPDF() {
    window.location.href = "{{ route('degree-audit.export-pdf', $auditReport->id ?? 0) }}";
}
</script>
@endsection