@extends('layouts.app')

@section('title', 'Grade Management Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Grade Management Dashboard</h1>
            <p class="mb-0 text-muted">{{ $currentTerm->name ?? 'Current Term' }} - Manage your course grades</p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Grade Deadlines Card -->
    @if($deadlines)
    <div class="card mb-4 border-left-warning">
        <div class="card-header bg-warning bg-opacity-10">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Important Deadlines</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <small class="text-muted">Midterm Grades Due</small>
                    <p class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($deadlines->midterm_grade_deadline)->format('M d, Y') }}</p>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Final Grades Due</small>
                    <p class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($deadlines->final_grade_deadline)->format('M d, Y') }}</p>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Grade Changes Until</small>
                    <p class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($deadlines->grade_change_deadline)->format('M d, Y') }}</p>
                </div>
                <div class="col-md-3">
                    <small class="text-muted">Incomplete Resolution</small>
                    <p class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($deadlines->incomplete_deadline)->format('M d, Y') }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Course Sections Grid -->
    <div class="row">
        @forelse($sections as $section)
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">{{ $section->course->code }} - {{ $section->course->title }}</h6>
                    <small>Section {{ $section->section_code }}</small>
                </div>
                <div class="card-body">
                    <!-- Section Stats -->
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <small class="text-muted d-block">Enrolled</small>
                            <span class="h5">{{ $section->stats['total_enrolled'] }}</span>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Graded</small>
                            <span class="h5 text-success">{{ $section->stats['grades_submitted'] }}</span>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Pending</small>
                            <span class="h5 text-warning">{{ $section->stats['pending_grades'] }}</span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    @php
                        $progress = $section->stats['total_enrolled'] > 0 
                            ? ($section->stats['grades_submitted'] / $section->stats['total_enrolled']) * 100 
                            : 0;
                    @endphp
                    <div class="mb-3">
                        <small class="text-muted">Grading Progress</small>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $progress }}%"
                                 aria-valuenow="{{ $progress }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                        <small class="text-muted">{{ number_format($progress, 0) }}% Complete</small>
                    </div>

                    <!-- Average Grade Display -->
                    @if($section->stats['average_grade'] > 0)
                    <div class="text-center mb-3">
                        <small class="text-muted d-block">Class Average</small>
                        <span class="h4">{{ number_format($section->stats['average_grade'], 1) }}%</span>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <a href="{{ route('grades.entry', $section->id) }}" 
                           class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i> Enter Grades
                        </a>
                        <a href="{{ route('grades.components', $section->id) }}" 
                           class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-cog me-1"></i> Manage Components
                        </a>
                        <a href="{{ route('grades.statistics', $section->id) }}" 
                           class="btn btn-outline-info btn-sm">
                            <i class="fas fa-chart-bar me-1"></i> View Statistics
                        </a>
                        @if($section->stats['pending_grades'] == 0 && $section->stats['total_enrolled'] > 0)
                        <a href="{{ route('grades.preview', $section->id) }}" 
                           class="btn btn-success btn-sm">
                            <i class="fas fa-check me-1"></i> Submit Final Grades
                        </a>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>
                        Last updated: {{ $section->updated_at->diffForHumans() }}
                    </small>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                You are not assigned to any sections for {{ $currentTerm->name ?? 'the current term' }}.
            </div>
        </div>
        @endforelse
    </div>

    <!-- Quick Actions -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <button class="btn btn-outline-primary w-100" onclick="downloadAllTemplates()">
                        <i class="fas fa-download me-2"></i>Download All Templates
                    </button>
                </div>
                <div class="col-md-3 mb-2">
                    <button class="btn btn-outline-success w-100" onclick="exportGrades()">
                        <i class="fas fa-file-excel me-2"></i>Export All Grades
                    </button>
                </div>
                <div class="col-md-3 mb-2">
                    <button class="btn btn-outline-info w-100" onclick="viewGradeHistory()">
                        <i class="fas fa-history me-2"></i>Grade History
                    </button>
                </div>
                <div class="col-md-3 mb-2">
                    <button class="btn btn-outline-warning w-100" onclick="viewPendingChanges()">
                        <i class="fas fa-exclamation-triangle me-2"></i>Pending Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-2px);
    }
    .progress {
        background-color: #e9ecef;
    }
</style>
@endpush

@push('scripts')
<script>
function downloadAllTemplates() {
    // Implementation for downloading all grade templates
    alert('Downloading all grade templates...');
}

function exportGrades() {
    // Implementation for exporting all grades
    alert('Exporting all grades...');
}

function viewGradeHistory() {
    // Implementation for viewing grade history
    window.location.href = '/grades/history';
}

function viewPendingChanges() {
    // Implementation for viewing pending grade changes
    alert('Viewing pending grade changes...');
}
</script>
@endpush
@endsection