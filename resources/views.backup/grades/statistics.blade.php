@extends('layouts.app')

@section('title', 'Grade Statistics - ' . $section->course->code)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('grades.index') }}">Grade Management</a></li>
                    <li class="breadcrumb-item active">Statistics</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Grade Statistics & Analytics</h1>
            <p class="text-muted">
                {{ $section->course->code }} - {{ $section->course->title }} | 
                Section {{ $section->section_code }} | 
                {{ $section->term->name }}
            </p>
        </div>
    </div>

    <!-- Summary Cards Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Class Average
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['class_average'] ?? 0, 2) }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Pass Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['pass_rate'] ?? 0, 1) }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Highest Grade
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['highest_grade'] ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                At Risk Students
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['at_risk_count'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Grade Distribution Chart -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Grade Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="gradeDistributionChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Performance by Component -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance by Component</h6>
                </div>
                <div class="card-body">
                    <canvas id="componentPerformanceChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Detailed Grade Analysis</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Metric</th>
                            <th>Value</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Mean (Average)</strong></td>
                            <td>{{ number_format($stats['mean'] ?? 0, 2) }}%</td>
                            <td>Average of all student grades</td>
                        </tr>
                        <tr>
                            <td><strong>Median</strong></td>
                            <td>{{ number_format($stats['median'] ?? 0, 2) }}%</td>
                            <td>Middle value when grades are sorted</td>
                        </tr>
                        <tr>
                            <td><strong>Mode</strong></td>
                            <td>{{ $stats['mode'] ?? 'N/A' }}</td>
                            <td>Most frequently occurring grade</td>
                        </tr>
                        <tr>
                            <td><strong>Standard Deviation</strong></td>
                            <td>{{ number_format($stats['std_dev'] ?? 0, 2) }}</td>
                            <td>Measure of grade spread</td>
                        </tr>
                        <tr>
                            <td><strong>Range</strong></td>
                            <td>{{ number_format($stats['range'] ?? 0, 2) }}%</td>
                            <td>Difference between highest and lowest</td>
                        </tr>
                        <tr>
                            <td><strong>25th Percentile</strong></td>
                            <td>{{ number_format($stats['percentile_25'] ?? 0, 2) }}%</td>
                            <td>25% of students scored below this</td>
                        </tr>
                        <tr>
                            <td><strong>75th Percentile</strong></td>
                            <td>{{ number_format($stats['percentile_75'] ?? 0, 2) }}%</td>
                            <td>75% of students scored below this</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Component Performance Details -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Component Performance Analysis</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Component</th>
                            <th>Type</th>
                            <th>Weight</th>
                            <th>Average Score</th>
                            <th>Completion Rate</th>
                            <th>Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['components'] ?? [] as $component)
                        <tr>
                            <td><strong>{{ $component['name'] }}</strong></td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($component['type']) }}</span>
                            </td>
                            <td>{{ $component['weight'] }}%</td>
                            <td>{{ number_format($component['average'] ?? 0, 1) }}%</td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                         style="width: {{ $component['completion_rate'] ?? 0 }}%">
                                        {{ $component['completion_rate'] ?? 0 }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $performance = $component['average'] ?? 0;
                                @endphp
                                <span class="badge bg-{{ $performance >= 80 ? 'success' : ($performance >= 70 ? 'warning' : 'danger') }}">
                                    {{ $performance >= 80 ? 'Good' : ($performance >= 70 ? 'Average' : 'Needs Improvement') }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row">
        <div class="col-md-4">
            <a href="{{ route('grades.entry', $section->id) }}" class="btn btn-primary w-100 mb-2">
                <i class="fas fa-edit me-2"></i>Return to Grade Entry
            </a>
        </div>
        <div class="col-md-4">
            <button class="btn btn-success w-100 mb-2" onclick="exportStatistics()">
                <i class="fas fa-download me-2"></i>Export Statistics
            </button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-info w-100 mb-2" onclick="printStatistics()">
                <i class="fas fa-print me-2"></i>Print Report
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }
    .border-left-success {
        border-left: 4px solid #1cc88a !important;
    }
    .border-left-info {
        border-left: 4px solid #36b9cc !important;
    }
    .border-left-warning {
        border-left: 4px solid #f6c23e !important;
    }
    .text-xs {
        font-size: .7rem;
    }
    .font-weight-bold {
        font-weight: 700 !important;
    }
    .text-uppercase {
        text-transform: uppercase !important;
    }
    .text-gray-800 {
        color: #5a5c69 !important;
    }
    .text-gray-300 {
        color: #dddfeb !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Grade Distribution Chart
const gradeCtx = document.getElementById('gradeDistributionChart').getContext('2d');
new Chart(gradeCtx, {
    type: 'bar',
    data: {
        labels: ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'F'],
        datasets: [{
            label: 'Number of Students',
            data: {!! json_encode($stats['grade_distribution'] ?? []) !!},
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(40, 167, 69, 0.7)',
                'rgba(32, 201, 151, 0.8)',
                'rgba(32, 201, 151, 0.7)',
                'rgba(32, 201, 151, 0.6)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(255, 193, 7, 0.7)',
                'rgba(255, 193, 7, 0.6)',
                'rgba(253, 126, 20, 0.8)',
                'rgba(253, 126, 20, 0.7)',
                'rgba(220, 53, 69, 0.8)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Component Performance Chart
const componentCtx = document.getElementById('componentPerformanceChart').getContext('2d');
const componentData = {!! json_encode($stats['components'] ?? []) !!};

new Chart(componentCtx, {
    type: 'radar',
    data: {
        labels: componentData.map(c => c.name),
        datasets: [{
            label: 'Average Score (%)',
            data: componentData.map(c => c.average || 0),
            backgroundColor: 'rgba(78, 115, 223, 0.2)',
            borderColor: 'rgba(78, 115, 223, 1)',
            pointBackgroundColor: 'rgba(78, 115, 223, 1)',
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: 'rgba(78, 115, 223, 1)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            r: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});

// Export statistics function
function exportStatistics() {
    window.location.href = '{{ route("grades.statistics", $section->id) }}?export=excel';
}

// Print statistics function
function printStatistics() {
    window.print();
}
</script>
@endpush
@endsection