{{-- File: C:\IntelliCampus\resources\views\degree-audit\graduation\check.blade.php --}}
{{-- URL: /graduation/check --}}
@extends('layouts.app')

@section('title', 'Graduation Eligibility Check')

@section('styles')
<style>
    .eligibility-card {
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .eligible {
        background: linear-gradient(135deg, #10b98110, #d1fae510);
        border: 2px solid #10b981;
    }
    
    .not-eligible {
        background: linear-gradient(135deg, #f59e0b10, #fed7aa10);
        border: 2px solid #f59e0b;
    }
    
    .requirement-item {
        padding: 12px;
        margin-bottom: 8px;
        border-left: 4px solid #e5e7eb;
        background: #f9fafb;
    }
    
    .requirement-item.complete {
        border-left-color: #10b981;
        background: #d1fae520;
    }
    
    .requirement-item.incomplete {
        border-left-color: #ef4444;
        background: #fee2e220;
    }
    
    .hold-item {
        background: #fee2e2;
        border: 1px solid #ef4444;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 8px;
    }
    
    .progress-circle {
        width: 200px;
        height: 200px;
        margin: 0 auto;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Graduation Eligibility Check</h2>
                <a href="{{ route('degree-audit.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    @if(isset($error))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i> {{ $error }}
        </div>
    @endif

    @if($student && $eligibility)
    <!-- Eligibility Status Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="eligibility-card card {{ $eligibility->is_eligible ? 'eligible' : 'not-eligible' }}">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="{{ $eligibility->is_eligible ? 'text-success' : 'text-warning' }}">
                                @if($eligibility->is_eligible)
                                    <i class="fas fa-check-circle"></i> You are eligible for graduation!
                                @else
                                    <i class="fas fa-exclamation-circle"></i> Not yet eligible for graduation
                                @endif
                            </h3>
                            <p class="mb-2">
                                <strong>Student:</strong> {{ $student->user->name ?? 'N/A' }} ({{ $student->student_id }})
                            </p>
                            <p class="mb-2">
                                <strong>Program:</strong> {{ $student->program->name ?? 'Undeclared' }}
                            </p>
                            <p class="mb-0">
                                <strong>Expected Graduation:</strong> 
                                {{ $eligibility->expected_graduation_date ? \Carbon\Carbon::parse($eligibility->expected_graduation_date)->format('F Y') : 'To be determined' }}
                            </p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="progress-circle">
                                <canvas id="progressChart"></canvas>
                            </div>
                            <h4 class="mt-2">{{ number_format($eligibility->completion_percentage, 0) }}% Complete</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-book fa-2x text-primary mb-2"></i>
                    <h3>{{ $eligibility->total_credits_completed }}/{{ $eligibility->total_credits_required }}</h3>
                    <p class="text-muted mb-0">Credits Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-graduation-cap fa-2x text-info mb-2"></i>
                    <h3 class="{{ $eligibility->gpa >= 2.0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($eligibility->gpa, 2) }}
                    </h3>
                    <p class="text-muted mb-0">Cumulative GPA</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-tasks fa-2x text-warning mb-2"></i>
                    <h3>{{ count($eligibility->pending_requirements) }}</h3>
                    <p class="text-muted mb-0">Pending Requirements</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-ban fa-2x text-danger mb-2"></i>
                    <h3>{{ count($eligibility->holds) }}</h3>
                    <p class="text-muted mb-0">Active Holds</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Requirements Status -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Graduation Requirements</h5>
                </div>
                <div class="card-body">
                    @if(empty($eligibility->pending_requirements))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> All graduation requirements have been met!
                        </div>
                    @else
                        <h6 class="text-muted mb-3">Pending Requirements:</h6>
                        @foreach($eligibility->pending_requirements as $requirement)
                        <div class="requirement-item incomplete">
                            <strong>{{ $requirement['name'] ?? 'Requirement' }}</strong>
                            <p class="mb-0 text-muted">{{ $requirement['description'] ?? '' }}</p>
                            @if(isset($requirement['credits_needed']))
                                <small>Credits needed: {{ $requirement['credits_needed'] }}</small>
                            @endif
                        </div>
                        @endforeach
                    @endif

                    <h6 class="text-muted mb-3 mt-4">Completed Requirements:</h6>
                    <div class="requirement-item complete">
                        <i class="fas fa-check text-success"></i> Core Curriculum Complete
                    </div>
                    <div class="requirement-item complete">
                        <i class="fas fa-check text-success"></i> Major Requirements Complete
                    </div>
                    @if($eligibility->gpa >= 2.0)
                    <div class="requirement-item complete">
                        <i class="fas fa-check text-success"></i> Minimum GPA Requirement Met
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Holds and Actions -->
        <div class="col-lg-6">
            <!-- Holds -->
            <div class="card mb-4">
                <div class="card-header {{ count($eligibility->holds) > 0 ? 'bg-danger text-white' : 'bg-success text-white' }}">
                    <h5 class="mb-0">Registration Holds</h5>
                </div>
                <div class="card-body">
                    @if(count($eligibility->holds) > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            You must clear all holds before graduation
                        </div>
                        @foreach($eligibility->holds as $hold)
                        <div class="hold-item">
                            <strong>{{ ucfirst($hold['type']) }} Hold</strong>
                            <p class="mb-0">{{ $hold['description'] }}</p>
                        </div>
                        @endforeach
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> No active holds on your account
                        </div>
                    @endif
                </div>
            </div>

            <!-- Next Steps -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Next Steps</h5>
                </div>
                <div class="card-body">
                    @if($eligibility->is_eligible)
                        <ol>
                            <li class="mb-2">Review your degree audit for accuracy</li>
                            <li class="mb-2">Clear any outstanding holds</li>
                            <li class="mb-2">Submit your graduation application</li>
                            <li class="mb-2">Order your cap and gown</li>
                            <li class="mb-2">Confirm your diploma name</li>
                        </ol>
                        
                        <div class="d-grid gap-2">
                            <a href="{{ route('graduation.apply') }}" class="btn btn-success btn-lg">
                                <i class="fas fa-graduation-cap"></i> Apply for Graduation
                            </a>
                        </div>
                    @else
                        <ol>
                            <li class="mb-2">Complete all pending requirements</li>
                            <li class="mb-2">Meet with your academic advisor</li>
                            <li class="mb-2">Clear any outstanding holds</li>
                            <li class="mb-2">Plan your remaining coursework</li>
                        </ol>
                        
                        <div class="d-grid gap-2">
                            <a href="{{ route('academic-plans.planner') }}" class="btn btn-primary">
                                <i class="fas fa-calendar-alt"></i> Plan Remaining Courses
                            </a>
                            <a href="{{ route('degree-audit.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-chart-line"></i> View Degree Audit
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if($eligibility)
// Progress Chart
const ctx = document.getElementById('progressChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        datasets: [{
            data: [
                {{ $eligibility->completion_percentage }}, 
                {{ 100 - $eligibility->completion_percentage }}
            ],
            backgroundColor: ['#10b981', '#e5e7eb'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
            legend: { display: false },
            tooltip: { enabled: false }
        }
    }
});
@endif
</script>
@endsection