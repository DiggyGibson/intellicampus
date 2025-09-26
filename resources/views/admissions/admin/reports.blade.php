{{-- File: resources/views/admissions/admin/reports.blade.php --}}
@extends('layouts.app')

@section('title', 'Admissions Reports & Analytics')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">Admissions Reports & Analytics</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admissions.admin.dashboard') }}">Admissions</a></li>
                            <li class="breadcrumb-item active">Reports</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-download"></i> Export Reports
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" onclick="exportReport('summary')">
                            <i class="fas fa-file-pdf"></i> Summary Report (PDF)
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportReport('detailed')">
                            <i class="fas fa-file-excel"></i> Detailed Report (Excel)
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="exportReport('demographics')">
                            <i class="fas fa-file-csv"></i> Demographics (CSV)
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#customReportModal">
                            <i class="fas fa-cog"></i> Custom Report
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Period Selection --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label for="reportTerm" class="form-label">Academic Term</label>
                    <select class="form-select" id="reportTerm" onchange="updateReports()">
                        @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ $term->is_current ? 'selected' : '' }}>
                            {{ $term->name }} ({{ $term->start_date->format('M Y') }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="reportProgram" class="form-label">Program Filter</label>
                    <select class="form-select" id="reportProgram" onchange="updateReports()">
                        <option value="">All Programs</option>
                        @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="reportDateFrom" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="reportDateFrom" onchange="updateReports()">
                </div>
                <div class="col-md-2">
                    <label for="reportDateTo" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="reportDateTo" value="{{ date('Y-m-d') }}" onchange="updateReports()">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary w-100" onclick="refreshReports()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Key Metrics Dashboard --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Applications</h6>
                            <h2 class="mb-0">{{ number_format($metrics['total_applications'] ?? 0) }}</h2>
                            <small>
                                <i class="fas fa-arrow-{{ ($metrics['application_trend'] ?? 0) >= 0 ? 'up' : 'down' }}"></i>
                                {{ abs($metrics['application_trend'] ?? 0) }}% vs last term
                            </small>
                        </div>
                        <div>
                            <i class="fas fa-file-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Acceptance Rate</h6>
                            <h2 class="mb-0">{{ number_format($metrics['acceptance_rate'] ?? 0, 1) }}%</h2>
                            <small>{{ $metrics['admitted'] ?? 0 }} admitted</small>
                        </div>
                        <div>
                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Yield Rate</h6>
                            <h2 class="mb-0">{{ number_format($metrics['yield_rate'] ?? 0, 1) }}%</h2>
                            <small>{{ $metrics['enrolled'] ?? 0 }} enrolled</small>
                        </div>
                        <div>
                            <i class="fas fa-user-graduate fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Avg Processing Time</h6>
                            <h2 class="mb-0">{{ $metrics['avg_processing_days'] ?? 0 }}</h2>
                            <small>days to decision</small>
                        </div>
                        <div>
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row mb-4">
        {{-- Application Funnel --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-funnel-dollar"></i> Application Funnel</h5>
                </div>
                <div class="card-body">
                    <canvas id="funnelChart" height="300"></canvas>
                    <div class="mt-3">
                        <div class="row text-center small">
                            <div class="col">
                                <strong>Started</strong><br>
                                {{ number_format($funnel['started'] ?? 0) }}
                            </div>
                            <div class="col">
                                <strong>Submitted</strong><br>
                                {{ number_format($funnel['submitted'] ?? 0) }}
                                <small class="text-muted d-block">
                                    {{ $funnel['submitted_rate'] ?? 0 }}%
                                </small>
                            </div>
                            <div class="col">
                                <strong>Reviewed</strong><br>
                                {{ number_format($funnel['reviewed'] ?? 0) }}
                                <small class="text-muted d-block">
                                    {{ $funnel['reviewed_rate'] ?? 0 }}%
                                </small>
                            </div>
                            <div class="col">
                                <strong>Admitted</strong><br>
                                {{ number_format($funnel['admitted'] ?? 0) }}
                                <small class="text-muted d-block">
                                    {{ $funnel['admitted_rate'] ?? 0 }}%
                                </small>
                            </div>
                            <div class="col">
                                <strong>Enrolled</strong><br>
                                {{ number_format($funnel['enrolled'] ?? 0) }}
                                <small class="text-muted d-block">
                                    {{ $funnel['enrolled_rate'] ?? 0 }}%
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Applications Timeline --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Application Timeline</h5>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-secondary active" onclick="updateTimeline('daily')">Daily</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="updateTimeline('weekly')">Weekly</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="updateTimeline('monthly')">Monthly</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="timelineChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Tables Row --}}
    <div class="row mb-4">
        {{-- Program-wise Statistics --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> Program Statistics</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Program</th>
                                    <th class="text-center">Applied</th>
                                    <th class="text-center">Admitted</th>
                                    <th class="text-center">Enrolled</th>
                                    <th class="text-center">Accept Rate</th>
                                    <th class="text-center">Yield</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($programStats as $stat)
                                <tr>
                                    <td>
                                        <strong>{{ $stat->program_name }}</strong>
                                        <small class="d-block text-muted">{{ $stat->program_code }}</small>
                                    </td>
                                    <td class="text-center">{{ number_format($stat->applied) }}</td>
                                    <td class="text-center">{{ number_format($stat->admitted) }}</td>
                                    <td class="text-center">{{ number_format($stat->enrolled) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $stat->acceptance_rate > 50 ? 'success' : ($stat->acceptance_rate > 25 ? 'warning' : 'danger') }}">
                                            {{ number_format($stat->acceptance_rate, 1) }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $stat->yield_rate > 40 ? 'success' : ($stat->yield_rate > 25 ? 'warning' : 'danger') }}">
                                            {{ number_format($stat->yield_rate, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Demographics Breakdown --}}
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-users"></i> Demographics Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Gender Distribution</h6>
                            <canvas id="genderChart" height="200"></canvas>
                            <div class="mt-2 small">
                                <div class="d-flex justify-content-between">
                                    <span><i class="fas fa-circle text-primary"></i> Male</span>
                                    <strong>{{ $demographics['gender']['male'] ?? 0 }}%</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span><i class="fas fa-circle text-danger"></i> Female</span>
                                    <strong>{{ $demographics['gender']['female'] ?? 0 }}%</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span><i class="fas fa-circle text-secondary"></i> Other</span>
                                    <strong>{{ $demographics['gender']['other'] ?? 0 }}%</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Application Type</h6>
                            <canvas id="typeChart" height="200"></canvas>
                            <div class="mt-2 small">
                                <div class="d-flex justify-content-between">
                                    <span><i class="fas fa-circle text-success"></i> Freshman</span>
                                    <strong>{{ $demographics['type']['freshman'] ?? 0 }}%</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span><i class="fas fa-circle text-info"></i> Transfer</span>
                                    <strong>{{ $demographics['type']['transfer'] ?? 0 }}%</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span><i class="fas fa-circle text-warning"></i> Graduate</span>
                                    <strong>{{ $demographics['type']['graduate'] ?? 0 }}%</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span><i class="fas fa-circle text-purple"></i> International</span>
                                    <strong>{{ $demographics['type']['international'] ?? 0 }}%</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Additional Reports Row --}}
    <div class="row">
        {{-- Geographic Distribution --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-globe"></i> Geographic Distribution</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 300px;">
                        <table class="table table-sm mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Country/Region</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-center">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($geographic as $location)
                                <tr>
                                    <td>
                                        <img src="https://flagcdn.com/16x12/{{ strtolower($location->country_code ?? 'xx') }}.png" 
                                             class="me-1" alt="">
                                        {{ $location->country }}
                                    </td>
                                    <td class="text-center">{{ number_format($location->count) }}</td>
                                    <td class="text-center">{{ number_format($location->percentage, 1) }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Test Score Distribution --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Test Score Ranges</h5>
                </div>
                <div class="card-body">
                    <h6>SAT Distribution</h6>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small">
                            <span>1200-1300</span>
                            <span>{{ $testScores['sat']['1200-1300'] ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-info" style="width: {{ $testScores['sat']['1200-1300'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small">
                            <span>1300-1400</span>
                            <span>{{ $testScores['sat']['1300-1400'] ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-primary" style="width: {{ $testScores['sat']['1300-1400'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small">
                            <span>1400-1500</span>
                            <span>{{ $testScores['sat']['1400-1500'] ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-success" style="width: {{ $testScores['sat']['1400-1500'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small">
                            <span>1500+</span>
                            <span>{{ $testScores['sat']['1500+'] ?? 0 }}%</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-warning" style="width: {{ $testScores['sat']['1500+'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>GPA Distribution</h6>
                    <canvas id="gpaChart" height="150"></canvas>
                </div>
            </div>
        </div>

        {{-- Processing Metrics --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-tachometer-alt"></i> Processing Metrics</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-7">Avg. Days to Review:</dt>
                        <dd class="col-5">{{ number_format($processing['avg_review_days'] ?? 0, 1) }} days</dd>
                        
                        <dt class="col-7">Avg. Days to Decision:</dt>
                        <dd class="col-5">{{ number_format($processing['avg_decision_days'] ?? 0, 1) }} days</dd>
                        
                        <dt class="col-7">Reviews per Application:</dt>
                        <dd class="col-5">{{ number_format($processing['avg_reviews'] ?? 0, 1) }}</dd>
                        
                        <dt class="col-7">Document Verification:</dt>
                        <dd class="col-5">{{ number_format($processing['avg_doc_days'] ?? 0, 1) }} days</dd>
                        
                        <dt class="col-7">Incomplete Applications:</dt>
                        <dd class="col-5">{{ $processing['incomplete_rate'] ?? 0 }}%</dd>
                        
                        <dt class="col-7">Withdrawn Applications:</dt>
                        <dd class="col-5">{{ $processing['withdrawn_rate'] ?? 0 }}%</dd>
                    </dl>
                    
                    <hr>
                    
                    <h6>Decision Distribution</h6>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="text-success">
                                <i class="fas fa-check-circle fa-2x"></i>
                                <p class="mb-0"><strong>{{ $decisions['admit'] ?? 0 }}%</strong></p>
                                <small>Admit</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-warning">
                                <i class="fas fa-clock fa-2x"></i>
                                <p class="mb-0"><strong>{{ $decisions['waitlist'] ?? 0 }}%</strong></p>
                                <small>Waitlist</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-danger">
                                <i class="fas fa-times-circle fa-2x"></i>
                                <p class="mb-0"><strong>{{ $decisions['deny'] ?? 0 }}%</strong></p>
                                <small>Deny</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Custom Report Modal --}}
<div class="modal fade" id="customReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generate Custom Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admissions.admin.reports.custom') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Data Fields</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="personal" id="fieldPersonal" checked>
                                <label class="form-check-label" for="fieldPersonal">Personal Information</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="academic" id="fieldAcademic" checked>
                                <label class="form-check-label" for="fieldAcademic">Academic Information</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="test_scores" id="fieldTestScores">
                                <label class="form-check-label" for="fieldTestScores">Test Scores</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="reviews" id="fieldReviews">
                                <label class="form-check-label" for="fieldReviews">Review Data</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="decisions" id="fieldDecisions">
                                <label class="form-check-label" for="fieldDecisions">Decision Information</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="fields[]" value="enrollment" id="fieldEnrollment">
                                <label class="form-check-label" for="fieldEnrollment">Enrollment Status</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6>Filters</h6>
                            <div class="mb-3">
                                <label for="customTerm" class="form-label">Term</label>
                                <select class="form-select" name="term_id" id="customTerm">
                                    @foreach($terms as $term)
                                    <option value="{{ $term->id }}">{{ $term->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="customProgram" class="form-label">Program</label>
                                <select class="form-select" name="program_id" id="customProgram">
                                    <option value="">All Programs</option>
                                    @foreach($programs as $program)
                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="customStatus" class="form-label">Decision Status</label>
                                <select class="form-select" name="decision" id="customStatus">
                                    <option value="">All Decisions</option>
                                    <option value="admit">Admitted</option>
                                    <option value="deny">Denied</option>
                                    <option value="waitlist">Waitlisted</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="reportFormat" class="form-label">Output Format</label>
                            <select class="form-select" name="format" id="reportFormat">
                                <option value="excel">Excel (.xlsx)</option>
                                <option value="csv">CSV (.csv)</option>
                                <option value="pdf">PDF Report</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="reportName" class="form-label">Report Name</label>
                            <input type="text" class="form-control" name="report_name" id="reportName" 
                                   value="Admissions Report {{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-download"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .opacity-50 {
        opacity: 0.5;
    }
    .text-purple {
        color: #6f42c1;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Initialize charts
$(document).ready(function() {
    // Funnel Chart
    const funnelCtx = document.getElementById('funnelChart').getContext('2d');
    new Chart(funnelCtx, {
        type: 'bar',
        data: {
            labels: ['Started', 'Submitted', 'Complete', 'Reviewed', 'Admitted', 'Enrolled'],
            datasets: [{
                label: 'Applications',
                data: [
                    {{ $funnel['started'] ?? 0 }},
                    {{ $funnel['submitted'] ?? 0 }},
                    {{ $funnel['complete'] ?? 0 }},
                    {{ $funnel['reviewed'] ?? 0 }},
                    {{ $funnel['admitted'] ?? 0 }},
                    {{ $funnel['enrolled'] ?? 0 }}
                ],
                backgroundColor: [
                    'rgba(108, 117, 125, 0.5)',
                    'rgba(13, 110, 253, 0.5)',
                    'rgba(23, 162, 184, 0.5)',
                    'rgba(255, 193, 7, 0.5)',
                    'rgba(40, 167, 69, 0.5)',
                    'rgba(220, 53, 69, 0.5)'
                ],
                borderColor: [
                    'rgba(108, 117, 125, 1)',
                    'rgba(13, 110, 253, 1)',
                    'rgba(23, 162, 184, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(40, 167, 69, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Timeline Chart
    const timelineCtx = document.getElementById('timelineChart').getContext('2d');
    new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($timeline['labels'] ?? []) !!},
            datasets: [{
                label: 'Applications',
                data: {!! json_encode($timeline['applications'] ?? []) !!},
                borderColor: 'rgb(13, 110, 253)',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.1
            }, {
                label: 'Decisions',
                data: {!! json_encode($timeline['decisions'] ?? []) !!},
                borderColor: 'rgb(40, 167, 69)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gender Chart
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    new Chart(genderCtx, {
        type: 'doughnut',
        data: {
            labels: ['Male', 'Female', 'Other'],
            datasets: [{
                data: [
                    {{ $demographics['gender']['male_count'] ?? 0 }},
                    {{ $demographics['gender']['female_count'] ?? 0 }},
                    {{ $demographics['gender']['other_count'] ?? 0 }}
                ],
                backgroundColor: [
                    'rgba(13, 110, 253, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(108, 117, 125, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Type Chart
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Freshman', 'Transfer', 'Graduate', 'International'],
            datasets: [{
                data: [
                    {{ $demographics['type']['freshman_count'] ?? 0 }},
                    {{ $demographics['type']['transfer_count'] ?? 0 }},
                    {{ $demographics['type']['graduate_count'] ?? 0 }},
                    {{ $demographics['type']['international_count'] ?? 0 }}
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(23, 162, 184, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(111, 66, 193, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // GPA Distribution Chart
    const gpaCtx = document.getElementById('gpaChart').getContext('2d');
    new Chart(gpaCtx, {
        type: 'bar',
        data: {
            labels: ['< 2.5', '2.5-3.0', '3.0-3.5', '3.5-4.0'],
            datasets: [{
                label: 'GPA Distribution',
                data: [
                    {{ $gpaDistribution['below_25'] ?? 0 }},
                    {{ $gpaDistribution['25_30'] ?? 0 }},
                    {{ $gpaDistribution['30_35'] ?? 0 }},
                    {{ $gpaDistribution['35_40'] ?? 0 }}
                ],
                backgroundColor: [
                    'rgba(220, 53, 69, 0.6)',
                    'rgba(255, 193, 7, 0.6)',
                    'rgba(23, 162, 184, 0.6)',
                    'rgba(40, 167, 69, 0.6)'
                ],
                borderColor: [
                    'rgba(220, 53, 69, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(23, 162, 184, 1)',
                    'rgba(40, 167, 69, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 10
                    }
                }
            }
        }
    });
});

// Update reports based on filters
function updateReports() {
    const termId = $('#reportTerm').val();
    const programId = $('#reportProgram').val();
    const dateFrom = $('#reportDateFrom').val();
    const dateTo = $('#reportDateTo').val();
    
    // Show loading state
    $('.card-body').addClass('loading');
    
    // Make AJAX call to update data
    $.ajax({
        url: '{{ route("admissions.admin.reports.data") }}',
        method: 'GET',
        data: {
            term_id: termId,
            program_id: programId,
            date_from: dateFrom,
            date_to: dateTo
        },
        success: function(response) {
            // Update metrics
            updateMetrics(response.metrics);
            // Update charts
            updateCharts(response);
            // Update tables
            updateTables(response);
            
            $('.card-body').removeClass('loading');
        },
        error: function() {
            toastr.error('Failed to update reports');
            $('.card-body').removeClass('loading');
        }
    });
}

// Update timeline view
function updateTimeline(period) {
    // Update button states
    $('.btn-group button').removeClass('active');
    event.target.classList.add('active');
    
    // Fetch new timeline data
    $.ajax({
        url: '{{ route("admissions.admin.reports.timeline") }}',
        method: 'GET',
        data: {
            period: period,
            term_id: $('#reportTerm').val()
        },
        success: function(response) {
            // Update timeline chart
            const chart = Chart.getChart('timelineChart');
            chart.data.labels = response.labels;
            chart.data.datasets[0].data = response.applications;
            chart.data.datasets[1].data = response.decisions;
            chart.update();
        }
    });
}

// Refresh all reports
function refreshReports() {
    updateReports();
    toastr.success('Reports refreshed');
}

// Export report function
function exportReport(type) {
    const termId = $('#reportTerm').val();
    const programId = $('#reportProgram').val();
    
    window.location.href = `{{ route("admissions.admin.reports.export") }}?type=${type}&term_id=${termId}&program_id=${programId}`;
}

// Update metrics cards
function updateMetrics(metrics) {
    $('#totalApplications').text(metrics.total_applications.toLocaleString());
    $('#acceptanceRate').text(metrics.acceptance_rate.toFixed(1) + '%');
    $('#yieldRate').text(metrics.yield_rate.toFixed(1) + '%');
    $('#avgProcessingDays').text(metrics.avg_processing_days);
}

// Update charts with new data
function updateCharts(data) {
    // Update funnel chart
    const funnelChart = Chart.getChart('funnelChart');
    if (funnelChart && data.funnel) {
        funnelChart.data.datasets[0].data = [
            data.funnel.started,
            data.funnel.submitted,
            data.funnel.complete,
            data.funnel.reviewed,
            data.funnel.admitted,
            data.funnel.enrolled
        ];
        funnelChart.update();
    }
    
    // Update other charts similarly...
}

// Update tables with new data
function updateTables(data) {
    // Update program statistics table
    if (data.programStats) {
        let tableHtml = '';
        data.programStats.forEach(stat => {
            tableHtml += `
                <tr>
                    <td>
                        <strong>${stat.program_name}</strong>
                        <small class="d-block text-muted">${stat.program_code}</small>
                    </td>
                    <td class="text-center">${stat.applied.toLocaleString()}</td>
                    <td class="text-center">${stat.admitted.toLocaleString()}</td>
                    <td class="text-center">${stat.enrolled.toLocaleString()}</td>
                    <td class="text-center">
                        <span class="badge bg-${stat.acceptance_rate > 50 ? 'success' : (stat.acceptance_rate > 25 ? 'warning' : 'danger')}">
                            ${stat.acceptance_rate.toFixed(1)}%
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-${stat.yield_rate > 40 ? 'success' : (stat.yield_rate > 25 ? 'warning' : 'danger')}">
                            ${stat.yield_rate.toFixed(1)}%
                        </span>
                    </td>
                </tr>
            `;
        });
        $('#programStatsTable tbody').html(tableHtml);
    }
}

// Auto-refresh every 5 minutes
setInterval(function() {
    if (!document.hidden) {
        updateReports();
    }
}, 300000);

// Initialize tooltips
$(function () {
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>
@endpush