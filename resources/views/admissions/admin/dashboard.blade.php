@extends('layouts.app')

@section('title', 'Admissions Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Admissions Dashboard</h1>
                    <p class="text-muted mb-0">
                        @if(isset($stats['current_term']) && $stats['current_term'])
                            {{ $stats['current_term']->name }} - {{ $stats['current_term']->academic_year }}
                        @else
                            Current Academic Term
                        @endif
                    </p>
                </div>
                <div>
                    <div class="btn-group me-2" role="group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.admissions.applications.export.all', ['format' => 'excel']) }}">
                                <i class="fas fa-file-excel me-2"></i>Excel Report
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.admissions.reports.export', ['type' => 'pdf']) }}">
                                <i class="fas fa-file-pdf me-2"></i>PDF Report
                            </a></li>
                        </ul>
                    </div>
                    <a href="{{ route('admin.admissions.applications.index') }}" class="btn btn-primary">
                        <i class="fas fa-list me-1"></i> View All Applications
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-file-alt fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Applications</h6>
                            <h3 class="mb-0">{{ number_format($stats['total_applications'] ?? 0) }}</h3>
                            <small class="text-muted">
                                <i class="fas fa-arrow-up text-success"></i> {{ $stats['today'] ?? 0 }} today
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pending Review</h6>
                            <h3 class="mb-0">{{ number_format($stats['pending_review'] ?? 0) }}</h3>
                            <small class="text-muted">Awaiting initial review</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-search fa-2x text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Under Review</h6>
                            <h3 class="mb-0">{{ number_format($stats['under_review'] ?? 0) }}</h3>
                            <small class="text-muted">Currently being reviewed</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Admitted</h6>
                            <h3 class="mb-0">{{ number_format($stats['admitted'] ?? 0) }}</h3>
                            <small class="text-muted">{{ $stats['enrolled'] ?? 0 }} enrolled</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Row of Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Documents Pending</h6>
                    <h4 class="mb-0 text-warning">{{ $stats['documents_pending'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Waitlisted</h6>
                    <h4 class="mb-0 text-info">{{ $stats['waitlisted'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Denied</h6>
                    <h4 class="mb-0 text-danger">{{ $stats['denied'] ?? 0 }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Conversion Rate</h6>
                    <h4 class="mb-0 text-primary">{{ $stats['conversion_rate'] ?? 0 }}%</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Applications -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Applications</h5>
                        <a href="{{ route('admin.admissions.applications.index') }}" class="btn btn-sm btn-outline-primary">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0">Application #</th>
                                    <th class="border-0">Applicant</th>
                                    <th class="border-0">Program</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Submitted</th>
                                    <th class="border-0">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentApplications ?? [] as $application)
                                <tr>
                                    <td>
                                        <span class="font-monospace">{{ $application->application_number }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-semibold">{{ $application->first_name }} {{ $application->last_name }}</div>
                                            <small class="text-muted">{{ $application->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @if($application->program)
                                            <span class="badge bg-light text-dark">{{ $application->program->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'draft' => 'secondary',
                                                'submitted' => 'info',
                                                'under_review' => 'warning',
                                                'admitted' => 'success',
                                                'denied' => 'danger',
                                                'waitlisted' => 'primary',
                                                'withdrawn' => 'dark'
                                            ];
                                            $statusColor = $statusColors[$application->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $statusColor }}">
                                            {{ ucwords(str_replace('_', ' ', $application->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($application->submitted_at)
                                            <small>{{ \Carbon\Carbon::parse($application->submitted_at)->format('M d, Y') }}</small>
                                        @else
                                            <small class="text-muted">Not submitted</small>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.admissions.applications.show', $application->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        No recent applications found
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Stats -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.admissions.applications.pending') }}" class="btn btn-outline-primary text-start">
                            <i class="fas fa-clock me-2"></i> 
                            Review Pending Applications
                            @if(($stats['pending_review'] ?? 0) > 0)
                                <span class="badge bg-warning float-end">{{ $stats['pending_review'] }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.admissions.reviews.index') }}" class="btn btn-outline-info text-start">
                            <i class="fas fa-tasks me-2"></i> 
                            Manage Reviews
                        </a>
                        <a href="{{ route('admin.admissions.decisions.pending') }}" class="btn btn-outline-success text-start">
                            <i class="fas fa-gavel me-2"></i> 
                            Make Decisions
                        </a>
                        <a href="{{ route('admin.admissions.verification.pending') }}" class="btn btn-outline-warning text-start">
                            <i class="fas fa-file-check me-2"></i> 
                            Verify Documents
                        </a>
                        <a href="{{ route('admin.admissions.reports.index') }}" class="btn btn-outline-secondary text-start">
                            <i class="fas fa-chart-bar me-2"></i> 
                            View Reports
                        </a>
                    </div>
                </div>
            </div>

            <!-- Application Distribution -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">This Week's Activity</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">Applications</small>
                            <span class="badge bg-primary">{{ $stats['this_week'] ?? 0 }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ min(100, ($stats['this_week'] ?? 0) * 10) }}%"></div>
                        </div>
                    </div>
                    
                    @if(isset($stats['average_review_time']))
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Average Review Time</small>
                        <h5 class="mb-0">{{ $stats['average_review_time'] ?? 'N/A' }} days</h5>
                    </div>
                    @endif

                    <hr>
                    
                    <div class="text-center">
                        <a href="{{ route('admin.admissions.statistics') }}" class="btn btn-sm btn-outline-primary">
                            View Detailed Statistics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Optional: Add Chart.js for visualization -->
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // You can add charts here if needed
</script>
@endpush

@endsection