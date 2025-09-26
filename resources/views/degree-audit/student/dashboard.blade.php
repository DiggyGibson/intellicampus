{{-- resources/views/degree-audit/student/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Degree Progress Dashboard')

@section('styles')
<style>
    .progress-ring {
        transform: rotate(-90deg);
    }
    .progress-ring-circle {
        stroke-dasharray: 339.292;
        stroke-dashoffset: 339.292;
        transition: stroke-dashoffset 1s ease-in-out;
    }
    .requirement-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    .requirement-card.completed {
        border-left-color: #10b981;
        background: linear-gradient(to right, #10b98110, transparent);
    }
    .requirement-card.in-progress {
        border-left-color: #f59e0b;
        background: linear-gradient(to right, #f59e0b10, transparent);
    }
    .requirement-card.not-started {
        border-left-color: #ef4444;
        background: linear-gradient(to right, #ef444410, transparent);
    }
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h2 mb-1">Degree Progress Dashboard</h1>
                    <p class="text-muted mb-0">
                        {{ $student->program->name ?? 'Undeclared' }} | 
                        Catalog Year: {{ $auditReport->catalog_year }}
                    </p>
                </div>
                <div>
                    <button class="btn btn-outline-primary me-2" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                    <a href="{{ route('degree-audit.detailed-report') }}" class="btn btn-primary">
                        <i class="fas fa-file-alt"></i> Detailed Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Progress Section -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title mb-4">Overall Progress</h5>
                    <div class="position-relative d-inline-block mb-3">
                        <svg width="150" height="150">
                            <circle cx="75" cy="75" r="54" stroke="#e5e7eb" stroke-width="12" fill="none"/>
                            <circle class="progress-ring-circle" cx="75" cy="75" r="54" 
                                    stroke="{{ $auditReport->overall_completion_percentage >= 75 ? '#10b981' : ($auditReport->overall_completion_percentage >= 50 ? '#f59e0b' : '#ef4444') }}" 
                                    stroke-width="12" fill="none"
                                    style="stroke-dashoffset: {{ 339.292 - (339.292 * $auditReport->overall_completion_percentage / 100) }}"/>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle">
                            <h2 class="mb-0">{{ number_format($auditReport->overall_completion_percentage, 1) }}%</h2>
                            <small class="text-muted">Complete</small>
                        </div>
                    </div>
                    
                    <div class="text-start">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Credits Completed:</span>
                            <strong>{{ $auditReport->total_credits_completed }} / {{ $auditReport->total_credits_required }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Credits In Progress:</span>
                            <strong>{{ $auditReport->total_credits_in_progress }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Credits Remaining:</span>
                            <strong>{{ $auditReport->total_credits_remaining }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="row h-100">
                <!-- GPA Card -->
                <div class="col-md-6 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-3">
                                <i class="fas fa-graduation-cap"></i> Academic Standing
                            </h6>
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Cumulative GPA</small>
                                        <h3 class="mb-0 {{ $auditReport->cumulative_gpa >= 2.0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($auditReport->cumulative_gpa, 2) }}
                                        </h3>
                                        <small class="text-muted">Min: 2.00</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Major GPA</small>
                                        <h3 class="mb-0 {{ $auditReport->major_gpa >= 2.0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($auditReport->major_gpa, 2) }}
                                        </h3>
                                        <small class="text-muted">Min: 2.00</small>
                                    </div>
                                </div>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar {{ $auditReport->cumulative_gpa >= 2.0 ? 'bg-success' : 'bg-danger' }}" 
                                     style="width: {{ min(100, ($auditReport->cumulative_gpa / 4.0) * 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graduation Status Card -->
                <div class="col-md-6 mb-3">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title text-muted mb-3">
                                <i class="fas fa-flag-checkered"></i> Graduation Status
                            </h6>
                            <div class="mb-3">
                                @if($auditReport->graduation_eligible)
                                    <span class="badge bg-success fs-6">Eligible for Graduation</span>
                                @else
                                    <span class="badge bg-warning fs-6">Not Yet Eligible</span>
                                @endif
                            </div>
                            <div class="small">
                                <div class="mb-2">
                                    <i class="fas fa-calendar-alt text-muted"></i>
                                    Expected Graduation: 
                                    <strong>{{ $auditReport->expected_graduation_date ? Carbon\Carbon::parse($auditReport->expected_graduation_date)->format('F Y') : 'TBD' }}</strong>
                                </div>
                                <div>
                                    <i class="fas fa-hourglass-half text-muted"></i>
                                    Terms Remaining: 
                                    <strong>{{ $auditReport->terms_to_completion ?? 'TBD' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="col-12">
                    <div class="card stats-card text-white shadow-sm">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-3">
                                    <h4 class="mb-0">{{ $completedCount ?? 0 }}</h4>
                                    <small>Completed</small>
                                </div>
                                <div class="col-3">
                                    <h4 class="mb-0">{{ $inProgressCount ?? 0 }}</h4>
                                    <small>In Progress</small>
                                </div>
                                <div class="col-3">
                                    <h4 class="mb-0">{{ $remainingCount ?? 0 }}</h4>
                                    <small>Remaining</small>
                                </div>
                                <div class="col-3">
                                    <h4 class="mb-0">{{ $categoriesSatisfied ?? 0 }}/{{ $totalCategories ?? 0 }}</h4>
                                    <small>Categories</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Requirements by Category -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Requirements by Category</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="requirementsAccordion">
                        @foreach($auditReport->requirements_summary as $categoryId => $category)
                        <div class="accordion-item border-0 mb-2">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ !$loop->first ? 'collapsed' : '' }}" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#category{{ $categoryId }}">
                                    <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                        <div>
                                            <strong>{{ $category['category_name'] }}</strong>
                                            <span class="badge {{ $category['is_satisfied'] ? 'bg-success' : 'bg-warning' }} ms-2">
                                                {{ $category['is_satisfied'] ? 'Complete' : 'In Progress' }}
                                            </span>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">
                                                {{ number_format($category['completion_percentage'], 0) }}% Complete
                                            </small>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="category{{ $categoryId }}" 
                                 class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                 data-bs-parent="#requirementsAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        @foreach($category['requirements'] as $req)
                                        <div class="col-md-6 mb-3">
                                            <div class="requirement-card card h-100 {{ $req['is_satisfied'] ? 'completed' : ($req['progress_percentage'] > 0 ? 'in-progress' : 'not-started') }}">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title mb-0">{{ $req['requirement_name'] }}</h6>
                                                        @if($req['is_satisfied'])
                                                            <i class="fas fa-check-circle text-success"></i>
                                                        @elseif($req['progress_percentage'] > 0)
                                                            <i class="fas fa-clock text-warning"></i>
                                                        @else
                                                            <i class="fas fa-circle text-muted"></i>
                                                        @endif
                                                    </div>
                                                    
                                                    @if($req['requirement_type'] === 'credit_hours')
                                                        <div class="mb-2">
                                                            <small class="text-muted">Credits</small>
                                                            <div class="d-flex justify-content-between">
                                                                <span>{{ $req['credits_completed'] ?? 0 }} / {{ $req['credits_required'] ?? 0 }}</span>
                                                                <span>{{ number_format($req['progress_percentage'] ?? 0, 0) }}%</span>
                                                            </div>
                                                            <div class="progress" style="height: 5px;">
                                                                <div class="progress-bar" style="width: {{ $req['progress_percentage'] ?? 0 }}%"></div>
                                                            </div>
                                                        </div>
                                                    @elseif($req['requirement_type'] === 'specific_courses')
                                                        <div class="mb-2">
                                                            <small class="text-muted">Required Courses</small>
                                                            @if(!empty($req['remaining_courses']))
                                                                <div class="mt-1">
                                                                    <small class="text-danger">Still Need: {{ implode(', ', $req['remaining_courses']) }}</small>
                                                                </div>
                                                            @else
                                                                <div class="mt-1">
                                                                    <small class="text-success">All courses completed</small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @elseif($req['requirement_type'] === 'gpa')
                                                        <div class="mb-2">
                                                            <small class="text-muted">GPA Requirement</small>
                                                            <div>
                                                                Current: {{ $req['current_gpa'] ?? 'N/A' }} / Required: {{ $req['required_gpa'] ?? 2.0 }}
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations Section -->
    @if(!empty($auditReport->recommendations))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Recommendations</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($auditReport->recommendations as $recommendation)
                        <div class="col-md-6 mb-3">
                            <div class="alert alert-{{ $recommendation['priority'] === 'high' ? 'danger' : 'warning' }} mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                {{ $recommendation['message'] }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('degree-audit.what-if') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-question-circle"></i> What-If Analysis
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('academic-plans.index') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-calendar-alt"></i> Academic Plan
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-outline-info w-100" onclick="refreshAudit()">
                                <i class="fas fa-sync"></i> Refresh Audit
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('graduation.check') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-graduation-cap"></i> Check Graduation
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function refreshAudit() {
    if(confirm('This will regenerate your degree audit. Continue?')) {
        fetch('{{ route("degree-audit.run") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ force_refresh: true })
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Error refreshing audit. Please try again.');
            }
        });
    }
}
</script>
@endsection