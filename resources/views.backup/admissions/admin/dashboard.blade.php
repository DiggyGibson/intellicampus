{{-- resources/views/admissions/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Admissions Dashboard')

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-graduation-cap me-2"></i>Admissions Dashboard
                    </h1>
                    <p class="text-muted mb-0">{{ $currentTerm->name ?? 'Current Term' }} Admissions Overview</p>
                </div>
                <div class="btn-toolbar" role="toolbar">
                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admissions.admin.export', ['format' => 'excel']) }}">
                                <i class="fas fa-file-excel me-2"></i>Excel Report
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('admissions.admin.export', ['format' => 'pdf']) }}">
                                <i class="fas fa-file-pdf me-2"></i>PDF Report
                            </a></li>
                        </ul>
                    </div>
                    <a href="{{ route('admissions.admin.applications.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> New Application
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Stats Cards --}}
    <div class="row mb-4">
        {{-- Total Applications --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Applications
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_applications'] ?? 0) }}
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-{{ $stats['trend_total'] > 0 ? 'success' : 'danger' }} mr-2">
                                    <i class="fas fa-arrow-{{ $stats['trend_total'] > 0 ? 'up' : 'down' }}"></i> 
                                    {{ abs($stats['trend_total'] ?? 0) }}%
                                </span>
                                vs last term
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Under Review --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Under Review
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['under_review'] ?? 0) }}
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-info mr-2">
                                    {{ $stats['avg_review_time'] ?? 0 }} days avg
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-search fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Admitted --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Admitted
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['admitted'] ?? 0) }}
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                <span class="text-primary mr-2">
                                    {{ $stats['acceptance_rate'] ?? 0 }}% rate
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Enrolled --}}
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Enrolled
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                        {{ number_format($stats['enrolled'] ?? 0) }}
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar"
                                            style="width: {{ $stats['yield_rate'] ?? 0 }}%"
                                            aria-valuenow="{{ $stats['yield_rate'] ?? 0 }}"
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 mb-0 text-muted text-xs">
                                {{ $stats['yield_rate'] ?? 0 }}% yield rate
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-4">
        {{-- Application Trends Chart --}}
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Application Trends</h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary active" data-period="week">Week</button>
                        <button type="button" class="btn btn-outline-secondary" data-period="month">Month</button>
                        <button type="button" class="btn btn-outline-secondary" data-period="year">Year</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="applicationTrendsChart" height="80"></canvas>
                </div>
            </div>
        </div>

        {{-- Program Distribution --}}
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Applications by Program</h6>
                </div>
                <div class="card-body">
                    <canvas id="programChart" height="200"></canvas>
                    <div class="mt-4 text-center small">
                        @foreach($topPrograms as $program)
                        <span class="mr-2">
                            <i class="fas fa-circle text-{{ $loop->first ? 'primary' : ($loop->iteration == 2 ? 'success' : 'info') }}"></i> 
                            {{ $program->code }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity & Quick Actions --}}
    <div class="row">
        {{-- Recent Applications --}}
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Applications</h6>
                    <a href="{{ route('admissions.admin.applications.index') }}" class="text-primary">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Application #</th>
                                    <th>Applicant</th>
                                    <th>Program</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentApplications as $application)
                                <tr>
                                    <td>
                                        <a href="{{ route('admissions.admin.applications.show', $application->id) }}">
                                            {{ $application->application_number }}
                                        </a>
                                    </td>
                                    <td>{{ $application->first_name }} {{ $application->last_name }}</td>
                                    <td>{{ $application->program->code ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $application->getStatusColor() }}">
                                            {{ ucwords(str_replace('_', ' ', $application->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admissions.admin.applications.show', $application->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No recent applications</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Actions --}}
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pending Actions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        {{-- Documents Pending Verification --}}
                        <a href="{{ route('admissions.admin.documents.pending') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <i class="fas fa-file-alt text-warning me-2"></i>
                                    <strong>Documents Pending Verification</strong>
                                </div>
                                <span class="badge bg-warning rounded-pill">{{ $pendingActions['documents'] ?? 0 }}</span>
                            </div>
                            <p class="mb-1 text-muted small">Review and verify uploaded documents</p>
                        </a>

                        {{-- Applications Awaiting Review --}}
                        <a href="{{ route('admissions.admin.reviews.pending') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <i class="fas fa-search text-info me-2"></i>
                                    <strong>Applications Awaiting Review</strong>
                                </div>
                                <span class="badge bg-info rounded-pill">{{ $pendingActions['reviews'] ?? 0 }}</span>
                            </div>
                            <p class="mb-1 text-muted small">Complete assigned application reviews</p>
                        </a>

                        {{-- Decisions Pending --}}
                        <a href="{{ route('admissions.admin.decisions.pending') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <i class="fas fa-gavel text-primary me-2"></i>
                                    <strong>Decisions Pending</strong>
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $pendingActions['decisions'] ?? 0 }}</span>
                            </div>
                            <p class="mb-1 text-muted small">Make admission decisions</p>
                        </a>

                        {{-- Interviews Scheduled --}}
                        <a href="{{ route('admissions.admin.interviews.today') }}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <i class="fas fa-calendar-check text-success me-2"></i>
                                    <strong>Today's Interviews</strong>
                                </div>
                                <span class="badge bg-success rounded-pill">{{ $pendingActions['interviews'] ?? 0 }}</span>
                            </div>
                            <p class="mb-1 text-muted small">Scheduled interviews for today</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Filters --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Filters</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <select class="form-select" id="termFilter">
                                <option value="">All Terms</option>
                                @foreach($terms as $term)
                                    <option value="{{ $term->id }}" {{ $currentTerm->id == $term->id ? 'selected' : '' }}>
                                        {{ $term->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="programFilter">
                                <option value="">All Programs</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="submitted">Submitted</option>
                                <option value="under_review">Under Review</option>
                                <option value="admitted">Admitted</option>
                                <option value="denied">Denied</option>
                                <option value="waitlisted">Waitlisted</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary w-100" onclick="applyFilters()">
                                <i class="fas fa-filter me-1"></i> Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Application Trends Chart
    const ctx = document.getElementById('applicationTrendsChart').getContext('2d');
    const trendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['labels']) !!},
            datasets: [{
                label: 'Applications',
                data: {!! json_encode($chartData['applications']) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Admitted',
                data: {!! json_encode($chartData['admitted']) !!},
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Program Distribution Chart
    const programCtx = document.getElementById('programChart').getContext('2d');
    const programChart = new Chart(programCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($programData['labels']) !!},
            datasets: [{
                data: {!! json_encode($programData['values']) !!},
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                ],
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Apply Filters Function
    function applyFilters() {
        const term = document.getElementById('termFilter').value;
        const program = document.getElementById('programFilter').value;
        const status = document.getElementById('statusFilter').value;
        
        window.location.href = `{{ route('admissions.admin.dashboard') }}?term=${term}&program=${program}&status=${status}`;
    }

    // Auto-refresh dashboard every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000);
</script>
@endpush
@endsection